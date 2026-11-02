<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * What people search on Google around a query like "pet shop in jaipur".
 *
 * With SERPAPI_KEY set it uses SerpApi: Google's own "People also ask" and
 * "Related searches" for the query (one paid search). Without a key it falls
 * back to Google's search suggestions for a few phrasings of the query,
 * which is free and close enough for a trial.
 */
class RelatedSearches
{
    private const MAX = 40;

    /** Words that may come before "pet shop" without making it a business's name. */
    private const DESCRIBING = [
        'best', 'top', 'good', 'cheap', 'cheapest', 'famous', 'biggest', 'big', 'largest', 'small', 'exotic', 'wholesale',
        'online', 'local', 'new', 'near', 'nearest', 'all', 'the', 'a', 'any', 'open', 'popular', 'nearby', 'branded', 'premium',
        'where', 'is', 'which', 'what', 'list', 'of', 'in', 'my', 'me',
    ];

    /**
     * @param  string[]  $terms  words a kept search must contain (one of); default: the service name's words
     * @return array{source: string, keywords: string[]}
     */
    public static function for(string $service, string $city, array $terms = [], array $seeds = []): array
    {
        $query = mb_strtolower(trim($service) . ' in ' . trim($city));
        $terms = array_map('mb_strtolower', $terms ?: array_diff(
            preg_split('/[^\p{L}\p{M}]+/u', mb_strtolower($service), -1, PREG_SPLIT_NO_EMPTY),
            ['shop', 'shops', 'services', 'service', 'dealers', 'dealer', 'centre', 'center', 'and', 'the', 'for', 'on', 'in', 'of']
        ));

        $fromGoogle = [];
        $key = trim((string) config('services.serpapi.key'));
        if ($key !== '') {
            $fromGoogle = self::clean(self::serpApi($query, $key), $query, $city, $terms);
        }

        // Google's own boxes give 3-8 on-topic searches at most, and none at
        // all for some cities (Surat's page is all map and listings), so
        // Google's search suggestions are always added: for the query itself,
        // then for the query plus each letter ("pet shop in jaipur m" ->
        // "pet shop in jaipur mansarovar"), which is how people actually
        // finish it. Only the page's own query is sent — never topics made up
        // here ("rabbit shop in jaipur" came back as a "suggestion" only
        // because it had been typed in), and nothing that was sent is kept.
        $short = mb_strtolower(trim($service) . ' ' . trim($city));
        $sent = [$query, $short];
        foreach (range('a', 'z') as $letter) {
            $sent[] = $query . ' ' . $letter;
        }

        $lists = [];
        foreach ($sent as $q) {
            $lists[] = self::suggestions($q);
            usleep(250000);
        }

        // The plain query's suggestions first (Google lists them most-searched
        // first), then the letters'.
        $found = array_merge($lists[0], $lists[1]);
        for ($i = 0; $i < 10; $i++) {
            foreach (array_slice($lists, 2) as $list) {
                if (isset($list[$i])) {
                    $found[] = $list[$i];
                }
            }
        }
        $sentSet = array_flip(array_map(fn ($q) => trim($q), $sent));
        $found = array_values(array_filter($found, fn ($k) => !isset($sentSet[mb_strtolower(trim((string) $k))])));

        return [
            'source'   => $fromGoogle ? 'serpapi+autocomplete' : 'autocomplete',
            'keywords' => self::clean(array_merge($fromGoogle, $found), $query, $city, $terms),
        ];
    }

