<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A landline number in the Indian STD-code shape.
 *
 * Unlike a mobile number, a landline carries a 2-4 digit STD/area code plus
 * a 6-8 digit local number, so it never starts 6-9 the way a mobile does and
 * its total length varies by city (e.g. 0141-2345678 for Jaipur is 4+7,
 * 011-23456789 for Delhi is 3+8). This only checks the shape a real Indian
 * landline can take, the same spirit as IndianMobile for mobiles.
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

        // STD code (2-4 digits) + local number (6-8 digits) = 8-10 digits
        // total once the trunk/country prefix is stripped.
        if (strlen($digits) < 8 || strlen($digits) > 10) {
            $fail('Please enter a valid Indian landline number (with STD code).');

            return;
        }

        if (!ctype_digit($digits)) {
            $fail('Please enter a valid Indian landline number.');

            return;
        }

        // Every digit the same, or a straight keypad run — not a real line.
        if (preg_match('/^(\d)\1+$/', $digits) || $digits === substr('0123456789', 0, strlen($digits)) || $digits === substr('9876543210', 0, strlen($digits))) {
            $fail('Please enter a real landline number.');
        }
    }
}
