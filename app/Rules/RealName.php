<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A name that belongs to a person, not to a link farm.
 *
 * The name field took any 255 characters, which is what made it worth filling
 * with a URL — the admin's enquiry list renders it, so a spammer gets their
 * address in front of staff for the price of one POST. Same for a name that is
 * only digits or punctuation: nothing to greet the caller by.
 */
class RealName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = trim((string) $value);

        if (mb_strlen($name) < 2) {
            $fail('Please enter your name.');

            return;
        }

        // A link, an address, or an email in the name field is advertising.
        if (preg_match('~(https?://|www\.|\.com|\.in\b|\.net|@|<|>)~i', $name)) {
            $fail('Please enter your name only.');

            return;
        }

        /*
         * Has to contain letters — "1234", "....", "----" do not.
         *
         * Counted across the whole string rather than as a run of two
         * adjacent letters, because in Devanagari (and Arabic, Tamil, and the
         * rest) the vowel signs between the consonants are combining marks,
         * not letters: "राहुल" is र + ा + ह + ु + ल, which has no two letters
         * standing next to each other. A run-based check rejects most Indian
         * names written in their own script, on a site whose visitors write
         * them that way.
         */
        if (preg_match_all('/\p{L}/u', $name) < 2) {
            $fail('Please enter your name in letters.');
        }
    }
}
