<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Mark genuine search-engine crawlers so the rate limiter can exempt them.
 *
 * Sweeping 30k+ URLs, Googlebot easily clears the per-IP throttle within a
 * minute; a 429 then makes Google cut its crawl rate and new/updated listings
 * take weeks to index (audit finding #11). Verified crawlers are let through
 * uncapped instead.
 *
 * "Verified" is the operative word. A User-Agent is trivially spoofed, and
 * exempting anything that merely *claims* to be Googlebot would hand every
 * scraper and flooder a free pass around the throttle onto the most expensive
 * (uncached, ~27-query) pages — the exact overload the throttle exists to
 * stop. So a UA match only triggers a reverse-DNS check against the bot's
 * published host suffix, forward-confirmed back to the same IP — the method
 * Google and Bing document for verifying their crawlers.
 *
 * nginx rewrites REMOTE_ADDR from CF-Connecting-IP (see the site's nginx
 * conf), so $request->ip() is the crawler's real IP here, not Cloudflare's.
 *
 * The verdict per IP is cached, so the DNS round-trips happen once per crawler
 * IP, not once per request, and only ever for a request whose UA already looks
 * like a bot — ordinary human traffic never triggers a lookup.
 */
class AllowSearchEngineCrawlers
{
    /** Verified-crawler cache TTL (positive result). */
    private const VERIFIED_TTL_HOURS = 168; // 7 days

    /** Cache TTL for a UA-claimed bot that FAILED verification (a spoofer). */
    private const UNVERIFIED_TTL_HOURS = 6;

    /**
     * UA token => the reverse-DNS host suffixes that prove it genuine.
     *
     * Only crawlers with a stable, documented rDNS scheme are listed; each is
     * forward-confirmed, so a spoofed UA from any other host is never exempt.
     * DuckDuckBot and facebookexternalhit are intentionally absent — they
     * don't publish verifiable rDNS and matter little for crawl budget, so
     * they stay subject to the normal limit.
     */
    protected array $crawlers = [
        'googlebot'   => ['.googlebot.com', '.google.com'],
        'bingbot'     => ['.search.msn.com'],
        'slurp'       => ['.crawl.yahoo.net'],
        'yandexbot'   => ['.yandex.com', '.yandex.ru', '.yandex.net'],
        'baiduspider' => ['.crawl.baidu.com', '.baidu.com', '.baidu.jp'],
    ];

    public function handle(Request $request, Closure $next)
    {
        $userAgent = strtolower($request->header('User-Agent') ?? '');

        $suffixes = null;
        foreach ($this->crawlers as $token => $hostSuffixes) {
            if (str_contains($userAgent, $token)) {
                $suffixes = $hostSuffixes;
                break;
            }
        }

        // UA doesn't claim to be a verifiable bot -> ordinary visitor, no lookup.
        if ($suffixes !== null && $this->isVerifiedCrawler($request->ip(), $suffixes)) {
            $request->attributes->set('is_verified_crawler', true);
        }

        return $next($request);
    }

    /**
     * Reverse-DNS + forward-confirm the IP against the bot's host suffixes,
     * cached per IP so the DNS work happens at most once per TTL.
     */
    private function isVerifiedCrawler(?string $ip, array $suffixes): bool
    {
        if (empty($ip)) {
            return false;
        }

        $cacheKey = 'crawler_verify:' . $ip;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (bool) $cached;
        }

        $verified = $this->reverseDnsMatches($ip, $suffixes);

        Cache::put(
            $cacheKey,
            $verified,
            now()->addHours($verified ? self::VERIFIED_TTL_HOURS : self::UNVERIFIED_TTL_HOURS)
        );

        return $verified;
    }

    private function reverseDnsMatches(string $ip, array $suffixes): bool
    {
        $host = @gethostbyaddr($ip);
        if ($host === false || $host === $ip) {
            return false;
        }

        $host = rtrim(strtolower($host), '.');

        $suffixMatch = false;
        foreach ($suffixes as $suffix) {
            if (str_ends_with($host, $suffix)) {
                $suffixMatch = true;
                break;
            }
        }
        if (!$suffixMatch) {
            return false;
        }

        // Forward-confirm: the PTR host must resolve back to the same IP.
        // dns_get_record covers both A (IPv4) and AAAA (IPv6) crawler IPs;
        // gethostbyname alone would miss Googlebot's IPv6 ranges.
        $records = @dns_get_record($host, DNS_A + DNS_AAAA) ?: [];
        foreach ($records as $record) {
            if (($record['ip'] ?? null) === $ip || ($record['ipv6'] ?? null) === $ip) {
                return true;
            }
        }

        return false;
    }
}
