
@extends($themeView.'layouts.site')
@php
    $ogTitle = 'Service Bazzar — Find Trusted Local Businesses Near You';
    $ogDescription = 'Discover and connect with verified local businesses across India — services, healthcare, repairs, restaurants and professionals. Compare ratings and prices, then contact them directly. Free to use.';
@endphp

@section('content')
@section('title')
Local Businesses & Trusted Services Near You
@endsection
@include($themeView.'includes.rich-content')

@php
    $currentLocationName = request('location_name');
    $currentLatitude = request('latitude');
    $currentLongitude = request('longitude');
@endphp

@php
    $usedCategoryIds = [];
@endphp

@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-01.css'])
<script>
(function () {
    var SEL = '.trending-same-section .trend-card-title, .location-same-section .trend-card-title, .seasonal-same-section .trend-card-title, .popular-same-section .trend-card-title';
    function overflows(el) {
        return el.scrollWidth > el.clientWidth + 1 || el.scrollHeight > el.clientHeight + 1;
    }
    function fit() {
        if (window.innerWidth <= 767) return; 
        document.querySelectorAll(SEL).forEach(function (el) {
            el.style.removeProperty('font-size');
            el.style.removeProperty('white-space');
            if (!el.clientWidth) return;
            var size = parseFloat(getComputedStyle(el).fontSize);
            var guard = 0;
            while (overflows(el) && size > 9 && guard++ < 12) {
                size -= 0.5;
                el.style.setProperty('font-size', size + 'px', 'important');
            }
            el.style.removeProperty('letter-spacing');
            if (overflows(el)) el.style.setProperty('letter-spacing', '-0.3px', 'important');
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fit); else fit();
    window.addEventListener('load', fit);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
    var t; window.addEventListener('resize', function () { clearTimeout(t); t = setTimeout(fit, 150); });
})();
</script>
@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-02.css'])

@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-03.css'])
@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-04.css'])
<div id="sbLocNotFound" class="sb-locnf" role="alert" hidden>
    <span class="sb-locnf-text"></span>
    <button type="button" class="sb-locnf-x" aria-label="Close">&times;</button>
</div>
@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-05.css'])
<script>

(function () {
    var params = new URLSearchParams(location.search);
    if (!params.has('location_not_found')) return;
    var bad = (params.get('location_not_found') || '').trim();
    function run() {
        var msgText = (bad ? '"' + bad + '" is not a location we cover. ' : 'That location is not one we cover. ') + 'Please pick your city or area from the list.';
        var heroInput = document.getElementById('homeLocationInput');
        var box = document.getElementById('sbLocNotFound');
        if (heroInput && heroInput.offsetParent !== null && window.sbShowLocError) {
            heroInput.value = '';
            window.sbShowLocError(heroInput, msgText);
            box = null;
        }
        if (box) {
            box.querySelector('.sb-locnf-text').textContent = msgText;
            box.hidden = false;
            box.querySelector('.sb-locnf-x').addEventListener('click', function () { box.hidden = true; });
            setTimeout(function () { box.hidden = true; }, 9000);
        }
        try {
            ['jf_city', 'jf_detected_city', 'jf_selected_location_name', 'jf_user_city', 'jf_location_id'].forEach(function (k) {
                var v = localStorage.getItem(k);
                if (v && (!window.sbKnownCity || !window.sbKnownCity(v))) localStorage.removeItem(k);
            });
        } catch (e) {}
        ['homeLocationInput', 'headerLocationInput'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && (!window.sbKnownCity || !window.sbKnownCity(el.value))) { el.value = ''; el.dataset.selectedLocation = '0'; }
        });
        var target = document.getElementById('homeLocationInput');
        if (target && target.offsetParent !== null) target.focus();
        params.delete('location_not_found');
        history.replaceState(null, '', location.pathname + (params.toString() ? '?' + params : '') + location.hash);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { setTimeout(run, 50); });
    else setTimeout(run, 50);
})();
</script>
@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-06.css'])
<script>
document.addEventListener('DOMContentLoaded', function () {
    var header = document.getElementById('stickyScrollHeader');
    if (!header) return;
    var pairs = [
        ['homeLocationInput', 'headerLocationInput'],
        ['homeCategorySearch', 'headerCategorySearch'],
        ['homeSearchLatitude', 'headerSearchLatitude'],
        ['homeSearchLongitude', 'headerSearchLongitude'],
        ['homeSearchLocationId', 'headerSearchLocationId']
    ].map(function (p) { return [document.getElementById(p[0]), document.getElementById(p[1])]; })
     .filter(function (p) { return p[0] && p[1]; });

    function copy(toHeader) {
        pairs.forEach(function (p) {
            var from = toHeader ? p[0] : p[1], to = toHeader ? p[1] : p[0];
            if (from.value && from.value !== to.value) to.value = from.value;
            if (from.type === 'text' && from.placeholder && to.type === 'text' && (from.id || '').indexOf('Category') > -1) to.placeholder = from.placeholder;
        });
    }
    var was = header.classList.contains('scrolled');
    copy(true);
    new MutationObserver(function () {
        var now = header.classList.contains('scrolled');
        if (now !== was) { copy(now); was = now; }
    }).observe(header, { attributes: true, attributeFilter: ['class'] });
});
</script>
<section class="banner-section">
    <div class="container-fulde">
             <div class="row g-0">
<div class="col-md-8 custom-right">
    
   <div class="title-area ll text-left">

   <h1 class="hero-title">
    Discover Trusted
    <span class="text-slider">
        <span>
            <strong>Local Services</strong>
            <small>{{ !empty($cityHomeName) ? 'in ' . $cityHomeName : 'Near Your Location' }}</small>
        </span>
        <span>
            <strong>Verified Businesses</strong>
            <small>{{ !empty($cityHomeName) ? 'in ' . $cityHomeName : 'Across India' }}</small>
        </span>

    </span>

</h1>

</div>

    <div class="search-area" id="homeSearchOriginalPlace">
     <form action="{{ route('listings.search') }}" method="GET" id="homeSearchForm">
         <div class="search-row-custom">

