<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileHandler;
use App\Models\Banner;
use App\Models\Category;
use App\Models\ExtraImage;
use App\Models\Listing;
use App\Http\Libs\SendMail;
use App\Events\AdminNotification;
use App\Models\Location;
use App\Models\Subcategory;
use App\Models\Rating;
use App\Models\Setting;
use App\Models\ServiceKeyword;
use App\Http\Controllers\SiteController;
use Illuminate\Http\Request;
use HTMLPurifier;

class ListingController extends Controller
{
    use FileHandler;

	protected $setting;
	protected $theme;

	public function __construct(){
		$this->setting = Setting::first();
		$this->theme = 'themes.'.$this->setting->theme.'.';
	}

    public function index(){
        $listings = Listing::orderBy('id','desc')->where('user_id',auth()->user()->id)->with('location','category')->paginate(20);
    	return view($this->theme.'user.listing.index',compact('listings'));
    }

    public function create()
{
    $categories = Category::where('status', 1)->get();
    $locations  = Location::where('status', 1)->get();

    $subcategories = Subcategory::where('status', 1)->get();

    return view($this->theme.'user.listing.create',
        compact('categories','locations','subcategories')
    );
}

public function store(Request $request)
{
    $rules = array_merge([
        'image'                    => 'nullable|image|mimes:jpg,jpeg,png,webp',
        'images'                   => 'nullable|array',
        'images.*'                 => 'nullable|image|mimes:jpg,jpeg,png,webp',
        'features'                 => 'nullable|array',
        'title'                    => 'nullable|array',
        'content'                  => 'nullable|array',
        'question'                 => 'nullable|array',
        'answer'                   => 'nullable|array',

        'name'                     => 'required|string|max:255',
        'mobile'                   => 'nullable|string|max:20',
        'email'                    => 'nullable|email|max:255',

        'pincode'                  => 'nullable|string|max:10',
        'plot_no'                  => 'nullable|string|max:255',
        'building_name'            => 'nullable|string|max:255',
        'street_name'              => 'nullable|string|max:255',
        'landmark'                 => 'nullable|string|max:255',
        'area'                     => 'nullable|string|max:255',
        'city'                     => 'nullable|string|max:255',
        'state'                    => 'nullable|string|max:255',
        'latitude'                 => 'nullable|numeric',
        'longitude'                => 'nullable|numeric',

        'contact_title'            => 'nullable|string|max:50',
        'contact_person'           => 'nullable|string|max:255',

        'mobile_country_code'      => 'nullable|array',
        'mobile_country_code.*'    => 'nullable|string|max:10',
        'mobile_numbers'           => 'nullable|array',
        'mobile_numbers.*'         => 'nullable|string|max:20',

        'whatsapp_country_code'    => 'nullable|array',
        'whatsapp_country_code.*'  => 'nullable|string|max:10',
        'whatsapp_numbers'         => 'nullable|array',
        'whatsapp_numbers.*'       => 'nullable|string|max:20',

        'landline_country_code'    => 'nullable|array',
        'landline_country_code.*'  => 'nullable|string|max:10',
        'landline_numbers'         => 'nullable|array',
        'landline_numbers.*'       => 'nullable|string|max:20',

        'extra_emails'             => 'nullable|array',
        'extra_emails.*'           => 'nullable|email|max:255',

        'business_days'            => 'nullable|array',
        'business_days.*'          => 'nullable|string|max:20',
'open_24_hours'            => 'nullable|boolean',

        'open_time'                => 'nullable|array',
        'open_time.*'              => 'nullable|string|max:50',

        'close_time'               => 'nullable|array',
        'close_time.*'             => 'nullable|string|max:50',

        'category_id'              => 'nullable|exists:categories,id',
        'subcategory_ids'          => 'nullable|array',
        'subcategory_ids.*'        => 'nullable|exists:subcategories,id',
 'established_year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
        'thumb'                    => 'nullable|image|mimes:jpg,jpeg,png,webp',
        'photos'                   => 'nullable|array',
        'photos.*'                 => 'nullable|image|mimes:jpg,jpeg,png,webp',
    ]);

    $request->validate($rules);

    $user = auth()->user();

    $features            = array_values(array_filter($request->features ?? []));
    $images              = $request->images ?? [];
    
    $titles              = array_values(array_filter($request->title ?? []));
    $contents            = array_values(array_filter($request->content ?? []));
$questions = collect($request->input('question', []))
    ->map(fn ($question) => trim((string) $question))
    ->filter(fn ($question) => $question !== '')
    ->values()
    ->all();

$rawAnswers = collect($request->input('answer', []))
    ->map(fn ($answer) => trim((string) $answer))
    ->values()
    ->all();

$answers = [];

foreach ($questions as $index => $question) {
    $answers[] = $rawAnswers[$index] ?? '';
}
    $mobileCountryCode   = array_values(array_filter($request->mobile_country_code ?? []));
    $mobileNumbers       = array_values(array_filter($request->mobile_numbers ?? []));
    $whatsappCountryCode = array_values(array_filter($request->whatsapp_country_code ?? []));
    $whatsappNumbers     = array_values(array_filter($request->whatsapp_numbers ?? []));
    $landlineCountryCode = array_values(array_filter($request->landline_country_code ?? []));
    $landlineNumbers     = array_values(array_filter($request->landline_numbers ?? []));
    $extraEmails         = array_values(array_filter($request->extra_emails ?? []));
   $businessDays = array_values(
    array_filter($request->business_days ?? [])
);

$isOpen24Hours = $request->boolean('open_24_hours');

if ($isOpen24Hours) {

    // Open 24 Hours checkbox checked hai
    $openTime = ['12:00 AM'];
    $closeTime = ['11:59 PM'];

} else {

    // Normal selected timing
    $openTime = array_values(
        array_filter($request->open_time ?? [])
    );

    $closeTime = array_values(
        array_filter($request->close_time ?? [])
    );
}
    $subcategoryIds      = array_values(array_filter($request->subcategory_ids ?? []));

    $thumb = null;
    if ($request->hasFile('thumb')) {
        $file = $request->file('thumb');
        $thumb = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('assets/images/listing/thumb'), $thumb);
    }

    $uploadedPhotos = [];
    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            if ($photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->move(public_path('assets/images/listing/photos'), $filename);
                $uploadedPhotos[] = $filename;
            }
        }
    }

    $businessCategories = [];
    if (!empty($subcategoryIds)) {
        $businessCategories = Subcategory::whereIn('id', $subcategoryIds)->pluck('name')->toArray();
    }

  

    $listing = new Listing();
$listing->location_id = $request->location_id;
    $listing->name        = $request->name;
    $listing->slug        = \Str::slug($request->name) . '-' . time();
    $listing->user_id     = $user->id;
    $listing->category_id = $request->category_id;
    // $listing->details is rendered unescaped ({!! !!}) on the public listing
    // page — purify it so a business owner can't stored-XSS every visitor
    // via the listing description.
    $listing->details     = $request->filled('details') ? (new HTMLPurifier())->purify($request->details) : null;
   $listing->meta_title = $request->meta_title ?: null;
$listing->meta_keywords = $request->meta_keywords ?: null;
$listing->meta_description = $request->meta_description ?: null;
    $listing->plot_no       = $request->plot_no;
$listing->building_name = $request->building_name;
$listing->street_name   = $request->street_name;
$listing->landmark      = $request->landmark;
    $listing->thumb       = $thumb;
    $listing->is_featured = $request->featured ?? 0;
    $listing->status      = 0;
    $listing->established_year = $request->established_year;

    $listing->features = $features;

    $listing->contact = [
        'title'   => $titles,
        'content' => $contents,
    ];

    $listing->faq = [
        'question' => $questions,
        'answer'   => $answers,
    ];

    $listing->contact_title         = $request->contact_title;
    $listing->contact_person        = $request->contact_person;
    $listing->mobile_country_code   = $mobileCountryCode;
    $listing->mobile_numbers        = $mobileNumbers;
    $listing->whatsapp_country_code = $whatsappCountryCode;
    $listing->whatsapp_numbers      = $whatsappNumbers;
    $listing->subcategory_ids       = $subcategoryIds;
    $listing->landline_country_code = $landlineCountryCode;
    $listing->landline_numbers      = $landlineNumbers;
    $listing->extra_emails          = $extraEmails;
    $listing->business_days         = $businessDays;
    $listing->open_time             = $openTime;
    $listing->close_time            = $closeTime;
    $listing->business_categories   = $businessCategories;
    $listing->photos                = $uploadedPhotos;

    if (\Schema::hasColumn('listings', 'mobile')) {
        $listing->mobile = $request->mobile;
    }

    if (\Schema::hasColumn('listings', 'email')) {
        $listing->email = $request->email;
    }

    if (\Schema::hasColumn('listings', 'pincode')) {
        $listing->pincode = $request->pincode;
    }

    if (\Schema::hasColumn('listings', 'area')) {
        $listing->area = $request->area;
    }

    if (\Schema::hasColumn('listings', 'city')) {
        $listing->city = $request->city;
    }

    if (\Schema::hasColumn('listings', 'state')) {
        $listing->state = $request->state;
    }

    if (\Schema::hasColumn('listings', 'latitude')) {
        $listing->latitude = $request->latitude;
    }

    if (\Schema::hasColumn('listings', 'longitude')) {
        $listing->longitude = $request->longitude;
    }

    $listing->verified = 0;
    $listing->trust    = 0;

    $listing->save();

$count = Listing::where('status', 0)->count();

event(new AdminNotification('listing', $count));

if (!empty($images)) {
    $this->addExtraImage($request, $listing);
}

\App\Support\Tracking\Tracker::conversion($request, 'listing_created', ['listing_id' => $listing->id]);

return redirect()
    ->route('home')
    ->withAlert(['success', 'Listing added successfully']);
}

