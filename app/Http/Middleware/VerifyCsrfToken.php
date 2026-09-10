<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'ipn*',
        'gupshup/webhook',
        '/gupshup/webhook',
        'user/deposit/confirm',  // Payment gateway redirect — session may have expired, but IPN validates the transaction
        'pay/*',  // Mobile app checkout (URL-keyed, not session-keyed)
    ];
}