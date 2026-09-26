<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Keeps storage/logs from being a slow disk leak.
 *
 * The daily driver already drops files older than LOG_DAILY_DAYS, but it
 * only rotates on date — a single runaway day (a loop that logs on every
 * request, a provider erroring on every retry) can grow one file without
 * limit until the next midnight write, and Monolog has no size trigger.
 *
 * So this covers the two cases date-rotation cannot:
 *   - a file past --max-mb is deleted whatever its date
 *   - a file past --hours is deleted even if nothing has written since,
 *     which is when the daily driver's own cleanup never runs
 */
class PruneLogs extends Command
{
    protected $signature = 'logs:prune
                            {--hours=168 : Delete log files last modified longer ago than this}
                            {--max-mb=100 : Delete any log file larger than this, regardless of age}
                            {--dry-run : List what would go without deleting it}';

    protected $description = 'Delete log files that are too old or too large';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $maxBytes = max(1, (int) $this->option('max-mb')) * 1024 * 1024;
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subHours($hours)->getTimestamp();
        $files = glob(storage_path('logs') . '/*.log') ?: [];

        $deleted = 0;
        $freed = 0;

        foreach ($files as $file) {
            $size = @filesize($file) ?: 0;
            $modified = @filemtime($file) ?: 0;

            $reason = null;

            if ($size > $maxBytes) {
                $reason = 'over ' . $this->option('max-mb') . 'MB (' . $this->mb($size) . ')';
            } elseif ($modified < $cutoff) {
                $reason = 'older than ' . $hours . 'h';
            }

            if ($reason === null) {
                continue;
            }

            $this->line(($dryRun ? '[dry-run] ' : '') . basename($file) . ' — ' . $reason);

            if (!$dryRun && @unlink($file)) {
                $deleted++;
                $freed += $size;
            }
        }

        $cacheRemoved = $this->pruneStaleCache($dryRun);

        $this->info($dryRun
            ? 'Dry run: nothing was deleted.'
            : $deleted . ' log file(s) deleted, ' . $this->mb($freed) . ' freed; '
              . $cacheRemoved . ' stale cache file(s) removed.');

        return self::SUCCESS;
    }

    /**
     * Delete expired file-cache entries.
     *
     * The file driver only drops an expired entry when something asks for it
     * again. CachePublicPages stores one entry per public URL, and crawlers
     * walk far more distinct URLs than they revisit — so most entries expire
     * and are never read again, leaving a file behind for each. On an account
     * with an inode allowance that is a slow leak, not a harmless one.
     *
     * Each cache file starts with the expiry as a unix timestamp, so this
     * reads ten bytes rather than unserializing anything.
     */
    protected function pruneStaleCache(bool $dryRun): int
    {
        $files = glob(storage_path('framework/cache/data') . '/*/*/*') ?: [];
        $now = time();
        $removed = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $handle = @fopen($file, 'r');

            if (!$handle) {
                continue;
            }

            $expiry = (int) fread($handle, 10);
            fclose($handle);

            // 0 means "forever" in Laravel's file store — leave those alone.
            if ($expiry === 0 || $expiry > $now) {
                continue;
            }

            if (!$dryRun && @unlink($file)) {
                $removed++;
            } elseif ($dryRun) {
                $removed++;
            }
        }

        return $removed;
    }

    private function mb(int $bytes): string
    {
        return round($bytes / 1048576, 1) . 'MB';
    }
}
