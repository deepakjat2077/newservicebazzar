<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\WhatsappMessage;
use App\Support\HttpRetry;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * One place that talks to the Gupshup template API.
 *
 * Every send writes a whatsapp_messages row so the admin panel can show what
 * went out, what failed, and — once the webhook fires — what was actually
 * delivered or read. Credentials come from services.gupshup.*, which
 * AppServiceProvider fills from the admin settings row.
 */
class WhatsappSender
{
    public const ENDPOINT = 'https://api.gupshup.io/wa/api/v1/template/msg';

    /** Free-form replies inside the 24-hour session window. */
    public const SESSION_ENDPOINT = 'https://api.gupshup.io/wa/api/v1/msg';

    /**
     * WhatsApp only allows a free-form reply within 24 hours of the user's
     * last inbound message. After that only an approved template may be sent.
     */
    public const SESSION_HOURS = 24;

    /** Gupshup statuses that mean "accepted, on its way". */
    protected const ACCEPTED = ['success', 'submitted', 'accepted', 'queued'];

    public function apiKey(): ?string
    {
        return config('services.gupshup.api_key');
    }

    public function appName(): ?string
    {
        return config('services.gupshup.app_name');
    }

    public function source(): string
    {
        return preg_replace('/\D+/', '', (string) config('services.gupshup.source'));
    }

    public function isConfigured(): bool
    {
        return (bool) ($this->apiKey() && $this->appName() && $this->source());
    }

    /**
     * Template ids saved in settings, as [id => label], for the send form's
     * dropdown. Blank slots are skipped.
     */
    public function templateOptions(): array
    {
        $slots = [
            'enquiry_template_id' => 'Enquiry template',
            'template_id'         => 'Listing / Marketing template',
            'otp_template_id'     => 'OTP template',
        ];

        $options = [];

        foreach ($slots as $key => $label) {
            $id = trim((string) config('services.gupshup.' . $key));

            if ($id !== '') {
                $options[$id] = $label;
            }
        }

        return $options;
    }

    public function defaultTemplateId(): ?string
    {
        $id = trim((string) config('services.gupshup.template_id'));

        return $id !== '' ? $id : null;
    }

    public function enquiryTemplateId(): ?string
    {
        $id = trim((string) config('services.gupshup.enquiry_template_id'));

        return $id !== '' ? $id : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Template variables
    |--------------------------------------------------------------------------
    */

    /**
     * The seven variables the enquiry template (servicebazzar_enquiry) takes,
     * in template order:
     *
     *   {{1}} contact person at the listing   {{5}} location
     *   {{2}} listing name                    {{6}} category
     *   {{3}} customer name                   {{7}} enquiry date & time
     *   {{4}} customer phone
     *
     * Values are taken from the enquiry first and fall back to the listing,
     * because a category enquiry carries the location/category while a
     * listing enquiry may not.
     */
    public static function enquiryParams(Enquiry $enquiry, Listing $listing): array
    {
        $contactPerson = trim((string) $listing->contact_person);

        if ($contactPerson === '') {
            // The template opens with "Hello {{1}}," so it must never be
            // blank — the business name reads fine there.
            $contactPerson = (string) $listing->name;
        }

        $location = optional($enquiry->location)->name
            ?: optional($listing->location)->name;

        $category = optional($enquiry->category)->name
            ?: optional($listing->category)->name;

        $date = optional($enquiry->created_at)
            ->timezone(config('app.timezone', 'Asia/Kolkata'))
            ->format('d M Y, h:i A');

        return array_map([static::class, 'cleanParam'], [
            $contactPerson,
            (string) $listing->name,
            (string) $enquiry->name,
            (string) $enquiry->phone,
            (string) $location,
            (string) $category,
            (string) $date,
        ]);
    }

    /**
     * WhatsApp rejects template variables containing newlines, tabs or more
     * than four consecutive spaces, and blank values, so squash whitespace
     * and substitute a dash for anything empty.
     */
    protected static function cleanParam(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value));

        return $value === '' ? '-' : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Listing helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Best WhatsApp number for a listing: the WhatsApp field if set, else the
     * mobile, prefixed with whichever country code the listing carries.
     * Returns null when there is nothing usable to dial.
     */
    public static function listingPhone(Listing $listing): ?string
    {
        $first = function ($value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (trim((string) $item) !== '') {
                        return trim((string) $item);
                    }
                }

                return '';
            }

