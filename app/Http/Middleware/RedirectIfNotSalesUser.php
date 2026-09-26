<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotSalesUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('sales')->check()) {
            return redirect()->route('sales_genio.login');
        }

        if (Auth::guard('sales')->user()->status != 1) {
            Auth::guard('sales')->logout();
            return redirect()->route('sales_genio.login')
                ->withErrors(['empcode' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
