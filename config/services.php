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

'gupshup' => [
    'api_key'         => env('GUPSHUP_API_KEY'),
    'app_name'        => env('GUPSHUP_APP_NAME'),
    'source'          => env('GUPSHUP_SOURCE'),

    // Marketing template
    'template_id'     => env('GUPSHUP_TEMPLATE_ID'),

    // WhatsApp OTP template
    'otp_template_id' => env('GUPSHUP_OTP_TEMPLATE_ID'),

    // Enquiry-forwarding template (servicebazzar_enquiry): 7 variables —
    // contact person, listing name, customer name, customer phone, location,
    // category, enquiry date. Built by WhatsappSender::enquiryParams().
    'enquiry_template_id' => env('GUPSHUP_ENQUIRY_TEMPLATE_ID'),

    // Shared secret Gupshup must include (as a `token` query parameter) on
    // its webhook callback URL so the endpoint can reject spoofed requests.
    'webhook_secret'  => env('GUPSHUP_WEBHOOK_SECRET'),
],

];
