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
        if (!$user->email_verified) {
            return redirect()->route('user.verification');
        }elseif(!$user->status){
            return redirect()->route('user.banned');
        }else{
            return $next($request);
        }
    }
}
