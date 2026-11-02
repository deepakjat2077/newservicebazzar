<?php

namespace App\Support\Tracking;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends our server-side conversions to GA4 through the Measurement Protocol,
 * so enquiries, sign ups and new listings show up in GA4 next to the
 * browser's call/WhatsApp click events (those go out from tracking.js).
 *
 * The visitor's own GA client id and session id are read from the _ga
 * cookies, so GA4 credits the conversion to the same visit and traffic
 * source. Sent after the response has gone out; failures are only logged.
 */
class GoogleAnalytics
{
    /** Our conversion name => GA4 event name (the "key events" set up in GA4). */
    public const EVENT_NAMES = [
        'enquiry'                => 'enquiry_submit',
        'quote_request'          => 'enquiry_submit',
        'registration'           => 'sign_up',
        'listing_created'        => 'listing_created',
        'contact_form_submitted' => 'contact_submit',
    ];

    public static function configured(): bool
    {
        return (bool) config('services.ga4.api_secret') && (bool) config('services.ga4.measurement_id');
    }

    public static function conversion(Request $request, string $eventType, array $extra = []): void
    {
        $name = self::EVENT_NAMES[$eventType] ?? null;
        if (!$name || !self::configured() || TrafficFilter::shouldIgnoreRequest($request)) {
            return;
        }

        $cookies = self::rawCookies($request);
        $clientId = self::clientId($cookies['_ga'] ?? null);
        if (!$clientId) {
            // No GA cookie (blocked or not yet set): GA4 would count this as a
            // brand-new user with no source, which only muddies the reports.
            return;
        }

        $measurementId = config('services.ga4.measurement_id');
        $params = array_filter([
            'session_id'           => self::sessionId($cookies['_ga_' . substr($measurementId, 2)] ?? null),
            'engagement_time_msec' => 1,
            'form'                 => $eventType,
            'listing_id'           => $extra['listing_id'] ?? null,
            'category_id'          => $extra['category_id'] ?? null,
            'page_location'        => mb_substr($request->fullUrl(), 0, 400),
        ], fn ($v) => $v !== null && $v !== '');

        $payload = ['client_id' => $clientId, 'events' => [['name' => $name, 'params' => $params]]];
        $url = 'https://www.google-analytics.com/mp/collect?' . http_build_query([
            'measurement_id' => $measurementId,
            'api_secret'     => config('services.ga4.api_secret'),
        ]);

        app()->terminating(function () use ($url, $payload, $name) {
            try {
                Http::timeout(3)->post($url, $payload);
            } catch (\Throwable $e) {
                Log::warning('GA4 measurement protocol failed', ['event' => $name, 'error' => $e->getMessage()]);
            }
        });
    }

    /**
     * Cookies straight from the header: _ga* are set by gtag in the browser,
     * not by Laravel, so the encrypted-cookie middleware hands them over as null.
     */
    private static function rawCookies(Request $request): array
    {
        $out = [];
        foreach (explode(';', (string) $request->header('cookie')) as $pair) {
            [$k, $v] = array_pad(explode('=', trim($pair), 2), 2, '');
            if (str_starts_with($k, '_ga')) {
                $out[$k] = urldecode($v);
            }
        }
        return $out;
    }

    /** "GA1.1.123456789.1700000000" => "123456789.1700000000" */
    private static function clientId(?string $ga): ?string
    {
        return $ga && preg_match('/^GA\d\.\d+\.(\d+\.\d+)$/', $ga, $m) ? $m[1] : null;
    }

    /** "GS1.1.1700000000.3.1..." or "GS2.1.s1700000000$o3$g1..." => "1700000000" */
    private static function sessionId(?string $gs): ?string
    {
        if (!$gs) {
            return null;
        }
        if (preg_match('/^GS2\.\d+\.s(\d+)/', $gs, $m) || preg_match('/^GS1\.\d+\.(\d+)\./', $gs, $m)) {
            return $m[1];
        }
        return null;
    }
}
