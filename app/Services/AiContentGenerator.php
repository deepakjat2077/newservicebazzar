<?php

namespace App\Services;

use App\Support\HttpRetry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the same AI endpoint the listing form's auto-content
 * uses (ListingController@aiGenerate). Ask for JSON, get an array back.
 *
 * Note the key is still called GEMINI_API_KEY / services.gemini.key for
 * historical reasons — the request actually goes to OpenAI, matching what
 * ListingController::callGemini() has always done.
 */
class AiContentGenerator
{
    public const ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    public const MODEL = 'gpt-4o-mini';

    /**
     * What the caller is told when the provider was only busy.
     *
     * Deliberately free of status codes: a 503 or a 408 from OpenAI is not
     * something the person adding a subcategory can act on, and every caller
     * already has usable fallback text to write instead.
     */
    public const BUSY_MESSAGE = 'AI service was busy; default text was used instead.';

    public function apiKey(): ?string
    {
        $key = trim((string) config('services.gemini.key'));

        return $key !== '' ? $key : null;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== null;
    }

    /**
     * Run a prompt that must answer with a JSON object.
     *
     * Never throws: callers use this to enrich a record, and a failed
     * enrichment must not take the save down with it.
     *
     * @return array{status: bool, data: array, message: string}
     */
    public function json(string $prompt, int $timeout = 25, int $tries = 3): array
    {
        if (!$this->isConfigured()) {
            return $this->fail('AI key is not configured (GEMINI_API_KEY).');
        }

        try {
            $attempt = HttpRetry::send(function () use ($prompt, $timeout) {
                return Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey(),
                        'Content-Type'  => 'application/json',
                    ])
                    ->acceptJson()
                    ->connectTimeout(10)
                    ->timeout($timeout)
                    ->post(self::ENDPOINT, $this->payload($prompt));
            }, $tries, 800, 'openai.chat', $timeout + 5);

            $response = $attempt['response'];

            // Every attempt failed to connect at all.
            if ($response === null) {
                Log::error('AI request never reached the provider', [
                    'attempts' => $attempt['attempts'],
                    'error'    => optional($attempt['exception'])->getMessage(),
                ]);

                return $this->fail(self::BUSY_MESSAGE);
            }

            if (!$response->successful()) {
                Log::error('AI request failed', [
                    'status'   => $response->status(),
                    'attempts' => $attempt['attempts'],
                    'body'     => mb_substr($response->body(), 0, 500),
                ]);

                // A 429/5xx that survived every retry is the provider having a
                // bad minute, not a fault in the request — say so without the
                // status code, which means nothing to the person on the screen.
                return $this->fail(
                    HttpRetry::retryable($response)
                        ? self::BUSY_MESSAGE
                        : 'AI request was rejected; default text was used instead.'
                );
            }

            $content = (string) $response->json('choices.0.message.content');

            if (trim($content) === '') {
                return $this->fail('AI returned an empty response.');
            }

            $data = $this->decode($content);

            if (!is_array($data)) {
                Log::warning('AI returned unparseable JSON', ['content' => $content]);

                return $this->fail('AI returned something that was not JSON.');
            }

