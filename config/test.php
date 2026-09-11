<?php

/**
 * Test/Review Account Configuration
 * For Google Play Store Reviewer & App Testing
 */

return [
    'enabled' => env('TEST_ACCOUNT_ENABLED', true),
    
    'phone' => env('TEST_PHONE', '8290231484'),
    'otp' => env('TEST_OTP', '207711'),
    
    'user' => [
        'name' => 'Test User',
        'city' => 'Test City',
    ],
];
