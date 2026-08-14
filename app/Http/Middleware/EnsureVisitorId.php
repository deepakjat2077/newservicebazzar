<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Ensures every visitor (guest or logged-in) carries a long-lived,
 * JS-readable first-party cookie identifying them across requests/visits.
 * Deliberately does no DB work — actual visitor/session rows are written
 * asynchronously via the JS beacon -> queued job pipeline (see
 * TrackingController), so this middleware's per-request cost is a single
 * cookie read and, at most, a cookie write.
 */
class EnsureVisitorId
{
    public const COOKIE_NAME = 'visitor_id';
    public const COOKIE_MINUTES = 60 * 24 * 365 * 5; // ~5 years

    public function handle(Request $request, Closure $next)
    {
        if (!$request->cookie(self::COOKIE_NAME)) {
            $visitorId = (string) Str::uuid();

            // Not http_only: the tracking script needs to read this
            // cookie client-side to attach the visitor_id to every beacon.
            Cookie::queue(Cookie::make(
                self::COOKIE_NAME,
                $visitorId,
                self::COOKIE_MINUTES,
                '/',
                null,
                $request->secure(),
                false,
                false,
                'lax'
            ));

            // Make it available to the rest of this same request too
            // (e.g. if a controller wants it before the response cookie
            // round-trips on the next request).
            $request->cookies->set(self::COOKIE_NAME, $visitorId);
        }

        return $next($request);
    }
}
