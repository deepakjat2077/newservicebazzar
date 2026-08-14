<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Route-level permission gate for the admin panel.
 *
 * Usage: ->middleware('permission:listing.edit')
 *        ->middleware('permission:category.manage,subcategory.manage')  // any of
 *
 * Always pair with the `admin` middleware — this one only decides whether an
 * already-authenticated admin is allowed through, not whether they are
 * logged in at all.
 */
class AdminPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        foreach ($permissions as $permission) {
            if ($admin->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
