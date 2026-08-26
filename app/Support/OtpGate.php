<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * The OTP check standing in front of the enquiry forms.
 *
 * Every enquiry form on the site asks for a mobile number, and that number is
 * the whole value of the lead — a business owner pays attention to these and
 * rings them back. A number nobody proved is a wasted call, so a code goes to
 * it over WhatsApp and the enquiry is only accepted once that code comes back.
 *
 * State is keyed by the visitor's session id but kept in the cache, not in the
 * session payload. The site fires tracking beacons alongside every page, and
 * two requests that both loaded the session before either wrote it back means
 * the later write wins wholesale — which was enough to restore a proof this
 * gate had just spent and let a second enquiry through on one code. A cache
 * entry is written on its own and cannot be rolled back that way.
 *
 * One OTP per enquiry, deliberately. A proof is spent the moment an enquiry is
 * accepted (consume()), and being signed in — even with the very number typed
 * into the form — does not stand in for it. That is the site owner's call:
 * every lead that reaches a business is to have a code answered on that
 * handset first. It costs a WhatsApp message per enquiry; loosen it by giving
 * a proof a longer life below and not consuming it.
 */
class OtpGate
{
    /** How long a code stays usable. */
    public const CODE_TTL_MINUTES = 10;

    /**
     * How long an unused proof stays good.
     *
     * Short, because it is single-use: it covers the gap between answering the
     * code and the enquiry going through — including a second try after the
     * form comes back with "the name field is required", which must not cost
     * the visitor another OTP.
     */
    public const VERIFIED_TTL_MINUTES = 15;

    /** Wrong guesses allowed before the code is burned. */
    public const MAX_ATTEMPTS = 5;

    /** Seconds before the same number may be sent another code. */
    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Codes one session may ask for per hour. The route throttle already caps
     * requests per IP; this caps the damage a single session can do to the
     * WhatsApp bill by cycling through numbers.
     */
    public const SEND_BUDGET_PER_HOUR = 8;

    /** Everything this visitor's gate stores hangs off one prefix. */
    protected static function scope(): string
    {
        return 'otp_gate:' . session()->getId();
    }

    protected static function store()
    {
        return Cache::store(config('cache.default'));
    }

    /** One number, one key, however the visitor typed it. */
    public static function key($mobile): string
    {
        return MobileNumber::national($mobile);
    }

    public static function isVerified($mobile): bool
    {
        $key = self::key($mobile);

        if ($key === '') {
            return false;
        }

        return (bool) self::store()->get(self::scope() . ':verified:' . $key);
    }

    public static function markVerified($mobile): void
    {
        self::store()->put(
            self::scope() . ':verified:' . self::key($mobile),
            true,
            now()->addMinutes(self::VERIFIED_TTL_MINUTES)
        );

        self::store()->forget(self::scope() . ':pending');
    }

    /**
     * Spend the proof.
     *
     * Called once an enquiry has actually been accepted, so the next one asks
     * for a fresh code rather than riding on this one.
     */
    public static function consume($mobile): void
    {
        self::store()->forget(self::scope() . ':verified:' . self::key($mobile));
    }

    /** Seconds left before another code may be sent to this number, 0 if now. */
    public static function cooldownRemaining($mobile): int
    {
        $pending = self::pending($mobile);

        if (!$pending) {
            return 0;
        }

        $next = $pending['sent_at'] + self::RESEND_COOLDOWN_SECONDS;

        return max(0, $next - now()->timestamp);
    }

    /** False once a session has asked for more codes than it plausibly needs. */
    public static function withinSendBudget(): bool
    {
        return count(self::recentSends()) < self::SEND_BUDGET_PER_HOUR;
    }

    public static function challenge($mobile, string $code): void
    {
        self::store()->put(self::scope() . ':pending', [
            'mobile'     => self::key($mobile),
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES)->timestamp,
            'sent_at'    => now()->timestamp,
            'attempts'   => 0,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        $sends = self::recentSends();
        $sends[] = now()->timestamp;

        self::store()->put(self::scope() . ':sends', $sends, now()->addHour());
    }

    /**
     * Check a code.
     *
     * @return string one of: ok, none, expired, too_many, invalid
     */
    public static function attempt($mobile, string $code): string
    {
        $pending = self::pending($mobile);

        if (!$pending) {
            return 'none';
        }

        if ($pending['expires_at'] < now()->timestamp) {
            self::store()->forget(self::scope() . ':pending');

            return 'expired';
        }

        if ($pending['attempts'] >= self::MAX_ATTEMPTS) {
            self::store()->forget(self::scope() . ':pending');

            return 'too_many';
        }

        if (!hash_equals((string) $pending['code'], $code)) {
            $pending['attempts']++;

            self::store()->put(
                self::scope() . ':pending',
                $pending,
                now()->addSeconds(max(1, $pending['expires_at'] - now()->timestamp))
            );

            return 'invalid';
        }

        self::markVerified($mobile);

        return 'ok';
    }

    /** Guesses left on the pending code, for the "attempts left" message. */
    public static function attemptsLeft($mobile): int
    {
        $pending = self::pending($mobile);

        return $pending ? max(0, self::MAX_ATTEMPTS - $pending['attempts']) : 0;
    }

    /** The live challenge, but only if it belongs to this number. */
    protected static function pending($mobile): ?array
    {
        $pending = self::store()->get(self::scope() . ':pending');

        if (!is_array($pending) || ($pending['mobile'] ?? null) !== self::key($mobile)) {
            return null;
        }

        return $pending;
    }

    protected static function recentSends(): array
    {
        $cutoff = now()->subHour()->timestamp;

        return array_values(array_filter(
            (array) self::store()->get(self::scope() . ':sends', []),
            fn ($at) => $at > $cutoff
        ));
    }
}
