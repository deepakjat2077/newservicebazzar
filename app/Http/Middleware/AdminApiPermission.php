<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Route-level permission gate for the API, mirroring App\Http\Middleware\
 * AdminPermission but answering in JSON instead of redirecting/aborting with
 * an HTML page.
 *
 * Usage: ->middleware('admin.api.can:whatsapp.send')
 *        ->middleware('admin.api.can:whatsapp.view,whatsapp.send')  // any of
 *
 * Two gates have to agree, not one. The admin's role must grant the
 * permission *and* the token must carry it as an ability — a token minted
 * for a read-only panel stays read-only even if that admin is later promoted,
 * until they log in again and get a fresh one.
 */
class AdminApiPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $token = $request->attributes->get('admin_api_token');

        foreach ($permissions as $permission) {
            if ($admin->hasPermission($permission) && (!$token || $token->can($permission))) {
                return $next($request);
            }
        }

        return response()->json([
            'success'  => false,
            'message'  => 'You do not have permission to perform this action.',
            'required' => $permissions,
        ], 403);
    }
}
