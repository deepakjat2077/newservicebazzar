<?php

namespace App\Support;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Google Indexing API client (service account servicebazzar-indexing@...,
 * added as Owner of the Search Console property). The key file lives outside
 * the project, root-only: /etc/servicebazzar/google-indexing.json.
 *
 * Note: Google documents this API for JobPosting / BroadcastEvent pages only.
 * It is used here at the owner's request for listings and blog posts; Google
 * may ignore those notifications. Default quota: 200 publish calls a day.
 */
class GoogleIndexing
{
    public const KEY_FILE = '/etc/servicebazzar/google-indexing.json';
    private const SCOPE = 'https://www.googleapis.com/auth/indexing';
    private const PUBLISH_URL = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    private const INSPECT_SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';
    private const INSPECT_URL = 'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect';
    // The Search Console property the service account owns.
    public const SITE = 'https://www.servicebazzar.com/';
    // Cache keys the admin Google Indexing page reads.
    public const LAST_RUN_KEY = 'google-index:last-run';
    public const INSPECT_ERROR_KEY = 'google-index:inspect-error';

    public static function configured(): bool
    {
        return is_readable(self::KEY_FILE);
    }

    private static function token(string $scope = self::SCOPE): string
    {
        $cacheKey = $scope === self::SCOPE ? 'google-indexing:token' : 'google-indexing:token:' . md5($scope);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($scope) {
            $key = json_decode((string) file_get_contents(self::KEY_FILE), true);
            $now = time();
            $jwt = JWT::encode([
                'iss'   => $key['client_email'],
                'scope' => $scope,
                'aud'   => $key['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $key['private_key'], 'RS256');

            $res = Http::asForm()->timeout(20)->post($key['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            $token = $res->json('access_token');
            if (!$token) {
                throw new \RuntimeException('Google token request failed: HTTP ' . $res->status() . ' ' . substr($res->body(), 0, 200));
            }

            return $token;
        });
    }

    /**
     * Tell Google a URL was added/changed (URL_UPDATED) or removed (URL_DELETED).
     * Returns [httpStatus, errorMessageOrNull].
     */
    public static function publish(string $url, string $type = 'URL_UPDATED'): array
    {
        $res = Http::withToken(self::token())->timeout(20)->post(self::PUBLISH_URL, [
            'url'  => $url,
            'type' => $type,
        ]);

        if ($res->status() === 401) {
            Cache::forget('google-indexing:token');
        }

        return [$res->status(), $res->successful() ? null : mb_substr((string) ($res->json('error.message') ?? $res->body()), 0, 450)];
    }

    /**
     * Ask Search Console's URL Inspection API whether Google has indexed $url
     * (needs the "Google Search Console API" enabled in the key's Cloud project;
     * quota 2,000 a day). Returns [httpStatus, indexStatusResult|null, errorOrNull].
     */
    public static function inspect(string $url): array
    {
        $res = Http::withToken(self::token(self::INSPECT_SCOPE))->timeout(30)->post(self::INSPECT_URL, [
            'inspectionUrl' => $url,
            'siteUrl'       => self::SITE,
        ]);

        if ($res->status() === 401) {
            Cache::forget('google-indexing:token:' . md5(self::INSPECT_SCOPE));
        }

        if (!$res->successful()) {
            return [$res->status(), null, mb_substr((string) ($res->json('error.message') ?? $res->body()), 0, 450)];
        }

        return [$res->status(), (array) $res->json('inspectionResult.indexStatusResult'), null];
    }

    /**
     * Inspect one google_index_submissions row and store Google's answer on it.
     * A failed call leaves the last good answer alone. Returns [httpStatus, errorOrNull].
     */
    public static function inspectAndStore(int $id, string $url): array
    {
        try {
            [$status, $result, $error] = self::inspect($url);
        } catch (\Throwable $e) {
            [$status, $result, $error] = [0, null, mb_substr($e->getMessage(), 0, 450)];
        }

        if ($status === 200) {
            DB::table('google_index_submissions')->where('id', $id)->update([
                'inspected_at'   => now(),
                'index_verdict'  => mb_substr((string) ($result['verdict'] ?? 'UNKNOWN'), 0, 20),
                'coverage_state' => isset($result['coverageState']) ? mb_substr((string) $result['coverageState'], 0, 150) : null,
                'last_crawl_at'  => !empty($result['lastCrawlTime']) ? Carbon::parse($result['lastCrawlTime'])->setTimezone(config('app.timezone')) : null,
                'updated_at'     => now(),
            ]);
            Cache::forget(self::INSPECT_ERROR_KEY);
        } else {
            Cache::put(self::INSPECT_ERROR_KEY, ['at' => now()->toDateTimeString(), 'status' => $status, 'error' => $error], now()->addDays(2));
        }

        return [$status, $error];
    }

    /**
     * Run a Search Console Search Analytics query (same read-only scope as the
     * URL Inspection API — needs "Google Search Console API" enabled and the
     * service account added to the property). Returns the raw `rows` array.
     * Pass $startRow to page through results (25,000 max per request).
     *
     * @return array<int, array{keys: array<int,string>, clicks: int, impressions: int, ctr: float, position: float}>
     */
    public static function searchAnalytics(string $start, string $end, array $dimensions, int $rowLimit = 1000, int $startRow = 0, array $orderBy = []): array
    {
        $body = [
            'startDate'  => $start,
            'endDate'    => $end,
            'dimensions' => $dimensions,
            'rowLimit'   => min($rowLimit, 25000),
            'startRow'   => $startRow,
        ];
        if ($orderBy) {
            $body['orderBy'] = $orderBy;
        }

        $res = Http::withToken(self::token(self::INSPECT_SCOPE))->timeout(40)->post(
            'https://searchconsole.googleapis.com/webmasters/v3/sites/' . rawurlencode(self::SITE) . '/searchAnalytics/query',
            $body
        );

        if ($res->status() === 401) {
            Cache::forget('google-indexing:token:' . md5(self::INSPECT_SCOPE));
        }

        if (!$res->successful()) {
            throw new \RuntimeException('GSC searchAnalytics HTTP ' . $res->status() . ': ' . mb_substr((string) ($res->json('error.message') ?? $res->body()), 0, 300));
        }

        return (array) ($res->json('rows') ?? []);
    }
}
