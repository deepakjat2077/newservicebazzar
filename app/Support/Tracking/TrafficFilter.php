<?php

namespace App\Support\Tracking;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TrafficFilter
{
    /**
     * Whether this beacon should be dropped without writing anything.
     *
     * Note this only governs the analytics pipeline — the page itself is
     * served normally either way. A crawler is welcome to read the site;
     * it just does not get a row in the reporting tables for doing so.
     */
    public static function shouldIgnoreRequest(Request $request): bool
    {
        return auth()->guard('admin')->check()
            || $request->is('admin', 'admin/*')
            || static::isBotRequest($request);
    }

    /**
     * The request-time half of bot detection: everything decidable from
     * the request alone, with no DB work.
     *
     * The other half is behavioural and cannot be known yet — a session
     * that turns out to have nobody behind it is caught later by
     * App\Console\Commands\MarkBotTraffic.
     */
    public static function isBotRequest(Request $request): bool
    {
        return static::isBotUserAgent($request->userAgent())
            || static::isExcludedIp($request->ip())
            || static::hasBotHeaderProfile($request->header('Accept-Language'));
    }

    /**
     * Same decision as isBotRequest(), made inside a queued job from the
     * payload the controller captured — the job has no Request to read.
     */
    public static function isBotPayload(array $data): bool
    {
        return static::isBotUserAgent($data['user_agent'] ?? null)
            || static::isExcludedIp($data['ip_address'] ?? null)
            || static::hasBotHeaderProfile($data['accept_language'] ?? null);
    }

    /**
     * Real browsers send Accept-Language on every document and beacon
     * request. Scripted clients — including the headless-Chrome pools that
     * otherwise present a perfectly ordinary desktop UA — routinely omit
     * it, and it costs nothing to check.
     */
    public static function hasBotHeaderProfile(?string $acceptLanguage): bool
    {
        if (!config('analytics.require_accept_language', true)) {
            return false;
        }

        return trim((string) $acceptLanguage) === '';
    }

    public static function isBotUserAgent(?string $userAgent): bool
    {
        $userAgent = (string) $userAgent;

        if ($userAgent === '') {
            return true;
        }

        foreach (config('analytics.bot_user_agent_patterns', []) as $pattern) {
            if ($pattern !== '' && stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function isExcludedIp(?string $ip): bool
    {
        $ip = trim((string) $ip);

        if ($ip === '') {
            return false;
        }

        foreach (config('analytics.excluded_ips', []) as $rule) {
            if ($rule === $ip || static::ipInCidr($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Narrows a reporting query to real traffic.
     *
     * `is_bot = false` is enough on its own now that PendingTraffic holds
     * an unknown session's beacons out of the database until it proves
     * human — a stored row is one something with a person behind it
     * produced. The flag still matters for rows written before that (and
     * for anything MarkBotTraffic catches on the way past), so the
     * predicate stays.
     */
    public static function applyCleanScope(Builder $query, string $table, bool $hasIp = false): Builder
    {
        $query->where(function ($q) use ($table) {
            $q->whereNull($table . '.is_bot')->orWhere($table . '.is_bot', false);
        });

        $excludedIps = config('analytics.excluded_ips', []);

        if ($hasIp && $excludedIps) {
            $query->whereNotIn($table . '.ip_address', $excludedIps);
        }

        return $query;
    }

    protected static function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return false;
        }

        [$subnet, $bits] = explode('/', $cidr, 2);

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            || !filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $bits = (int) $bits;

        if ($bits < 0 || $bits > 32) {
            return false;
        }

        $mask = -1 << (32 - $bits);

        return (ip2long($ip) & $mask) === (ip2long($subnet) & $mask);
    }
}
