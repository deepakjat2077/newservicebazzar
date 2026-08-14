<?php

namespace App\Http\Controllers;

use App\Http\Libs\SendMail;
use App\Models\GuestConversation;
use App\Models\GuestMessage;
use App\Models\GuestOtp;
use App\Models\GuestUser;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GuestChatController extends Controller
{
    public function sessionStatus(Request $request)
    {
        $data = $request->validate([
            'listing_id' => 'required|integer',
        ]);

        $guestId = $request->session()->get('guest_id');
        $token = $request->session()->get('guest_token');
        if (!$guestId || !$token) {
            return response()->json(['ok' => true, 'verified' => false]);
        }

        $guest = GuestUser::where('id', $guestId)
            ->where('token', $token)
            ->first();
        if (!$guest || !$guest->email_verified_at) {
            return response()->json(['ok' => true, 'verified' => false]);
        }

        $listing = Listing::notExpired()->where('status', 1)->find($data['listing_id']);
        if (!$listing) {
            return $this->fail('Listing not found.', 404);
        }

        $conversation = GuestConversation::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'guest_user_id' => $guest->id,
            ],
            [
                'owner_id' => $listing->user_id,
                'status' => 'open',
            ]
        );

        if ((int) $conversation->owner_id !== (int) $listing->user_id) {
            $conversation->owner_id = $listing->user_id;
            $conversation->save();
        }

        $this->touchGuestOnline($guest->id);

        $guest->last_ip = $request->ip();
        $guest->save();

        $ownerStatus = $this->ownerStatus($conversation->owner_id);

        return response()->json([
            'ok' => true,
            'verified' => true,
            'conversation_id' => $conversation->id,
            'guest_name' => $guest->name,
            'guest_email' => $guest->email,
            'owner_online' => $ownerStatus['online'],
            'owner_last_seen' => $ownerStatus['last_seen'],
        ]);
    }

    public function notifications(Request $request)
    {
        $guestId = $request->session()->get('guest_id');
        $token = $request->session()->get('guest_token');
        if (!$guestId || !$token) {
            return response()->json(['ok' => true, 'active' => false]);
        }

        $guest = GuestUser::where('id', $guestId)
            ->where('token', $token)
            ->first();
        if (!$guest || !$guest->email_verified_at) {
            return response()->json(['ok' => true, 'active' => false]);
        }

        $this->touchGuestOnline($guest->id);

        $unreadQuery = GuestMessage::where('sender_type', 'owner')
            ->whereNull('read_at')
            ->whereHas('conversation', function ($query) use ($guest) {
                $query->where('guest_user_id', $guest->id);
            });

        $latest = (clone $unreadQuery)
            ->with(['conversation.listing'])
            ->orderByDesc('id')
            ->first();

        $count = $unreadQuery->count();

        return response()->json([
            'ok' => true,
            'active' => true,
            'guest_id' => $guest->id,
            'count' => $count,
            'latest_id' => $latest?->id,
            'conversation_id' => $latest?->conversation_id,
            'listing_name' => $latest?->conversation?->listing?->name,
        ]);
    }

   public function start(Request $request)
{
    $isAdminChat = $request->boolean('admin_chat');

    $data = $request->validate([
        'listing_id' => $isAdminChat ? 'nullable|integer' : 'required|integer',
        'admin_chat' => 'nullable|boolean',
        'name' => 'required|string|max:120',
        'email' => 'required|email|max:190',
        'phone' => 'nullable|string|max:30',
        'first_message' => 'nullable|string|max:' . config('guest_chat.message_max', 1000),
    ]);

    if (config('guest_chat.recaptcha_enabled')) {
        // TODO: Validate reCAPTCHA response here when enabled.
    }

    $listing = null;

    if (!$isAdminChat) {
        $listing = Listing::notExpired()
            ->where('status', 1)
            ->with('user')
            ->find($data['listing_id']);

        if (!$listing) {
            return $this->fail('Listing not found.', 404);
        }
    }

    $email = strtolower($data['email']);
    $guest = GuestUser::where('email', $email)->first();

    if (!$guest) {
        $guest = new GuestUser();
        $guest->email = $email;
    }

    // Strip any markup at capture time — this value later gets embedded into
    // outbound notification emails (see SendMail::sendMailProcess).
    $guest->name = trim(strip_tags($data['name']));
    if (!empty($data['phone'])) {
        $guest->phone = trim(strip_tags($data['phone']));
    }

    $guest->last_ip = $request->ip();
    $guest->save();

    GuestOtp::where('guest_user_id', $guest->id)->delete();

    $code = (string) random_int(100000, 999999);
    $expiresAt = now()->addMinutes(config('guest_chat.otp_exp_minutes', 10));

    GuestOtp::create([
        'guest_user_id' => $guest->id,
        'code' => $code,
        'expires_at' => $expiresAt,
    ]);

    try {
        $this->sendOtpEmail($guest, $code, $expiresAt);
    } catch (\Throwable $e) {
        return $this->fail('Unable to send verification code. Please try again.', 500);
    }

    $request->session()->put('guest_pending_id', $guest->id);

    if ($isAdminChat) {
        $request->session()->forget('guest_pending_listing_id');
        $request->session()->put('guest_pending_admin_chat', 1);
    } else {
        $request->session()->put('guest_pending_listing_id', $listing->id);
        $request->session()->forget('guest_pending_admin_chat');
    }

    $firstMessage = $this->cleanMessage($data['first_message'] ?? '');

    if ($firstMessage !== '') {
        $request->session()->put('guest_first_message', $firstMessage);
    } else {
        $request->session()->forget('guest_first_message');
    }

    return response()->json([
        'ok' => true,
        'step' => 'otp_sent',
    ]);
}

 public function verifyOtp(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email|max:190',
        'otp' => 'required|digits:6',
        'listing_id' => 'nullable|integer',
        'admin_chat' => 'nullable|boolean',
    ]);

    $email = strtolower($data['email']);

    $guest = GuestUser::where('email', $email)->first();
    if (!$guest) {
        return $this->fail('Guest not found.', 404);
    }

    $pendingId = $request->session()->get('guest_pending_id');
    if ($pendingId && (int) $pendingId !== (int) $guest->id) {
        return $this->fail('OTP session mismatch. Please restart chat.', 422);
    }

    $otp = GuestOtp::where('guest_user_id', $guest->id)->orderByDesc('id')->first();
    if (!$otp) {
        return $this->fail('OTP not found. Please request a new code.', 422);
    }

    $maxAttempts = (int) config('guest_chat.otp_attempts_max', 5);
    if ($otp->attempts >= $maxAttempts) {
        return $this->fail('Too many attempts. Please request a new code.', 429);
    }

    if ($otp->expires_at->isPast()) {
        return $this->fail('OTP expired. Please request a new code.', 422);
    }

    if (!hash_equals($otp->code, $data['otp'])) {
        $otp->increment('attempts');
        $attemptsLeft = max(0, $maxAttempts - $otp->attempts);
        return $this->fail('Invalid OTP. Attempts left: ' . $attemptsLeft . '.', 422);
    }

    $otp->delete();

    $isAdminChat = $request->boolean('admin_chat')
        || (int) $request->session()->get('guest_pending_admin_chat') === 1;

    if ($isAdminChat) {

        $adminId = 1; // yaha apne admin user ka id lagao

        $conversation = GuestConversation::firstOrCreate(
            [
                'guest_user_id' => $guest->id,
                'listing_id' => null,
            ],
            [
                'owner_id' => $adminId,
                'status' => 'open',
            ]
        );

        if ((int) $conversation->owner_id !== (int) $adminId) {
            $conversation->owner_id = $adminId;
            $conversation->save();
        }

    } else {

        $listingId = $data['listing_id'] ?? $request->session()->get('guest_pending_listing_id');

        if (!$listingId) {
            return $this->fail('Listing context missing. Please restart chat.', 422);
        }

        $listing = Listing::notExpired()->where('status', 1)->find($listingId);

        if (!$listing) {
            return $this->fail('Listing not found.', 404);
        }

        $conversation = GuestConversation::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'guest_user_id' => $guest->id,
            ],
            [
                'owner_id' => $listing->user_id,
                'status' => 'open',
            ]
        );

        if ((int) $conversation->owner_id !== (int) $listing->user_id) {
            $conversation->owner_id = $listing->user_id;
            $conversation->save();
        }
    }

    $token = Str::random(64);

    $guest->token = $token;
    $guest->email_verified_at = $guest->email_verified_at ?? now();
    $guest->last_ip = $request->ip();
    $guest->save();

    $this->touchGuestOnline($guest->id);

    $request->session()->put('guest_id', $guest->id);
    $request->session()->put('guest_token', $token);

    $request->session()->forget([
        'guest_pending_id',
        'guest_pending_listing_id',
        'guest_pending_admin_chat',
    ]);

    $firstMessage = $request->session()->get('guest_first_message');

    if (!empty($firstMessage)) {
        $message = $conversation->messages()->create([
            'sender_type' => 'guest',
            'body' => $firstMessage,
        ]);

        $conversation->last_message_at = $message->created_at;
        $conversation->save();

        $request->session()->forget('guest_first_message');
    }

    return response()->json([
        'ok' => true,
        'step' => 'verified',
        'conversation_id' => $conversation->id,
    ]);
}

    public function send(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'required|integer',
            'body' => 'required|string|max:' . config('guest_chat.message_max', 1000),
        ]);

        $guestId = $request->session()->get('guest_id');
        $token = $request->session()->get('guest_token');
        if (!$guestId || !$token) {
            return $this->fail('Guest verification required.', 403);
        }

        $guest = GuestUser::where('id', $guestId)
            ->where('token', $token)
            ->first();
        if (!$guest || !$guest->email_verified_at) {
            return $this->fail('Guest verification required.', 403);
        }

        $conversation = GuestConversation::where('id', $data['conversation_id'])
            ->where('guest_user_id', $guest->id)
            ->first();
        if (!$conversation) {
            return $this->fail('Conversation not found.', 404);
        }

        if ($conversation->status !== 'open') {
            return $this->fail('Conversation is closed.', 403);
        }

        $body = $this->cleanMessage($data['body']);
        if ($body === '') {
            return $this->fail('Message cannot be empty.', 422);
        }

    $message = $conversation->messages()->create([
    'sender_type' => 'guest',
    'body' => $body,
]);

