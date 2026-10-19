<?php

namespace App\Support;

use Illuminate\Support\Str;

/*
 * Builds the structured-data + SEO-copy for a city/category (and city/service)
 * lander and drops it into the view-data array. Shared by
 * SiteController::listingsCategory and ListingController::cityCategoryArea so
 * both routes that render listings.all emit the same schema.
 *
 * It intentionally does NOT emit a BreadcrumbList — listings.all already prints
 * one, and a second would duplicate it. Everything it does emit is derived from
 * content the page already shows, so the JSON-LD matches the visible text.
 */
class LanderSchema
{
    /**
     * @param  array<string,mixed>  $data      view-data array, mutated in place
     * @param  object|null          $item      Category or Subcategory being listed
     * @param  string|null          $cityName  resolved city name, or null for a city-less hub
     * @param  iterable             $listings  the provider listings shown on the page
     * @param  int                  $providerCount  total providers (drives the "Verified N" copy)
     * @param  string               $siteName
     */
    public static function attach(array &$data, $item, ?string $cityName, iterable $listings, int $providerCount, string $siteName): void
    {
        if (!$item) {
            return;
        }

        $currentUrl = url()->current();

        // Aggregate rating straight from the already-loaded listing rows
        // (withCount('ratings') + withAvg('ratings','ratings')) — no extra query.
        $reviewTotal = 0;
        $weighted    = 0.0;
        foreach ($listings as $listing) {
            $count = (int) ($listing->ratings_count ?? 0);
            $avg   = (float) ($listing->ratings_avg_ratings ?? 0);
            if ($count > 0) {
                $reviewTotal += $count;
                $weighted    += $avg * $count;
            }
        }
        // Only listings an admin marked Verified = Yes may be called verified. A
        // paginator holds one page, so its count would understate: say nothing then.
        $partial = $listings instanceof \Illuminate\Contracts\Pagination\Paginator
            && method_exists($listings, 'total') && $listings->total() > $listings->count();
        $verifiedCount = null;
        if (!$partial) {
            $verifiedCount = 0;
            foreach ($listings as $listing) {
                if ((int) ($listing->verified ?? 0) === 1) {
                    $verifiedCount++;
                }
            }
        }
        $data['landerVerified'] = $verifiedCount;

        $aggregate = $reviewTotal > 0
            ? ['ratingValue' => $weighted / $reviewTotal, 'reviewCount' => $reviewTotal]
            : [];

        $data['schemaService'] = [
            'name'        => $item->name,
            'description' => Str::limit(strip_tags($item->description ?? ''), 300, ''),
            'url'         => $currentUrl,
            'areaServed'  => $cityName,
            'aggregate'   => $aggregate,
        ];

        // FAQPage from the item's own FAQ copy, city-interpolated the same way
        // listings.all interpolates it for display (the 'Jaipur' placeholder ->
        // the real city), so schema and on-page text stay identical. Rewrite
        // the URL segment with the lowercase slug before the prose, so an
        // internal link like servicebazzar.com/jaipur/<cat> doesn't become the
        // mixed-case servicebazzar.com/<City>/<cat> (matches listings.all).
        $citySlug = \Illuminate\Support\Str::slug($cityName ?: 'Jaipur');
        $faqSource = str_ireplace('servicebazzar.com/jaipur', 'servicebazzar.com/' . $citySlug, $item->description ?? '');
        $faqSource = str_ireplace('Jaipur', $cityName ?: 'Jaipur', $faqSource);
        $data['schemaFaqs'] = FaqExtractor::fromHtml($faqSource);

        $data['landerFacts'] = LanderFacts::build($item, $cityName, $listings, $providerCount);

        if (!empty($data['landerFacts']['faqs'])) {
            $data['schemaFaqs'] = array_merge($data['landerFacts']['faqs'], $data['schemaFaqs']);
        }

        // ItemList of the providers on the page, each a LocalBusiness.
        $items = [];
        foreach ($listings as $listing) {
            if (empty($listing->name)) {
                continue;
            }
            $items[] = [
                'name'        => $listing->name,
                'url'         => (!empty(optional($listing->category)->slug) && !empty($listing->slug))
                    ? route('listings.details', ['category' => $listing->category->slug, 'slug' => $listing->slug])
                    : null,
                'address'     => $listing->address ?? null,
                'city'        => $listing->city ?? ($cityName ?: null),
                'state'       => $listing->state ?? null,
                'ratingValue' => $listing->ratings_avg_ratings ?? null,
                'ratingCount' => $listing->ratings_count ?? null,
                // Same validated coordinates the business's own page carries.
                'geo'         => ListingSchema::geo($listing),
            ];
        }
        $data['schemaListings'] = $items;

        // City-specific title/description. A category's own seo_title is written
        // for the city-less hub; reusing it verbatim on 60 city variants is the
        // duplicate-title problem the SEO audit flagged, so a city lander always
        // gets a city-keyed title instead.
        if (!$cityName) {
            $data['seoTitle'] = self::hubTitle((string) ($data['seoTitle'] ?? ''), $item);
        }

        if ($cityName) {
            // Use the catalogue name as it is stored (it's already Title Case),
            // with Str::ucfirst only to guarantee the title never starts on a
            // lower-case letter. This previously used lcfirst(), which lower-
            // cased the first letter of a clean name — turning "Packers And
            // Movers" into "packers And Movers" and even breaking acronyms
            // ("CCTV" -> "cCTV") across ~8,000 city-keyword titles.
            $name = Str::ucfirst(trim($item->name));

            // Some category names already end with the city ("affordable taxi
            // service Jaipur") — don't append " in Jaipur" again.
            $inCity = Str::contains(mb_strtolower($name), mb_strtolower(trim(Str::afterLast($cityName, ','))))
                ? $name
                : $name . ' in ' . $cityName;

            // "Top AC Ice Formation Repair in Jaipur – Verified Professionals":
            // a lead word, the place, and a suffix that lands the title at 50-60
            // characters. No brand; Google shows the site name on its own line.
            $display = LanderNames::plural($name, $item->slug ?? null);
            $titleMain = Str::contains(mb_strtolower($display), mb_strtolower(trim(Str::afterLast($cityName, ','))))
                ? $display
                : $display . ' in ' . $cityName;
            $data['seoTitle'] = self::landerTitle($titleMain, ($item->id ?? $item->name) . ':' . $cityName);

            // The admin's own SEO Description wins (Category/Subcategory edit page).
            // Text written for Jaipur gets the page's city, the same way the FAQ copy
            // above is interpolated, so a Delhi page never says "Jaipur".
            $ownDesc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($item->seo_description ?? ''))));
            if ($ownDesc !== '') {
                $cityOnly = trim(Str::afterLast($cityName, ','));
                $data['seoDescription'] = sb_clamp_meta_description(str_ireplace('Jaipur', $cityOnly, $ownDesc), 160);
                return;
            }

            $seed = ($item->id ?? $item->name) . ':' . $cityName;
            $data['seoDescription'] = seoTextVariant($seed, [
                "Find {$inCity}. Compare ratings, prices & contact details, and book trusted local providers on {$siteName}.",
                "Looking for {$inCity}? Explore providers, compare prices and check reviews on {$siteName}.",
                "Discover {$inCity} — compare ratings and connect with providers directly on {$siteName}.",
                "{$cityName} {$item->name}: browse listings, compare prices and reviews, and get in touch on {$siteName}.",
            ]);
        }
    }

    private const TITLE_LEADS = ['Top', 'Best', 'Trusted', 'Verified'];

    private const TITLE_SUFFIXES = [
        'Reviews & Contact Details',
        'Find Trusted Experts',
        'Verified Professionals',
        'Trusted Local Experts',
        'Compare Ratings & Reviews',
        'Verified Experts',
        'Top Rated',
    ];

    /**
     * Lead word + $main + " – " + suffix, aiming for 50-60 characters. The lead
     * and suffix are picked from $seed, so pages vary but each keeps the same
     * title on every render. A suffix repeating a word already in the title
     * ("Verified ... – Verified Professionals") is skipped.
     */
    /**
     * City-less hub (/listings/category/..., /listings/subcategory/...): keep the
     * admin's SEO Title wording, minus the brand, padded to 50-60 characters.
     */
    public static function hubTitle(string $adminTitle, $item): string
    {
        $hub = trim(preg_replace('/\s*[|\-–]\s*Service\s?Bazzar\s*$/i', '', $adminTitle));

        return self::landerTitle($hub !== '' ? $hub : Str::ucfirst(trim((string) $item->name)), (string) ($item->id ?? $item->name));
    }

    public static function landerTitle(string $main, string $seed): string
    {
        $main = trim(preg_replace('/\s+/', ' ', $main));
        // "24 Hours Electrician" / "24x7 Chemist" -> "24/7": shorter, and the form people search.
        $main = preg_replace('/\b24\s*(?:hours?|hrs?|x\s*7|\*\s*7|\/\s*7)\b/i', '24/7', $main);
        $words = fn (string $t) => array_map('mb_strtolower', preg_split('/\W+/u', $t, -1, PREG_SPLIT_NO_EMPTY));
        $rotate = function (array $list, string $salt) use ($seed) {
            $k = crc32($seed . $salt) % count($list);
            return array_merge(array_slice($list, $k), array_slice($list, 0, $k));
        };

        // Already in the final shape ("Best X – Top Rated", e.g. a stored admin
        // title): leave it alone rather than stack a second suffix.
        foreach (self::TITLE_SUFFIXES as $suffix) {
            if (str_ends_with($main, ' – ' . $suffix)) {
                return $main;
            }
        }

        $leads = preg_match('/^(top|best|trusted|verified)\b/i', $main) ? [''] : $rotate(self::TITLE_LEADS, ':lead');
        $suffixes = $rotate(self::TITLE_SUFFIXES, ':suffix');
        $fallback = null;

        foreach ($leads as $lead) {
            $base = $lead === '' ? $main : $lead . ' ' . $main;
            $len = mb_strlen($base);
            if ($len > 60) {
                continue;
            }
            if ($len >= 50) {
                return $base;
            }
            $have = $words($base);
            foreach ($suffixes as $suffix) {
                // Never repeat a word ("Top ... – Top Rated", "Verified ... – Verified Experts").
                if (array_intersect(array_diff($words($suffix), ['and', 'details']), $have)) {
                    continue;
                }
                $title = $base . ' – ' . $suffix;
                $tl = mb_strlen($title);
                if ($tl >= 50 && $tl <= 60) {
                    return $title;
                }
                if ($tl <= 60 && ($fallback === null || $tl > mb_strlen($fallback))) {
                    $fallback = $title;
                }
            }
            if ($fallback === null || $len > mb_strlen($fallback)) {
                $fallback = $fallback ?? $base;
            }
        }

        // Nothing fits 50-60: the longest version that stays within 60, or the
        // bare name when even that is too long (it is never cut mid-word).
        return $fallback ?? $main;
    }
}
