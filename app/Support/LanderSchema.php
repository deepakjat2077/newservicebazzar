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
        // the real city), so schema and on-page text stay identical.
        $faqSource = str_ireplace('Jaipur', $cityName ?: 'Jaipur', $item->description ?? '');
        $data['schemaFaqs'] = FaqExtractor::fromHtml($faqSource);

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
            ];
        }
        $data['schemaListings'] = $items;

        // City-specific title/description. A category's own seo_title is written
        // for the city-less hub; reusing it verbatim on 60 city variants is the
        // duplicate-title problem the SEO audit flagged, so a city lander always
        // gets a city-keyed title instead.
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
            $inCity = Str::endsWith(mb_strtolower($name), mb_strtolower($cityName))
                ? $name
                : $name . ' in ' . $cityName;

            $countLabel = $providerCount > 0 ? "{$providerCount} verified " : '';

            // Build the title, then shorten progressively so long category
            // names don't blow past Google's ~60-char limit: drop the brand,
            // then the "N verified" prefix, then truncate as a last resort.
            $full = trim($countLabel . $inCity) . ' | ' . $siteName;
            if (mb_strlen($full) > 60) {
                $full = trim($countLabel . $inCity);
            }
            if (mb_strlen($full) > 60) {
                $full = $inCity;
            }
            if (mb_strlen($full) > 60) {
                $full = rtrim(mb_substr($full, 0, 59)) . '…';
            }
            $data['seoTitle'] = $full;

            $countPhrase = $providerCount > 0 ? "{$providerCount} verified" : 'verified';
            $data['seoDescription'] = "Find {$countPhrase} {$item->name} in {$cityName}. "
                . "Compare ratings, prices & contact details, and book trusted local providers on {$siteName}.";
        }
    }
}
