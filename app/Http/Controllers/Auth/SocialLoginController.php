<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Continue with Google/Facebook" — same quick-login popup that already
 * offers email-OTP login, just proving identity via the provider's account
 * instead of an OTP. Credentials come from the admin panel (Setting >
 * Social Login), pushed into services.google.* / services.facebook.* by
 * AppServiceProvider, exactly like Gupshup's WhatsApp credentials are.
 */
class SocialLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect(Request $request, string $provider)
    {
        if (!$this->providerEnabled($provider)) {
            return redirect()->route('user.login')
                ->withAlert(['danger', ucfirst($provider) . ' login is not available right now.']);
        }

        // Round-trips through the OAuth provider, so the popup can send the
        // visitor back to whatever page they opened it from.
        session(['social_login_redirect' => $request->query('redirect') ?: url('/')]);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if (!$this->providerEnabled($provider)) {
            return redirect()->route('user.login')
                ->withAlert(['danger', ucfirst($provider) . ' login is not available right now.']);
        }

        $redirectUrl = session('social_login_redirect', url('/'));

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $exception) {
            Log::error('Social login callback failed', [
                'provider' => $provider,
                'message'  => $exception->getMessage(),
            ]);

            return redirect()->route('user.login')
                ->withAlert(['danger', 'Could not sign you in with ' . ucfirst($provider) . '. Please try again.']);
        }

        $email = $socialUser->getEmail();

        if (!$email) {
            // Facebook accounts without a verified email attached don't hand
            // one over — nothing to key an account on, so this can't proceed.
            return redirect()->route('user.login')
                ->withAlert(['danger', 'Your ' . ucfirst($provider) . ' account has no email address we can use. Please log in another way.']);
        }

        $user = User::where('email', $email)->first();
        $isNewUser = !$user;

        // Google (and Facebook, when the scope allows it) hand over a phone
        // number under a couple of different raw-payload keys depending on
        // the provider — this only ever fills a gap, never overwrites a
        // number the user already has on file.
        $socialPhone = $this->extractPhone($socialUser);

        if (!$user) {
            $name = trim((string) $socialUser->getName()) ?: 'User';
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? 'User';
            $lastName = $nameParts[1] ?? '';

            $baseUsername = Str::slug($firstName . $lastName) ?: 'user';
            $username = $baseUsername;
            $i = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $i;
                $i++;
            }

            $user = User::create([
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'username'       => $username,
                'email'          => $email,
                'email_verified' => 1,
                'image'          => $socialUser->getAvatar(),
                'mobile'         => $socialPhone,
                'password'       => Hash::make(Str::random(16)),
            ]);

            event(new Registered($user));
        } else {
            // Returning account — only backfill whatever it's still missing,
            // so a name/photo/number the user has since edited by hand is
            // never clobbered by what Google happens to say this time.
            $updates = [];

            if (blank($user->first_name) && blank($user->last_name)) {
                $name = trim((string) $socialUser->getName());
                if ($name !== '') {
                    $nameParts = explode(' ', $name, 2);
                    $updates['first_name'] = $nameParts[0] ?? '';
                    $updates['last_name'] = $nameParts[1] ?? '';
                }
            }

            if (blank($user->image) && $socialUser->getAvatar()) {
                $updates['image'] = $socialUser->getAvatar();
            }

            if (blank($user->mobile) && $socialPhone) {
                $updates['mobile'] = $socialPhone;
            }

            if ($updates) {
                $user->fill($updates)->save();
            }
        }

        $booster = new Booster();
        $booster->createLoginLog($user);

        Auth::login($user);
        request()->session()->regenerate();

        \App\Support\Tracking\Tracker::conversion(request(), $isNewUser ? 'registration' : 'login', ['user_id' => $user->id]);
        \App\Support\Tracking\Tracker::linkUser(request(), $user->id);

        session()->forget('social_login_redirect');

        return redirect($redirectUrl)->withAlert(['success', 'Login successful']);
    }

    protected function providerEnabled(string $provider): bool
    {
        return filled(config('services.' . $provider . '.client_id'))
            && filled(config('services.' . $provider . '.client_secret'));
    }

    /**
     * Neither provider hands a phone number to Socialite's User object
     * directly — it only ever shows up (if at all) buried in the raw
     * payload, and under a different key per provider, so this checks every
     * shape either one is known to use instead of trusting a single field.
     */
    protected function extractPhone($socialUser): ?string
    {
        $raw = $socialUser->getRaw() ?? [];

        if (!empty($raw['phone_number'])) {
            return $raw['phone_number'];
        }

        if (!empty($raw['phoneNumbers'][0]['value'])) {
            return $raw['phoneNumbers'][0]['value'];
        }

        return null;
    }
}
