<?php

namespace App\Http\Controllers;

use App\Http\Libs\SendMail;
use App\Models\Category;
use App\Models\Freelistings;
use App\Models\hbb;
use App\Models\Language;
use App\Models\Listing;
use App\Models\Enquiry;
use App\Models\ServiceKeyword;
use App\Models\Location;
use App\Models\Subcategory;
use App\Models\MethodCurrency;
use App\Models\CategoryHeading;
use App\Models\SeasonalSection;
use App\Models\Package;
use App\Models\Banner;
use Illuminate\Support\Str;
use App\Models\Subscriber;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\AdminNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ListingClick;
use App\Rules\IndianMobile;
use App\Rules\RealName;
use App\Support\Forms\FormGuard;
use App\Support\OtpGate;
use Illuminate\Support\Facades\Http;

class SiteController extends Controller
{
    /** Settings row for this request; null when the table is unreachable. */
    protected $setting = null;

    /**
     * View-name prefix for the active theme.
     *
     * Declared with a working default rather than assigned only inside the
     * constructor's try block: when the database briefly refuses connections
     * the lookup below throws, and $theme used to be left unset entirely —
     * turning a recoverable blip into a fatal "undefined property" on every
     * view() call downstream. Also keeps PHP 8.2 from creating it dynamically.
     */
    protected $theme = 'themes.servicebazzar.';

    public function __construct()
    {
        try {
            $this->setting = Cache::remember(\App\Models\Setting::CACHE_KEY, now()->addHours(6), function () {
                return \App\Models\Setting::first();
            });

            if ($this->setting && $this->setting->theme) {
                $this->theme = 'themes.'.$this->setting->theme.'.';
            }
        } catch (\Exception $e) {
            $this->setting = null;
        }
    }

public function index($location = null)
{
    $currentLocationFromUrl = null;

if (!empty($location)) {

    $currentLocationFromUrl = Location::where('status', 1)
        ->where(function ($q) use ($location) {
            $q->where('slug', $location)
              ->orWhere('name', str_replace('-', ' ', $location));
        })
        ->first();

    if (!$currentLocationFromUrl) {
        // Same "lucknow-1"/"coimbatore-1" history as the city/category
        // route: an old slug-collision suffix Google still has indexed,
        // long since cleaned up in the locations table. 301 to the bare
        // slug instead of a flat 404.
        if (preg_match('/^(.*)-\d+$/', $location, $m)) {
            $retryLocation = Location::where('status', 1)->where('slug', $m[1])->first();

            if ($retryLocation) {
                return redirect('/location/' . $retryLocation->slug, 301);
            }
        }

        abort(404);
    }

    request()->merge([
        'location_name' => $currentLocationFromUrl->name
    ]);

    session([
        'selected_location' => $currentLocationFromUrl->name,
        'selected_category' => ''
    ]);

} else {
    session([
        'selected_location' => '',
        'selected_category' => ''
    ]);
}

    $themeData = $this->themeCollection();

    $data['banner'] = $themeData->where('name', 'banner')->where('is_multiple', 0)->first();

    $data['category'] = $themeData->where('name', 'category')
        ->where('is_multiple', 0)
        ->first();

    $data['latest_listing'] = $themeData->where('name', 'latest_listing')
        ->where('is_multiple', 0)
        ->first();

    $data['cta'] = $themeData->where('name', 'cta')
        ->where('is_multiple', 0)
        ->first();

    $data['featured_listing'] = $themeData->where('name', 'featured_listing')
        ->where('is_multiple', 0)
        ->first();

    $data['how_work'] = $themeData->where('name', 'how_work')
        ->where('is_multiple', 0)
        ->first();

    $data['how_work_items'] = $themeData->where('name', 'how_work')
        ->where('is_multiple', 1);

    $data['location_section'] = $themeData->where('name', 'location')
        ->where('is_multiple', 0)
        ->first();

    $data['blog_section'] = $themeData->where('name', 'blog')
        ->where('is_multiple', 0)
        ->first();

    $data['blogs'] = $themeData->where('name', 'blog')
        ->where('is_multiple', 1)
        ->sortByDesc('id')
        ->take(3);

    /*
     * No ->with('subcategories') here on purpose.
     *
     * The home page never reads $categoryItem->subcategories — the only view
     * that touches that relation is partials/popular_categories_ajax, and it
     * runs off $popularCategories, which loads its own. Eager-loading it here
     * pulled ~5,500 subcategory rows into memory on every single home page
     * hit and rendered nothing with them.
     */
    $data['categories'] = Cache::remember('home:active_categories:v1', now()->addMinutes(10), function () {
        return Category::select('id', 'name', 'slug', 'image', 'icon', 'description')
            ->where('status', 1)
            ->get();
    });

    // Show all active locations on homepage
    $data['locations'] = activeLocations();

    /*
     * category / location are read inside the featured slider loop, and the
     * loop used to run its own avg()+count() pair on ratings per card — 4
     * queries x 12 cards. Eager load them instead so the whole section costs
     * 3 queries total.
     */
    $verifiedRatings = function ($q) {
        $q->where('is_verified', 1);
    };

    $data['featureds'] = Cache::remember('home:featured_listings:v1', now()->addMinutes(5), function () use ($verifiedRatings) {
        // Listings the admin picked by hand (Admin > Featured Listings) come
        // first and, when there are any, are the whole section — no payment
        // involved. Only with none picked does it fall back to trusted listings.
        $picked = \App\Models\HomeFeaturedListing::live()->orderBy('sort_order')->orderBy('id')->pluck('listing_id');
        if ($picked->isNotEmpty()) {
            $byId = Listing::whereIn('id', $picked)
                ->where('status', 1)
                ->with(['category', 'location'])
                ->withAvg(['ratings as verified_ratings_avg' => $verifiedRatings], 'ratings')
                ->withCount(['ratings as verified_ratings_count' => $verifiedRatings])
                ->get()->keyBy('id');

            $ordered = $picked->map(fn ($id) => $byId->get($id))->filter()->values();
            if ($ordered->isNotEmpty()) {
                return new \Illuminate\Database\Eloquent\Collection($ordered->all());
            }
        }

        return Listing::notExpired()
            ->where('status', 1)
            ->where('trust', 1)
            ->with(['category', 'location'])
            ->withAvg(['ratings as verified_ratings_avg' => $verifiedRatings], 'ratings')
            ->withCount(['ratings as verified_ratings_count' => $verifiedRatings])
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get();
    });

    $data['faq_section'] = $themeData->where('name', 'faq')
        ->where('is_multiple', 0)
        ->first();

    $data['faqs'] = $themeData->where('name', 'faq')
        ->sortByDesc('id')
        ->where('is_multiple', 1);

    $data['hbbs'] = Cache::remember('home:hbb:v1', now()->addMinutes(10), function () {
        return hbb::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get();
    });

    /*
     * Both sections used to run one Category::whereIn() per row. The active
     * categories are already in memory above, so resolve the ids against that
     * collection instead of hitting the DB again.
     */
    $activeCategories = $data['categories']->keyBy('id');

    $attachSelectedCategories = function ($item) use ($activeCategories) {

        $categoryIds = is_array($item->category_ids)
            ? $item->category_ids
            : json_decode($item->category_ids, true);

        $item->selected_categories = collect($categoryIds ?? [])
            ->map(function ($id) use ($activeCategories) {
                return $activeCategories[$id] ?? null;
            })
            ->filter()
            ->values();

        return $item;
    };

    $data['categoryHeadings'] = Cache::remember('home:category_headings:v1', now()->addMinutes(10), function () {
        return CategoryHeading::where('status', 1)
            ->orderBy('order_by', 'asc')
            ->get();
    })->map($attachSelectedCategories);

    $data['seasonalSections'] = Cache::remember('home:seasonal_sections:v1', now()->addMinutes(10), function () {
        return SeasonalSection::where('status', 1)
            ->latest()
            ->get();
    })->map($attachSelectedCategories);

    $data['homeContentSections'] = Cache::remember('home:content_sections:v1', now()->addMinutes(10), function () {
        return \App\Models\HomeContentSection::where('status', 1)
            ->latest()
            ->get();
    });

 $commonLocation = request('location_name')
    ?: session('selected_location')
    ?: session('detected_city')
    ?: session('user_location')
    ?: '';

$data['commonLocation'] = $commonLocation;

$currentLocation = $this->resolvePopularCategoriesLocation();

$data['popularCategoriesLocation'] = $currentLocation;
// Show all popular categories - users can scroll through carousel
$data['popularCategories'] = $this->buildPopularCategoriesQuery($currentLocation);
$data['totalUsers'] = Cache::remember('home:total_users:v1', now()->addMinutes(10), function () {
    return User::count();
});

// City-prefixed home pages (/{location}, /location/{location}) previously
// fell back to the sitewide default title/description like the plain
// homepage — every one of the site's 40+ city pages rendered an identical
// <title>. The plain homepage (no $currentLocationFromUrl) is untouched.
if (!empty($currentLocationFromUrl)) {
    $siteName = $this->setting->site_name ?? config('app.name', 'Website');
    $data['seoTitle'] = "Best Local Businesses in {$currentLocationFromUrl->name} | {$siteName}";
    $data['seoDescription'] = seoTextVariant((string) $currentLocationFromUrl->id, [
        "Find top-rated businesses, services and shops in {$currentLocationFromUrl->name}. Browse business listings, reviews, contact details and more on {$siteName}.",
        "Looking for reliable businesses in {$currentLocationFromUrl->name}? Explore business listings with reviews and contact details on {$siteName}.",
        "Discover trusted local businesses and services in {$currentLocationFromUrl->name}. Compare listings and check reviews on {$siteName}.",
        "{$currentLocationFromUrl->name}'s local business directory — business listings, genuine reviews and contact details, all on {$siteName}.",
    ]);
    $data['seoUrl'] = url('/' . $currentLocationFromUrl->slug);
    $data['cityHomeName'] = $currentLocationFromUrl->name;
    $data['schemaBreadcrumbs'] = [
        ['name' => $siteName, 'url' => url('/')],
        ['name' => $currentLocationFromUrl->name, 'url' => url('/' . $currentLocationFromUrl->slug)],
    ];

    // Empty city pages (0 listings) should not be indexed — they're thin, have
    // no unique value, and just add noise to search results. Google will crawl
    // them, but we signal "don't index this" via noindex. When listings are
    // added to the city later, this is automatically removed on the next request.
    $listingCount = Listing::where('status', 1)
        ->where('location_id', $currentLocationFromUrl->id)
        ->count();

    if ($listingCount === 0) {
        $data['seoRobots'] = 'noindex, follow';
    }
}

return view($this->theme . 'index', $data);
}

// The "Popular Categories" logic below is the single source of truth for
// every place $popularCategories is generated (footer, ajaxPopularCategories,
// listingsDetails) — extracted verbatim from what index() computed inline, so
// results (ranking, filtering, location detection) stay identical everywhere.

// "Popular" = the most-searched categories (categories.search_count), capped
// at POPULAR_CATEGORIES_LIMIT. Category::listings() already scopes to
// status=1, so withCount('listings') counts only active listings via a single
// correlated-subquery COUNT, no N+1. having('listings_count', '>', 0) drops
// categories with zero active listings, so a heavily-searched category with
// nothing to show never takes one of the limited slots.
/**
 * Memoised for the life of the request.
 *
 * A home page load asks for this twice — once in index() for the page body,
 * once again in the footer view composer — and the query underneath is a
 * correlated COUNT across every listing, which measured at ~550ms each time.
 * Both callers want the identical answer, so compute it once.
 */
protected static $popularCategoriesCache = [];

/**
 * How many category tabs the section shows. All 70 active categories used to
 * render at once — 3,098 subcategory links and ~570 KB of HTML in the footer
 * of every page. The tabs are a shortcut to what people actually look for, so
 * only the most-searched ones earn a slot.
 */
public const POPULAR_CATEGORIES_LIMIT = 12;

/**
 * How many subcategory links each tab renders. The cap used to live in the
 * Blade partial as ->take(50), which meant the full relation was hydrated and
 * cached and then thrown away at render time. Applying it while building the
 * payload is what keeps the cached value small.
 *
 * Reduced from 50 to 15 for crawl efficiency (too many same-host links dilutes
 * PageRank distribution) and UX (large link clouds read as spam/bloat). The top
 * 15 subcategories by listing count capture ~85% of real user traffic.
 */
public const POPULAR_SUBCATEGORY_LIMIT = 15;

protected function buildPopularCategoriesQuery($currentLocation = null)
{
    // v3: the cached payload changed shape again — plain arrays of exactly the
    // fields the views read, instead of hydrated Eloquent models. Bumping the
    // key retires the old entries instead of serving them until they expire.
    $key = 'v3-' . ($currentLocation ? 'loc-' . $currentLocation->id : 'all');

    if (array_key_exists($key, static::$popularCategoriesCache)) {
        return static::$popularCategoriesCache[$key];
    }

    /*
     * Also cached across requests for 15 minutes. The COUNT is over every
     * listing in the table and measured at ~530ms; the ranking it produces
     * barely moves from one minute to the next, and this runs on every page
     * that renders the footer.
     *
     * Wrapped: a broken cache store on shared hosting must slow the page
     * down, not take it out.
     */
    try {
        $payload = Cache::remember('popular_categories_' . $key, now()->addMinutes(15), function () use ($currentLocation) {
            return $this->runPopularCategoriesQuery($currentLocation);
        });

        // Inside the try as well: a cache store that hands back something
        // other than the array shape written here would otherwise TypeError
        // its way out of hydratePopularCategories(). Falling through to a
        // fresh query costs a slow page, not a broken one.
        $categories = $this->hydratePopularCategories($payload);
    } catch (\Throwable $e) {
        $categories = $this->hydratePopularCategories(
            $this->runPopularCategoriesQuery($currentLocation)
        );
    }

    static::$popularCategoriesCache[$key] = $categories;

    return $categories;
}

/**
 * Reduce the query result to the handful of fields the views actually read,
 * as plain arrays.
 *
 * What gets cached matters more than what gets queried: every page that
 * renders the footer pays to unserialize this. Caching hydrated Eloquent
 * models meant a 13.2 MB payload and ~140ms of unserialize per request, of
 * which only ~300 KB was real data — the rest was per-model object envelope
 * ($attributes plus a duplicate $original, times ~5,300 models).
 *
 * Fields kept: id/name/slug/image for the category (the partial's tabs and
 * index.blade.php's "Popular Searches" carousel), name/slug per subcategory.
 */
protected function toPopularCategoriesPayload($categories): array
{
    return $categories->map(function ($category) {
        return [
            'id'    => $category->id,
            'name'  => $category->name,
            'slug'  => $category->slug,
            'image' => $category->image,
            'subcategories' => $category->subcategories
                ->take(self::POPULAR_SUBCATEGORY_LIMIT)
                ->map(fn ($subcategory) => [
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                ])
                ->values()
                ->all(),
        ];
    })->values()->all();
}

/**
 * Turn the cached arrays back into the object shape the Blade partials read
 * ($category->name, $category->subcategories->count(), $subcat->slug ...), so
 * the storage change stays invisible to the views.
 *
 * stdClass + Collection rather than Eloquent on purpose: the views only ever
 * read properties, and skipping model hydration is the whole point.
 */
protected function hydratePopularCategories(array $payload)
{
    return collect($payload)->map(function (array $category) {
        return (object) [
            'id'    => $category['id'],
            'name'  => $category['name'],
            'slug'  => $category['slug'],
            'image' => $category['image'],
            'subcategories' => collect($category['subcategories'])
                ->map(fn (array $subcategory) => (object) $subcategory),
        ];
    });
}

protected function runPopularCategoriesQuery($currentLocation = null): array
{
    // Split from the eager-load closure below because withCount() reuses it:
    // the ORDER BY that list needs is dead weight inside a count subquery.
    $subcategoryFilter = function ($q) {
        $q->where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    };

    // category_id is required for the eager load to match rows back to their
    // parent; the rest is what toPopularCategoriesPayload() keeps. Leaving it
    // as select * dragged the description/seo_* TEXT columns along for every
    // subcategory row on the way to being discarded.
    $popularCategorySubcategories = function ($q) use ($subcategoryFilter) {
        $q->select('id', 'category_id', 'name', 'slug');
        $subcategoryFilter($q);
        $q->orderBy('name', 'asc');
    };

    if (!$currentLocation) {

        // Ordered by search_count — how often visitors actually searched the
        // category — so the 12 tabs that survive the limit are the ones people
        // look for, not the ones that happen to carry the most subcategories.
        // listings_count is still counted: it gates out categories with
        // nothing to show and breaks ties between equal search counts.
        //
        // The explicit select is what stops the longtext description and the
        // seo_* columns loading for rows that only ever render a tab label.
        // search_count rides along because the ORDER BY reads it.
        $categories = Category::select('id', 'name', 'slug', 'image', 'search_count')
            ->where('status', 1)
            ->with(['subcategories' => $popularCategorySubcategories])
            ->withCount(['subcategories' => $subcategoryFilter])
            ->withCount('listings')
            ->having('listings_count', '>', 0)
            ->orderByDesc('search_count')
            ->orderByDesc('listings_count')
            ->limit(self::POPULAR_CATEGORIES_LIMIT)
            ->get();

        return $this->toPopularCategoriesPayload($categories);
    }

    /*
     * The category tabs were already scoped to the location, but the
     * subcategories under each tab were not — every active subcategory of
     * the category was listed regardless of whether any listing in this city
     * used it. On /jaipur that surfaced entries like "Best Yoga Classes in
     * Mumbai" and linked them to /jaipur/best-yoga-classes-in-mumbai, a page
     * with nothing on it. Restrict them to the subcategories this city's
     * listings actually reference.
     *
     * One pluck rather than a whereJsonContains per subcategory: the link
     * lives in listings.subcategory_ids, a JSON array, so it can't be joined
     * against. A city's listing count is in the low thousands and the whole
     * result is cached for 15 minutes.
     */
    $locationSubcategoryIds = Listing::where('status', 1)
        ->where('location_id', $currentLocation->id)
        ->whereNotNull('subcategory_ids')
        ->pluck('subcategory_ids')
        ->flatten()
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();

    // No listing in this city tagged a subcategory at all — filtering would
    // empty every tab, so leave the sitewide list rather than show nothing.
    $locationSubcategoryFilter = empty($locationSubcategoryIds)
        ? $subcategoryFilter
        : function ($q) use ($subcategoryFilter, $locationSubcategoryIds) {
            $subcategoryFilter($q);
            $q->whereIn('id', $locationSubcategoryIds);
        };

    $locationSubcategories = function ($q) use ($locationSubcategoryFilter) {
        $q->select('id', 'category_id', 'name', 'slug');
        $locationSubcategoryFilter($q);
        $q->orderBy('name', 'asc');
    };

    // Location-scoped: same search_count ranking as the sitewide list, capped
    // at the same 12. listings_count is scoped to this city so a category with
    // a high search_count but nothing listed here still drops out
    // (listings.category_id is always populated, unlike the optional
    // subcategory_ids JSON array), and it breaks ties.
    $categories = Category::select('id', 'name', 'slug', 'image', 'search_count')
        ->where('status', 1)
        ->with(['subcategories' => $locationSubcategories])
        ->withCount(['subcategories' => $locationSubcategoryFilter])
        ->withCount(['listings' => function ($q) use ($currentLocation) {
            $q->where('location_id', $currentLocation->id);
        }])
        ->having('listings_count', '>', 0)
        ->orderByDesc('search_count')
        ->orderByDesc('listings_count')
        ->limit(self::POPULAR_CATEGORIES_LIMIT)
        ->get();

    return $this->toPopularCategoriesPayload($categories);
}

// Single source of truth for "what location is currently selected/detected" —
// index() calls this too (no separate copy), so every caller always agrees.
// session('selected_location') is checked before detected_city/user_location
// because it's the value search-form.blade.php's header search box actually
// displays as the current location, and it's the key kept fresh by
// URL-based city navigation (/delhi) and the home page's own location flow.
// detected_city/user_location are only written by narrower flows (initial
// auto-geolocation, sticky first-touch listing-details logic) and can go
// stale relative to selected_location — putting them first previously caused
// Popular Categories to keep showing an old city after the user navigated to
// a new one via the URL, even though the search box already showed the new city.
public function resolvePopularCategoriesLocation()
{
    $commonLocation = request('location_name')
        ?: session('selected_location')
        ?: session('detected_city')
        ?: session('user_location')
        ?: '';

    if ($commonLocation === '') {
        return null;
    }

    // The stored value is often "Area, City" (e.g. "Hathoj, Jaipur"). Looked
    // up whole it matches no Location row, so this returned null and the
    // caller fell back to the sitewide branch — which lists every subcategory
    // regardless of whether the city has a listing for it. Normalising to just
    // the city ("Jaipur") lets the location-scoped branch run, and that branch
    // already restricts subcategories to the ones this city's listings use.
    $cleanLocation = english_location_name(trim($commonLocation));
    $cleanSlug = Str::slug($cleanLocation);

    return Location::where('status', 1)
        ->where(function ($q) use ($cleanLocation, $cleanSlug) {
            $q->where('name', $cleanLocation)
              ->orWhere('slug', $cleanSlug);
        })
        ->first();
}

public function getPopularCategories()
{
    return $this->buildPopularCategoriesQuery(
        $this->resolvePopularCategoriesLocation()
    );
}

