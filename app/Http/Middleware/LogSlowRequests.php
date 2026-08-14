<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records requests that took too long, with where the time actually went.
 *
 * A request that outlives LiteSpeed's patience never reaches the app's
 * error handling: the connection is dropped in front of PHP, no exception
 * is thrown, and the browser is shown a 408 the app never produced. The
 * admin error log in bootstrap/app.php only fires on HttpException, so
 * those requests leave nothing behind and "which page is timing out" has
 * been unanswerable after the fact.
 *
 * This measures instead of waiting to be told. PHP usually keeps running
 * after the connection drops, so the line still gets written even for the
 * request the admin saw fail.
 *
 * The db_ms/other_ms split is the point of the whole thing: a slow request
 * is either waiting on our own database or waiting on somebody else's API,
 * and the fix is completely different in each case.
 */
class LogSlowRequests
{
    /** Anything at or past this many milliseconds gets a line. */
    protected const THRESHOLD_MS = 3000;

    public function handle(Request $request, Closure $next)
    {
        $startedAt = microtime(true);

        $queries = 0;
        $queryMs = 0.0;
        $worstMs = 0.0;
        $worstSql = null;

        // The whole query log is never retained — only the running totals and
        // the single worst statement, so a page with 500 queries costs a few
        // hundred bytes rather than a few megabytes.
        DB::listen(function ($query) use (&$queries, &$queryMs, &$worstMs, &$worstSql) {
            $queries++;
            $queryMs += $query->time;

            if ($query->time > $worstMs) {
                $worstMs = $query->time;
                $worstSql = $query->sql;
            }
        });

        $response = $next($request);

        $elapsedMs = (microtime(true) - $startedAt) * 1000;

        if ($elapsedMs < self::THRESHOLD_MS) {
            return $response;
        }

        // Whatever is left after the database is PHP itself plus any
        // outbound HTTP call — an OpenAI or Gupshup round trip lands here.
        $otherMs = max(0, $elapsedMs - $queryMs);

        // The "slow" channel (config/logging.php) rotates daily *and* truncates
        // at a 10MB hard cap — on a bad day every request can be a slow one,
        // and an uncapped diagnostics file is how a shared host runs out of
        // disk. Defined in config rather than built inline here so the cap
        // cannot be bypassed by a caller that forgets it.
        Log::channel('slow')->warning('SLOW REQUEST', [
            'uri'         => $request->path(),
            'method'      => $request->method(),
            'total_ms'    => (int) $elapsedMs,
            'db_ms'       => (int) $queryMs,
            'other_ms'    => (int) $otherMs,
            'queries'     => $queries,
            'slowest_sql' => $worstSql === null
                ? null
                : (int) $worstMs . 'ms: ' . mb_substr($worstSql, 0, 300),
            'admin_id'    => optional(auth()->guard('admin')->user())->id,
            'peak_mb'     => (int) round(memory_get_peak_usage(true) / 1048576),
        ]);

        return $response;
    }
}