<div class="location-col-custom mobile-home-location-box" id="mobileHomeLocationBox">
    <div class="input_location_box">
      

<input type="text"
    name="location_name"
    id="homeLocationInput"
    autocomplete="off"
    placeholder="Search location..."
    value=""
    class="input_location"
    role="combobox"
    aria-label="Search location"
    aria-autocomplete="list"
    aria-expanded="false"
    aria-controls="homeLocationSuggestions">

         
        <input type="hidden" name="latitude" id="homeSearchLatitude" value="{{ request()->latitude }}">
        <input type="hidden" name="longitude" id="homeSearchLongitude" value="{{ request()->longitude }}">
        <input type="hidden" name="location_id" id="homeSearchLocationId" value="">

        {{-- JavaScript populates autocomplete suggestions here via AJAX. If JS fails,
             the plain text input field above still works for GET submission, and the
             server's /listings/search route will match it against location names. --}}

        <div id="homeLocationSuggestions" class="suggestion-box"></div>

        <noscript>
            <small style="display: block; margin-top: 6px; color: #666;">
                ✓ Location search works without JavaScript — just type and submit the form.
            </small>
        </noscript>
    </div>
</div>

<script>
window.SB = {
    csrf: "{{ csrf_token() }}",
    routes: {
        popularCategories:   "{{ route('ajax.popular.categories') }}",
        categorySuggestions: "{{ route('category.suggestions') }}",
        nearbySuggestions:   "{{ route('nearby.location.suggestions') }}",
        saveUserLocation:    "{{ route('save.user.location') }}"
    },
    img: {
        def:      "{{ asset('assets/images/default.jpg') }}",
        trending: "{{ asset('assets/images/ffffjndjs.svg') }}",
        sbPng:    "{{ asset('assets/servicebazzar.png') }}",
        sbWebp:   "{{ asset('assets/servicebazzar.webp') }}"
    }
};
</script>
<script src="{{ asset($themeAsset.'js/home.js') }}?v={{ filemtime(public_path($themeAsset.'js/home.js')) }}" defer></script>
      <div class="search-col-custom">
    <div class="input_search_box custom-search-wrap">

        <input type="text"
            name="categorysearch"
            id="homeCategorySearch"
            autocomplete="off"
            placeholder="Search for Packers and Movers"
            value=""
            class="input_search"
            role="combobox"
            aria-label="Search category"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="homeCategorySuggestions">

        <!-- Custom Clear Icon -->
        <span class="custom-clear-btn" id="customClearBtn">
            <i class="fas fa-times"></i>
        </span>
         <span class="voice-search-btn" id="homeVoiceSearchBtn" title="Voice Search">
    <i class="fas fa-microphone"></i>
</span>
<div class="search_btnbox">
    <button type="submit" class="search_buttonh" aria-label="search">
        <i class="fas fa-search"></i>
    </button>

</div>

       
        <noscript>
        
            <div style="margin-top: 8px;">
                <div style="display: flex; gap: 4px; align-items: center;">
                   
                    <select name="categorysearch" id="noScriptCategoryFallback"
                            style="flex: 1; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Select a popular category</option>
                        @foreach(\App\Models\Category::where('status', 1)->orderBy('name')->limit(20)->get() as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" style="padding: 6px 14px; border: 1px solid #ddd; border-radius: 4px; background: #fff; cursor: pointer;">
                        Search
                    </button>
                </div>
            </div>
        </noscript>

        <ul id="homeCategorySuggestions" class="shadow" role="listbox"></ul>

    </div>
</div>



           
            </div>

            <div id="searchError" style="color:#dc3545; font-size:14px; margin-top:8px; display:none;">
    Please enter category / subcategory name for search.
</div>
        </form>
    </div>

</div>
        </div>
    </div>

    <div class="banner-download-btn desktop-only">
    <a href="https://play.google.com/store/apps/details?id=com.servicebazaar" class="download-btn"
       target="_blank" rel="noopener" aria-label="Download the Service Bazzar app on Google Play">
        Download App
        <i class="fas fa-download"></i>
    </a>
</div>


</section>
<!-- Banner Section End -->


<section class="custom-banner-section ">
    <div class="container-fulde">
        <div class="row g-3 align-items-stretch mobile-banner-row">

            {{-- Left Big Slider --}}
            <div class="col-lg-5 col-12 add-right mobile-banner-col">
                <div id="mainBannerCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
                    <div class="main-banner-box carousel-inner">

                        @php
                            $rawImage = $banner->content->image ?? null;
                            $rawMobileImage = $banner->content->mobile_banner ?? null;

                            $bannerImages = [];
                            $mobileBannerImages = [];

                            if (!empty($rawImage)) {
                                $decoded = json_decode($rawImage, true);

                                if (is_array($decoded)) {
                                    foreach ($decoded as $row) {
                                        if (is_array($row)) {
                                            $bannerImages[] = [
                                                'image' => $row['image'] ?? '',
                                                'link'  => $row['link'] ?? ''
                                            ];
                                        } else {
                                            $bannerImages[] = [
                                                'image' => $row,
                                                'link'  => ''
                                            ];
                                        }
                                    }
                                }
                            }

                            if (!empty($rawMobileImage)) {
                                $decodedMobile = json_decode($rawMobileImage, true);

                                if (is_array($decodedMobile)) {
                                    foreach ($decodedMobile as $row) {
                                        if (is_array($row)) {
                                            $mobileBannerImages[] = [
                                                'image' => $row['image'] ?? '',
                                                'link'  => $row['link'] ?? ''
                                            ];
                                        } else {
                                            $mobileBannerImages[] = [
                                                'image' => $row,
                                                'link'  => ''
                                            ];
                                        }
                                    }
                                }
                            }
                        @endphp

                            {{-- Preload the first (LCP) banner image in <head> --}}
                            @php
                            $firstBannerSrc    = !empty($bannerImages[0]['image'])
                                ? 'assets/servicebazzar/images/banner/'.$bannerImages[0]['image']
                                : null;
                            $firstMobileSrc    = !empty($mobileBannerImages[0]['image'])
                                ? 'assets/servicebazzar/images/banner/'.$mobileBannerImages[0]['image']
                                : $firstBannerSrc;
                            @endphp

                        {{-- Inject LCP preload links into <head> via stack --}}
                        @if($firstBannerSrc)
                            @push('lcp')
                                {{-- Mobile LCP preload (WebP preferred) --}}
                                <link rel="preload" as="image"
                                    href="{{ narrowWebp($firstMobileSrc) }}"
                                    imagesrcset="{{ narrowWebp($firstMobileSrc) }}"
                                    media="(max-width: 767px)"
                                    fetchpriority="high">
                                {{-- Desktop LCP preload (WebP preferred) --}}
                                <link rel="preload" as="image"
                                    href="{{ loadImageWebp($firstBannerSrc) }}"
                                    imagesrcset="{{ loadImageWebp($firstBannerSrc) }}"
                                    media="(min-width: 768px)"
                                    fetchpriority="high">
                            @endpush
                        @endif

                        @foreach($bannerImages as $imgData)
                            @php
                                $mobileImgData = $mobileBannerImages[$loop->index] ?? null;
                                $isFirst        = $loop->first;
                                $loadPriority   = $isFirst ? 'eager' : 'lazy';
                                $fetchPriority  = $isFirst ? 'high'  : 'low';
                                $desktopSrc     = 'assets/servicebazzar/images/banner/'.$imgData['image'];
                                $mobileSrc      = !empty($mobileImgData['image'])
                                                    ? 'assets/servicebazzar/images/banner/'.$mobileImgData['image']
                                                    : $desktopSrc;
                            @endphp

                            @php
                             
                                $bannerLink    = $imgData['link'] ?? '';
                                $bannerCatSlug = '';
                                if (!empty($bannerLink)) {
                                    $bHost = parse_url($bannerLink, PHP_URL_HOST);
                                    $bPath = trim((string) parse_url($bannerLink, PHP_URL_PATH), '/');
                                    $bSegs = $bPath === '' ? [] : explode('/', $bPath);
                                    $reservedFirst = ['listings','blogs','blog','user','admin','dashboard','category','search','contact','location','free-listing','pricing','popular-services'];
                                    if ((!$bHost || $bHost === request()->getHost())
                                        && count($bSegs) === 2
                                        && !in_array(strtolower($bSegs[0]), $reservedFirst, true)) {
                                        $bannerCatSlug = $bSegs[1];
                                    }
                                }
                            @endphp

                            <div class="carousel-item {{ $isFirst ? 'active' : '' }}">
                                <div class="main-banner-box">

                                    @if(!empty($bannerLink))
                                        <a href="{{ $bannerLink }}"
                                           @if($bannerCatSlug) class="dynamic-category-link" data-slug="{{ $bannerCatSlug }}" @endif>
                                    @endif

                                        {{-- Desktop banner — WebP with fallback --}}
                                        <picture class="desktop-banner-img">
                                            <source srcset="{{ loadImageWebp($desktopSrc) }}" type="image/webp">
                                            <img width="1200" height="534"
                                                src="{{ loadImage($desktopSrc) }}"
                                                loading="{{ $loadPriority }}"
                                                fetchpriority="{{ $fetchPriority }}"
                                                decoding="async"
                                                alt="{{ $banner->content->title }}"
                                                class="desktop-banner-img">
                                        </picture>

                                        {{-- Mobile banner — WebP with fallback --}}
                                        <picture class="mobile-banner-img">
                                            {{-- phones get an 800px copy (the banner is ~400px wide there) --}}
                                            <source srcset="{{ narrowWebp($mobileSrc) }}" type="image/webp">
                                            <img width="1200" height="534"
                                                src="{{ loadImage($mobileSrc) }}"
                                                loading="{{ $loadPriority }}"
                                                fetchpriority="{{ $fetchPriority }}"
                                                decoding="async"
                                                alt="{{ $isFirst ? 'Best' : 'Find' }} {{ $banner->content->title }}"
                                                class="mobile-banner-img">
                                        </picture>

                                    @if(!empty($bannerLink))
                                        </a>
                                    @endif

                                </div>
                            </div>
                        @endforeach

                    </div>

                    @if(count($bannerImages) > 1)
                        <a class="carousel-control-prev" href="#mainBannerCarousel" role="button" data-slide="prev" aria-label="Previous banner">
                            <span class="carousel-control-prev-icon"></span>
                        </a>
                        <a class="carousel-control-next" href="#mainBannerCarousel" role="button" data-slide="next" aria-label="Next banner">
                            <span class="carousel-control-next-icon"></span>
                        </a>
                    @endif
                </div>
            </div>

     @php
   
    $fixedCategories = [
        'packers-and-movers',
        'real-estate',
        'chartered-accountant-ca',
        'hotel'
    ];

    $topCategories = $categories->whereIn('slug', $fixedCategories);

    $bgColors = [
        'packers-and-movers' => '#2654A1',
        'real-estate' => '#6769D0',
        'chartered-accountant-ca' => '#008561',
        'hotel' => '#0E77CD',
    ];
@endphp


{{-- Right 4 Cards --}}
<div class="col-lg-6 col-12 mobile-category-col">
    <div class="row g-3 mobile-category-row">

        @foreach($fixedCategories as $slug)

            @php
               $categoryItem = $topCategories->firstWhere('slug', $slug);

                if(!$categoryItem){
                    continue;
                }

                $usedCategoryIds[] = $categoryItem->id;

                $bgColor = $bgColors[$slug] ?? '#0E77CD';
            @endphp

            <div class="col-6 col-md-3 mobile-category-item">

                <a href="{{ cityCategoryUrl($categoryItem->slug) }}"
                   class="mini-service-card dynamic-category-link"
                   data-slug="{{ $categoryItem->slug }}"
                   style="background-color: {{ $bgColor }}; --c: {{ $bgColor }}; display:block; height:100%;">

                    {{-- The category name is right below in .mini-card-title, so
                         repeating it in alt makes a screen reader say it twice. --}}
                    <img width="300" height="200" src="{{ loadImage('assets/images/category/'.$categoryItem->image) }}"
                         loading="lazy"
                         alt="">

                    <div class="mini-card-title">
                        {{ $categoryItem->name }}
                    </div>

                    <div class="home_banner_subtext" style="color:#FFF">
                        Get Nearest Vendor
                    </div>

                    <div class="home_banner_explore font11" style="background-color: {{ $bgColor }};">
                        <div class="home_banner_exploretxt" style="color:white;">
                            Explore
                        </div>

                        <div class="home_banner_explorearw font11" style="color:#fff;"><i class="las la-arrow-right"></i></div>
                    </div>

                </a>

            </div>

        @endforeach

    </div>
</div>

        </div>
    </div>
</section>


    
@php
    // Pehle woh categories jo pehle use nahi hui hain
    $unusedCategories = $categories
        ->whereNotIn('id', $usedCategoryIds)
        ->values();

    // Agar unused categories 11 se kam hain,
    // to baaki categories se list complete karo
    $categoryItems = $unusedCategories
        ->concat(
            $categories->whereNotIn('id', $unusedCategories->pluck('id'))
        )
        ->unique('id')
        ->take(19)
        ->values();
@endphp

<input type="checkbox" id="categoryPopupToggle" class="category-popup-toggle" aria-hidden="true" tabindex="-1">

<section class="custom-right category-area py-5">
    <div class="container-fildu">

        <div class="category-grid mt-4">

            @foreach($categoryItems as $index => $categoryItem)

                @php
                    if (!in_array($categoryItem->id, $usedCategoryIds)) {
                        $usedCategoryIds[] = $categoryItem->id;
                    }
                @endphp

                <div class="category-item text-center {{ $index >= 11 ? 'hide-mobile' : '' }}">
                    <a href="{{ cityCategoryUrl($categoryItem->slug) }}"
                       class="text-decoration-none text-dark dynamic-category-link"
                       data-slug="{{ $categoryItem->slug }}">

                        <div class="category-box d-flex align-items-center justify-content-center size">

                            @if(
                                $categoryItem->icon &&
                                Str::endsWith(
                                    strtolower($categoryItem->icon),
                                    ['.png', '.jpg', '.jpeg', '.svg', '.webp']
                                )
                            )
                                <img width="60" height="60"
                                    src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                                    loading="lazy"
                                    alt="Top {{ $categoryItem->name }}"
                                    class="category-icon-img"
                                >
                            @else
                                <i class="{{ $categoryItem->icon }}"></i>
                            @endif

                        </div>

                        <div class="font14 color111 fw400">
                            {{ $categoryItem->name }}
                        </div>
                    </a>
                </div>

            @endforeach

            <div class="category-item text-center more-category-item">
             
                <label
                    for="categoryPopupToggle"
                    class="more-btn"
                    tabindex="0"
                >
                    <div class="category-box d-flex align-items-center justify-content-center">
                        <i class="fas fa-bars more"></i>
                    </div>

                    <div class="font14 color111 fw400">
                        More Categories
                    </div>
                </label>
            </div>

        </div>
    </div>
</section>

<section class="featured pt-4 pb-4">
   
    @include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-07.css'])
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="section-title">
                    <h2 class="hh-h4-size">{{ __($featured_listing->content->title) }}</h2>
                </div>
            </div>
        </div>

   
<div class="featured-listing-slider">
    @foreach($featureds as $featured)

        @php
            $mobileNumbers = is_array($featured->mobile_numbers)
                ? $featured->mobile_numbers
                : (json_decode($featured->mobile_numbers, true) ?: []);

            $whatsappNumbers = is_array($featured->whatsapp_numbers)
                ? $featured->whatsapp_numbers
                : (json_decode($featured->whatsapp_numbers, true) ?: []);

            $mobilePhone = $mobileNumbers[0] ?? '';
            $whatsappPhone = $whatsappNumbers[0] ?? '';

            $cleanMobilePhone = preg_replace('/[^0-9]/', '', $mobilePhone);
            $cleanWhatsappPhone = preg_replace('/[^0-9]/', '', $whatsappPhone);

            // Service Bazzar rating
            $rating = 0;
            $ratingCount = 0;

            if (isset($featured->review) && is_numeric($featured->review)) {
                $rating = (float) $featured->review;
            } elseif (isset($featured->avg_rating) && is_numeric($featured->avg_rating)) {
                $rating = (float) $featured->avg_rating;
            } elseif (isset($featured->rating) && is_numeric($featured->rating)) {
                $rating = (float) $featured->rating;
            } elseif (isset($featured->verified_ratings_avg) && is_numeric($featured->verified_ratings_avg)) {
                $rating = (float) $featured->verified_ratings_avg;
            }

            if (isset($featured->total_review) && is_numeric($featured->total_review)) {
                $ratingCount = (int) $featured->total_review;
            } elseif (isset($featured->review_count) && is_numeric($featured->review_count)) {
                $ratingCount = (int) $featured->review_count;
            } elseif (isset($featured->ratings_count) && is_numeric($featured->ratings_count)) {
                $ratingCount = (int) $featured->ratings_count;
            } elseif (isset($featured->verified_ratings_count) && is_numeric($featured->verified_ratings_count)) {
                $ratingCount = (int) $featured->verified_ratings_count;
            }

            // Google rating and review count
            $gRating = (float) ($featured->gmb_rating ?? 0);
            $gCount = (int) ($featured->gmb_review_count ?? 0);

            // Combine Service Bazzar and Google ratings
            $existingRatingSum = $rating * $ratingCount;
            $googleRatingSum = $gRating * $gCount;
            $combinedCount = $ratingCount + $gCount;

            if ($combinedCount > 0) {
                $rating = round(
                    ($existingRatingSum + $googleRatingSum) / $combinedCount,
                    1
                );
            } else {
                $rating = 0;
            }

            $ratingCount = $combinedCount;

            // Years in business
            $yearsInBusiness = null;

            if (!empty($featured->established_year) && is_numeric($featured->established_year)) {
                $yearsInBusiness = date('Y') - (int) $featured->established_year;
            }
        @endphp

        <div class="listing-area"
             data-url="{{ route('listings.details', [
                'category' => optional($featured->category)->slug,
                'slug' => $featured->slug
             ]) }}"
             style="cursor:pointer;">

            <div class="thumb">
                <img width="300" height="225"
                     src="{{ loadImage('assets/images/listing/thumb/'.$featured->thumb) }}"
                     loading="lazy"
                     alt="{{ $featured->name }}"
                     class="listing-thumb">
            </div>

            <div class="listing-content">

                <h2 class="title align-items-center flex-wrap">
                    @if(($featured->is_star ?? 0) == 1)
                        <i class="fas fa-star ico"></i>
                    @endif

                    <a href="{{ route('listings.details', [
                        'category' => optional($featured->category)->slug,
                        'slug' => $featured->slug
                    ]) }}">
                        {{ __($featured->name) }}
                    </a>
                </h2>

                <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap">

                    @if($rating > 0)
                        <span class="jd-rating-badge">
                            {{ number_format($rating, 1) }} ★
                        </span>
                    @endif

                    @if($ratingCount > 0)
                        <span class="jd-rating-count">
                            {{ $ratingCount }} Ratings
                        </span>
                    @endif

                    @if(($featured->trust ?? 0) == 1)
                        <img src="{{ asset('assets/images/trust_2x.gif') }}"
                             class="jd-badge-img"
                             loading="lazy"
                             decoding="async"
                             alt="Trust">
                    @endif

                    @if(($featured->verified ?? 0) == 1)
                        <img src="{{ asset('assets/images/verified_2x.gif') }}"
                             class="jd-badge-img"
                             loading="lazy"
                             decoding="async"
                             alt="Verified">
                    @endif

                </div>

                <div class="listing-location">
                    <i class="las la-map-marker"></i>
                    <span>{{ $featured->location->name ?? '' }}</span>

                    @if(!empty($yearsInBusiness) && $yearsInBusiness > 0)
                        <span class="dot-separator">•</span>
                        <span class="business-years">
                            <i class="las la-briefcase"></i>
                            {{ $yearsInBusiness }} Years in Business
                        </span>
                    @endif
                </div>

                @if(!empty($cleanMobilePhone) || !empty($cleanWhatsappPhone))
                    <div class="listing-contact mt-3">

                        @if(!empty($cleanMobilePhone))
                            <a href="tel:{{ $cleanMobilePhone }}" class="btn-call">
                                <i class="las la-phone"></i>
                                Call Now
                            </a>
                        @endif

                        @if(!empty($cleanWhatsappPhone))
                            <a href="https://wa.me/{{ $cleanWhatsappPhone }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn-whatsapp">
                                <i class="lab la-whatsapp"></i>
                                WhatsApp
                            </a>
                        @endif

                    </div>
                @endif

            </div>
        </div>

    @endforeach
