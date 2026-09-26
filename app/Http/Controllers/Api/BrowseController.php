<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Rating;
use App\Models\RatingComment;
use App\Models\RatingLike;
use App\Models\Subcategory;
use Illuminate\Http\Request;

/**
 * Consumer discovery for the mobile app — the "find a business" side.
 *
 * JSON twin of the public website (App\Http\Controllers\SiteController):
 * home, category grid, search with filters/sort, business detail with its
 * reviews, plus writing a review, liking/commenting on one, sending an
 * enquiry, and location search. The app is login-first, so every route runs
 * behind `user.api` — which also lets each business card carry whether the
 * current user has favourited it.
 *
 * Search filters/sort mirror SiteController@search exactly (verified / trust /
 * top_rated, sort by rating / popular / distance). A review from a signed-in
 * user is saved straight away with no OTP — the login already proved the
 * phone — which is the logged-in branch of ListingController@review.
 */
class BrowseController extends Controller
{
    /* ---------------------------------------------------------------------
     | Home & categories
     | -------------------------------------------------------------------*/

    public function home(Request $request)
    {
        $favIds = $this->favoriteIds($request);

        $verified = fn ($q) => $q->where('is_verified', 1);

        $featured = Listing::notExpired()
            ->where('status', 1)->where('trust', 1)
            ->with('category', 'location')
            ->orderByDesc('id')->limit(12)->get();

        return response()->json([
            'success'    => true,
            'categories' => Category::where('status', 1)
                ->get(['id', 'name', 'slug', 'image', 'icon'])
                ->map(fn ($c) => $this->categoryPayload($c)),
            'featured'   => $featured->map(fn ($l) => $this->listingCard($l, $favIds))->values(),
            'banners'    => Banner::where('status', 1)->orderBy('sort_order')->latest()->get()
                ->map(fn ($b) => [
                    'title'  => $b->title,
                    'image'  => $b->desktop_banner ? asset('assets/images/category/banner/' . $b->desktop_banner) : null,
                    'mobile' => $b->mobile_banner ? asset('assets/images/category/banner/' . $b->mobile_banner) : null,
                    'link'   => $b->link,
                ]),
            'locations'  => Location::where('status', 1)->orderByDesc('id')->get(['id', 'name', 'slug'])
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'slug' => $l->slug]),
        ]);
    }

    public function categories()
    {
        return response()->json([
            'success'    => true,
            'categories' => Category::where('status', 1)->orderBy('name')
                ->get(['id', 'name', 'slug', 'image', 'icon', 'description'])
                ->map(fn ($c) => $this->categoryPayload($c)),
        ]);
    }

    public function subcategories(string $slug)
    {
        $category = Category::where('status', 1)->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success'       => true,
            'category'      => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug],
            'subcategories' => Subcategory::where('status', 1)->where('category_id', $category->id)
                ->orderBy('name')->get(['id', 'name', 'slug'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug]),
        ]);
    }

    /**
     * The options that populate the "More Filters" panel for a category. Every
     * key here is a value categoryListings() below understands, so the app can
     * feed a chosen chip straight back into /api/categories/{slug}/listings.
     */
    public function filters(string $slug)
    {
        $category = Category::where('status', 1)->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success'  => true,
            'category' => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug],
            'subcategories' => Subcategory::where('status', 1)->where('category_id', $category->id)
                ->orderBy('name')->get(['id', 'name', 'slug'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug])->values(),
            // "More Filters" chips — can be combined (Verified AND Trust).
            'filters'  => [
                ['key' => 'verified', 'label' => 'Verified'],
                ['key' => 'trust',    'label' => 'Trust'],
            ],
            // "Sort By" — distance needs latitude/longitude on the request.
            'sort'     => [
                ['key' => 'rating',   'label' => 'Rating'],
                ['key' => 'popular',  'label' => 'Popular'],
                ['key' => 'distance', 'label' => 'Distance'],
            ],
            // "Ratings" — minimum star value.
            'ratings'  => [
                ['key' => 1, 'label' => '1 Star'],
                ['key' => 2, 'label' => '2 Star'],
                ['key' => 3, 'label' => '3 Star'],
                ['key' => 4, 'label' => '4 Star'],
                ['key' => 5, 'label' => '5 Star'],
            ],
        ]);
    }

    /**
     * City + category listing page — the JSON twin of the website URL
     * /{city}/{category}?sort=&filter=  (ListingController@cityCategoryArea).
     * e.g. GET /api/jaipur/packers-and-movers?sort=rating&filter=trust
     *
     * {location} is matched against a listing's city / area / address / linked
     * location, and {category} against the category slug. Then the same panel
     * filters as categoryListings() apply.
     */
    public function cityCategoryListings(Request $request, string $location, string $category)
    {
        // Same slugs the website takes: a category, a subcategory, either one
        // with "-in-{area}" on the end.
        [$cat, $sub, $area] = $this->resolveServiceSlug($category);

        if (!$cat && !$sub) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        return $this->respondListings($request, $cat, $location, $sub, $area);
    }

    /** @return array{0: ?Category, 1: ?Subcategory, 2: ?string} */
    protected function resolveServiceSlug(string $slug): array
    {
        $find = function (string $s) {
            $cat = Category::where('status', 1)->where('slug', $s)->first();

            return $cat ? [$cat, null] : [null, Subcategory::where('status', 1)->where('slug', $s)->first()];
        };

        [$cat, $sub] = $find($slug);

        if (($cat || $sub) || !str_contains($slug, '-in-')) {
            return [$cat, $sub, null];
        }

        [$cat, $sub] = $find(\Illuminate\Support\Str::before($slug, '-in-'));
        $area = trim(str_replace('-', ' ', \Illuminate\Support\Str::after($slug, '-in-')));

        return [$cat, $sub, ($cat || $sub) && $area !== '' ? $area : null];
    }

    /**
     * Category listing without a city — /api/categories/{slug}/listings.
     * Kept for callers that filter by category alone.
     */
    public function categoryListings(Request $request, string $slug)
    {
        $category = Category::where('status', 1)->where('slug', $slug)->firstOrFail();

        return $this->respondListings($request, $category, null);
    }

    /**
     * Shared body of the two listing endpoints above.
     *
     * Reads the same chip keys filters() advertises:
     *   filter=verified,trust  (comma-separated or filter[]=; combined with AND)
     *   rating=4               (minimum stars)
     *   sort=rating|popular|distance
     *   latitude,longitude     (required for sort=distance)
     * Paginated 15 at a time, same card shape as search().
     *
     * Reads params with input() (body OR query) on purpose: Cloudflare's scraper
     * rule challenges any URL whose query string carries filter=/rating=/sort=,
     * so the app can POST these in the body to stay clear of that rule.
     * See [[servicebazzar-scraper-defence]].
     */
    protected function respondListings(Request $request, ?Category $category, ?string $location, ?Subcategory $subcategory = null, ?string $area = null)
    {
        $lat = $request->filled('latitude')  ? (float) $request->latitude  : null;
        $lng = $request->filled('longitude') ? (float) $request->longitude : null;

        $listings = Listing::notExpired()->where('status', 1)
            ->when($subcategory, fn ($q) => $q->where(fn ($qq) => $qq
                ->whereJsonContains('subcategory_ids', (string) $subcategory->id)
                ->orWhereJsonContains('subcategory_ids', $subcategory->id)))
            ->when(!$subcategory, fn ($q) => $q->where('category_id', $category->id))
            ->with('category', 'location')->withCount('ratings');

        if ($area !== null && $area !== '') {
            $listings->where(fn ($q) => $q->where('area', 'like', "%{$area}%")
                ->orWhere('address', 'like', "%{$area}%")
                ->orWhere('landmark', 'like', "%{$area}%"));
        }

        // City/area text match, mirroring search(). Slugs come hyphenated.
        $location = $location !== null ? trim(str_replace('-', ' ', $location)) : null;
        if ($location !== null && $location !== '') {
            $listings->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                    ->orWhere('area', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%")
                    ->orWhereHas('location', fn ($lq) => $lq->where('name', 'like', "%{$location}%"));
            });
        }

        // "More Filters" chips — accept filter=verified,trust or filter[]=verified.
        $chips = collect(is_array($request->input('filter'))
                ? $request->input('filter')
                : explode(',', (string) $request->input('filter')))
            ->map(fn ($c) => trim(strtolower($c)))->filter();

        if ($chips->contains('verified')) $listings->where('verified', 1);
        if ($chips->contains('trust'))    $listings->where('trust', 1);

        // "Ratings" — minimum star value.
        if ($request->filled('rating')) {
            $listings->where('review', '>=', (float) $request->rating);
        }

        if ($lat !== null && $lng !== null) {
            $listings->whereNotNull('latitude')->where('latitude', '!=', '')
                ->whereNotNull('longitude')->where('longitude', '!=', '')
                ->selectRaw(
                    'listings.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lng, $lat]
                );
        }

        switch ($request->input('sort')) {
            case 'rating':   $listings->orderByDesc('review'); break;
            case 'popular':  $listings->orderByDesc('view'); break;
            case 'distance':
                $lat !== null && $lng !== null
                    ? $listings->orderBy('distance')
                    : $listings->orderByDesc('id');
                break;
            default:
                $listings->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('top_rank')->orderByDesc('id');
        }

        // page can arrive in the body too (POST), so read it with input().
        $page   = $listings->paginate(15, ['*'], 'page', (int) $request->input('page', 1));
        $favIds = $this->favoriteIds($request);

        return response()->json([
            'success'  => true,
            'city'     => $location ?: null,
            'area'     => $area ?: null,
            'category' => $category
                ? ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug]
                : ($subcategory->category ? ['id' => $subcategory->category->id, 'name' => $subcategory->category->name, 'slug' => $subcategory->category->slug] : null),
            'subcategory' => $subcategory ? ['id' => $subcategory->id, 'name' => $subcategory->name, 'slug' => $subcategory->slug] : null,
            'listings' => collect($page->items())->map(fn ($l) => $this->listingCard($l, $favIds))->values(),
            'meta'     => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    /**
     * The website's search-box dropdown, for the app: categories, subcategories
     * and businesses matching what the user typed, limited to the chosen city.
     *
     *   GET /api/suggest?q=pack&city=jaipur&area=jagatpura&latitude=..&longitude=..
     *
     * Empty q returns the city's most-listed categories (the website's
     * "trending" list). Category/subcategory rows carry `listings_url`, the
     * endpoint that opens that choice in that city.
     */
    public function suggest(Request $request)
    {
        $rawQ = trim((string) $request->query('q', ''));
        $location = $this->resolveCity($request->query('city') ?? $request->query('location_id'));
        $area = trim((string) $request->query('area', '')) ?: null;

        if (($request->filled('city') || $request->filled('location_id')) && !$location) {
            return response()->json(['success' => false, 'message' => 'City not found.'], 404);
        }

        $counts = $this->taxonomyCounts($location, $area);
        $catRow = function (Category $c) use ($counts, $location, $area) {
            return [
                'type' => 'category',
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'listings_count' => $counts['cat'][$c->id] ?? 0,
                'listings_url' => $this->listingsUrl($location, $c->slug, $area),
            ];
        };
        $subRow = function (Subcategory $s) use ($counts, $location, $area) {
            return [
                'type' => 'subcategory',
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'category' => $s->category ? ['id' => $s->category->id, 'name' => $s->category->name, 'slug' => $s->category->slug] : null,
                'listings_count' => $counts['sub'][$s->id] ?? 0,
                'listings_url' => $this->listingsUrl($location, $s->slug, $area),
            ];
        };

        $meta = [
            'q' => $rawQ,
            'city' => $location ? ['id' => $location->id, 'name' => $location->name, 'slug' => $location->slug] : null,
            'area' => $area,
        ];

        if ($rawQ === '') {
            $ids = collect($counts['cat'])->sortDesc()->keys()->take(15)->all();
            $cats = Category::where('status', 1)->whereIn('id', $ids)->get(['id', 'name', 'slug'])
                ->sortBy(fn ($c) => array_search($c->id, $ids))->values();

            return response()->json(['success' => true] + $meta + [
                'categories' => $cats->map($catRow)->values(),
                'subcategories' => [],
                'listings' => [],
            ]);
        }

        // Same matching as the website: '&', spaces and '-' ignored on both sides.
        $norm = "REPLACE(REPLACE(REPLACE(LOWER(TRIM(name)), '&', ''), ' ', ''), '-', '')";
        $qn = preg_replace('/[^a-z0-9]/', '', strtolower($rawQ));

        if ($qn === '') {
            return response()->json(['success' => true] + $meta + ['categories' => [], 'subcategories' => [], 'listings' => []]);
        }

        $rank = fn ($name, $count) => [str_starts_with(preg_replace('/[^a-z0-9]/', '', strtolower($name)), $qn) ? 0 : 1, -$count, $name];

        $cats = Category::where('status', 1)->whereNotNull('slug')->where('slug', '!=', '')
            ->whereIn('id', array_keys($counts['cat']))
            ->whereRaw("{$norm} LIKE ?", ['%' . $qn . '%'])
            ->get(['id', 'name', 'slug'])
            ->sortBy(fn ($c) => $rank($c->name, $counts['cat'][$c->id] ?? 0))->take(5)->values();

        $subs = Subcategory::with('category:id,name,slug')->where('status', 1)->whereNotNull('slug')->where('slug', '!=', '')
            ->whereIn('id', array_keys($counts['sub']))
            ->whereRaw("{$norm} LIKE ?", ['%' . $qn . '%'])
            ->get(['id', 'name', 'slug', 'category_id'])
            ->sortBy(fn ($x) => $rank($x->name, $counts['sub'][$x->id] ?? 0))->take(10)->values();

        // Businesses by name from any city, as on the website; the chosen city's first.
        $lat = $request->filled('latitude') ? (float) $request->latitude : null;
        $lng = $request->filled('longitude') ? (float) $request->longitude : null;
        $favIds = $this->favoriteIds($request);

        $listings = Listing::notExpired()->where('status', 1)
            ->whereRaw("{$norm} LIKE ?", ['%' . $qn . '%'])
            ->with('category', 'location')->withCount('ratings')
            ->when($location, fn ($q) => $q->orderByRaw('CASE WHEN location_id = ? THEN 0 ELSE 1 END', [$location->id]))
            ->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')->orderBy('top_rank')->orderByDesc('id')
            ->limit(5)->get()
            ->map(function ($l) use ($favIds, $lat, $lng) {
                $card = $this->listingCard($l, $favIds);
                $card['type'] = 'listing';
                $card['distance_km'] = ($lat !== null && $lng !== null && is_numeric($l->latitude) && is_numeric($l->longitude))
                    ? round(6371 * acos(min(1, cos(deg2rad($lat)) * cos(deg2rad((float) $l->latitude)) * cos(deg2rad((float) $l->longitude) - deg2rad($lng)) + sin(deg2rad($lat)) * sin(deg2rad((float) $l->latitude)))), 1)
                    : null;
                $card['detail_url'] = url('/api/listings/' . $l->slug);

                return $card;
            });

        // Nothing matched at all: offer the closest names instead of an empty list.
        $didYouMean = [];
        if ($cats->isEmpty() && $subs->isEmpty() && $listings->isEmpty()) {
            $key = fn ($x) => preg_replace('/[^a-z0-9]/', ' ', strtolower(trim((string) $x)));
            $didYouMean = Subcategory::with('category:id,name,slug')->where('status', 1)->whereIn('id', array_keys($counts['sub']))
                ->get(['id', 'name', 'slug', 'category_id'])
                ->map(function ($x) use ($key, $rawQ) { similar_text($key($rawQ), $key($x->name), $pct); $x->score = $pct; return $x; })
                ->sortByDesc('score')->take(3)->values()->map($subRow);
        }

        return response()->json(['success' => true] + $meta + [
            'categories' => $cats->map($catRow)->values(),
            'subcategories' => $subs->map($subRow)->values(),
            'listings' => $listings->values(),
            'did_you_mean' => $didYouMean,
        ]);
    }

    /**
     * Subcategories that have live businesses in a city (optionally one area of
     * it), most-listed first — what the website offers for a chosen location.
     *
     *   GET /api/locations/jaipur/subcategories?area=jagatpura&category=packers-and-movers&q=home&limit=50
     */
    public function citySubcategories(Request $request, string $city)
    {
        $location = $this->resolveCity($city);

        if (!$location) {
            return response()->json(['success' => false, 'message' => 'City not found.'], 404);
        }

        $area = trim((string) $request->query('area', '')) ?: null;
        $q = strtolower(trim((string) $request->query('q', '')));
        $limit = min(max((int) $request->query('limit', 50), 1), 200);
        $counts = $this->taxonomyCounts($location, $area);

        $parent = null;
        if ($request->filled('category')) {
            $parent = Category::where('status', 1)->where('slug', $request->query('category'))->first();
            if (!$parent) {
                return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
            }
        }

        $subs = Subcategory::with('category:id,name,slug')->where('status', 1)
            ->whereNotNull('slug')->where('slug', '!=', '')
            ->whereIn('id', array_keys($counts['sub']))
            ->when($parent, fn ($w) => $w->where('category_id', $parent->id))
            ->when($q !== '', fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', ['%' . $q . '%']))
            ->get(['id', 'name', 'slug', 'category_id'])
            ->sortBy(fn ($x) => [-($counts['sub'][$x->id] ?? 0), $x->name])
            ->values();

        return response()->json([
            'success' => true,
            'city' => ['id' => $location->id, 'name' => $location->name, 'slug' => $location->slug],
            'area' => $area,
            'category' => $parent ? ['id' => $parent->id, 'name' => $parent->name, 'slug' => $parent->slug] : null,
            'total' => $subs->count(),
            'subcategories' => $subs->take($limit)->map(fn ($x) => [
                'id' => $x->id,
                'name' => $x->name,
                'slug' => $x->slug,
                'category' => $x->category ? ['id' => $x->category->id, 'name' => $x->category->name, 'slug' => $x->category->slug] : null,
                'listings_count' => $counts['sub'][$x->id] ?? 0,
                'listings_url' => $this->listingsUrl($location, $x->slug, $area),
            ])->values(),
        ]);
    }

    /** A city by slug, name or id; null when unknown or empty. */
    protected function resolveCity($value): ?Location
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Location::where('status', 1)
            ->where(function ($q) use ($value) {
                $q->where('slug', \Illuminate\Support\Str::slug($value))
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(str_replace('-', ' ', $value))]);
                if (ctype_digit($value)) {
                    $q->orWhere('id', (int) $value);
                }
            })
            ->first();
    }

    /**
     * Live listing counts per category and subcategory in a city/area (or site-
     * wide when no city). Cached briefly: it scans the city's listings.
     *
     * @return array{cat: array<int,int>, sub: array<int,int>}
     */
    protected function taxonomyCounts(?Location $location, ?string $area): array
    {
        $key = 'api:taxonomy-counts:v1:' . ($location->id ?? 'all') . ':' . md5(strtolower((string) $area));

        return \Illuminate\Support\Facades\Cache::remember($key, now()->addMinutes(10), function () use ($location, $area) {
            $cat = [];
            $sub = [];

            Listing::notExpired()->where('status', 1)
                ->when($location, fn ($q) => $q->where(fn ($qq) => $qq->where('location_id', $location->id)
                    ->orWhereRaw('LOWER(city) = ?', [strtolower($location->name)])))
                ->when($area, fn ($q) => $q->where(fn ($qq) => $qq->where('area', 'like', "%{$area}%")
                    ->orWhere('address', 'like', "%{$area}%")
                    ->orWhere('landmark', 'like', "%{$area}%")))
                ->select(['id', 'category_id', 'subcategory_ids'])
                ->chunkById(2000, function ($rows) use (&$cat, &$sub) {
                    foreach ($rows as $r) {
                        if ($r->category_id) {
                            $cat[$r->category_id] = ($cat[$r->category_id] ?? 0) + 1;
                        }
                        $ids = is_string($r->subcategory_ids) ? json_decode($r->subcategory_ids, true) : $r->subcategory_ids;
                        foreach (array_unique(array_map('intval', is_array($ids) ? $ids : [])) as $id) {
                            if ($id > 0) {
                                $sub[$id] = ($sub[$id] ?? 0) + 1;
                            }
                        }
                    }
                });

            return ['cat' => $cat, 'sub' => $sub];
        });
    }

    /** The app endpoint that lists one category/subcategory in a city (and area). */
    protected function listingsUrl(?Location $location, string $slug, ?string $area): ?string
    {
        if (!$location) {
            return null;
        }

        return url('/api/' . $location->slug . '/' . $slug . ($area ? '-in-' . \Illuminate\Support\Str::slug($area) : ''));
    }

    public function searchCategories(Request $request, $term = null)
    {
        // Support both /api/categories/search?q=term and /api/categories/search/term
        $q = $term ?? trim((string) $request->query('q'));

        return response()->json([
            'success' => true,
            'results' => $q === '' ? [] : Category::where('status', 1)
                ->where('name', 'like', "%{$q}%")->limit(10)->get(['id', 'name', 'slug'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]),
        ]);
    }

    /**
     * The areas inside one city — the list the website's search box offers
     * once a city is picked.
     *
     * There is no areas table: an area is whatever the businesses in that city
     * were filed under, so the list is built from the live listings and
     * ordered by how many there are. Pass ?q= to narrow it as the user types.
     */
    public function areas(Request $request, string $city)
    {
        $needle = str_replace('-', ' ', trim($city));

        $location = Location::where('status', 1)
            ->where(fn ($q) => $q->where('slug', trim($city))->orWhere('name', $needle))
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'City not found.',
            ], 404);
        }

        $q     = trim((string) $request->query('q'));
        $limit = min(max((int) $request->query('limit', 200), 1), 500);

        $rows = Listing::notExpired()->where('status', 1)
            ->where('location_id', $location->id)
            ->whereNotNull('area')->where('area', '<>', '')
            // Prefix matches are what someone typing expects to see.
            ->when($q !== '', fn ($w) => $w->where('area', 'like', $q . '%'))
            ->selectRaw('area, COUNT(*) AS listings, MIN(pincode) AS pincode')
            ->groupBy('area')
            ->orderByDesc('listings')
            ->orderBy('area')
            ->limit($limit)
            ->get();

        // Nothing starts with it — fall back to matching anywhere in the name.
        if ($rows->isEmpty() && $q !== '') {
            $rows = Listing::notExpired()->where('status', 1)
                ->where('location_id', $location->id)
                ->whereNotNull('area')->where('area', '<>', '')
                ->where('area', 'like', '%' . $q . '%')
                ->selectRaw('area, COUNT(*) AS listings, MIN(pincode) AS pincode')
                ->groupBy('area')
                ->orderByDesc('listings')
                ->orderBy('area')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'success' => true,
            'city'    => [
                'id'   => $location->id,
                'name' => $location->name,
                'slug' => $location->slug,
            ],
            'total'   => $rows->count(),
            'areas'   => $rows->map(fn ($r) => [
                'name'     => $r->area,
                'slug'     => \Illuminate\Support\Str::slug($r->area),
                'pincode'  => $r->pincode ?: null,
                'listings' => (int) $r->listings,
            ])->values(),
        ]);
    }

    public function searchLocations(Request $request, $term = null)
    {
        // Support both /api/locations/search?q=term and /api/locations/search/term
        $q = $term ?? trim((string) $request->query('q'));

        return response()->json([
            'success' => true,
            'results' => $q === '' ? [] : Location::where('status', 1)
                ->where('name', 'like', "%{$q}%")->limit(10)->get(['id', 'name', 'slug'])
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'slug' => $l->slug]),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Search
     | -------------------------------------------------------------------*/

    public function search(Request $request, $location = null, $term = null)
    {
        // Support multiple formats:
        // 1. /api/find?q=plumber&location=delhi (old query params)
        // 2. /api/find/plumber (path term only)
        // 3. /api/find/delhi/plumber (path location/term - slug or search term)

        // Handle the case where location is actually the term (when called as /api/find/{term})
        if ($term === null && $location !== null) {
            $term = $location;
            $location = null;
        }

        // Convert hyphens to spaces for better search
        $term     = str_replace('-', ' ', $term ?? trim((string) $request->query('q', '')));
        $location = str_replace('-', ' ', $location ?? trim((string) $request->query('location', '')));
        $lat      = $request->filled('latitude') ? (float) $request->latitude : null;
        $lng      = $request->filled('longitude') ? (float) $request->longitude : null;

        $listings = Listing::notExpired()->where('status', 1)
            ->with('category', 'location')->withCount('ratings');

        // Match by category slug first (for URL-friendly search like /api/find/delhi/packers-and-movers)
        if ($term !== '') {
            // First try to match by category slug (convert back to check slug)
            $termSlug = str_replace(' ', '-', strtolower($term));
            $categoryBySlug = Category::where('status', 1)->where('slug', $termSlug)->first();

            if ($categoryBySlug) {
                // If term matches a category slug, return listings from that category
                $listings->where('category_id', $categoryBySlug->id);
            } else {
                // Otherwise, do text search (name/category/subcategory match)
                $catIds = Category::where('status', 1)->where('name', 'like', "%{$term}%")->pluck('id')->toArray();
                $subIds = Subcategory::where('status', 1)->where('name', 'like', "%{$term}%")->pluck('id')->toArray();
                $subCatIds = Subcategory::where('status', 1)->where('name', 'like', "%{$term}%")->pluck('category_id')->toArray();

                $listings->where(function ($q) use ($term, $catIds, $subIds, $subCatIds) {
                    if ($catIds)    $q->orWhereIn('category_id', $catIds);
                    if ($subCatIds) $q->orWhereIn('category_id', $subCatIds);
                    if ($subIds) {
                        $q->orWhere(function ($sq) use ($subIds) {
                            foreach ($subIds as $id) $sq->orWhereJsonContains('subcategory_ids', (int) $id);
                        });
                    }
                    $q->orWhere('name', 'like', "%{$term}%");
                });
            }
        }

        // Distance (needs coords) or plain location-text match.
        if ($lat !== null && $lng !== null) {
            $listings->whereNotNull('latitude')->where('latitude', '!=', '')
                ->whereNotNull('longitude')->where('longitude', '!=', '')
                ->selectRaw(
                    'listings.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lng, $lat]
                )
                ->having('distance', '<=', 20);
        } elseif ($location !== '') {
            $listings->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                    ->orWhere('area', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%")
                    ->orWhereHas('location', fn ($lq) => $lq->where('name', 'like', "%{$location}%"));
            });
        }

        if ($request->filled('rating')) {
            $listings->where('review', '>=', $request->rating);
        }

        // Clone query for filter attempt
        $filteredListings = clone $listings;

        switch ($request->query('filter')) {
            case 'verified':  $filteredListings->where('verified', 1); break;
            case 'trust':     $filteredListings->where('trust', 1); break;
            case 'top_rated': $filteredListings->where('review', '>=', 4); break;
        }

        // Use filtered results if they exist, otherwise use unfiltered
        if ($request->filled('filter') && $filteredListings->count() > 0) {
            $listings = $filteredListings;
        } elseif (!$request->filled('filter')) {
            $listings = $filteredListings;
        }

        switch ($request->query('sort')) {
            case 'rating':     $listings->orderByDesc('review'); break;
            case 'low_rating': $listings->orderBy('review'); break;
            case 'popular':    $listings->orderByDesc('view'); break;
            default:
                if ($lat !== null && $lng !== null) {
                    $listings->orderBy('distance');
                } else {
                    $listings->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('top_rank')->orderByDesc('id');
                }
        }

        $page   = $listings->paginate(15)->withQueryString();
        $favIds = $this->favoriteIds($request);

        return response()->json([
            'success'  => true,
            'listings' => collect($page->items())->map(fn ($l) => $this->listingCard($l, $favIds))->values(),
            'meta'     => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Listing detail + reviews
     | -------------------------------------------------------------------*/

    public function detail(Request $request, string $slug)
    {
        $listing = Listing::notExpired()->where('status', 1)->where('slug', $slug)
            ->with('category', 'location', 'ratings.user', 'ratings.comments.user')
            ->withCount('ratings')
            ->firstOrFail();

        $listing->increment('view');

        $favIds = $this->favoriteIds($request);
        $myId   = optional($request->user())->id;

        return response()->json([
            'success' => true,
            'listing' => array_merge($this->listingCard($listing, $favIds), [
                'details'        => $listing->details,
                'pincode'        => $listing->pincode,
                'address'        => $this->composeAddress($listing),
                'latitude'       => $listing->latitude,
                'longitude'      => $listing->longitude,
                'verified'       => (bool) $listing->verified,
                'trust'          => (bool) $listing->trust,
                'established_year'=> $listing->established_year,
                'views'          => (int) $listing->view,
                'photos'         => collect($listing->photos ?? [])
                    ->map(fn ($p) => asset('assets/images/listing/photos/' . $p))->values(),
                'contact'        => [
                    'person'    => $listing->contact_person,
                    'title'     => $listing->contact_title,
                    'mobiles'   => array_values($listing->mobile_numbers ?? []),
                    'whatsapps' => array_values($listing->whatsapp_numbers ?? []),
                    'landlines' => array_values($listing->landline_numbers ?? []),
                    'emails'    => array_values($listing->extra_emails ?? []),
                ],
                'timings' => [
                    'days'       => array_values($listing->business_days ?? []),
                    'open_time'  => array_values($listing->open_time ?? []),
                    'close_time' => array_values($listing->close_time ?? []),
                ],
                'websites'     => array_values($listing->extra_websites ?? []),
                'social_links' => $listing->social_links ?? [],
                'faqs'         => $this->faqPayload($listing),
                'reviews'      => $listing->ratings->map(fn ($r) => $this->reviewPayload($r, $myId))->values(),
            ]),
        ]);
    }

    /**
     * The listing's FAQs as question/answer pairs.
     *
     * They are stored as two parallel arrays — faq.question[] and faq.answer[]
     * — which is awkward for an app to line up, so they are zipped here the
     * same way the website pairs them. A question with no answer yet is still
     * returned, with an empty answer; a blank question is dropped.
     */
    private function faqPayload(Listing $listing): array
    {
        $faq = is_array($listing->faq) ? $listing->faq : (json_decode((string) $listing->faq, true) ?? []);

        $answers = array_values($faq['answer'] ?? []);

        return collect($faq['question'] ?? [])
            ->values()
            ->map(fn ($question, $i) => [
                'question' => is_string($question) ? trim($question) : '',
                'answer'   => isset($answers[$i]) && is_string($answers[$i]) ? trim($answers[$i]) : '',
            ])
            ->filter(fn ($row) => $row['question'] !== '')
            ->values()
            ->all();
    }

    /** Submit (or update) a review — signed-in only, no OTP needed. */
    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'ratings' => 'required|integer|min:1|max:5',
            'review'  => 'required|string|max:1000',
        ]);

        $listing = Listing::where('status', 1)->findOrFail($id);
        $user    = $request->user();

        if ((int) $listing->user_id === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot review your own business.',
            ], 422);
        }

        // One review per user per listing — update the existing one if any.
        $rating = Rating::firstOrNew(['listing_id' => $listing->id, 'user_id' => $user->id]);
        $rating->name        = $user->fullname() ?: ($user->username ?: 'User');
        $rating->email       = $user->email;
        $rating->ratings     = $request->ratings;
        $rating->review      = $request->review;
        $rating->is_verified = 1;
        $rating->save();

        // Keep the listing's cached average in step, same as the website.
        $listing->review = Rating::where('listing_id', $listing->id)->where('is_verified', 1)->avg('ratings') ?: 0;
        $listing->save();

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'review'  => $this->reviewPayload($rating->fresh()->load('comments.user'), $user->id),
        ]);
    }

    public function likeReview(Request $request, $ratingId)
    {
        $rating = Rating::findOrFail($ratingId);
        $like   = RatingLike::where('rating_id', $rating->id)->where('user_id', $request->user()->id)->first();

        if ($like) {
            $like->delete();
            if ($rating->likes_count > 0) $rating->decrement('likes_count');
            $liked = false;
        } else {
            RatingLike::create(['rating_id' => $rating->id, 'user_id' => $request->user()->id]);
            $rating->increment('likes_count');
            $liked = true;
        }

        return response()->json(['success' => true, 'liked' => $liked, 'count' => $rating->fresh()->likes_count]);
    }

    public function commentReview(Request $request, $ratingId)
    {
        $request->validate(['comment' => 'required|string|max:1000']);

        $rating  = Rating::findOrFail($ratingId);
        $comment = RatingComment::create([
            'rating_id' => $rating->id,
            'user_id'   => $request->user()->id,
            'comment'   => $request->comment,
        ]);
        $rating->increment('comments_count');

        return response()->json([
            'success'    => true,
            'id'         => $comment->id,
            'count'      => $rating->fresh()->comments_count,
            'name'       => $request->user()->fullname() ?: 'User',
            'comment'    => $comment->comment,
            'can_delete' => true,
        ]);
    }

    public function deleteComment(Request $request, $id)
    {
        $comment = RatingComment::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $rating  = Rating::find($comment->rating_id);
        $comment->delete();
        if ($rating && $rating->comments_count > 0) $rating->decrement('comments_count');

        return response()->json([
            'success'   => true,
            'rating_id' => $rating?->id,
            'count'     => $rating ? $rating->fresh()->comments_count : 0,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Enquiry & click tracking
     | -------------------------------------------------------------------*/

    /** Direct "Enquiry Now" to one business. */
    public function enquiry(Request $request, $id)
    {
        $listing = Listing::where('status', 1)->findOrFail($id);
        $user    = $request->user();

        $data = $request->validate([
            'name'    => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        Enquiry::create([
            'type'        => 'enquiry',
            'listing_id'  => $listing->id,
            'category_id' => $listing->category_id,
            'location_id' => $listing->location_id,
            'name'        => $data['name']  ?? $user->fullname(),
            'phone'       => $data['phone'] ?? $user->mobile,
            'email'       => $data['email'] ?? $user->email,
            'message'     => $data['message'] ?? null,
            'is_read'     => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Your enquiry has been sent.']);
    }

    /** Records a Call / WhatsApp / Direction / Share tap for analytics. */
    public function trackClick(Request $request, $id)
    {
        $request->validate(['type' => 'required|string|max:20']);

        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['success' => true]);
        }

        $click = new \App\Models\ListingClick();
        $click->listing_id   = $listing->id;
        $click->listing_name = $listing->name;
        $click->category_id  = $listing->category_id;
        $click->location_id  = $listing->location_id;
        $click->type = [
            'call' => 'call', 'landline' => 'call',
            'whatsapp' => 'whatsapp', 'enquiry' => 'enquiry',
        ][$request->type] ?? 'call';
        $click->ip_address = $request->ip();
        $click->user_agent = $request->userAgent();
        $click->save();

        return response()->json(['success' => true]);
    }

    /* ---------------------------------------------------------------------
     | Payload builders
     | -------------------------------------------------------------------*/

    protected function categoryPayload($c): array
    {
        return [
            'id'    => $c->id,
            'name'  => $c->name,
            'slug'  => $c->slug,
            'icon'  => $c->icon,
            'image' => $c->image ? asset('assets/images/category/' . $c->image) : null,
        ];
    }

    protected function listingCard(Listing $l, array $favIds): array
    {
        return [
            'id'           => $l->id,
            'name'         => $l->name,
            'slug'         => $l->slug,
            'category'     => optional($l->category)->name,
            'category_slug'=> optional($l->category)->slug,
            'area'         => $l->area ?: optional($l->location)->name,
            'city'         => $l->city,
            'thumb'        => $l->thumb ? loadImage('assets/images/listing/thumb/' . $l->thumb) : loadImage('', false),
            'avg_rating'   => round((float) $l->review, 1),
            'review_count' => (int) ($l->ratings_count ?? $l->ratings()->count()),
            'verified'     => (bool) $l->verified,
            'trust'        => (bool) $l->trust,
            'is_favorited' => in_array($l->id, $favIds, true),
        ];
    }

    protected function reviewPayload(Rating $r, $myId): array
    {
        return [
            'id'             => $r->id,
            'name'           => $r->name,
            'rating'         => (int) $r->ratings,
            'review'         => $r->review,
            'likes_count'    => (int) $r->likes_count,
            'comments_count' => (int) $r->comments_count,
            'liked_by_me'    => $myId && ($r->relationLoaded('likes')
                ? $r->likes->contains('user_id', $myId)
                : RatingLike::where('rating_id', $r->id)->where('user_id', $myId)->exists()),
            'created_at'     => optional($r->created_at)->toIso8601String(),
            'comments'       => $r->relationLoaded('comments')
                ? $r->comments->map(fn ($c) => [
                    'id'         => $c->id,
                    'name'       => optional($c->user)->first_name ? trim($c->user->first_name . ' ' . $c->user->last_name) : 'User',
                    'comment'    => $c->comment,
                    'can_delete' => $myId && (int) $c->user_id === (int) $myId,
                ])->values()
                : [],
        ];
    }

    protected function composeAddress(Listing $l): string
    {
        return collect([
            $l->plot_no, $l->building_name, $l->street_name,
            $l->landmark, $l->area, $l->city, $l->state, $l->pincode,
        ])->filter()->implode(', ');
    }

    /** Listing ids the current user has favourited, for is_favorited flags. */
    protected function favoriteIds(Request $request): array
    {
        // Public screens are reachable without a token; a guest has favourited
        // nothing, so every card comes back with is_favorited false.
        if (!$request->user()) {
            return [];
        }

        return $request->user()->favoriteListings()->pluck('listing_id')->toArray();
    }
}