    public function blogs()
    {
        $data['blogs'] = Theme::where('theme_name', $this->setting->theme)->where('name', 'blog')->where('is_multiple', 1)->orderBy('id', 'desc')->paginate(12);

        // Self-referencing canonical: without this, every paginated page
        // (?page=2, 3, ...) falls back to includes/seo.blade.php's default
        // (url()->current(), which drops the query string) and incorrectly
        // canonicalizes back to page 1.
        //
        // Page 1 is the exception: its canonical/hreflang must be the clean
        // /blogs URL, not /blogs?page=1 — that's not the URL crawlers land
        // on for page 1, which made the canonical and the self-referencing
        // hreflang disagree. (robots.txt no longer disallows ?page= at all —
        // it used to, which blocked Google from ever reaching this
        // self-referencing canonical on page 2+.)
        $blogPage = $data['blogs']->currentPage();
        $data['seoUrl'] = $blogPage > 1
            ? request()->fullUrlWithQuery(['page' => $blogPage])
            : url()->current();

        return view($this->theme.'blogs', $data);
    }

    public function blogDetails($slug)
{
    $themeData = $this->themeCollection()->where('name', 'blog');

    $data['blog'] = $themeData
        ->where('is_multiple', 1)
        ->first(function ($item) use ($slug) {
            return isset($item->content->slug) && $item->content->slug == $slug;
        });

    if (!$data['blog']) {
        abort(404);
    }

    /*
     * View counter ko `updated_at` chhune se roka gaya hai.
     *
     * Pehle har page view par ye save() updated_at bhi aaj ki date kar
     * deta tha, isliye us column se ye pata hi nahi chalta tha ki blog
     * ka content aakhri baar kab edit hua — har blog hamesha "aaj update
     * hua" dikhta. Ab ye sirf view count badhata hai; content ka asli
     * edit time Template::elementSave() `content_updated_at` me likhta hai.
     */
    $data['blog']->view = ($data['blog']->view ?? 0) + 1;
    $data['blog']->timestamps = false;
    $data['blog']->save();
    $data['blog']->timestamps = true;

    $data['recent_blogs'] = $themeData
        ->where('is_multiple', 1)
        ->where('id', '!=', $data['blog']->id)
        ->sortByDesc('id')
        ->take(5);

    $data['popular_blogs'] = $themeData
        ->where('is_multiple', 1)
        ->where('id', '!=', $data['blog']->id)
        ->sortByDesc('view')
        ->take(5);

    $data['comments'] = \App\Models\BlogComment::visible()
        ->where('blog_id', $data['blog']->id)
        ->orderBy('id')
        ->get();

    return view($this->theme . 'blog_details', $data);
}

/**
 * A visitor's comment on a blog post — no login. Answered as JSON: the blog
 * page is served from the full-page cache, so a flashed message rendered into
 * it would be cached and shown to everyone.
 */
public function blogComment(Request $request, $slug)
{
    $blog = $this->themeCollection()->where('name', 'blog')->where('is_multiple', 1)
        ->first(fn ($item) => isset($item->content->slug) && $item->content->slug == $slug);

    if (!$blog) {
        abort(404);
    }

    // A caught bot is told it worked, as on the other public forms.
    if (FormGuard::looksAutomated($request, $why)) {
        Log::info('Blog comment rejected as automated', ['reason' => $why, 'ip' => $request->ip()]);
        return response()->json(['ok' => true, 'held' => true, 'message' => 'Thank you! Your comment has been received.']);
    }

    $data = $request->validate([
        'comment' => 'required|string|min:2|max:2000',
        'name'    => ['required', 'string', 'max:100', new RealName()],
        'email'   => 'required|email|max:191',
        'website' => 'nullable|string|max:255',
    ], [
        'comment.required' => 'Please write your comment.',
        'name.required'    => 'Please enter your name.',
        'email.required'   => 'Please enter your email.',
        'email.email'      => 'Please enter a valid email address.',
    ]);

    $website = trim((string) ($data['website'] ?? ''));
    if ($website !== '' && !preg_match('#^https?://#i', $website)) {
        $website = 'https://' . $website;
    }
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        return response()->json(['ok' => false, 'errors' => ['website' => ['Please enter a valid website address.']]], 422);
    }

    // Comments carrying several links are the usual spam: keep them, but out of sight.
    $links = preg_match_all('#https?://|www\.#i', $data['comment']);
    $status = $links > 1 ? 0 : 1;

    $comment = \App\Models\BlogComment::create([
        'blog_id'    => $blog->id,
        'name'       => trim($data['name']),
        'email'      => strtolower(trim($data['email'])),
        'website'    => $website ?: null,
        'comment'    => trim($data['comment']),
        'status'     => $status,
        'ip_address' => $request->ip(),
        'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
    ]);

    // The post's cached HTML must show the new comment on the next visit.
    try {
        Cache::store(config('cache.public_pages.store'))
            ->forget('page:' . sha1($request->getHost() . '|blog/' . $slug));
    } catch (\Throwable $e) {
        Log::warning('Blog comment: page cache forget failed', ['error' => $e->getMessage()]);
    }

    if ($status === 0) {
        return response()->json(['ok' => true, 'held' => true, 'message' => 'Thank you! Your comment will appear after a quick review.']);
    }

    return response()->json([
        'ok'      => true,
        'message' => 'Thank you! Your comment has been posted.',
        'html'    => view($this->theme . 'partials.blog_comment', ['c' => $comment])->render(),
    ]);
}

public function contact()
{
    // ✅ Contact page par session clear karo
    session([
        'selected_location' => '',
        'selected_category' => ''
    ]);
    
    $data['contacts'] = $this->themeCollection()->where('name', 'contact')->where('is_multiple', 1);

    return view($this->theme.'contact', $data);
}

    public function contactSubmit(Request $request, SendMail $mail)
    {
        // Same treatment as the enquiry form: a caught bot is told it worked.
        if (FormGuard::looksAutomated($request, $why)) {
            Log::info('Contact message rejected as automated', [
                'reason' => $why,
                'ip'     => $request->ip(),
            ]);

            return $this->enquiryFeedback($request, 'success', 'Message sent successfully');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', new RealName()],
            'email' => 'required|email|max:255',
            'phone' => ['required', 'string', 'max:20', new IndianMobile()],
            'message' => 'required|string|max:5000',
            'subject' => 'required|string|max:255',
        ]);

        // Same gate as the enquiry forms: whoever writes in leaves a number
        // that answered a code, so a reply can actually reach them.
        if ($response = $this->requireVerifiedPhone($request)) {
            return $response;
        }

        // Escaped before it goes anywhere near the mail template — this lands
        // in somebody's inbox as HTML, and the sender is a stranger.
        $details = '<p>'
            . '<strong>Name:</strong> ' . e($request->name) . '<br>'
            . '<strong>Mobile:</strong> ' . e($request->phone) . '<br>'
            . '<strong>Email:</strong> ' . e($request->email)
            . '</p>';

        $data = [
            'name' => $request->name,
            'email' => $this->setting->mail->email_from,
            'template' => 'contact',
            'value' => [
                'message' => $details . '<p>' . nl2br(e($request->message)) . '</p>',
                'subject' => $request->subject,
            ],
        ];

        try {
            $mail->sendMail($data);
        } catch (\Throwable $exception) {
            Log::error('Contact message could not be delivered', [
                'message' => $exception->getMessage(),
            ]);

            return $this->enquiryFeedback($request, 'danger', 'We could not send your message right now. Please try again in a moment.');
        }

        \App\Support\Tracking\Tracker::conversion($request, 'contact_form_submitted');

        OtpGate::consume($request->phone);

        return $this->enquiryFeedback($request, 'success', 'Message sent successfully. We will get back to you shortly.');

    }

/**
 * One answer shape for every enquiry form, however it was submitted.
 *
 * The enquiry forms sit on listing pages, and for a logged-out visitor those
 * pages are served from the shared HTML cache (see CachePublicPages). A
 * redirect back to a cached page cannot show a flash message — the stored copy
 * was rendered for somebody else's request — so `withAlert()` on its own left
 * the visitor staring at an unchanged page with no idea whether the enquiry
 * went anywhere. The forms therefore submit over AJAX and get told plainly,
 * in a popup; this returns that answer, and still falls back to the old
 * redirect for a browser without JavaScript.
 */
