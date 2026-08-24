<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/user/dashboard';
    protected $setting;
    protected $theme;

    public function __construct()
    {
        $this->middleware('guest');
        $this->setting = Setting::first();
        $this->theme = 'themes.' . $this->setting->theme . '.';
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:30'],
            'last_name'  => ['required', 'string', 'max:30'],
            'username'   => ['required', 'string', 'min:6', 'unique:users,username'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile'     => ['required', 'string', 'max:255', 'unique:users,mobile'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function showRegistrationForm()
    {
        $data['register'] = Theme::where('name', 'register')->where('is_multiple', 0)->first();
        return view($this->theme . 'user.auth.register', $data);
    }

    public function register(Request $request, SendMail $mail)
    {
        $this->validator($request->all())->validate();

        $otp = verification_code(6);

        session([
            'register_data' => [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'username'   => $request->username,
                'mobile'     => $request->mobile,
                'email'      => $request->email,
                'password'   => $request->password,
            ],
            'register_otp' => $otp,
            'register_otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        try {
            $data = [
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'template' => 'verification_code',
                'value' => [
                    'code' => $otp,
                ]
            ];

            $mail->sendMail($data);
        } catch (\Exception $e) {
            return back()->withAlert(['danger', $e->getMessage()]);
        }

        return redirect()->route('user.otp.form')->withAlert(['success', 'OTP sent to your email']);
    }

    public function showOtpForm()
    {
        if (!session()->has('register_data')) {
            return redirect()->route('register')->withAlert(['danger', 'Please register first']);
        }

        $email = session('register_data.email');
        return view($this->theme . 'user.auth.verify_otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (!session()->has('register_data') || !session()->has('register_otp')) {
            return redirect()->route('register')->withAlert(['danger', 'Session expired. Please register again']);
        }

        if (now()->timestamp > session('register_otp_expires_at')) {
            session()->forget(['register_data', 'register_otp', 'register_otp_expires_at']);
            return redirect()->route('register')->withAlert(['danger', 'OTP expired. Please register again']);
        }

        if (!hash_equals((string) session('register_otp'), (string) $request->otp)) {
            return back()->withAlert(['danger', 'Invalid OTP']);
        }

        $data = session('register_data');

        $user = User::create([
            'first_name'           => $data['first_name'],
            'last_name'            => $data['last_name'],
            'username'             => $data['username'],
            'mobile'               => $data['mobile'],
            'email'                => $data['email'],
            'email_verified'       => 1,
            'email_otp'            => null,
            'email_otp_expires_at' => null,
            'password'             => Hash::make($data['password']),
        ]);

        $booster = new Booster();
        $booster->createLoginLog($user);

        event(new Registered($user));
        $this->guard()->login($user);
        $request->session()->regenerate();

        \App\Support\Tracking\Tracker::conversion($request, 'registration', ['user_id' => $user->id]);
        \App\Support\Tracking\Tracker::conversion($request, 'otp_verification', ['user_id' => $user->id]);
        \App\Support\Tracking\Tracker::linkUser($request, $user->id);

        session()->forget(['register_data', 'register_otp', 'register_otp_expires_at']);

       return redirect()->route('user.login')->withAlert(['success', 'Registration completed successfully. Please login now.']);
    }

    public function resendOtp(SendMail $mail)
    {
        if (!session()->has('register_data')) {
            return redirect()->route('register')->withAlert(['danger', 'Please register first']);
        }

        $otp = verification_code(6);

        session([
            'register_otp' => $otp,
            'register_otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        $data = session('register_data');

        try {
            $mailData = [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'template' => 'verification_code',
                'value' => [
                    'code' => $otp,
                ]
            ];

            $mail->sendMail($mailData);
        } catch (\Exception $e) {
            return back()->withAlert(['danger', $e->getMessage()]);
        }

        return back()->withAlert(['success', 'OTP resent successfully']);
    }


public function quickAuthSend(Request $request, SendMail $mail)
{
    $validated = $request->validate([
        'name'        => ['required', 'string', 'max:60'],
        'mobile'      => ['required', 'string', 'max:20'],
        'email'       => ['required', 'email', 'max:255'],
        'current_url' => ['nullable', 'string'],
    ]);

    $mobile = preg_replace('/\D+/', '', $validated['mobile']);

    if (strlen($mobile) === 10) {
        $mobile = '91' . $mobile;
    }

    if (strlen($mobile) < 11 || strlen($mobile) > 15) {
        return response()->json([
            'status'  => false,
            'message' => 'Please enter a valid mobile number.',
        ], 422);
    }

    // Quick-login OTP goes out over email now, not WhatsApp — the site's
    // mail settings (already configured under admin) are the single place
    // this depends on, instead of a separate Gupshup account/template.
    $otp = (string) verification_code(6);

    try {
        $mail->sendMail([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'template' => 'verification_code',
            'value'    => [
                'code' => $otp,
            ],
        ]);

        session([
            'quick_auth_data' => [
                'name'   => $validated['name'],
                'mobile' => $mobile,
                'email'  => $validated['email'],
            ],
            'quick_auth_otp' => $otp,
            'quick_auth_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'quick_auth_redirect' => $validated['current_url'] ?: url('/'),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'OTP has been sent successfully to your email.',
            'email'   => $validated['email'],
        ]);
    } catch (\Throwable $exception) {
        Log::error('Quick-auth email OTP exception', [
            'exception_class' => get_class($exception),
            'message'         => $exception->getMessage(),
            'file'            => $exception->getFile(),
            'line'            => $exception->getLine(),
            'email'           => $validated['email'],
        ]);

        return response()->json([
            'status'  => false,
            'message' => config('app.debug')
                ? $exception->getMessage()
                : 'Unable to send the OTP email right now. Please try again in a moment.',
        ], 500);
    }
}

public function quickAuthVerify(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6',
    ]);

    if (!session()->has('quick_auth_data') || !session()->has('quick_auth_otp')) {
        return response()->json([
            'status' => false,
            'message' => 'Session expired'
        ], 422);
    }

    if (now()->timestamp > session('quick_auth_otp_expires_at')) {
        session()->forget([
            'quick_auth_data',
            'quick_auth_otp',
            'quick_auth_otp_expires_at',
            'quick_auth_redirect'
        ]);

        return response()->json([
            'status' => false,
            'message' => 'OTP expired'
        ], 422);
    }

    if (!hash_equals((string) session('quick_auth_otp'), (string) $request->otp)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP'
        ], 422);
    }

    $data = session('quick_auth_data');

    $redirectUrl = session('quick_auth_redirect', url('/'));

    // Match strictly on `email` — the only channel the OTP actually proved
    // ownership of now that it's sent by mail, not WhatsApp. Matching on
    // `mobile` too would let an attacker supply a victim's mobile number
    // alongside their own email address and log in as the victim once the
    // OTP (sent to the attacker's own inbox) passes verification.
    $user = User::where('email', $data['email'])->first();

    if (!$user && User::where('mobile', $data['mobile'])->exists()) {
        // The email is new, but the mobile number already belongs to a
        // different account — refuse rather than crash on a duplicate-mobile
        // insert, and don't silently attach it to the wrong account either.
        session()->forget([
            'quick_auth_data',
            'quick_auth_otp',
            'quick_auth_otp_expires_at',
            'quick_auth_redirect'
        ]);

        return response()->json([
            'status' => false,
            'message' => 'This mobile number is already registered with a different email. Please log in with your password instead.',
        ], 422);
    }

    $isNewUser = !$user;

    if (!$user) {
        $nameParts = explode(' ', trim($data['name']), 2);

        $firstName = $nameParts[0] ?? 'User';
        $lastName  = $nameParts[1] ?? '';

        $baseUsername = Str::slug($firstName . $lastName);

        if (!$baseUsername) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $i;
            $i++;
        }

        $user = User::create([
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'username'             => $username,
            'mobile'               => $data['mobile'],
            'email'                => $data['email'],
            'email_verified'       => 1,
            'email_otp'            => null,
            'email_otp_expires_at' => null,
            'password'             => Hash::make(Str::random(12)),
        ]);

        event(new Registered($user));
    }

    $booster = new Booster();
    $booster->createLoginLog($user);

    $this->guard()->login($user);
    $request->session()->regenerate();

    \App\Support\Tracking\Tracker::conversion($request, 'otp_verification', ['user_id' => $user->id]);
    \App\Support\Tracking\Tracker::conversion($request, $isNewUser ? 'registration' : 'login', ['user_id' => $user->id]);
    \App\Support\Tracking\Tracker::linkUser($request, $user->id);

    session()->forget([
        'quick_auth_data',
        'quick_auth_otp',
        'quick_auth_otp_expires_at',
        'quick_auth_redirect'
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Login successful',
        'redirect' => $redirectUrl
    ]);
}

public function quickAuthResend(SendMail $mail)
{
    if (!session()->has('quick_auth_data')) {
        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => 'Your session has expired. Please start again.',
        ], 422);
    }

    $data = session('quick_auth_data');
    $email = (string) ($data['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => 'A valid email was not found in the session.',
        ], 422);
    }

    $otp = (string) random_int(100000, 999999);

    try {
        $mail->sendMail([
            'name'     => $data['name'] ?? 'Subscriber',
            'email'    => $email,
            'template' => 'verification_code',
            'value'    => [
                'code' => $otp,
            ],
        ]);

        session([
            'quick_auth_otp' => $otp,
            'quick_auth_otp_expires_at' => now()
                ->addMinutes(10)
                ->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'OTP has been resent successfully to your email.',
            'email'   => $email,
        ]);
    } catch (\Throwable $exception) {
        Log::error('Quick-auth email OTP resend exception.', [
            'exception_class' => get_class($exception),
            'message'         => $exception->getMessage(),
            'file'            => $exception->getFile(),
            'line'            => $exception->getLine(),
            'email'           => $email,
        ]);

        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => app()->isLocal()
                ? $exception->getMessage()
                : 'Unable to resend the OTP email right now.',
        ], 500);
    }
}


}