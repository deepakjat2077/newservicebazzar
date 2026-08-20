<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\AnalyticsController;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Deletes rows already flagged is_bot.
 *
 * MarkBotTraffic only flags, because the behavioural rules it applies are
 * heuristics and a flag is reversible. This command is the second step,
 * once those rows have had a day to be sanity-checked: at roughly seventy
 * thousand rows a day, keeping automated traffic forever costs storage and
 * slows every aggregate the dashboard runs, for data nobody will ever look
 * at.
 *
 * `--keep-days` is the safety margin — a row is only deleted once it has
 * been flagged for that long, so a bad sweep is always still undoable at
 * the point anyone would notice it.
 */
class PruneBotTraffic extends Command
{
    protected $signature = 'analytics:prune-bots
                            {--keep-days=1 : Only delete bot rows older than this many days}
                            {--all : Ignore --keep-days and delete every flagged row}
                            {--dry-run : Report what would be deleted without deleting}';

    protected $description = 'Delete analytics rows flagged as bot traffic';

    /** Each table paired with the timestamp column its age is judged by. */
    protected array $tables = [
        'page_views' => 'opened_at',
        'analytics_events' => 'created_at',
        'visitor_sessions' => 'started_at',
        'visitors' => 'last_visit_at',
    ];

    public function handle(): int
    {
        $cutoff = $this->option('all')
            ? null
            : now()->subDays((int) $this->option('keep-days'));

        $this->line($cutoff
            ? 'Pruning bot rows older than ' . $cutoff->toDateTimeString()
            : 'Pruning every flagged bot row');

        $deleted = 0;

        foreach ($this->tables as $table => $dateColumn) {
            $n = $this->prune($table, $dateColumn, $cutoff);
            $deleted += $n;

            $this->line(sprintf('  %-18s %s', $table, number_format($n)));
        }

        if ($deleted > 0 && !$this->option('dry-run')) {
            Cache::forever(AnalyticsController::SWEEP_STAMP_KEY, now()->getTimestamp());
        }

        return self::SUCCESS;
    }

    protected function prune(string $table, string $dateColumn, ?Carbon $cutoff): int
    {
        $query = DB::table($table)->where('is_bot', true);

        if ($cutoff) {
            $query->where($dateColumn, '<', $cutoff);
        }

        if ($this->option('dry-run')) {
            return $query->count();
        }

        // Deleted in batches rather than as one statement: a single DELETE
        // over tens of thousands of rows builds an undo log big enough to
        // hold locks across the table while live beacons are still writing
        // to it. limit() also lets each statement finish quickly enough
        // that replication never falls behind.
        $deleted = 0;

        do {
            $n = (clone $query)->limit(5000)->delete();
            $deleted += $n;
        } while ($n > 0);

        return $deleted;
    }
}