</div>

    </div>
</section>



<label class="popup-overlay" id="popupOverlay" for="categoryPopupToggle" aria-label="Close categories"></label>

<!-- Panel -->
<div class="category-popup" id="categoryPopup">
    <div class="popup-top">
     
        <div class="d-flex align-items-center">
            <label for="categoryPopupToggle" class="close-btn mr-3" tabindex="0">✕</label>
            <h3 class="mb-0 hh-h5-size">Popular Categories</h3>
        </div>

        
        <div class="search-wrapper">
            <input type="text" id="categorySearch"
                   placeholder="Search"
                   class="search-input">
            <span class="clear-search" role="button" tabindex="0" aria-label="Clear search" onclick="clearSearch()" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();clearSearch();}">✕</span>
        </div>
    </div>

  
    <div class="popup-location-notice" id="popupLocationNotice" hidden>
        <button type="button" class="popup-location-notice__close" id="popupLocationNoticeClose" aria-label="Close location popup">
            &times;
        </button>

        <div class="popup-location-notice__row">
            <div class="popup-location-notice__text">
                <i class="fas fa-location-dot"></i>
                <span id="popupLocationNoticeMsg">Location is turned off. Turn it on to see services near you.</span>
            </div>
            <button type="button" class="popup-location-notice__btn" id="popupLocationEnableBtn">
                Turn on location
            </button>
        </div>

       
        <ol class="popup-location-notice__steps" id="popupLocationSteps" hidden>
            <li>Click the lock or info icon next to the website address.</li>
            <li>Open <strong>Site settings</strong> (or <strong>Permissions</strong>).</li>
            <li>Set <strong>Location</strong> to <strong>Allow</strong>.</li>
            <li>Reload this page.</li>
        </ol>
    </div>

    
   @php
    
    $rotationCities = function_exists('activeLocations')
        ? activeLocations()->where('is_popular', 1)->pluck('name')->values()
        : collect();
    $fallbackCity = $rotationCities->isNotEmpty()
        ? $rotationCities[(int) date('z') % $rotationCities->count()]
        : 'Jaipur';

    $currentCity = $locationText ?? session('detected_city') ?? $fallbackCity;

    if (str_contains($currentCity, ',')) {
        $parts = explode(',', $currentCity);
        $currentCity = trim(end($parts));
    }

    $currentCitySlug = \Str::slug($currentCity);
