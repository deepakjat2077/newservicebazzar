<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guard = 'admin';
        
        if (!Auth::guard($guard)->check()) {
            return redirect()->route('admin.login');
        }

        // Daily logout (config/admin_session.php): a session that began before
        // the last cut-off ends now. A session with no start time recorded
        // (open before this existed, or revived by the remember-me cookie)
        // starts counting from now, so it ends at the next cut-off.
        if (!$request->session()->has('admin_login_at')) {
            $request->session()->put('admin_login_at', now()->timestamp);
        }
        $startedAt = (int) $request->session()->get('admin_login_at');
        if ($startedAt < \App\Support\AdminDailyLogout::lastCutoff()->timestamp) {
            $admin = Auth::guard($guard)->user();
            \App\Support\AdminLoginRecorder::record('auto_logout', $admin->id, $admin->username ?? null);
            Auth::guard($guard)->logout();          // also clears the remember-me cookie
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session ended (daily logout). Please sign in again.'], 401);
            }
            return redirect()->route('admin.login')
                ->with('error', 'You were signed out automatically (daily logout). Please sign in again.');
        }

        return $next($request);
    }
}
