<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds baseline security response headers to every web response. These are
 * additive/non-breaking — none of them change existing page behavior, they
 * only instruct the browser to apply extra protections.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // `headers` is a public property (Symfony HeaderBag) on every
        // Laravel/Symfony response type (Response, JsonResponse,
        // RedirectResponse, StreamedResponse) — not a method.
        if (isset($response->headers)) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

            // Tell browsers to stick to HTTPS for a year. The site already
            // 301s HTTP->HTTPS and sits behind Cloudflare TLS, so this only
            // enforces what already happens. No includeSubDomains — we don't
            // vouch for every possible subdomain.
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');

            // Allow only the powerful features the site actually uses (own
            // origin): geolocation for city detection, microphone for voice
            // search, camera for photo upload, payment for deposits. Opt out
            // of ad-tracking APIs. Anything omitted keeps its browser default.
            $response->headers->set(
                'Permissions-Policy',
                'geolocation=(self), microphone=(self), camera=(self), payment=(self), fullscreen=(self), browsing-topics=()'
            );
        }

        return $response;
    }
}
