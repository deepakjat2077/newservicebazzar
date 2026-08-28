<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * The OTP challenge behind the mobile app's login.
 *
 * OtpGate keys its state on the visitor's session id, which the website has
 * and a native app does not. This is the same idea keyed on the phone number
 * itself, so it works for a stateless, token-authenticated client: send a code
 * to a number, then check the code that comes back.
 *
 * State lives in the cache (not a table) so an expired challenge cleans itself
 * up. Everything is single-writer per key, so the concurrent-session write
 * race that pushed OtpGate off the session payload cannot happen here.
 *
 * @see \App\Support\OtpGate  the website twin this mirrors.
 */
class ApiOtpStore
{
    /** How long a code stays usable. */
    public const CODE_TTL_MINUTES = 10;

    /** Wrong guesses allowed before the code is burned. */
    public const MAX_ATTEMPTS = 5;

    /** Seconds before the same number may be sent another code. */
    public const RESEND_COOLDOWN_SECONDS = 60;

    /** Codes one number may be sent per hour, to cap the WhatsApp bill. */
    public const SEND_BUDGET_PER_HOUR = 6;

    protected static function store()
    {
        return Cache::store(config('cache.default'));
    }

    /** One number, one key, however the app formatted it. */
    protected static function key($mobile): string
    {
        return 'api_otp:' . MobileNumber::national($mobile);
    }

    protected static function sendsKey($mobile): string
    {
        return self::key($mobile) . ':sends';
    }

    /** Seconds left before another code may be sent to this number, 0 if now. */
    public static function cooldownRemaining($mobile): int
    {
        $pending = self::store()->get(self::key($mobile));

        if (!is_array($pending)) {
            return 0;
        }

        $next = $pending['sent_at'] + self::RESEND_COOLDOWN_SECONDS;

        return max(0, $next - now()->timestamp);
    }

    /** False once a number has been sent more codes than it plausibly needs. */
    public static function withinSendBudget($mobile): bool
    {
        return count(self::recentSends($mobile)) < self::SEND_BUDGET_PER_HOUR;
    }

    /** Store a fresh code for this number and record the send. */
    public static function challenge($mobile, string $code): void
    {
        self::store()->put(self::key($mobile), [
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES)->timestamp,
            'sent_at'    => now()->timestamp,
            'attempts'   => 0,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        $sends = self::recentSends($mobile);
        $sends[] = now()->timestamp;

        self::store()->put(self::sendsKey($mobile), $sends, now()->addHour());
    }

    /**
     * Check a code.
     *
     * @return string one of: ok, none, expired, too_many, invalid
     */
    public static function attempt($mobile, string $code): string
    {
        $pending = self::store()->get(self::key($mobile));

        if (!is_array($pending)) {
            return 'none';
        }

        if ($pending['expires_at'] < now()->timestamp) {
            self::store()->forget(self::key($mobile));

            return 'expired';
        }

        if ($pending['attempts'] >= self::MAX_ATTEMPTS) {
            self::store()->forget(self::key($mobile));

            return 'too_many';
        }

        if (!hash_equals((string) $pending['code'], $code)) {
            $pending['attempts']++;

            self::store()->put(
                self::key($mobile),
                $pending,
                now()->addSeconds(max(1, $pending['expires_at'] - now()->timestamp))
            );

            return 'invalid';
        }

        // One code, one login: burn it the moment it works so it can't be
        // replayed into a second token.
        self::store()->forget(self::key($mobile));

        return 'ok';
    }

    /** Guesses left on the pending code, for the "attempts left" message. */
    public static function attemptsLeft($mobile): int
    {
        $pending = self::store()->get(self::key($mobile));

        return is_array($pending) ? max(0, self::MAX_ATTEMPTS - $pending['attempts']) : 0;
    }

    protected static function recentSends($mobile): array
    {
        $cutoff = now()->subHour()->timestamp;

        return array_values(array_filter(
            (array) self::store()->get(self::sendsKey($mobile), []),
            fn ($at) => $at > $cutoff
        ));
    }
}
