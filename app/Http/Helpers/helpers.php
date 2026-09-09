<?php

use Illuminate\Support\Str;

function verification_code($length = 6){
	if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = 0;
    while ($length > 0 && $length--) {
        $max = ($max * 10) + 9;
    }
    return random_int($min, $max);
}

function menuActive($routename)
{
    if (request()->routeIs($routename)) {
        return [
            'sidebar-menu-active',
            'child-menu-active'
        ];
    }else{
        return [
            '',
            '',
        ];
    }
}

/**
 * Normalise a page/listing SEO title so the brand is consistent and never the
 * part Google truncates.
 *
 * Listing meta_titles are admin-authored and carry the brand in three
 * spellings ("Service Bazzar", "Services Bazzar", "ServiceBazzar"), often
 * pushing the title past ~60 chars so Google cuts the brand off anyway. This
 * strips whatever brand tail is present and re-appends the ONE canonical brand
 * only when it still fits within $max; otherwise the title keeps the business
 * name + keywords and drops the brand (which was getting truncated regardless).
 * It never hard-cuts mid-phrase — an over-long core is left for Google to
 * truncate cleanly in the SERP.
 */
function normalizeSeoTitle(?string $title, ?string $brand = null, int $max = 60): string
{
    $brand = $brand ?: 'Service Bazzar';
    $title = trim(preg_replace('/\s+/', ' ', (string) $title));
    if ($title === '') {
        return $brand;
    }

    // Drop a trailing brand tail in any spelling, with or without a separator
    // ("... | Service Bazzar", "... - services bazzar", "...ServiceBazzar").
    $core = preg_replace('/[\s|·\-–—,]*services?\s*bazzar\s*$/i', '', $title);
    $core = trim(rtrim($core, " \t|·-–—,;:."));
    if ($core === '') {
        $core = $title; // title was only the brand — keep it
    }

    $withBrand = $core . ' | ' . $brand;

    return mb_strlen($withBrand) <= $max ? $withBrand : $core;
}

/**
 * Does a lander's name reference a city OTHER than the one in its URL?
 *
 * The catalogue holds location-keyword entries like "PG with laundry service
 * Delhi" or "best Travel Agents in Noida". Rendered under /jaipur/ they become
 * "... in Jaipur" doorway pages — a Delhi keyword sitting on a Jaipur URL.
 * When that mismatch is detected the lander is served noindex (see the
 * controllers), so Google is told not to index the wrong-city page. The same
 * entry under its own city (/delhi/...) matches and stays indexable.
 *
 * Whole-word / whole-phrase match only, so "Punjabi" is never mistaken for a
 * city and a name that names its own URL city ("...Jaipur" under /jaipur/) is
 * not flagged. City list is cached — this runs on every lander render.
 */
function landerMentionsForeignCity(?string $name, ?string $urlCityName): bool
{
    $name = trim((string) $name);
    if ($name === '') {
        return false;
    }

    $cities = \Illuminate\Support\Facades\Cache::remember('lander:city_names:v1', now()->addHours(6), function () {
        return \App\Models\Location::where('status', 1)
            ->pluck('name')
            ->map(fn ($n) => trim((string) $n))
            ->filter(fn ($n) => mb_strlen($n) >= 3)
            ->unique()
            ->values()
            ->all();
    });

    $urlCityName = mb_strtolower(trim((string) $urlCityName));

    foreach ($cities as $city) {
        if (mb_strtolower($city) === $urlCityName) {
            continue; // the page's own city is not "foreign"
        }
        if (preg_match('/\b' . preg_quote($city, '/') . '\b/i', $name)) {
            return true;
        }
    }

    return false;
}

/**
 * Direct, non-redirecting URL for a category/subcategory lander.
 *
 * Prefer this over route('listings.category', $slug): that route points at
 * /listings/{slug}, which only 301-redirects to /{city}/{slug} — a wasted hop
 * on every internal category link (slower for users, double crawl cost for
 * Googlebot). This returns the final /{city}/{slug} straight away.
 *
 * On shared, full-page-cached pages the city can't be per-visitor server-side,
 * so it defaults to config('seo.default_city') and the .dynamic-category-link
 * JS rewrites it to the visitor's own location client-side. Pass $city
 * explicitly on pages that already know the city (a city/category page).
 */
function cityCategoryUrl($categorySlug, $city = null){
    $city = $city ?: config('seo.default_city', 'jaipur');
    return url('/' . \Illuminate\Support\Str::slug($city) . '/' . $categorySlug);
}

