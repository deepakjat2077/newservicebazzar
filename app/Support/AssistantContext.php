<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * What the site assistant is allowed to answer from: this site's own
 * categories, cities and listings. The model is given these and told to use
 * nothing else, so it cannot invent a business that does not exist here.
 */
class AssistantContext
{
    /** Businesses named in one answer. More is noise, and a longer prompt. */
    private const MAX_LISTINGS = 6;

    /** How far "near me" may reach before the answer stops being useful. */
    private const NEARBY_KM = 25;

    /** Acronyms that are also everyday words, so they only count capitalised. */
    private const AMBIGUOUS_ACRONYMS = ['it', 'is', 'as', 'at', 'on', 'in', 'or', 'an', 'be', 'no', 'so', 'to', 'we', 'he', 'do', 'go', 'me'];

    /** Ordinary words in a question, never a place name. */
    private const FILLER_WORDS = [
        // "mere aas pass" was being read as a place, and "pass" matches the
        // "Bypass Road" in somebody's address.
        'near', 'nearby', 'nearest', 'closest', 'around', 'here', 'paas', 'pass', 'najdik', 'nazdik',
        'mere', 'meri', 'mera', 'yaha', 'yahan', 'abhi', 'kareeb', 'karib', 'nearme',
        'best', 'good', 'achi', 'acha', 'accha', 'mein', 'main', 'chahiye', 'chaiye', 'batao',
        'bata', 'kahan', 'kaha', 'konsi', 'kaunsi', 'konsa', 'please', 'want', 'need', 'find',
        'looking', 'show', 'give', 'list', 'nearby', 'around', 'cheap', 'sasta', 'sasti', 'koi',
        'mujhe', 'hai', 'hain', 'kya', 'kuch', 'the', 'top', 'open', 'with', 'price', 'prices',
        'jana', 'jaana', 'karna', 'karwana', 'lena', 'dena', 'hona', 'raha', 'raha',
        'chahta', 'chahti', 'sakta', 'sakte', 'sakti', 'batana', 'bhai', 'yaar', 'phir',
        'wala', 'wale', 'wali', 'konse', 'kaunse', 'bhi', 'thoda', 'zara', 'jara',
    ];

    /**
     * The generic half of a neighbourhood name. Jaipur alone has 17 areas
     * ending in "nagar" and three containing "park", so "city park yha pr"
     * was answered with Bani Park — confidently, and to someone standing in
     * Mansarovar. These only count attached to the name that identifies one.
     */
    private const PLACE_GENERIC_WORDS = [
        'park', 'nagar', 'colony', 'vihar', 'puram', 'marg', 'road', 'sector', 'scheme',
        'enclave', 'extension', 'phase', 'block', 'chowk', 'circle', 'bazar', 'bazaar',
        'mandi', 'city', 'town', 'area', 'gali', 'street', 'lane', 'cross', 'main',
    ];

    /** Too common across category names to identify one on their own. */
    private const GENERIC_WORDS = [
        'service', 'services', 'dealer', 'dealers', 'shops', 'store', 'stores', 'centre', 'center',
        'centres', 'centers', 'repair', 'repairs', 'provider', 'providers', 'company', 'companies',
        'agency', 'agencies', 'solutions', 'contractor', 'contractors', 'consultant', 'consultants',
        'business', 'businesses', 'local', 'other', 'others', 'india', 'indian', 'online', 'near',
    ];

