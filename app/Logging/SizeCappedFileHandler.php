<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\LogRecord;

/**
 * A daily log handler that also refuses to let any single file grow past a
 * hard byte ceiling.
 *
 * The daily driver on its own only rotates on a date change. That is fine for
 * a normal day, but one loop — a failing DB connection hit by every crawler
 * request, a warning inside a foreach — can write hundreds of megabytes
 * before midnight. On shared hosting that fills the account's disk quota, and
 * a full disk takes the site down far harder than the original error did.
 *
 * When today's file reaches the ceiling it is truncated rather than rotated
 * to a second file: rotating would simply mean two files of the ceiling size
 * on disk, which is not what a disk cap means. The newest lines are the ones
 * worth keeping, so the oldest are the ones dropped, and a marker line is
 * written so nobody mistakes a truncated log for a quiet one.
 */
class SizeCappedFileHandler extends RotatingFileHandler
{
    /** Hard ceiling for one log file. */
    public const MAX_BYTES = 10 * 1024 * 1024;

    protected function write(LogRecord $record): void
    {
        $this->capCurrentFile();

        parent::write($record);
    }

    protected function capCurrentFile(): void
    {
        $path = $this->getUrl();

        if (!$path || !is_file($path)) {
            return;
        }

        // filesize() is served from PHP's stat cache, which would keep
        // returning the size from before this request's own writes.
        clearstatcache(true, $path);

        if (filesize($path) < static::MAX_BYTES) {
            return;
        }

        // close() releases the handler's own stream first — truncating a file
        // that is still open in append mode leaves the write offset where it
        // was and pads the gap with null bytes.
        $this->close();

        $marker = '[' . date('Y-m-d H:i:s') . '] TRUNCATED: log reached the '
            . (int) (static::MAX_BYTES / 1048576) . 'MB cap, earlier lines were dropped.'
            . PHP_EOL;

        file_put_contents($path, $marker, LOCK_EX);
    }
}
