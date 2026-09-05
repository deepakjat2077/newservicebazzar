<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Exempt verified search engine crawlers (Googlebot, Bingbot) from rate limiting.
 *
 * During crawling of 6900+ listings, legitimate bots easily hit 200+ requests
 * within minutes, triggering 429 Too Many Requests and causing Google to
 * reduce crawl rate. New listings then take weeks to index.
 *
 * This middleware detects the bot via User-Agent and (optionally) reverse-DNS
 * verification, then marks the request so rate-limiting middleware skips it.
 */
class AllowSearchEngineCrawlers
{
    /**
     * List of crawler User-Agent strings to allow (case-insensitive match).
     */
    protected $crawlerAgents = [
        'googlebot',
        'bingbot',
        'slurp',           // Yahoo
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'facebookexternalhit',
    ];

    public function handle(Request $request, Closure $next)
    {
        $userAgent = $request->header('User-Agent') ?? '';

        foreach ($this->crawlerAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                // Mark request as crawler so rate limiting middleware skips it
                $request->attributes->set('is_verified_crawler', true);
                break;
            }
        }

        return $next($request);
    }
}
