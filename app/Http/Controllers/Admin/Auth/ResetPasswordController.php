<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/admin/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Http\Response
     */
    public function showResetForm(Request $request, $token)
    {
        $adminPass = AdminPasswordReset::where('token', $token)->where('status', 0)->first();

        if (empty($adminPass) || ($adminPass->expires_at && now()->greaterThan($adminPass->expires_at))) {
            $alert[] = ['type'=>'danger','message'=>'Token Not Found!'];
            return redirect()->route('admin.password.reset')->withAlert($alert);
        }
        $email = $adminPass->email;
        return view('admin.auth.passwords.reset', compact('email', 'token'));
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:4',
        ]);

        // Scope by BOTH token and the submitted email so a code can only be
        // used to reset the account it was actually issued to.
        $reset = AdminPasswordReset::where('token', $request->token)
            ->where('email', $request->email)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$reset) {
            $alert[] = ['type'=>'danger', 'message'=>'Invalid Verification Code'];
            return redirect()->route('admin.login')->withAlert($alert);
        }

        if ($reset->status == 1 || ($reset->expires_at && now()->greaterThan($reset->expires_at))) {
            $alert[] = ['type'=>'danger', 'message'=>'Invalid code'];
            return redirect()->route('admin.login')->withAlert($alert);
        }

        $user = Admin::where('email', $reset->email)->first();
        if (!$user) {
            $alert[] = ['type'=>'danger', 'message'=>'Invalid Verification Code'];
            return redirect()->route('admin.login')->withAlert($alert);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        AdminPasswordReset::where('email', $user->email)->update(['status' => 1]);

        $alert[] = ['type'=>'success', 'message'=>'Password Changed'];
        return redirect()->route('admin.login')->withAlert($alert);
    }


	/**
	 * Get the broker to be used during password reset.
	 *
	 * @return \Illuminate\Contracts\Auth\PasswordBroker
	 */
    public function broker()
    {
        return Password::broker('admins');
    }
    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

}