    private static function serpApi(string $query, string $key): array
    {
        try {
            $res = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine' => 'google', 'q' => $query, 'gl' => 'in', 'hl' => 'en', 'api_key' => $key,
            ]);
            if (!$res->successful()) {
                Log::warning('SerpApi related searches failed', ['status' => $res->status(), 'query' => $query]);
                return [];
            }
            $json = $res->json();
        } catch (\Throwable $e) {
            Log::warning('SerpApi related searches failed', ['error' => $e->getMessage(), 'query' => $query]);
            return [];
        }

        return array_merge(
            array_column($json['related_questions'] ?? [], 'question'),
            array_column($json['related_searches'] ?? [], 'query')
        );
    }

    private static function suggestions(string $query): array
    {
        try {
            $res = Http::timeout(10)->get('https://suggestqueries.google.com/complete/search', [
                'client' => 'firefox', 'hl' => 'en', 'gl' => 'in', 'q' => $query,
            ]);
            $json = json_decode($res->body(), true);
        } catch (\Throwable $e) {
            Log::warning('Google suggestions failed', ['error' => $e->getMessage(), 'query' => $query]);
            return [];
        }

        return is_array($json[1] ?? null) ? $json[1] : [];
    }

    private static function otherCity(string $keyword, string $city): bool
    {
        static $names = null;
        $names ??= \App\Models\Location::where('status', 1)->pluck('name')
            ->map(fn ($n) => mb_strtolower(trim($n)))->filter(fn ($n) => mb_strlen($n) >= 4)->all();

        foreach ($names as $name) {
            if ($name !== $city && !str_contains($city, $name) && preg_match('/\b' . preg_quote($name, '/') . '\b/u', $keyword)) {
                return true;
            }
        }

        return false;
    }

    /** Unique, not the query itself, no other directories' names. */
    private static function clean(array $found, string $query, string $city = '', array $terms = []): array
    {
        $city = mb_strtolower(trim($city));
        $exclude = array_map('mb_strtolower', (array) config('seo.pasf_faq_exclude', []));
        $out = [];
        foreach ($found as $k) {
            $k = trim(preg_replace('/\s+/u', ' ', mb_strtolower((string) $k)));
            if ($k === '' || $k === $query || mb_strlen($k) > 90) {
                continue;
            }
            // Off topic: "kolkata map", "shop app download", "pet scan cost".
            $words = preg_split('/[^\p{L}\p{M}]+/u', $k, -1, PREG_SPLIT_NO_EMPTY);
            if ($terms && !array_intersect($words, $terms)) {
                continue;
            }
            foreach ($exclude as $phrase) {
                if (preg_match('/\b' . preg_quote($phrase, '/') . '\b/u', $k)) {
                    continue 2;
                }
            }
            if (array_intersect($words, (array) config('seo.pasf_faq_foreign_words', []))) {
                continue;
            }
            // A general question, not someone looking for a shop: "which city
            // has the most pets in india?", "how to get a pet license".
            if (preg_match('/^(how|what|which|why|who|when|is|are|does|do|can|should)\b/u', $k)
                && !preg_match('/\b(shop|shops|store|stores|buy|price|prices|cost|sale|adopt\w*|market|online)\b/u', $k)) {
                continue;
            }
            // Another business's name: "rudra pet shop amritsar", "pawan pet
            // house kota" — words before "pet shop" that are neither animals
            // nor ordinary describing words.
            if (preg_match('/^(.+?)\s+(?:pets?|animal)\s+(?:shop|shops|store|house|world|zone|point|centre|center|care|boutique)\b/u', $k, $m)) {
                $before = array_diff(
                    preg_split('/[^\p{L}\p{M}\d]+/u', $m[1], -1, PREG_SPLIT_NO_EMPTY),
                    $terms, self::DESCRIBING
                );
                if ($before) {
                    continue;
                }
            }
            // "surat" suggests "surathkal" and "suratgarh": other towns.
            if ($city !== '' && str_contains($k, $city) && !preg_match('/\b' . preg_quote($city, '/') . '\b/u', $k)) {
                continue;
            }
            // Another of our own cities ("pet shop pune" on the Jaipur page).
            // Places outside our list (Surat Thani) are left to the AI prompt.
            if ($city !== '' && self::otherCity($k, $city)) {
                continue;
            }
            if (preg_match('/justdial|sulekha|indiamart|olx|quikr|amazon|flipkart/', $k)) {
                continue;
            }
            // "... with price", "... with price list", "... with price list near me" are one search.
            $same = trim(preg_replace('/\b(with|list|near me)\b/u', ' ', $k));
            $same = preg_replace('/\s+/u', ' ', $same);
            $out[$same] ??= $k;
        }

        return array_slice(array_values($out), 0, self::MAX);
    }
}
