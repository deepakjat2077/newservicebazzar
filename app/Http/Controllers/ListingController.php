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
    $listingQuery = Listing::where('status', 1);

    if (!empty($locationId)) {
        $listingQuery->where('location_id', $locationId);
    }

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
    ->get();

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
        ? ucwords($area) . ', ' . $locationData->name
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
 * A city x category/subcategory combination with zero listings is a real
 * page (it can still list other cities/categories to try) but it has no
 * actual content of its own — with ~60 cities x thousands of categories
 * most combinations are empty. Left indexable, Google renders it, finds
 * nothing there, and flags it as Soft 404 / "Crawled - currently not
 * indexed" / picks a different page as canonical — all symptoms of the
 * same root cause seen in Search Console. noindex here says upfront "don't
 * index this one", instead of Google reaching that conclusion itself after
 * spending crawl budget on every empty combination. `follow` is kept so
 * the related-categories/cities links on the page still get crawled.
 */
if ($listings->isEmpty()) {
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