@endphp

<div class="popup-grid" id="popupCategoryList">
    @foreach($categories as $categoryItem)

     
        <a href="{{ cityCategoryUrl($categoryItem->slug) }}"
   class="popup-item dynamic-category-link"
   data-slug="{{ $categoryItem->slug }}">

            @if($categoryItem->icon && Str::endsWith($categoryItem->icon, ['.png', '.jpg', '.jpeg', '.svg']))

                <img width="60" height="60" src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                     loading="lazy"
                     alt="Browse {{ $categoryItem->name }}"
                     class="popup-category-icon">

            @else

                <i class="{{ $categoryItem->icon }}"></i>

            @endif

            <span>{{ $categoryItem->name }}</span>

        </a>

    @endforeach
</div>
    <!-- NO RESULT -->
    <div class="no-result text-center" id="noResult">
        <img width="300" height="225" src="{{ asset('assets/images/no-results-found.svg') }}" loading="lazy" alt="No Search Found">
        <p class="mt-3">No Search Found</p>
    </div>

</div>





<section class="custom-right rr-section">
    <div class="container-fluid">
        <div class="row g-3">
            @foreach($categoryHeadings as $headingItem)
                <div class=" p-30 pt-15 pb-15 cardhotkey__wrap">
                    <div class="">
                        <h2 class="fw500 font18 color111">{{ $headingItem->heading }}</h2>

                        <div class="subcategory-scroll">
                            <div class="subcategory-row">
                              @foreach(($headingItem->selected_categories ?? collect())->whereNotIn('id', $usedCategoryIds) as $category)

    @php
        $usedCategoryIds[] = $category->id;
    @endphp
                                    <div class="subcategory-col text_center mt-10">
                                    <a href="{{ cityCategoryUrl($category->slug) }}"
   class="mini-service-card dynamic-category-link"
   data-slug="{{ $category->slug }}">

   
                                            <img width="300" height="200" src="{{ asset('assets/images/category/' . $category->image) }}"
                                              loading="lazy"   alt="{{ $category->name }} Services">

                                         
                                              

                                          
                                        </a>
                                        <div class="color111 font14 pt-10">
    {{ $category->name }}
