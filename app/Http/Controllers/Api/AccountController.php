<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\AppFeedback;
use App\Models\AppNotification;
use App\Models\CallbackRequest;
use App\Models\KycSubmission;
use App\Models\Payment;
use App\Models\UserApiToken;
use Exception;
use Illuminate\Http\Request;

/**
 * Account-section extras for the mobile app: Manage Devices, My Transactions,
 * Notifications, App Feedback, and Settings.
 *
 * Devices and transactions read existing data (the app's own login tokens and
 * the Payment ledger); notifications / feedback / settings are the app's own
 * engagement surfaces (see the app_notifications / app_feedbacks tables and
 * users.app_settings). All behind `user.api`, all scoped to the token owner.
 */
class AccountController extends Controller
{
    use FileHandler;

    /* ------------------------------- Devices ------------------------------ */

    /** Every device that currently holds a login token for this account. */
    public function devices(Request $request)
    {
        $currentId = optional($request->attributes->get('user_api_token'))->id;

        $tokens = UserApiToken::where('user_id', $request->user()->id)
            ->orderByDesc('last_used_at')->get();

        return response()->json([
            'success' => true,
            'devices' => $tokens->map(fn ($t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'provider'     => $t->provider,
                'last_used_at' => optional($t->last_used_at)->toIso8601String(),
                'created_at'   => optional($t->created_at)->toIso8601String(),
                'is_current'   => $t->id === $currentId,
            ])->values(),
        ]);
    }

    /** Sign a device out by dropping its token. */
    public function revokeDevice(Request $request, $id)
    {
        UserApiToken::where('user_id', $request->user()->id)->where('id', $id)->firstOrFail()->delete();

        return response()->json(['success' => true, 'message' => 'Device signed out.']);
    }

    /* ---------------------------- Transactions ---------------------------- */