function loadImage($image,$isAvatar = false){
    return file_exists(public_path($image)) && is_file(public_path($image)) ? asset($image) : ($isAvatar ? asset('assets/images/avatar.jpg') : asset('assets/images/default.jpg'));
}

/**
 * Resolve a user's avatar for display: reviews, comments etc.
 *
 * $user->image is not always a local filename — a Google-OAuth signup
 * stores the full googleusercontent.com URL there, which was getting
 * concatenated onto 'assets/images/user/' and 404'ing (broken avatar
 * icon on reviews, e.g. Mohan Malhotra). This normalizes all three
 * cases: absolute URL, local filename, and no image at all.
 */
function userAvatarUrl($image) {
    if (!empty($image)) {
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }
        return asset('assets/images/user/'.$image);
    }
    return asset('assets/images/user82.avif');
}

/**
 * Return the WebP version of an image path if it exists on disk.
 * Strips the original extension and appends .webp.
 * Falls back to the original path if no WebP is found.
 */
function loadImageWebp($image, $isAvatar = false) {
    if ($image) {
        $webpPath = preg_replace('/\.(png|jpe?g|gif)$/i', '.webp', $image);
        if ($webpPath !== $image && file_exists(public_path($webpPath)) && is_file(public_path($webpPath))) {
            return asset($webpPath);
        }
    }
    return loadImage($image, $isAvatar);
}

function numberFormat($number,$precision = null){
    if ($precision) {
        $number = round($number,$precision);
    }else{
        $number = ($number + 0);
    }
    return $number;
}

function searchForKey($value ,$key, $array) {
    foreach ($array as $k => $val) {
       if (@$val[$key] == $value) {
           return $k;
       }
   }
   return null;
}

function trxNumber(){
    return Str::random();
}

if (! function_exists('english_location_name')) {
    function english_location_name($value)
    {
        $city = trim(preg_replace('/\s+/u', ' ', (string) $value));

        if ($city === '') {
            return '';
        }

        $map = [
            'जयपुर' => 'Jaipur',
            'दिल्ली' => 'Delhi',
            'नई दिल्ली' => 'New Delhi',
            'मुंबई' => 'Mumbai',
            'बेंगलुरु' => 'Bengaluru',
            'बैंगलोर' => 'Bengaluru',
            'हैदराबाद' => 'Hyderabad',
            'अहमदाबाद' => 'Ahmedabad',
            'चेन्नई' => 'Chennai',
            'कोलकाता' => 'Kolkata',
            'पुणे' => 'Pune',
            'सूरत' => 'Surat',
            'लखनऊ' => 'Lucknow',
            'कानपुर' => 'Kanpur',
            'नागपुर' => 'Nagpur',
            'इंदौर' => 'Indore',
            'भोपाल' => 'Bhopal',
            'पटना' => 'Patna',
            'वडोदरा' => 'Vadodara',
            'गाजियाबाद' => 'Ghaziabad',
            'लुधियाना' => 'Ludhiana',
            'आगरा' => 'Agra',
            'नासिक' => 'Nashik',
            'फरीदाबाद' => 'Faridabad',
            'मेरठ' => 'Meerut',
            'राजकोट' => 'Rajkot',
            'वाराणसी' => 'Varanasi',
            'श्रीनगर' => 'Srinagar',
            'औरंगाबाद' => 'Aurangabad',
            'धनबाद' => 'Dhanbad',
            'अमृतसर' => 'Amritsar',
            'नवी मुंबई' => 'Navi Mumbai',
            'प्रयागराज' => 'Prayagraj',
            'इलाहाबाद' => 'Prayagraj',
            'रांची' => 'Ranchi',
            'हावड़ा' => 'Howrah',
            'कोयंबटूर' => 'Coimbatore',
            'जबलपुर' => 'Jabalpur',
            'ग्वालियर' => 'Gwalior',
            'विजयवाड़ा' => 'Vijayawada',
            'जोधपुर' => 'Jodhpur',
            'मदुरै' => 'Madurai',
            'रायपुर' => 'Raipur',
            'कोटा' => 'Kota',
            'चंडीगढ़' => 'Chandigarh',
            'गुवाहाटी' => 'Guwahati',
            'मैसूर' => 'Mysuru',
            'उदयपुर' => 'Udaipur',
            'अजमेर' => 'Ajmer',
            'अलवर' => 'Alwar',
            'भीलवाड़ा' => 'Bhilwara',
            'बीकानेर' => 'Bikaner',
        ];

        if (isset($map[$city])) {
            return $map[$city];
        }

        if (str_contains($city, ',')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $city))));

            for ($i = count($parts) - 1; $i >= 0; $i--) {
                if (isset($map[$parts[$i]])) {
                    return $map[$parts[$i]];
                }
            }

            return end($parts) ?: $city;
        }

        return $city;
    }
}

