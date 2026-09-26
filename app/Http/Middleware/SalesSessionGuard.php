<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Keeps a Sales Genio sign-in to the browser that made it.
 *
 * The site's own session lasts 24 hours and rolls forward on every request, so
 * a rep who opened the app each day was never asked to sign in again. This ends
 * their sign-in when the browser is closed, and when they have been idle.
 */
class SalesSessionGuard
{
    /** Signed out after this long with no activity. */
    public const IDLE_MINUTES = 120;

    /**
     * A cookie with no expiry, so the browser drops it on close. The matching
     * value is kept in the session; if the cookie comes back missing or
     * different, this is not the browser that signed in.
     */
    public const COOKIE = 'sg_browser';

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('sales')->check()) {
            return $next($request);
        }

        $session = $request->session();
        $marker  = $session->get('sales_browser_token');

        // Signed in before this check existed — give it a marker rather than
        // throwing the rep out mid-task.
        if (!$marker) {
            $marker = Str::random(40);
            $session->put('sales_browser_token', $marker);
            $session->put('sales_last_seen', now()->timestamp);
            Cookie::queue(Cookie::make(self::COOKIE, $marker, 0));

            return $next($request);
        }

        if (!hash_equals($marker, (string) $request->cookie(self::COOKIE))) {
            return $this->signOut($request, 'Please sign in again.');
        }

        $lastSeen = (int) $session->get('sales_last_seen', 0);

        if ($lastSeen && now()->timestamp - $lastSeen > self::IDLE_MINUTES * 60) {
            return $this->signOut($request, 'You were signed out after '
                . self::IDLE_MINUTES / 60 . ' hours of inactivity.');
        }

        $session->put('sales_last_seen', now()->timestamp);

        return $next($request);
    }

    private function signOut(Request $request, string $message)
    {
        Auth::guard('sales')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cookie::queue(Cookie::forget(self::COOKIE));

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['status' => false, 'message' => $message], 401);
        }

        return redirect()->route('sales_genio.login')->withErrors(['empcode' => $message]);
    }
}
