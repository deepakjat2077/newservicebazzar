<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

/**
 * Sends an outbound HTTP call again while the far end is only temporarily unhappy.
 *
 * 408, 429 and 500/502/503/504 all mean "ask me again in a moment", but
 * Laravel's ->retry() only fires on transport exceptions — a 503 body counts as
 * a completed request, so it came straight back and ended up on the admin's
 * screen. This wraps the call instead: same request, backed off and repeated,
 * and the caller only hears about it once every attempt is spent.
 */
class HttpRetry
{
    /** Upstream statuses that are worth another attempt. */
    public const RETRYABLE_STATUSES = [408, 409, 425, 429, 500, 502, 503, 504];

    /** Longest we ever wait between two attempts, whatever Retry-After asks for. */
    public const MAX_DELAY_MS = 8000;

    public static function retryable(?Response $response): bool
    {
        return $response !== null
            && in_array($response->status(), self::RETRYABLE_STATUSES, true);
    }

    /**
     * Run $send until it gives a usable answer or the attempts run out.
     *
     * A connection failure and a 503 are treated the same way: both get backed
     * off and tried again. Anything else — a real 400, a 401, a success — comes
     * back immediately, because repeating it would only waste the caller's time.
     *
     * $budgetSeconds bounds how long the caller is willing to sit here. A new
     * attempt is only started while the budget still has room, so a provider
     * that refuses in a second gets retried, while one that burns the full
     * timeout does not — the alternative is a request so long that our own
     * proxy answers 408, which is the error we were trying to get rid of.
     * The effective ceiling is the budget plus one attempt's timeout.
     *
     * @param  callable(int):Response  $send  receives the 1-based attempt number
     * @return array{response: ?Response, exception: ?\Throwable, attempts: int}
     */
    public static function send(
        callable $send,
        int $tries = 3,
        int $baseDelayMs = 800,
        string $label = 'http',
        ?float $budgetSeconds = null
    ): array {
        $tries = max(1, $tries);
        $startedAt = microtime(true);
        $response = null;
        $exception = null;
        $made = 0;

        for ($attempt = 1; $attempt <= $tries; $attempt++) {
            $exception = null;
            $made = $attempt;

            try {
                $response = $send($attempt);

                if (!self::retryable($response)) {
                    return [
                        'response'  => $response,
                        'exception' => null,
                        'attempts'  => $attempt,
                    ];
                }
            } catch (ConnectionException $connectionException) {
                $response = null;
                $exception = $connectionException;
            }

            if ($attempt === $tries) {
                break;
            }

            $delay = self::delayFor($attempt, $baseDelayMs, $response);
            $elapsed = microtime(true) - $startedAt;

            if ($budgetSeconds !== null && ($elapsed + ($delay / 1000)) >= $budgetSeconds) {
                Log::warning('Upstream call out of time budget', [
                    'label'      => $label,
                    'attempt'    => $attempt . ' of ' . $tries,
                    'status'     => $response ? $response->status() : null,
                    'elapsed_ms' => (int) ($elapsed * 1000),
                    'budget_ms'  => (int) ($budgetSeconds * 1000),
                ]);

                break;
            }

            Log::warning('Upstream call retrying', [
                'label'   => $label,
                'attempt' => $attempt . ' of ' . $tries,
                'status'  => $response ? $response->status() : null,
                'error'   => $exception ? $exception->getMessage() : null,
                'wait_ms' => $delay,
            ]);

            usleep($delay * 1000);
        }

        return [
            'response'  => $response,
            'exception' => $exception,
            'attempts'  => $made,
        ];
    }

    /**
     * How long to hold off before the next attempt.
     *
     * Exponential, with the server's own Retry-After winning whenever it sends
     * one, plus a little jitter so a batch of parallel calls does not all come
     * back at the same instant and get throttled together.
     */
    public static function delayFor(int $attempt, int $baseDelayMs, ?Response $response = null): int
    {
        $retryAfter = $response ? trim((string) $response->header('Retry-After')) : '';

        if ($retryAfter !== '' && is_numeric($retryAfter)) {
            return (int) min(self::MAX_DELAY_MS, max(0, (float) $retryAfter) * 1000);
        }

        $delay = $baseDelayMs * (2 ** max(0, $attempt - 1));
        $delay += random_int(0, (int) max(1, $baseDelayMs / 2));

        return (int) min(self::MAX_DELAY_MS, $delay);
    }

    /**
     * A status code that is safe to hand to the browser.
     *
     * The admin UI must never be shown a raw 408 or 503 from a third party: it
     * reads as "servicebazzar is down" when it actually means "the message
     * vendor blinked". Everything transient becomes a plain 502 with a readable
     * message; a genuine rejection stays a 400 so the operator can tell the
     * difference.
     */
    public static function clientStatus(?int $upstreamStatus): int
    {
        if ($upstreamStatus === null) {
            return 502;
        }

        $isTransient = in_array($upstreamStatus, self::RETRYABLE_STATUSES, true);

        if (!$isTransient && $upstreamStatus >= 400 && $upstreamStatus < 500) {
            return 400;
        }

        return 502;
    }
}
