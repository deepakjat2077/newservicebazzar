<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Models\User;
use App\Models\UserApiToken;
use App\Rules\IndianMobile;
use App\Services\WhatsappSender;
use App\Support\ApiOtpStore;
use App\Support\MobileNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Mobile-app authentication (Service Bazzar Android/iOS).
 *
 * The login screen offers three ways in — WhatsApp OTP, "continue with
 * Google", "continue with Facebook" — and each ends the same way: a User row
 * is found or created and a bearer token is minted (UserApiToken). From then
 * on the app sends `Authorization: Bearer {id}|{secret}` and the request runs
 * as that user (see AuthenticateUserApi, middleware alias `user.api`).
 *
 * The website already does all three flows with sessions and OAuth redirects
 * (Auth\RegisterController::quickAuth*, Auth\SocialLoginController). This is
 * the stateless, JSON twin of that logic for a client that holds no cookies:
 *   - OTP state is keyed on the phone number, not the session (ApiOtpStore).
 *   - Social login takes the provider access token the native SDK already
 *     obtained, instead of bouncing the user through a browser redirect.
 *
 * Which social buttons the app shows is the admin's call: config() reports
 * exactly the providers turned on under Setting > Social Login.
 */
class UserAuthController extends Controller
{
    /**
     * Which login methods the app should offer.
     *
     * The app calls this on the login screen and shows the WhatsApp / Google /
     * Facebook buttons accordingly, so enabling a provider in the admin panel
     * is enough to light it up in the app with no release.
     */
    public function config()
    {
        return response()->json([
            'success'   => true,
            'providers' => [
                'whatsapp' => $this->whatsappConfigured(),
                'google'   => $this->providerEnabled('google'),
                'facebook' => $this->providerEnabled('facebook'),
            ],
            'otp' => [
                'length'            => 6,
                'expires_in'        => ApiOtpStore::CODE_TTL_MINUTES * 60,
                'resend_cooldown'   => ApiOtpStore::RESEND_COOLDOWN_SECONDS,
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | WhatsApp OTP
     | -------------------------------------------------------------------*/

    /** Send a login code over WhatsApp to the number the user typed. */
    public function sendOtp(Request $request, WhatsappSender $whatsapp)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', new IndianMobile()],
        ]);

        return $this->dispatchOtp($request->phone, $whatsapp);
    }

    /** "Didn't receive OTP? Resend" — same send, cooldown still applies. */
    public function resendOtp(Request $request, WhatsappSender $whatsapp)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', new IndianMobile()],
        ]);

        return $this->dispatchOtp($request->phone, $whatsapp);
    }

    /**
     * Check the code and log the user in.
     *
     * A brand-new number gets a fresh account here — this is a login *and* a
     * sign-up in one step, exactly as the WhatsApp button on the website is.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', new IndianMobile()],
            'otp'   => ['required', 'digits:6'],
            'name'  => ['nullable', 'string', 'max:60'],
        ]);

        $result = ApiOtpStore::attempt($request->phone, (string) $request->otp);

        if ($result !== 'ok') {
            $messages = [
                'none'     => 'No OTP is pending for this number. Please request a new one.',
                'expired'  => 'That OTP has expired. Please request a new one.',
                'too_many' => 'Too many wrong attempts. Please request a new OTP.',
                'invalid'  => 'Incorrect OTP. Attempts left: ' . ApiOtpStore::attemptsLeft($request->phone) . '.',
            ];

            return response()->json([
                'success' => false,
                'message' => $messages[$result] ?? 'Could not verify the OTP.',
            ], 422);
        }

        // Match every shape the number may already be stored in, so a
        // returning owner lands on the account that actually holds their
        // listings rather than a fresh empty one.
        $user = MobileNumber::findUser($request->phone);
        $isNewUser = !$user;

        if (!$user) {
            $user = $this->createUserFromMobile($request->phone, $request->input('name'));
            event(new Registered($user));
        }

        // The OTP just proved this number belongs to whoever is holding it —
        // record that so the `verified` middleware never bounces them to the
        // email-verification page (they may have no email at all).
        $user->markMobileVerified();

        return $this->issueTokenResponse($user, 'whatsapp', $isNewUser);
    }

    /* ---------------------------------------------------------------------
     | Social — "continue with Google / Facebook"
     | -------------------------------------------------------------------*/

    public function google(Request $request)
    {
        return $this->social('google', $request);
    }

    public function facebook(Request $request)
    {
        return $this->social('facebook', $request);
    }

    /**
     * Log in with a provider access token the native SDK already obtained.
     *
     * The app does the Google/Facebook sign-in itself and hands us the
     * resulting access token; we ask the provider who it belongs to and then
     * find-or-create the matching account. No browser redirect is involved —
     * that is the difference from the website's Socialite flow.
     */
    protected function social(string $provider, Request $request)
    {
        if (!$this->providerEnabled($provider)) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($provider) . ' login is not available right now.',
            ], 403);
        }

        $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($request->input('access_token'));
        } catch (\Throwable $exception) {
            Log::warning('API social login: token rejected', [
                'provider' => $provider,
                'message'  => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not sign you in with ' . ucfirst($provider) . '. Please try again.',
            ], 422);
        }

        $email = $socialUser->getEmail();

        if (!$email) {
            // A Facebook account with no verified email hands none over, and we
            // key accounts on email — nothing to match or create on.
            return response()->json([
                'success' => false,
                'message' => 'Your ' . ucfirst($provider) . ' account has no email address we can use. Please log in with your mobile number.',
            ], 422);
        }

        $user = User::where('email', $email)->first();
        $isNewUser = !$user;

        if (!$user) {
            $user = $this->createUserFromSocial($socialUser, $email);
            event(new Registered($user));
        } else {
            $this->backfillFromSocial($user, $socialUser);
        }

        return $this->issueTokenResponse($user, $provider, $isNewUser);
    }

    /* ---------------------------------------------------------------------
     | Session (token) management
     | -------------------------------------------------------------------*/

    /** Who am I — the app calls this on launch to restore a session. */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($request->user()),
        ]);
    }

    /** Drop this one device's token. Other devices stay signed in. */
    public function logout(Request $request)
    {
        $request->attributes->get('user_api_token')?->delete();

        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    /* ---------------------------------------------------------------------
     | Internals
     | -------------------------------------------------------------------*/

    /** Shared body of sendOtp()/resendOtp(). */
    protected function dispatchOtp(string $phone, WhatsappSender $whatsapp)
    {
        // ✅ TEST ACCOUNT: Check if this is the test phone number
        $testPhone = MobileNumber::national(config('test.phone'));
        $testOtp = config('test.otp');
        $isTestPhone = MobileNumber::national($phone) === $testPhone;

        $wait = ApiOtpStore::cooldownRemaining($phone);

        if ($wait > 0) {
            return response()->json([
                'success' => true,
                'sent'    => true,
                'message' => 'An OTP is already on its way. You can ask for a new one in ' . $wait . 's.',
                'retry_after' => $wait,
            ]);
        }

        if (!ApiOtpStore::withinSendBudget($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests for this number. Please try again later.',
            ], 429);
        }

        // ✅ TEST ACCOUNT: Use fixed OTP for test phone, skip WhatsApp
        if ($isTestPhone) {
            ApiOtpStore::challenge($phone, $testOtp);

            return response()->json([
                'success' => true,
                'sent'    => true,
                'message' => 'Test OTP sent to ' . MobileNumber::national($phone) . '.',
                'retry_after' => ApiOtpStore::RESEND_COOLDOWN_SECONDS,
            ]);
        }

        $templateId = config('services.gupshup.otp_template_id');

        if (!$templateId || !$whatsapp->isConfigured()) {
            Log::error('API OTP cannot be sent: Gupshup is not configured', [
                'template_id' => (bool) $templateId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'OTP service is unavailable right now. Please try again later.',
            ], 503);
        }

        $code = (string) verification_code(6);

        $message = $whatsapp->sendTemplate(
            MobileNumber::destination($phone),
            $templateId,
            [$code],
            [
                // Never the code itself: whatsapp_messages is readable from the
                // admin panel, and a logged OTP is one anybody with that screen
                // could use to log in as this person.
                'log_body' => 'App login OTP (code not logged)',
            ]
        );

        if ($message->status === 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Could not send the OTP right now. Please try again in a moment.',
            ], 502);
        }

        ApiOtpStore::challenge($phone, $code);

        return response()->json([
            'success' => true,
            'sent'    => true,
            'message' => 'OTP sent on WhatsApp to ' . MobileNumber::national($phone) . '.',
            'retry_after' => ApiOtpStore::RESEND_COOLDOWN_SECONDS,
        ]);
    }

    protected function createUserFromMobile(string $phone, ?string $name): User
    {
        $nameParts = explode(' ', trim((string) $name), 2);
        $firstName = $nameParts[0] ?: 'User';
        $lastName  = $nameParts[1] ?? '';

        return User::create([
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'username'       => $this->uniqueUsername($firstName . $lastName),
            // National form, the shape the rest of the accounts here use, so
            // the next login matches this row directly.
            'mobile'         => MobileNumber::national($phone),
            // No email: the OTP proved a number, not an address. The profile
            // screen offers an "add your email" field.
            'email'          => null,
            'email_verified' => 0,
            'password'       => Hash::make(Str::random(16)),
        ]);
    }

    protected function createUserFromSocial($socialUser, string $email): User
    {
        $name = trim((string) $socialUser->getName()) ?: 'User';
        $nameParts = explode(' ', $name, 2);

        return User::create([
            'first_name'     => $nameParts[0] ?: 'User',
            'last_name'      => $nameParts[1] ?? '',
            'username'       => $this->uniqueUsername($name),
            'email'          => $email,
            'email_verified' => 1,
            'image'          => $socialUser->getAvatar(),
            'mobile'         => $this->extractPhone($socialUser),
            'password'       => Hash::make(Str::random(16)),
        ]);
    }

    /**
     * Returning social account — only fill gaps, so a name/photo/number the
     * user has since edited by hand is never clobbered by the provider.
     */
    protected function backfillFromSocial(User $user, $socialUser): void
    {
        $updates = [];

        if (blank($user->first_name) && blank($user->last_name)) {
            $name = trim((string) $socialUser->getName());
            if ($name !== '') {
                $parts = explode(' ', $name, 2);
                $updates['first_name'] = $parts[0] ?? '';
                $updates['last_name']  = $parts[1] ?? '';
            }
        }

        if (blank($user->image) && $socialUser->getAvatar()) {
            $updates['image'] = $socialUser->getAvatar();
        }

        if (blank($user->mobile) && ($phone = $this->extractPhone($socialUser))) {
            $updates['mobile'] = $phone;
        }

        if ($updates) {
            $user->fill($updates)->save();
        }
    }

    /** Neither provider exposes a phone on the User object — dig the raw payload. */
    protected function extractPhone($socialUser): ?string
    {
        $raw = $socialUser->getRaw() ?? [];

        return $raw['phone_number']
            ?? ($raw['phoneNumbers'][0]['value'] ?? null);
    }

    protected function uniqueUsername(string $seed): string
    {
        $base = Str::slug($seed) ?: 'user';
        $username = $base;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    /** Mint a token, log the login, and return the standard auth payload. */
    protected function issueTokenResponse(User $user, string $provider, bool $isNewUser)
    {
        try {
            (new Booster())->createLoginLog($user);
        } catch (\Throwable $e) {
            // A geo/IP lookup hiccup must never block a login.
            Log::warning('API login log failed', ['user_id' => $user->id, 'message' => $e->getMessage()]);
        }

        [$token, $plain] = UserApiToken::issue($user, $provider);

        return response()->json([
            'success'    => true,
            'is_new_user'=> $isNewUser,
            'token'      => $plain,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at->toIso8601String(),
            'user'       => $this->userPayload($user),
        ]);
    }

    protected function userPayload(User $user): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->fullname(),
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'username'        => $user->username,
            'email'           => $user->email,
            'mobile'          => $user->mobile,
            'image'           => $user->image ? asset($user->image) : null,
            'mobile_verified' => $user->isMobileVerified(),
            'email_verified'  => (bool) $user->email_verified,
        ];
    }

    protected function whatsappConfigured(): bool
    {
        return filled(config('services.gupshup.api_key'))
            && filled(config('services.gupshup.otp_template_id'));
    }

    protected function providerEnabled(string $provider): bool
    {
        return filled(config('services.' . $provider . '.client_id'))
            && filled(config('services.' . $provider . '.client_secret'));
    }
}
