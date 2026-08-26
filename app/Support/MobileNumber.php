<?php

namespace App\Support;

use App\Models\User;

/**
 * One phone number can sit in the `users` table in several shapes: the bare
 * 10 digits most accounts were registered with, the same number with the
 * "91" country code in front (what the WhatsApp OTP send needs as a Gupshup
 * destination), and older rows carrying spaces or dashes. Comparing the raw
 * strings therefore misses the account the caller already owns and hands
 * them a brand-new empty one instead — with none of their listings on it.
 *
 * Everything that resolves a person from a phone number goes through here.
 */
class MobileNumber
{
    /** Digits only — no +, spaces, dashes or brackets. */
    public static function digits($value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    /**
     * How the number is stored on a *new* account: the national form, since
     * that is the shape the existing accounts on this site use.
     */
    public static function national($value): string
    {
        $digits = self::digits($value);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return $digits;
    }

    /** The Gupshup destination form: country code included. */
    public static function destination($value): string
    {
        $digits = self::digits($value);

        if (strlen($digits) === 10) {
            return '91' . $digits;
        }

        return $digits;
    }

    /**
     * Every string an account may be holding for this same number.
     */
    public static function variants($value): array
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return [];
        }

        $variants = [$digits];

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $variants[] = substr($digits, 2);
        } elseif (strlen($digits) === 10) {
            $variants[] = '91' . $digits;
        }

        return array_values(array_unique($variants));
    }

    /**
     * The account behind a number, whichever shape it was stored in.
     *
     * When the same number ended up on more than one account (a duplicate
     * signup, or the country-code mismatch above), the one actually carrying
     * listings wins — that is the account whose owner is trying to get back
     * in. Oldest first breaks the remaining ties.
     */
    public static function findUser($value): ?User
    {
        $variants = self::variants($value);

        if (empty($variants)) {
            return null;
        }

        // Separators only older rows have; kept out of the comparison so a
        // "98765 43210" row still matches the digits the OTP was sent to.
        $stripped = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '')";

        return User::where(function ($query) use ($variants, $stripped) {
                foreach ($variants as $variant) {
                    $query->orWhere('mobile', $variant)
                        ->orWhereRaw("$stripped = ?", [$variant]);
                }
            })
            ->withCount('listings')
            ->orderByDesc('listings_count')
            ->orderBy('id')
            ->first();
    }
}