$conversation->last_message_at = $message->created_at;
$conversation->save();

event(new \App\Events\AdminNotificationEvent(
    'guest_chat',
    \App\Models\GuestMessage::where('sender_type', 'guest')
        ->whereNull('read_at')
        ->count()
));

$this->touchGuestOnline($guest->id);
$this->maybeNotifyOwner($conversation, $guest, $message);

        return response()->json([
            'ok' => true,
            'message' => $this->messagePayload($message),
        ]);
    }

    public function messages(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'required|integer',
            'after_id' => 'nullable|integer|min:0',
        ]);

        $guestId = $request->session()->get('guest_id');
        $token = $request->session()->get('guest_token');
        if (!$guestId || !$token) {
            return $this->fail('Guest verification required.', 403);
        }

        $guest = GuestUser::where('id', $guestId)
            ->where('token', $token)
            ->first();
        if (!$guest || !$guest->email_verified_at) {
            return $this->fail('Guest verification required.', 403);
        }

        $conversation = GuestConversation::where('id', $data['conversation_id'])
            ->where('guest_user_id', $guest->id)
            ->first();
        if (!$conversation) {
            return $this->fail('Conversation not found.', 404);
        }

        $query = $conversation->messages()->orderBy('id', 'asc');
        if (!empty($data['after_id'])) {
            $query->where('id', '>', $data['after_id']);
        }
        $messages = $query->limit(50)->get();

        $unreadOwnerIds = $messages
            ->where('sender_type', 'owner')
            ->whereNull('read_at')
            ->pluck('id');
        if ($unreadOwnerIds->isNotEmpty()) {
            GuestMessage::whereIn('id', $unreadOwnerIds)->update(['read_at' => now()]);
        }

        $this->touchGuestOnline($guest->id);
        $ownerStatus = $this->ownerStatus($conversation->owner_id);

        return response()->json([
            'ok' => true,
            'messages' => $messages->map(fn ($message) => $this->messagePayload($message)),
            'owner_online' => $ownerStatus['online'],
            'owner_last_seen' => $ownerStatus['last_seen'],
        ]);
    }

    protected function sendOtpEmail(GuestUser $guest, string $code, $expiresAt): void
    {
        $expireMinutes = (int) config('guest_chat.otp_exp_minutes', 10);
        $message = '<p>Hello ' . e($guest->name) . ',</p>'
            . '<p>Your verification code is:</p>'
            . '<h2 style="margin:12px 0; letter-spacing:2px;">' . e($code) . '</h2>'
            . '<p>This code will expire in ' . $expireMinutes . ' minutes.</p>'
            . '<p>If you did not request this code, you can ignore this email.</p>';

        $data = [
            'name' => $guest->name,
            'email' => $guest->email,
            'subject' => 'Your verification code',
            'template' => 'guest_otp',
            'value' => [
                'message' => $message,
                'code' => $code,
                'expires' => $expireMinutes,
            ],
        ];

        $mail = new SendMail();
        $mail->sendMail($data);
    }

    protected function maybeNotifyOwner(GuestConversation $conversation, GuestUser $guest, GuestMessage $message): void
    {
        if ($this->isOwnerOnline($conversation->owner_id)) {
            return;
        }

        $cacheKey = 'guest_owner_notify_' . $conversation->id;
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));

        $owner = $conversation->owner()->first();
        $listing = $conversation->listing()->first();
        if (!$owner || !$listing) {
            return;
        }

        $body = nl2br(e($message->body));
        $listingUrl = route('listings.details', $listing->slug);
        $notifyMessage = '<p>You have a new guest message about <strong>' . e($listing->name) . '</strong>.</p>'
            . '<p><strong>Guest:</strong> ' . e($guest->name) . ' (' . e($guest->email) . ')</p>'
            . '<p><strong>Message:</strong><br>' . $body . '</p>'
            . '<p><a href="' . e($listingUrl) . '">View listing</a> and reply from your dashboard.</p>';

        $data = [
            'name' => $owner->fullname(),
            'email' => $owner->email,
            'subject' => 'New guest message',
            'template' => 'guest_chat_owner_notify',
            'value' => [
                'message' => $notifyMessage,
            ],
        ];

        $mail = new SendMail();
        $mail->sendMail($data);
    }

    protected function touchGuestOnline(int $guestId): void
    {
        Cache::put('guest_chat_seen_' . $guestId, now()->timestamp, now()->addMinutes(5));
    }

    protected function ownerStatus(int $ownerId): array
    {
        $lastSeen = Cache::get('owner_chat_seen_' . $ownerId);
        $online = $lastSeen && $lastSeen >= now()->subMinutes(3)->timestamp;
        return [
            'online' => (bool) $online,
            'last_seen' => $lastSeen ? \Carbon\Carbon::createFromTimestamp($lastSeen)->toDateTimeString() : null,
        ];
    }

    protected function isOwnerOnline(int $ownerId): bool
    {
        $lastSeen = Cache::get('owner_chat_seen_' . $ownerId);
        return $lastSeen && $lastSeen >= now()->subMinutes(3)->timestamp;
    }

    protected function cleanMessage(string $body): string
    {
        $clean = trim(strip_tags($body));
        $max = (int) config('guest_chat.message_max', 1000);
        if (mb_strlen($clean) > $max) {
            $clean = mb_substr($clean, 0, $max);
        }
        return $clean;
    }

    protected function messagePayload(GuestMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'body' => $message->body,
            'created_at' => $message->created_at?->toDateTimeString(),
        ];
    }

    protected function fail(string $message, int $status = 422)
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
        ], $status);
    }
}
