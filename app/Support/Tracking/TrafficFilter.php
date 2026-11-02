<?php

namespace App\Support\Tracking;

use App\Models\ExcludedIp;
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

    /**
     * The addresses whose traffic is not analytics: the office wifi, a staff
     * phone on it. ANALYTICS_EXCLUDED_IPS still works, and the admin list
     * (Tracking → Excluded IPs) is added to it so a changed connection does
     * not need a file edited on both servers.
     *
     * @return string[]
     */
    public static function excludedIps(): array
    {
        return array_values(array_unique(array_merge(
            (array) config('analytics.excluded_ips', []),
            ExcludedIp::values()
        )));
    }

    public static function isExcludedIp(?string $ip): bool
    {
        $ip = trim((string) $ip);

        if ($ip === '') {
            return false;
        }

        foreach (static::excludedIps() as $rule) {
            if (static::ipMatches($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    /** One address against one rule: an address, or a range like …::/64. */
    public static function ipMatches(string $ip, string $rule): bool
    {
        $rule = trim($rule);

        if ($rule === '') {
            return false;
        }

        if (!str_contains($rule, '/')) {
            // Through inet_pton, so the same IPv6 address written two ways
            // still counts as the same address.
            $a = @inet_pton($ip);
            $b = @inet_pton($rule);

            return $a !== false && $b !== false ? $a === $b : $rule === $ip;
        }

        return static::ipInCidr($ip, $rule);
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

        // Only the plain addresses. A range cannot be expressed in SQL here,
        // and does not need to be: adding one flags the traffic already
        // recorded from it as bot, and nothing new from it is ever stored.
        $excludedIps = array_values(array_filter(
            static::excludedIps(),
            fn ($rule) => !str_contains((string) $rule, '/')
        ));

        if ($hasIp && $excludedIps) {
            $query->whereNotIn($table . '.ip_address', $excludedIps);
        }

        return $query;
    }

    /**
     * Compared as raw bytes, so one routine covers both families. IPv6
     * matters here: a home or office connection hands every device its own
     * IPv6 address out of one shared /64, so excluding a single address would
     * miss every other phone on the same wifi.
     */
    protected static function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return false;
        }

        [$subnet, $bits] = explode('/', $cidr, 2);

        $ipBytes = @inet_pton(trim($ip));
        $subnetBytes = @inet_pton(trim($subnet));

        if ($ipBytes === false || $subnetBytes === false || strlen($ipBytes) !== strlen($subnetBytes)) {
            return false;
        }

        $bits = (int) $bits;

        if ($bits < 0 || $bits > strlen($ipBytes) * 8) {
            return false;
        }

        $whole = intdiv($bits, 8);
        $spare = $bits % 8;

        if ($whole > 0 && strncmp($ipBytes, $subnetBytes, $whole) !== 0) {
            return false;
        }

        if ($spare === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $spare)) & 0xFF);

        return ($ipBytes[$whole] & $mask) === ($subnetBytes[$whole] & $mask);
    }
}
