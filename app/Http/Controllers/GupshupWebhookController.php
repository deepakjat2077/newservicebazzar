<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gupshup's callback endpoint.
 *
 * Two kinds of POST arrive here:
 *
 *   - "message"       — a user replied on WhatsApp. Stored as an inbound row
 *                       so it shows in the admin panel and badges as unread.
 *   - "message-event" — delivery status for something we sent (enqueued /
 *                       sent / delivered / read / failed). Matched back to the
 *                       outbound row by Gupshup's message id.
 *
 * Anything else is logged and acknowledged; Gupshup retries on a non-200, so
 * unknown event types must still return 200.
 */
class GupshupWebhookController extends Controller
{
    /**
     * Bumped whenever this file changes, and echoed in the GET response.
     *
     * Deploying to shared hosting is a manual file copy, so this is the only
     * quick way to tell from a browser whether the server is actually running
     * the version you think it is.
     */
    public const HANDLER_VERSION = '2026-08-01.6';

    /**
     * Where the last POST is recorded.
     *
     * A plain file, not the cache: on shared hosting a misconfigured cache
     * driver fails silently, and then "no POST recorded" is indistinguishable
     * from "the cache is broken" — which is exactly the ambiguity this is
     * meant to settle.
     */
    protected function markerPath(): string
    {
        return storage_path('app/gupshup-last-post.json');
    }

