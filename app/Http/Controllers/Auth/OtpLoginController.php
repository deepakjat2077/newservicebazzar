<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpLoginController extends Controller
{
    protected $setting;
    protected $theme;

    public function __construct()
    {
        $this->middleware('guest');
        $this->setting = Setting::first();
        $this->theme = 'themes.'.$this->setting->theme.'.';
    }

    public function showOtpLoginForm()
    {
         $setting = $this->setting;
           $data['login'] = Theme::where('name','login')->where('is_multiple',0)->first();
        return view($this->theme.'user.auth.otp_login',$data);
    }

    public function sendOtp(Request $request, SendMail $mail)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Always mark the flow as pending and respond identically whether or
        // not the account exists, so this endpoint can't be used to
        // enumerate registered email addresses. Only real accounts get a
        // usable OTP stored; a non-existent-account attempt will always fail
        // verification with a generic "Invalid OTP" below.
        session(['login_otp_pending' => true]);

        if ($user) {
            $otp = verification_code(6);

            session([
                'login_otp_user_id' => $user->id,
                'login_otp' => $otp,
                'login_otp_expires_at' => now()->addMinutes(10)->timestamp,
            ]);

            $data = [
                'name' => $user->fullname(),
                'email' => $user->email,
                'template' => 'verification_code',
                'value' => [
                    'code' => $otp,
                ]
            ];

            $mail->sendMail($data);
        }

        return redirect()->route('user.otp.login.verify.form')
            ->withAlert(['success', 'If an account exists for this email, an OTP has been sent.']);
    }

    public function showVerifyForm()
    {
        if (!session()->has('login_otp_pending')) {
            return redirect()->route('user.otp.login.form')
                ->withAlert(['danger', 'Please request OTP first']);
        }
         $setting = $this->setting;
           $data['login'] = Theme::where('name','login')->where('is_multiple',0)->first();

        return view($this->theme.'user.auth.otp_login_verify',$data);
    }



    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        if (!session()->has('login_otp_pending')) {
            return redirect()->route('user.otp.login.form')
                ->withAlert(['danger', 'Session expired. Please try again.']);
        }

        if (!session()->has('login_otp_user_id') || !session()->has('login_otp')) {
            // No account matched the submitted email during sendOtp() — fail
            // the same way an incorrect OTP would, rather than revealing
            // that this specific email has no account.
            return back()->withAlert(['danger', 'Invalid OTP']);
        }

        if (now()->timestamp > session('login_otp_expires_at')) {
            session()->forget(['login_otp_pending', 'login_otp_user_id', 'login_otp', 'login_otp_expires_at']);

            return redirect()->route('user.otp.login.form')
                ->withAlert(['danger', 'OTP expired. Please request again.']);
        }

        if (!hash_equals((string) session('login_otp'), (string) $request->otp)) {
            return back()->withAlert(['danger', 'Invalid OTP']);
        }

        $user = User::find(session('login_otp_user_id'));

        if (!$user) {
            session()->forget(['login_otp_pending', 'login_otp_user_id', 'login_otp', 'login_otp_expires_at']);
            return redirect()->route('user.otp.login.form')
                ->withAlert(['danger', 'User not found']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        \App\Support\Tracking\Tracker::conversion($request, 'otp_verification', ['user_id' => $user->id]);
        \App\Support\Tracking\Tracker::conversion($request, 'login', ['user_id' => $user->id]);
        \App\Support\Tracking\Tracker::linkUser($request, $user->id);

        session()->forget(['login_otp_pending', 'login_otp_user_id', 'login_otp', 'login_otp_expires_at']);

        return redirect()->route('user.dashboard')->withAlert(['success', 'Login successful']);
    }
}