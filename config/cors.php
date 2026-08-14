<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
|
| The WhatsApp panel is served from its own domain, so every call it makes to
| /api/* is cross-origin and the browser will refuse the response unless this
| allows it.
|
| Origins come from WHATSAPP_PANEL_ORIGINS in .env, comma-separated, e.g.
|
|   WHATSAPP_PANEL_ORIGINS="https://panel.servicebazzar.com,http://localhost:5173"
|
| Deliberately NOT '*': the API is authenticated, and a wildcard would let any
| page on the internet make requests with a token it had somehow obtained. Set
| the real origins.
|
*/

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('WHATSAPP_PANEL_ORIGINS', ''))
)));

return [

    // Only the API. The rest of the site is same-origin and needs no headers.
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Falls back to the Vite dev server so a fresh clone works locally
    // without anyone having to discover this file first.
    'allowed_origins' => $origins ?: ['http://localhost:5173'],

    'allowed_origins_patterns' => [],

    // Authorization is the one that matters — without it the bearer token is
    // stripped from the preflight and every call comes back 401.
    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    // Cache the preflight for a day so the panel isn't paying an extra
    // round-trip before every non-GET call.
    'max_age' => 86400,

    /*
     * False on purpose. Auth is a bearer token, not a cookie, so the browser
     * never needs to send credentials — and leaving this off means a stray
     * session cookie can never be used to authenticate a cross-origin call.
     */
    'supports_credentials' => false,

];
