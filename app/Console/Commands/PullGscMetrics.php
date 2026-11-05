<?php

namespace App\Console\Commands;

use App\Support\GoogleIndexing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pull daily Google Search Console performance (queries + pages) into
 * gsc_metrics so the admin Search Console page can report trends and
 * find "page-2" / low-CTR click opportunities. Uses the same service
 * account as seo:google-index — no OAuth, works on both servers.
 *
 * GSC data lags ~2-3 days, so we backfill the recent window and re-pull
 * the last few days (their numbers keep settling). Rows older than
 * --keep days are pruned.
 */
class PullGscMetrics extends Command
{
    protected $signature = 'seo:gsc-pull
                            {--days=5 : How many recent days (ending 2 days ago) to pull/refresh}
                            {--rows=5000 : Max query/page rows to keep per day}
                            {--keep=120 : Prune gsc_metrics rows older than this many days}';

    protected $description = 'Pull daily Search Console queries & pages into gsc_metrics';

    public function handle(): int
    {
        if (!GoogleIndexing::configured()) {
            $this->error('Google service account key missing — cannot pull GSC data.');
            return self::FAILURE;
        }

        $rowCap = max(100, (int) $this->option('rows'));
        $days   = max(1, (int) $this->option('days'));

        // GSC finalises data ~2 days late; pull the window ending 2 days ago.
        $end = now()->subDays(2)->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $day  = $end->copy()->subDays($i);
            $date = $day->toDateString();

            try {
                $this->pullTotal($date);
                $this->pullDay($date, 'query', $rowCap);
                $this->pullDay($date, 'page', $rowCap);
                $this->info("pulled {$date}");
            } catch (\Throwable $e) {
                $this->error("{$date}: " . $e->getMessage());
                // Stop on quota/permission errors; a transient one just skips the day.
                if (str_contains($e->getMessage(), '403') || str_contains($e->getMessage(), '429')) {
                    return self::FAILURE;
                }
            }
        }

        $cutoff  = now()->subDays(max(1, (int) $this->option('keep')))->toDateString();
        $deleted = DB::table('gsc_metrics')->where('date', '<', $cutoff)->delete();
        if ($deleted) {
            $this->line("pruned {$deleted} rows older than {$cutoff}");
        }

        cache()->put('gsc:last-pull', now()->toDateTimeString(), now()->addDays(7));

        return self::SUCCESS;
    }

    /** Pull one day's true site total (no dimension) so headline numbers match GSC. */
    private function pullTotal(string $date): void
    {
        $rows = GoogleIndexing::searchAnalytics($date, $date, [], 1, 0);
        $r    = $rows[0] ?? null;

        DB::table('gsc_metrics')->where('date', $date)->where('dimension', 'total')->delete();

        if ($r) {
            DB::table('gsc_metrics')->insert([
                'date'        => $date,
                'dimension'   => 'total',
                'value'       => 'total',
                'value_hash'  => sha1('total'),
                'clicks'      => (int) ($r['clicks'] ?? 0),
                'impressions' => (int) ($r['impressions'] ?? 0),
                'position'    => round((float) ($r['position'] ?? 0), 2),
            ]);
        }
    }

    /** Pull one day of one dimension, replacing that day's rows for it. */
    private function pullDay(string $date, string $dimension, int $rowCap): void
    {
        $order = [['fieldName' => 'impressions', 'sortOrder' => 'DESCENDING']];
        $all   = [];
        $start = 0;
        $page  = min(25000, $rowCap);

        do {
            $rows = GoogleIndexing::searchAnalytics($date, $date, [$dimension], $page, $start, $order);
            $all  = array_merge($all, $rows);
            $start += $page;
        } while (count($rows) === $page && count($all) < $rowCap);

        $all = array_slice($all, 0, $rowCap);

        DB::transaction(function () use ($date, $dimension, $all) {
            DB::table('gsc_metrics')->where('date', $date)->where('dimension', $dimension)->delete();

            foreach (array_chunk($all, 500) as $chunk) {
                $insert = [];
                foreach ($chunk as $r) {
                    $value = (string) ($r['keys'][0] ?? '');
                    if ($value === '') {
                        continue;
                    }
                    $insert[] = [
                        'date'        => $date,
                        'dimension'   => $dimension,
                        'value'       => mb_substr($value, 0, 500),
                        'value_hash'  => sha1($value),
                        'clicks'      => (int) ($r['clicks'] ?? 0),
                        'impressions' => (int) ($r['impressions'] ?? 0),
                        'position'    => round((float) ($r['position'] ?? 0), 2),
                    ];
                }
                if ($insert) {
                    DB::table('gsc_metrics')->insert($insert);
                }
            }
        });
    }
}
