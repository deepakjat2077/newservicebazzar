<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Verification
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
        $user = auth()->user();
        // A WhatsApp OTP login proves the account owner just as well as a
        // clicked email link does — and those accounts carry a mobile-derived
        // placeholder address (see RegisterController::quickAuthSend), so the
        // email route is one they could never complete.
        if (!$user->email_verified && !$user->isMobileVerified()) {
            return redirect()->route('user.verification');
        }elseif(!$user->status){
            return redirect()->route('user.banned');
        }else{
            return $next($request);
        }
    }
}
