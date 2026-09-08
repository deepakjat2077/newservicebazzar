<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Redirect mixed-/upper-case city & category lander URLs to lowercase (301).
 *
 * The city/category routes resolve their segments case-insensitively, so
 * /Delhi/home-renovation, /DELHI/home-renovation and /delhi/Home-Renovation
 * all render 200 — and each canonicalises to its own casing, so Google sees
 * the same page several times over. (The uppercase variants leak in through
 * links hand-written into category descriptions.) One lowercase 301 collapses
 * them onto the single canonical URL.
 *
 * Scope is deliberately tight. It only fires when the first path segment,
 * lowercased, is a real city or category slug (validRouteFirstSegments() — the
 * same cached set the router trusts). So a lander like /Delhi/... is
 * redirected, while a case-sensitive path that merely happens to carry an
 * uppercase letter — a username at /{slug} (matched against a case-sensitive
 * username column, and noindex anyway), /admin/..., etc. — is left untouched
 * because its first segment isn't a lander slug.
 *
 * Runs early in the web stack (prepended, alongside RedirectTrailingSlash) so
 * the redirect happens before the page is rendered or cached. Query strings
 * are preserved; only the path is lowercased.
 */
class ForceLowercaseLanderUrl
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET')) {
            $path = $request->path(); // no leading slash, no query string

            if ($path !== '/' && preg_match('/[A-Z]/', $path)) {
                $firstSegment = strtolower(explode('/', $path, 2)[0]);

                if (isset(validRouteFirstSegments()[$firstSegment])) {
                    $url = '/' . strtolower($path);
                    if ($qs = $request->getQueryString()) {
                        $url .= '?' . $qs;
                    }

                    return redirect($url, 301);
                }
            }
        }

        return $next($request);
    }
}
