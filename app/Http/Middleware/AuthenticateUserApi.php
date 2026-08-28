<?php

namespace App\Http\Middleware;

use App\Models\UserApiToken;
use Closure;
use Illuminate\Http\Request;

/**
 * Bearer-token authentication for the mobile app.
 *
 * A native app holds no session cookie, so the `web` guard is unavailable to
 * it. This resolves the token instead and then *sets the web guard's user*
 * for the rest of the request — so every existing auth()->user() call keeps
 * working unchanged, whether the caller arrived by cookie or by token.
 *
 * Never redirects — an API client wants a 401, not the HTML login page. Twin
 * of App\Http\Middleware\AuthenticateAdminApi.
 */
class AuthenticateUserApi
{
    public function handle(Request $request, Closure $next)
    {
        $token = UserApiToken::resolve($request->bearerToken());

        if (!$token || !$token->user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        auth()->guard('web')->setUser($token->user);

        // Handy for "last active" and for pruning abandoned tokens. Written
        // without touching updated_at so the row's real modification time
        // stays meaningful.
        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        $request->attributes->set('user_api_token', $token);

        return $next($request);
    }
}
