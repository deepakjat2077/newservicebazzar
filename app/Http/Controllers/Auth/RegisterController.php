<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use App\Models\Theme;
use App\Models\User;
use App\Support\HttpRetry;
use App\Support\MobileNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
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


public function quickAuthSend(Request $request)
{
    $validated = $request->validate([
        'name'        => ['required', 'string', 'max:60'],
        'mobile'      => ['required', 'string', 'max:20'],
        'current_url' => ['nullable', 'string'],
    ]);

    // Gupshup routes on the country-code form, so that is what gets sent.
    // Which account the caller lands on is no longer decided here:
    // quickAuthVerify() matches every shape the number may already be stored
    // in, so a "91" added for delivery can't strand anyone on a fresh, empty
    // account with none of their listings on it.
    $mobile = MobileNumber::destination($validated['mobile']);

    if (strlen($mobile) < 11 || strlen($mobile) > 15) {
        return response()->json([
            'status'  => false,
            'message' => 'Please enter a valid WhatsApp mobile number.',
        ], 422);
    }

    $apiKey = config('services.gupshup.api_key');
    $appName = config('services.gupshup.app_name');
    $source = config('services.gupshup.source');
    $templateId = config('services.gupshup.otp_template_id');

    if (!$apiKey || !$appName || !$source || !$templateId) {
        Log::error('Gupshup configuration is incomplete', [
            'api_key_exists' => !empty($apiKey),
            'app_name'       => $appName,
            'source'         => $source,
            'template_id'    => $templateId,
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'WhatsApp configuration is incomplete.',
        ], 500);
    }

    $otp = (string) verification_code(6);

    try {
        $attempt = HttpRetry::send(function () use ($apiKey, $source, $mobile, $appName, $templateId, $otp) {
            return Http::asForm()
                ->connectTimeout(10)
                ->timeout(30)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post('https://api.gupshup.io/wa/api/v1/template/msg', [
                    'channel'     => 'whatsapp',
                    'source'      => $source,
                    'destination' => $mobile,
                    'src.name'    => $appName,
                    // Authentication-category templates (like otp222) carry
                    // one {{1}} variable, reused for both the body and the
                    // "Copy code" button — a single param, not the repeated
                    // pair a two-placeholder marketing template needed.
                    'template'    => json_encode([
                        'id' => $templateId,
                        'params' => [$otp],
                    ]),
                ]);
        }, 3, 1500, 'gupshup.otp', 20);

        $response = $attempt['response'];

        if ($response === null) {
            Log::error('Gupshup WhatsApp OTP unreachable', [
                'mobile'   => $mobile,
                'attempts' => $attempt['attempts'],
                'error'    => optional($attempt['exception'])->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Could not send the OTP right now. Please try again in a moment.',
            ], 502);
        }

        $responseData = $response->json();

        Log::info('Gupshup WhatsApp OTP response', [
            'http_status' => $response->status(),
            'attempts'    => $attempt['attempts'],
            'response'    => $responseData ?: $response->body(),
            'mobile'      => $mobile,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'status'  => false,
                'message' => HttpRetry::retryable($response)
                    ? 'Could not send the OTP right now. Please try again in a moment.'
                    : ($responseData['message']
                        ?? 'Gupshup rejected the WhatsApp OTP request.'),
            ], HttpRetry::clientStatus($response->status()));
        }

        $status = strtolower((string) ($responseData['status'] ?? ''));

        if (!in_array($status, ['success', 'submitted'], true)) {
            return response()->json([
                'status'  => false,
                'message' => $responseData['message']
                    ?? 'Failed to send WhatsApp OTP.',
            ], 500);
        }

        session([
            'quick_auth_data' => [
                'name'   => $validated['name'],
                'mobile' => $mobile,
                // No email: WhatsApp never hands us one, and the account row
                // now holds NULL rather than a made-up
                // "<number>@whatsapp.servicebazzar.com" address. Inventing one
                // put a fake, undeliverable address on the owner's own profile
                // page. They add a real one there when they want to.
            ],
            'quick_auth_otp' => $otp,
            'quick_auth_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'quick_auth_redirect' => $validated['current_url'] ?: url('/'),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'OTP has been sent successfully via WhatsApp.',
            'mobile'  => $mobile,
        ]);
    } catch (\Throwable $exception) {
        Log::error('Gupshup WhatsApp OTP exception', [
            'exception_class' => get_class($exception),
            'message'         => $exception->getMessage(),
            'file'            => $exception->getFile(),
            'line'            => $exception->getLine(),
            'mobile'          => $mobile,
        ]);

        return response()->json([
            'status'  => false,
            'message' => config('app.debug')
                ? $exception->getMessage()
                : 'Unable to connect to the WhatsApp service.',
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

    // Match strictly on `mobile` — the only channel the OTP actually proved
    // ownership of, now that it's sent over WhatsApp again. Matching on
    // `email` too would let an attacker supply a victim's mobile number
    // alongside their own email/placeholder and log in as the victim once
    // the OTP (sent to the attacker's own WhatsApp) passes verification.
    //
    // The lookup goes through MobileNumber so every shape the number may be
    // stored in counts as the same person: most accounts here hold the bare
    // 10 digits, while `$data['mobile']` carries the "91" Gupshup needs. An
    // exact-string match missed those accounts and quietly created a second,
    // empty one instead — which is why a returning owner saw none of their
    // listings after logging in.
    $user = MobileNumber::findUser($data['mobile']);

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
            // Stored in the national form the rest of the accounts on this
            // site use, so the next login matches this row directly.
            'mobile'               => MobileNumber::national($data['mobile']),
            // Left empty on purpose — the OTP proved a mobile number, not an
            // address. The profile page offers an "add your email" field, and
            // markMobileVerified() below records what the OTP did prove.
            'email'                => null,
            'email_verified'       => 0,
            'email_otp'            => null,
            'email_otp_expires_at' => null,
            'password'             => Hash::make(Str::random(12)),
        ]);

        event(new Registered($user));
    }

    // The OTP just proved this number belongs to whoever is holding the
    // session, on an existing account as much as a fresh one. Without this
    // the `verified` middleware bounces them to the email-verification page,
    // which tries to mail a placeholder address no SMTP server will accept.
    $user->markMobileVerified();

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

