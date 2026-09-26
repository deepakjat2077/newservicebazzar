<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'authorization' => [
        // No hardcoded fallback — a committed default secret is a secret
        // leak. Set AUTHORIZATION_TOKEN in .env for any code that needs it.
        'token' => env('Authorization_TOKEN'),
    ],
 'gemini' => [
    'key' => env('GEMINI_API_KEY'),
],

/*
 * Icon search for the category form (CategoryController@searchIcons). Lived
 * only as an env() call inside that controller, which reads as null the
 * moment `config:cache` runs — the search would have started 401ing on a
 * deploy step that is otherwise routine.
 */
'iconscout' => [
    'client_id' => env('ICONSCOUT_CLIENT_ID'),
],

/*
 * Address -> lat/lng lookup (SiteController@geocodeAddress). Providers are
 * tried in this order; whichever key is set wins, and the keyless Nominatim
 * is the last-resort fallback. Live needs a key — Nominatim 403s shared
 * hosting IPs.
 */
'geocoding' => [
    'locationiq' => env('LOCATIONIQ_KEY'),
    'google'     => env('GOOGLE_MAPS_KEY'),
],

/*
 * "Continue with Google/Facebook" login (Auth\SocialLoginController via
 * Laravel Socialite). Credentials are managed from the admin panel
 * (Setting > Social Login), not .env — AppServiceProvider pushes them in
 * here at runtime, same as the gupshup block below.
 * Also includes Google Places API for GMB business photo sync.
 */
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
    'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
],

'facebook' => [
    'client_id'     => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect'      => env('FACEBOOK_REDIRECT_URI'),
],

/*
 * Firebase Cloud Messaging — push notifications for the mobile app, sent via
 * the FCM HTTP v1 API. Credentials are managed from the admin panel
 * (Setting > Push Notification) — the admin pastes the Firebase service-account
 * JSON and AppServiceProvider fills these in. When disabled/empty, push is
 * skipped silently and the in-app notification list still works. See
 * App\Services\FcmService.
 */
'fcm' => [
    'enabled'         => false,
    'project_id'      => null,
    // The decoded service-account array (type/project_id/private_key/client_email/…).
    'service_account' => null,
],

'gupshup' => [
    'api_key'         => env('GUPSHUP_API_KEY'),
    'app_name'        => env('GUPSHUP_APP_NAME'),
    'source'          => env('GUPSHUP_SOURCE'),

    // Marketing template
    'template_id'     => env('GUPSHUP_TEMPLATE_ID'),

    // WhatsApp OTP template
    'otp_template_id' => env('GUPSHUP_OTP_TEMPLATE_ID'),

    // Sales payment link (sb_payment_link): 4 variables — customer name,
    // business name, amount incl. GST, order number — plus the order token
    // as the dynamic button suffix.
    'payment_template_id' => env('GUPSHUP_PAYMENT_TEMPLATE_ID'),

    // Enquiry-forwarding template (servicebazzar_enquiry): 7 variables —
    // contact person, listing name, customer name, customer phone, location,
    // category, enquiry date. Built by WhatsappSender::enquiryParams().
    'enquiry_template_id' => env('GUPSHUP_ENQUIRY_TEMPLATE_ID'),

    // Shared secret Gupshup must include (as a `token` query parameter) on
    // its webhook callback URL so the endpoint can reject spoofed requests.
    'webhook_secret'  => env('GUPSHUP_WEBHOOK_SECRET'),
],

];
