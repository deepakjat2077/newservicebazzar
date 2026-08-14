<?php

namespace App\Services;

use App\Support\HttpRetry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Spelling/grammar correction for listing text.
 *
 * Shared by the admin listing form (live, per-field, as you type) and the
 * `listings:fix-spelling` command (bulk, over rows already in the database),
 * so both use the exact same prompt, field allowlist and safety guards.
 */
class SpellFixer
{
    /**
     * Fields safe to correct. Emails, URLs, phone numbers, pincodes and years
     * are deliberately absent — a model that "fixes" a phone number does more
     * damage than the typo it was chasing.
     */
    public const ALLOWED_FIELDS = [
        'name', 'plot_no', 'building_name', 'street_name', 'landmark',
        'contact_person', 'area', 'city', 'state',
        'meta_title', 'meta_keywords', 'meta_description', 'details',
        'question', 'answer',
    ];

    /** Anything longer than this is skipped rather than sent in one request. */
    public const MAX_FIELD_LENGTH = 6000;

    /**
     * Correct a set of field => value pairs.
     *
     * @return array{status: bool, corrections: array<string, array{from: string, to: string}>, message?: string}
     */
    public function correct(array $fields): array
    {
        $payload = $this->filter($fields);

        if ($payload === []) {
            return ['status' => true, 'corrections' => []];
        }

        $result = $this->ask($payload);

        if (!$result['status']) {
            return $result + ['corrections' => []];
        }

        return [
            'status'      => true,
            'corrections' => $this->diff($payload, $result['data']),
        ];
    }

    /** Drop anything not worth (or not safe) sending. */
    public function filter(array $fields): array
    {
        $payload = [];

        foreach ($fields as $key => $value) {
            // `question_0`, `answer_3` etc. all map back to their base field.
            $baseKey = preg_replace('/_\d+$/', '', (string) $key);

            if (!in_array($baseKey, self::ALLOWED_FIELDS, true)) {
                continue;
            }

            $value = (string) $value;

            if (trim($value) === '' || mb_strlen($value) > self::MAX_FIELD_LENGTH) {
                continue;
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    /**
     * Keep only genuine changes, and throw away anything that looks like the
     * model rewriting rather than correcting.
     */
    public function diff(array $original, array $corrected): array
    {
        $changes = [];

        foreach ($original as $key => $before) {
            $after = $corrected[$key] ?? null;

            if (!is_string($after)) {
                continue;
            }

            $after = $this->restoreProtectedTerms($before, trim($after));

            if ($after === '' || $after === $before) {
                continue;
            }

            // A "correction" that changes the length by more than ~40% is the
            // model going off script, not a typo fix.
            if (mb_strlen($after) < mb_strlen($before) * 0.6 ||
                mb_strlen($after) > mb_strlen($before) * 1.6) {
                continue;
            }

            $changes[$key] = ['from' => $before, 'to' => $after];
        }

        return $changes;
    }

    /**
     * Undo "corrections" to words the site spells its own way.
     *
     * The prompt already says not to touch these, but a model that ignores it
     * would silently rebrand every listing — "ServiceBazzar" is not a typo of
     * "bazaar". Only terms present in the original are restored, so this can
     * never introduce a word that was not already there.
     */
    private function restoreProtectedTerms(string $before, string $after): string
    {
        $protected = [
            'bazzar' => '/\b(bazaars?|bazars?)\b/iu',
        ];

        foreach ($protected as $term => $wrongPattern) {
            if (stripos($before, $term) === false) {
                continue;
            }

            // Reuse the casing the original actually used (Bazzar vs bazzar).
            preg_match('/\b' . preg_quote($term, '/') . '\b/iu', $before, $m);
            $correct = $m[0] ?? $term;

            $after = preg_replace($wrongPattern, $correct, $after);
        }

        return $after;
    }

    private function prompt(array $payload): string
    {
        $brand = config('app.name');

        return "You are a strict spelling and grammar corrector for an Indian local business directory.

Correct ONLY spelling mistakes and clear grammar errors in the JSON values below.

Hard rules:
- Return JSON only, with exactly the same keys, no markdown, no code block
- Keep the original meaning, tone and language (Hinglish stays Hinglish)
- The site is called \"{$brand}\". The words ServiceBazzar, Service Bazzar and bazzar are the brand's own spelling — NEVER change them to bazaar, bazar or anything else
- NEVER change proper nouns: business names, people's names, Indian city / area / colony / street names, brand names. Words like Samrpura, Niwaru, Jaat, Mansarovar, Sanganer, Bansa are correct as written
- Keep every HTML tag, attribute and structure byte-for-byte
- Never change numbers, emails, URLs, phone numbers, pincodes
- Do not rephrase, do not improve style, do not add or remove content
- If a value has no mistake, return it exactly as given
- Fix capitalisation only when it is clearly wrong (e.g. sentence start)

JSON to correct:
" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Calls the same provider the listing form's AI content generation uses.
     *
     * Note the key name: config('services.gemini.key') is what this app calls
     * it, but the endpoint is OpenAI's — the existing ListingController helper
     * has the same mismatch. Kept as-is so one key keeps serving both.
     */
    private function ask(array $payload): array
    {
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            return ['status' => false, 'message' => 'GEMINI_API_KEY missing in .env'];
        }

        try {
            $attempt = HttpRetry::send(function () use ($apiKey, $payload) {
                return Http::withHeaders([
                        'Authorization' => 'Bearer ' . trim($apiKey),
                        'Content-Type'  => 'application/json',
                    ])
                    ->acceptJson()
                    ->connectTimeout(10)
                    ->timeout(60)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'    => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'user', 'content' => $this->prompt($payload)],
                        ],
                    ]);
            // Live, per-field, while someone is typing: a retry is only worth
            // it when the refusal came back quickly.
            }, 3, 800, 'openai.spellfix', 60);

            $response = $attempt['response'];
        } catch (\Throwable $e) {
            Log::warning('Spell fix request threw', ['error' => $e->getMessage()]);

            return ['status' => false, 'message' => 'Spell check service is busy. Please try again.'];
        }

        if ($response === null) {
            Log::warning('Spell fix API unreachable', [
                'attempts' => $attempt['attempts'],
                'error'    => optional($attempt['exception'])->getMessage(),
            ]);

            return ['status' => false, 'message' => 'Spell check service is busy. Please try again.'];
        }

        if (!$response->successful()) {
            Log::warning('Spell fix API error', [
                'status'   => $response->status(),
                'attempts' => $attempt['attempts'],
                'body'     => mb_substr($response->body(), 0, 300),
            ]);

            // The upstream status stays in the log: the editor only needs to
            // know whether waiting a moment is worth it.
            return [
                'status'  => false,
                'message' => HttpRetry::retryable($response)
                    ? 'Spell check service is busy. Please try again.'
                    : 'Spell check could not be completed.',
            ];
        }

        $content = $response->json('choices.0.message.content');

        if (empty($content)) {
            return ['status' => false, 'message' => 'Empty response from AI'];
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            $clean = trim(preg_replace('/^```json\s*|^```\s*|```$/m', '', trim($content)));
            $data  = json_decode($clean, true);
        }

        if (!is_array($data)) {
            return ['status' => false, 'message' => 'Model did not return valid JSON'];
        }

        return ['status' => true, 'data' => $data];
    }
}
