<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;

/**
 * Page-specific facts for a city x category lander, computed from the listings
 * the page already loaded (no extra queries). Gives every lander content of its
 * own instead of the same template text with a different city name.
 */
class LanderFacts
{
    private const MIN_REVIEWS_FOR_TOP = 3;

    private const MIN_REVIEWS_FOR_AVG = 3;

    public static function build($item, ?string $place, iterable $listings, int $providerCount): ?array
    {
        if (!$item || !$place || $providerCount < 1) {
            return null;
        }

        // A paginated set only holds one page: stats from it would misstate the whole.
        if ($listings instanceof Paginator && method_exists($listings, 'total') && $listings->total() > $listings->count()) {
            return null;
        }

        $rows = collect($listings)->filter(fn ($l) => !empty($l->name))->values();

        if ($rows->isEmpty()) {
            return null;
        }

        $isAreaPage = str_contains($place, ',');
        $city = trim(Str::afterLast($place, ','));
        $service = trim($item->name);

        $verified = $rows->filter(fn ($l) => (int) ($l->verified ?? 0) === 1)->count();
        $trusted = $rows->filter(fn ($l) => (int) ($l->trust ?? 0) === 1)->count();

        $reviews = 0;
        $weighted = 0.0;
        $top = null;

        foreach ($rows as $l) {
            $n = (int) ($l->ratings_count ?? 0);
            $avg = (float) ($l->ratings_avg_ratings ?? 0);

            if ($n > 0) {
                $reviews += $n;
                $weighted += $avg * $n;
            }

            if ($n >= self::MIN_REVIEWS_FOR_TOP && (!$top || $avg > $top['rating'] || ($avg == $top['rating'] && $n > $top['reviews']))) {
                $top = ['listing' => $l, 'rating' => round($avg, 1), 'reviews' => $n];
            }
        }

        $areas = collect();

        if (!$isAreaPage) {
            $areas = $rows->map(fn ($l) => trim((string) ($l->area ?? '')))
                ->filter(fn ($a) => $a !== '' && mb_strtolower($a) !== mb_strtolower($city))
                ->map(fn ($a) => Str::title(mb_strtolower($a)))
                ->countBy()
                ->sortDesc()
                ->take(6);

            // A list where every area has one listing says nothing a visitor can use.
            if ($areas->count() < 2 || $areas->max() < 2) {
                $areas = collect();
            }
        }

        $facts = [
            'service' => $service,
            'place' => $place,
            'providers' => $providerCount,
            'verified' => $verified,
            'trusted' => $trusted,
            'reviews' => $reviews,
            'avgRating' => $reviews >= self::MIN_REVIEWS_FOR_AVG ? round($weighted / $reviews, 1) : null,
            'top' => null,
            'areas' => $areas->all(),
        ];

        if ($top) {
            $l = $top['listing'];
            $facts['top'] = [
                'name' => $l->name,
                'rating' => $top['rating'],
                'reviews' => $top['reviews'],
                'url' => (!empty(optional($l->category)->slug) && !empty($l->slug))
                    ? route('listings.details', ['category' => $l->category->slug, 'slug' => $l->slug])
                    : null,
            ];
        }

        $facts['faqs'] = static::faqs($facts);

        return $facts;
    }

    /** Plain-text Q&As; the page renders exactly these so schema and visible text match. */
    private static function faqs(array $f): array
    {
        $s = $f['service'];
        $p = $f['place'];
        $out = [];

        $a = $f['providers'] === 1
            ? "There is 1 business listed under {$s} in {$p} on Service Bazzar"
            : "There are {$f['providers']} {$s} listed in {$p} on Service Bazzar";
        if ($f['verified'] > 0) {
            $a .= $f['providers'] === 1 ? ', and it is verified' : ", of which {$f['verified']} " . ($f['verified'] === 1 ? 'is' : 'are') . ' verified';
        }
        $out[] = ['question' => "How many {$s} are there in {$p}?", 'answer' => $a . '.'];

        if ($f['areas']) {
            $list = collect($f['areas'])->map(fn ($n, $area) => $n > 1 ? "{$area} ({$n})" : $area)->implode(', ');
            $out[] = [
                'question' => "Which areas of {$p} have the most {$s}?",
                'answer' => "Most listed {$s} in {$p} are in " . rtrim($list, '.') . '.',
            ];
        }

        if ($f['top']) {
            $out[] = [
                'question' => "Which {$s} in {$p} has the best rating?",
                'answer' => "{$f['top']['name']} has the highest rating among {$s} in {$p} with at least "
                    . self::MIN_REVIEWS_FOR_TOP . " reviews: {$f['top']['rating']} out of 5 from {$f['top']['reviews']} reviews.",
            ];
        } elseif ($f['avgRating'] !== null) {
            $out[] = [
                'question' => "What is the average rating of {$s} in {$p}?",
                'answer' => "{$s} in {$p} have an average rating of {$f['avgRating']} out of 5 from {$f['reviews']} customer reviews.",
            ];
        }

        return $out;
    }
}