</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

    <section class="py-4 custom-right trending-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="d-flex align-items-center mb-2">
                {{-- aria-level, not a different tag: the CSS styles h4 and the visual has to stay put. --}}
                <h2 class="fw-bold mb-0 hh-h4-size">Trending Searches Near You</h2>
                <span class="badge bg-danger text-white ms-2">NEW</span>
            </div>

            <p class="text-muted">Stay updated with the latest local trends.</p>

            <div class="trending-wrapper position-relative">

                <button class="trend-nav prev-btn" type="button" onclick="scrollTrending(-1)" aria-label="Scroll left">
                    <i class="las la-angle-left"></i>
                </button>

                <div class="trending-scroll" id="trendingScroll">

  @foreach($categories->whereNotIn('id', $usedCategoryIds)->take(12) as $category)

        @php
            $usedCategoryIds[] = $category->id;
        @endphp
                        <div class="trend-card-item"
                             data-category-id="{{ $category->id }}"
                             data-category-name="{{ strtolower($category->name) }}">

                            <a href="{{ cityCategoryUrl($category->slug) }}"
                               class="trend-card-link dynamic-category-link"
                               data-slug="{{ $category->slug }}">

                                {{-- Name is announced by the <h3> in the overlay. --}}
                                <img width="300" height="200" src="{{ asset('assets/images/category/' . $category->image) }}"
                                     loading="lazy"
                                     onerror="this.hidden=true;"
                                    alt="">

                                <div class="trend-card-overlay">
                                    <div>
                                        <h3 class="trend-card-title {{ preg_match('/\s+/', trim($category->name)) ? 'trend-card-title--words' : 'trend-card-title--single' }}">
                                            {{ $category->name }}
                                        </h3>
                                    </div>
                                </div>

                            </a>
                        </div>
                    @endforeach

                </div>

                <button class="trend-nav next-btn" type="button" onclick="scrollTrending(1)" aria-label="Scroll right">
                    <i class="las la-angle-right"></i>
                </button>

            </div>
        </div>
    </div>
