<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\AnalyticsController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Flags automated traffic that the request-time filters cannot catch.
 *
 * TrafficFilter works off the request alone — user agent, IP, headers —
 * which only stops bots that declare themselves. The traffic that actually
 * dominates this site's numbers presents a stock desktop Chrome UA from a
 * different residential IP on every hit, so no request-time rule can
 * separate it from a first-time human on its own.
 *
 * What does separate it is behaviour after the fact. tracking.js fires a
 * close beacon from both `pagehide` and `visibilitychange`, so any real
 * browser leaving a page reports back. A session that opened one page,
 * never reported back, never triggered a deliberate interaction, and whose
 * visitor never returned is a page render with nobody behind it.
 *
 * Rows are flagged, never deleted — is_bot is what every analytics query
 * already filters on (TrafficFilter::applyCleanScope), so flagging is
 * enough to clean the dashboard, and `--undo` puts everything back if a
 * rule ever proves too aggressive.
 */
class MarkBotTraffic extends Command
{
    protected $signature = 'analytics:mark-bots
                            {--since= : Start of the window to judge (default: now minus analytics.sweep.lookback_hours)}
                            {--until= : End of the window (default: now minus the grace period)}
                            {--grace= : Minutes a session is left alone before being judged}
                            {--undo : Clear is_bot on the window instead of setting it}
                            {--dry-run : Report what would change without writing}';

    protected $description = 'Flag automated sessions (and their page views, events and visitors) as bot traffic';

    public function handle(): int
    {
        $grace = (int) ($this->option('grace') ?? config('analytics.sweep.grace_minutes', 45));

        $until = $this->option('until')
            ? Carbon::parse($this->option('until'))
            : now()->subMinutes($grace);

        $since = $this->option('since')
            ? Carbon::parse($this->option('since'))
            : now()->subHours((int) config('analytics.sweep.lookback_hours', 48));

        if ($since->gte($until)) {
            $this->error('Nothing to sweep: --since is not before --until.');

            return self::FAILURE;
        }

        $this->line(sprintf(
            '%s window %s .. %s',
            $this->option('undo') ? 'Clearing' : 'Sweeping',
            $since->toDateTimeString(),
            $until->toDateTimeString()
        ));

        if ($this->option('undo')) {
            $counts = $this->undo($since, $until);
        } else {
            $counts = $this->sweep($since, $until);
        }

        foreach ($counts as $label => $n) {
            $this->line(sprintf('  %-18s %s', $label, number_format($n)));
        }

        // buildStats() results are memoised per range for five minutes; a
        // sweep that just moved rows out of the reported figures must not
        // leave the admin staring at the old numbers until that expires.
        // Bumping the stamp the analytics cache key is built from orphans
        // those entries without touching anything else on this shared
        // Redis store.
        if (!$this->option('dry-run') && array_sum($counts) > 0) {
            Cache::forever(AnalyticsController::SWEEP_STAMP_KEY, now()->getTimestamp());
        }

        return self::SUCCESS;
    }

    /**
     * A session is left alone if anything about it required a human:
     *
     *  - ended_at        the close beacon fired, so the page was really left
     *  - page_view_count more than the landing page was viewed
     *  - user_id         it belongs to a logged-in account
     *  - an event that is not in analytics.passive_event_types (listing_view
     *    fires automatically on page load and proves nothing)
     *  - the visitor has been here before (total_visits > 1)
     *
     * Everything else in the window is flagged.
     */
    protected function sweep(Carbon $since, Carbon $until): array
    {
        $passive = config('analytics.passive_event_types', ['listing_view']);

        $botSessions = DB::table('visitor_sessions as s')
            ->whereBetween('s.started_at', [$since, $until])
            ->where('s.is_bot', false)
            ->whereNull('s.ended_at')
            ->where('s.page_view_count', '<=', 1)
            ->whereNull('s.user_id')
            ->whereNotExists(function ($q) use ($passive) {
                $q->select(DB::raw(1))
                    ->from('analytics_events as e')
                    ->whereColumn('e.session_uuid', 's.session_uuid')
                    ->whereNotIn('e.event_type', $passive);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('visitors as v')
                    ->whereColumn('v.visitor_id', 's.visitor_id')
                    ->where('v.total_visits', '>', 1);
            });

        $counts = ['sessions' => (clone $botSessions)->count()];

        if ($this->option('dry-run')) {
            $this->warn('  (dry run — nothing written)');

            return $counts;
        }

        // Flag the sessions first, then let the flagged set drive the child
        // tables. Chunked rather than issuing one enormous
        // UPDATE ... WHERE NOT EXISTS, which on a table this size holds row
        // locks for long enough to stall live beacon writes.
        //
        // chunkById, not chunk: the loop writes the very column the query
        // filters on, so each batch leaves the result set as soon as it is
        // flagged. Offset paging would then step straight over the batch
        // that shifted down into its place and reach only every other one.
        $sessionUuids = [];
        (clone $botSessions)->select('s.id', 's.session_uuid', 's.visitor_id')
            ->chunkById(2000, function ($rows) use (&$sessionUuids) {
                $uuids = $rows->pluck('session_uuid')->all();
                $sessionUuids = array_merge($sessionUuids, $uuids);

                DB::table('visitor_sessions')->whereIn('session_uuid', $uuids)->update(['is_bot' => true]);
            }, 's.id', 'id');

        $counts['page_views'] = 0;
        $counts['events'] = 0;

        foreach (array_chunk($sessionUuids, 2000) as $chunk) {
            $counts['page_views'] += DB::table('page_views')
                ->whereIn('session_uuid', $chunk)->where('is_bot', false)->update(['is_bot' => true]);

            $counts['events'] += DB::table('analytics_events')
                ->whereIn('session_uuid', $chunk)->where('is_bot', false)->update(['is_bot' => true]);
        }

        // A visitor is only bot traffic if it has no surviving human
        // session at all — a returning visitor whose latest session got
        // flagged still had a real one earlier and must stay counted.
        $counts['visitors'] = DB::table('visitors as v')
            ->whereBetween('v.last_visit_at', [$since, $until])
            ->where('v.is_bot', false)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('visitor_sessions as s')
                    ->whereColumn('s.visitor_id', 'v.visitor_id');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('visitor_sessions as s')
                    ->whereColumn('s.visitor_id', 'v.visitor_id')
                    ->where('s.is_bot', false);
            })
            ->update(['v.is_bot' => true]);

        return $counts;
    }

    protected function undo(Carbon $since, Carbon $until): array
    {
        if ($this->option('dry-run')) {
            $this->warn('  (dry run — nothing written)');

            return [];
        }

        return [
            'sessions' => DB::table('visitor_sessions')->whereBetween('started_at', [$since, $until])->update(['is_bot' => false]),
            'page_views' => DB::table('page_views')->whereBetween('opened_at', [$since, $until])->update(['is_bot' => false]),
            'events' => DB::table('analytics_events')->whereBetween('created_at', [$since, $until])->update(['is_bot' => false]),
            'visitors' => DB::table('visitors')->whereBetween('last_visit_at', [$since, $until])->update(['is_bot' => false]),
        ];
    }
}
