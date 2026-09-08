<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RemoveIndexPhp
{
    public function handle(Request $request, Closure $next)
    {
        // If request path is /index.php, redirect to /
        if ($request->getPathInfo() === '/index.php') {
            return redirect('/', 301);
        }

        return $next($request);
    }
}
