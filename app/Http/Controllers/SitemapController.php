<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Service;
use App\Models\Theme;
use App\Support\PageIndexability;

/**
 * The sitemap lists every category/subcategory as a /jaipur/{slug} lander.
 * Two kinds of those URLs must never be submitted:
 *
 *   - "-in-<area>" slugs (e.g. labor-contractor-service-in-delhi): the
 *     /{city}/{categoryArea} route treats "-in-" as an area separator, so it
 *     strips it, fails to find the base slug and 404s (or 301s when the area
 *     repeats the city). Neither belongs in a sitemap.
 *   - landers with no live listings in the sitemap's city: those render with
 *     noindex (see PageIndexability), so submitting them produces GSC's
 *     "Submitted URL marked noindex" and wastes crawl budget.
 *
 * Both were flooding the sitemap (~73% of the city-keyword section was 404 or
 * noindex), so the loops below are filtered to only resolvable, indexable
 * landers in SITEMAP_CITY.
 */

class SitemapController extends Controller
{
    /** Every category/subcategory lander in the sitemap is scoped to this city. */
    private const SITEMAP_CITY = 'jaipur';

    public function __construct()
    {
        try {
            $this->setting = \App\Models\Setting::first();
            $this->theme = 'themes.'.$this->setting->theme.'.';
        } catch (\Exception $e) {
            $this->setting = null;
        }
    }
    
    public function index()
    {
        // The view only ever reads a slug and an updated_at, so only those are
        // selected. Hydrating whole rows cost ~106 MB for the listings alone —
        // on an endpoint crawlers hit far more often than any human does.
        $categories = Category::where('status', 1)
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        $subcategories = Subcategory::where('status', 1)
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        // Category comes along so the sitemap can build the real canonical
        // `listings/{category}/{slug}` URL instead of the redirecting
        // `/listings/{slug}` shortcut.
        $listings = Listing::where('status', 1)
            ->select(['id', 'slug', 'category_id', 'updated_at'])
            ->with(['category' => function ($query) {
                $query->select(['id', 'slug']);
            }])
            ->get();

        $services = Service::where('status', 1)
            ->select(['id', 'slug', 'updated_at'])
            ->get();
       $blogs = Theme::where('theme_name', $this->setting->theme)
        ->where('name', 'blog')
        ->where('is_multiple', 1)
        ->get();

        // Drop category/subcategory landers that would 404/301 or render
        // noindex in SITEMAP_CITY, so the sitemap only carries live,
        // indexable URLs. See the class docblock for the why.
        [$catCounts, $subCounts] = $this->cityListingCounts(self::SITEMAP_CITY);
        $min = PageIndexability::minimumListings();

        $categories = $categories->filter(function ($c) use ($catCounts, $min) {
            return !empty($c->slug)
                && !str_contains($c->slug, '-in-')
                && (($catCounts[$c->id] ?? 0) >= $min);
        })->values();

        $subcategories = $subcategories->filter(function ($s) use ($subCounts, $min) {
            return !empty($s->slug)
                && !str_contains($s->slug, '-in-')
                && (($subCounts[$s->id] ?? 0) >= $min);
        })->values();

        return response()
            ->view('sitemap', compact('categories', 'subcategories', 'listings', 'services', 'blogs'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Count live listings per category and per subcategory within one city, so
     * the sitemap can tell which /{city}/{slug} landers actually have content
     * (and are therefore indexable). Returns [categoryId => count,
     * subcategoryId => count].
     *
     * Only the two columns needed are pulled, and only the one city's rows, so
     * this stays cheap even on the crawler-hammered sitemap endpoint.
     */
    private function cityListingCounts(string $citySlug): array
    {
        $catCounts = [];
        $subCounts = [];

        $city = Location::where('status', 1)->where('slug', $citySlug)->first();
        if (!$city) {
            return [$catCounts, $subCounts];
        }

        Listing::where('status', 1)
            ->where(function ($q) use ($city) {
                $q->where('location_id', $city->id)
                  ->orWhereRaw('LOWER(city) = ?', [strtolower($city->name)]);
            })
            ->select(['category_id', 'subcategory_ids'])
            ->chunk(1000, function ($rows) use (&$catCounts, &$subCounts) {
                foreach ($rows as $row) {
                    if (!empty($row->category_id)) {
                        $catCounts[$row->category_id] = ($catCounts[$row->category_id] ?? 0) + 1;
                    }

                    $subs = $row->subcategory_ids;
                    if (is_string($subs)) {
                        $subs = json_decode($subs, true);
                    }
                    if (is_array($subs)) {
                        foreach ($subs as $sid) {
                            $sid = (int) $sid;
                            if ($sid > 0) {
                                $subCounts[$sid] = ($subCounts[$sid] ?? 0) + 1;
                            }
                        }
                    }
                }
            });

        return [$catCounts, $subCounts];
    }
}