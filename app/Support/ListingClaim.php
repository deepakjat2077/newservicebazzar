<?php

namespace App\Support;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Hands a business to the person who just proved, with an OTP, that they own
 * the phone number printed on it.
 *
 * The number on a listing is the business's own number, so whoever receives
 * its SMS is the business. Listings were often created by the sales team on a
 * throwaway account, or the owner later signed up again with a typo in the
 * number, which left the listing invisible on the account they actually log
 * in with. One owner may legitimately have any number of listings, so there
 * is no limit on how many a number may bring.
 *
 * Two things are never taken automatically. Nothing moves; the attempt is
 * written to listing_claims with status "held" as a record only — there is no
 * admin screen for these, so a held listing simply stays where it is:
 *  - a number belonging to Service Bazzar itself (the sales team filled their
 *    own number in on ~106 listings, so it identifies nobody);
 *  - a listing whose current owner actually signs in, since that account is
 *    being used to manage it.
 */
class ListingClaim
{
    /** Cached for a minute: this runs on every OTP login. */
    private const STAFF_CACHE_KEY = 'listing-claim:staff-numbers';

    /**
     * Give $user every listing carrying $phone that is safe to give.
     *
     * @return int how many listings moved
     */
    public static function forUser(User $user, string $phone): int
    {
        $digits = MobileNumber::national($phone);

        if (strlen($digits) !== 10 || !config('listing.claim_by_phone', true)) {
            return 0;
        }

        if (in_array($digits, self::staffNumbers(), true)) {
            self::hold($user, $digits, 'Service Bazzar staff number');

            return 0;
        }

        $listings = self::listingsWith($digits);

        // One owner may have any number of listings, so there is no limit as
        // such — but a number printed on more than this many is in practice a
        // placeholder somebody typed in across unrelated businesses
        // (9352806004 sits on a courier, a CA, a plumber and a pet shop), and
        // handing all of those to one person would be wrong. Those wait for an
        // admin. 0 turns the check off.
        $reviewAbove = (int) config('listing.claim_review_above', 5);

        if ($reviewAbove > 0 && $listings->count() > $reviewAbove) {
            foreach ($listings as $listing) {
                if ((int) $listing->user_id !== (int) $user->id) {
                    self::record($listing, $user, $digits, 'held', 'On ' . $listings->count() . ' listings; needs a check');
                }
            }

            return 0;
        }

        $moved = 0;

        foreach ($listings as $listing) {
            if ((int) $listing->user_id === (int) $user->id) {
                continue;
            }

            if (self::ownerSignsIn($listing->user_id)) {
                self::record($listing, $user, $digits, 'held', 'Current owner signs in');
                continue;
            }

            try {
                DB::transaction(function () use ($listing, $user, $digits, &$moved) {
                    $previous = $listing->user_id;
                    DB::table('listings')->where('id', $listing->id)->update(['user_id' => $user->id]);
                    self::record($listing, $user, $digits, 'claimed', null, $previous);
                    $moved++;
                });
            } catch (\Throwable $e) {
                Log::warning('Listing claim failed', ['listing_id' => $listing->id, 'error' => $e->getMessage()]);
            }
        }

        if ($moved) {
            Log::info('Listings claimed by phone', ['user_id' => $user->id, 'phone' => $digits, 'count' => $moved]);
        }

        return $moved;
    }

    /**
     * Listings printing this number, in any of the three fields and in any
     * shape it was typed in. Narrowed in SQL first (a LIKE on the bare digits
     * matches "+91 89...", "89-...", and the JSON arrays alike), then checked
     * properly in PHP so "98765432101" never matches "9876543210".
     */
    private static function listingsWith(string $digits)
    {
        // Numbers are stored as typed: "95871 64115", "+91-98765 43210".
        // The separators come out in SQL too, or the LIKE misses the row.
        $bare = fn (string $column) => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`{$column}`, ' ', ''), '-', ''), '+', ''), '(', ''), ')', ''), '.', '')";

        return Listing::where(fn ($q) => $q
                ->whereRaw($bare('mobile') . ' LIKE ?', ["%{$digits}%"])
                ->orWhereRaw($bare('mobile_numbers') . ' LIKE ?', ["%{$digits}%"])
                ->orWhereRaw($bare('whatsapp_numbers') . ' LIKE ?', ["%{$digits}%"]))
            ->get(['id', 'name', 'user_id', 'mobile', 'mobile_numbers', 'whatsapp_numbers'])
            ->filter(fn ($l) => in_array($digits, self::numbersOn($l), true));
    }

    /** @return string[] every 10-digit number printed on a listing */
    public static function numbersOn($listing): array
    {
        $out = [];

        foreach ([$listing->mobile, $listing->mobile_numbers, $listing->whatsapp_numbers] as $value) {
            if (is_string($value) && str_starts_with(trim($value), '[')) {
                $value = json_decode($value, true) ?: [];
            }
            foreach ((array) $value as $number) {
                $d = MobileNumber::digits($number);
                if (strlen($d) >= 10) {
                    $out[substr($d, -10)] = true;
                }
            }
        }

        // Back to strings: PHP turns an all-digit array key into an int.
        return array_map('strval', array_keys($out));
    }

    /** An owner who has signed in is managing the listing; don't take it from them. */
    private static function ownerSignsIn($userId): bool
    {
        return $userId && DB::table('login_histories')->where('user_id', $userId)->exists();
    }

    /**
     * Service Bazzar's own numbers: every admin's, plus the WhatsApp sender.
     * Read from the admins table so a new staff member is covered without a
     * code change; LISTING_CLAIM_BLOCKED_NUMBERS adds any others.
     */
    public static function staffNumbers(): array
    {
        return Cache::remember(self::STAFF_CACHE_KEY, now()->addMinutes(10), function () {
            $numbers = [];

            foreach (array_filter(explode(',', (string) config('listing.claim_blocked_numbers'))) as $n) {
                $numbers[] = MobileNumber::national(trim($n));
            }

            $numbers[] = MobileNumber::national(config('services.gupshup.source'));

            try {
                foreach (DB::table('admins')->get() as $admin) {
                    foreach ((array) $admin as $column => $value) {
                        if ($value && preg_match('/mobile|phone/i', $column)) {
                            $numbers[] = MobileNumber::national($value);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not read staff numbers for listing claims', ['error' => $e->getMessage()]);
            }

            return array_values(array_unique(array_filter($numbers, fn ($n) => strlen($n) === 10)));
        });
    }

    /** Note that a staff number was used to log in, without moving anything. */
    private static function hold(User $user, string $digits, string $reason): void
    {
        foreach (self::listingsWith($digits) as $listing) {
            if ((int) $listing->user_id !== (int) $user->id) {
                self::record($listing, $user, $digits, 'held', $reason);
            }
        }
    }

    private static function record($listing, User $user, string $phone, string $status, ?string $reason, $previous = null): void
    {
        DB::table('listing_claims')->updateOrInsert(
            ['listing_id' => $listing->id, 'user_id' => $user->id],
            [
                'previous_user_id' => $previous ?? $listing->user_id,
                'phone'            => $phone,
                'status'           => $status,
                'reason'           => $reason,
                'updated_at'       => now(),
                'created_at'       => now(),
            ]
        );
    }
}
