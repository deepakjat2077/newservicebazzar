<?php

namespace App\Support\Tracking;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TrafficFilter
{
    public static function shouldIgnoreRequest(Request $request): bool
    {
        return auth()->guard('admin')->check()
            || $request->is('admin', 'admin/*')
            || static::isExcludedIp($request->ip())
            || static::isBotUserAgent($request->userAgent());
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
