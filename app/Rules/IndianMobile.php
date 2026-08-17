<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A phone number an agent can actually ring.
 *
 * The enquiry form only ever checked `string|max:20`, so "asdfgh", "1234567890"
 * and an empty pair of brackets all counted as leads. Every one of those costs
 * an agent a call-back attempt before anyone finds out.
 *
 * Accepts the shapes a real visitor types — 98765 43210, +91 98765 43210,
 * 098765-43210 — and judges only the ten digits underneath.
 */
class IndianMobile implements ValidationRule
{
    /**
     * Numbers that are the right length and shape but cannot be a subscriber:
     * every digit the same, or a straight run up or down the keypad. These are
     * what someone types when they do not want to give a number.
     */
    private const OBVIOUS_FAKES = [
        '1234567890', '0123456789', '9876543210', '1111111111', '2222222222',
        '3333333333', '4444444444', '5555555555', '6666666666', '7777777777',
        '8888888888', '9999999999', '0000000000', '1234512345', '9999999998',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        // Strip the country code or trunk prefix, whichever was typed.
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            $fail('Please enter a valid 10-digit mobile number.');

            return;
        }

        // Indian mobile numbers begin 6, 7, 8 or 9. Landlines and service
        // codes do not reach a person on WhatsApp, which is how these leads
        // are followed up.
        if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
            $fail('Please enter a valid mobile number starting with 6, 7, 8 or 9.');

            return;
        }

        if (in_array($digits, self::OBVIOUS_FAKES, true)) {
            $fail('Please enter your real mobile number so we can contact you.');
        }
    }
}