</section>




<section class="location-section  custom-right location-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="d-flex align-items-center mb-0">
                <h2 class="fw-bold mb-0 hh-h4-size">{{ __($location_section->content->title) }}</h2>
                <span class="badge bg-danger text-white ms-2">NEW</span>
            </div>

            <p class="text-muted ">Discover popular locations around you.</p>

            <div class="trending-wrapper position-relative">
  <button class="trend-nav prev-btn" type="button" aria-label="Scroll left">
                        <i class="las la-angle-left"></i>
                    </button>
              

                <div class="trending-scroll" >
                    @foreach($locations as $location)
                        <div class="trend-card-item">
                     
                      <a href="{{ url($location->slug) }}"
   class="trend-card-link select-home-location"
   data-location-name="{{ $location->name }}"
   data-location-slug="{{ $location->slug }}">
                                
     <img width="300" height="200" src="{{ loadImage('assets/images/location/'.$location->image) }}"
                                  loading="lazy"   alt="{{ $location->name }}">


                                <div class="trend-card-overlay">
                                    <div>
                                        <h3 class="trend-card-title">{{ $location->name }}</h3>
                                        <div class="trend-card-subtitle">Explore Now</div>
                                    </div>

                                   
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <button class="trend-nav next-btn" type="button" aria-label="Scroll right">
                        <i class="las la-angle-right"></i>
                    </button>

            </div>
        </div>
    </div>
</section>

    <!-- =========================
     LATEST LISTINGS SECTION
========================= -->

<section class="custom-right mt-2 mb-2 ps-section popular-same-section">
    <div class="container-fluid">

        <div class="d-flex align-items-center mb-2">
            <h2 class="fw-bold mb-0 hh-h4-size">Popular Searches</h2>
        </div>

        <div class="trending-wrapper position-relative">

            <button class="trend-nav prev-btn"
                    type="button"
                    onclick="scrollPopular(-1)" aria-label="Scroll left">
                <i class="las la-angle-left"></i>
            </button>

            <div id="popularScroll" class="trending-scroll">

           @foreach($popularCategories as $category)
                  

                    <div class="trend-card-item">

                        <a href="{{ cityCategoryUrl($category->slug) }}"
                           class="trend-card-link dynamic-category-link"
                           data-slug="{{ $category->slug }}">

                            <img width="300" height="200" src="{{ asset('assets/images/category/'.$category->image) }}"
                                 loading="lazy"
                                 onerror="this.hidden=true;"
                                 alt="Best {{ $category->name }}">

                            <div class="trend-card-overlay">
                                <div>
                                    <h3 class="trend-card-title {{ preg_match('/\s+/', trim($category->name)) ? 'trend-card-title--words' : 'trend-card-title--single' }}">
                                        {{ $category->name }}
                                    </h3>
                                </div>
                            </div>

                        </a>

                    </div>

                @endforeach

            </div>

            <button class="trend-nav next-btn"
                    type="button"
                    onclick="scrollPopular(1)" aria-label="Scroll right">
                <i class="las la-angle-right"></i>
            </button>

        </div>

    </div>
