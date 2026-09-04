<?php

namespace App\Http\Middleware;

use App\Models\UserApiToken;
use Closure;
use Illuminate\Http\Request;

/**
 * Bearer-token authentication that never rejects.
 *
 * The home screen, category grid, search and business detail are public — the
 * app shows them before anyone signs in. But a signed-in caller should still
 * get the personalised bits (is_favorited, liked_by_me, can_delete), so the
 * token is resolved when one is sent and simply ignored when it is not.
 *
 * Same work as App\Http\Middleware\AuthenticateUserApi, minus the 401.
 */
class AuthenticateUserApiOptional
{
    public function handle(Request $request, Closure $next)
    {
        $token = UserApiToken::resolve($request->bearerToken());

        if ($token && $token->user) {
            auth()->guard('web')->setUser($token->user);

            $token->forceFill(['last_used_at' => now()])->saveQuietly();

            $request->attributes->set('user_api_token', $token);
        }

        return $next($request);
    }
}
