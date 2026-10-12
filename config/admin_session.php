<?php

return [
    /*
     * Every admin session ends once a day at this time (HH:MM, app timezone,
     * Asia/Kolkata). Whoever signed in before it is logged out at it; a login
     * after it lasts until the same time the next day.
     */
    'daily_logout_at' => env('ADMIN_DAILY_LOGOUT_AT', '19:00'),
];