public function nearbyLocationSuggestions(Request $request)
{
    $search = trim($request->q ?? '');
    $latitude = $request->latitude;
    $longitude = $request->longitude;

    $suggestions = collect();

    /*
    |--------------------------------------------------------------------------
    | Search city/location from locations table
    |--------------------------------------------------------------------------
    */
    $matchedLocations = Location::where('status', 1)
        ->when($search !== '', function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'LIKE', $search . '%')
                   ->orWhere('slug', 'LIKE', \Str::slug($search) . '%');
            });
        })
        ->when($search === '', function ($q) {
            $q->orderBy('id', 'desc');
        })
        ->select('id', 'name', 'latitude', 'longitude')
        ->limit(5)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Main city first
    |--------------------------------------------------------------------------
    */
    foreach ($matchedLocations as $location) {
        $suggestions->push([
            'id'          => $location->id,
            'location_id' => $location->id,
            'name'        => trim($location->name),
            'parent'      => '',
            'latitude'    => $location->latitude,
            'longitude'   => $location->longitude,
            'type'        => 'location',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Areas according to searched city
    |--------------------------------------------------------------------------
    */
    $cityNames = $matchedLocations->pluck('name')->filter()->values();

    $areaQuery = Listing::where('listings.status', 1)
        ->whereNotNull('listings.area')
        ->where('listings.area', '!=', '')
        ->leftJoin('locations', 'locations.id', '=', 'listings.location_id');

    if ($search !== '') {
        if ($cityNames->count() > 0) {
            $areaQuery->where(function ($q) use ($cityNames) {
                foreach ($cityNames as $cityName) {
                    $q->orWhere('locations.name', 'LIKE', '%' . $cityName . '%');
                }
            });
        } else {
            $areaQuery->where(function ($q) use ($search) {
                $q->where('locations.name', 'LIKE', '%' . $search . '%')
                  ->orWhere('listings.city', 'LIKE', '%' . $search . '%')
                  ->orWhere('listings.area', 'LIKE', $search . '%');
            });
        }
    } else {
        if (!empty($latitude) && !empty($longitude)) {
            $areaQuery->whereNotNull('listings.latitude')
                ->whereNotNull('listings.longitude')
                ->selectRaw("
                    (
                        6371 * acos(
                            LEAST(1, GREATEST(-1,
                                cos(radians(?))
                                * cos(radians(listings.latitude))
                                * cos(radians(listings.longitude) - radians(?))
                                + sin(radians(?))
                                * sin(radians(listings.latitude))
                            ))
                        )
                    ) AS distance
                ", [$latitude, $longitude, $latitude])
                ->orderBy('distance', 'asc');
        } else {
            $areaQuery->orderBy('listings.id', 'desc');
        }
    }

    // addSelect, not select: the lat/lng branch above adds a computed
    // `distance` column and orders by it, and a plain select() here would
    // replace that column list — leaving ORDER BY distance with no such
    // column and the endpoint throwing 1054.
    $areaQuery->addSelect(
        'listings.area',
        'listings.city',
        'listings.location_id',
        'listings.latitude',
        'listings.longitude',
        'locations.name as main_city'
    );

    $areas = $areaQuery
        ->limit(25)
        ->get()
        ->map(function ($item) {
            return [
                'id'          => $item->location_id,
                'location_id' => $item->location_id,
                'name'        => trim($item->area),
                'parent'      => trim($item->main_city ?? $item->city ?? ''),
                'latitude'    => $item->latitude,
                'longitude'   => $item->longitude,
                'type'        => 'area',
            ];
        });

    $suggestions = $suggestions
        ->merge($areas)
        ->filter(fn ($item) => is_array($item) && !empty($item['name']))
        ->unique(fn ($item) => strtolower($item['name'] . '|' . ($item['parent'] ?? '')))
        ->values()
        ->take(30);

    return response()->json($suggestions);
}

public function categorySuggestions(Request $request)
{
    $rawQ = (string) ($request->q ?? '');
    $q = strtolower(trim($rawQ));
    $exactOnly = (int) $request->exact === 1;
    $withListings = (int) ($request->with_listings ?? 1); // 👈 ये important है

    $locationName = trim($request->location_name ?? '');
    $locationId = $request->location_id;

    // ---- Loc ation ID fetch if not provided ----
    if (empty($locationId) && $locationName !== '') {
        $tempLocationName = $locationName;

        if (str_contains($tempLocationName, ',')) {
            $parts = explode(',', $tempLocationName);
            $tempLocationName = trim(end($parts));
        }

        $location = Location::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($tempLocationName)])
            ->first();

        if ($location) {
            $locationId = $location->id;
        }
    }

    // ---- Empty Search - Show All Categories ----
   // ---- Empty Search - Show Trending Categories ----
if ($q === '') {

    $categories = Category::query()
        ->select('categories.id', 'categories.name', 'categories.slug')
        ->leftJoin('listings', function ($join) use ($locationId) {
            $join->on('listings.category_id', '=', 'categories.id')
                ->where('listings.status', 1);

            if (!empty($locationId)) {
                $join->where('listings.location_id', $locationId);
            }
        })
        ->where('categories.status', 1)
        ->whereNotNull('categories.slug')
        ->where('categories.slug', '!=', '')
        ->groupBy('categories.id', 'categories.name', 'categories.slug')
        ->orderByRaw('COUNT(listings.id) DESC')
        ->limit(15)
        ->get();

    return response()->json(
        $categories->map(function ($item) {
            return [
                'name' => $item->name,
                'type' => 'Category',
                'url'  => route('listings.category', ['slug' => $item->slug]),
            ];
        })
    );
}

    // ---- Get Location City/Area ----
    $city = '';
    $area = '';

    if ($locationName !== '') {
        if (str_contains($locationName, ',')) {
            $parts = explode(',', $locationName);
            $area = trim($parts[0] ?? '');
            $city = trim($parts[1] ?? '');
        } else {
            $city = trim($locationName);
        }
    }

    // ---- Base Listing Query ----
    // Note: We don't filter by location for listing searches,
    // so users can find listings from any location by name
    $listingQuery = Listing::where('status', 1);

    // ---- Get Used Category & Subcategory IDs ----
    $listings = $listingQuery
        ->select('category_id', 'subcategory_ids')
        ->get();

    $usedCategoryIds = $listings
        ->pluck('category_id')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    $usedSubcategoryIds = $listings
        ->flatMap(function ($listing) {
            $ids = $listing->subcategory_ids;
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            return is_array($ids) ? $ids : [];
        })
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    // ---- No Results Found ----
    if ($locationName !== '' && empty($usedCategoryIds) && empty($usedSubcategoryIds)) {
        return response()->json([]);
    }

    // ---- Push Helper ----
    $data = [];
    $pushResult = function ($items, $type) use (&$data) {
        foreach ($items as $item) {
            $data[] = [
                'name' => $item->name,
                'type' => $type,
                'url'  => $type === 'Category'
                    ? route('listings.category', ['slug' => $item->slug])
                    : route('listings.subcategory', ['slug' => $item->slug]),
            ];
        }
    };

    if (($locationName !== '' || !empty($locationId)) && empty($usedCategoryIds) && empty($usedSubcategoryIds)) {
        return response()->json([]);
    }

    // ---- Base Queries ----
    $baseCategoryQuery = Category::where('status', 1)
        ->whereNotNull('slug')
        ->where('slug', '!=', '');

    $baseSubcategoryQuery = Subcategory::where('status', 1)
        ->whereNotNull('slug')
        ->where('slug', '!=', '');

    if (!empty($locationId) || $area !== '') {
        $baseCategoryQuery->whereIn('id', $usedCategoryIds);
        $baseSubcategoryQuery->whereIn('id', $usedSubcategoryIds);
    }

    // ---- Exact Match ----
    if ($exactOnly) {
        $categories = (clone $baseCategoryQuery)
            ->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [$q])
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [$q . ' %'])
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [$q . '-%'])
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [$q . '&%']);
            })
            ->get();

        $subcategories = (clone $baseSubcategoryQuery)
            ->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [$q])
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [$q . ' %'])
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [$q . '-%'])
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [$q . '&%']);
            })
            ->get();

        $pushResult($categories, 'Category');
        $pushResult($subcategories, 'Subcategory');

        return response()->json(
            collect($data)
                ->unique('name')
                ->values()
                ->take(10)
        );
    }

    // ---- Normal Search ----
    // Matches with '&', spaces, and '-' stripped from BOTH sides, so
    // "s s ass" still finds "S S & Associates" (the '&' no longer breaks
    // the substring match), and category/subcategory names are matched
    // anywhere in the name (not just as a prefix) so "ca" finds
    // "Chartered Accountant (CA)" via the "(CA)" abbreviation instead of
    // requiring the name to start with "ca".
    $normalizedNameSql = "REPLACE(REPLACE(REPLACE(LOWER(TRIM(name)), '&', ''), ' ', ''), '-', '')";
    $qNormalized = preg_replace('/[^a-z0-9]/', '', $q);
    $qLikeNormalized = '%' . $qNormalized . '%';

    $categories = (clone $baseCategoryQuery)
        ->whereRaw("{$normalizedNameSql} LIKE ?", [$qLikeNormalized])
        ->get();

    $subcategories = (clone $baseSubcategoryQuery)
        ->whereRaw("{$normalizedNameSql} LIKE ?", [$qLikeNormalized])
        ->get();

    $pushResult($categories, 'Category');
    $pushResult($subcategories, 'Subcategory');

    // ============================================================
    // 👇 FIX: LISTINGS WITH RATING, REVIEWS, DISTANCE, IMAGE
    // ============================================================

    // ---- Get Listings with all details ----
    if ($withListings) {
        $matchedListings = (clone $listingQuery)
            ->whereRaw("{$normalizedNameSql} LIKE ?", [$qLikeNormalized])
            ->with(['category', 'location', 'ratings']) // 👈 Eager loading
            ->select(
                'id',
                'name',
                'slug',
                'category_id',
                'location_id',
                'thumb',
                'area',
                'latitude',
                'longitude'
            )
            ->take(5)
            ->get();

        foreach ($matchedListings as $listing) {
            $categorySlug = optional($listing->category)->slug;

            // ---- Calculate Distance (if location available) ----
            $distance = '';
            $userLat = $request->latitude ?? session('user_latitude') ?? null;
            $userLng = $request->longitude ?? session('user_longitude') ?? null;

            if ($userLat && $userLng && $listing->latitude && $listing->longitude) {
                $distance = $this->calculateDistance(
                    $userLat,
                    $userLng,
                    $listing->latitude,
                    $listing->longitude
                );
            }

            // ---- Get Rating & Reviews ----
            $avgRating = \App\Models\Rating::where('listing_id', $listing->id)
                ->where('is_verified', 1)
                ->avg('ratings');

            $ratingCount = \App\Models\Rating::where('listing_id', $listing->id)
                ->where('is_verified', 1)
                ->count();

            $avgRating = $avgRating ? (float) number_format($avgRating, 1) : 0;

            // ---- Get Area Name ----
            $areaName = $listing->area ?? optional($listing->location)->name ?? '';

            // ---- Image ----
            $image = !empty($listing->thumb)
                ? asset('assets/images/listing/thumb/' . $listing->thumb)
                : asset('assets/images/default.jpg');

            $data[] = [
                'name'     => $listing->name,
                'type'     => 'Listing',
                'url'      => $categorySlug
                    ? route('listings.details', [
                        'category' => $categorySlug,
                        'slug'     => $listing->slug
                    ])
                    : url('/listings/' . $listing->slug),
                'image'    => $image,
                'area'     => $areaName,
                'rating'   => $avgRating,
                'reviews'  => $ratingCount,
                'distance' => $distance,
            ];
        }
    }

    // ---- Never show "No results found": if nothing matched even the
    // normalized substring search above (e.g. a typo, or a term that
    // isn't a substring of anything), fall back to whichever category,
    // subcategory, or listing name is textually closest to the query and
    // surface that instead of an empty list. ----
    if (empty($data)) {
        $similarityKey = function ($str) {
            return preg_replace('/[^a-z0-9]/', ' ', strtolower(trim((string) $str)));
        };

        $qForSimilarity = $similarityKey($rawQ);
        $candidates = [];

        foreach ((clone $baseCategoryQuery)->limit(200)->get() as $item) {
            $candidates[] = ['name' => $item->name, 'type' => 'Category', 'slug' => $item->slug];
        }

        foreach ((clone $baseSubcategoryQuery)->limit(200)->get() as $item) {
            $candidates[] = ['name' => $item->name, 'type' => 'Subcategory', 'slug' => $item->slug];
        }

        if ($withListings) {
            $fallbackListings = (clone $listingQuery)
                ->with('category')
                ->select('id', 'name', 'slug', 'category_id')
                ->limit(200)
                ->get();

            foreach ($fallbackListings as $listing) {
                $candidates[] = ['name' => $listing->name, 'type' => 'ListingCandidate', 'listing' => $listing];
            }
        }

        $closest = collect($candidates)
            ->map(function ($candidate) use ($similarityKey, $qForSimilarity) {
                similar_text($qForSimilarity, $similarityKey($candidate['name']), $percent);
                $candidate['score'] = $percent;

                return $candidate;
            })
            ->sortByDesc('score')
            ->take(3);

        foreach ($closest as $candidate) {
            if ($candidate['type'] === 'ListingCandidate') {
                $listing = $candidate['listing'];
                $categorySlug = optional($listing->category)->slug;

                $data[] = [
                    'name' => $listing->name,
                    'type' => 'Listing',
                    'url'  => $categorySlug
                        ? route('listings.details', ['category' => $categorySlug, 'slug' => $listing->slug])
                        : url('/listings/' . $listing->slug),
                ];
            } else {
                $data[] = [
                    'name' => $candidate['name'],
                    'type' => $candidate['type'],
                    'url'  => $candidate['type'] === 'Category'
                        ? route('listings.category', ['slug' => $candidate['slug']])
                        : route('listings.subcategory', ['slug' => $candidate['slug']]),
                ];
            }
        }
    }

    // ---- Sort Results: Exact match first, then Categories, then Listings ----
    return response()->json(
        collect($data)
            ->unique('name')
            ->sortBy(function ($item) use ($q) {
                // Exact match of Category/Subcategory
                if ($item['type'] !== 'Listing' && strtolower(trim($item['name'])) === $q) {
                    return 0;
                }
                // Categories/Subcategories
                if ($item['type'] !== 'Listing') {
                    return 1;
                }
                // Listings
                return 2;
            })
            ->values()
            ->take(15)
    );
}

// ============================================================
// 👇 HELPER: Distance Calculation
// ============================================================