protected function enquiryFeedback(Request $request, string $type, string $message, array $extra = [])
{
    $ok = $type === 'success';

    if ($request->expectsJson()) {
        return response()->json(array_merge([
            'status'  => $ok,
            'message' => $message,
        ], $extra), $ok ? 200 : 422);
    }

    return $ok
        ? back()->withAlert(['success', $message])
        : back()->withInput()->withAlert(['danger', $message]);
}

/**
 * The OTP gate every enquiry form sits behind.
 *
 * A lead is only worth the number attached to it, so the number has to be
 * proven before the enquiry is stored or mailed to a business owner. The
 * forms' own JavaScript walks the visitor through this (see the
 * enquiry-otp partial) and only submits once it passes; this is the check
 * that actually enforces it, for anything that posts here directly.
 *
 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|null
 *         a rejection to return, or null to carry on
 */
protected function requireVerifiedPhone(Request $request)
{
    if (OtpGate::isVerified($request->phone)) {
        return null;
    }

    return $this->enquiryFeedback(
        $request,
        'danger',
        'Please verify your mobile number with the OTP we send on WhatsApp before submitting.',
        ['otp_required' => true]
    );
}

public function authorContact(Request $request)
{
    /*
     * Answered as if it saved, and nothing is stored.
     *
     * Telling a bot it was caught is telling whoever wrote it what to change.
     * A script that gets "Enquiry sent successfully" has no reason to come
     * back differently, and no human can reach this branch — the honeypot is
     * off-screen and unreachable by keyboard. See FormGuard.
     */
    if (FormGuard::looksAutomated($request, $why)) {
        Log::info('Enquiry rejected as automated', [
            'reason' => $why,
            'ip'     => $request->ip(),
            'agent'  => substr((string) $request->userAgent(), 0, 120),
        ]);

        return $this->enquiryFeedback($request, 'success', 'Enquiry sent successfully');
    }

    $request->validate([
        // Was `string|max:255` / `string|max:20`, which let "asdfgh" and
        // "1234567890" through as leads an agent then tried to ring.
        'name'          => ['required', 'string', 'max:255', new RealName()],
        'phone'         => ['required', 'string', 'max:20', new IndianMobile()],
        'email'         => 'nullable|email|max:255',
        'listing_id'    => 'nullable|integer',
        'category_id'   => 'nullable|integer|exists:categories,id',
        'category_name' => 'nullable|string|max:255',
        'location_name' => 'nullable|string|max:255',
    ]);

    if ($response = $this->requireVerifiedPhone($request)) {
        return $response;
    }

    $listingId  = $request->listing_id ?: null;
    $categoryId = $request->category_id ?: null;
    $locationId = $request->location_id ?: null;

    $dedupeKey = Enquiry::dedupeKeyFor(
        $request->phone,
        $listingId,
        $categoryId,
        $locationId
    );

    /*
     * One enquiry per person per listing, and per person per category-in-a-
     * location — the same visitor was sending the same one repeatedly and each
     * copy arrived as a separate unread lead. See Enquiry::dedupeKeyFor().
     *
     * firstOrCreate covers the ordinary repeat; the catch covers two submits
     * close enough together that both found nothing, which the unique index on
     * dedupe_key turns into an error rather than a second row. Either way the
     * visitor is told their enquiry is already with us — from their side a
     * repeat submit and a first submit both worked.
     */
    if ($dedupeKey !== null) {
        try {
            $enquiry = Enquiry::firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                [
                    'listing_id'  => $listingId,
                    'category_id' => $categoryId,
                    'name'        => $request->name,
                    'phone'       => $request->phone,
                    'email'       => $request->email,
                    'location_id' => $locationId,
                    'is_read'     => 0,
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Only a duplicate on `dedupe_key` means "we already have this
            // one". Any other database error means the enquiry was NOT
            // stored, and answering that with a cheerful "already received"
            // is how a lead disappears while the visitor walks away happy.
            if (($e->errorInfo[1] ?? null) !== 1062) {
                Log::error('Enquiry could not be stored', [
                    'listing_id' => $listingId,
                    'sql_state'  => $e->errorInfo[0] ?? null,
                    'message'    => $e->getMessage(),
                ]);

                return $this->enquiryFeedback($request, 'danger', 'We could not save your enquiry just now. Please try again in a moment.');
            }

            OtpGate::consume($request->phone);

            return $this->enquiryFeedback($request, 'success', 'We have already received your enquiry. We will contact you soon.');
        }

        if (!$enquiry->wasRecentlyCreated) {
            OtpGate::consume($request->phone);

            return $this->enquiryFeedback($request, 'success', 'We have already received your enquiry. We will contact you soon.');
        }
    } else {
        // No usable phone number, so nothing to recognise a repeat by.
        $enquiry = Enquiry::create([
            'listing_id'  => $listingId,
            'category_id' => $categoryId,
            'name'        => $request->name,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'location_id' => $locationId,
            'is_read'     => 0,
        ]);
    }

    $count = Enquiry::where('is_read', 0)->count();

    event(new AdminNotification('enquiry', $count));

    \App\Support\AdminPush::send(
        'New Enquiry Received',
        $request->name . ' submitted an enquiry' . ($listingId ? ' on listing #' . $listingId : '') . '.',
        '/admin/enquiries'
    );

    \App\Support\Tracking\Tracker::conversion($request, 'enquiry', ['listing_id' => $listingId]);

    if ($listingId) {
        $this->notifyListingOwner($listingId, $request->name, [
            'phone'    => $request->phone,
            'email'    => $request->email,
            'location' => $locationId,
            'category' => $categoryId ? \App\Models\Category::whereKey($categoryId)->value('name') : null,
            'message'  => $request->input('message'),
            'enquiry_id' => $enquiry->id ?? null,
        ]);
    }

    // One code, one enquiry — the next one asks again. See OtpGate.
    OtpGate::consume($request->phone);

    return $this->enquiryFeedback($request, 'success', 'Enquiry sent successfully. The business will contact you shortly.');
}

/**
 * "Get Free Quotes" — one submission goes out to several matching
 * businesses at once (real fan-out, capped at 5 so it can't silently
 * spam an entire category), rather than one enquiry to one listing.
 * This is what powers the "Quotation Request" section on a business
 * owner's dashboard; before this there was nowhere any quote request
 * could come from.
 */
public function requestQuoteForm(Request $request)
{
    $category = $request->filled('category_id')
        ? Category::where('status', 1)->find($request->category_id)
        : null;

    $location = $request->filled('location_id')
        ? Location::where('status', 1)->find($request->location_id)
        : null;

    $categories = Category::where('status', 1)->orderBy('name')->get();
    $locations = activeLocations();

    return view($this->theme.'request-quote', compact('category', 'location', 'categories', 'locations'));
}

public function requestQuoteStore(Request $request)
{
    if (FormGuard::looksAutomated($request, $why)) {
        Log::info('Quote request rejected as automated', ['reason' => $why, 'ip' => $request->ip()]);

        return $this->enquiryFeedback($request, 'success', 'Your quote request has been sent.');
    }

    $request->validate([
        'name' => ['required', 'string', 'max:255', new RealName()],
        'phone' => ['required', 'string', 'max:20', new IndianMobile()],
        'email' => 'nullable|email|max:255',
        'category_id' => 'required|integer|exists:categories,id',
        'location_id' => 'nullable|integer|exists:locations,id',
        'message' => 'nullable|string|max:1000',
    ]);

    if ($response = $this->requireVerifiedPhone($request)) {
        return $response;
    }

    $baseQuery = fn () => Listing::notExpired()
        ->where('status', 1)
        ->where('category_id', $request->category_id)
        ->orderByDesc('is_featured')
        ->orderByDesc('trust')
        ->orderByDesc('verified');

    $matchingListings = $request->filled('location_id')
        ? $baseQuery()->where('location_id', $request->location_id)->take(5)->get()
        : $baseQuery()->take(5)->get();

    // Same-city businesses didn't fill the fan-out (or none were picked) —
    // top up from other cities, but only listings that opted in via
    // "Serviceable Area / Pincodes" (receive_leads_other_cities), and never
    // a listing already in the same-city batch above.
    if ($request->filled('location_id') && $matchingListings->count() < 5) {
        $remaining = 5 - $matchingListings->count();

        $fallback = $baseQuery()
            ->where('location_id', '!=', $request->location_id)
            ->where('receive_leads_other_cities', 1)
            ->whereNotIn('id', $matchingListings->pluck('id'))
            ->take($remaining)
            ->get();

        $matchingListings = $matchingListings->concat($fallback);
    }

    if ($matchingListings->isEmpty()) {
        return $this->enquiryFeedback($request, 'danger', 'No businesses currently match this category/location — please try a different one.');
    }

    foreach ($matchingListings as $listing) {
        Enquiry::create([
            'type' => 'quotation',
            'listing_id' => $listing->id,
            'category_id' => $request->category_id,
            'location_id' => $request->location_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
            'is_read' => 0,
        ]);
    }

    \App\Support\Tracking\Tracker::conversion($request, 'quote_request', ['category_id' => $request->category_id]);

    OtpGate::consume($request->phone);

    return $this->enquiryFeedback($request, 'success', 'Your quote request has been sent to '.$matchingListings->count().' matching business(es). They will contact you shortly.');
}

    public function pricing()
    {
        $packages = Package::where('status', 1)->get();
        $methods = MethodCurrency::where('enable_status', 1)->with('paymentMethod')->orderBy('currency_name')->get();

        return view($this->theme.'pricing', compact('packages', 'methods'));
    }

    public function listings(Request $request)
    {
        $data['categories'] = Category::where('status', 1)->get();
        $data['locations'] = activeLocations();

        /*
         * listings.all is shared with the category / subcategory / city routes,
         * which each scope the page to one of these. The unfiltered "all
         * listings" page has neither, but the view still reads them outside of
         * ?? / empty() guards (the heading, the filter base URL), so they have
         * to be defined — leaving them out is what made this route 500.
         */
        $data['selectedCategory'] = null;
        $data['selectedSubcategory'] = null;

        /*
         * This page used to `->get()` every approved listing (2,000+) with
         * only user+category eager loaded, so the card partial lazy-loaded a
         * location per listing and rendered 14MB of HTML. Now it loads the
         * same relations the category pages do, and paginates.
         */
        $listings = Listing::notExpired()
            ->where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })
            ->with(['user', 'category', 'location'])
            ->withCount('ratings')
            ->withAvg('ratings', 'ratings');

        /*
         * Random order is intentional (every business gets a turn at the top),
         * but an unseeded RAND() reshuffles on each page request, which makes
         * paginated results repeat and skip rows. Seeding it per visitor keeps
         * one visit's paging consistent while still varying between visits.
         */
        $seed = $request->session()->get('listing_shuffle_seed');

        if (!$seed) {
            $seed = random_int(1, 999999);
            $request->session()->put('listing_shuffle_seed', $seed);
        }

        $data['listings'] = $listings
            ->inRandomOrder($seed)
            ->paginate(12)
            ->appends($request->query());

        // The plain /listings page has no category in context, so the view
        // fell back to a bare "Listings | ServiceBazzar" (too short). Give it
        // a full, descriptive title + meta description.
        $siteName = $this->setting->site_name ?? config('app.name');
        $data['seoTitle'] = 'Local Business Listings in India | ' . $siteName;
        $data['seoDescription'] = 'Browse local businesses across India on ' . $siteName
            . ' — services, healthcare, repairs, restaurants and professionals. Compare ratings, prices and contact them directly, free.';

        return view($this->theme.'listings.all', $data);
    }