</section>



@foreach($seasonalSections as $section)

<section class="location-section custom-right location-same-section seasonal-same-section">
    <div class="container-fluid">

        <div class="border rounded-4 p-2 bg-white">

            <div class="d-flex align-items-center mb-0">

                <h2 class="fw-bold mb-0 hh-h4-size">
                    {{ $section->title }}
                </h2>

                @if($section->badge_text)
                    <span class="badge bg-danger text-white ms-2">
                        {{ $section->badge_text }}
                    </span>
                @endif

            </div>

            @if($section->subtitle)
                <p class="text-muted">
                    {{ $section->subtitle }}
                </p>
            @endif

            <div class="trending-wrapper position-relative">

              
                <div class="trending-scroll">

                    @foreach($section->selected_categories as $category)

                        <div class="trend-card-item">

                            <a href="{{ cityCategoryUrl($category->slug) }}"
                               class="trend-card-link dynamic-category-link"
                               data-slug="{{ $category->slug }}">

                                <img width="300" height="200" src="{{ asset('assets/images/category/'.$category->image) }}"
                                  loading="lazy"  onerror="this.hidden=true;" alt="Find {{ $category->name }}">

                                <div class="trend-card-overlay">
                                    <div>

                                        <h3 class="trend-card-title {{ preg_match('/\s+/', trim($category->name)) ? 'trend-card-title--words' : 'trend-card-title--single' }}">
                                            {{ $category->name }}
                                        </h3>

                                       
                                    </div>
                                </div>

                            </a>

                        </div>

                    @endforeach

                </div>


            </div>

        </div>

    </div>
</section>

@endforeach




    <!-- call to action section -->
    {{-- <section class="cta bg-image" style="background-image: url('{{ loadImage('assets/directory360/images/cta/'.$cta->content->image) }}');">
        <div class="container">
            <div class="cta-content">
                <h1>{{ __($cta->content->title) }}</h1>
                <a href="{{ __($cta->content->button_url) }}" class="btn btn__base"><i class="las la-plus"></i> {{ __($cta->content->button_name) }}</a>
            </div>
        </div>
    </section> --}}

    <!-- =========================
     LATEST LISTINGS SECTION
========================= -->
{{-- <section class="latest-listings latest-scroll-section pt-80 pb-80">
<div class="container">

<div class="row justify-content-center">
<div class="col-md-8">
<div class="section-title">
<h4>{{ __($latest_listing->content->title) }}</h4>
<p>{{ __($latest_listing->content->sub_title) }}</p>
</div>
</div>
</div>

<div class="tabs-wrapper mt-4 mb-4">

<button type="button" class="arrow-btn left-btn">
<i class="las la-angle-left"></i>
</button>

<div class="tabs-scroll">
<ul class="nav nav-tabs" role="tablist">

@foreach($categories as $categoryItem)

<li class="nav-item">
<a class="nav-link @if($loop->first) active @endif"
data-toggle="tab"
href="#cat{{$loop->index}}"
role="tab">

{{ __($categoryItem->name) }}

</a>
</li>

@endforeach

</ul>
</div>

<button type="button" class="arrow-btn right-btn">
<i class="las la-angle-right"></i>
</button>

</div>

<div class="tab-content mt-4">

@foreach($categories as $categoryItem)

<div class="tab-pane fade @if($loop->first) show active @endif"
id="cat{{$loop->index}}"
role="tabpanel">

<div class="row mb__30">

@foreach($categoryItem->listings()->notExpired()->orderBy('id','desc')->limit(6)->get() as $catListing)

<div class="col-lg-4 col-md-6 mb-4">

<div class="listing-area">

<div class="thumb">
<img width="300" height="225" src="{{ loadImage('assets/images/listing/thumb/'.$catListing->thumb) }}" alt="{{ $catListing->name }}" class="listing-thumb" loading="lazy">
</div>

<div class="listing-content">

<div class="listing-author">
<div class="author-thumb">
<a href="{{ route('listings.author',$catListing->user->id) }}">
<img width="48" height="48" src="{{ loadImage('assets/images/user/'.$catListing->user->image) }}" alt="{{ $catListing->user->name ?? 'Business owner' }}" loading="lazy">
</a>
</div>
</div>

<h3 class="title">
<a href="{{ route('listings.details',$catListing->slug) }}">
{{ __($catListing->name) }}
</a>
</h3>

<p>{{ \Str::limit(strip_tags($catListing->details),95) }}</p>

<div class="listing-rating">
@php echo listingRating($catListing) @endphp
</div>

<div class="listing-location">
<i class="las la-map-marker"></i>
<span>{{ __($catListing->address) }}</span>
</div>

@php

$phone='';

if(is_array($catListing->contact)){
$phone=$catListing->contact['content'][0] ?? '';
}
else{
$phone=$catListing->contact;
}

$cleanPhone=preg_replace('/[^0-9]/','',$phone);

@endphp

@if(!empty($cleanPhone))

<div class="listing-contact mt-3">

<a href="tel:{{ $cleanPhone }}" class="btn-call">
<i class="las la-phone"></i>
Call Now
</a>

<a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
<i class="lab la-whatsapp"></i>
WhatsApp
</a>

</div>

@endif

</div>
</div>
</div>

@endforeach

</div>
</div>

@endforeach

</div>

</div>
</section> --}}

  @include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-08.css'])

  <section class="category-banner-slider m-1">
    <div id="categoryBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">

        <div class="carousel-indicators">
            @foreach($hbbs as $key => $hbb)
                <button type="button"
                        data-bs-target="#categoryBannerCarousel"
                        data-bs-slide-to="{{ $key }}"
                        class="{{ $key == 0 ? 'active' : '' }}"
                        aria-current="{{ $key == 0 ? 'true' : 'false' }}"
                        aria-label="Slide {{ $key + 1 }}">
                </button>
            @endforeach
        </div>

        <div class="carousel-inner">
            @foreach($hbbs as $key => $hbb)
                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                    <a href="{{ $hbb->link ?? '#' }}" target="_blank" rel="noopener noreferrer">
                        <picture>
                            @if(!empty($hbb->mobile_banner))
                                <source media="(max-width: 767px)"
                                        srcset="{{ asset('assets/images/category/hbb/' . $hbb->mobile_banner) }}">
                            @endif

                            <img width="1200" height="534" src="{{ asset('assets/images/category/hbb/' . $hbb->desktop_banner) }}"
                                 class="d-block w-100 category-banner-img"
                             loading="lazy"    alt="{{ $hbb->title ?? 'Banner' }}">
                        </picture>
                    </a>
                </div>
            @endforeach
        </div>

       

    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  

   

    
    <!-- Blog section start -->
    <section class="blog-section pb-4 pt-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="section-title">
                        <h2 class="hh-h4-size">{{ __($blog_section->content->title) }}</h2>
                        <p class="bgl">{{ __($blog_section->content->sub_title) }}</p>
                    </div>
                </div>
            </div>
            <div class="row mb__30">
                @foreach($blogs as $blog)
                <div class="col-md-4">
                  <div class="blog-area"
     data-url="{{ route('blog.details', $blog->content->slug) }}"
     style="cursor:pointer;">
                        <div class="thumb">
                            <img width="400" height="250" src="{{ blogThumb($blog->content->image) }}" loading="lazy" alt="blog">
                        </div>
                       
                       <div class="content-area">
    <h3 class="hh-h5-size">
        <a href="{{ route('blog.details', $blog->content->slug) }}">
            {{ __($blog->content->title) }}
        </a>
    </h3>

    <p>{{ __($blog->content->sub_title) }}</p>

    <a href="{{ route('blog.details', $blog->content->slug) }}" class="btn btn__base">
        @lang('Read')
    </a>
