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

function loadImage($image,$isAvatar = false){
    return file_exists($image) && is_file($image) ? asset($image) : ($isAvatar ? asset('assets/images/avatar.jpg') : asset('assets/images/default.jpg'));
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
