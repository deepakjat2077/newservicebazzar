<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Service;
use App\Models\Theme;
use App\Support\PageIndexability;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Sitemap index + section/city child sitemaps.
 *
 *   sitemap.xml               – the index (referenced from robots.txt / GSC)
 *   sitemap-pages.xml         – home, /listings, /blogs, /services, blog posts
 *   sitemap-listings.xml      – every live listing detail page
 *   sitemap-city-{slug}.xml   – one per city that has listings: that city's
 *                               indexable category & subcategory landers
 *
 * Splitting by section/city means Search Console's coverage report can be read
 * per section instead of as one 18k-URL blob, and — the bigger win — every
 * city with listings gets its category landers submitted, not just Jaipur.
 *
 * Two kinds of category/subcategory landers are still kept OUT of every child:
 *   - "-in-<area>" slugs, which the /{city}/{categoryArea} route can't resolve
 *     as a plain lander (it strips "-in-" and 404s / 301s), and
 *   - landers with no live listing in that city, which render noindex.
 * Both were flooding the old single sitemap with 404 / noindex URLs.
 */
class SitemapController extends Controller
{
    /** Recompute the per-city listing tally at most this often. */
    private const COUNTS_TTL_HOURS = 6;

    public function __construct()
    {
        try {
            $this->setting = \App\Models\Setting::first();
            $this->theme = 'themes.'.$this->setting->theme.'.';
        } catch (\Exception $e) {
            $this->setting = null;
        }
    }

    /** sitemap.xml — the index pointing at every child sitemap. */
    public function index()
    {
        $sitemaps = [
            ['loc' => route('sitemap.pages')],
            ['loc' => route('sitemap.listings')],
        ];

        foreach ($this->citiesWithListings() as $slug) {
            $sitemaps[] = ['loc' => route('sitemap.city', ['city' => $slug])];
        }

        return response()
            ->view('sitemaps.index', compact('sitemaps'))
            ->header('Content-Type', 'application/xml');
    }

    /** sitemap-pages.xml — static entry points + blog posts + service pages. */
    public function pages()
    {
        $urls = [];

        $urls[] = ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('listings'), 'priority' => '0.9', 'changefreq' => 'daily'];

        if (Route::has('blogs')) {
            $urls[] = ['loc' => route('blogs'), 'priority' => '0.8', 'changefreq' => 'weekly'];
        }
        if (Route::has('services')) {
            $urls[] = ['loc' => route('services'), 'priority' => '0.8', 'changefreq' => 'weekly'];
        }

        foreach (Service::where('status', 1)->select(['slug', 'updated_at'])->get() as $service) {
            if (empty($service->slug)) {
                continue;
            }
            $urls[] = [
                'loc'        => route('services.show', ['slug' => $service->slug]),
                'lastmod'    => optional($service->updated_at)->toAtomString(),
                'priority'   => '0.6',
                'changefreq' => 'weekly',
            ];
        }

        $blogs = Theme::where('theme_name', $this->setting->theme ?? null)
            ->where('name', 'blog')
            ->where('is_multiple', 1)
            ->get();

        foreach ($blogs as $blog) {
            $slug = $blog->content->slug ?? $blog->slug ?? null;
            if (empty($slug)) {
                continue;
            }
            $urls[] = [
                'loc'        => route('blog.details', ['slug' => $slug]),
                'lastmod'    => optional($blog->updated_at)->toAtomString(),
                'priority'   => '0.6',
                'changefreq' => 'weekly',
            ];
        }