function getDay($month,$year){
    $list=array();
    $month = $month;
    $year = $year;

    for($d=1; $d<=31; $d++)
    {
        $time=mktime(12, 0, 0, $month, $d, $year);
        if (date('m', $time)==$month)
            $list[]=date('d', $time);
    }
    return $list;

}

function listingRating($listing){
    $html = '';
    $review = floor($listing->review);
    $extra = abs($listing->review - $review);
    $extraDone = 0;
    for ($i=1; $i <= 5; $i++) {
        if ($review < $i) {
            if ($extra > 0 && $extraDone == 0) {
                $html .= '<i class="las la-star-half-alt fill"></i>';
                $extraDone = 1;
            }else{
                $html .= '<i class="las la-star"></i>';
            }
        }else{
            $html .= '<i class="las la-star fill"></i>';
        }
    }
    return $html;
}

function reviewerRatings($number){
    $html = '';
    $review = floor($number);
    $extra = abs($number - $review);
    $extraDone = 0;
    for ($i=1; $i <= 5; $i++) {
        if ($review < $i) {
            if ($extra > 0 && $extraDone == 0) {
                $html .= '<i class="las la-star-half-alt fill"></i>';
                $extraDone = 1;
            }else{
                $html .= '<i class="las la-star"></i>';
            }
        }else{
            $html .= '<i class="las la-star fill"></i>';
        }
    }
    return $html;
}

/*
 * Cached theme rows for the current theme.
 *
 * The footer, top header and app layout each ran their own
 * Theme::where(...)->first()/get() — 5 queries on every single page view for
 * rows that change only when an admin edits the theme. One cached collection
 * now serves all of them; SiteController::themeCollection() uses the same key
 * and TTL, so the two share the cache entry.
 */
function themeRows($themeName)
{
    if (empty($themeName)) {
        return collect();
    }

    return \Illuminate\Support\Facades\Cache::remember(
        "theme_data:{$themeName}",
        300,
        function () use ($themeName) {
            return \App\Models\Theme::where('theme_name', $themeName)->get();
        }
    );
}

/*
 * Active services for the menu/footer links. Same reasoning as themeRows():
 * the layout and footer both queried this on every request.
 */
function activeServices()
{
    return \Illuminate\Support\Facades\Cache::remember('active_services', 300, function () {
        return \App\Models\Service::where('status', 1)->get();
    });
}

/*
 * Active locations, cached.
 *
 * The footer view composer runs on every page and pulled the whole locations
 * table each time, on top of the copy the home/details controllers already
 * loaded. Callers that need a different order sort the cached collection in
 * PHP — it is a small table and sorting it costs nothing next to a query.
 *
 * Returned as a fresh collection so a caller's sort()/filter() cannot mutate
 * the cached instance.
 */
function activeLocations()
{
    $locations = \Illuminate\Support\Facades\Cache::remember('active_locations', 300, function () {
        return \App\Models\Location::where('status', 1)->orderBy('id', 'desc')->get();
    });

    return collect($locations->all());
}

/**
 * Every slug that may legitimately open a public two-segment URL.
 *
 * `/{city}/{categoryArea}` is an unconstrained catch-all, so it is the entire
 * public URL space — and crawlers walk it with first segments that were never
 * real (`guraong-ahmedabad`, `delhi-with-modern-equipment-thane`; 3,662 such
 * URLs in one day). Each one used to run several queries before the Location
 * lookup finally rejected it, and at crawl volume that is what drains the
 * connection pool until the account is refused new work altogether.
 *
 * The first segment is only ever a location (a city page) or a category (a
 * service-keyword page). Both tables are small — 60 locations — so the whole
 * valid set is held as a hash and an unknown slug is answered without opening
 * a database connection at all.
 *
 * Returned keyed by slug so callers test with isset() rather than in_array().
 */
function validRouteFirstSegments(): array
{
    return \Illuminate\Support\Facades\Cache::remember(
        'route_first_segments',
        300,
        function () {
            $slugs = array_merge(
                \App\Models\Location::where('status', 1)->pluck('slug')->all(),
                \App\Models\Category::where('status', 1)->pluck('slug')->all()
            );

            return array_fill_keys(array_filter($slugs), true);
        }
    );
}

