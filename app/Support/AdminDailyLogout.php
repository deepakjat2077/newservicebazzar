<?php

namespace App\Support;

use Carbon\Carbon;

/** The once-a-day admin logout time (config/admin_session.php). */
class AdminDailyLogout
{
    /** The most recent cut-off that has already passed (today's, or yesterday's if today's is still ahead). */
    public static function lastCutoff(?Carbon $now = null): Carbon
    {
        $now = $now ?: now();
        $today = static::cutoffOn($now);

        return $today->lte($now) ? $today : $today->subDay();
    }

    /** The next cut-off still ahead. */
    public static function nextCutoff(?Carbon $now = null): Carbon
    {
        $now = $now ?: now();
        $today = static::cutoffOn($now);

        return $today->gt($now) ? $today : $today->addDay();
    }

    protected static function cutoffOn(Carbon $day): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', (string) config('admin_session.daily_logout_at', '19:00')) + [1 => 0]);

        return $day->copy()->setTime($h, $m, 0);
    }
}