    protected function readMarker(): ?array
    {
        try {
            if (!is_file($this->markerPath())) {
                return null;
            }

            $decoded = json_decode((string) file_get_contents($this->markerPath()), true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function writeMarker(array $data): void
    {
        try {
            @file_put_contents($this->markerPath(), json_encode($data, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            // Never let diagnostics cost us a webhook.
        }
    }

    /** Status names Gupshup uses, mapped to what we store. */
    protected const STATUS_MAP = [
        'enqueued'  => 'sent',
        'sent'      => 'sent',
        'delivered' => 'delivered',
        'read'      => 'read',
        'failed'    => 'failed',
        'error'     => 'failed',
        'rejected'  => 'failed',
        'deleted'   => 'failed',
    ];

    /** Never move a delivered/read message back to an earlier state. */
    protected const RANK = [
        'queued' => 0, 'submitted' => 1, 'sent' => 2, 'delivered' => 3, 'read' => 4,
    ];

    public function handle(Request $request)
    {
        // This route is CSRF-exempt (it's an external webhook) and
        // unauthenticated, so a shared secret is the only thing standing
        // between this endpoint and anyone on the internet who can POST to
        // it. Reject anything that doesn't present the configured secret
        // before doing any processing or logging of the payload.
        $expectedSecret = config('services.gupshup.webhook_secret');

        if ($expectedSecret) {
            $providedSecret = (string) $request->query('token', $request->header('X-Webhook-Token', ''));

            if (!hash_equals((string) $expectedSecret, $providedSecret)) {
                Log::warning('Gupshup webhook rejected: invalid or missing token', [
                    'ip' => $request->ip(),
                ]);

                return response('', 401);
            }
        }

        $rawBody = $request->getContent();
        $payload = $request->all();

        if (empty($payload) && !empty($rawBody)) {
            $decoded = json_decode($rawBody, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        /*
         * Diagnostics only on an explicit ?diag=1, never otherwise.
         *
         * Gupshup support (ticket, 1 Aug 2026): "The webhook should return
         * HTTP_SUCCESS (code: 2xx) with an empty response." Our JSON body
         * made Gupshup treat the callback as invalid, which is why it stopped
         * delivering. So every ordinary request — GET or POST — now gets a
         * bare 200 with no body.
         */
        if ($request->boolean('diag')) {
            return response()->json([
                'success'         => true,
                'message'         => 'Diagnostics only. Gupshup itself receives an empty 200.',
                'method'          => $request->method(),
                'handler_version' => self::HANDLER_VERSION,
                'secret_required' => (bool) $expectedSecret,
                'configured'      => (bool) config('services.gupshup.api_key'),
                'replies_stored'  => WhatsappMessage::inbound()->count(),
                'last_reply_at'   => optional(WhatsappMessage::inbound()->latest('id')->first())->created_at,
                'last_post'       => $this->readMarker(),
            ], 200);
        }

        if ($request->isMethod('get')) {
            return $this->ack();
        }

        // One line per incoming POST, unconditionally. Without it there is no
        // way to tell "Gupshup never called us" apart from "it called and we
        // ignored the payload" when something is wrong in production.
        Log::info('Gupshup webhook POST received', [
            'ip'      => $request->ip(),
            'type'    => data_get($payload, 'type') ?? data_get($payload, 'object'),
            'keys'    => is_array($payload) ? array_keys($payload) : [],
            'payload' => $payload,
        ]);

        // Stamped so the GET response can answer "has Gupshup ever POSTed
        // here?" without anyone having to open a log file on the server.
        $this->writeMarker([
            'at'      => now()->toDateTimeString(),
            'ip'      => $request->ip(),
            'type'    => data_get($payload, 'type') ?? data_get($payload, 'object'),
            'keys'    => is_array($payload) ? array_keys($payload) : [],
            'payload' => $payload,
        ]);

        // Gupshup lets you register a webhook in either "Gupshup format (v2)"
        // or "Meta format (v3)", and both can be switched on at once. The two
        // payload shapes are completely different, so detect and branch.
        //
        // Note v2 is the one that matters for delivery status: its event ids
        // are the same messageId the send API returns, so they can be matched
        // back to our outbound rows. Meta's wamid never will be.
        if (is_array(data_get($payload, 'entry'))) {
            return $this->handleMetaPayload($payload);
        }

        $eventType = strtolower((string) (
            data_get($payload, 'type')
            ?? data_get($payload, 'payload.type')
            ?? ''
        ));

        if ($eventType === 'message') {
            return $this->handleInboundMessage($payload);
        }

        if ($eventType === 'message-event') {
            return $this->handleStatusEvent($payload);
        }

        Log::info('Gupshup webhook: unhandled event', [
            'type'    => $eventType,
            'payload' => $payload,
        ]);

        return $this->ack();
    }

    /**
     * The only thing Gupshup is allowed to see: 200, empty body.
     *
     * Anything else — JSON, whitespace, a redirect — and Gupshup marks the
     * callback invalid and stops delivering to it.
     */
    protected function ack()
    {
        return response('', 200)->header('Content-Type', 'text/plain');
    }

    /*
    |--------------------------------------------------------------------------
    | Meta format (v3)
    |--------------------------------------------------------------------------
    */

    /**
     * WhatsApp Cloud API shape:
     * entry[].changes[].value.messages[]  — replies
     * entry[].changes[].value.statuses[]  — delivery receipts
     */
    protected function handleMetaPayload(array $payload)
    {
        $stored = 0;
        $updated = 0;

        foreach ((array) data_get($payload, 'entry', []) as $entry) {
            foreach ((array) data_get($entry, 'changes', []) as $change) {
                $value = data_get($change, 'value', []);

                // Profile names arrive in a parallel array keyed by wa_id.
                $names = [];

                foreach ((array) data_get($value, 'contacts', []) as $contact) {
                    $waId = preg_replace('/\D+/', '', (string) data_get($contact, 'wa_id'));

                    if ($waId !== '') {
                        $names[$waId] = data_get($contact, 'profile.name');
                    }
                }

                foreach ((array) data_get($value, 'messages', []) as $message) {
                    $phone = preg_replace('/\D+/', '', (string) data_get($message, 'from'));

                    if ($phone === '') {
                        continue;
                    }

                    $messageId = data_get($message, 'id');

                    $body = $this->metaBody($message);

                    if ($this->isDuplicateInbound($phone, $body, $messageId)) {
                        continue;
                    }

                    WhatsappMessage::create([
                        'direction'    => 'inbound',
                        'phone'        => $phone,
                        'contact_name' => $names[$phone] ?? null,
                        'body'         => $body,
                        'message_id'   => $messageId,
                        'status'       => 'received',
                        'payload'      => $payload,
                        'listing_id'   => $this->lastListingIdFor($phone),
                    ]);

                    $stored++;
                }

                foreach ((array) data_get($value, 'statuses', []) as $status) {
                    $updated += $this->applyStatus(
                        data_get($status, 'id'),
                        strtolower((string) data_get($status, 'status')),
                        data_get($status, 'errors.0.title')
                            ?? data_get($status, 'errors.0.message')
                    ) ? 1 : 0;
                }
            }
        }

        Log::info('Gupshup webhook (Meta v3) processed', [
            'replies_stored'   => $stored,
            'statuses_updated' => $updated,
        ]);

        return $this->ack();
    }

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    */

    /** Content types we are willing to write into a public web directory. */
    protected const MEDIA_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'audio/ogg'  => 'ogg',
        'audio/mpeg' => 'mp3',
        'audio/mp4'  => 'm4a',
        'audio/aac'  => 'aac',
        'video/mp4'  => 'mp4',
        'video/3gpp' => '3gp',
    ];

    /**
     * Fetch the media and keep our own copy.
     *
     * Gupshup's media URLs expire after a short window, so storing only the
     * link means the picture is gone in a day or two. Returns a public path
     * relative to the web root, or null if we could not or should not store
     * it — in which case the caller still keeps the original URL.
     */
    protected function storeMedia(?string $url, ?string $contentType): ?string
    {
        $contentType = strtolower(trim(explode(';', (string) $contentType)[0]));

        if (!$url || !isset(self::MEDIA_EXTENSIONS[$contentType])) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning('WhatsApp media download failed', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $body = $response->body();

            // 25 MB is above WhatsApp's own limits; anything larger is wrong.
            if ($body === '' || strlen($body) > 25 * 1024 * 1024) {
                return null;
            }

            $directory = public_path('assets/images/whatsapp');

            if (!is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }

            // Extension comes from our whitelist, never from the URL, so a
            // ".php" can't be talked into this directory.
            $filename = date('Ymd') . '-' . bin2hex(random_bytes(8))
                . '.' . self::MEDIA_EXTENSIONS[$contentType];

            if (@file_put_contents($directory . '/' . $filename, $body) === false) {
                return null;
            }

            return 'assets/images/whatsapp/' . $filename;

        } catch (\Throwable $exception) {
            Log::warning('WhatsApp media store failed', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /** Readable text for a Meta-format inbound message of any type. */
    protected function metaBody(array $message): string
    {
        $type = strtolower((string) data_get($message, 'type', 'text'));

        $text = data_get($message, 'text.body')
            ?? data_get($message, 'button.text')
            ?? data_get($message, 'interactive.button_reply.title')
            ?? data_get($message, 'interactive.list_reply.title')
            ?? data_get($message, $type . '.caption');

        if (is_string($text) && trim($text) !== '') {
            return trim($text);
        }

        return '[' . $type . ']';
    }

    /*
    |--------------------------------------------------------------------------
    | Inbound replies
    |--------------------------------------------------------------------------
    */

    /**
     * Gupshup retries on timeout, and with both the v2 and v3 webhooks
     * enabled the same reply arrives twice under two different ids — so fall
     * back to matching the same text from the same number within a minute.
     */
    protected function duplicateInbound(string $phone, string $body, $messageId): ?WhatsappMessage
    {
        if ($messageId) {
            $byId = WhatsappMessage::inbound()->where('message_id', $messageId)->first();

            if ($byId) {
                return $byId;
            }
        }

        return WhatsappMessage::inbound()
            ->where('phone', $phone)
            ->where('body', $body)
            ->where('created_at', '>=', now()->subMinute())
            ->latest('id')
            ->first();
    }

    protected function isDuplicateInbound(string $phone, string $body, $messageId): bool
    {
        return (bool) $this->duplicateInbound($phone, $body, $messageId);
    }

    protected function handleInboundMessage(array $payload)
    {
        $inner = data_get($payload, 'payload', []);

        $phone = preg_replace('/\D+/', '', (string) (
            data_get($inner, 'sender.phone')
            ?? data_get($inner, 'source')
            ?? ''
        ));

        if ($phone === '') {
            Log::warning('Gupshup inbound message without a sender', ['payload' => $payload]);

            return $this->ack();
        }

        $messageId = data_get($inner, 'id');
        $body = $this->readableBody($inner);

        // Gupshup v2 hands us a direct media URL for images/documents/audio.
        $mediaUrl = data_get($inner, 'payload.url');
        $mediaType = data_get($inner, 'payload.contentType');

        $existing = $this->duplicateInbound($phone, $body, $messageId);

        if ($existing) {
            // The same picture can arrive through both the v2 and the v3
            // webhook. Meta's copy carries no URL, so when the row we already
            // stored has no media, upgrade it instead of dropping this one.
            if ($mediaUrl && !$existing->hasMedia()) {
                $existing->update([
                    'media_url'  => $mediaUrl,
                    'media_type' => $mediaType,
                    'media_path' => $this->storeMedia($mediaUrl, $mediaType),
                ]);
            }

            return $this->ack();
        }

        WhatsappMessage::create([
            'direction'    => 'inbound',
            'phone'        => $phone,
            'contact_name' => data_get($inner, 'sender.name'),
            'body'         => $body,
            'media_url'    => $mediaUrl,
            'media_type'   => $mediaType,
            'media_path'   => $this->storeMedia($mediaUrl, $mediaType),
            'message_id'   => $messageId,
            'status'       => 'received',
            'payload'      => $payload,
            // Tie the reply back to whichever listing we last messaged on this
            // number, so the panel can show what it is a reply to.
            'listing_id'   => $this->lastListingIdFor($phone),
        ]);

        Log::info('Gupshup inbound WhatsApp reply stored', [
            'phone'      => $phone,
            'message_id' => $messageId,
        ]);

        return $this->ack();
    }

    /**
     * Replies are not always text — turn media/location/button payloads into
     * something readable for the panel's list.
     */
    protected function readableBody(array $inner): string
    {
        $type = strtolower((string) data_get($inner, 'type', 'text'));

        $text = data_get($inner, 'payload.text')
            ?? data_get($inner, 'payload.title')
            ?? data_get($inner, 'payload.caption');

        if (is_string($text) && trim($text) !== '') {
            return trim($text);
        }

        /*
         * The media URL used to be appended here so the row wasn't empty, but
         * it is already kept in media_url/media_path and the panel renders the
         * attachment itself — printing it again just dumped a raw gupshup.io
         * link under every picture. Keep the body to the type marker.
         */
        return '[' . $type . ']';
    }

    protected function lastListingIdFor(string $phone): ?int
    {
        return WhatsappMessage::outbound()
            ->where('phone', $phone)
            ->whereNotNull('listing_id')
            ->latest('id')
            ->value('listing_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery status
    |--------------------------------------------------------------------------
    */

    protected function handleStatusEvent(array $payload)
    {
        $inner = data_get($payload, 'payload', []);

        /*
         * Two different ids travel in these events and which one lands in
         * `payload.id` changes with the event:
         *
         *   enqueued        id = gsId (the one the send API returned to us),
         *                   payload.whatsappMessageId = WhatsApp's own id
         *   sent/delivered  id = WhatsApp's id, gsId = the send API's id
         *
         * We store whatever the send call handed back, so reading `id` first
         * meant every sent/delivered/read receipt looked up WhatsApp's id,
         * missed, and was dropped as "unknown message" — which is why nothing
         * ever moved past a single tick. Collect every id in the payload and
         * match on any of them.
         */
        $messageIds = array_values(array_unique(array_filter([
            data_get($inner, 'gsId'),
            data_get($inner, 'payload.gsId'),
            data_get($inner, 'id'),
            data_get($inner, 'payload.whatsappMessageId'),
            data_get($payload, 'messageId'),
        ], fn ($id) => is_scalar($id) && (string) $id !== '')));

        $rawStatus = strtolower((string) data_get($inner, 'type', ''));

        $status = self::STATUS_MAP[$rawStatus] ?? $rawStatus;

        $reason = data_get($inner, 'payload.reason')
            ?? data_get($inner, 'reason')
            ?? data_get($payload, 'reason');

        if (empty($messageIds)) {
            Log::info('Gupshup status event without a message id', ['payload' => $payload]);

            return $this->ack();
        }

        $applied = $this->applyStatus($messageIds, $status, $reason);

        return $this->ack();
    }

    /**
     * Move an outbound message to a new delivery state. Takes every id the
     * receipt carried and matches on whichever one we actually stored — see
     * handleStatusEvent(). Returns false when none of them is a row of ours.
     */
    protected function applyStatus($messageId, string $rawStatus, $reason = null): bool
    {
        $messageIds = array_values(array_filter(
            is_array($messageId) ? $messageId : [$messageId],
            fn ($id) => is_scalar($id) && (string) $id !== ''
        ));

        if (empty($messageIds)) {
            return false;
        }

        $status = self::STATUS_MAP[$rawStatus] ?? $rawStatus;

        $message = WhatsappMessage::outbound()
            ->whereIn('message_id', $messageIds)
            ->first();

        if (!$message) {
            Log::info('Gupshup status event for an unknown message', [
                'message_ids' => $messageIds,
                'status'      => $status,
            ]);

            return false;
        }

        // Events can arrive out of order — don't downgrade a delivered message
        // back to "sent" because a late event turned up.
        $currentRank = self::RANK[$message->status] ?? -1;
        $newRank = self::RANK[$status] ?? -1;

        if ($status === 'failed' || $newRank >= $currentRank) {
            $message->status = $status;

            if ($status === 'failed') {
                $message->error = is_string($reason)
                    ? $reason
                    : (is_array($reason) ? json_encode($reason) : 'Delivery failed');
            }

            $message->save();
        }

        if ($status === 'failed') {
            Log::error('Gupshup WhatsApp message failed', [
                'message_id' => $message->message_id,
                'reason'     => $reason,
            ]);
        }

        return true;
    }
}