/**
 * Retired category AND subcategory slugs -> their surviving slug, 301
 * (audit #42, plus any later one-off category rename).
 *
 * A bulk import once wrote subcategory rows with a keyword-stuffed slug
 * (many "X for Y" phrases concatenated together) instead of running them
 * through the admin controller's Str::slug($name); nine of those hit
 * 80-255 characters, one truncated mid-word at the varchar(255) limit.
 * Google had already indexed and was re-crawling those URLs, so renaming
 * the slug alone would have turned each into a fresh 404 — this map lets
 * cityCategoryArea() 301 the old URL to the current one instead (or, for
 * the three that were exact-name duplicates of an existing clean
 * subcategory, to that survivor).
 *
 * Also holds plain category renames (e.g. "house-keeping-services" ->
 * "housekeeping-services") for the same reason — cityCategoryArea() checks
 * this map right after both the category and subcategory lookups miss.
 *
 * Small and fixed, so a plain array is cheaper than a cached query.
 */
function retiredSubcategorySlugs(): array
{
    return [
        'house-keeping-services' => 'housekeeping-services',
        'event-organisers-for-navratri-audition-organisers-event-organisers-for-corporate-event-organisers-for-holi-street-play-organisers-puppet-show-organisers-rain-dance-organisers-firework-show-organisers-dance-competition-organisers-music-concert-organisers-e' => 'event-organisers',
        'best-photographer-in-kolkata-wedding-photographer-pre-wedding-photography-baby-shower-photography-kids-photography-food-photography-cinematic-wedding-film' => 'best-photographer-in-kolkata',
        'business-stay-corporate-stay-executive-stay-vacation-home-holiday-home-homestay-serviced-apartment-fully-furnished-apartment-pet-friendly-airbnb-weekend-stay-in-mumbai' => 'business-stay',
        'business-stay-corporate-stay-executive-stay-vacation-home-holiday-home-homestay-serviced-apartment-fully-furnished-apartment-pet-friendly-airbnb-weekend-stay' => 'business-stay',
        'on-site-eye-testing-and-mobile-optical-services-across-delhi-for-convenient-vision-care' => 'on-site-eye-testing-and-mobile-optical',
        'advanced-lens-coatings-such-as-anti-glare-blue-light-filter-and-scratch-resistant-options' => 'advanced-lens-coatings-such-as-anti-glare',
        'customized-prescription-optical-lenses-including-single-vision-bifocal-and-progressive-lenses' => 'customized-prescription-optical-lenses',
        'job-placement-services-for-freshers-and-experienced-candidates-across-multiple-industries' => 'job-placement-services-for-freshers-and',
        'recruitment-and-staffing-solutions-for-companies-including-permanent-and-temporary-hiring' => 'recruitment-and-staffing-solutions-for',
        'career-support-services-such-as-resume-building-interview-preparation-and-job-counseling' => 'career-support-services-such-as-resume',
        'recruitment-solutions-for-companies-including-permanent-temporary-and-contract-staffing' => 'recruitment-solutions-for-companies',
        'codleo-consulting-trusted-partner-for-digital-transformation-and-crm-success-in-delhi' => 'codleo-consulting',
        'business-stay-mumbai-weekend-stay-mumbai-vacation-home-mumbai-homestay-mumbai-serviced-apartment-mumbai-premium-airbnb-mumbai-pet-friendly-airbnb-mumbai' => 'business-stay-mumbai',
        'rainwear-dealers-mumbai-umbrella-dealers-mumbai-raincoat-shop-mumbai-rainwear-store-mumbai-umbrella-shop-mumbai-waterproof-rainwear-mumbai-raincoat-suppliers-mumbai-monsoon-wear-mumbai-rain-protection-accessories-mumbai-rain-gear-mumbai' => 'rainwear-dealers-mumbai',
        'hotel-with-free-wifi-in-mumbai-hotel-with-parking-in-mumbai-couple-friendly-hotel-in-mumbai-family-stay-in-mumbai-hotel-for-business-travelers-in-mumbai-weekend-stay-hotel-in-mumbai' => 'hotel-with-free-wifi-in-mumbai',
        'best-wedding-band-mumbai-wedding-bands-mumbai-mumbai-wedding-band-marriage-band-mumbai-brass-band-mumbai-baraat-band-mumbai-wedding-music-band-mumbai-professional-wedding-band-mumbai-premium-wedding-band-mumbai' => 'best-wedding-band-mumbai',
        'decorators-event-organisers-for-arm-wrestling-event-organisers-for-bachelorette-party-event-organisers-for-bhajan-sandhya-event-organisers-for-birthday-party-event-organisers-for-concert-event-organisers-for-corporate-event-organisers-for-dj-event-organis' => 'decorators-event-organisers',
    ];
}