public function quickAuthResend()
{
    if (!session()->has('quick_auth_data')) {
        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => 'Your session has expired. Please start again.',
        ], 422);
    }

    $data = session('quick_auth_data');
    $mobile = preg_replace('/\D/', '', (string) ($data['mobile'] ?? ''));

    if (!preg_match('/^[1-9][0-9]{10,14}$/', $mobile)) {
        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => 'A valid WhatsApp number was not found in the session.',
        ], 422);
    }

    $apiKey = trim((string) config('services.gupshup.api_key'));
    $appName = trim((string) config('services.gupshup.app_name'));
    $source = preg_replace('/\D/', '', (string) config('services.gupshup.source'));
    $templateId = trim((string) config('services.gupshup.otp_template_id'));

    if ($apiKey === '' || $appName === '' || $source === '' || $templateId === '') {
        Log::error('Gupshup resend configuration is incomplete.', [
            'api_key_exists'  => $apiKey !== '',
            'app_name_exists' => $appName !== '',
            'source_exists'   => $source !== '',
            'template_exists' => $templateId !== '',
        ]);

        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => 'WhatsApp configuration is incomplete.',
        ], 500);
    }

    $otp = (string) random_int(100000, 999999);

    try {
        $attempt = HttpRetry::send(function () use ($apiKey, $source, $mobile, $appName, $templateId, $otp) {
            return Http::asForm()
                ->connectTimeout(10)
                ->timeout(30)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post('https://api.gupshup.io/wa/api/v1/template/msg', [
                    'channel'     => 'whatsapp',
                    'source'      => $source,
                    'destination' => $mobile,
                    'src.name'    => $appName,
                    'template'    => json_encode([
                        'id' => $templateId,
                        'params' => [$otp],
                    ], JSON_UNESCAPED_SLASHES),
                ]);
        }, 3, 1500, 'gupshup.otp.resend', 20);

        $response = $attempt['response'];

        if ($response === null) {
            Log::error('Gupshup WhatsApp OTP resend unreachable', [
                'destination' => $mobile,
                'attempts'    => $attempt['attempts'],
                'error'       => optional($attempt['exception'])->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => 'Could not resend the OTP right now. Please try again in a moment.',
            ], 422);
        }

        $responseData = $response->json();

        Log::info('Gupshup WhatsApp OTP resend response.', [
            'http_status' => $response->status(),
            'attempts'    => $attempt['attempts'],
            'response'    => $responseData ?: $response->body(),
            'destination' => $mobile,
            'message_id'  => $responseData['messageId'] ?? null,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => is_array($responseData)
                    ? ($responseData['message']
                        ?? 'Gupshup rejected the OTP resend request.')
                    : 'Gupshup rejected the OTP resend request.',
                'debug' => app()->isLocal()
                    ? ($responseData ?: $response->body())
                    : null,
            ], 422);
        }

        $gupshupStatus = strtolower((string) ($responseData['status'] ?? ''));

        if (!in_array($gupshupStatus, ['success', 'submitted'], true)) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => $responseData['message']
                    ?? 'The OTP resend request was not accepted.',
                'debug' => app()->isLocal()
                    ? $responseData
                    : null,
            ], 422);
        }

        session([
            'quick_auth_otp' => $otp,
            'quick_auth_otp_expires_at' => now()
                ->addMinutes(10)
                ->timestamp,
            'quick_auth_message_id' => $responseData['messageId'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'OTP has been resent successfully via WhatsApp.',
            'mobile'  => $mobile,
        ]);
    } catch (\Throwable $exception) {
        Log::error('Gupshup WhatsApp OTP resend exception.', [
            'exception_class' => get_class($exception),
            'message'         => $exception->getMessage(),
            'file'            => $exception->getFile(),
            'line'            => $exception->getLine(),
            'destination'     => $mobile,
        ]);

        return response()->json([
            'success' => false,
            'status'  => false,
            'message' => app()->isLocal()
                ? $exception->getMessage()
                : 'Unable to resend the WhatsApp OTP.',
        ], 500);
    }
}


}