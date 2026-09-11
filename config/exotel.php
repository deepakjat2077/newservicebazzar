<?php

return [
    'enabled' => env('EXOTEL_ENABLED', false),
    'account_sid' => env('EXOTEL_ACCOUNT_SID'),
    'api_key' => env('EXOTEL_API_KEY'),
    'api_token' => env('EXOTEL_API_TOKEN'),
    'api_endpoint' => env('EXOTEL_API_ENDPOINT', 'https://api.exotel.com'),
    'recording' => env('EXOTEL_RECORDING', true),

    // The ExoPhone (virtual number) Exotel provisioned for this account —
    // used as CallerId so both parties only ever see this number. Must be a
    // real ExoPhone, not a co-worker's personal line.
    'virtual_number' => env('EXOTEL_VIRTUAL_NUMBER', '01414940663'),

    /*
     * Trial-phase rollout: bridge call is only offered on listings that match
     * BOTH a category and a location below. Empty arrays = no restriction
     * (every listing gets the feature) — used once this expands past the
     * pilot category/city.
     */
    'restricted_category_ids' => array_filter(explode(',', env('EXOTEL_CATEGORY_IDS', '65'))),
    'restricted_location_ids' => array_filter(explode(',', env('EXOTEL_LOCATION_IDS', '1'))),
];
