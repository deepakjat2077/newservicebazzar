<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Rate limit normal traffic but let verified crawlers through uncapped.
 *
 * A request is flagged is_verified_crawler by AllowSearchEngineCrawlers only
 * after a reverse-DNS check, so this exemption can't be reached by spoofing a
 * User-Agent.
 *
 * The exemption is a hard short-circuit in handle(): the previous version
 * tried to skip throttling by returning a null signature from
 * resolveRequestSignature(), but Laravel builds the throttle key as
 * $prefix . $signature and still counts the hit — a null signature just made
 * every crawler share one empty-key bucket, i.e. throttled them together
 * instead of exempting them. Bypassing handle() entirely is the only correct
 * way to actually skip the limiter.
 */
class ThrottleRequestsForHumans extends ThrottleRequests
{
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if ($request->attributes->get('is_verified_crawler')) {
            return $next($request);
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }
}
