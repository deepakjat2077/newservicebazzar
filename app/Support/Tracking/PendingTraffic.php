<?php

namespace App\Support\Tracking;

use App\Models\VisitorSession;
use Illuminate\Support\Facades\Cache;

/**
 * Holds a session's beacons in Redis until something proves a human was
 * behind them, and only then lets them through to MySQL.
 *
 * The problem this solves is that the signal separating this site's bot
 * flood from its visitors is behavioural — the pool renders one page from
 * a stock desktop Chrome UA on a fresh residential IP and discards the
 * context, so nothing about the request itself gives it away (see
 * TrafficFilter). Judging it therefore has to happen after the fact, and
 * the pipeline used to do that by writing every beacon, flagging the
 * automated ones later (MarkBotTraffic) and deleting them a day after
 * that (PruneBotTraffic) — roughly 35,000 sessions a day written purely
 * to be thrown away, plus every aggregate on the dashboard reading around
 * them in the meantime.
 *
 * Inverting it costs nothing in accuracy and keeps the data out entirely:
 * the first page view of an unknown session is parked here instead of
 * being written, and it is released — in arrival order, as though it had
 * been written all along — the moment that session does something an
 * unattended render never does:
 *
 *   - the close beacon fires (pagehide/visibilitychange, so a real
 *     browser really left the page)
 *   - a second page view arrives
 *   - a deliberate event arrives (anything outside
 *     analytics.passive_event_types, which fire on page load and prove
 *     nothing)
 *   - the visitor is logged in, or has a session on record from before
 *
 * Anything that never earns its release simply expires out of Redis after
 * analytics.hold.minutes and is never stored anywhere.
 *
 * The is_bot flag and its sweep stay where they are: they still catch
 * whatever slips past this on the way in, and are what the older rows
 * already in the tables are judged by.
 */
class PendingTraffic
{
    public const TYPE_PAGE_VIEW_OPEN = 'page_view_open';
    public const TYPE_PAGE_VIEW_CLOSE = 'page_view_close';
    public const TYPE_EVENT = 'event';

    protected static function holdKey(string $sessionUuid): string
    {
        return 'tracking:held:' . $sessionUuid;
    }

    protected static function liveKey(string $sessionUuid): string
    {
        return 'tracking:live:' . $sessionUuid;
    }

    /**
     * Whether this session has already been released, in which case its
     * beacons go straight to the database like any other write.
     *
     * The Redis flag is only a cache of "a row for this session exists";
     * on a miss it is settled against the table itself, so a cache
     * eviction (or a session that predates this class) downgrades to one
     * indexed lookup rather than parking a visitor who is already being
     * recorded.
     */
    public static function isLive(?string $sessionUuid): bool
    {
        if (!$sessionUuid) {
            return false;
        }

        if (Cache::get(static::liveKey($sessionUuid))) {
            return true;
        }

        if (VisitorSession::where('session_uuid', $sessionUuid)->exists()) {
            static::markLive($sessionUuid);

            return true;
        }

        return false;
    }

    public static function markLive(string $sessionUuid): void
    {
        Cache::put(static::liveKey($sessionUuid), 1, now()->addHours(12));
    }

    /**
     * Park a beacon. Held items keep their arrival order, so a release
     * replays them in the sequence the writers already expect (the open
     * that creates the session and its visitor, then everything that
     * refers back to them).
     */
    public static function hold(string $sessionUuid, string $type, array $data): void
    {
        static::withLock($sessionUuid, function () use ($sessionUuid, $type, $data) {
            $held = Cache::get(static::holdKey($sessionUuid), []);
            $held[] = ['type' => $type, 'data' => $data];

            Cache::put(static::holdKey($sessionUuid), $held, static::ttl());
        });
    }

    /**
     * How many beacons are currently parked for this session, optionally
     * of one type only.
     *
     * The type matters for the caller that asks whether a page view is a
     * session's second: beacons are not ordered across endpoints, so the
     * listing_view event belonging to the *first* page view can easily be
     * parked before the open beacon it accompanies. Counting everything
     * held would read that as a second page view and release a session
     * that has still only rendered one page.
     */
    public static function heldCount(?string $sessionUuid, ?string $type = null): int
    {
        if (!$sessionUuid) {
            return 0;
        }

        $held = Cache::get(static::holdKey($sessionUuid), []);

        if ($type === null) {
            return count($held);
        }

        return count(array_filter($held, fn ($item) => ($item['type'] ?? null) === $type));
    }

    /**
     * Mark the session live and hand back everything parked for it, so the
     * caller can write it before writing whatever proved the session human.
     */
    public static function release(?string $sessionUuid): array
    {
        if (!$sessionUuid) {
            return [];
        }

        return static::withLock($sessionUuid, function () use ($sessionUuid) {
            $held = Cache::get(static::holdKey($sessionUuid), []);

            Cache::forget(static::holdKey($sessionUuid));
            static::markLive($sessionUuid);

            return $held;
        });
    }

    protected static function ttl(): \DateTimeInterface
    {
        return now()->addMinutes((int) config('analytics.hold.minutes', 60));
    }

    /**
     * Both hold() and release() are read-modify-write over one key, and
     * the tracking jobs run on the "sync" connection by default — i.e.
     * inside the web request — so two beacons from the same session can be
     * in here at once. Without the lock, an open arriving alongside the
     * close that releases it can be written to a key that has just been
     * forgotten and sit there until it expires, losing a real visitor.
     */
    protected static function withLock(string $sessionUuid, \Closure $callback)
    {
        $lock = Cache::lock('tracking:hold-lock:' . $sessionUuid, 5);

        try {
            $lock->block(3);
        } catch (\Throwable $e) {
            // A contended lock must not cost the beacon: fall through and
            // do the work unlocked rather than dropping it.
            return $callback();
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
