<?php

return [
    'enabled' => env('EXOTEL_ENABLED', false),
    'account_sid' => env('EXOTEL_ACCOUNT_SID'),
    'api_key' => env('EXOTEL_API_KEY'),
    'api_endpoint' => env('EXOTEL_API_ENDPOINT', 'https://exotel.com'),
    'recording' => env('EXOTEL_RECORDING', true),

    // Add your Exotel phone number (Virtual Number) here
    'virtual_number' => env('EXOTEL_VIRTUAL_NUMBER', '9876543210'),
];