</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Blog section end -->
    {{-- ================= CATEGORY CONTENT SECTION ================= --}}
<section class="custom-right category-content-section py-4">
    <div class="container-fluid px-4">


        @if(isset($homeContentSections) && $homeContentSections->count() > 0)

<div class="home-top-content-section mb-4">

    @foreach($homeContentSections as $homeContent)

        <div class="home-top-content-box">
         <h2 class="cod mb-0 hh-h4-size">
            {{ $homeContent->title }}
            </h2>
            <div class="home-top-content-text rich-content">
              {!! \App\Support\ContentHtml::cleanCached($homeContent->content ?? '') !!}
            </div>

        </div>

    @endforeach

</div>

@endif

        <div class="category-content-heading mb-4">
            <h2 class=" cod mb-0 hh-h4-size">
                Explore Popular Services That Can Help You In Your Daily Needs :
            </h2>
        </div>

      @php
    $currentCity = $locationText ?? request('location_name') ?? session('detected_city') ?? '';

    if (!empty($currentCity) && str_contains($currentCity, ',')) {
        $parts = explode(',', $currentCity);
        $currentCity = trim(end($parts));
    }

    $currentCitySlug = !empty($currentCity) ? \Str::slug($currentCity) : '';
@endphp

<div class="row category-content-row">
    @foreach($categories->take(12) as $categoryItem)
        <div class="col-lg-3 col-md-6 col-12">

            <div class="d-flex align-items-center category-content-title-wrap">

                <span class="category-content-icon">
                    @if(!empty($categoryItem->icon) && Str::endsWith(strtolower($categoryItem->icon), ['.png', '.jpg', '.jpeg', '.svg', '.webp']))
                        <img width="60" height="60" src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                             loading="lazy"
                             alt="{{ $categoryItem->name }}">
                    @elseif(!empty($categoryItem->icon) && Str::startsWith($categoryItem->icon, ['fa', 'fas', 'far', 'fab', 'las', 'la']))
                        <i class="{{ $categoryItem->icon }}"></i>
                    @else
                        <span>{{ $categoryItem->icon ?? '📌' }}</span>
                    @endif
                </span>

                <h3 class="category-content-title mb-0 hh-h5-size">
                    {{-- Direct /{city}/{slug} (no redirect) + client-side
                         rewrite by .dynamic-category-link JS, same as every
                         other category link on this page. --}}
                    <a href="{{ cityCategoryUrl($categoryItem->slug) }}"
                       class="text-dark dynamic-category-link"
                       data-slug="{{ $categoryItem->slug }}">

                        {{ $categoryItem->name }}
                    </a>
                </h3>

            </div>

            <p class="spn mb-0">
                {{ Str::limit(strip_tags($categoryItem->content ?? $categoryItem->description ?? ''), 150) }}
            </p>

        </div>
    @endforeach
</div>

          <div class="category-content-bottom mt-4">
            <span class=" spn mb-0">
               Along with these popular categories, you can also find many other daily-use services such as Pest Control, Skin Care Clinics, Painters, Laundry Services, Interior Designers, Mobile Phone Repair, AC Repair Services, Internet Service Providers, Travel & Vacation Centres, Wedding Bands, Event Organisers, Packers and Movers and more. Our platform helps you quickly connect with reliable local businesses and service providers as per your needs.
            </span>
        </div>  
        

    </div>

</section>










<!-- Popup -->
<div id="buyerPopup" class="buyer-popup">

    <button class="buyer-close" onclick="closeBuyerPopup()">×</button>

    <div class="buyer-content">
   <h3>
    Connect with<br>
    {{ number_format($totalUsers ?? 0) }}+ Local Users
</h3>
       <p>
    Grow Your Business on
    <br>
    Service Bazzar
</p>

        @guest
<a href="{{ route('free.listing') }}"
   onclick="event.preventDefault(); openSbAuthPopup();"
   class="buyer-btn">
    List your Business for FREE
</a>
@else
<a href="{{ route('free.listing') }}"
   class="buyer-btn">
    List your Business for FREE
</a>
@endguest
    </div>

</div>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-09.css'])


@endsection