    /** My Transactions / Bills — this account's payment ledger. */
    public function transactions(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('package', 'method_currency.paymentMethod')
            ->latest()->paginate(20);

        return response()->json([
            'success'      => true,
            'transactions' => collect($payments->items())->map(fn ($p) => [
                'id'         => $p->id,
                'trx_number' => $p->trx_number,
                'amount'     => $p->amount,
                'charge'     => $p->charge,
                'status'     => (int) $p->status,
                'package'    => optional($p->package)->name ?? optional($p->package)->title,
                'method'     => optional(optional($p->method_currency)->paymentMethod)->name,
                'created_at' => optional($p->created_at)->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'total'        => $payments->total(),
            ],
        ]);
    }

    /* ---------------------------- Notifications --------------------------- */

    public function notifications(Request $request)
    {
        $items = AppNotification::where('user_id', $request->user()->id)
            ->latest()->paginate(20);

        return response()->json([
            'success'       => true,
            'unread_count'  => AppNotification::where('user_id', $request->user()->id)->where('is_read', false)->count(),
            'notifications' => collect($items->items())->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'body'       => $n->body,
                'image'      => $n->image,
                'link'       => $n->link,
                'is_read'    => (bool) $n->is_read,
                'created_at' => optional($n->created_at)->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function readNotification(Request $request, $id)
    {
        $n = AppNotification::where('user_id', $request->user()->id)->where('id', $id)->firstOrFail();
        $n->is_read = true;
        $n->save();

        return response()->json(['success' => true]);
    }

    public function readAllNotifications(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /* ------------------------------ Feedback ------------------------------ */

    /** Report a Bug / Suggest a Feature. */
    public function submitFeedback(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|in:bug,feature',
            'subject' => 'nullable|string|max:191',
            'message' => 'required|string|max:5000',
        ]);

        AppFeedback::create([
            'user_id' => $request->user()->id,
            'type'    => $data['type'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);

        return response()->json(['success' => true, 'message' => 'Thanks for your feedback!'], 201);
    }

    /* ------------------------------ Settings ------------------------------ */

    /** App settings — notification toggles etc., merged over sensible defaults. */
    public function getSettings(Request $request)
    {
        return response()->json([
            'success'  => true,
            'settings' => $this->settingsWithDefaults($request->user()->app_settings ?? []),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'display_name'          => 'nullable|string|max:60',
            'enable_notifications'  => 'nullable|boolean',
            'enable_full_page_notification' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $merged = array_merge($this->settingsWithDefaults($user->app_settings ?? []), array_filter($data, fn ($v) => $v !== null));
        $user->app_settings = $merged;
        $user->save();

        return response()->json(['success' => true, 'settings' => $merged]);
    }

    protected function settingsWithDefaults(array $settings): array
    {
        return array_merge([
            'display_name'                  => null,
            'enable_notifications'          => true,
            'enable_full_page_notification' => true,
        ], $settings);
    }

    /* ---------------------------- Push (FCM) ------------------------------ */

    /** Register (or refresh) this device's Firebase token for push. */
    public function registerPushToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string|max:255']);

        // Store it on the exact device (token) making the call.
        $token = $request->attributes->get('user_api_token');
        if ($token) {
            $token->forceFill(['fcm_token' => $request->fcm_token])->saveQuietly();
        }

        return response()->json(['success' => true, 'message' => 'Push token registered.']);
    }

    /* ---------------------------- Callback -------------------------------- */

    /** "Get a Callback" from the Help screen — lands in the admin panel. */
    public function requestCallback(Request $request)
    {
        $data = $request->validate([
            'phone'   => 'nullable|string|max:20',
            'topic'   => 'nullable|string|max:100',
            'message' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // One open request at a time, so a double-tap doesn't queue two.
        if (CallbackRequest::where('user_id', $user->id)->where('status', 0)->exists()) {
            return response()->json([
                'success' => true,
                'message' => 'You already have a callback request pending. Our team will call you soon.',
            ]);
        }

        CallbackRequest::create([
            'user_id' => $user->id,
            'name'    => $user->fullname() ?: $user->username,
            'phone'   => $data['phone'] ?? $user->mobile,
            'topic'   => $data['topic'] ?? null,
            'message' => $data['message'] ?? null,
            'status'  => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Callback requested. Our team will call you shortly.',
        ], 201);
    }

    /* -------------------------------- KYC --------------------------------- */

    /** Current KYC status (latest submission), for the "KYC Completed" row. */
    public function kycStatus(Request $request)
    {
        $kyc = KycSubmission::where('user_id', $request->user()->id)->latest()->first();

        return response()->json([
            'success' => true,
            'kyc'     => $kyc ? [
                'id'              => $kyc->id,
                'document_type'   => $kyc->document_type,
                'document_number' => $kyc->document_number,
                'front_image'     => $kyc->front_image ? asset($kyc->front_image) : null,
                'back_image'      => $kyc->back_image ? asset($kyc->back_image) : null,
                'status'          => (int) $kyc->status,
                'status_label'    => $kyc->statusLabel(),
                'admin_note'      => $kyc->admin_note,
                'submitted_at'    => optional($kyc->created_at)->toIso8601String(),
            ] : ['status' => null, 'status_label' => 'not_submitted'],
        ]);
    }

    /** Submit an ID document for verification — waits for admin approval. */
    public function submitKyc(Request $request)
    {
        $data = $request->validate([
            'document_type'   => 'required|in:aadhaar,pan,gst,driving_license,passport,other',
            'document_number' => 'nullable|string|max:100',
            'front_image'     => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'back_image'      => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Block a duplicate submission while one is still pending.
        if (KycSubmission::where('user_id', $request->user()->id)->where('status', 0)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Your KYC is already under review.',
            ], 422);
        }

        try {
            $front = $this->uploadFile('image', [
                'file' => $request->file('front_image'), 'path' => 'assets/images/kyc/', 'size' => null, 'prevFile' => null,
            ]);
            $back = $request->hasFile('back_image')
                ? $this->uploadFile('image', [
                    'file' => $request->file('back_image'), 'path' => 'assets/images/kyc/', 'size' => null, 'prevFile' => null,
                ])
                : null;
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not process the document image.'], 422);
        }

        $kyc = KycSubmission::create([
            'user_id'         => $request->user()->id,
            'document_type'   => $data['document_type'],
            'document_number' => $data['document_number'] ?? null,
            'front_image'     => $front,
            'back_image'      => $back,
            'status'          => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted. It will be reviewed by our team.',
            'kyc'     => ['id' => $kyc->id, 'status' => 0, 'status_label' => 'pending'],
        ], 201);
    }
}
