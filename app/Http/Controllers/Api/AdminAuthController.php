<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Login for the external WhatsApp panel.
 *
 * Same credentials as the admin panel — same `admins` table, same `username`
 * field (see Admin\Auth\LoginController::username()), same password hash.
 * There is no second account system to keep in sync; an admin who can sign
 * into /admin can sign into the panel with exactly what they already type.
 *
 * The one extra rule: the panel is the WhatsApp panel, so an admin without
 * `whatsapp.view` is turned away at login rather than being let in to a
 * screen where every request would 403.
 */
class AdminAuthController extends Controller
{
    /**
     * Permissions a token can carry. Kept narrow on purpose: this credential
     * exists to run the WhatsApp panel, so it cannot be used to reach the
     * rest of the admin surface even though it belongs to a full admin.
     */
    public const ABILITIES = ['whatsapp.view', 'whatsapp.send', 'whatsapp.delete'];

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:191',
            'password' => 'required|string|max:191',
        ]);

        /*
         * The web login sits behind throttle:5,1 at the route level. This one
         * is throttled per username+IP here instead, so a wrong password on
         * one account cannot lock out everyone sharing an office IP — which
         * matters more than usual while the app is behind Cloudflare and
         * every visitor can look like the same address.
         */
        $key = 'admin-api-login:' . strtolower($request->username) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Try again in '
                    . RateLimiter::availableIn($key) . ' seconds.',
            ], 429);
        }

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($key, 60);

            // One message for both cases: saying "no such user" would let
            // anyone enumerate admin usernames.
            return response()->json([
                'success' => false,
                'message' => 'These credentials do not match our records.',
            ], 422);
        }

        if ((int) $admin->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This account is disabled.',
            ], 403);
        }

        // hasPermission() reads the role relation, so the guard user has to be
        // set before asking — and a super admin passes everything by design.
        Auth::guard('admin')->setUser($admin);

        if (!$admin->hasPermission('whatsapp.view')) {
            return response()->json([
                'success' => false,
                'message' => 'This account does not have WhatsApp access.',
            ], 403);
        }

        RateLimiter::clear($key);

        $abilities = array_values(array_filter(
            self::ABILITIES,
            fn ($ability) => $admin->hasPermission($ability)
        ));

        [$token, $plain] = AdminApiToken::issue(
            $admin,
            $abilities,
            $request->input('device_name') ?: 'whatsapp-panel'
        );

        return response()->json([
            'success' => true,
            'token'   => $plain,
            'expires_at' => $token->expires_at->toIso8601String(),
            'admin'   => $this->adminPayload($admin, $abilities),
        ]);
    }

    /** Who am I — used by the panel to restore a session on reload. */
    public function me(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $token = $request->attributes->get('admin_api_token');

        return response()->json([
            'success' => true,
            'admin'   => $this->adminPayload($admin, (array) $token?->abilities),
            'expires_at' => $token?->expires_at?->toIso8601String(),
        ]);
    }

    /** Drop this one token. Other devices stay signed in. */
    public function logout(Request $request)
    {
        $request->attributes->get('admin_api_token')?->delete();

        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    protected function adminPayload($admin, array $abilities): array
    {
        return [
            'id'        => $admin->id,
            'name'      => trim($admin->first_name . ' ' . $admin->last_name) ?: $admin->username,
            'username'  => $admin->username,
            'email'     => $admin->email,
            'image'     => $admin->image ? asset($admin->image) : null,
            // The panel hides the send box / delete buttons off these rather
            // than letting the request go out and bounce with a 403.
            'abilities' => $abilities,
        ];
    }
}
