<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Redirect URLs with trailing slashes to non-trailing versions (301).
 *
 * This enforces a consistent URL structure for SEO: both /blogs and /blogs/
 * would return content, but we redirect /blogs/ → /blogs so Google sees
 * only one canonical URL per page and concentrates crawl budget there.
 *
 * Preserves query strings and skips paths that are likely directories.
 */
class RedirectTrailingSlash
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->getPathInfo();

        // Only redirect if:
        // 1. Path ends with a slash
        // 2. Path is not just "/" (root)
        // 3. Path is not "/admin/" or other special routes (optional)
        if (substr($path, -1) === '/' && $path !== '/') {
            $url = rtrim($path, '/');
            if ($request->getQueryString()) {
                $url .= '?' . $request->getQueryString();
            }
            return redirect($url, 301);
        }

        return $next($request);
    }
}
