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
        }

        return $response;
    }
}