            return trim((string) $value);
        };

        $number = $first($listing->whatsapp_numbers) ?: $first($listing->mobile_numbers);

        if ($number === '') {
            return null;
        }

        $code = $first($listing->whatsapp_country_code) ?: $first($listing->mobile_country_code);

        $phone = preg_replace('/\D+/', '', $code . $number);

        // A bare 10-digit Indian number has no country code in front of it.
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return (strlen($phone) >= 10 && strlen($phone) <= 15) ? $phone : null;
    }

    /**
     * The "category-slug/listing-slug" tail the template's dynamic button
     * appends to the site URL.
     */
    public static function listingButtonSuffix(Listing $listing): string
    {
        $categorySlug = optional($listing->category)->slug;

        return trim(($categorySlug ? $categorySlug . '/' : '') . $listing->slug, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | Sending
    |--------------------------------------------------------------------------
    */

    /**
     * Send a template to one listing. `$params` overrides the default
     * [name, button-suffix] pair for templates that take other variables.
     */
    public function sendToListing(
        Listing $listing,
        string $templateId,
        ?array $params = null,
        array $meta = []
    ): WhatsappMessage {
        $phone = static::listingPhone($listing);

        $meta = array_merge($meta, [
            'listing_id'   => $listing->id,
            'contact_name' => $listing->name,
        ]);

        if (!$phone) {
            return $this->logFailure(
                '',
                $templateId,
                'Listing has no usable WhatsApp or mobile number.',
                $meta
            );
        }

        if ($params === null || $params === []) {
            $params = [
                (string) $listing->name,
                static::listingButtonSuffix($listing),
            ];
        }

        return $this->sendTemplate($phone, $templateId, $params, $meta);
    }

    /**
     * Fire one template message and record the outcome. Never throws — the
     * failure is written to the log row and returned, so bulk sends can carry
     * on through a bad number.
     */
    public function sendTemplate(
        string $phone,
        string $templateId,
        array $params = [],
        array $meta = []
    ): WhatsappMessage {
        $phone = preg_replace('/\D+/', '', $phone);

        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return $this->logFailure($phone, $templateId, 'Invalid WhatsApp number.', $meta);
        }

        if (!$this->isConfigured()) {
            return $this->logFailure(
                $phone,
                $templateId,
                'Gupshup is not configured. Fill in Settings > WhatsApp Setting.',
                $meta
            );
        }

        if (trim($templateId) === '') {
            return $this->logFailure($phone, $templateId, 'No template id selected.', $meta);
        }

        $template = [
            'id'     => $templateId,
            'params' => array_values(array_map('strval', $params)),
        ];

        try {
            // ->retry() alone only covers a dropped connection; a 429 or 503
            // body counts as a completed request and would be logged as a
            // permanent failure for something that works seconds later.
            $attempt = HttpRetry::send(function () use ($phone, $template) {
                return Http::asForm()
                    ->acceptJson()
                    ->withHeaders([
                        'apikey'        => $this->apiKey(),
                        'Cache-Control' => 'no-cache',
                    ])
                    ->connectTimeout(10)
                    ->timeout(30)
                    ->post(self::ENDPOINT, [
                        'channel'     => 'whatsapp',
                        'source'      => $this->source(),
                        'destination' => $phone,
                        'src.name'    => $this->appName(),
                        'template'    => json_encode(
                            $template,
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        ),
                    ]);
            }, 3, 2000, 'gupshup.template', 20);

            $response = $attempt['response'];

            if ($response === null) {
                throw $attempt['exception'] ?? new ConnectionException('Gupshup could not be reached.');
            }

            $data = $response->json();

            if (!is_array($data)) {
                $data = ['raw' => $response->body()];
            }

            $status = strtolower(trim((string) ($data['status'] ?? '')));

            $rejected = !$response->successful()
                || ($status !== '' && !in_array($status, self::ACCEPTED, true));

            if ($rejected) {
                $reason = $data['message']
                    ?? $data['reason']
                    ?? $data['error']
                    ?? 'Gupshup rejected the message.';

                Log::warning('WhatsApp send rejected', [
                    'destination' => $phone,
                    'template_id' => $templateId,
                    'http_status' => $response->status(),
                    'response'    => $data,
                ]);

                return $this->log($phone, $templateId, 'failed', $meta, [
                    'error'   => is_string($reason) ? $reason : json_encode($reason),
                    'payload' => $data,
                    'body'    => $meta['log_body'] ?? implode(' | ', $template['params']),
                ]);
            }

            return $this->log($phone, $templateId, 'submitted', $meta, [
                'message_id' => $data['messageId'] ?? $data['message_id'] ?? null,
                'payload'    => $data,
                // `log_body` lets a caller keep the parameters out of the
                // admin-readable log — a one-time code has no business
                // sitting in a table anybody with that screen can read.
                'body'       => $meta['log_body'] ?? implode(' | ', $template['params']),
            ]);

        } catch (ConnectionException $exception) {
            Log::error('WhatsApp connection error', [
                'destination' => $phone,
                'error'       => $exception->getMessage(),
            ]);

            return $this->logFailure(
                $phone,
                $templateId,
                'Could not reach Gupshup: ' . $this->shortMessage($exception),
                $meta
            );

        } catch (\Throwable $exception) {
            Log::error('WhatsApp send exception', [
                'destination' => $phone,
                'error'       => $exception->getMessage(),
                'class'       => get_class($exception),
                'file'        => $exception->getFile(),
                'line'        => $exception->getLine(),
            ]);

            // Surface the real reason rather than a generic "server error" —
            // this table is admin-only, and a bare "something broke" makes a
            // live problem impossible to diagnose without SSH access.
            return $this->logFailure(
                $phone,
                $templateId,
                class_basename($exception) . ': ' . $this->shortMessage($exception)
                    . ' (' . basename($exception->getFile()) . ':' . $exception->getLine() . ')',
                $meta
            );
        }
    }

    /** Exception text trimmed to something that fits a table cell. */
    protected function shortMessage(\Throwable $exception): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $exception->getMessage()));

        if ($message === '') {
            return 'no message';
        }

        return mb_strlen($message) > 300 ? mb_substr($message, 0, 300) . '…' : $message;
    }

    /*
    |--------------------------------------------------------------------------
    | Free-form replies (session messages)
    |--------------------------------------------------------------------------
    */

    /**
     * Has this number messaged us recently enough that we may reply freely?
     *
     * WhatsApp closes the window 24 hours after the user's last message; a
     * free-form send after that is rejected by Meta, not by us.
     */
    public static function sessionOpenFor(string $phone): bool
    {
        $lastInbound = WhatsappMessage::inbound()
            ->where('phone', preg_replace('/\D+/', '', $phone))
            ->latest('id')
            ->value('created_at');

        if (!$lastInbound) {
            return false;
        }

        return $lastInbound->gt(now()->subHours(self::SESSION_HOURS));
    }

    /** When the reply window for this number shuts. Null if already shut. */
    public static function sessionExpiresAt(string $phone)
    {
        $lastInbound = WhatsappMessage::inbound()
            ->where('phone', preg_replace('/\D+/', '', $phone))
            ->latest('id')
            ->value('created_at');

        if (!$lastInbound) {
            return null;
        }

        $expires = $lastInbound->copy()->addHours(self::SESSION_HOURS);

        return $expires->isFuture() ? $expires : null;
    }

    /**
     * Send a plain text reply. Only valid inside the 24-hour window — outside
     * it, use sendTemplate() instead.
     */
    public function sendText(string $phone, string $text, array $meta = []): WhatsappMessage
    {
        $phone = preg_replace('/\D+/', '', $phone);
        $text = trim($text);

        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return $this->logFailure($phone, '', 'Invalid WhatsApp number.', $meta, $text);
        }

        if ($text === '') {
            return $this->logFailure($phone, '', 'Message is empty.', $meta, $text);
        }

        if (!$this->isConfigured()) {
            return $this->logFailure(
                $phone,
                '',
                'Gupshup is not configured. Fill in Settings > WhatsApp Setting.',
                $meta,
                $text
            );
        }

        if (!static::sessionOpenFor($phone)) {
            return $this->logFailure(
                $phone,
                '',
                'The 24-hour reply window has closed for this number. Send an approved template instead.',
                $meta,
                $text
            );
        }

        try {
            $attempt = HttpRetry::send(function () use ($phone, $text) {
                return Http::asForm()
                    ->acceptJson()
                    ->withHeaders([
                        'apikey'        => $this->apiKey(),
                        'Cache-Control' => 'no-cache',
                    ])
                    ->connectTimeout(10)
                    ->timeout(30)
                    ->post(self::SESSION_ENDPOINT, [
                        'channel'     => 'whatsapp',
                        'source'      => $this->source(),
                        'destination' => $phone,
                        'src.name'    => $this->appName(),
                        'message'     => json_encode([
                            'type' => 'text',
                            'text' => $text,
                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ]);
            }, 3, 2000, 'gupshup.session', 20);

            $response = $attempt['response'];

            if ($response === null) {
                throw $attempt['exception'] ?? new ConnectionException('Gupshup could not be reached.');
            }

            $data = $response->json();

            if (!is_array($data)) {
                $data = ['raw' => $response->body()];
            }

            $status = strtolower(trim((string) ($data['status'] ?? '')));

            $rejected = !$response->successful()
                || ($status !== '' && !in_array($status, self::ACCEPTED, true));

            if ($rejected) {
                $reason = $data['message'] ?? $data['reason'] ?? $data['error']
                    ?? 'Gupshup rejected the reply.';

                Log::warning('WhatsApp reply rejected', [
                    'destination' => $phone,
                    'http_status' => $response->status(),
                    'response'    => $data,
                ]);

                return $this->log($phone, '', 'failed', $meta, [
                    'error'   => is_string($reason) ? $reason : json_encode($reason),
                    'payload' => $data,
                    'body'    => $text,
                ]);
            }

            return $this->log($phone, '', 'submitted', $meta, [
                'message_id' => $data['messageId'] ?? $data['message_id'] ?? null,
                'payload'    => $data,
                'body'       => $text,
            ]);

        } catch (ConnectionException $exception) {
            Log::error('WhatsApp reply connection error', [
                'destination' => $phone,
                'error'       => $exception->getMessage(),
            ]);

            return $this->logFailure(
                $phone,
                '',
                'Could not reach Gupshup: ' . $this->shortMessage($exception),
                $meta,
                $text
            );

        } catch (\Throwable $exception) {
            Log::error('WhatsApp reply exception', [
                'destination' => $phone,
                'error'       => $exception->getMessage(),
                'class'       => get_class($exception),
            ]);

            return $this->logFailure(
                $phone,
                '',
                class_basename($exception) . ': ' . $this->shortMessage($exception),
                $meta,
                $text
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    protected function logFailure(
        string $phone,
        string $templateId,
        string $error,
        array $meta,
        ?string $body = null
    ): WhatsappMessage {
        $extra = ['error' => $error];

        // Keep the text the admin typed, so a failed reply can be seen and
        // retried instead of vanishing.
        if ($body !== null) {
            $extra['body'] = $body;
        }

        return $this->log($phone, $templateId, 'failed', $meta, $extra);
    }

    /**
     * The admin doing the sending, or null.
     *
     * Guarded: the 'admin' guard is session-based, so resolving it outside a
     * web request (an artisan command, a queued job) throws. Sends must work
     * from the console too.
     */
    protected function currentAdminId(): ?int
    {
        try {
            return optional(auth()->guard('admin')->user())->id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function log(
        string $phone,
        string $templateId,
        string $status,
        array $meta,
        array $extra = []
    ): WhatsappMessage {
        return WhatsappMessage::create(array_merge([
            'direction'    => 'outbound',
            'phone'        => $phone !== '' ? $phone : '-',
            'template_id'  => $templateId,
            'status'       => $status,
            'listing_id'   => $meta['listing_id'] ?? null,
            'enquiry_id'   => $meta['enquiry_id'] ?? null,
            'admin_id'     => $meta['admin_id'] ?? $this->currentAdminId(),
            'contact_name' => $meta['contact_name'] ?? null,
        ], $extra));
    }
}
