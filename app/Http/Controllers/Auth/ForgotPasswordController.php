<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\PasswordReset;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    protected $redirectTo = '/user/dashboard';
    protected $setting;
    protected $theme;

    public function __construct()
    {
        $this->middleware('guest');
        $this->setting = Setting::first();
        $this->theme = 'themes.'.$this->setting->theme.'.';
    }

    public function showCodeRequestForm()
    {
        return view($this->theme.'user.auth.passwords.email');
    }

    public function sendResetCodeEmail(Request $request,SendMail $mail)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();

        // Respond identically whether or not the account exists, so this
        // endpoint can't be used to enumerate registered email addresses.
        if ($user) {
            PasswordReset::where('email', $user->email)->delete();
            $code = verification_code(6);
            PasswordReset::create([
                'email' => $user->email,
                'token' => $code,
                'expires_at' => now()->addMinutes(15),
                'created_at' => \Carbon\Carbon::now(),
            ]);

            $data = [
                'name'=>$user->fullname(),
                'email'=>$user->email,
                'template'=>'verification_code',
                'value'=>[
                    "code"=>$code,
                ]
            ];
            $mail->sendMail($data);
        }

        $alert = ['success','If an account exists for this email, a password reset code has been sent.'];
        session()->put('verification_mail', $request->email);
        return redirect()->route('user.password.verify')->withAlert($alert);
    }

    public function verify(){
        if (!session('verification_mail')) {
            $alert = ['danger','Oops! Session out'];
            return redirect()->route('user.login');
        }
        $email = session('verification_mail');
        return view($this->theme.'user.auth.passwords.code',compact('email'));
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required', 'email' => 'required']);
        $reset = PasswordReset::where('token', $request->code)->where('email', $request->email)->first();

        if (!$reset || ($reset->expires_at && now()->greaterThan($reset->expires_at))) {
            $alert = ['danger','Invalid or expired code'];
            return redirect()->route('user.password.reset')->withAlert($alert);
        }
        $alert = ['success','You can change your password.'];
        session()->flash('fpass_email', $request->email);
        return redirect()->route('user.password.reset-code', $request->code)->withAlert($alert);
    }
}