        return $this->urlset($urls);
    }

    /** sitemap-listings.xml — every live listing's canonical detail URL. */
    public function listingsSitemap()
    {
        $urls = [];

        Listing::where('status', 1)
            ->select(['id', 'slug', 'category_id', 'updated_at'])
            ->with(['category' => function ($q) {
                $q->select(['id', 'slug']);
            }])
            ->chunk(2000, function ($listings) use (&$urls) {
                foreach ($listings as $listing) {
                    if (empty($listing->slug) || empty($listing->category?->slug)) {
                        continue;
                    }
                    $urls[] = [
                        'loc' => route('listings.details', [
                            'category' => $listing->category->slug,
                            'slug'     => $listing->slug,
                        ]),
                        'lastmod'    => optional($listing->updated_at)->toAtomString(),
                        'priority'   => '0.7',
                        'changefreq' => 'weekly',
                    ];
                }
            });

        return $this->urlset($urls);
    }

    /** sitemap-city-{slug}.xml — one city's indexable category/subcategory landers. */
    public function city($city)
    {
        $location = Location::where('status', 1)->where('slug', $city)->first();
        if (!$location) {
            abort(404);
        }

        $counts = $this->cityCounts();
        $catCounts = $counts[$location->id]['cat'] ?? [];
        $subCounts = $counts[$location->id]['sub'] ?? [];
        $min = PageIndexability::minimumListings();

        $urls = [];

        $categories = Category::where('status', 1)->select(['id', 'slug', 'updated_at'])->get();
        foreach ($categories as $c) {
            if (empty($c->slug) || str_contains($c->slug, '-in-')) {
                continue;
            }
            if (($catCounts[$c->id] ?? 0) < $min) {
                continue;
            }
            $urls[] = [
                'loc'        => url('/' . $city . '/' . $c->slug),
                'lastmod'    => optional($c->updated_at)->toAtomString(),
                'priority'   => '0.8',
                'changefreq' => 'daily',
            ];
        }

        $subcategories = Subcategory::where('status', 1)->select(['id', 'slug', 'updated_at'])->get();
        foreach ($subcategories as $s) {
            if (empty($s->slug) || str_contains($s->slug, '-in-')) {
                continue;
            }
            if (($subCounts[$s->id] ?? 0) < $min) {
                continue;
            }
            $urls[] = [
                'loc'        => url('/' . $city . '/' . $s->slug),
                'lastmod'    => optional($s->updated_at)->toAtomString(),
                'priority'   => '0.7',
                'changefreq' => 'daily',
            ];
        }

        return $this->urlset($urls);
    }

    /** City slugs (status=1) that have at least one indexable lander. */
    private function citiesWithListings(): array
    {
        $counts = $this->cityCounts();
        if (empty($counts)) {
            return [];
        }

        return Location::where('status', 1)
            ->whereIn('id', array_keys($counts))
            ->whereNotNull('slug')
            ->orderByDesc('is_popular')
            ->orderBy('name')
            ->pluck('slug')
            ->all();
    }

    /**
     * Per-city listing tally: [locationId => ['cat' => [catId => n], 'sub' => [subId => n]]].
     *
     * A listing counts toward a city the same way the city/category page counts
     * it — by location_id, and also by a `city` text field that names a known
     * location (so both are reflected here and the sitemap can't disagree with
     * the page's own index/noindex decision). Cached, since the sitemap is a
     * crawler-hammered endpoint and this walks every live listing.
     */
    private function cityCounts(): array
    {
        return Cache::remember('sitemap:city_counts:v1', now()->addHours(self::COUNTS_TTL_HOURS), function () {
            $nameToLoc = Location::where('status', 1)
                ->get(['id', 'name'])
                ->mapWithKeys(function ($l) {
                    return [strtolower(trim($l->name)) => $l->id];
                });

            $counts = [];

            Listing::where('status', 1)
                ->select(['location_id', 'city', 'category_id', 'subcategory_ids'])
                ->chunk(2000, function ($rows) use (&$counts, $nameToLoc) {
                    foreach ($rows as $r) {
                        // Set of location ids this listing belongs to.
                        $locIds = [];
                        if (!empty($r->location_id)) {
                            $locIds[$r->location_id] = true;
                        }
                        if (!empty($r->city)) {
                            $byName = $nameToLoc[strtolower(trim($r->city))] ?? null;
                            if ($byName) {
                                $locIds[$byName] = true;
                            }
                        }
                        if (empty($locIds)) {
                            continue;
                        }

                        $subs = $r->subcategory_ids;
                        if (is_string($subs)) {
                            $subs = json_decode($subs, true);
                        }
                        $subs = is_array($subs) ? $subs : [];

                        foreach (array_keys($locIds) as $locId) {
                            if (!isset($counts[$locId])) {
                                $counts[$locId] = ['cat' => [], 'sub' => []];
                            }
                            if (!empty($r->category_id)) {
                                $counts[$locId]['cat'][$r->category_id] =
                                    ($counts[$locId]['cat'][$r->category_id] ?? 0) + 1;
                            }
                            foreach ($subs as $sid) {
                                $sid = (int) $sid;
                                if ($sid > 0) {
                                    $counts[$locId]['sub'][$sid] =
                                        ($counts[$locId]['sub'][$sid] ?? 0) + 1;
                                }
                            }
                        }
                    }
                });

            return $counts;
        });
    }

    private function urlset(array $urls)
    {
        return response()
            ->view('sitemaps.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
