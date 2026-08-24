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

        $setting->social_login = [
            'google_enabled'         => $request->boolean('google_enabled'),
            'google_client_id'       => $keep('google_client_id'),
            'google_client_secret'   => $keep('google_client_secret'),

            'facebook_enabled'       => $request->boolean('facebook_enabled'),
            'facebook_client_id'     => $keep('facebook_client_id'),
            'facebook_client_secret' => $keep('facebook_client_secret'),
        ];

        $setting->save();

        return back()->withAlert(['success', 'Social login setting updated successfully']);
    }
}
