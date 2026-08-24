<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Google / Facebook "continue with" login credentials, managed here instead
 * of .env so they can be changed without a deploy — same pattern as
 * SettingController::gupshup() for the WhatsApp credentials. Values are
 * picked up by AppServiceProvider and pushed into services.google.* /
 * services.facebook.* for Socialite (see Auth\SocialLoginController).
 */
class SocialLoginSettingController extends Controller
{
    public function index()
    {
        return view('admin.setting.social-login');
    }

    public function update(Request $request)
    {
        $request->validate([
            // Google and Facebook are two separate <form>s on the page, each
            // posting only its own fields — this says which one just came
            // in, so the other provider's saved values are left untouched
            // below rather than read as "absent" and turned off.
            'provider' => 'required|in:google,facebook',

            'google_enabled'   => 'nullable|boolean',
            // Secrets are never re-displayed, so a blank submission means
            // "keep the stored value" — only required together with a
            // freshly-typed client id.
            'google_client_id'     => 'nullable|string|max:191',
            'google_client_secret' => 'nullable|string|max:191',

            'facebook_enabled'   => 'nullable|boolean',
            'facebook_client_id'     => 'nullable|string|max:191',
            'facebook_client_secret' => 'nullable|string|max:191',
        ]);

        $setting = Setting::first();
        $current = (array) $setting->social_login;

        $keep = function ($field) use ($request, $current) {
            return $request->filled($field)
                ? trim($request->input($field))
                : ($current[$field] ?? null);
        };

        $provider = $request->input('provider');

        $current['google_enabled'] = $provider === 'google'
            ? $request->boolean('google_enabled')
            : (bool) ($current['google_enabled'] ?? false);
        $current['google_client_id'] = $keep('google_client_id');
        $current['google_client_secret'] = $keep('google_client_secret');

        $current['facebook_enabled'] = $provider === 'facebook'
            ? $request->boolean('facebook_enabled')
            : (bool) ($current['facebook_enabled'] ?? false);
        $current['facebook_client_id'] = $keep('facebook_client_id');
        $current['facebook_client_secret'] = $keep('facebook_client_secret');

        $setting->social_login = $current;
        $setting->save();

        return back()->withAlert(['success', ucfirst($provider) . ' login setting updated successfully']);
    }
}
