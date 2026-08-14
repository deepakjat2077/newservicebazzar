<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops anything from serving a stale copy of an admin page.
 *
 * Nothing in this application ever sent cache headers for the admin panel, so
 * the browser and the host's page cache were both free to keep their own copy
 * of a form. That is wrong for every admin page — they are per-admin, they
 * change constantly, and a listing form reopened from cache still holds the
 * previous listing's data and the previous session's CSRF token.
 *
 * It also made changes to the panel invisible: a fix shipped to the listing
 * form kept losing to a copy the browser had already stored, and the only way
 * to see it was a manual hard refresh.
 */
class NoStoreAdminPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Registered globally rather than on the admin route groups, so a
        // route added to the panel later cannot be left out by accident.
        if (!$request->is('admin', 'admin/*')) {
            return $response;
        }

        // no-store rather than no-cache: no-cache still permits a stored copy
        // that gets revalidated, and the host's cache is not reliably asked.
        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // LiteSpeed's own page cache reads this, and honours it regardless of
        // whether the standard headers above reached it intact.
        $response->headers->set('X-LiteSpeed-Cache-Control', 'no-cache');

        return $response;
    }
}
