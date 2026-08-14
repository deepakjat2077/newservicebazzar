<?php

namespace App\Http\Controllers;

use App\Models\GuestConversation;
use App\Models\GuestMessage;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OwnerChatController extends Controller
{
    protected $setting;
    protected $theme;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->theme = 'themes.' . $this->setting->theme . '.';
    }

    public function index()
    {
        $this->touchOwnerOnline();
        $conversations = GuestConversation::where('owner_id', auth()->id())
            ->with(['listing', 'guestUser'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view($this->theme . 'user.guest_chats.index', compact('conversations'));
    }

    public function show(GuestConversation $conversation)
    {
        $this->touchOwnerOnline();
        if ((int) $conversation->owner_id !== (int) auth()->id()) {
            abort(403);
        }

        $conversation->load(['listing', 'guestUser']);
        $messages = $conversation->messages()->orderByDesc('id')->limit(50)->get()->reverse()->values();

        $unreadGuestIds = $messages
            ->where('sender_type', 'guest')
            ->whereNull('read_at')
            ->pluck('id');
        if ($unreadGuestIds->isNotEmpty()) {
            GuestMessage::whereIn('id', $unreadGuestIds)->update(['read_at' => now()]);
        }

        return view($this->theme . 'user.guest_chats.show', compact('conversation', 'messages'));
    }

    public function send(Request $request, GuestConversation $conversation)
    {
        $this->touchOwnerOnline();
        if ((int) $conversation->owner_id !== (int) auth()->id()) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($conversation->status !== 'open') {
            return response()->json(['ok' => false, 'message' => 'Conversation is closed.'], 403);
        }

        $data = $request->validate([
            'message' => 'required|string|max:' . config('guest_chat.message_max', 1000),
        ]);

        $body = $this->cleanMessage($data['message']);
        if ($body === '') {
            return response()->json(['ok' => false, 'message' => 'Message cannot be empty.'], 422);
        }

        $message = $conversation->messages()->create([
            'sender_type' => 'owner',
            'body' => $body,
        ]);

        $conversation->last_message_at = $message->created_at;
        $conversation->save();

        $this->maybeNotifyGuest($conversation, $message);

        return response()->json([
            'ok' => true,
            'message' => $this->messagePayload($message),
        ]);
    }

    public function messages(Request $request, GuestConversation $conversation)
    {
        $this->touchOwnerOnline();
        if ((int) $conversation->owner_id !== (int) auth()->id()) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'after_id' => 'nullable|integer|min:0',
        ]);

        $query = $conversation->messages()->orderBy('id', 'asc');
        if (!empty($data['after_id'])) {
            $query->where('id', '>', $data['after_id']);
        }
        $messages = $query->limit(50)->get();

        $unreadGuestIds = $messages
            ->where('sender_type', 'guest')
            ->whereNull('read_at')
            ->pluck('id');
        if ($unreadGuestIds->isNotEmpty()) {
            GuestMessage::whereIn('id', $unreadGuestIds)->update(['read_at' => now()]);
        }

        return response()->json([
            'ok' => true,
            'messages' => $messages->map(fn ($message) => $this->messagePayload($message)),
        ]);
    }

    public function notifications()
    {
        $this->touchOwnerOnline();

        $unreadQuery = GuestMessage::where('sender_type', 'guest')
            ->whereNull('read_at')
            ->whereHas('conversation', function ($query) {
                $query->where('owner_id', auth()->id());
            });

        $latest = (clone $unreadQuery)
            ->with(['conversation.guestUser', 'conversation.listing'])
            ->orderByDesc('id')
            ->first();

        $count = $unreadQuery->count();

        return response()->json([
            'ok' => true,
            'count' => $count,
            'latest_id' => $latest?->id,
            'conversation_id' => $latest?->conversation_id,
            'guest_name' => $latest?->conversation?->guestUser?->name,
            'listing_name' => $latest?->conversation?->listing?->name,
        ]);
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

    protected function touchOwnerOnline(): void
    {
        if (auth()->check()) {
            Cache::put('owner_chat_seen_' . auth()->id(), now()->timestamp, now()->addMinutes(5));
        }
    }

    protected function maybeNotifyGuest(GuestConversation $conversation, GuestMessage $message): void
    {
        $guest = $conversation->guestUser()->first();
        $listing = $conversation->listing()->first();
        if (!$guest || !$listing) {
            return;
        }

        if ($this->isGuestOnline($guest->id)) {
            return;
        }

        $cacheKey = 'guest_reply_notify_' . $conversation->id;
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));

        $body = nl2br(e($message->body));
        $listingUrl = route('listings.details', $listing->slug);
        $notifyMessage = '<p>You received a reply about <strong>' . e($listing->name) . '</strong>.</p>'
            . '<p><strong>Message:</strong><br>' . $body . '</p>'
            . '<p><a href="' . e($listingUrl) . '">Open the listing</a> to continue the chat.</p>';

        $data = [
            'name' => $guest->name,
            'email' => $guest->email,
            'subject' => 'Owner replied to your message',
            'template' => 'guest_chat_reply_notify',
            'value' => [
                'message' => $notifyMessage,
            ],
        ];

        $mail = new SendMail();
        $mail->sendMail($data);
    }

    protected function isGuestOnline(int $guestId): bool
    {
        $lastSeen = Cache::get('guest_chat_seen_' . $guestId);
        return $lastSeen && $lastSeen >= now()->subMinutes(3)->timestamp;
    }
}