private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c;

    if ($distance < 1) {
        return number_format($distance * 1000, 0) . ' m';
    }

    return number_format($distance, 1) . ' km';
}
public function updatesection(Request $request, $id)
{
    $listing = Listing::findOrFail($id);

    if (!auth()->check()) {
        abort(403);
    }

    
    $website = trim($request->website);

    if (auth()->id() == $listing->user_id) {

        $listing->website = $website;
        $listing->pending_website = null;
        $listing->website_approval_status = null;

        $message = 'Website updated successfully.';

    } else {

        $listing->pending_website = $website;
        $listing->website_approval_status = 'pending';

        $message = 'Website submitted successfully. Waiting for admin approval.';
    }

    $listing->save();

    return back()->withAlert(['success', $message]);
}
    public function edit($id){
        $listing = Listing::where('id',$id)->where('user_id',auth()->user()->id)->where('status',1)->firstOrFail();
        $categories = Category::where('status',1)->get();
        $locations = Location::where('status',1)->get();
        return view($this->theme.'user.listing.edit',compact('listing','categories','locations'));
    }

    /**
     * The business owner's "M S Infotech" panel — real counts pulled from
     * the enquiries table, nothing invented. There's no call-tracking,
     * competitor data or profile-scoring pipeline behind this site, so
     * unlike a JustDial-style dashboard this only ever shows numbers this
     * listing's own enquiry rows can actually answer for.
     */
    public function businessDashboard($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $ownerListings = Listing::where('user_id', auth()->id())
            ->where('status', 1)
            ->with('location')
            ->orderBy('id', 'desc')
            ->get();

        // type='enquiry' only — quotation requests (see requestQuoteStore())
        // are a different thing with their own count below, not folded into
        // these numbers.
        $enquiries = \App\Models\Enquiry::where('listing_id', $listing->id)->where('type', 'enquiry');

        $todayCount = (clone $enquiries)->whereDate('created_at', today())->count();
        $totalCount = (clone $enquiries)->count();
        $missedCount = (clone $enquiries)->where('is_read', 0)->count();
        $respondedCount = $totalCount - $missedCount;
        $respondedPercent = $totalCount > 0 ? round(($respondedCount / $totalCount) * 100) : 0;
        // "Hot" is plainly defined as "arrived in the last 24 hours" — a
        // real, derivable cut, not a fabricated interest score.
        $hotCount = (clone $enquiries)->where('created_at', '>=', now()->subDay())->count();

        $recentEnquiries = (clone $enquiries)->latest()->take(10)->get();

        $quotationCount = \App\Models\Enquiry::where('listing_id', $listing->id)
            ->where('type', 'quotation')
            ->where('is_hidden', 0)
            ->count();

        return view($this->theme.'user.listing.dashboard', compact(
            'listing', 'todayCount', 'totalCount', 'missedCount',
            'respondedCount', 'respondedPercent', 'hotCount', 'recentEnquiries',
            'quotationCount', 'ownerListings'
        ));
    }

    /**
     * Full-page JustDial-style "Manage Business" screen — the one the Edit
     * pencil on the public listing opens. Everything on it is a real,
     * already-saved field of this listing; each row's "Edit" button links to
     * the existing per-section form (business-name, business-contact, ...).
     * No invented data — value/"Info Missing" states are derived from the
     * listing's own columns, same as the dashboard's Business Profile panel.
     */
    public function manage($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $ownerListings = Listing::where('user_id', auth()->id())
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

        $galleryCount   = $listing->extraImages()->where('type', 'gallery')->count()
            + collect($listing->photos ?? [])->filter()->count();
        $catalogueCount = $listing->extraImages()->where('type', 'catalogue')->count();

        $avgRating    = round($listing->ratings->avg('ratings') ?? 0, 1);
        $totalRatings = $listing->ratings->count();

        $publicUrl = $listing->category
            ? route('listings.details', [$listing->category->slug, $listing->slug])
            : null;

        return view($this->theme.'user.listing.manage', compact(
            'listing', 'ownerListings', 'galleryCount', 'catalogueCount',
            'avgRating', 'totalRatings', 'publicUrl'
        ));
    }

    /**
     * Public "Advertise / Boost your business" plans page for one listing.
     *
     * Shows every active admin package as a JustDial-style plan card. Buying a
     * plan runs through the existing deposit/payment flow (enabled payment
     * methods only), carrying this listing's id so the package activates on the
     * business rather than the buyer's account.
     */
    public function advertise($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $packages = \App\Models\Package::where('status', 1)->orderBy('price')->get();
        $methods = \App\Models\MethodCurrency::where('enable_status', 1)
            ->with('paymentMethod')->orderBy('currency_name')->get();

        return view($this->theme.'user.listing.advertise', compact('listing', 'packages', 'methods'));
    }

    /**
     * JustDial-style checkout page for one plan on one listing: payment methods
     * on the left, a price-details summary (budget / discount / GST / total) on
     * the right. Paying runs through the same deposit flow (enabled methods
     * only) with this listing's id attached.
     */
    public function advertiseCheckout($id, $package)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $package = \App\Models\Package::where('status', 1)->where('id', $package)->firstOrFail();

        // Free plans have no checkout — activate straight away.
        if ($package->price <= 0) {
            return redirect()->route('admin.package.free.activate', $package->id);
        }

        $methods = \App\Models\MethodCurrency::where('enable_status', 1)
            ->with('paymentMethod')->orderBy('currency_name')->get();

        return view($this->theme.'user.listing.advertise-checkout', compact('listing', 'package', 'methods'));
    }

    /**
     * Analytics — every number here comes from either the enquiries table
     * or listing_daily_stats (both already tracked for other reasons: leads
     * and the admin analytics dashboard). There is no call telephony, no
     * competitor data and no profile-scoring pipeline behind this site, so
     * unlike JustDial's version this page simply does not have a Response
     * Time / Competitors / Profile Score section — showing one would mean
     * inventing numbers this site has no way to know.
     */
    public function analytics($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        $enquiryRows = \App\Models\Enquiry::where('listing_id', $listing->id)
            ->where('type', 'enquiry')
            ->where('created_at', '>=', $months->first())
            ->get();

        $statRows = \App\Models\ListingDailyStats::where('listing_id', $listing->id)
            ->where('date', '>=', $months->first())
            ->get();

        $ratingRows = $listing->ratings()->where('created_at', '>=', $months->first())->get();

        $monthly = $months->map(function ($month) use ($enquiryRows, $statRows, $ratingRows) {
            $inMonth = fn ($date) => $date->isSameMonth($month) && $date->isSameYear($month);

            $monthEnquiries = $enquiryRows->filter(fn ($e) => $inMonth($e->created_at));
            $monthStats = $statRows->filter(fn ($s) => $inMonth($s->date));

            return [
                'label' => $month->format('M'),
                'enquiries' => $monthEnquiries->count(),
                'responded' => $monthEnquiries->where('is_read', 1)->count(),
                'views' => (int) $monthStats->sum('views'),
                'phone_clicks' => (int) $monthStats->sum('phone_clicks'),
                'whatsapp_clicks' => (int) $monthStats->sum('whatsapp_clicks'),
                'direction_clicks' => (int) $monthStats->sum('direction_clicks'),
                'gallery_clicks' => (int) $monthStats->sum('gallery_clicks'),
                'reviews' => $ratingRows->filter(fn ($r) => $inMonth($r->created_at))->count(),
            ];
        });

        $totals = [
            'enquiries' => $enquiryRows->count(),
            'responded' => $enquiryRows->where('is_read', 1)->count(),
            'views' => (int) $statRows->sum('views'),
            'unique_views' => (int) $statRows->sum('unique_views'),
            'phone_clicks' => (int) $statRows->sum('phone_clicks'),
            'whatsapp_clicks' => (int) $statRows->sum('whatsapp_clicks'),
            'website_clicks' => (int) $statRows->sum('website_clicks'),
            'direction_clicks' => (int) $statRows->sum('direction_clicks'),
            'gallery_clicks' => (int) $statRows->sum('gallery_clicks'),
            'share_clicks' => (int) $statRows->sum('share_clicks'),
            'favourite_clicks' => (int) $statRows->sum('favourite_clicks'),
        ];
        $totals['responded_percent'] = $totals['enquiries'] > 0
            ? round(($totals['responded'] / $totals['enquiries']) * 100)
            : 0;

        $totalReviews = $listing->ratings()->count();
        $avgRating = round($listing->ratings()->avg('ratings') ?? 0, 1);

        return view($this->theme.'user.listing.analytics', compact(
            'listing', 'monthly', 'totals', 'totalReviews', 'avgRating'
        ));
    }

    /**
     * Average Response Time — real, but only from the point responded_at
     * started being recorded (see the migration that added it): the moment
     * an enquiry first has its details opened. Enquiries from before that
     * column existed have no responded_at and are excluded rather than
     * guessed at. There is no competitor response time anywhere in this
     * site's data, so unlike JustDial's version this page has nothing next
     * to "Your Response Time" — not a placeholder, just absent.
     */
    public function responseTime($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $weeks = collect(range(3, 0))->map(fn ($i) => now()->subWeeks($i)->startOfWeek());

        $responded = \App\Models\Enquiry::where('listing_id', $listing->id)
            ->where('type', 'enquiry')
            ->whereNotNull('responded_at')
            ->where('created_at', '>=', $weeks->first())
            ->get();

        $weekly = $weeks->map(function ($weekStart) use ($responded) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            $inWeek = $responded->filter(fn ($e) => $e->created_at->between($weekStart, $weekEnd));

            $avgSeconds = $inWeek->isNotEmpty()
                ? $inWeek->avg(fn ($e) => $e->responded_at->diffInSeconds($e->created_at))
                : null;

            return [
                'label' => $weekStart->format('d M'),
                'avg_seconds' => $avgSeconds,
                'count' => $inWeek->count(),
            ];
        });

        $overallAvgSeconds = $responded->isNotEmpty()
            ? $responded->avg(fn ($e) => $e->responded_at->diffInSeconds($e->created_at))
            : null;

        return view($this->theme.'user.listing.response-time', compact(
            'listing', 'weekly', 'overallAvgSeconds', 'responded'
        ));
    }

    /**
     * Competitors — ranks this listing against the real other listings in
     * the same category and city, using numbers this site actually has:
     * enquiry counts, ratings, review counts, gallery photo counts and
     * response rate. No search-volume trend, no "Call Answered Rate", no
     * profile-scoring algorithm and no "Most Liked Features" carousel —
     * this site tracks none of that, so unlike JustDial's version this
     * page simply doesn't have those sections instead of inventing them.
     */
    public function competitors($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $peers = Listing::where('category_id', $listing->category_id)
            ->where('location_id', $listing->location_id)
            ->where('status', 1)
            ->notExpired()
            ->get();

        // Always include this listing in its own peer set even if it
        // somehow fell out of the query above (e.g. expired/inactive) —
        // it still needs to appear in its own ranking.
        if (!$peers->contains('id', $listing->id)) {
            $peers->push($listing);
        }

        $peerIds = $peers->pluck('id');

        $enquiryCounts = \App\Models\Enquiry::whereIn('listing_id', $peerIds)
            ->where('type', 'enquiry')
            ->selectRaw('listing_id, count(*) as total')
            ->groupBy('listing_id')
            ->pluck('total', 'listing_id');

        $respondedCounts = \App\Models\Enquiry::whereIn('listing_id', $peerIds)
            ->where('type', 'enquiry')->where('is_read', 1)
            ->selectRaw('listing_id, count(*) as total')
            ->groupBy('listing_id')
            ->pluck('total', 'listing_id');

        $ratingStats = \App\Models\Rating::whereIn('listing_id', $peerIds)
            ->selectRaw('listing_id, count(*) as cnt, avg(ratings) as avg_rating')
            ->groupBy('listing_id')
            ->get()
            ->keyBy('listing_id');

        $photoCounts = \App\Models\ExtraImage::whereIn('listing_id', $peerIds)
            ->selectRaw('listing_id, count(*) as total')
            ->groupBy('listing_id')
            ->pluck('total', 'listing_id');

        $ranking = $peers->map(function ($peer) use ($enquiryCounts, $respondedCounts, $ratingStats, $photoCounts) {
            $enquiries = (int) ($enquiryCounts[$peer->id] ?? 0);
            $responded = (int) ($respondedCounts[$peer->id] ?? 0);

            return [
                'id' => $peer->id,
                'name' => $peer->name,
                'is_self' => false, // set by caller
                'leads' => $enquiries,
                'response_percent' => $enquiries > 0 ? round(($responded / $enquiries) * 100) : null,
                'review_count' => (int) ($ratingStats[$peer->id]->cnt ?? 0),
                'avg_rating' => round((float) ($ratingStats[$peer->id]->avg_rating ?? 0), 1),
                'photo_count' => (int) ($photoCounts[$peer->id] ?? 0),
            ];
        })->sortByDesc('leads')->values();

        $ranking = $ranking->map(function ($row, $index) use ($listing) {
            $row['rank'] = $index + 1;
            $row['is_self'] = $row['id'] === $listing->id;
            return $row;
        });

        $self = $ranking->firstWhere('id', $listing->id);
        $others = $ranking->where('id', '!=', $listing->id);

        $competitionAverages = [
            'response_percent' => $others->pluck('response_percent')->filter(fn ($v) => $v !== null)->avg(),
            'avg_rating' => $others->pluck('avg_rating')->filter()->avg(),
            'review_count' => $others->pluck('review_count')->avg(),
            'photo_count' => $others->pluck('photo_count')->avg(),
        ];

        return view($this->theme.'user.listing.competitors', compact(
            'listing', 'ranking', 'self', 'competitionAverages'
        ));
    }

    /**
     * Leads screen for one listing — every filter tab and the category
     * picker are real, derived from this listing's own enquiry rows rather
     * than a fixed list, so a business with only three categories of
     * enquiry never sees ninety it has never actually received one for.
     */
    public function leads(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Two screens share this one method: plain enquiries (default) and
        // Quotation Request (type=quotation) — same filters, same row
        // markup, just a different base slice of the same table.
        $type = $request->query('type') === 'quotation' ? 'quotation' : 'enquiry';

        $base = \App\Models\Enquiry::where('listing_id', $listing->id)->where('type', $type);

        $totalCount = (clone $base)->where('is_hidden', 0)->count();
        // "Missed" here means the same thing "Missed Enquiries" means on the
        // business dashboard — never responded to — because there's no call
        // telephony behind this site to know a call rang and was not
        // answered (that's "Missed Call" / "Not Connected" below, which
        // this site genuinely cannot know and says so rather than guessing).
        $newCount = (clone $base)->where('is_hidden', 0)->where('is_read', 0)->count();
        $missedCount = $newCount;

        $query = (clone $base)->with('category');

        $filter = $request->query('filter');
        if ($filter === 'hidden') {
            $query->where('is_hidden', 1);
        } elseif ($filter === 'starred') {
            $query->where('is_starred', 1);
        } elseif ($filter === 'notes') {
            $query->where('is_hidden', 0)->whereNotNull('note')->where('note', '!=', '');
        } elseif ($filter === 'hot') {
            $query->where('is_hidden', 0)->where('created_at', '>=', now()->subDay());
        } elseif ($filter === 'today') {
            // Same cut the dashboard's "Today's Enquiries" count uses —
            // clicking that number should show exactly the rows it counted.
            $query->where('is_hidden', 0)->whereDate('created_at', today());
        } elseif ($filter === 'new') {
            $query->where('is_hidden', 0)->where('is_read', 0);
        } elseif ($filter === 'missed') {
            $query->where('is_hidden', 0)->where('is_read', 0);
        } else {
            $query->where('is_hidden', 0);
        }

        if ($request->filled('subcategory_id')) {
            // Enquiries only ever carry a top-level category_id, never a
            // subcategory — the listing's subcategories (subcategory_ids)
            // have no column to join against. Matching on name is the only
            // real link between "what the buyer's enquiry says it's about"
            // and "which subcategory the owner picked for this listing".
            $subcategoryNames = \App\Models\Subcategory::whereIn('id', (array) $request->subcategory_id)
                ->pluck('name');

            $query->whereHas('category', function ($q) use ($subcategoryNames) {
                $q->whereIn('name', $subcategoryNames);
            });
        }

        // Real date-range filter — Today / Yesterday / This Week / This
        // Month / a picked range, all against the enquiry's own created_at.
        $dateRange = $request->query('date_range');
        if ($dateRange === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateRange === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($dateRange === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateRange === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($dateRange === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($request->date_from)->startOfDay(),
                \Carbon\Carbon::parse($request->date_to)->endOfDay(),
            ]);
        }

        // Real read/unread filter — the "Enquiry Status" dropdown.
        $status = $request->query('status');
        if ($status === 'read') {
            $query->where('is_read', 1);
        } elseif ($status === 'unread') {
            $query->where('is_read', 0);
        }

        $leads = $query->latest()->paginate(20)->withQueryString();

        // For a Quotation Request, "also sent to N other businesses" is a
        // real count — every fan-out from requestQuoteStore() shares the
        // same phone + category_id + a created_at within the same second,
        // since all rows for one submission are inserted in one loop.
        // Nothing here says *which* businesses — just how many, which is
        // as far as this data can honestly go.
        $alsoSentCounts = [];
        if ($type === 'quotation' && $leads->count()) {
            foreach ($leads as $lead) {
                $alsoSentCounts[$lead->id] = \App\Models\Enquiry::where('type', 'quotation')
                    ->where('phone', $lead->phone)
                    ->where('category_id', $lead->category_id)
                    ->where('created_at', $lead->created_at)
                    ->where('listing_id', '!=', $listing->id)
                    ->count();
            }
        }

        // The subcategories the owner actually picked for this listing when
        // they added/edited it — not a fixed list, and not derived from
        // enquiries either (a subcategory with zero enquiries so far should
        // still be pickable as a filter).
        $categoryOptions = $listing->subcategory;

        return view($this->theme.'user.listing.leads', compact(
            'listing', 'leads', 'newCount', 'missedCount', 'totalCount', 'filter',
            'categoryOptions', 'dateRange', 'status', 'type', 'alsoSentCounts'
        ));
    }

    protected function findOwnedEnquiry($enquiryId)
    {
        return \App\Models\Enquiry::whereHas('listing', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($enquiryId);
    }

    public function leadStar($enquiryId)
    {
        $enquiry = $this->findOwnedEnquiry($enquiryId);
        $enquiry->is_starred = !$enquiry->is_starred;
        $enquiry->save();

        return response()->json(['starred' => $enquiry->is_starred]);
    }

    public function leadHide($enquiryId)
    {
        $enquiry = $this->findOwnedEnquiry($enquiryId);
        $enquiry->is_hidden = !$enquiry->is_hidden;
        $enquiry->save();

        return response()->json(['hidden' => $enquiry->is_hidden]);
    }

    public function leadNote(Request $request, $enquiryId)
    {
        $request->validate(['note' => 'nullable|string|max:2000']);

        $enquiry = $this->findOwnedEnquiry($enquiryId);
        $enquiry->note = $request->note;
        $enquiry->save();

        return response()->json(['note' => $enquiry->note]);
    }

    public function leadReminder(Request $request, $enquiryId)
    {
        $request->validate(['reminder_at' => 'required|date']);

        $enquiry = $this->findOwnedEnquiry($enquiryId);
        $enquiry->reminder_at = $request->reminder_at;
        $enquiry->save();

        return response()->json(['reminder_at' => $enquiry->reminder_at->toIso8601String()]);
    }

    public function leadRead($enquiryId)
    {
        $enquiry = $this->findOwnedEnquiry($enquiryId);

        if (!$enquiry->is_read) {
            $enquiry->is_read = 1;
            $enquiry->responded_at = now();
            $enquiry->save();
        }

        return response()->json(['ok' => true]);
    }

    public function importLeadsForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.import-leads', compact('listing'));
    }

    /**
     * "Business Name" — its own real page (not a panel-within-a-panel),
     * same as every other business-owner screen on this site.
     */
    public function businessNameForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-name', compact('listing'));
    }

    /**
     * Same pending-approval rule the main edit form already enforces (see
     * update(), "Business name approval"): a changed name never applies
     * straight away, it sits as pending_name until an admin approves it.
     * This just gives that same rule a second, focused entry point instead
     * of duplicating the logic.
     */
    public function requestNameChange(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $newName = trim($request->name);
        $changed = $newName !== trim((string) $listing->name);

        if ($changed) {
            $listing->pending_name = $newName;
            $listing->name_approval_status = 'pending';
            $listing->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => $changed ? 'pending' : 'unchanged',
                'message' => $changed ? 'Your new business name has been sent for admin approval.' : "That's already your business name.",
            ]);
        }

        return back()->withAlert([
            $changed ? 'success' : 'info',
            $changed ? 'Your new business name has been sent for admin approval.' : "That's already your business name.",
        ]);
    }

    /**
     * "Business Address" — its own real page, same reasoning as the name.
     */
    public function businessAddressForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-address', compact('listing'));
    }

    /**
     * Same pending-approval rule as the name, for the address.
     */
    public function requestAddressChange(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'address' => 'required|string|max:1000',
        ]);

        $newAddress = trim($request->address);
        $changed = $newAddress !== trim((string) $listing->address);

        if ($changed) {
            $listing->pending_address = $newAddress;
            $listing->address_approval_status = 'pending';
            $listing->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => $changed ? 'pending' : 'unchanged',
                'message' => $changed ? 'Your new business address has been sent for admin approval.' : "That's already your business address.",
            ]);
        }

        return back()->withAlert([
            $changed ? 'success' : 'info',
            $changed ? 'Your new business address has been sent for admin approval.' : "That's already your business address.",
        ]);
    }

    /**
     * "Contact Details" — its own real page. Unlike name/address these
     * fields already apply immediately in the main edit form (no approval
     * step exists for them there either), so this keeps the same behaviour
     * rather than inventing an approval rule the rest of the site doesn't
     * have for contact info.
     */
    public function businessContactForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-contact', compact('listing'));
    }

    public function updateContactDetails(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'contact_title' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',

            'mobile_country_code' => 'nullable|array',
            'mobile_country_code.*' => 'nullable|string|max:10',
            'mobile_numbers' => 'nullable|array',
            'mobile_numbers.*' => 'nullable|string|max:20',

            'whatsapp_country_code' => 'nullable|array',
            'whatsapp_country_code.*' => 'nullable|string|max:10',
            'whatsapp_numbers' => 'nullable|array',
            'whatsapp_numbers.*' => 'nullable|string|max:20',

            'landline_country_code' => 'nullable|array',
            'landline_country_code.*' => 'nullable|string|max:10',
            'landline_numbers' => 'nullable|array',
            'landline_numbers.*' => 'nullable|string|max:20',

            'extra_emails' => 'nullable|array',
            'extra_emails.*' => 'nullable|email|max:255',
        ]);

        $stripEmpty = fn ($values) => collect($values ?? [])
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->values();

        $mobileNumbers = $stripEmpty($request->mobile_numbers);
        $whatsappNumbers = $stripEmpty($request->whatsapp_numbers);
        $landlineNumbers = $stripEmpty($request->landline_numbers);
        $extraEmails = $stripEmpty($request->extra_emails);

        // Every NEW mobile number and email must be OTP-verified before it can
        // be saved. Numbers/emails already on the listing are grandfathered
        // (they were fine before this rule existed); only values that weren't
        // saved yet need a verification done this session. WhatsApp/landline
        // are not gated.
        $existingMobiles = collect($listing->mobile_numbers ?? [])
            ->map(fn ($v) => \App\Support\MobileNumber::national($v))->filter()->all();
        $existingEmails = collect($listing->extra_emails ?? [])
            ->map(fn ($v) => strtolower(trim((string) $v)))->filter()->all();
        $verifiedKeys = collect(session('contact_verified', []));

        foreach ($mobileNumbers as $num) {
            $norm = \App\Support\MobileNumber::national($num);
            if (in_array($norm, $existingMobiles, true) || $verifiedKeys->contains('mobile:'.$norm)) {
                continue;
            }
            return back()->withInput()->withAlert(['danger', "Please verify the mobile number {$num} with the OTP before saving."]);
        }

        foreach ($extraEmails as $mail) {
            $norm = strtolower(trim($mail));
            if (in_array($norm, $existingEmails, true) || $verifiedKeys->contains('email:'.$norm)) {
                continue;
            }
            return back()->withInput()->withAlert(['danger', "Please verify the email {$mail} with the OTP before saving."]);
        }

        $listing->contact_title = $request->contact_title;
        $listing->contact_person = $request->contact_person;

        $listing->mobile_numbers = $mobileNumbers->all();
        $listing->mobile_country_code = array_slice((array) $request->mobile_country_code, 0, $mobileNumbers->count());

        $listing->whatsapp_numbers = $whatsappNumbers->all();
        $listing->whatsapp_country_code = array_slice((array) $request->whatsapp_country_code, 0, $whatsappNumbers->count());

        $listing->landline_numbers = $landlineNumbers->all();
        $listing->landline_country_code = array_slice((array) $request->landline_country_code, 0, $landlineNumbers->count());

        $listing->extra_emails = $extraEmails->all();

        $listing->save();

        return back()->withAlert(['success', 'Contact details updated successfully.']);
    }

    /** Session key for one contact value's OTP / verified state. */
    private function contactOtpKey(string $channel, string $value): string
    {
        $norm = $channel === 'email'
            ? strtolower(trim($value))
            : \App\Support\MobileNumber::national($value);

        return $channel.':'.$norm;
    }

    /**
     * Send an OTP to a mobile number (WhatsApp, via Gupshup) or an email
     * (mail) the owner is adding on the Contact Details page. The code is held
     * in the session keyed by the normalised value; verifyContactOtp() checks
     * it, and updateContactDetails() refuses to save an unverified new value.
     */
    public function sendContactOtp(Request $request, $id)
    {
        Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'channel' => 'required|in:mobile,email',
            'value' => 'required|string|max:255',
        ]);

        $channel = $request->channel;
        $value = trim($request->value);

        if ($channel === 'mobile' && strlen(\App\Support\MobileNumber::national($value)) !== 10) {
            return response()->json(['status' => false, 'message' => 'Enter a valid 10-digit mobile number first.'], 422);
        }
        if ($channel === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => false, 'message' => 'Enter a valid email address first.'], 422);
        }

        $otp = (string) random_int(100000, 999999);

        if ($channel === 'mobile') {
            $sent = $this->sendWhatsappOtp(\App\Support\MobileNumber::national($value), $otp);
            if ($sent !== true) {
                return response()->json(['status' => false, 'message' => $sent], 502);
            }
        } else {
            try {
                app(\App\Http\Libs\SendMail::class)->sendMail([
                    'name' => 'there',
                    'email' => $value,
                    'template' => 'verification_code',
                    'value' => ['code' => $otp],
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Contact email OTP failed', ['error' => $e->getMessage()]);

                return response()->json(['status' => false, 'message' => 'Could not send the OTP email right now. Please try again.'], 502);
            }
        }

        session()->put('contact_otp.'.$this->contactOtpKey($channel, $value), [
            'otp' => $otp,
            'expires' => now()->addMinutes(10)->timestamp,
        ]);

        return response()->json([
            'status' => true,
            'message' => $channel === 'mobile' ? 'OTP sent on WhatsApp.' : 'OTP sent to that email.',
        ]);
    }

    public function verifyContactOtp(Request $request, $id)
    {
        Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'channel' => 'required|in:mobile,email',
            'value' => 'required|string|max:255',
            'otp' => 'required|digits:6',
        ]);

        $key = $this->contactOtpKey($request->channel, trim($request->value));
        $entry = session('contact_otp.'.$key);

        if (! $entry) {
            return response()->json(['status' => false, 'message' => 'Please request an OTP first.'], 422);
        }
        if (now()->timestamp > ($entry['expires'] ?? 0)) {
            session()->forget('contact_otp.'.$key);

            return response()->json(['status' => false, 'message' => 'OTP expired. Please request a new one.'], 422);
        }
        if (! hash_equals((string) $entry['otp'], (string) $request->otp)) {
            return response()->json(['status' => false, 'message' => 'Incorrect OTP. Please try again.'], 422);
        }

        session()->forget('contact_otp.'.$key);
        $verified = collect(session('contact_verified', []))->push($key)->unique()->values()->all();
        session()->put('contact_verified', $verified);

        return response()->json(['status' => true, 'message' => 'Verified.']);
    }

    /**
     * Fire one WhatsApp OTP through Gupshup (same template the profile
     * Mobile-2 OTP uses). Returns true on success, or a user-facing error
     * string on failure.
     *
     * @return true|string
     */
    private function sendWhatsappOtp(string $national, string $otp)
    {
        $apiKey = trim((string) config('services.gupshup.api_key'));
        $appName = trim((string) config('services.gupshup.app_name'));
        $source = preg_replace('/\D/', '', (string) config('services.gupshup.source'));
        $templateId = trim((string) config('services.gupshup.otp_template_id'));

        if ($apiKey === '' || $appName === '' || $source === '' || $templateId === '') {
            return 'WhatsApp OTP is not configured on the server.';
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->connectTimeout(10)
                ->timeout(30)
                ->withHeaders(['apikey' => $apiKey, 'Accept' => 'application/json'])
                ->post('https://api.gupshup.io/wa/api/v1/template/msg', [
                    'channel' => 'whatsapp',
                    'source' => $source,
                    'destination' => \App\Support\MobileNumber::destination($national),
                    'src.name' => $appName,
                    'template' => json_encode(['id' => $templateId, 'params' => [$otp]], JSON_UNESCAPED_SLASHES),
                ]);

            if (! $response->successful()) {
                return 'Could not send the WhatsApp OTP right now. Please try again in a moment.';
            }

            $status = strtolower((string) ($response->json()['status'] ?? ''));
            if (! in_array($status, ['success', 'submitted'], true)) {
                return $response->json()['message'] ?? 'Could not send the WhatsApp OTP right now.';
            }

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Contact WhatsApp OTP failed', ['error' => $e->getMessage()]);

            return 'Unable to connect to the WhatsApp service right now.';
        }
    }

    /**
     * "Serviceable Area / Pincodes" — the one real, working choice out of
     * that JustDial screen: whether this listing can ever be reached by a
     * quote request from outside its own city. There's no pincode/suburb
     * boundary system on this site, so unlike JustDial's version there's no
     * pincode picker here — just the choice that actually does something,
     * in SiteController::requestQuoteStore()'s cross-city fallback.
     */
    public function businessServiceableAreaForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-serviceable-area', compact('listing'));
    }

    public function updateServiceableArea(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'receive_leads_other_cities' => 'required|in:0,1',
        ]);

        $listing->receive_leads_other_cities = (bool) $request->receive_leads_other_cities;
        $listing->save();

        return back()->withAlert(['success', 'Serviceable area preference saved.']);
    }

    /**
     * "Map and Location" — a real, draggable-pin map (Leaflet + OpenStreetMap
     * tiles, same source the edit form's own pincode-to-coordinates lookup
     * already uses) over this listing's actual latitude/longitude, not a
     * static screenshot.
     */
    public function businessMapLocationForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-map-location', compact('listing'));
    }

    public function updateMapLocation(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $listing->latitude = $request->latitude;
        $listing->longitude = $request->longitude;
        $listing->save();

        return back()->withAlert(['success', 'Map location updated.']);
    }

    /**
     * "Business Timings" — its own page, same business_days/open_time/
     * close_time/open_24_hours columns and multi-slot format (index-matched
     * open_time[i] / close_time[i] pairs) the main edit form already saves,
     * so a dual shift set here ("Mon 9:30 AM–12:00 PM, then 5:00 PM–8:00
     * PM") is the exact same "+ Add Another Time Slot" the edit form has
     * had all along — not a new schema, a second entry point to the one
     * that exists.
     */
    public function businessTimingsForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-timings', compact('listing'));
    }

    public function updateBusinessTimings(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'business_days' => 'nullable|array',
            'business_days.*' => 'nullable|string|max:20',
            'open_24_hours' => 'nullable|boolean',
            'open_time' => 'nullable|array',
            'open_time.*' => 'nullable|string|max:50',
            'close_time' => 'nullable|array',
            'close_time.*' => 'nullable|string|max:50',
        ]);

        $listing->business_days = $request->input('business_days', []);

        // `open_24_hours` isn't a real column on `listings` — the public page
        // derives "Open 24 Hours" purely from the 12:00 AM–11:59 PM times (see
        // Listing::isOpen24HoursDerived()). So branch on the request value, and
        // only write the flag where the column actually exists, to stay safe.
        $open24 = $request->boolean('open_24_hours');
        if (\Schema::hasColumn('listings', 'open_24_hours')) {
            $listing->open_24_hours = $open24;
        }

        if ($open24) {
            // Store the same 12:00 AM–11:59 PM the public detail page reads as
            // "Open 24 Hours", so the flag and the shown hours never diverge
            // (clearing the times left the public page with no hours at all).
            $listing->open_time = ['12:00 AM'];
            $listing->close_time = ['11:59 PM'];
        } else {
            $openTimes = collect($request->input('open_time', []));
            $closeTimes = collect($request->input('close_time', []));

            // Keep only slots that actually have both ends filled in — an
            // "+ Add Another Time Slot" row left empty shouldn't save as a
            // blank shift.
            $keep = $openTimes->keys()->filter(fn ($i) => filled($openTimes[$i] ?? null) && filled($closeTimes[$i] ?? null));

            $listing->open_time = $keep->map(fn ($i) => $openTimes[$i])->values()->all();
            $listing->close_time = $keep->map(fn ($i) => $closeTimes[$i])->values()->all();
        }

        $listing->save();

        return back()->withAlert(['success', 'Business timings updated.']);
    }

    /**
     * "Year of Establishment" — its own page. established_year already
     * existed and already applies immediately in the main edit form (no
     * admin review step exists for it there), so this keeps that same
     * behaviour rather than claiming a "sent for verification" wait this
     * site doesn't actually have for this field.
     */
    public function businessEstablishedForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-established', compact('listing'));
    }

    public function updateEstablished(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'established_month' => 'nullable|integer|between:1,12',
            'established_year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
        ]);

        $listing->established_month = $request->established_month ?: null;
        $listing->established_year = $request->established_year ?: null;
        $listing->save();

        return back()->withAlert(['success', 'Year of establishment updated.']);
    }

    /**
     * "Business Categories" — real subcategories on the real
     * subcategory_ids column (same one the admin listing form's own
     * search-and-tag picker already writes to; see Admin\ListingController
     * and Listing::getSubcategoryAttribute()). Search runs across every
     * subcategory, not just this listing's own category — the saved list
     * a business already has can span several categories (an IT company
     * tagged under "Digital Marketing" and "SEO Services" too), so search
     * has to be able to find those as well.
     */
    public function businessCategoriesForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $selected = $listing->subcategory; // accessor: real Subcategory rows for subcategory_ids

        return view($this->theme.'user.listing.business-categories', compact('listing', 'selected'));
    }

    public function searchSubcategories(Request $request, $id)
    {
        // Just ownership — not scoped to the listing's own category. The
        // saved subcategory_ids this same page already lists prove that:
        // "IT Consultants near me" sits alongside "Digital Marketing" and
        // "SEO Services", categories a business can genuinely want to be
        // found under even when they're not the listing's primary one
        // (matches how the real JustDial search behaves too — results
        // aren't confined to one category).
        Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // input() so it works whether the term arrives as a POST body field
        // (the firewall-friendly path the page now uses) or a GET query.
        $term = trim((string) $request->input('q'));

        if ($term === '') {
            return response()->json([]);
        }

        $results = \App\Models\Subcategory::where('status', 1)
            ->where('name', 'like', '%' . $term . '%')
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name']);

        return response()->json($results);
    }

    public function updateCategories(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'subcategory_ids' => 'nullable|array',
            'subcategory_ids.*' => 'integer|exists:subcategories,id',
        ]);

        $listing->subcategory_ids = array_values(array_unique(array_filter((array) $request->subcategory_ids)));
        $listing->save();

        return back()->withAlert(['success', 'Business categories updated.']);
    }

    /**
     * "Photos and Videos" — real gallery, same extra_images table and
     * upload path addExtraImage() already writes to on the main edit form;
     * this is a second, focused entry point to it, plus a cover-photo flag
     * and a YouTube link field neither of which existed before.
     */
    public function businessPhotosForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Guards photos uploaded before the `type` column existed: MySQL
        // backfills a new NOT NULL DEFAULT column on ALTER, but this still
        // covers any row that somehow ended up with '' or NULL instead —
        // safer than a strict where('type','gallery') silently hiding it.
        $photos = $listing->extraImages()
            ->where(fn ($q) => $q->where('type', 'gallery')->orWhereNull('type')->orWhere('type', ''))
            ->orderByDesc('is_cover')
            ->latest()
            ->get();

        // Photos added through the older main edit form live on the listing's
        // own `photos` array column (files under listing/photos/), not in
        // extra_images — without these the page looked empty for every
        // business that ever uploaded through the classic form.
        $legacyPhotos = collect($listing->photos ?? [])->filter()->values();

        return view($this->theme.'user.listing.business-photos', compact('listing', 'photos', 'legacyPhotos'));
    }

    /**
     * Delete one legacy photo (a filename on the listing's `photos` array
     * column, shown alongside the extra_images gallery on the Photos page).
     */
    public function removeLegacyPhoto(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate(['filename' => 'required|string']);

        $photos = collect($listing->photos ?? []);
        if ($photos->contains($request->filename)) {
            $file = public_path('assets/images/listing/photos/'.$request->filename);
            if (is_file($file)) {
                @unlink($file);
            }
            $listing->photos = $photos->reject(fn ($p) => $p === $request->filename)->values()->all();
            $listing->save();
        }

        return back()->withAlert(['success', 'Photo removed.']);
    }

    /**
     * "Set as Cover Photo" for a legacy photo (one on the listing's `photos`
     * array column) — makes it the listing's thumbnail (`thumb`), which is the
     * image shown on listing cards, the public detail header and the manage
     * page header.
     *
     * The photo is copied into the thumb folder under the SAME filename and
     * `thumb` is set to that filename, so a legacy photo can be recognised as
     * the current cover simply by `listing->thumb === filename` — that single
     * check drives the "Cover Photo" tag on the gallery and every thumbnail.
     */
    public function setLegacyCoverPhoto(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate(['filename' => 'required|string']);

        $photos = collect($listing->photos ?? []);
        if (! $photos->contains($request->filename)) {
            return back()->withAlert(['danger', 'Photo not found.']);
        }

        // Only point thumb at it once the file is actually in the thumb folder,
        // so we never set thumb to a name that renders as a broken image.
        if ($this->copyImageToThumb('assets/images/listing/photos', $request->filename)) {
            $listing->thumb = $request->filename;
            $listing->save();

            return back()->withAlert(['success', 'Cover photo updated.']);
        }

        return back()->withAlert(['danger', 'Could not update the cover photo — the image file could not be found on the server.']);
    }

    /**
     * Copy an image from one of the listing image folders into the thumb
     * folder under the same filename, and return whether it landed there.
     *
     * The base dir is resolved tolerantly: loadImage()/deleteFile() read files
     * by a CWD-relative path (on the live server the working dir IS the public
     * dir), while uploads move files via public_path(). Different shared-hosting
     * layouts make one or the other the real location, so we copy inside
     * whichever base actually holds the source file.
     */
    protected function copyImageToThumb(string $sourceRelDir, string $filename): bool
    {
        $sourceRel = rtrim($sourceRelDir, '/').'/'.$filename;
        $thumbRelDir = 'assets/images/listing/thumb';
        $thumbRel = $thumbRelDir.'/'.$filename;

        $bases = array_values(array_unique(array_filter([
            rtrim(public_path(), '/\\').DIRECTORY_SEPARATOR,
            rtrim(base_path('public'), '/\\').DIRECTORY_SEPARATOR,
            rtrim(getcwd() ?: '', '/\\').DIRECTORY_SEPARATOR,
            '', // CWD-relative, exactly like loadImage()
        ])));

        foreach ($bases as $base) {
            if (! is_file($base.$sourceRel)) {
                continue;
            }
            if (! is_dir($base.$thumbRelDir)) {
                @mkdir($base.$thumbRelDir, 0755, true);
            }
            @copy($base.$sourceRel, $base.$thumbRel);
            if (is_file($base.$thumbRel)) {
                return true;
            }
        }

        return false;
    }

    public function uploadGalleryPhotos(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $this->addExtraImage($request, $listing);

        return back()->withAlert(['success', 'Photos uploaded successfully.']);
    }

    public function setCoverPhoto($listingId, $imageId)
    {
        $listing = Listing::where('id', $listingId)->where('user_id', auth()->id())->firstOrFail();

        $image = $listing->extraImages()->where('id', $imageId)->firstOrFail();

        $listing->extraImages()->update(['is_cover' => false]);
        $image->is_cover = true;
        $image->save();

        // Also make it the listing thumbnail (cards / public header / manage
        // header all render `thumb`), so "Set as Cover Photo" means the same
        // thing for gallery photos as it does for legacy ones.
        if ($this->copyImageToThumb('assets/images/listing/extra', $image->image)) {
            $listing->thumb = $image->image;
            $listing->save();
        }

        return back()->withAlert(['success', 'Cover photo updated.']);
    }

    public function updateVideoLink(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'youtube_video_url' => ['nullable', 'url', 'regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//'],
        ], [
            'youtube_video_url.regex' => 'That doesn\'t look like a YouTube link.',
        ]);

        $listing->youtube_video_url = $request->youtube_video_url ?: null;
        $listing->save();

        return back()->withAlert(['success', 'Video link saved.']);
    }

    /**
     * "Rate Card / Catalogue" — same extra_images table and upload path as
     * the photo gallery, kept apart only by type='catalogue' so the two
     * pages never see each other's images.
     */
    public function businessCatalogueForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $catalogueImages = $listing->extraImages()->where('type', 'catalogue')->latest()->get();

        return view($this->theme.'user.listing.business-catalogue', compact('listing', 'catalogueImages'));
    }

    public function uploadCatalogueImages(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        foreach ($request->file('images') as $file) {
            $image = $this->uploadFile('image', [
                'file' => $file,
                'path' => 'assets/images/listing/extra/',
                'size' => '1000x1000',
                'prevFile' => null,
            ]);

            $extraImage = new ExtraImage();
            $extraImage->listing_id = $listing->id;
            $extraImage->type = 'catalogue';
            $extraImage->image = $image;
            $extraImage->save();
        }

        return back()->withAlert(['success', 'Catalogue image(s) uploaded.']);
    }

    /**
     * "Business Website" — its own page over listings.website (already
     * real, already saved directly by updatesection() for the owner) plus
     * extra_websites for "+ Add Another Website".
     */
    public function businessWebsiteForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view($this->theme.'user.listing.business-website', compact('listing'));
    }

    public function updateWebsite(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'website' => 'nullable|url|max:255',
            'extra_websites' => 'nullable|array',
            'extra_websites.*' => 'nullable|url|max:255',
        ]);

        $listing->website = $request->website ?: null;
        // Same pending/approval columns updatesection() uses stay untouched
        // here — this route only ever runs for the listing's own owner
        // (see the firstOrFail() above), which is the branch that already
        // applies immediately there too.
        $listing->pending_website = null;
        $listing->website_approval_status = null;

        $listing->extra_websites = collect($request->extra_websites ?? [])
            ->map(fn ($url) => trim((string) $url))
            ->filter()
            ->values()
            ->all();

        $listing->save();

        return back()->withAlert(['success', 'Business website updated.']);
    }

    /**
     * "Social Media" — one JSON column, five keys (facebook/x/linkedin/
     * youtube/instagram), each a plain array of URLs so multiple links per
     * platform are just extra entries in that platform's array.
     */
    const SOCIAL_PLATFORMS = ['facebook', 'x', 'linkedin', 'youtube', 'instagram'];

    public function businessSocialForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $socialLinks = collect(self::SOCIAL_PLATFORMS)
            ->mapWithKeys(fn ($platform) => [$platform => collect((array) (($listing->social_links ?? [])[$platform] ?? []))]);

        return view($this->theme.'user.listing.business-social', compact('listing', 'socialLinks'));
    }

    public function updateSocialLinks(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $rules = [];
        foreach (self::SOCIAL_PLATFORMS as $platform) {
            $rules[$platform] = 'nullable|array';
            $rules[$platform . '.*'] = 'nullable|url|max:255';
        }
        $request->validate($rules);

        $social = [];
        foreach (self::SOCIAL_PLATFORMS as $platform) {
            $social[$platform] = collect($request->input($platform, []))
                ->map(fn ($url) => trim((string) $url))
                ->filter()
                ->values()
                ->all();
        }

        $listing->social_links = $social;
        $listing->save();

        return back()->withAlert(['success', 'Social media links updated.']);
    }

    /**
     * "Ratings QR" — a real QR code (generated client-side, no external
     * API/key needed) encoding this listing's actual public page URL —
     * the same page its "Ask Rating" WhatsApp message already links to.
     * Scanning it lands a customer on the real review form that page
     * already has; nothing new needed on the review side.
     */
    public function businessRatingsQrForm($id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $reviewUrl = $listing->category
            ? route('listings.details', [$listing->category->slug, $listing->slug])
            : null;

        return view($this->theme.'user.listing.business-ratings-qr', compact('listing', 'reviewUrl'));
    }

    public function importLeadsSample()
    {
        $csv = "Enquiry Date,Name,Mobile Number\n"
             . date('d-m-Y') . ",Ramesh Kumar,9876543210\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="example.csv"',
        ]);
    }

    public function importLeadsUpload(Request $request, $id)
    {
        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'data_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        $rows = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('data_file'));
        $sheet = $rows[0] ?? [];

        if (count($sheet) < 2) {
            return back()->withAlert(['danger', 'That file has no data rows to import.']);
        }

        $header = array_map(fn ($cell) => strtolower(trim((string) $cell)), $sheet[0]);

        $dateCol = null;
        $mobileCol = null;
        $nameCol = null;

        foreach ($header as $index => $label) {
            if ($dateCol === null && str_contains($label, 'date')) {
                $dateCol = $index;
            }
            if ($mobileCol === null && (str_contains($label, 'mobile') || str_contains($label, 'phone'))) {
                $mobileCol = $index;
            }
            if ($nameCol === null && str_contains($label, 'name')) {
                $nameCol = $index;
            }
        }

        if ($dateCol === null || $mobileCol === null) {
            return back()->withAlert(['danger', 'The file must have an "Enquiry Date" column and a "Mobile Number" column.']);
        }

        $imported = 0;
        $skipped = 0;
        $oneYearAgo = now()->subYear();

        foreach (array_slice($sheet, 1) as $row) {
            $phone = trim((string) ($row[$mobileCol] ?? ''));
            $rawDate = trim((string) ($row[$dateCol] ?? ''));

            if ($phone === '' || $rawDate === '') {
                $skipped++;
                continue;
            }

            try {
                $date = \Carbon\Carbon::parse($rawDate);
            } catch (\Throwable $e) {
                $skipped++;
                continue;
            }

            // "Only 1 year data from current day can be viewed" — matches
            // the note shown on the upload page.
            if ($date->lt($oneYearAgo)) {
                $skipped++;
                continue;
            }

            \App\Models\Enquiry::create([
                'listing_id' => $listing->id,
                'name'       => $nameCol !== null ? trim((string) ($row[$nameCol] ?? '')) ?: 'Imported Lead' : 'Imported Lead',
                'phone'      => $phone,
                'is_read'    => 0,
                'created_at' => $date,
                'updated_at' => now(),
            ]);

            $imported++;
        }

        return back()->withAlert(['success', "Imported {$imported} lead(s)." . ($skipped ? " {$skipped} row(s) skipped (missing/invalid date or mobile)." : '')]);
    }

