<?php

namespace App\Support;

use App\Models\HomeFeaturedListing;
use App\Models\Listing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Drops the cached HTML of the pages that show a listing most prominently
 * (homepage + its featured-block data, its city hub, its city + category page,
 * its own page), so a change such as a new photo, a Trust/Verified badge or a
 * new rating shows at once instead of after the 30-minute page cache.
 * Area pages (/city/category-in-area) are not listed and catch up on expiry.
 */
class ListingPageCache
{
    public static function forget(?Listing $listing): void
    {
        if (!$listing) {
            return;
        }

        try {
            $listing->loadMissing(['category:id,slug', 'location:id,name']);
            $city = Str::slug(optional($listing->location)->name ?: (string) $listing->city);
            $catSlug = optional($listing->category)->slug;

            $paths = ['/'];
            if ($city) {
                $paths[] = $city;
                if ($catSlug) {
                    $paths[] = $city . '/' . $catSlug;
                }
            }
            if ($catSlug && $listing->slug) {
                $paths[] = 'listings/' . $catSlug . '/' . $listing->slug;
            }

            Cache::forget(HomeFeaturedListing::CACHE_KEY);

            $store = Cache::store(config('cache.public_pages.store'));
            foreach ($paths as $path) {
                $store->forget('page:' . sha1('www.servicebazzar.com|' . $path));
            }
        } catch (\Throwable $e) {
            report($e); // a cache problem must never block the save that triggered it
        }
    }
}