    /** @return array{category: ?Category, location: ?Location, area: ?string, nearby: bool, asked_nearby: bool, listings: \Illuminate\Support\Collection} */
    public static function find(string $message, ?string $fallbackCity = null, $lat = null, $lng = null, ?string $placeHint = null): array
    {
        $plain = ' ' . preg_replace('/\s+/u', ' ', $message) . ' ';
        $text = mb_strtolower($plain);

        // "Jhotwara, Jaipur" from the header's location box: the city half
        // names the city, the rest is the neighbourhood to prefer.
        $hintParts = array_values(array_filter(array_map('trim', explode(',', (string) $placeHint))));
        $hintText = $hintParts ? ' ' . mb_strtolower(implode(' ', $hintParts)) . ' ' : '';

        $location = self::match($text, self::locations(), $plain)
            ?? ($hintText !== '' ? self::match($hintText, self::locations(), $hintText) : null)
            ?? self::locationByName($fallbackCity);

        // "konse cafe me jau": no category of ours is called a cafe, so the
        // assistant used to answer that nothing is listed — about 175
        // restaurants it was holding all along. See config/listing.php.
        $category = self::match($text, self::categories(), $plain)
            ?? self::synonym($text);

        $listings = collect();
        $area = null;
        $areaFromHint = false;
        $areaCount = 0;

        // "Justfinds Solution ka number do": a business named outright, which
        // no category match would ever find. Tried before the category path
        // so a follow-up about one business does not become a fresh list.
        if ($named = self::matchByName($text, $location)) {
            return [
                'category'     => $named->category,
                'location'     => $location,
                'area'         => null,
                'area_from_hint' => false,
                'area_count'   => 0,
                'nearby'       => false,
                'asked_nearby' => false,
                // They asked about one business by name; which neighbourhood
                // they are standing in has nothing to do with the answer.
                'hint_area'    => null,
                'listings'     => collect([$named]),
            ];
        }

        // Only half the visitors have shared a position, and a third of the
        // listings carry no coordinates, so distance is an extra ordering —
        // never the only way a business can be found.
        $lat = is_numeric($lat) ? (float) $lat : null;
        $lng = is_numeric($lng) ? (float) $lng : null;
        $askedNearby = self::asksForNearby($text);
        $nearby = $lat && $lng && $askedNearby;

        if ($category) {
            $base = fn () => Listing::where('status', 1)
                ->where('category_id', $category->id)
                ->when($location && !$nearby, fn ($q) => $q->where('location_id', $location->id))
                ->when($nearby, fn ($q) => $q
                    ->whereNotNull('latitude')->where('latitude', '!=', 0)
                    ->selectRaw('listings.*, ' . self::distanceSql($lat, $lng) . ' AS sb_km')
                    ->having('sb_km', '<', self::NEARBY_KM));

            // A neighbourhood, not a city: "Bilonchi me achi hotel". Tried
            // first, because the city-wide list would answer a different
            // question. Both columns are searched — the same place is often
            // "Bilochi" in `area` and "Bilonchi" in `address`.
            // An area named in the question wins; otherwise the one the
            // visitor already picked in the header is used, so "best CA near
            // me" does not have to ask where they are.
            // Neighbourhoods get typed the way they sound — "mansrover" for
            // Mansarovar — so each word is tried as written and then as the
            // real names it was probably aiming at.
            $inAsked = function (array $words) use ($base, $nearby, $location) {
                foreach (self::spellings($words, $location) as $word) {
                    $hits = $base()
                        ->where(fn ($q) => $q->where('area', 'like', "%{$word}%")->orWhere('address', 'like', "%{$word}%"))
                        ->when($nearby, fn ($q) => $q->orderBy('sb_km'))
                        ->orderByDesc('verified')
                        ->orderByRaw('COALESCE(gmb_review_count, 0) DESC')
                        ->limit(self::MAX_LISTINGS)
                        ->get(['id', 'name', 'slug', 'category_id', 'area', 'mobile', 'mobile_numbers', 'verified', 'gmb_rating', 'gmb_review_count']);

                    // LIKE matches inside other words, so each row is checked
                    // again for the word standing on its own.
                    $hits = $hits->filter(fn ($l) => preg_match(
                        '/(?<![\p{L}])' . preg_quote($word, '/') . '(?![\p{L}])/iu',
                        $l->area . ' ' . $l->address
                    ));

                    if ($hits->isNotEmpty()) {
                        return [$hits->values(), trim((string) ($hits->first()->area ?: $word))];
                    }
                }

                return [collect(), null];
            };

            if (!$nearby) {
                [$listings, $area] = $inAsked(self::placeWords($text, $category, $location));

                // Only once the question's own words have come up empty. A
                // stray word like "jana" used to count as a neighbourhood,
                // find nothing, and stop the header's location being tried at
                // all — so someone standing in Mansarovar got the whole city.
                if ($listings->isEmpty() && $hintParts) {
                    [$listings, $area] = $inAsked(
                        self::placeWords(' ' . mb_strtolower($hintParts[0]) . ' ', $category, $location)
                    );

                    // Where they are, not where they asked about — the answer
                    // may say "you are in X" only for this one.
                    $areaFromHint = $listings->isNotEmpty();
                }
            }

            $areaCount = $listings->count();

            // A neighbourhood answer should not be a thin one. Jhotwara has
            // one hospital, so "best hospital in Jaipur" came back with that
            // alone; the area's own businesses stay at the top and the rest
            // of the city follows.
            if ($areaCount > 0 && $areaCount < self::MAX_LISTINGS) {
                $listings = $listings->concat(
                    $base()
                        ->whereNotIn('listings.id', $listings->pluck('id')->all())
                        ->orderByDesc('verified')
                        ->orderByRaw('COALESCE(gmb_review_count, 0) DESC')
                        ->limit(self::MAX_LISTINGS - $areaCount)
                        ->get(['id', 'name', 'slug', 'category_id', 'area', 'mobile', 'mobile_numbers', 'verified', 'gmb_rating', 'gmb_review_count'])
                )->values();
            }

            if ($listings->isEmpty()) {
                $listings = $base()
                    ->when($nearby, fn ($q) => $q->orderBy('sb_km'))
                    ->orderByDesc('verified')
                    ->orderByRaw('COALESCE(gmb_review_count, 0) DESC')
                    ->limit(self::MAX_LISTINGS)
                    ->get(['id', 'name', 'slug', 'category_id', 'area', 'mobile', 'mobile_numbers', 'verified', 'gmb_rating', 'gmb_review_count']);
            }
        }

        return [
            'category'     => $category,
            'location'     => $location,
            'area'         => $area,
            // True only when the neighbourhood came from the header's location
            // box. When it came out of the question the visitor told us what
            // they were asking about, not where they are standing.
            'area_from_hint' => $area ? $areaFromHint : false,
            // How many of the listings are actually in that neighbourhood —
            // the rest were topped up from the wider city.
            'area_count'   => $areaCount,
            'nearby'       => $nearby,
            // Asked "near me" but we have no position for them: the answer
            // has to say so rather than quietly list the whole city.
            'asked_nearby' => $askedNearby && !$nearby && !$area && !$hintParts,
            // The neighbourhood the visitor has set in the header, even when
            // nothing of this kind is listed there.
            'hint_area'    => $hintParts[0] ?? null,
            'listings'     => $listings,
        ];
    }

