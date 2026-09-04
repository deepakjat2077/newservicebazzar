<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Keeps crawlers out of the session store.
 *
 * Every request that reaches Laravel gets a session, and this site takes
 * ~158,000 requests a day, most of them crawlers walking the city x category
 * URL space. Each one minted a session key that sat in Redis for
 * SESSION_LIFETIME (24 hours), which is how db0 reached 198,069 keys and 2 GB.
 *
 * Redis runs `allkeys-lru`, so once it filled it started evicting — 146,867
 * keys in one day. LRU does not know an admin's session from a crawler's, so
 * an admin who had been filling in a long listing form was signed out
 * mid-edit and the save never reached the server.
 *
 * A crawler has no use for a session that outlives its request, so this
 * switches those requests to the in-memory `array` driver: csrf_token() and
 * anything else that reads the session still work for the request, nothing is
 * written to Redis, and nothing is there to evict a real session.
 *
 * The list is deliberately conservative — only agents that name themselves as
 * bots. A real browser misread as a crawler would be unable to sign in, so
 * matching broadly would be the more expensive mistake.
 */
class NoSessionForCrawlers
{
    /**
     * Substrings that only appear in self-declared crawler user agents.
     * Matched case-insensitively against the whole UA string.
     */
    protected const CRAWLER_AGENTS = [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
        'yandexbot', 'sogou', 'exabot', 'ia_archiver', 'applebot',
        'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'petalbot',
        'bytespider', 'gptbot', 'ccbot', 'claudebot', 'perplexitybot',
        'amazonbot', 'facebookexternalhit', 'twitterbot', 'linkedinbot',
        'telegrambot', 'whatsapp', 'discordbot', 'pinterestbot',
        'screaming frog', 'seznambot', 'dataforseobot', 'barkrowler',
        'crawler', 'spider', 'scrapy', 'python-requests', 'go-http-client',
        'curl/', 'wget/', 'headlesschrome',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($this->isCrawler($request)) {
            // Nothing has read the session yet — StartSession runs after this
            // middleware — so swapping the driver here costs the request
            // nothing and leaves Redis untouched.
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }

    protected function isCrawler(Request $request): bool
    {
        // A signed-in person is never a crawler, whatever the agent says.
        if ($request->hasCookie(config('session.cookie'))) {
            return false;
        }

        $agent = strtolower((string) $request->userAgent());

        if ($agent === '') {
            return true;
        }

        foreach (self::CRAWLER_AGENTS as $needle) {
            if (str_contains($agent, $needle)) {
                return true;
            }
        }

        return false;
    }
}