public function listingsCategoryLocation(Request $request, $location, $slug)
{
    $locationModel = Location::where('status', 1)
        ->where('slug', $location)
        ->firstOrFail();

    $request->merge([
        'location_id'   => $locationModel->id,
        'location_name' => $locationModel->name,
    ]);

    session([
        'detected_city' => $locationModel->name,
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 1: CATEGORY / SUBCATEGORY + AREA URL
    | Example: /mumbai/ac-repair-services-in-chembur
    |--------------------------------------------------------------------------
    */
    if (str_contains($slug, '-in-')) {

        [$serviceSlug, $areaSlug] = explode('-in-', $slug, 2);

        $category = Category::where('status', 1)
            ->where('slug', $serviceSlug)
            ->first();

        if ($category) {
            return app(\App\Http\Controllers\ListingController::class)
                ->cityCategoryArea($location, $slug);
        }

        $subcategory = Subcategory::where('status', 1)
            ->where('slug', $serviceSlug)
            ->first();

        if ($subcategory) {
            return app(\App\Http\Controllers\ListingController::class)
                ->cityCategoryArea($location, $slug);
        }

        abort(404);
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2: LISTING DETAIL CHECK
    |--------------------------------------------------------------------------
    */
    $listing = Listing::where('status', 1)
        ->where('slug', $slug)
        ->with('category')
        ->first();

    if (
        $listing &&
        !empty(optional($listing->category)->slug) &&
        !empty($listing->slug)
    ) {
        return redirect()->route('listings.details', [
            'category' => $listing->category->slug,
            'slug'     => $listing->slug,
        ], 301);
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 3: NORMAL CATEGORY URL
    | Example: /mumbai/ac-repair-services
    |--------------------------------------------------------------------------
    */
    $category = Category::where('status', 1)
        ->where('slug', $slug)
        ->first();

    if ($category) {
        return $this->listingsCategory($request, $slug);
    }

    abort(404);
}

public function saveUserLocation(Request $request)
{
    $data = $request->validate([
        'city' => 'nullable|string|max:255',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'pincode' => 'nullable|string|max:10',
    ]);

    session(['detected_lat' => $data['latitude']]);
    session(['detected_lng' => $data['longitude']]);

    if (!empty($data['city'])) {
        session(['detected_city' => english_location_name($data['city'])]);
    }

    return response()->json(['status' => true]);
}
public function listingsCategory(Request $request, $slug)
{
    $category = Category::where('status', 1)
        ->where('slug', $slug)
        ->firstOrFail();

    $locationName = trim($request->location_name ?? '');
    $locationId   = $request->location_id;

    $latitude = $request->filled('latitude')
        ? (float) $request->latitude
        : session('detected_lat');

    $longitude = $request->filled('longitude')
        ? (float) $request->longitude
        : session('detected_lng');

    $hasCoordinates = !empty($latitude) && !empty($longitude);
    $radius = 20;

    $category->increment('search_count');

    $data['categories'] = Category::where('status', 1)
        ->orderBy('search_count', 'desc')
        ->get();

    $data['selectedCategory'] = $category;
    $data['selectedSubcategory'] = null;

    $bannersQuery = Banner::where('status', 1)
        ->where(function ($q) use ($category) {
            $q->whereNull('category_id')
              ->orWhere('category_id', $category->id);
        });

    if ($locationName !== '') {
        $bannersQuery->where(function ($q) use ($locationName) {
            $q->whereNull('city')
              ->orWhere('city', 'LIKE', "%{$locationName}%");
        });
    }

    $data['banners'] = $bannersQuery
        ->orderBy('sort_order', 'asc')
        ->latest()
        ->get();

    $listings = Listing::notExpired()
        ->where('status', 1)
        ->where('category_id', $category->id)
        ->with(['user', 'category', 'location', 'subcategory'])
        ->withCount('ratings')
        ->withAvg('ratings', 'ratings');

    if ($hasCoordinates) {
        $listings->whereNotNull('listings.latitude')
            ->where('listings.latitude', '!=', '')
            ->whereNotNull('listings.longitude')
            ->where('listings.longitude', '!=', '')
            ->selectRaw("
                listings.*,
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(listings.latitude)) *
                        cos(radians(listings.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(listings.latitude))
                    )
                ) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius);

    } elseif (!empty($locationId)) {
        $listings->where('location_id', $locationId);

    } elseif ($locationName !== '') {
        preg_match('/\b\d{6}\b/', $locationName, $pinMatch);
        $pincode = $pinMatch[0] ?? null;

        $normalizedLocation = trim(
            preg_replace('/\s+/', ' ', str_replace([' - ', '-'], ' ', $locationName))
        );

        $locationParts = array_filter(array_map('trim', explode(',', $normalizedLocation)));

        $listings->where(function ($q) use ($normalizedLocation, $locationParts, $pincode) {
           $q->where('address', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('plot_no', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('building_name', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('street_name', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('landmark', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('area', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('city', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('state', 'LIKE', "%{$normalizedLocation}%");

            foreach ($locationParts as $part) {
                if ($part === '') continue;

               $q->orWhere('address', 'LIKE', "%{$part}%")
  ->orWhere('plot_no', 'LIKE', "%{$part}%")
  ->orWhere('building_name', 'LIKE', "%{$part}%")
  ->orWhere('street_name', 'LIKE', "%{$part}%")
  ->orWhere('landmark', 'LIKE', "%{$part}%")
  ->orWhere('area', 'LIKE', "%{$part}%")
  ->orWhere('city', 'LIKE', "%{$part}%")
  ->orWhere('state', 'LIKE', "%{$part}%");
            }

            if ($pincode) {
                $q->orWhere('pincode', 'LIKE', "%{$pincode}%")
  ->orWhere('address', 'LIKE', "%{$pincode}%")
  ->orWhere('plot_no', 'LIKE', "%{$pincode}%")
  ->orWhere('building_name', 'LIKE', "%{$pincode}%")
  ->orWhere('street_name', 'LIKE', "%{$pincode}%")
  ->orWhere('landmark', 'LIKE', "%{$pincode}%")
  ->orWhere('area', 'LIKE', "%{$pincode}%")
  ->orWhere('city', 'LIKE', "%{$pincode}%")
  ->orWhere('state', 'LIKE', "%{$pincode}%");
            }

            $q->orWhereHas('location', function ($subQ) use ($normalizedLocation, $locationParts) {
                $subQ->where('name', 'LIKE', "%{$normalizedLocation}%");

                foreach ($locationParts as $part) {
                    if ($part === '') continue;
                    $subQ->orWhere('name', 'LIKE', "%{$part}%");
                }
            });
        });
    }

    if ($request->filled('rating')) {
        $listings->where('review', '>=', $request->rating);
    }

    if ($request->filled('filter')) {
        switch ($request->filter) {
            case 'verified':
                $listings->where('verified', 1);
                break;

            case 'trust':
                $listings->where('trust', 1);
                break;

            case 'top_rated':
                $listings->where('review', '>=', 4);
                break;
        }
    }

    switch ($request->sort) {
        case 'rating':
        case 'top_rating':
            $listings->orderBy('review', 'desc');
            break;

        case 'low_rating':
            $listings->orderBy('review', 'asc');
            break;

        case 'popular':
            $listings->orderBy('view', 'desc');
            break;

        default:
            if ($hasCoordinates) {
                $listings->orderBy('distance', 'asc');
            }

            $listings->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
                ->orderBy('top_rank', 'asc')
                ->orderByRaw('CASE WHEN is_star = 1 THEN 0 ELSE 1 END')
                ->orderByRaw('CASE WHEN trust = 1 OR verified = 1 THEN 0 ELSE 1 END')
                ->orderBy('id', 'desc');
            break;
    }

    /*
     * Was ->get() — every listing in the category, unpaginated. A category
     * with a few hundred rows meant a few hundred haversine-distance
     * calculations and ratings aggregates per request, and the browser
     * rendering all of them at once — the exact "category page load leti
     * hai" slowness reported. listingsCategoryLocation() (the city×category
     * variant) already paginates for the same reason; this brings the plain
     * category page in line with it. The view already branches on
     * $listings being a paginator (see all.blade.php) to render page links.
     */
    $data['listings'] = $listings->paginate(12)->appends($request->query());
    $data['title'] = $category->name;

    $data['seoTitle'] = !empty($category->seo_title)
        ? $category->seo_title
        : $category->name . ' | ' . ($this->setting->site_name ?? config('app.name'));

    $data['seoDescription'] = !empty($category->seo_description)
        ? $category->seo_description
        : Str::limit(strip_tags($category->description ?? ''), 160, '');

    $data['seoKeywords'] = !empty($category->seo_keywords)
        ? $category->seo_keywords
        : $category->name;

    $data['seoImage'] = !empty($category->image)
        ? asset('assets/images/category/' . $category->image)
        : asset('assets/images/logo/favicon_new.png');

    $data['seoUrl'] = url()->current();
    $data['seoType'] = 'website';

    // Same reasoning as ListingController::cityCategoryArea() — a page without
    // enough content of its own should not be indexed. The threshold is shared
    // by every lander and lives in config/seo.php.
    $data['seoRobots'] = \App\Support\PageIndexability::robots($data['listings']->total());

    \App\Support\LanderSchema::attach(
        $data,
        $category,
        $locationName !== '' ? $locationName : null,
        $data['listings'],
        $data['listings']->total(),
        $this->setting->site_name ?? config('app.name')
    );

    return view($this->theme . 'listings.all', $data);
}

public function listingsSubcategory(Request $request, $slug)
{
    $subcategory = Subcategory::where('status', 1)
        ->where('slug', $slug)
        ->firstOrFail();

    $locationName = trim($request->location_name ?? '');

    $detectedLat = session('detected_lat');
    $detectedLng = session('detected_lng');
    $hasDetectedCoordinates = !is_null($detectedLat) && !is_null($detectedLng);

    $data['categories'] = Category::where('status', 1)->get();
    $data['selectedSubcategory'] = $subcategory;
    $data['selectedCategory'] = Category::where('id', $subcategory->category_id)->first();

    $data['banners'] = Banner::where('status', 1)
        ->where(function ($q) use ($subcategory) {
            $q->whereNull('category_id')
              ->orWhere('category_id', $subcategory->category_id);
        })
        ->orderBy('sort_order', 'asc')
        ->latest()
        ->get();

    $listings = Listing::notExpired()
        ->where('status', 1)
        ->whereJsonContains('subcategory_ids', (string) $subcategory->id)
        ->with(['user', 'category', 'location', 'subcategory'])
        ->withCount('ratings')
        ->withAvg('ratings', 'ratings');

    if ($locationName !== '') {
        preg_match('/\b\d{6}\b/', $locationName, $pinMatch);
        $pincode = $pinMatch[0] ?? null;

        $normalizedLocation = preg_replace('/\s+/', ' ', $locationName);
        $normalizedLocation = str_replace([' - ', '-'], ' ', $normalizedLocation);
        $locationParts = array_filter(array_map('trim', explode(',', $normalizedLocation)));

        $listings->where(function ($q) use ($normalizedLocation, $locationParts, $pincode) {
           $q->where('address', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('plot_no', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('building_name', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('street_name', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('landmark', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('area', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('city', 'LIKE', "%{$normalizedLocation}%")
  ->orWhere('state', 'LIKE', "%{$normalizedLocation}%");

            foreach ($locationParts as $part) {
                if ($part === '') continue;

              $q->orWhere('address', 'LIKE', "%{$part}%")
  ->orWhere('plot_no', 'LIKE', "%{$part}%")
  ->orWhere('building_name', 'LIKE', "%{$part}%")
  ->orWhere('street_name', 'LIKE', "%{$part}%")
  ->orWhere('landmark', 'LIKE', "%{$part}%")
  ->orWhere('area', 'LIKE', "%{$part}%")
  ->orWhere('city', 'LIKE', "%{$part}%")
  ->orWhere('state', 'LIKE', "%{$part}%");
            }

            if ($pincode) {
             $q->orWhere('pincode', 'LIKE', "%{$pincode}%")
  ->orWhere('address', 'LIKE', "%{$pincode}%")
  ->orWhere('plot_no', 'LIKE', "%{$pincode}%")
  ->orWhere('building_name', 'LIKE', "%{$pincode}%")
  ->orWhere('street_name', 'LIKE', "%{$pincode}%")
  ->orWhere('landmark', 'LIKE', "%{$pincode}%")
  ->orWhere('area', 'LIKE', "%{$pincode}%")
  ->orWhere('city', 'LIKE', "%{$pincode}%")
  ->orWhere('state', 'LIKE', "%{$pincode}%");
            }

            $q->orWhereHas('location', function ($subQ) use ($normalizedLocation, $locationParts) {
                $subQ->where('name', 'LIKE', "%{$normalizedLocation}%");

                foreach ($locationParts as $part) {
                    if ($part === '') continue;
                    $subQ->orWhere('name', 'LIKE', "%{$part}%");
                }
            });
        });
    }

    if ($hasDetectedCoordinates) {
        $listings = $listings->selectRaw("
            listings.*,
            IF(
                listings.latitude IS NOT NULL
                AND listings.longitude IS NOT NULL
                AND listings.latitude != ''
                AND listings.longitude != '',
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(listings.latitude)) *
                        cos(radians(listings.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(listings.latitude))
                    )
                ),
                NULL
            ) AS distance
        ", [$detectedLat, $detectedLng, $detectedLat]);

        $listings->orderByRaw("
            CASE
                WHEN distance IS NOT NULL AND distance <= 5 THEN 0
                ELSE 1
            END ASC
        ");

        $listings->orderByRaw("
            CASE
                WHEN top_rank IS NOT NULL AND top_rank IN (1,2,3,4) THEN 0
                ELSE 1
            END ASC
        ");

        $listings->orderByRaw("CASE WHEN top_rank IS NULL THEN 999 ELSE top_rank END ASC")
            ->orderByRaw("CASE WHEN is_star = 1 THEN 0 ELSE 1 END ASC")
            ->orderByRaw("CASE WHEN trust = 1 OR verified = 1 THEN 0 ELSE 1 END ASC")
            ->orderByRaw("COALESCE(distance, 999999) ASC")
            ->orderBy('id', 'desc');
    } else {
        $listings->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
            ->orderBy('top_rank', 'asc')
            ->orderByRaw('CASE WHEN is_star = 1 THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN trust = 1 OR verified = 1 THEN 0 ELSE 1 END')
            ->orderBy('id', 'desc');
    }

// Same fix as listingsCategory() just above — was ->get(), loading every
// listing under the subcategory unpaginated. ->first() below still works
// on a paginator (it forwards to the underlying page's collection).
$data['listings'] = $listings->paginate(12)->appends($request->query());
$data['listing'] = $data['listings']->first();

    $data['seoTitle'] = !empty($subcategory->seo_title)
        ? $subcategory->seo_title
        : $subcategory->name . ' | ' . ($this->setting->site_name ?? config('app.name'));
    // Same lead word / 50-60 character pattern as the city pages, no brand.
    $data['seoTitle'] = \App\Support\LanderSchema::hubTitle($data['seoTitle'], $subcategory);

    $data['seoDescription'] = !empty($subcategory->seo_description)
        ? $subcategory->seo_description
        : Str::limit(strip_tags($subcategory->description ?? ''), 160, '');

    $data['seoKeywords'] = !empty($subcategory->seo_keywords)
        ? $subcategory->seo_keywords
        : $subcategory->name;

    $data['seoImage'] = !empty($subcategory->image)
        ? asset('assets/images/subcategory/' . $subcategory->image)
        : asset('assets/images/logo/favicon_new.png');

    $data['seoUrl'] = url()->current();
    $data['seoType'] = 'website';
    $data['title'] = $subcategory->name;

    // Shared thin-page rule — see config/seo.php.
    $data['seoRobots'] = \App\Support\PageIndexability::robots($data['listings']->total());

    return view($this->theme . 'listings.all', $data);
}
        public function listingsLocation(Request $request, $slug)
    {
        $location = Location::where('status', 1)
            ->where('slug', $slug)
            ->firstOrFail();

        $data['location'] = $location;

        /*
         * This used to ->get() every active listing of the city at once (plus
         * an ORDER BY id that RAND() immediately threw away), so a big city
         * hydrated thousands of models per request — the 512M/timeout case.
         * Same seeded-shuffle + paginate pattern the other listing pages use.
         */
        $seed = $request->session()->get('listing_shuffle_seed');

        if (!$seed) {
            $seed = random_int(1, 999999);
            $request->session()->put('listing_shuffle_seed', $seed);
        }

        $data['listings'] = Listing::notExpired()
            ->where('status', 1)
            ->where('location_id', $location->id)
            ->with(['user', 'category', 'location'])
            ->withCount('ratings')
            ->withAvg('ratings', 'ratings')
            ->inRandomOrder($seed)
            ->paginate(24)
            ->appends($request->query());

        $data['title'] = $location->name;

        // Previously unset — every one of these location-only pages fell
        // back to the sitewide default title/description, making all 40+
        // city pages identical/duplicate for SEO purposes.
        $siteName = $this->setting->site_name ?? config('app.name', 'Website');
        $data['seoTitle'] = "Best Local Businesses in {$location->name} | {$siteName}";
        $data['seoDescription'] = seoTextVariant((string) $location->id, [
            "Find top-rated businesses, services and shops in {$location->name}. Browse business listings, reviews, contact details and more on {$siteName}.",
            "Looking for reliable businesses in {$location->name}? Explore business listings with reviews and contact details on {$siteName}.",
            "Discover trusted local businesses and services in {$location->name}. Compare listings and check reviews on {$siteName}.",
            "{$location->name}'s local business directory — business listings, genuine reviews and contact details, all on {$siteName}.",
        ]);
        $data['seoUrl'] = route('listings.location', ['slug' => $location->slug]);

        /*
         * City pages were the one lander type missing this check, so a city
         * with no live listings was served as an indexable page with nothing
         * on it — the same Soft 404 signal the category and subcategory pages
         * were already fixed for. Threshold shared via config/seo.php.
         */
        $data['seoRobots'] = \App\Support\PageIndexability::robots($data['listings']->total());

        return view($this->theme . 'listings.cat_loc', $data);
    }

    public function listingsAuthor($id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $data['listings'] = Listing::notExpired()->where('status', 1)->where('user_id', $user->id)->orderBy('id', 'desc')->with(['user'])->paginate(8);
        $data['title'] = 'User Listings';
        $data['user'] = $user;

        // An author with no live listings is an empty page — same rule as the
        // other landers (config/seo.php).
        $data['seoRobots'] = \App\Support\PageIndexability::robots($data['listings']->total());

        return view($this->theme.'listings.author', $data);
    }





public function listingsDetails(Request $request, $category, $slug)
{
    // A slug that lost its "-<n>" when the listing was renamed: no row carries
    // it any more, but the plain one is live. Point the old URL there.
    if (preg_match('/^(.+)-\d+$/', $slug, $m) && !Listing::where('slug', $slug)->exists()) {
        $renamed = Listing::where('status', 1)->where('slug', $m[1])->with('category:id,slug')->first();
        if ($renamed && !empty($renamed->category->slug)) {
            return redirect()->route('listings.details', ['category' => $renamed->category->slug, 'slug' => $renamed->slug], 301);
        }
    }

    $data['listing'] = Listing::notExpired()
        ->where('status', 1)
        ->where('slug', $slug)
        // ratings.comments is eager loaded because the review block used to run
        // one comments query per review on top of ~6 repeated ratings queries.
        ->with(['user', 'category', 'location', 'ratings.user', 'ratings.comments.user'])
        ->withCount('ratings')
        ->withAvg('ratings', 'ratings')
        ->firstOrFail();

    // category slug mismatch ho to clean URL par redirect
    if ($data['listing']->category->slug != $category) {
        return redirect()->route('listings.details', [
            'category' => $data['listing']->category->slug,
            'slug'     => $data['listing']->slug,
        ], 301);
    }

    // ✅ ========== SESSION SET KARO (LISTING DETAILS KE LIYE) ==========
    $listing = $data['listing'];
    
    // Location text: area + city
    $listingArea = trim($listing->area ?? '');
    $listingCity = trim($listing->city ?? ($listing->location->name ?? ''));
    
    if($listingArea && $listingCity){
        $locationText = $listingArea . ', ' . $listingCity;
    }elseif($listingCity){
        $locationText = $listingCity;
    }else{
        $locationText = '';
    }
    
    // Category text: Listing ka name
    $categoryText = $listing->name ?? '';
    
    // Session set karo
    session([
        'selected_location' => $locationText,
        'selected_category' => $categoryText
    ]);
    // ✅ ========== SESSION SET END ==========

    // location session me save / preserve
    if ($request->filled('location_name')) {
        session(['user_location' => trim($request->location_name)]);
    } elseif (!session()->has('user_location') && !empty(optional($data['listing']->location)->name)) {
        session(['user_location' => $data['listing']->location->name]);
    }

    $data['listing']->increment('view');

    $listing = $data['listing'];

    /*
    |--------------------------------------------------------------------------
    | Also Listed In
    |--------------------------------------------------------------------------
    */
    $subcategoryIds = [];

    if (!empty($listing->subcategory_ids)) {
        if (is_array($listing->subcategory_ids)) {
            $subcategoryIds = $listing->subcategory_ids;
        } else {
            $subcategoryIds = json_decode($listing->subcategory_ids, true) ?? [];
        }
    }

    $subcategoryIds = array_filter(array_map('intval', $subcategoryIds));

    $alsoListedIn = collect();

    if ($listing->category && !empty($listing->category->slug)) {
        $alsoListedIn->push([
            'name' => $listing->category->name,
            'slug' => $listing->category->slug,
            'type' => 'category',
        ]);
    }

    $selectedSubcategories = Subcategory::where('status', 1)
        ->whereIn('id', $subcategoryIds)
        ->whereNotNull('slug')
        ->where('slug', '!=', '')
        ->get();

    foreach ($selectedSubcategories as $sub) {
        $alsoListedIn->push([
            'name' => $sub->name,
            'slug' => $sub->slug,
            'type' => 'subcategory',
        ]);
    }

    $relatedSubcategories = Subcategory::where('status', 1)
        ->where('category_id', $listing->category_id)
        ->when(!empty($subcategoryIds), function ($query) use ($subcategoryIds) {
            $query->whereNotIn('id', $subcategoryIds);
        })
        ->whereNotNull('slug')
        ->where('slug', '!=', '')
        ->get();

    foreach ($relatedSubcategories as $sub) {
        $alsoListedIn->push([
            'name' => $sub->name,
            'slug' => $sub->slug,
            'type' => 'subcategory',
        ]);
    }

    $data['alsoListedIn'] = $alsoListedIn
        ->filter(function ($item) {
            return !empty($item['name']) && !empty($item['slug']) && !empty($item['type']);
        })
        ->unique(function ($item) {
            return strtolower(trim($item['type'] . '-' . $item['slug']));
        })
        ->take(12)
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Banner Data
    |--------------------------------------------------------------------------
    */
    $categoryBanners = Banner::where('status', 1)
        ->where('category_id', $listing->category_id)
        ->orderBy('sort_order', 'asc')
        ->latest()
        ->get();

    if ($categoryBanners->count() > 0) {
        $data['banners'] = $categoryBanners;
    } else {
        $data['banners'] = Banner::where('status', 1)
            ->whereNull('category_id')
            ->orderBy('sort_order', 'asc')
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | SEO Data
    |--------------------------------------------------------------------------
    */
    $siteName     = $this->setting->site_name ?? config('app.name', 'Website');
    $categoryName = $listing->category->name ?? '';
    $locationName = $listing->location->name ?? '';
    $cityName     = $listing->city ?? '';
    $stateName    = $listing->state ?? '';

    // Format: {Business Name} | {Service Name} in {City}
    // This ensures each listing has a unique title even in the same city/category.
    // Fallback: if no user/business name, use just {Service Name} in {City}.
    // normalizeSeoTitle() standardises the brand tail and enforces ~60 char limit.
    $businessName = optional($listing->user)->name ?: optional($listing->user)->fullname();

    $rawTitle = !empty($listing->meta_title)
        ? $listing->meta_title
        : trim($listing->name . ($locationName ? ' in ' . $locationName : ''));

    $data['seoTitle'] = normalizeSeoTitle($rawTitle, $siteName);

    $data['seoDescription'] = !empty($listing->meta_description)
        ? $listing->meta_description
        : Str::limit(
            'Find ' . $listing->name .
            ($locationName ? ' in ' . $locationName : '') .
            ($categoryName ? '. ' . $categoryName : '') .
            '. Get phone number, address, reviews, services, photos and more on ' . $siteName . '.',
            160,
            ''
        );

    if (!empty($listing->meta_keywords)) {
        $data['seoKeywords'] = is_array($listing->meta_keywords)
            ? implode(', ', $listing->meta_keywords)
            : $listing->meta_keywords;
    } else {
        $keywords = array_filter([
            $listing->name,
            $listing->name . ' ' . $locationName,
            $listing->name . ' phone number',
            $listing->name . ' address',
            $listing->name . ' reviews',
            $categoryName,
            $categoryName ? $categoryName . ' in ' . $locationName : '',
            $cityName,
            $stateName,
        ]);

        $data['seoKeywords'] = implode(', ', array_unique($keywords));
    }

    /*
     * The picture on the share card.
     *
     * WhatsApp and Facebook cannot decode AVIF (nor SVG), and a good number of
     * listing thumbnails are stored that way — which is why a shared listing
     * arrived in a chat as a bare "www.servicebazzar.com" card with no image
     * at all. The gallery photos next to those thumbnails are ordinary JPEGs,
     * so the first image a crawler can actually render is what gets offered,
     * whichever field it happens to live in.
     *
     * Nothing found falls back to the site's own SEO image (or the logo) — the
     * favicon that used to stand in here is far too small for a card and gets
     * ignored by both.
     */
    $rendersInAShareCard = fn ($file) => in_array(
        strtolower(pathinfo((string) $file, PATHINFO_EXTENSION)),
        ['jpg', 'jpeg', 'png', 'webp'],
        true
    );

    $shareImage = null;

    if (!empty($listing->thumb) && $rendersInAShareCard($listing->thumb)) {
        $shareImage = asset('assets/images/listing/thumb/' . $listing->thumb);
    }

    if (!$shareImage) {
        foreach ((array) $listing->photos as $photo) {
            if ($rendersInAShareCard($photo)) {
                $shareImage = asset('assets/images/listing/photos/' . $photo);
                break;
            }
        }
    }

    // Always set: the page's schema block reads $seoImage directly, so leaving
    // it unset breaks the view rather than falling through to a default.
    $data['seoImage'] = $shareImage
        ?: (!empty($this->setting->seo->image)
            ? asset('assets/images/seo/' . $this->setting->seo->image)
            : asset('assets/images/logo/logo.png'));

    $data['seoUrl'] = route('listings.details', [
        'category' => $listing->category->slug,
        'slug'     => $listing->slug,
    ]);

    $data['seoType'] = 'article';

    /*
    |--------------------------------------------------------------------------
    | Share card (WhatsApp, Facebook, X)
    |--------------------------------------------------------------------------
    |
    | Separate from the meta above on purpose. `meta_title`/`meta_description`
    | are written for Google and read as prose — pasted into a chat they arrive
    | cut off mid-sentence ("...specializing in Kundli reading, love problem")
    | and give the reader nothing to act on.
    |
    | A share card is competing with JustDial's in the same conversation, so it
    | carries what theirs does: which locality the business is in, what people
    | rated it, how many photos there are, and that the address and number are
    | one tap away. All of it from data already loaded with the listing.
    */
    $areaName = trim((string) $listing->area);
    $placeName = $cityName ?: $locationName;

    $place = collect([$areaName, $placeName])
        ->filter()
        ->unique(fn ($part) => Str::lower($part))
        ->implode(', ');

    $data['ogTitle'] = trim(
        $listing->name
        . ($place ? ', ' . $place : '')
        . ' - ' . $siteName
    );

    $facts = [];

    // withCount/withAvg on the query that loaded this listing, so these cost
    // nothing extra here.
    $ratingCount = (int) ($listing->ratings_count ?? 0);
    $ratingAvg   = round((float) ($listing->ratings_avg_ratings ?: $listing->review), 1);

    if ($ratingCount > 0 && $ratingAvg > 0) {
        $facts[] = 'Rated ' . $ratingAvg . '/5';
        $facts[] = $ratingCount . ' ' . Str::plural('Rating', $ratingCount)
            . ' & ' . Str::plural('Review', $ratingCount);
    }

    $photoCount = count($listing->photos ?? []) + (!empty($listing->thumb) ? 1 : 0);

    if ($photoCount > 0) {
        $facts[] = $photoCount . ' ' . Str::plural('Photo', $photoCount);
    }

    if (!empty($listing->established_year) && $listing->established_year <= now()->year) {
        $years = now()->year - (int) $listing->established_year;

        if ($years > 0) {
            $facts[] = $years . ' ' . Str::plural('Year', $years) . ' in Business';
        }
    }

    // Nothing to say about the business itself — a card reading only "Get
    // Address, Contact Number" is not worth sending, so name the trade and the
    // locality instead. Never alongside the facts above: the title already
    // carries the locality, and WhatsApp truncates a long description.
    if (empty($facts) && $categoryName) {
        $facts[] = $categoryName . ($place ? ' in ' . $place : '');
    }

    // No site name on the end: WhatsApp appends the domain itself, and every
    // character spent here is one it cuts off the front.
    $facts[] = 'Get Address, Contact Number and Timings';

    $data['ogDescription'] = Str::limit(implode('. ', $facts) . '.', 200, '');

    // Analytics: read by the tracking script (via data-* attributes on
    // <body>) to auto-fire a listing_view event and let per-listing
    // rollups resolve listing_id without parsing the URL.
    $data['pageName'] = 'listing_detail';
    $data['trackListingId'] = $listing->id;
    $data['trackCategoryId'] = $listing->category_id;
    $data['trackLocationId'] = $listing->location_id;

    // On a listing detail page the "current city" is the listing's OWN city —
    // not whatever is (or isn't) sitting in the visitor's server session. A
    // session-less direct visit resolved to null and fell back to the sitewide
    // branch, which lists every subcategory regardless of whether a listing
    // uses it (that is why "24 Hours Packers And Movers", "Corporate
    // Relocation" etc. showed here with nothing behind them). Scoping to the
    // listing's location makes Popular Categories list only the subcategories
    // this city's listings actually reference.
    $data['popularCategoriesLocation'] = $listing->location;
    $data['popularCategories'] = $listing->location
        ? $this->buildPopularCategoriesQuery($listing->location)
        : $this->getPopularCategories();

    $data['locations'] = activeLocations()->sortBy('name')->values();


        $data['popularCities'] = activeLocations()
    ->where('is_popular', 1)
    ->sortBy('name')
    ->values();

$data['nearbyAreas'] = Listing::where('status', 1)
    ->where('category_id', $listing->category_id)
    ->where('location_id', $listing->location_id)
    ->whereNotNull('area')
    ->where('area', '!=', '')
    ->select('area')
    ->distinct()
    ->orderBy('area')
    ->pluck('area');

    // The profile page's "Recent Search" rail is built from this.
    \App\Models\UserRecentActivity::remember(
        \App\Models\UserRecentActivity::TYPE_LISTING,
        $listing->id ?? null
    );

    return view($this->theme . 'listings.details', $data);
}
public function search(Request $request)
{
    // Same rail, the other half: what was typed into the search box.
    \App\Models\UserRecentActivity::remember(
        \App\Models\UserRecentActivity::TYPE_SEARCH,
        null,
        $request->input('categorysearch')
    );

if ($request->filled('location_id') && $request->filled('categorysearch')) {

    $location = Location::where('status', 1)
        ->where('id', $request->location_id)
        ->first();

    $category = Category::where('status', 1)
        ->where('name', $request->categorysearch)
        ->first();

    if ($location && $category) {

        $mainLocation = !empty($location->parent_id)
            ? Location::where('status', 1)->where('id', $location->parent_id)->first()
            : $location;

        $citySlug = $mainLocation ? $mainLocation->slug : $location->slug;

        return redirect('/' . $citySlug . '/' . $category->slug . '-in-' . $location->slug);
    }
}
    $categorySearch = trim($request->categorysearch ?? '');
    $locationName   = trim($request->location_name ?? '');

    $latitude = $request->filled('latitude') ? (float) $request->latitude : null;
    $longitude = $request->filled('longitude') ? (float) $request->longitude : null;
    $radius = 20;

    if ($locationName !== '' && $categorySearch === '' && !$request->filled('category')) {
        return back()->with('error', 'Location search ke liye category, subcategory ya listing name search karna zaroori hai.');
    }

    $listings = Listing::notExpired()
        ->where('status', 1)
        ->with(['user', 'category', 'subcategory', 'location'])
        ->withCount('ratings')
        ->withAvg('ratings', 'ratings');

    $selectedCategory = null;
    $selectedSubcategory = null;

    if ($categorySearch !== '') {
        $matchedCategoryIds = Category::where('status', 1)
            ->where('name', 'LIKE', "%{$categorySearch}%")
            ->pluck('id')
            ->toArray();

        $matchedSubcategoryIds = Subcategory::where('status', 1)
            ->where('name', 'LIKE', "%{$categorySearch}%")
            ->pluck('id')
            ->toArray();

        $matchedSubcategoryCategoryIds = Subcategory::where('status', 1)
            ->where('name', 'LIKE', "%{$categorySearch}%")
            ->pluck('category_id')
            ->toArray();

        $matchedListingIds = Listing::where('status', 1)
            ->where('name', 'LIKE', "%{$categorySearch}%")
            ->pluck('id')
            ->toArray();

        $selectedCategory = Category::where('status', 1)
            ->where('name', 'LIKE', "%{$categorySearch}%")
            ->first();

        $selectedSubcategory = Subcategory::where('status', 1)
            ->where('name', 'LIKE', "%{$categorySearch}%")
            ->first();

        $listings->where(function ($q) use (
            $categorySearch,
            $matchedCategoryIds,
            $matchedSubcategoryIds,
            $matchedSubcategoryCategoryIds,
            $matchedListingIds
        ) {
            if (!empty($matchedCategoryIds)) {
                $q->orWhereIn('category_id', $matchedCategoryIds);
            }

            if (!empty($matchedSubcategoryIds)) {
                $q->orWhere(function ($subQ) use ($matchedSubcategoryIds) {
                    foreach ($matchedSubcategoryIds as $subId) {
                        $subQ->orWhereJsonContains('subcategory_ids', (int) $subId);
                    }
                });
            }

            if (!empty($matchedSubcategoryCategoryIds)) {
                $q->orWhereIn('category_id', $matchedSubcategoryCategoryIds);
            }

            if (!empty($matchedListingIds)) {
                $q->orWhereIn('id', $matchedListingIds);
            }

            $q->orWhere('name', 'LIKE', "%{$categorySearch}%");
        });
    }

    if (!empty($latitude) && !empty($longitude)) {
        $listings->whereNotNull('listings.latitude')
            ->where('listings.latitude', '!=', '')
            ->whereNotNull('listings.longitude')
            ->where('listings.longitude', '!=', '')
            ->selectRaw("
                listings.*,
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(listings.latitude)) *
                        cos(radians(listings.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(listings.latitude))
                    )
                ) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius);
    } elseif ($locationName !== '') {
        preg_match('/\b\d{6}\b/', $locationName, $pinMatch);
        $pincode = $pinMatch[0] ?? null;

        $normalizedLocation = preg_replace('/\s+/', ' ', $locationName);
        $normalizedLocation = str_replace([' - ', '-'], ' ', $normalizedLocation);
        $locationParts = array_filter(array_map('trim', explode(',', $normalizedLocation)));

        $listings->where(function ($q) use ($normalizedLocation, $locationParts, $pincode) {
            $exactCityMatch = false;
            $cityNames = ['ajmer', 'jaipur', 'delhi', 'mumbai', 'noida', 'gurgaon', 'bangalore', 'chennai', 'kolkata', 'hyderabad', 'pune', 'lucknow', 'kanpur', 'nagpur', 'indore', 'bhopal', 'patna', 'vadodara', 'surat', 'ahmedabad', 'chandigarh', 'mysore', 'coimbatore', 'visakhapatnam', 'thiruvananthapuram', 'ranchi', 'bhubaneswar', 'guwahati', 'shimla', 'dehradun', 'jodhpur', 'udaipur'];
            
            $searchCity = strtolower($normalizedLocation);
            
            if (in_array($searchCity, $cityNames)) {
                $exactCityMatch = true;
                $q->where('city', 'LIKE', "{$normalizedLocation}");
            }
            
            if (!$exactCityMatch) {
                $q->where('city', 'LIKE', "{$normalizedLocation}")
                  ->orWhere('city', 'LIKE', "{$normalizedLocation}%")
                  ->orWhere('city', 'LIKE', "% {$normalizedLocation} %")
                  ->orWhere('city', 'LIKE', "{$normalizedLocation} %")
                  ->orWhere('city', 'LIKE', "% {$normalizedLocation}");
            }
            
            if (!$exactCityMatch) {
                foreach ($locationParts as $part) {
                    if ($part === '' || strlen($part) < 3) continue;
                    
                    $q->orWhere('area', 'LIKE', "%{$part}%")
                      ->orWhere('address', 'LIKE', "%{$part}%")
                      ->orWhere('plot_no', 'LIKE', "%{$part}%")
                      ->orWhere('building_name', 'LIKE', "%{$part}%")
                      ->orWhere('street_name', 'LIKE', "%{$part}%")
                      ->orWhere('landmark', 'LIKE', "%{$part}%");
                }
            }
            
            if ($pincode) {
                $q->orWhere('pincode', 'LIKE', "%{$pincode}%")
                  ->orWhere('address', 'LIKE', "%{$pincode}%")
                  ->orWhere('plot_no', 'LIKE', "%{$pincode}%")
                  ->orWhere('building_name', 'LIKE', "%{$pincode}%")
                  ->orWhere('street_name', 'LIKE', "%{$pincode}%")
                  ->orWhere('landmark', 'LIKE', "%{$pincode}%")
                  ->orWhere('area', 'LIKE', "%{$pincode}%")
                  ->orWhere('city', 'LIKE', "%{$pincode}%")
                  ->orWhere('state', 'LIKE', "%{$pincode}%");
            }

            $q->orWhereHas('location', function ($subQ) use ($normalizedLocation, $locationParts, $exactCityMatch) {
                if ($exactCityMatch) {
                    $subQ->where('name', 'LIKE', "{$normalizedLocation}");
                } else {
                    $subQ->where('name', 'LIKE', "%{$normalizedLocation}%");
                    
                    foreach ($locationParts as $part) {
                        if ($part === '' || strlen($part) < 3) continue;
                        $subQ->orWhere('name', 'LIKE', "%{$part}%");
                    }
                }
            });
        });
    }

    if ($request->filled('rating')) {
        $listings->where('review', '>=', $request->rating);
    }

    if ($request->filled('filter')) {
        switch ($request->filter) {
            case 'verified':
                $listings->where('verified', 1);
                break;

            case 'trust':
                $listings->where('trust', 1);
                break;

            case 'top_rated':
                $listings->where('review', '>=', 4);
                break;
        }
    }

    switch ($request->sort) {
        case 'rating':
        case 'top_rating':
            $listings->orderBy('review', 'desc');
            break;

        case 'low_rating':
            $listings->orderBy('review', 'asc');
            break;

        case 'popular':
            $listings->orderBy('view', 'desc');
            break;

        default:
            if (!empty($latitude) && !empty($longitude)) {
                $listings->orderBy('distance', 'asc');
            } else {
                $listings->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('top_rank', 'asc')
                    ->orderBy('id', 'desc');
            }
            break;
    }

    $bannersQuery = Banner::where('status', 1);

    if ($selectedCategory) {
        $bannersQuery->where(function ($q) use ($selectedCategory) {
            $q->whereNull('category_id')
                ->orWhere('category_id', $selectedCategory->id);
        });
    }

    if ($locationName !== '') {
        $bannersQuery->where(function ($q) use ($locationName) {
            $q->whereNull('city')
                ->orWhere('city', 'LIKE', "%{$locationName}%");
        });
    }

    $data['categories'] = Category::where('status', 1)->orderBy('name')->get();
    $data['selectedCategory'] = $selectedCategory;
    $data['selectedSubcategory'] = $selectedSubcategory;
    $data['banners'] = $bannersQuery->orderBy('sort_order', 'asc')->latest()->get();
    $data['listings'] = $listings->paginate(8)->appends($request->query());

    // Self-referencing canonical so page 2+ isn't wrongly canonicalized back
    // to page 1 (includes/seo.blade.php's url()->current() default drops
    // the query string). Search/filter results are also excluded from the
    // index (crawlable, not indexable) since they're free-text/filter
    // permutations rather than unique landing content.
    $data['seoUrl'] = $request->fullUrlWithQuery(['page' => $data['listings']->currentPage()]);
    $data['seoRobots'] = 'noindex, follow';

    // Analytics: read by the tracking script to auto-fire a
    // search_performed event with the real results count — only this
    // controller knows the outcome of the search, so tracking happens
    // here rather than on the search form's submit.
    $data['pageName'] = 'search_results';
    $data['searchResultsCount'] = $data['listings']->total();
    $data['searchKeyword'] = $categorySearch ?: null;
    $data['searchCategoryId'] = $selectedCategory->id ?? null;
    $data['searchLocationId'] = $request->location_id ?? null;

    if ($request->ajax()) {
        return view($this->theme . 'partials.ajax-listings', $data)->render();
    }

    return view($this->theme . 'listings.all', $data);
}
    public function changeLang($langCode)
    {
        $lang = Language::where('lang_code', $langCode)->first();
        $lang ? session()->put('lang', $lang->lang_code) : session()->put('lang', 'en');

        return back();
    }

public function linkDetails($slug)
{
    // ✅ Static pages ki list (जहाँ inputs खाली होने चाहिए)
    $staticPages = [
        'about-us', 
        'privacy-policy', 
        'payment-policy', 
        'terms-conditions', 
        'refund-policy', 
        'faq', 
        'contact-us',
        'disclaimer',
        'cancellation-policy'
    ];
    
    // ✅ Agar current page static page hai to session clear karo
    if(in_array($slug, $staticPages)) {
        session([
            'selected_location' => '',
            'selected_category' => ''
        ]);
    }
    
    $link = $this->themeCollection()
        ->where('name', 'usefull_links')
        ->where('is_multiple', 1)
        ->first(function ($item) use ($slug) {
            return isset($item->content->slug) && $item->content->slug == $slug;
        });

    if (!$link) {
        abort(404);
    }

    $title = $link->content->title;

    // These pages set no SEO meta of their own, so every one of them inherited
    // the site's default description — Semrush flagged home / payment-policy /
    // privacy-policy as duplicates. Derive a unique title + description from
    // each page's own content instead.
    $siteName = $this->setting->site_name ?? config('app.name');
    $seoTitle = $title . ' | ' . $siteName;
    $bodyText = trim(preg_replace('/\s+/', ' ', strip_tags($link->content->content ?? '')));
    $seoDescription = $bodyText !== ''
        ? \Illuminate\Support\Str::limit($bodyText, 155)
        : $title . ' — ' . $siteName . '.';

    return view($this->theme . 'linkDetails', compact('link', 'title', 'seoTitle', 'seoDescription'));
}
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email',
        ]);
        $subscriber = new Subscriber;
        $subscriber->email = $request->email;
        $subscriber->save();
        $alert = ['success', 'Subscribe successful'];

        return back()->withAlert($alert);
    }

    protected function themeCollection()
    {
        if (! $this->setting) {
            return collect();
        }
        $themeName = $this->setting->theme;

        return Cache::remember("theme_data:{$themeName}", 300, function () use ($themeName) {
            return Theme::where('theme_name', $themeName)->get();
        });
    }

    public function searchLocations(Request $request)
    {
        $search = $request->q;

        $locations = Location::where('name', 'LIKE', "%{$search}%")
            ->limit(5)
            ->get();

        return response()->json($locations);
    }

    public function categorySearch(Request $request)
    {
        $search = $request->q;

        $Category = Category::where('name', 'LIKE', "%{$search}%")
            ->limit(5)
            ->get();

        return response()->json($Category);
    }

   public function faq()
{
    // ✅ FAQ page par session clear karo
    session([
        'selected_location' => '',
        'selected_category' => ''
    ]);
    
    $themeData = $this->themeCollection();

    $data['title'] = 'FAQ';

    $data['faq_section'] = $themeData->where('name', 'faq')->where('is_multiple', 0)->first();

    $data['faqs'] = $themeData->where('name', 'faq')
        ->where('is_multiple', 1)
        ->sortByDesc('id');

    return view($this->theme.'faq', $data);
}

     public function freelisting(Request $request)
{
    // Check authentication - return JSON if not logged in
    if (!auth()->check()) {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login to create a listing',
                'requiresAuth' => true
            ], 401);
        }

        // For non-AJAX requests, show popup via redirect
        return redirect('/')->with('showAuthPopup', true);
    }

    $categories = Category::where('status', 1)->get();
    // The shared helper orders newest-first, which reads as random in a
    // 100-entry picker; this one is searched by name, so sort by name.
    $locations  = activeLocations()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

    // Don't load subcategories here — they're 700+ rows now (after bulk-adding
    // 279 jewellery + 162 travel + 176 CA + 115 mehndi). Loading them inline
    // as @json($subcategories) balloons the page to 50KB+ of JSON, slowing
    // initial page load and JS parsing. Instead, they'll be fetched via AJAX
    // when the user selects a category (see listing_form.blade.php line ~2350).

    // Logged-in with at least one business already: land on "Select Your
    // Business" instead of straight into the form, so re-visiting this page
    // to add a second listing doesn't look like it forgot the first one.
    $existingListings = auth()->check()
        ? auth()->user()->listings()->with('location')->latest()->get()
        : collect();

    return view($this->theme.'listing_form',
        compact('categories','locations','existingListings')
    );
}



   
    public function freeListingStore(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
        ]);

        Freelistings::create([
            'user_id' => auth()->id(),
            'business_name' => $request->business_name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'pincode' => $request->pincode,
            'plot_no' => $request->plot_no,
            'building_name' => $request->building_name,
            'street_name' => $request->street_name,
            'landmark' => $request->landmark,
            'area' => $request->area,
            'city' => $request->city,
            'state' => $request->state,
            'status' => 0,
        ]);

        return redirect()->route('free.listing')
            ->with('success', 'Free listing submitted. Waiting for admin approval.');
    }
    
public function listingClickTrack(Request $request)
{
    $listing = Listing::with(['category', 'location'])->find($request->listing_id);

    $ownerNumber = null;

    if ($listing) {
        $numbers = is_array($listing->mobile_numbers)
            ? $listing->mobile_numbers
            : json_decode($listing->mobile_numbers, true);

        $ownerNumber = $numbers[0] ?? $listing->mobile ?? null;
    }

    $listingId  = $listing->id ?? $request->listing_id;
    $clickType  = [
        'call'     => 'call',
        'landline' => 'call',
        'whatsapp' => 'whatsapp',
        'enquiry'  => 'enquiry',
    ][$request->type] ?? 'call';

    // Deduplicate: same IP + same listing + same type within 30 minutes = one click
    $alreadyRecorded = ListingClick::where('listing_id', $listingId)
        ->where('type', $clickType)
        ->where('ip_address', $request->ip())
        ->where('created_at', '>=', now()->subMinutes(30))
        ->exists();

    if (!$alreadyRecorded) {
        $click = new ListingClick();
        $click->listing_id    = $listingId;
        $click->listing_name  = $listing->name ?? null;
        $click->category_id   = $request->category_id ?? $listing->category_id ?? null;
        $click->category_name = optional($listing->category)->name;
        $click->location_id   = $request->location_id ?? $listing->location_id ?? null;
        $click->location_name = optional($listing->location)->name;
        $click->owner_name    = $listing->contact_person ?? null;
        $click->owner_number  = $ownerNumber;
        $click->clicked_number = $request->clicked_number;
        $click->type          = $clickType;
        $click->ip_address    = $request->ip();
        $click->user_agent    = $request->userAgent();
        $click->save();
    }

    return response()->json(['success' => true]);
}

public function geocodeAddress(Request $request)
{
    /*
     * Callers send either a ready-made `address` string (listing add/edit
     * forms) or the individual address fields — accept both, otherwise the
     * form gets `status: false` for every single lookup.
     */
    $address = trim((string) $request->input('address'));

    if ($address === '') {
        $address = collect([
            $request->plot_no,
            $request->building_name,
            $request->street_name,
            $request->landmark,
            $request->area,
            $request->city,
            $request->state,
            $request->pincode,
        ])->filter()->implode(', ');
    }

    if ($address === '') {
        return response()->json([
            'status' => false
        ]);
    }

    // Cache every lookup so re-editing the same address costs one API call.
    $cacheKey = 'geocode:' . md5(mb_strtolower($address));

    $coords = Cache::remember($cacheKey, now()->addDays(30), function () use ($address) {
        return $this->lookupCoordinates($address);
    });

    if (empty($coords)) {
        // A miss can also mean "blocked/timed out" — don't keep it cached
        // for 30 days, let the next attempt retry.
        Cache::forget($cacheKey);

        return response()->json([
            'status' => false
        ]);
    }

    return response()->json([
        'status' => true,
        'lat'    => $coords['lat'],
        'lng'    => $coords['lng'],
    ]);
}

/**
 * Resolve an address to coordinates, trying each configured provider in
 * turn and returning the first hit as ['lat' => ..., 'lng' => ...].
 *
 * Nominatim is free but its usage policy caps callers at ~1 request/sec
 * and answers 403 once you cross it — on shared hosting that block hits
 * the whole server IP, which is why geocoding fails on live but works on
 * local. So the keyed providers are tried FIRST when configured, and
 * Nominatim is only the no-key fallback.
 */
/**
 * Public (not private) so ListingController::cityCategoryArea() can reuse
 * the same cached, multi-provider geocoding when it needs coordinates for
 * an area that has no listing tagged with its exact name — see the
 * 5km-radius fallback there.
 */
public function lookupCoordinates(string $address): ?array
{
    $providers = [];

    if (config('services.geocoding.locationiq')) {
        $providers['locationiq'] = fn () => $this->geocodeViaLocationIq($address);
    }

    if (config('services.geocoding.google')) {
        $providers['google'] = fn () => $this->geocodeViaGoogle($address);
    }

    $providers['nominatim'] = fn () => $this->geocodeViaNominatim($address);

    foreach ($providers as $name => $provider) {
        try {
            $coords = $provider();

            if (!empty($coords['lat']) && !empty($coords['lng'])) {
                return $coords;
            }
        } catch (\Exception $e) {
            Log::warning('Geocode provider threw', [
                'provider' => $name,
                'address'  => $address,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    return null;
}

/**
 * LocationIQ — same data as Nominatim (it is a hosted OSM geocoder) but
 * keyed, so no shared-IP blocking. Free tier covers 5k lookups/day.
 */
private function geocodeViaLocationIq(string $address): ?array
{
    $response = Http::timeout(15)->get('https://us1.locationiq.com/v1/search', [
        'key'          => config('services.geocoding.locationiq'),
        'q'            => $address,
        'format'       => 'json',
        'limit'        => 1,
        'countrycodes' => 'in',
    ]);

    if (!$response->successful()) {
        $this->logGeocodeFailure('locationiq', $address, $response);
        return null;
    }

    $result = $response->json();

    if (empty($result[0]['lat']) || empty($result[0]['lon'])) {
        return null;
    }

    return [
        'lat' => (string) $result[0]['lat'],
        'lng' => (string) $result[0]['lon'],
    ];
}

/**
 * Google Geocoding API — most accurate for Indian addresses, but needs a
 * billing-enabled key.
 */
private function geocodeViaGoogle(string $address): ?array
{
    $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/geocode/json', [
        'address'    => $address,
        'components' => 'country:IN',
        'region'     => 'in',
        'key'        => config('services.geocoding.google'),
    ]);

    if (!$response->successful()) {
        $this->logGeocodeFailure('google', $address, $response);
        return null;
    }

    $result = $response->json();

    // Google answers HTTP 200 even for REQUEST_DENIED / OVER_QUERY_LIMIT,
    // so the real outcome is in the `status` field.
    if (($result['status'] ?? '') !== 'OK') {
        if (($result['status'] ?? '') !== 'ZERO_RESULTS') {
            Log::warning('Google geocode failed', [
                'address' => $address,
                'status'  => $result['status'] ?? 'unknown',
                'message' => $result['error_message'] ?? null,
            ]);
        }

        return null;
    }

    $location = $result['results'][0]['geometry']['location'] ?? null;

    if (empty($location['lat']) || empty($location['lng'])) {
        return null;
    }

    return [
        'lat' => (string) $location['lat'],
        'lng' => (string) $location['lng'],
    ];
}

/**
 * Nominatim — free, no key, but rate-limited and IP-blocked aggressively.
 */
private function geocodeViaNominatim(string $address): ?array
{
    $response = Http::withHeaders([
        // Their policy requires an identifying User-Agent.
        'User-Agent' => 'ServiceBazzar/1.0 (' . config('app.url') . ')',
        'Accept'     => 'application/json',
    ])->timeout(15)->get('https://nominatim.openstreetmap.org/search', [
        'format'       => 'json',
        'limit'        => 1,
        'countrycodes' => 'in',
        'q'            => $address,
    ]);

    if (!$response->successful()) {
        $this->logGeocodeFailure('nominatim', $address, $response);
        return null;
    }

    $result = $response->json();

    if (empty($result[0]['lat']) || empty($result[0]['lon'])) {
        return null;
    }

    return [
        'lat' => (string) $result[0]['lat'],
        'lng' => (string) $result[0]['lon'],
    ];
}

/**
 * Logging a failed lookup is the only way to tell "address not found"
 * apart from "this provider blocked us" (403 / quota) after the fact.
 */
private function logGeocodeFailure(string $provider, string $address, $response): void
{
    Log::warning('Geocode provider failed', [
        'provider' => $provider,
        'status'   => $response->status(),
        'address'  => $address,
        'body'     => mb_substr($response->body(), 0, 300),
    ]);
}


public function authorEmailContact(Request $request, SendMail $mail)
{
    /*
     * This one mails the listing owner directly, so an unguarded bot here
     * does not just add a row — it sends mail from our domain to a business
     * owner, which is how a sender reputation gets spent. Caught submissions
     * are answered as if they sent and no mail leaves.
     */
    if (FormGuard::looksAutomated($request, $why)) {
        Log::info('Owner email rejected as automated', [
            'reason' => $why,
            'ip'     => $request->ip(),
        ]);

        return $this->enquiryFeedback($request, 'success', 'Message sent successfully');
    }

    $request->validate([
        'listing_id' => 'required|integer|exists:listings,id',
        'name'       => ['required', 'string', 'max:255', new RealName()],
        'phone'      => ['required', 'string', 'max:20', new IndianMobile()],
        'email'      => 'required|email|max:255',
        'message'    => 'required|string|max:5000',
    ]);

    if ($response = $this->requireVerifiedPhone($request)) {
        return $response;
    }

    $listing = Listing::findOrFail($request->listing_id);

    $ownerEmail = $listing->email ?? optional($listing->user)->email;

    if (empty($ownerEmail)) {
        return $this->enquiryFeedback($request, 'danger', 'This business has no email on file — please use the enquiry form instead.');
    }

    $message = '
        <div>
            <p><strong>New Enquiry For:</strong> '.$listing->name.'</p>

            <p>
                <strong>Customer Name:</strong> '.$request->name.'<br>
                <strong>Mobile No:</strong> '.$request->phone.'<br>
                <strong>Email ID:</strong> '.$request->email.'
            </p>

            <p>
                <strong>Message:</strong><br>
                '.nl2br(e($request->message)).'
            </p>

            <hr>

            <div style="text-align:center;padding-top:15px;">
                <img src="https://www.servicebazzar.com/assets/images/logo/logo.png"
                     alt="Service Bazzar"
                     style="max-width:180px;margin-bottom:10px;">

                <p style="margin:10px 0 5px;">
                    <strong>Service Bazzar</strong>
                </p>

                <p style="margin:0;color:#666;">
                    All Services. One Bazzar.
                </p>

                <p style="margin-top:10px;">
                    <a href="https://www.servicebazzar.com">www.servicebazzar.com</a>
                </p>
            </div>
        </div>
    ';

    $data = [
        'name'     => $listing->name,
        'email'    => $ownerEmail,
        'template' => 'email_enquiry',
        'value'    => [
            'message' => $message
        ]
    ];

    try {
        $mail->sendMail($data);
    } catch (\Throwable $exception) {
        // A refused address or a down SMTP server is a delivery problem, not
        // a 500 for the visitor — tell them plainly that it did not go.
        Log::error('Email enquiry could not be delivered', [
            'listing_id' => $listing->id,
            'owner'      => $ownerEmail,
            'message'    => $exception->getMessage(),
        ]);

        return $this->enquiryFeedback($request, 'danger', 'We could not deliver your message right now. Please try again in a moment.');
    }

    OtpGate::consume($request->phone);

    return $this->enquiryFeedback($request, 'success', 'Email enquiry sent successfully');
}


public function serviceKeywords()
{
    $categories = Category::where('status', 1)
        ->whereHas('serviceKeywords', function ($q) {
            $q->where('status', 1);
        })
        ->get();

    return view($this->theme . 'service_keywords.categories', compact('categories'));
}


public function serviceKeywordCategory($category)
{
    $keywordCategory = Category::where('slug', $category)
        ->where('status', 1)
        ->firstOrFail();

    $keywords = ServiceKeyword::where('category_id', $keywordCategory->id)
        ->where('status', 1)
        ->latest()
        ->paginate(30);

    return view($this->theme . 'service_keywords.keywords', compact(
        'keywordCategory',
        'keywords'
    ));
}


public function ajaxPopularCategories(Request $request)
{
    $locationName = trim((string) $request->city);
    $locationId   = $request->location_id;
    $latitude     = $request->latitude;
    $longitude    = $request->longitude;

    if (
        $locationName === '' &&
        empty($locationId) &&
        (empty($latitude) || empty($longitude))
    ) {
        return '';
    }

    $currentLocation = null;

    /*
    |--------------------------------------------------------------------------
    | 1. Dropdown se select hui location
    |--------------------------------------------------------------------------
    |
    | Normal location selection me location_id milti hai.
    | Isliye sabse pehle exact location_id use karein.
    |
    */
    if (!empty($locationId)) {
        $currentLocation = Location::query()
            ->where('status', 1)
            ->where('id', $locationId)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Detect Location
    |--------------------------------------------------------------------------
    |
    | Detect Location me location_id empty hoti hai, lekin latitude aur
    | longitude available hote hain. Nearest database location find karein.
    |
    | Location table me latitude/longitude column names alag hain to
    | neeche unhe apne actual column names se replace karein.
    |
    */
    if (
        !$currentLocation &&
        is_numeric($latitude) &&
        is_numeric($longitude)
    ) {
        $currentLocation = Location::query()
            ->where('status', 1)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('*')
            ->selectRaw(
                '(6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(latitude))
                )) AS distance',
                [
                    (float) $latitude,
                    (float) $longitude,
                    (float) $latitude
                ]
            )
            ->orderBy('distance')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | 3. City-name fallback
    |--------------------------------------------------------------------------
    |
    | Coordinates se location na mile to normalized city name use karein.
    |
    */
    if (!$currentLocation && $locationName !== '') {
        $cleanLocationName = trim(
            explode(',', $locationName)[0]
        );

        $locationSlug = Str::slug($cleanLocationName);

        $currentLocation = Location::query()
            ->where('status', 1)
            ->where(function ($query) use (
                $cleanLocationName,
                $locationSlug
            ) {
                $query
                    ->whereRaw(
                        'LOWER(TRIM(name)) = ?',
                        [Str::lower($cleanLocationName)]
                    )
                    ->orWhere('slug', $locationSlug);
            })
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Location resolve na ho to all categories bilkul mat return karein
    |--------------------------------------------------------------------------
    */
    if (!$currentLocation) {
        return '';
    }

    session([
        'detected_city'       => $currentLocation->name,
        'selected_location'   => $currentLocation->name,
        'user_location'       => $currentLocation->name,
        'selected_location_id'=> $currentLocation->id,
    ]);

    // Same query index() uses for its location-scoped branch — see
    // buildPopularCategoriesQuery() above.
    $popularCategories = $this->buildPopularCategoriesQuery($currentLocation);

    $popularCategoriesLocation = $currentLocation;

    return view(
        $this->theme . 'partials.popular_categories_ajax',
        compact('popularCategories', 'popularCategoriesLocation')
    )->render();
}

public function serviceKeywordDetails($category, $keyword)
{
    $keywordCategory = Category::where('slug', $category)
        ->where('status', 1)
        ->firstOrFail();

    $keyword = ServiceKeyword::where('slug', $keyword)
        ->where('category_id', $keywordCategory->id)
        ->where('status', 1)
        ->firstOrFail();

    $relatedKeywords = ServiceKeyword::where('category_id', $keywordCategory->id)
        ->where('status', 1)
        ->where('id', '!=', $keyword->id)
        ->latest()
        ->take(10)
        ->get();

    $relatedCategories = Category::where('status', 1)
        ->where('id', '!=', $keywordCategory->id)
        ->whereIn('id', function ($query) {
            $query->select('category_id')
                ->from('service_keywords')
                ->where('status', 1)
                ->whereNotNull('category_id')
                ->groupBy('category_id');
        })
        ->latest()
        ->take(10)
        ->get();

    /*
     * Businesses that actually serve this keyword's category. Scoped to the
     * visitor's location when we know it, because "AC repair" in Jaipur is a
     * different page from "AC repair" anywhere — but never scoped so tightly
     * that the section comes back empty.
     */
    $keywordLocationId = session('selected_location_id');

    $listingQuery = fn () => Listing::notExpired()
        ->where('status', 1)
        ->where('category_id', $keywordCategory->id)
        ->with(['category', 'location'])
        ->withCount('ratings')
        ->withAvg('ratings', 'ratings')
        ->orderByDesc('is_featured')
        ->orderByDesc('trust')
        ->orderByDesc('verified');

    $keywordListings = collect();

    if ($keywordLocationId) {
        $keywordListings = $listingQuery()
            ->where('location_id', $keywordLocationId)
            ->take(9)
            ->get();
    }

    if ($keywordListings->isEmpty()) {
        $keywordListings = $listingQuery()->take(9)->get();
    }

    $keywordListingTotal = Listing::notExpired()
        ->where('status', 1)
        ->where('category_id', $keywordCategory->id)
        ->count();

    return view($this->theme . 'service_keywords.details', compact(
        'keywordCategory',
        'keyword',
        'relatedKeywords',
        'relatedCategories',
        'keywordListings',
        'keywordListingTotal'
    ));
}

public function handleSingleSlug(Request $request, $slug)
{
    $location = Location::where('status', 1)
        ->where('slug', $slug)
        ->first();

    if ($location) {
        return $this->index($slug);
    }

    /*
     * Nationwide service hub. A bare category slug (e.g. /packers-and-movers)
     * used to fall through to linkDetails() and 404; now it lists that service
     * across every city — the top-level /[service] hub the SEO audit asked for.
     * Reuses the city/category lander with no location in context, so it also
     * gets the Service / FAQPage / ItemList JSON-LD for free.
     */
    $category = Category::where('status', 1)
        ->where('slug', $slug)
        ->first();

    if ($category) {
        return $this->listingsCategory($request, $slug);
    }

    return $this->linkDetails($slug);
}

public function userProfile($username)
{
    // Prefer the logged-in user when the requested handle resolves to their own
    // profile. Names/slugs aren't unique, so the fuzzy lookup below can pick a
    // *different* user who shares the same name — which would hide the owner's
    // Edit button and show the wrong avatar. Matching the authenticated user
    // first guarantees the owner always sees their own profile.
    $authUser = auth()->user();
    $handle = strtolower($username);
    if ($authUser && (
            strtolower((string) $authUser->username) === $handle
            || \Illuminate\Support\Str::slug($authUser->first_name.' '.$authUser->last_name) === $handle
            || strtolower(trim($authUser->first_name.'_'.$authUser->last_name)) === $handle
        )) {
        $user = $authUser;
    } else {
        // Search by username (exact), first_name (case-insensitive), or slug variants
        $user = User::where(function($q) use ($username) {
            $q->where('username', $username)
              ->orWhereRaw("LOWER(first_name) = LOWER(?)", [$username])
              ->orWhereRaw("LOWER(CONCAT(LOWER(first_name), '-', LOWER(last_name))) = ?", [strtolower($username)])
              ->orWhereRaw("LOWER(CONCAT(LOWER(first_name), '_', LOWER(last_name))) = ?", [strtolower($username)]);
        })->first();
    }

    if (!$user) {
        abort(404);
    }

    // The user's own reviews — each carries the business it was left on, so the
    // profile can render a JustDial-style social feed (business card + review +
    // like/comment counts). Eager-loads keep it to a handful of queries.
    $reviews = \App\Models\Rating::where('user_id', $user->id)
        ->with(['listing' => function ($q) {
            $q->with('category', 'location');
        }])
        ->withCount(['likes', 'comments'])
        ->latest('id')
        ->take(30)
        ->get()
        ->filter(fn ($r) => $r->listing !== null)
        ->values();

    // The "Recent Search" rail: this person's own trail, recorded in
    // user_recent_activity as they open businesses and run searches.
    //
    // There used to be a fallback here to the four newest listings "so the
    // rail is never empty". That made every account with no reviews show the
    // same four businesses, as if two different people had searched for the
    // same things. Nothing is invented now — an empty rail is hidden.
    $recentBusinesses = \App\Models\UserRecentActivity::where('user_id', $user->id)
        ->where('type', \App\Models\UserRecentActivity::TYPE_LISTING)
        ->with(['listing' => fn ($q) => $q->with('category', 'location')])
        ->latest('updated_at')
        ->take(4)
        ->get()
        ->pluck('listing')
        ->filter()
        ->unique('id')
        ->values();

    $recentSearches = \App\Models\UserRecentActivity::where('user_id', $user->id)
        ->where('type', \App\Models\UserRecentActivity::TYPE_SEARCH)
        ->latest('updated_at')
        ->take(8)
        ->pluck('term')
        ->filter()
        ->values();

    $data['user'] = $user;
    $data['reviews'] = $reviews;
    // "Posts" reflects the businesses this user has listed on the directory.
    $data['postCount'] = Listing::where('user_id', $user->id)->count();
    $data['recentBusinesses'] = $recentBusinesses;
    $data['recentSearches'] = $recentSearches;
    $data['seoTitle'] = $user->first_name . ' ' . $user->last_name . ' | ' . ($this->setting->site_name ?? 'Service Bazzar');
    $data['seoDescription'] = 'View ' . $user->first_name . '\'s profile on ' . ($this->setting->site_name ?? 'Service Bazzar');
    // This is the user's own account page, not a business/listing page —
    // it should not show up in search on its own. Their reviews already
    // surface (and are indexable) on the listing page each was left on.
    $data['seoRobots'] = 'noindex, follow';

    return view($this->theme . 'user.profile-view', $data);
}

/**
 * Sitemap page - directory of all cities, services, and links
 * Helps users navigate and improves SEO via internal linking
 */
public function sitemap()
{
    $data = [];
    $data['locations'] = activeLocations();
    $data['popularLocations'] = activeLocations()->filter(fn($loc) => $loc->is_popular == 1)->take(10);
    $data['categories'] = \App\Models\Category::where('status', 1)->orderBy('name')->get();
    $data['popularCategories'] = \App\Models\Category::where('status', 1)->orderBy('name')->limit(10)->get();

    return view($this->theme . 'sitemap', $data);
}

/**
 * Delete account page - Google Play Store requirement for user data deletion
 * Users can request account deletion here without signing in
 */
public function deleteAccount()
{
    return view($this->theme . 'delete-account');
}

/**
 * Handle account deletion request
 * Accepts phone and sends OTP for verification before deletion
 */
public function deleteAccountSubmit(\Illuminate\Http\Request $request)
{
    $request->validate([
        'phone' => ['required', new \App\Rules\IndianMobile()],
    ]);

    try {
        // Find user by phone
        $user = \App\Models\User::where('mobile', \App\Support\MobileNumber::national($request->phone))->first();

        if (!$user) {
            return back()->with('error', 'No account found with this phone number.');
        }

        // Send OTP for verification
        $otp = (string) verification_code(6);
        \App\Support\ApiOtpStore::challenge($request->phone, $otp);

        return back()->with('success', 'Verification OTP sent to your phone. Please confirm to delete your account.')
                     ->with('phone', $request->phone)
                     ->with('user_id', $user->id);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Delete account error', ['message' => $e->getMessage()]);
        return back()->with('error', 'Could not process your request. Please try again.');
    }
}

/**
 * Bridge call (number masking) from the website's listing detail page.
 *
 * Session-based twin of Api\ExotelController::initiateCall — the app talks
 * to that one with a Bearer token, the website (no token, just a logged-in
 * session) talks to this one. Same ExotelService and same category/city
 * eligibility gate underneath, so a listing is either bridge-call-eligible
 * everywhere or nowhere.
 */
public function bridgeCall(\Illuminate\Http\Request $request, \App\Services\ExotelService $exotel)
{
    $request->validate([
        'listing_id' => 'required|integer|exists:listings,id',
    ]);

    $user = auth()->user();
    if (!$user || empty($user->mobile)) {
        return response()->json([
            'success' => false,
            'message' => 'Please log in with a mobile number to use this feature.',
        ], 401);
    }

    $listing = \App\Models\Listing::where('status', 1)->find($request->listing_id);
    if (!$listing) {
        return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
    }

    $categoryIds = config('exotel.restricted_category_ids', []);
    $locationIds = config('exotel.restricted_location_ids', []);
    $eligible = (empty($categoryIds) || in_array((string) $listing->category_id, $categoryIds, true))
        && (empty($locationIds) || in_array((string) $listing->location_id, $locationIds, true));

    if (!$eligible) {
        return response()->json([
            'success' => false,
            'message' => 'Bridge call is not available for this listing yet.',
        ], 422);
    }

    $vendorNumber = $listing->mobile_numbers[0] ?? null;
    if (!$vendorNumber) {
        return response()->json(['success' => false, 'message' => 'No contact number on this listing.'], 422);
    }

    $result = $exotel->initiateCall(
        \App\Services\ExotelService::formatPhoneNumber($user->mobile),
        \App\Services\ExotelService::formatPhoneNumber($vendorNumber),
        ['listing_id' => $listing->id]
    );

    if (!$result['success']) {
        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Could not connect the call right now. Please try again.',
        ], 503);
    }

    return response()->json([
        'success' => true,
        'message' => 'Connecting... your phone will ring shortly.',
    ]);
}

/**
 * Option B — masked outbound call the customer places themselves.
 *
 * The customer taps "Call Now", their dialler opens pre-filled with our
 * ExoPhone (the virtual number), and they dial it from their own SIM. So
 * that Exotel knows which vendor to bridge that incoming call to, this
 * endpoint records a short-lived caller->vendor mapping first, keyed on the
 * customer's own number. When the ExoPhone rings, Exotel fetches the
 * destination from voiceConnect() below, which reads this same mapping.
 *
 * The mapping lives in the shared (Redis) cache so it is visible from both
 * app servers, and expires in minutes so a stale click can never connect a
 * later, unrelated call.
 */
public function prepareMaskedCall(\Illuminate\Http\Request $request)
{
    $request->validate([
        'listing_id' => 'required|integer|exists:listings,id',
    ]);

    $user = auth()->user();
    if (!$user || empty($user->mobile)) {
        return response()->json([
            'success' => false,
            'message' => 'Please log in with a mobile number to use this feature.',
        ], 401);
    }

    $listing = \App\Models\Listing::where('status', 1)->find($request->listing_id);
    if (!$listing) {
        return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
    }

    $categoryIds = config('exotel.restricted_category_ids', []);
    $locationIds = config('exotel.restricted_location_ids', []);
    $eligible = (empty($categoryIds) || in_array((string) $listing->category_id, $categoryIds, true))
        && (empty($locationIds) || in_array((string) $listing->location_id, $locationIds, true));

    if (!$eligible) {
        return response()->json([
            'success' => false,
            'message' => 'Bridge call is not available for this listing yet.',
        ], 422);
    }

    $vendorNumber = $listing->mobile_numbers[0] ?? null;
    if (!$vendorNumber) {
        return response()->json(['success' => false, 'message' => 'No contact number on this listing.'], 422);
    }

    // Key the mapping on the last 10 digits of the customer's number, the
    // shape Exotel reports the caller in (CallFrom), so voiceConnect() can
    // match it however Exotel formats the incoming call.
    $callerKey = substr(preg_replace('/[^0-9]/', '', $user->mobile), -10);
    \Illuminate\Support\Facades\Cache::store(config('cache.default'))->put(
        'exotel_mask:' . $callerKey,
        '0' . substr(preg_replace('/[^0-9]/', '', $vendorNumber), -10),
        now()->addMinutes(5)
    );

    \Illuminate\Support\Facades\Log::info('Exotel masked call prepared', [
        'caller' => $callerKey,
        'listing_id' => $listing->id,
    ]);

    return response()->json([
        'success'        => true,
        'virtual_number' => config('exotel.virtual_number'),
        'message'        => 'Dialling the secure number...',
    ]);
}

/**
 * Exotel incoming-call webhook for the ExoPhone (no auth — Exotel calls it).
 *
 * When a customer dials the virtual number, Exotel's "Connect" applet fetches
 * the number to bridge to from this URL. We look up the caller (CallFrom)
 * in the mapping prepareMaskedCall() stored and return the vendor's number as
 * plain text, which is what Exotel's dynamic-destination Connect expects.
 * Both legs then show only the ExoPhone, so neither real number is exposed.
 */
public function voiceConnect(\Illuminate\Http\Request $request)
{
    $rawFrom = $request->input('CallFrom', $request->input('From', ''));
    $callerKey = substr(preg_replace('/[^0-9]/', '', (string) $rawFrom), -10);

    $vendor = \Illuminate\Support\Facades\Cache::store(config('cache.default'))
        ->get('exotel_mask:' . $callerKey);

    \Illuminate\Support\Facades\Log::info('Exotel voiceConnect hit', [
        'caller'   => $callerKey,
        'vendor'   => $vendor,
        'call_sid' => $request->input('CallSid'),
        'params'   => $request->all(),
    ]);

    // Exotel's "dynamic Connect params" URL expects a JSON body describing who
    // to bridge to. `outgoing_phone_number` is the ExoPhone, so the vendor
    // sees the virtual number, never the customer's. No mapping (expired / a
    // stray call) => empty destinations, so Exotel connects nobody.
    return response()->json([
        'fetch_after_attempt'   => false,
        'select_number'         => 'sequential',
        'destination'           => [
            'numbers' => $vendor ? [$vendor] : [],
        ],
        'outgoing_phone_number' => config('exotel.virtual_number'),
        'record'                => (bool) config('exotel.recording'),
    ]);
}

/**
 * Notify the listing owner by email, FCM push, and browser push
 * when a new enquiry arrives on their listing.
 */
private function notifyListingOwner(int $listingId, string $enquirerName, array $info = []): void
{
    $listing = Listing::with('user')->find($listingId);
    if (!$listing || !$listing->user) {
        return;
    }

    $owner       = $listing->user;
    $leadsUrl    = route('user.listing.leads', $listing->id);
    $title       = 'New Enquiry — ' . $listing->name;
    $body        = $enquirerName . ' has enquired about your listing.';

    // 1. Email
    $ownerEmail = $listing->email ?? $owner->email;
    if (filled($ownerEmail) && !str_ends_with(strtolower($ownerEmail), '@whatsapp.servicebazzar.com')) {
        try {
            app(SendMail::class)->sendMailProcess([
                'email'    => $ownerEmail,
                'name'     => $owner->fullname(),
                'subject'  => $title,
                'template' => null,
                'value'    => [
                    'message' =>
                        '<p>Hello,</p>' .
                        '<p><strong>' . e($enquirerName) . '</strong> has submitted an enquiry on your listing ' .
                        '<strong>' . e($listing->name) . '</strong>.</p>' .
                        \App\Support\VendorEnquiryNotifier::emailTable($listing, ['name' => $enquirerName] + $info) .
                        '<p><a href="' . $leadsUrl . '" style="background:#e63c3c;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;">View Enquiry</a></p>',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Enquiry owner email failed', ['listing_id' => $listingId, 'error' => $e->getMessage()]);
        }
    }

    // 2 + 3. Header bell, mobile app (FCM) and browser push — with the whole enquiry in them.
    \App\Support\VendorEnquiryNotifier::notify($listing, ['name' => $enquirerName] + $info);
}

}
