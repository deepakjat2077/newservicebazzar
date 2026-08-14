<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\Setting;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

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
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/user/login';
    protected $setting;
    protected $theme;

    public function __construct()
    {
        $this->middleware('guest');
        $this->setting = Setting::first();
        $this->theme = 'themes.'.$this->setting->theme.'.';
    }

    public function showResetForm(Request $request, $token = null)
    {

        $email = session('fpass_email');
        $token = session()->has('token') ? session('token') : $token;
        $reset = PasswordReset::where('token', $token)->where('email', $email)->first();

        if (!$reset || ($reset->expires_at && now()->greaterThan($reset->expires_at))) {
            $alert = ['danger','Invalid or expired token'];
            return redirect()->route('user.password.reset')->withAlert($alert);
        }
        return view($this->theme.'user.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $email]
        );
    }

    public function reset(Request $request)
    {

        $request->validate($this->rules(), $this->validationErrorMessages());

        // Scope the lookup by BOTH token and the submitted email — a code is
        // only valid for the account it was actually issued to, not for
        // whichever account happens to hold a matching token value.
        $reset = PasswordReset::where('token', $request->token)
            ->where('email', $request->email)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$reset || ($reset->expires_at && now()->greaterThan($reset->expires_at))) {
            $alert = ['danger', 'Invalid or expired verification code'];
            return redirect()->route('user.login')->withAlert($alert);
        }

        $user = User::where('email', $reset->email)->first();
        if (!$user) {
            $alert = ['danger', 'Invalid Verification Code'];
            return redirect()->route('user.login')->withAlert($alert);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        // Single-use: invalidate this code (and any other pending codes for
        // the account) once the password has actually been changed.
        PasswordReset::where('email', $user->email)->delete();

        $alert = ['success','Password Changed'];
        return redirect()->route('user.login')->withAlert($alert);
    }



    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }
}