            return ['status' => true, 'data' => $data, 'message' => 'ok'];

        } catch (\Throwable $exception) {
            Log::error('AI exception', [
                'error' => $exception->getMessage(),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
            ]);

            return $this->fail(self::BUSY_MESSAGE);
        }
    }

    /** The request body, identical for the single and the pooled path. */
    protected function payload(string $prompt): array
    {
        return [
            'model' => self::MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You reply with a single valid JSON object and nothing else. No markdown, no code fences, no commentary.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ];
    }

    /**
     * Run many prompts at once.
     *
     * One at a time a bulk job is unusable — 4,700 subcategories at ~5s each
     * is over six hours. These go out concurrently, so the wall-clock cost is
     * roughly (count / concurrency) x 5s.
     *
     * Whatever the provider refused for a transient reason is gathered up and
     * sent again in a later pass, so a busy minute costs a few extra seconds
     * rather than a few hundred rows of default text.
     *
     * @param  array<string|int, string>  $prompts  keyed however the caller likes
     * @param  int  $passes  how many times the leftovers are tried again
     * @return array<string|int, array{status: bool, data: array, message: string}>  same keys
     */
    public function jsonBatch(array $prompts, int $timeout = 40, int $passes = 3): array
    {
        if (!$this->isConfigured()) {
            return array_map(function () {
                return $this->fail('AI key is not configured (GEMINI_API_KEY).');
            }, $prompts);
        }

        $passes = max(1, $passes);
        $done = [];
        $pending = $prompts;

        // A whole pool going out at once is exactly what makes the provider
        // answer 429/503, so anything transient is collected and sent again as
        // a smaller pool a moment later instead of being written off.
        for ($pass = 1; $pass <= $passes; $pass++) {
            $results = $this->pool($pending, $timeout);
            $retry = [];

            foreach ($results as $key => $result) {
                if ($result['status'] || empty($result['retryable'])) {
                    unset($result['retryable']);
                    $done[$key] = $result;
                    continue;
                }

                $retry[$key] = $pending[$key];
            }

            if (!$retry) {
                break;
            }

            if ($pass === $passes) {
                Log::warning('AI batch gave up on some prompts', ['count' => count($retry)]);

                foreach ($retry as $key => $prompt) {
                    $done[$key] = $this->fail(self::BUSY_MESSAGE);
                }

                break;
            }

            $pending = $retry;

            usleep(HttpRetry::delayFor($pass, 1500) * 1000);
        }

        // Hand back exactly the keys we were given, in the order we got them.
        $out = [];

        foreach ($prompts as $key => $prompt) {
            $out[$key] = $done[$key] ?? $this->fail(self::BUSY_MESSAGE);
        }

        return $out;
    }

    /**
     * One concurrent round trip.
     *
     * @param  array<string|int, string>  $prompts
     * @return array<string|int, array{status: bool, data: array, message: string, retryable: bool}>
     */
    protected function pool(array $prompts, int $timeout): array
    {
        try {
            $responses = Http::pool(function ($pool) use ($prompts, $timeout) {
                $requests = [];

                foreach ($prompts as $key => $prompt) {
                    $requests[] = $pool->as((string) $key)
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $this->apiKey(),
                            'Content-Type'  => 'application/json',
                        ])
                        ->acceptJson()
                        ->connectTimeout(10)
                        ->timeout($timeout)
                        ->post(self::ENDPOINT, $this->payload($prompt));
                }

                return $requests;
            });
        } catch (\Throwable $exception) {
            Log::error('AI batch exception', ['error' => $exception->getMessage()]);

            return array_map(function () {
                return $this->fail(self::BUSY_MESSAGE) + ['retryable' => true];
            }, $prompts);
        }

        $out = [];

        foreach ($prompts as $key => $prompt) {
            $response = $responses[(string) $key] ?? null;

            // A pooled request that threw comes back as the exception itself —
            // a timed-out or dropped connection, both worth another go.
            if ($response instanceof \Throwable) {
                Log::warning('AI pooled request threw', ['error' => $response->getMessage()]);

                $out[$key] = $this->fail(self::BUSY_MESSAGE) + ['retryable' => true];
                continue;
            }

            if (!$response) {
                $out[$key] = $this->fail(self::BUSY_MESSAGE) + ['retryable' => true];
                continue;
            }

            if (!$response->successful()) {
                Log::warning('AI pooled request failed', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 300),
                ]);

                $out[$key] = $this->fail(
                    HttpRetry::retryable($response)
                        ? self::BUSY_MESSAGE
                        : 'AI request was rejected; default text was used instead.'
                ) + ['retryable' => HttpRetry::retryable($response)];
                continue;
            }

            $content = (string) $response->json('choices.0.message.content');
            $data = trim($content) === '' ? null : $this->decode($content);

            $out[$key] = is_array($data)
                ? ['status' => true, 'data' => $data, 'message' => 'ok', 'retryable' => false]
                // Bad JSON is the model's answer, not a network hiccup: sending
                // the identical prompt again usually returns the same mess.
                : $this->fail('AI returned something that was not JSON.') + ['retryable' => false];
        }

        return $out;
    }

    /** Tolerates a model that wrapped its JSON in a ```json fence anyway. */
    protected function decode(string $content)
    {
        $data = json_decode($content, true);

        if (is_array($data)) {
            return $data;
        }

        $clean = trim(preg_replace('/^```json\s*|^```\s*|```$/m', '', trim($content)));

        return json_decode($clean, true);
    }

    protected function fail(string $message): array
    {
        return ['status' => false, 'data' => [], 'message' => $message];
    }
}