    /**
     * A listing the visitor named. Two distinctive words must line up, or one
     * long one, so "pet shop" does not pick a business called "Pet Shop" out
     * of a question that meant the category.
     */
    private static function matchByName(string $text, ?Location $location)
    {
        $words = array_values(array_filter(
            preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY),
            fn ($w) => mb_strlen($w) >= 5
                && !in_array($w, self::FILLER_WORDS, true)
                && !in_array($w, self::GENERIC_WORDS, true)
        ));

        if (!$words) {
            return null;
        }

        $candidates = Listing::where('status', 1)
            ->when($location, fn ($q) => $q->where('location_id', $location->id))
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $q->orWhere('name', 'like', "%{$w}%");
                }
            })
            ->with('category:id,name,slug')
            ->limit(25)
            ->get(['id', 'name', 'slug', 'category_id', 'area', 'address', 'mobile', 'mobile_numbers', 'verified', 'gmb_rating', 'gmb_review_count']);

        $best = null;
        $bestScore = 0;
        $noise = self::nameNoiseWords() + self::cityWords();

        foreach ($candidates as $listing) {
            $name = mb_strtolower($listing->name);
            $score = 0;
            // Every long word of a category name is also a word businesses put
            // in their own names, so "mansarovar me restaurant batao" scored a
            // match on "restaurant" alone and answered with one hotel in a
            // different neighbourhood. A business is only "named" when
            // something other than the service itself lines up.
            $named = false;

            foreach ($words as $w) {
                if (!str_contains($name, $w)) {
                    continue;
                }

                $score += mb_strlen($w);

                if (!isset($noise[$w])) {
                    $named = true;
                }
            }

            if ($named && $score > $bestScore) {
                $best = $listing;
                $bestScore = $score;
            }
        }

        // Nine characters is roughly two real words, or one unmistakable one.
        return $bestScore >= 9 ? $best : null;
    }

    /** Distance in km from a point, for ordering "nearest first". */
    private static function distanceSql(float $lat, float $lng): string
    {
        return sprintf(
            '(6371 * ACOS(LEAST(1, COS(RADIANS(%1$F)) * COS(RADIANS(listings.latitude))'
            . ' * COS(RADIANS(listings.longitude) - RADIANS(%2$F))'
            . ' + SIN(RADIANS(%1$F)) * SIN(RADIANS(listings.latitude)))))',
            $lat,
            $lng
        );
    }

    /**
     * "List my business" — one of the widget's own suggestions, and the one
     * question the assistant could not answer: it searched the directory for
     * the visitor's business, did not find it, and offered them AC repair.
     * They are not looking for a business; they want to become one.
     */
    private const LISTING_INTENT = [
        'list my business', 'list my shop', 'list my company', 'list my service',
        'list our business', 'list your business', 'add my business', 'add my shop',
        'add my listing', 'add my company', 'register my business', 'register my shop',
        'free listing', 'mera business add', 'mera business list', 'meri dukaan add',
        'apna business add', 'apna business list', 'apni dukaan', 'apni listing',
        'apna shop', 'business add kar', 'business list kar', 'business register kar',
        'listing kaise', 'business kaise add', 'business kaise list', 'dukaan add kar',
        'shop add kar', 'business kaise dale', 'business kaise daale',
    ];

    public static function asksToList(string $message): bool
    {
        $text = ' ' . mb_strtolower(preg_replace('/\s+/u', ' ', $message)) . ' ';

        foreach (self::LISTING_INTENT as $phrase) {
            if (str_contains($text, $phrase)) {
                return true;
            }
        }

        return false;
    }

    /** "near me", "paas me", "sabse najdik" — and anything on the way. */
    private static function asksForNearby(string $text): bool
    {
        return (bool) preg_match(
            '/\b(near ?me|nearest|nearby|closest|close by|around here|paas|pass|najdik|nazdik|nearme)\b/u',
            $text
        );
    }

    /**
     * Words in the question that could be a neighbourhood: long enough to be
     * a place name, and not part of the service or city already matched.
     */
    private static function placeWords(string $text, ?Category $category, ?Location $location): array
    {
        $known = [];

        foreach ([$category?->name, $location?->name] as $name) {
            foreach (preg_split('/[^\p{L}]+/u', mb_strtolower((string) $name), -1, PREG_SPLIT_NO_EMPTY) as $w) {
                $known[$w] = true;
            }
        }

        $out = [];
        // A word for the service itself is never the neighbourhood. "acha cafe
        // batao" left "cafe" standing as a place name, which found nothing and
        // then stopped the header's own location from being used at all.
        $noise = self::nameNoiseWords();

        foreach (preg_split('/[^\p{L}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (mb_strlen($word) >= 4
                && !isset($known[$word])
                && !isset($noise[$word])
                && !in_array($word, self::GENERIC_WORDS, true)
                && !in_array($word, self::PLACE_GENERIC_WORDS, true)
                && !in_array($word, self::FILLER_WORDS, true)) {
                $out[$word] = true;
            }
        }

        return array_keys($out);
    }

    /**
     * Longest phrase first, so "navi mumbai" is not read as "mumbai" and
     * "air cooler dealers" not as "dealers". Whole words only.
     */
    private static function match(string $text, array $rows, ?string $plain = null)
    {
        $plain = $plain ?? $text;
        $byLength = [];

        foreach ($rows as $row) {
            foreach (self::aliases($row) as $alias => $caseSensitive) {
                $byLength[] = [mb_strlen($alias), $alias, $caseSensitive, $row];
            }
        }

        usort($byLength, fn ($a, $b) => $b[0] <=> $a[0]);

        foreach ($byLength as [, $alias, $caseSensitive, $row]) {
            // An acronym is matched with its capitals intact: "IT wale" is the
            // IT category, "is it good" is not.
            $haystack = $caseSensitive ? $plain : $text;
            if (str_contains($haystack, ' ' . $alias . ' ')) {
                return $row;
            }
        }

        // Nothing matched in full; try the distinctive word of each name
        // ("hospital" for "Hospital", "plumber" for "Plumber"). Words that
        // appear in dozens of category names carry no meaning on their own —
        // "what services do you have" was being read as "RO Water Purifier
        // Repair & Services".
        foreach ([false, true] as $allowGeneric) {
            foreach ($rows as $row) {
                $words = preg_split('/[^\p{L}]+/u', mb_strtolower($row->name), -1, PREG_SPLIT_NO_EMPTY);
                $distinctive = array_filter($words, fn ($w) => mb_strlen($w) >= 5 && !in_array($w, self::GENERIC_WORDS, true));

                // Second pass only helps names whose every long word is a
                // common one — "IT Consultants" has nothing but "consultants".
                if ($allowGeneric && $distinctive) {
                    continue;
                }

                foreach (($allowGeneric ? $words : $distinctive) as $word) {
                    if (mb_strlen($word) >= 5 && str_contains($text, ' ' . $word)) {
                        return $row;
                    }
                }
            }
        }

        return null;
    }

    /**
     * The ways a visitor may write a name: as stored, without the bracketed
     * short form, and the short form on its own — "Chartered Accountant (CA)"
     * has to be found by someone who just types "CA".
     */
    private static function aliases($row): array
    {
        $raw = trim($row->name);
        $name = mb_strtolower($raw);

        // alias => is it matched with its original capitals?
        $out = [$name => false];

        if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/u', $name, $m)) {
            $out[trim($m[1])] = false;
        }

        // Acronyms people actually type: CCTV, AC, CA, IT, RO. Only the ones
        // that are also ordinary English words need their capitals kept —
        // "IT wale" is this category, "is it good" is not — while "cctv
        // dealer" and "ac repair" are written in lower case all day.
        foreach (preg_split('/[^A-Za-z]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (mb_strlen($word) < 2 || mb_strlen($word) > 5 || $word !== mb_strtoupper($word)) {
                continue;
            }

            if (in_array(mb_strtolower($word), self::AMBIGUOUS_ACRONYMS, true)) {
                $out[$word] = true;            // matched with capitals intact
            } else {
                $out[mb_strtolower($word)] = false;
            }
        }

        return array_filter($out, fn ($v, $a) => mb_strlen($a) >= 2, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * The place words to try, in the order worth trying them: longest first,
     * because a nine-letter neighbourhood is a far better guess than "park",
     * which sits in a dozen addresses — and each word is immediately followed
     * by the real spellings it was probably aiming at, so a misspelt
     * neighbourhood still beats a short word from elsewhere in the sentence.
     *
     * @param  string[]  $words
     * @return string[]
     */
    private static function spellings(array $words, ?Location $location): array
    {
        usort($words, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $out = [];

        foreach ($words as $word) {
            $out[$word] = true;

            foreach (self::nearMisses($word, $location) as $area) {
                $out[$area] = true;
            }
        }

        return array_keys($out);
    }

    /**
     * Real neighbourhood names a typed word was almost certainly aiming at.
     * One slip in a short name, two in a long one — close enough to be a
     * spelling, far enough that Malviya Nagar never becomes Mansarovar.
     *
     * @return string[]
     */
    private static function nearMisses(string $word, ?Location $location): array
    {
        if (!$location || mb_strlen($word) < 5) {
            return [];
        }

        $out = [];

        foreach (self::areasOf($location) as $area) {
            $lower = mb_strtolower($area);

            // Already covered by the literal pass.
            if (str_contains($lower, $word)) {
                continue;
            }

            foreach (preg_split('/[^\p{L}]+/u', $lower, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                if (mb_strlen($part) < 5 || in_array($part, self::PLACE_GENERIC_WORDS, true)) {
                    continue;
                }

                $slips = levenshtein($word, $part);

                if ($slips > 0 && $slips <= (mb_strlen($part) >= 8 ? 2 : 1)) {
                    $out[$area] = true;
                }
            }
        }

        return array_keys($out);
    }

    /** Every neighbourhood a live listing in this city sits in. */
    private static function areasOf(Location $location): array
    {
        return Cache::remember('assistant:areas:' . $location->id, now()->addHours(6), function () use ($location) {
            return Listing::where('status', 1)
                ->where('location_id', $location->id)
                ->whereNotNull('area')
                ->where('area', '!=', '')
                ->distinct()
                ->pluck('area')
                ->all();
        });
    }

    /**
     * Words that name a service rather than a business: every word of every
     * category name, and the everyday words people use for them. Finding one
     * inside a listing's name proves nothing — half the restaurants in the
     * directory have "Restaurant" in their name.
     */
    private static function nameNoiseWords(): array
    {
        return Cache::remember('assistant:name-noise', now()->addHours(6), function () {
            $out = [];

            foreach (self::categories() as $row) {
                foreach (preg_split('/[^\p{L}]+/u', mb_strtolower($row->name), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                    $out[$word] = true;
                }
            }

            foreach ((array) config('listing.assistant_synonyms', []) as $words) {
                foreach ((array) $words as $word) {
                    foreach (preg_split('/[^\p{L}]+/u', mb_strtolower($word), -1, PREG_SPLIT_NO_EMPTY) as $part) {
                        $out[$part] = true;
                    }
                }
            }

            return $out;
        });
    }

    /**
     * Every word of every city name. Only used when deciding whether a
     * business was named: "Packers and Movers in Jaipur" matched the one
     * business called "Jaipur Packers & Movers" and answered with it alone.
     *
     * Deliberately not treated as noise when looking for a neighbourhood —
     * "Ajmer Road" in Jaipur is a real address, and Ajmer is also a city.
     */
    private static function cityWords(): array
    {
        return Cache::remember('assistant:city-words', now()->addHours(6), function () {
            $out = [];

            foreach (self::locations() as $row) {
                foreach (preg_split('/[^\p{L}]+/u', mb_strtolower($row->name), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                    $out[$word] = true;
                }
            }

            return $out;
        });
    }

    /**
     * The category behind an everyday word for it — cafe, taxi, gym, PG.
     * Longest phrase first, whole words only, and only ever consulted after
     * the real names have had their turn.
     */
    private static function synonym(string $text)
    {
        $map = [];

        foreach ((array) config('listing.assistant_synonyms', []) as $categoryName => $words) {
            foreach ((array) $words as $word) {
                $map[] = [mb_strlen($word), mb_strtolower($word), mb_strtolower($categoryName)];
            }
        }

        usort($map, fn ($a, $b) => $b[0] <=> $a[0]);

        foreach ($map as [, $word, $categoryName]) {
            if (!str_contains($text, ' ' . $word . ' ')) {
                continue;
            }

            foreach (self::categories() as $row) {
                if (mb_strtolower(trim($row->name)) === $categoryName) {
                    return $row;
                }
            }
        }

        return null;
    }

    private static function categories(): array
    {
        return Cache::remember('assistant:categories', now()->addHours(6), function () {
            return Category::where('status', 1)->orderByRaw('CHAR_LENGTH(name) DESC')
                ->get(['id', 'name', 'slug'])->all();
        });
    }

    private static function locations(): array
    {
        return Cache::remember('assistant:locations', now()->addHours(6), function () {
            return Location::where('status', 1)->orderByRaw('CHAR_LENGTH(name) DESC')
                ->get(['id', 'name', 'slug'])->all();
        });
    }

    private static function locationByName(?string $name)
    {
        if (!$name) {
            return null;
        }

        foreach (self::locations() as $row) {
            if (mb_strtolower($row->name) === mb_strtolower(trim($name))) {
                return $row;
            }
        }

        return null;
    }

    /**
     * The brand as the admin set it (Setting > site_name). config('app.name')
     * is the installation's own name, "MS Infotech Directory", which is not
     * what visitors should be told.
     */
    public static function siteName(): string
    {
        $name = trim((string) optional(Cache::remember(\App\Models\Setting::CACHE_KEY, now()->addHours(6), fn () => \App\Models\Setting::first()))->site_name);

        return $name !== '' ? $name : 'Service Bazzar';
    }

    /** The names of every category, for "what can I find here" questions. */
    public static function categoryNames(): string
    {
        return collect(self::categories())->pluck('name')->sort()->implode(', ');
    }

    /** One line per business, exactly what the answer may say about it. */
    public static function listingLines($listings, ?Category $category): string
    {
        return $listings->map(function ($l) use ($category) {
            $rating = ($l->gmb_rating && $l->gmb_review_count)
                ? "{$l->gmb_rating}/5 from " . number_format($l->gmb_review_count) . ' Google reviews'
                : 'no rating yet';

            return '- ' . implode(' | ', array_filter([
                $l->name,
                $l->area ? 'area: ' . $l->area : null,
                isset($l->sb_km) ? round((float) $l->sb_km, 1) . ' km away' : null,
                $l->verified ? 'verified' : null,
                $rating,
                ($phone = self::phoneOf($l)) ? 'phone: ' . $phone : null,
            ]));
        })->implode("\n");
    }

    /**
     * The business's own number, as the listing page shows it. Service
     * Bazzar's own numbers sit on ~100 listings as a placeholder and identify
     * nobody, so those are left out and the answer sends the visitor to the
     * page instead.
     */
    private static function phoneOf($listing): ?string
    {
        $staff = \App\Support\ListingClaim::staffNumbers();

        foreach ([$listing->mobile ?? null, $listing->mobile_numbers ?? null] as $value) {
            if (is_string($value) && str_starts_with(trim($value), '[')) {
                $value = json_decode($value, true) ?: [];
            }

            foreach ((array) $value as $number) {
                $digits = MobileNumber::national($number);

                if (strlen($digits) === 10 && !in_array($digits, $staff, true)) {
                    return '+91 ' . $digits;
                }
            }
        }

        return null;
    }

    /** Links the widget shows as buttons under the answer. */
    public static function links($listings, ?Category $category, ?Location $location): array
    {
        $out = [];

        foreach ($listings as $l) {
            if ($category && $l->slug) {
                $out[] = ['label' => $l->name, 'url' => route('listings.details', ['category' => $category->slug, 'slug' => $l->slug])];
            }
        }

        if ($category && $location) {
            $out[] = [
                'label' => 'All ' . Str::plural($category->name) . ' in ' . $location->name,
                'url'   => url('/' . $location->slug . '/' . $category->slug),
            ];
        }

        return $out;
    }
}
