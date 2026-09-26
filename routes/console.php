<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Automated traffic can only be judged once a session has had time to
 * behave like one (see App\Console\Commands\MarkBotTraffic), so it is
 * flagged on a sweep rather than at write time. Every fifteen minutes
 * keeps the admin dashboard's "today" figures honest without the sweep
 * ever having much to do.
 */
Schedule::command('analytics:mark-bots')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

/*
 * Then throw the flagged rows away, a day after they were flagged — long
 * enough that a misjudged sweep is still reversible when anyone would
 * notice, short enough that ~70k automated rows a day never accumulate.
 * Runs after the nightly listing rollup at 02:30, so that rollup still
 * sees a full day before anything is removed.
 */
Schedule::command('analytics:prune-bots --keep-days=1')
    ->dailyAt('03:15')
    ->withoutOverlapping()
    ->runInBackground();

// Badges bought through a sales package last only as long as the plan.
Schedule::command('sales:expire-perks')->hourly();
