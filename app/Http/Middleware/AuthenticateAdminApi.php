<?php

namespace App\Http\Middleware;

use App\Models\AdminApiToken;
use Closure;
use Illuminate\Http\Request;

/**
 * Bearer-token authentication for the external WhatsApp panel.
 *
 * The panel runs on a different domain, so the session-based `admin` guard is
 * unavailable to it. This resolves the token instead and then *sets the admin
 * guard's user* for the rest of the request — which is the whole trick: every
 * existing call to auth()->guard('admin')->user() (WhatsappController's
 * admin_id stamping, Admin::hasPermission(), the AdminPermission middleware)
 * keeps working unchanged, whether the caller arrived by cookie or by token.
 *
 * Unlike App\Http\Middleware\AdminPermission this never redirects — an API
 * client wants a 401, not the HTML login page.
 */
class AuthenticateAdminApi
{
    public function handle(Request $request, Closure $next)
    {
        $token = AdminApiToken::resolve($request->bearerToken());

        if (!$token || !$token->admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // A token outlives a session by design (30 days), so an admin disabled
        // in the meantime must not keep working. The web panel does not check
        // this today; for a long-lived credential it is worth the extra gate.
        if ((int) $token->admin->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This account is disabled.',
            ], 403);
        }

        auth()->guard('admin')->setUser($token->admin);

        // Handy for "last active" display and for revoking a token that has
        // clearly been abandoned. Written without touching updated_at so the
        // row's real modification time stays meaningful.
        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        $request->attributes->set('admin_api_token', $token);

        return $next($request);
    }
}