public function update(Request $request, $id)
{
    $request->validate([
        'image'                   => 'nullable|image|mimes:jpg,jpeg,png,webp',

        'name'                    => 'required|string|max:255',
        'category_id'             => 'required|exists:categories,id',
        'location_id'             => 'required|exists:locations,id',
        'featured'                => 'nullable|in:0,1',

        'address'                 => 'nullable|string',

        'pincode'                 => 'nullable|string|max:10',
        'plot_no'                 => 'nullable|string|max:255',
        'building_name'           => 'nullable|string|max:255',
        'street_name'             => 'nullable|string|max:255',
        'landmark'                => 'nullable|string|max:255',
        'area'                    => 'nullable|string|max:255',
        'city'                    => 'nullable|string|max:255',
        'state'                   => 'nullable|string|max:255',
        'latitude'                => 'nullable|numeric',
        'longitude'               => 'nullable|numeric',

        'established_year'        => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),

        'contact_title'           => 'nullable|string|max:50',
        'contact_person'          => 'nullable|string|max:255',

        'mobile_country_code'     => 'nullable|array',
        'mobile_country_code.*'   => 'nullable|string|max:10',
        'mobile_numbers'          => 'nullable|array',
        'mobile_numbers.*'        => 'nullable|string|max:20',

        'whatsapp_country_code'   => 'nullable|array',
        'whatsapp_country_code.*' => 'nullable|string|max:10',
        'whatsapp_numbers'        => 'nullable|array',
        'whatsapp_numbers.*'      => 'nullable|string|max:20',

        'landline_country_code'   => 'nullable|array',
        'landline_country_code.*' => 'nullable|string|max:10',
        'landline_numbers'        => 'nullable|array',
        'landline_numbers.*'      => 'nullable|string|max:20',

        'extra_emails'            => 'nullable|array',
        'extra_emails.*'          => 'nullable|email|max:255',

        'business_days'           => 'nullable|array',
        'business_days.*'         => 'nullable|string|max:20',

        'open_24_hours'           => 'nullable|boolean',

        'open_time'               => 'nullable|array',
        'open_time.*'             => 'nullable|string|max:50',

        'close_time'              => 'nullable|array',
        'close_time.*'            => 'nullable|string|max:50',
    ]);

    $user = auth()->user();

    $listing = Listing::where('user_id', $user->id)
        ->where('status', 1)
        ->where('id', $id)
        ->firstOrFail();

    /*
    |--------------------------------------------------------------------------
    | Thumbnail
    |--------------------------------------------------------------------------
    */

    $thumb = $listing->thumb;

    if ($request->hasFile('image')) {
        $thumb = $this->uploadThumb($thumb, $request);
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile numbers
    |--------------------------------------------------------------------------
    */

    $mobileNumbers = collect($request->input('mobile_numbers', []))
        ->map(fn ($number) => trim((string) $number))
        ->filter(fn ($number) => $number !== '')
        ->values()
        ->all();

    $mobileCodes = array_values(
        array_slice(
            $request->input('mobile_country_code', []),
            0,
            count($mobileNumbers)
        )
    );

    /*
    |--------------------------------------------------------------------------
    | WhatsApp numbers
    |--------------------------------------------------------------------------
    */

    $whatsappNumbers = collect($request->input('whatsapp_numbers', []))
        ->map(fn ($number) => trim((string) $number))
        ->filter(fn ($number) => $number !== '')
        ->values()
        ->all();

    $whatsappCodes = array_values(
        array_slice(
            $request->input('whatsapp_country_code', []),
            0,
            count($whatsappNumbers)
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Landline numbers
    |--------------------------------------------------------------------------
    */

    $landlineNumbers = collect($request->input('landline_numbers', []))
        ->map(fn ($number) => trim((string) $number))
        ->filter(fn ($number) => $number !== '')
        ->values()
        ->all();

    $landlineCodes = array_values(
        array_slice(
            $request->input('landline_country_code', []),
            0,
            count($landlineNumbers)
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Extra emails
    |--------------------------------------------------------------------------
    */

    $extraEmails = collect($request->input('extra_emails', []))
        ->map(fn ($email) => trim((string) $email))
        ->filter(fn ($email) => $email !== '')
        ->values()
        ->all();

    /*
    |--------------------------------------------------------------------------
    | Business timings — same logic as store()
    |--------------------------------------------------------------------------
    */

    $businessDays = collect($request->input('business_days', []))
        ->map(fn ($day) => trim((string) $day))
        ->filter(fn ($day) => $day !== '')
        ->values()
        ->all();

    $isOpen24Hours = $request->boolean('open_24_hours');

    if ($isOpen24Hours) {
        $openTime = ['12:00 AM'];
        $closeTime = ['11:59 PM'];
    } else {
        $openTime = collect($request->input('open_time', []))
            ->map(fn ($time) => trim((string) $time))
            ->filter(fn ($time) => $time !== '')
            ->values()
            ->all();

        $closeTime = collect($request->input('close_time', []))
            ->map(fn ($time) => trim((string) $time))
            ->filter(fn ($time) => $time !== '')
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Image
    |--------------------------------------------------------------------------
    */

    $listing->thumb = $thumb;

    /*
    |--------------------------------------------------------------------------
    | Business name approval
    |--------------------------------------------------------------------------
    */

    if (trim((string) $request->name) !== trim((string) $listing->name)) {
        $listing->pending_name = $request->name;
        $listing->name_approval_status = 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | Address approval
    |
    | Address tabhi update/pending hoga jab address input actually form se aaye.
    | Plot/building/street etc. ko address me combine nahi kiya jayega.
    |--------------------------------------------------------------------------
    */

    if (
        $request->filled('address') &&
        trim((string) $request->address) !== trim((string) $listing->address)
    ) {
        $listing->pending_address = trim((string) $request->address);
        $listing->address_approval_status = 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | Direct update fields
    |--------------------------------------------------------------------------
    */

    $listing->category_id = $request->category_id;
    $listing->location_id = $request->location_id;
    $listing->is_featured = $request->input('featured', 0);

    $listing->pincode = $request->pincode;
    $listing->plot_no = $request->plot_no;
    $listing->building_name = $request->building_name;
    $listing->street_name = $request->street_name;
    $listing->landmark = $request->landmark;
    $listing->area = $request->area;
    $listing->city = $request->city;
    $listing->state = $request->state;
    $listing->latitude = $request->latitude;
    $listing->longitude = $request->longitude;

    $listing->established_year = $request->established_year;

    $listing->contact_title = $request->contact_title;
    $listing->contact_person = $request->contact_person;

    $listing->mobile_country_code = $mobileCodes;
    $listing->mobile_numbers = $mobileNumbers;

    $listing->whatsapp_country_code = $whatsappCodes;
    $listing->whatsapp_numbers = $whatsappNumbers;

    $listing->landline_country_code = $landlineCodes;
    $listing->landline_numbers = $landlineNumbers;

    $listing->extra_emails = $extraEmails;

    /*
    |--------------------------------------------------------------------------
    | Save business timings
    |--------------------------------------------------------------------------
    */

    $listing->business_days = $businessDays;
    $listing->open_time = $openTime;
    $listing->close_time = $closeTime;

    if (\Schema::hasColumn('listings', 'open_24_hours')) {
        $listing->open_24_hours = $isOpen24Hours ? 1 : 0;
    }

    $listing->save();

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    $message = 'Listing updated successfully.';

    if (
        $listing->name_approval_status === 'pending' ||
        $listing->address_approval_status === 'pending'
    ) {
        $message .= ' Business name and/or address changes are pending admin approval.';
    }

    \App\Support\Tracking\Tracker::conversion($request, 'listing_updated', ['listing_id' => $listing->id]);

    return redirect()
        ->route('user.listing.index')
        ->withAlert(['success', $message]);
}

    public function imageRemove($id){
        $extraImage = ExtraImage::findOrFail($id);
        $listing = $extraImage->listing;
        if ($listing->user_id != auth()->user()->id) {
            $alert = ['danger','Sorry! you have no access to remove this image'];
            return back()->withAlert($alert);
        }
        $this->deleteFile('assets/images/listing/extra/'.$extraImage->image);
        $extraImage->delete();
        $alert = ['success','Image removed successfully'];
        return back()->withAlert($alert);
    }

public function review(Request $request, SendMail $mail)
{
    $request->validate([
        'listing_id' => 'required|integer|exists:listings,id',
        'ratings'    => 'required|integer|min:1|max:5',
        'review'     => 'required|string|max:1000',
    ]);

    $listing = Listing::where('status', 1)
        ->where('id', $request->listing_id)
        ->firstOrFail();

    // ==========================
    // LOGIN USER REVIEW
    // ==========================
    if (auth()->check()) {
        $user = auth()->user();

        if ($listing->user_id == $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'You can not review your own listing'
            ], 422);
        }

        $rating = Rating::where('listing_id', $listing->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$rating) {
            $rating = new Rating();
            $rating->user_id = $user->id;
            $rating->listing_id = $listing->id;
        }

        $rating->name        = $user->fullname ?? $user->firstname ?? $user->username ?? $user->name ?? 'User';
        $rating->email       = $user->email ?? null;
        $rating->ratings     = $request->ratings;
        $rating->review      = $request->review;
        $rating->is_verified = 1;
        $rating->save();

        $avg = Rating::where('listing_id', $listing->id)
            ->where('is_verified', 1)
            ->avg('ratings');

        $listing->review = $avg ?: 0;
        $listing->save();

        return response()->json([
            'status'  => true,
            'message' => 'Review submitted successfully'
        ]);
    }

    // ==========================
    // GUEST USER OTP SEND
    // ==========================
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|max:255',
    ]);

    $otp = verification_code(6);

    session([
        'review_otp' => $otp,
        'review_otp_expires_at' => now()->addMinutes(10)->timestamp,
        'review_data' => [
            'listing_id' => $listing->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'ratings'    => $request->ratings,
            'review'     => $request->review,
        ]
    ]);

    $data = [
        'name'     => $request->name,
        'email'    => $request->email,
        'template' => 'verification_code',
        'value'    => [
            'code' => $otp,
        ]
    ];

    $mail->sendMail($data);

    return response()->json([
        'status'  => true,
        'message' => 'OTP sent to your email'
    ]);
}

public function verifyReviewOtp(Request $request)
{
    $request->validate([
        'otp' => 'required|string|min:4|max:10'
    ]);

    if (!session()->has('review_otp') || !session()->has('review_data')) {
        return response()->json([
            'status'  => false,
            'message' => 'Session expired. Please try again.'
        ], 422);
    }

    if (session('review_otp_expires_at') < now()->timestamp) {
        session()->forget(['review_otp', 'review_otp_expires_at', 'review_data']);

        return response()->json([
            'status'  => false,
            'message' => 'OTP expired'
        ], 422);
    }

    if (!hash_equals((string) session('review_otp'), (string) $request->otp)) {
        return response()->json([
            'status'  => false,
            'message' => 'Invalid OTP'
        ], 422);
    }

    $data = session('review_data');

    $rating = new Rating();
    $rating->user_id     = null;
    $rating->listing_id  = $data['listing_id'];
    $rating->name        = $data['name'];
    $rating->email       = $data['email'];
    $rating->ratings     = $data['ratings'];
    $rating->review      = $data['review'];
    $rating->is_verified = 1;
    $rating->save();

    $listing = Listing::find($data['listing_id']);
    if ($listing) {
        $avg = Rating::where('listing_id', $data['listing_id'])
            ->where('is_verified', 1)
            ->avg('ratings');

        $listing->review = $avg ?: 0;
        $listing->save();
    }

    session()->forget(['review_otp', 'review_otp_expires_at', 'review_data']);

    return response()->json([
        'status'  => true,
        'message' => 'Review submitted successfully'
    ]);
}

    public function updateReview(Request $request, $id)
{
    $request->validate([
        'ratings' => 'required|integer|min:1|max:5',
        'review'  => 'required|string',
    ]);

    $rating = Rating::findOrFail($id);
    $user = auth()->user();

    // A null authenticated user must never match a guest review's
    // `user_id IS NULL` — without this explicit check, an unauthenticated
    // visitor could edit any guest-submitted review.
    if (!$user || is_null($rating->user_id) || $rating->user_id != $user->id) {
        $alert = ['danger', 'You can edit only your own review'];
        return back()->withAlert($alert);
    }

    $rating->review = $request->review;
    $rating->ratings = $request->ratings;
    $rating->save();

    $listing = Listing::findOrFail($rating->listing_id);
    $avg = Rating::where('listing_id', $rating->listing_id)->avg('ratings');
    $listing->review = $avg ?: 0;
    $listing->save();

    $alert = ['success', 'Review updated successfully'];
    return back()->withAlert($alert);
}

    protected function validationRule(){
        return [
            'name'=>'required',
            'category'=>'required',
            'location'=>'required',
            'iframe'=>'required',
            'details'=>'required',
            'address'=>'required',
            'features'=>'required|array',
            'title'=>'required|array',
            'content'=>'required|array',
            'images.*'=>'nullable|mimes:jpg,jpeg,png',
        ];
    }

    protected function ability($request){
        $user = auth()->user();
        $package = $user->package;
        return [
            'availableFeatures' => $package->features_allow,
            'availableImages' => $package->image_allow,
            'availableContacts' => $package->contact_allow,
            'availableFeatureds' => $package->featured_allow,
            'availableListings' => $package->listing_allow,
        ];
    }

    protected function listingToDatabase($request,$thumb,$listing,$user){

        $purifier = new HTMLPurifier();
        $details = $purifier->purify($request->details);


        $listing->name = $request->name;
        $listing->user_id = $user->id;
        $listing->category_id = $request->category;
        $listing->location_id = $request->location;
        $listing->map_iframe = $request->iframe;
        $listing->details = $details;
        $listing->features = $request->features;
        $listing->address = $request->address;
        
        $listing->contact = [
            'title'=>$request->title,
            'content'=>$request->content,
        ];
        $listing->faq = [
            'question'=>$request->question,
            'answer'=>$request->answer,
        ];
        $listing->thumb = $thumb;
        $listing->is_featured = $request->featured;
        $listing->status = 0;
        $listing->save();
    }

    protected function uploadThumb($thumb,$request){
        if ($request->hasFile('image')) {
            try {
                $imageData = [
                    'file'=>$request->image,
                    'path'=>'assets/images/listing/thumb/',
                    'prevFile'=>$thumb,
                ];
                $thumb = $this->uploadFile('image',$imageData);
            } catch (Exception $e) {
                $alert = ['danger','Something wrong'];
                return back()->withAlert($alert);
            }
        }
        return $thumb;
    }

    protected function addExtraImage($request,$listing){
        if ($request->images) {
            foreach ($request->images as $key => $image) {
                try {
                    $imageData = [
                        'file'=>$image,
                        'path'=>'assets/images/listing/extra/',
                        'size'=>'730x484',
                        'prevFile'=>null,
                    ];
                    $image = $this->uploadFile('image',$imageData);
                } catch (Exception $e) {
                    $alert = ['danger','Something wrong'];
                    return back()->withAlert($alert);
                }
                $extraImage = new ExtraImage();
                $extraImage->listing_id = $listing->id;
                $extraImage->image = $image;
                $extraImage->save();
            }
        }
    }

public function cityCategoryArea($city, $categoryArea)
{
    // This route is an unconstrained two-segment catch-all, so it absorbs the
    // entire crawlable URL space — including thousands of invented city slugs
    // a day. Those were rejected only by the Location lookup further down,
    // several queries in; answering them from the cached slug whitelist keeps
    // a junk URL from costing a database connection at all, which is what was
    // draining the pool and getting real requests refused.
    $known = validRouteFirstSegments();

    if (!isset($known[$city]) && !isset($known[\Str::slug($city)])) {
        abort(404);
    }

 $serviceCategory = Category::where('slug', $city)->first();

    if ($serviceCategory) {
        $serviceKeyword = ServiceKeyword::where('category_id', $serviceCategory->id)
            ->where('slug', $categoryArea)
            ->where('status', 1)
            ->first();

        if ($serviceKeyword) {
            return app(SiteController::class)->serviceKeywordDetails($city, $categoryArea);
        }
    }
    $category = null;
    $subcategory = null;
    $area = null;

    if (str_contains($categoryArea, '-in-')) {
        [$serviceSlug, $areaSlug] = explode('-in-', $categoryArea, 2);
        $area = trim(str_replace('-', ' ', $areaSlug));

        // "/jaipur/courier-services-in-jaipur" is the same page as
        // "/jaipur/courier-services" — the area just repeats the city. That
        // produced two self-canonical URLs with identical content/meta
        // (a duplicate-description flag). Send the redundant one to the
        // clean city/category URL with a 301 so it consolidates.
        if (\Str::slug($areaSlug) === \Str::slug($city)) {
            return redirect('/' . $city . '/' . $serviceSlug, 301);
        }
    } else {
        $serviceSlug = $categoryArea;
    }

    $category = Category::where('status', 1)
        ->where('slug', $serviceSlug)
        ->first();

    if (!$category) {
        $subcategory = Subcategory::where('status', 1)
            ->where('slug', $serviceSlug)
            ->first();
    }

    if (!$category && !$subcategory) {
        abort(404);
    }

    $selectedItem = $subcategory ?? $category;

    $citySlug = \Str::slug($city);

    $locationData = Location::where('status', 1)
        ->where('slug', $citySlug)
        ->first();

    if (!$locationData) {
        abort(404);
    }

    $baseQuery = Listing::where('status', 1)
        ->where(function ($q) use ($locationData) {
            $q->where('location_id', $locationData->id)
              ->orWhereRaw('LOWER(city) = ?', [strtolower($locationData->name)]);
        })
        ->when($category, function ($q) use ($category) {
            $q->where('category_id', $category->id);
        })
        ->when($subcategory, function ($q) use ($subcategory) {
            $q->where(function ($qq) use ($subcategory) {
                $qq->whereJsonContains('subcategory_ids', (string) $subcategory->id)
                   ->orWhereJsonContains('subcategory_ids', $subcategory->id);
            });
        })
        ->with(['user', 'category', 'location'])
        ->withCount('ratings')
        ->withAvg('ratings', 'ratings');

    if (!empty($area)) {
        $areaWords = array_filter(explode(' ', strtolower($area)));

        $baseQuery->where(function ($q) use ($areaWords) {
            foreach ($areaWords as $word) {
                $q->where(function ($qq) use ($word) {
                    $qq->whereRaw("LOWER(listings.area) LIKE ?", ['%' . $word . '%'])
                       ->orWhereRaw("LOWER(listings.address) LIKE ?", ['%' . $word . '%'])
                       ->orWhereRaw("LOWER(listings.landmark) LIKE ?", ['%' . $word . '%']);
                });
            }
        });
    }

    $listings = $baseQuery
        ->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
        ->orderBy('top_rank', 'asc')
        ->orderBy('id', 'desc')
        ->get();

$currentCategoryId = $category
    ? $category->id
    : $subcategory->category_id;

$usedSubcategoryIds = $listings
    ->pluck('subcategory_ids')
    ->flatMap(function ($ids) {
        if (is_array($ids)) {
            return $ids;
        }

        return json_decode($ids, true) ?? [];
    })
    ->map(fn ($id) => (int) $id)
    ->filter()
    ->unique()
    ->values();

$relatedSubcategories = Subcategory::query()


    ->where('status', 1)
    ->where('category_id', $currentCategoryId)
    ->whereIn('id', $usedSubcategoryIds)
    ->whereNotNull('slug')
    ->where('slug', '!=', '')
    ->when($subcategory, function ($query) use ($subcategory) {
        $query->where('id', '!=', $subcategory->id);
    })
    ->orderBy('name')
    ->get()
    // Drop subcategories whose name names a city other than this URL's city
    // ("Car Rental For Nagpur" under /jaipur/). Rendered in the "Related
    // Categories" rail they become internal links to /{city}/{foreign-slug}
    // doorway pages — the same pages already served noindex and kept out of
    // the sitemap. Linking to them anyway keeps Google crawling a doorway
    // pattern that drags down sitewide quality, so they're filtered here at
    // the source, consistent with landerMentionsForeignCity() everywhere else.
    ->reject(fn ($sub) => landerMentionsForeignCity($sub->name, $locationData->name))
    ->values();

    $data['popularCities'] = activeLocations()
    ->where('is_popular', 1)
    ->sortBy('name')
    ->values();

/*
|--------------------------------------------------------------------------
| Category localities used in listings table
|--------------------------------------------------------------------------
*/

$localityQuery = Listing::query()
    ->where('status', 1)
    ->where(function ($query) use ($locationData) {
        $query->where('location_id', $locationData->id)
            ->orWhereRaw(
                'LOWER(TRIM(city)) = ?',
                [strtolower(trim($locationData->name))]
            );
    })
    ->whereNotNull('area')
    ->where('area', '!=', '');

if ($category) {
    $localityQuery->where('category_id', $category->id);
}

if ($subcategory) {
    $localityQuery->where(function ($query) use ($subcategory) {
        $query->whereJsonContains(
            'subcategory_ids',
            (string) $subcategory->id
        )->orWhereJsonContains(
            'subcategory_ids',
            $subcategory->id
        );
    });
}

$categoryLocalities = $localityQuery
    ->select('area')
    ->distinct()
    ->orderBy('area')
    ->pluck('area')
    ->map(fn ($areaName) => trim($areaName))
    ->filter()
    ->unique(fn ($areaName) => strtolower($areaName))
    ->values();



        $starListings = $listings
    ->where('is_star', 1)
    ->take(5)
    ->values();
    $locationText = !empty($area)
        ? trim(str_ireplace($locationData->name, '', ucwords($area))) . ', ' . $locationData->name
        : $locationData->name;

    session([
        'selected_location' => $locationText,
        'selected_category' => $selectedItem->name ?? ''
    ]);

    $data['listings'] = $listings;
$data['starListings'] = $starListings;
$data['categories'] = Category::where('status', 1)->get();
$data['selectedCategory'] = $subcategory ? null : $category;
$data['selectedSubcategory'] = $subcategory;
$data['headingName'] = $selectedItem->name;

$bannersQuery = Banner::where('status', 1)
    ->where(function ($q) use ($currentCategoryId) {
        $q->whereNull('category_id')
            ->orWhere('category_id', $currentCategoryId);
    })
    ->where(function ($q) use ($locationData) {
        $q->whereNull('city')
            ->orWhere('city', 'LIKE', "%{$locationData->name}%");
    });

$data['banners'] = $bannersQuery->orderBy('sort_order', 'asc')->latest()->get();
$data['title'] = $selectedItem->name . ' in ' . $locationText;
$data['location'] = $locationData;
$data['locationText'] = $locationText;
$data['relatedSubcategories'] = $relatedSubcategories;
$data['categoryLocalities'] = $categoryLocalities;

/*
 * A city x category/subcategory combination with too few listings is a real
 * page (it can still list other cities/categories to try) but it has no
 * actual content of its own — with ~60 cities x thousands of categories
 * most combinations are empty. Left indexable, Google renders it, finds
 * nothing there, and flags it as Soft 404 / "Crawled - currently not
 * indexed" / picks a different page as canonical — all symptoms of the
 * same root cause seen in Search Console. noindex here says upfront "don't
 * index this one", instead of Google reaching that conclusion itself after
 * spending crawl budget on every empty combination. `follow` is kept so
 * the related-categories/cities links on the page still get crawled.
 *
 * The threshold is shared with every other lander via config/seo.php.
 */
$data['seoRobots'] = \App\Support\PageIndexability::robots($listings->count());

// A lander whose name names a *different* city than this URL (e.g. a
// "...Delhi" keyword rendered under /jaipur/) is a doorway page — never
// index it under the wrong city, whatever its listing count.
if (landerMentionsForeignCity($selectedItem->name ?? null, $locationData->name)) {
    $data['seoRobots'] = 'noindex, follow';
}


$data['listing'] = $listings->first() ?? (object) [
    'name' => $selectedItem->name,
    'category_id' => $category->id ?? null,
    'location' => $locationData,
    'city' => $locationData->name,
    'area' => $area,
    'address' => $locationText,
    'distance' => null,
];

    // Service / FAQPage / ItemList JSON-LD + city-keyed title & description,
    // all derived from what this page already renders. See LanderSchema.
    \App\Support\LanderSchema::attach(
        $data,
        $selectedItem,
        $locationData->name,
        $listings,
        $listings->count(),
        $this->setting->site_name ?? config('app.name')
    );

    return view($this->theme . 'listings.all', $data);
}


public function deleteReview($id)
{
    // Laravel's query builder treats where('user_id', null) as
    // `user_id IS NULL`, which would otherwise match every guest-submitted
    // review whenever this is called by an unauthenticated visitor. Reject
    // that case explicitly instead of letting it fall through to the query.
    if (!auth()->check()) {
        return response()->json([
            'status'  => false,
            'message' => 'Unauthenticated',
        ], 401);
    }

    $rating = Rating::where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    $rating->delete();

    return response()->json([
        'status' => true
    ]);
}

}
