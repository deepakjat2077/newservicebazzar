<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A landline number in the Indian STD-code shape, or a toll-free number.
 *
 * Standard landline: 2-4 digit STD/area code plus a 6-8 digit local number
 * (e.g. 0141-2345678 for Jaipur is 4+7, 011-23456789 for Delhi is 3+8).
 *
 * Toll-free numbers: 1800 or 1860 prefix followed by 4+4 digit subscriber
 * number (e.g. 1800-270-1280 is 4+4+4).
 *
 * This only checks the shape a real Indian landline or toll-free can take,
 * the same spirit as IndianMobile for mobiles.
 */
class IndianLandline implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        // Strip a leading country code (91) or trunk prefix (0), whichever
        // was typed, same as IndianMobile does for mobiles.
        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) > 8 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (!ctype_digit($digits)) {
            $fail('Please enter a valid Indian landline or toll-free number.');

            return;
        }

        // Accept standard landlines (8-10 digits after stripping trunk/country code)
        // OR toll-free numbers (11 digits: 1800/1860 + 8 more digits).
        $isTollFree = str_starts_with($digits, '1800') || str_starts_with($digits, '1860');
        $isStandardLandline = strlen($digits) >= 8 && strlen($digits) <= 10;

        if (!$isTollFree && !$isStandardLandline) {
            $msg = $isTollFree
                ? 'Toll-free numbers must be 1800-XXXX-XXXX or 1860-XXXX-XXXX format.'
                : 'Please enter a valid Indian landline number (with STD code).';
            $fail($msg);

            return;
        }

        // Every digit the same, or a straight keypad run — not a real number.
        if (preg_match('/^(\d)\1+$/', $digits) || $digits === substr('0123456789', 0, strlen($digits)) || $digits === substr('9876543210', 0, strlen($digits))) {
            $fail('Please enter a real landline or toll-free number.');
        }
    }
}
