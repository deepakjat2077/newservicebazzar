<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveIndexPhp
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        // If request is /index.php, return 301 to / using raw response
        if ($path === '/index.php') {
            return response('', 301)
                ->header('Location', $request->getSchemeAndHttpHost() . '/');
        }

        return $next($request);
    }
}
