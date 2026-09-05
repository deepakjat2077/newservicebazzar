@extends($themeView . 'layouts.site')

@php
    $seoCategory = $selectedCategory ?? $selectedSubcategory ?? null;
    $seoSiteName = $setting->site_name ?? config('app.name', 'Website');
    // City×category combo pages (ListingController::cityCategoryArea) pass
    // $locationText but no $seoTitle/$seoDescription — fold the location in
    // here so every city variant of the same category isn't an identical,
    // duplicate title/description.
    $seoLocationSuffix = !empty($locationText) ? ' in ' . $locationText : '';

    // Controller-provided values (e.g. SiteController::listingsCategory,
    // which appends the site name to its fallback) must win over this
    // block's own recomputation — previously this used plain `=`, silently
    // discarding whatever the controller had already built.
    $seoTitle ??= $seoCategory?->seo_title
        ?: (($seoCategory?->name ?? 'Listings') . $seoLocationSuffix . ' | ' . $seoSiteName);

    $seoDescription ??= $seoCategory?->seo_description
        ?: (\Illuminate\Support\Str::limit(strip_tags($seoCategory?->description ?? ''), 160, '')
            ?: ('Find the best ' . ($seoCategory?->name ?? 'businesses') . $seoLocationSuffix . ' on ' . $seoSiteName . '.'));

    $seoKeywords ??= $seoCategory?->seo_keywords ?: '';

    $seoImage ??= !empty($seoCategory?->image)
        ? asset('assets/images/category/' . $seoCategory->image)
        : asset('assets/images/logo/logo.png');
@endphp

@section('title', $seoTitle)
@section('content')
    @php
        $filterBaseParams = request()->query();

        if (empty($filterBaseParams['category']) && !empty($selectedCategory?->id)) {
            $filterBaseParams['category'] = [$selectedCategory->id];
        }

        if (!empty($filterBaseParams['category'])) {
            unset($filterBaseParams['categorysearch']);
        } elseif (empty($filterBaseParams['categorysearch']) && !empty($selectedCategory?->name)) {
            $filterBaseParams['categorysearch'] = $selectedCategory->name;
        }

        if (empty($filterBaseParams['location_name']) && request('location_name')) {
            $filterBaseParams['location_name'] = request('location_name');
        }
    @endphp

    <style>
        /* ================= SEARCH SECTION ================= */

        /* ================= FILTER BAR ================= */

        .main-card {
            margin: 15px 12px;
            position: relative;
            overflow: visible !important;
        }

       .filters-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
}

       .filters-viewport {
    flex: 0 0 auto;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    position: relative;
}

        .filters-viewport.dropdown-open {
            overflow: visible !important;
        }

        .filters-viewport::-webkit-scrollbar {
            display: none;
        }

        .filters-scroll {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            width: max-content;
        }

        .filter-pill,
        .custom-dropdown {
            flex: 0 0 auto;
        }

        .filter-pill {
            border: 1px solid #d9d9d9;
            background: #fff;
            color: #111;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 15px;
            line-height: 1;
            cursor: pointer;
            white-space: nowrap;
            transition: 0.2s ease;
        }

        .filter-pill:hover {
            background: #060505;
            color: #fff;
        }

        .custom-dropdown {
            position: relative;
            overflow: visible !important;
        }

        .dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .custom-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 190px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            z-index: 999999;
            padding: 8px 0;
        }

        .custom-dropdown.open .custom-dropdown-menu {
            display: block;
        }

        .custom-dropdown-menu a {
            display: block;
            padding: 10px 16px;
            font-size: 14px;
            color: #111;
            text-decoration: none;
        }

        .custom-dropdown-menu a:hover {
            background: #f5f5f5;
        }

        .scroll-btn {
            background: #fff;
            border: 1px solid #ddd;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex: 0 0 auto;
            z-index: 20;
        }

        .scroll-btn.left-btn {
            position: absolute;
            left: 0;
        }

        .scroll-btn.right-btn {
            position: absolute;
            right: 0;
        }

        .all-filters-mobile {
            background: #000;
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 13px;
            white-space: nowrap;
            flex: 0 0 auto;
        }

        .filter-sidebar {
            position: fixed;
            top: 0;
            right: -100%;
            width: 85%;
            max-width: 400px;
            height: 100vh;
            background: #fff;
            z-index: 1050;
            transition: 0.3s ease;
            overflow-y: auto;
            padding: 20px;
        }

        .filter-sidebar.active {
            z-index: 1000000;
            right: 0;
        }

        .filter-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1040;
            display: none;
        }

        .filter-overlay.active {
            display: block;
        }

        .filter-pill.active-filter,
        .filter-chip.active,
        .custom-dropdown.active-filter .dropdown-btn {
            background: #000;
            color: #fff;
            border-color: #000;
        }

        .custom-dropdown.active-filter .dropdown-btn i {
            color: #fff;
        }

        @media (max-width:767px) {
            .filters-wrapper {
                padding: 10px 40px;
            }

            .scroll-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .listing-items {
                margin-top: 10px;
            }
        }

        @media (min-width:768px) {
            .scroll-btn {
                display: none !important;
            }

          
        }

@media (min-width:768px) {
    .scroll-btn {
        display: none;
    }

    .all-filters-mobile {
        display: none;
    }

    .all-filters-desktop {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
}

@media (max-width:767px) {
    .all-filters-desktop {
        display: none;
    }

    .all-filters-mobile {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
}
        /* ================= MOBILE ================= */

        @media (max-width:767px) {

            .listing-items {
                margin-top: 10px;
            }

        }

        .filter-pill.active-filter,
        .filter-chip.active,
        .custom-dropdown.active-filter .dropdown-btn {
            background: #000;
            color: #fff;
            border-color: #000;
        }

        .custom-dropdown.active-filter .dropdown-btn i {
            color: #fff;
        }

        .all-filters-desktop,
.all-filters-mobile{
    background:#000;
    color:#fff;
    border:none;
    padding:7px 14px;
    border-radius:20px;
    font-size:13px;
    white-space:nowrap;
    flex:0 0 auto;
}

@media (min-width:768px){
    .all-filters-desktop{
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }

    .all-filters-mobile{
        display:none;
    }
}

@media (max-width:767px){
    .filters-wrapper{
        padding:10px 45px 10px 42px;
        gap:8px;
    }

    .filters-viewport{
        flex:1 1 auto;
        width:auto;
    }

    .all-filters-desktop{
        display:none;
    }

    .all-filters-mobile{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        margin-left:6px;
        position:relative;
        z-index:25;
    }

    .scroll-btn.right-btn{
        right:0;
    }

    .scroll-btn.left-btn{
        left:0;
    }
}

a {
    color: #000;
}

    </style>

    @if(isset($banners) && $banners->count() > 0)
        <section class="category-banner-slider">
            <div id="categoryBannerCarousel" class="carousel slide" data-bs-ride="carousel">

                {{-- Indicators --}}
                <div class="carousel-indicators">
                    @foreach($banners as $key => $banner)
                        <button type="button" data-bs-target="#categoryBannerCarousel" data-bs-slide-to="{{ $key }}"
                            class="{{ $key == 0 ? 'active' : '' }}" aria-current="{{ $key == 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $key + 1 }}">
                        </button>
                    @endforeach
                </div>

                {{-- Slides --}}
                <div class="carousel-inner">
                    @foreach($banners as $key => $banner)
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            <a href="{{ $banner->link ?? '#' }}" target="_blank">
                                <picture>
                                    @if(!empty($banner->mobile_banner))
                                        <source media="(max-width: 767px)"
                                            srcset="{{ asset('assets/images/category/banner/' . $banner->mobile_banner) }}">
                                    @endif

                                    <img src="{{ asset('assets/images/category/banner/' . $banner->desktop_banner) }}"
                                        class="d-block w-100 category-banner-img" alt="{{ $banner->title ?? 'Banner' }}">
                                </picture>
                            </a>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>
    @endif

  @php
    $locationText = '';

    if ($locationId = request('location_id')) {
        $location = \App\Models\Location::find($locationId);
        if ($location) {
            $locationText = $location->name;
        }
    } elseif ($name = request('location_name')) {
        $locationText = $name;
    }

    if (empty($locationText)) {
        $citySegment = request()->segment(1);
        $categorySegment = request()->segment(2);

        if ($citySegment) {
            $locationText = ucwords(str_replace('-', ' ', $citySegment));
        }

        if ($categorySegment && str_contains($categorySegment, '-in-')) {
            $parts = explode('-in-', $categorySegment, 2);

            $areaText = ucwords(str_replace('-', ' ', $parts[1]));
            $cityText = ucwords(str_replace('-', ' ', $citySegment));

            $locationText = $areaText . ', ' . $cityText;
        }
    }

    $categoryText = $selectedSubcategory->name
        ?? $selectedCategory->name
        ?? request('categorysearch')
        ?? 'Listings';

    $listingCount = collect(method_exists($listings, 'items') ? $listings->items() : $listings)
        ->filter(function ($item) {
            return is_object($item) && isset($item->id);
        })
        ->count();
@endphp

   <div class="listing-top-info custom-right">

    <div class="listing-breadcrumb">

        @if(!empty($locationText))

            <a href="{{ route('home') }}" class="breadcrumb-location-link">
                {{ $locationText }}
            </a>

            <span class="sep">&gt;</span>

            <span>{{ $categoryText }} in {{ $locationText }}</span>

            <span class="sep">&gt;</span>

        @else

            <span>{{ $categoryText }}</span>

            <span class="sep">&gt;</span>

        @endif

        <span>{{ $listingCount }}+ Listings</span>

    </div>

    <h2 class="listing-main-title">
        Popular {{ $categoryText }}{{ !empty($locationText) ? ' in ' . $locationText : '' }}
    </h2>

</div>

@php
    // BreadcrumbList mirroring the visible trail above.
    $breadcrumbItems = [];

    if (!empty($locationText)) {
        $breadcrumbItems[] = ['name' => $locationText, 'item' => route('home')];
    }

    $breadcrumbItems[] = ['name' => $categoryText, 'item' => url()->current()];

    $breadcrumbSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumbItems)->values()->map(function ($crumb, $index) {
            return [
                '@type'    => 'ListItem',
                'position' => $index + 1,
                'name'     => $crumb['name'],
                'item'     => $crumb['item'],
            ];
        })->all(),
    ];
@endphp

<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>

    <style>
        .breadcrumb-location-link{
    color:#444;
    text-decoration:none;
}

.breadcrumb-location-link:hover{
    color:#0d6efd;
}
        .listing-top-info {
            padding: 10px 12px 0;
        }

        .listing-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 13px;
            color: #444;
            margin-top: 20px;
        }

        .listing-breadcrumb .sep {
            color: #888;
            font-weight: 600;
        }

        .listing-main-title {
            font-size: 20px;
            font-weight: 700;
            color: #111;
            margin: 12px 0 12px;
            line-height: 1.3;
        }

        @media (max-width: 767px) {
            .listing-top-info {
                padding: 10px 10px 0;
            }

            .listing-breadcrumb {
                font-size: 12px;
                gap: 4px;
                display: none;
            }

            .listing-main-title {
                font-size: 18px;
               
            }
        }

.all-filters-desktop,
.all-filters-mobile{
    background:#000;
    color:#fff;
    border:none;
    padding:7px 14px;
    border-radius:20px;
    font-size:13px;
    white-space:nowrap;
    flex:0 0 auto;
}

@media (min-width:768px){
    .all-filters-desktop{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        margin-left:0;
    }

    .all-filters-mobile{
        display:none;
    }
}

@media (max-width:767px){
    .all-filters-desktop{
        display:none;
    }

    .all-filters-mobile{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        margin-left:0;
    }
}
    </style>

    @php
        $filterBaseParams = request()->query();

        if (empty($filterBaseParams['category']) && !empty($selectedCategory?->id)) {
            $filterBaseParams['category'] = [$selectedCategory->id];
        }

        if (empty($filterBaseParams['category']) && empty($filterBaseParams['categorysearch']) && !empty($selectedCategory?->name)) {
            $filterBaseParams['categorysearch'] = $selectedCategory->name;
        }

        if (empty($filterBaseParams['location_name']) && request('location_name')) {
            $filterBaseParams['location_name'] = request('location_name');
        }
    @endphp

    @php
        $segmentLocation = request()->segment(1);

        $currentLocationSlug = null;

        if (!empty($segmentLocation)) {
            $locationExists = \App\Models\Location::where('slug', $segmentLocation)->exists();

            if ($locationExists) {
                $currentLocationSlug = $segmentLocation;
            }
        }

        if (empty($currentLocationSlug) && request('location_name')) {
            $currentLocationSlug = \Illuminate\Support\Str::slug(request('location_name'));
        }

        if (empty($currentLocationSlug) && session('user_location')) {
            $currentLocationSlug = \Illuminate\Support\Str::slug(session('user_location'));
        }

        if ($selectedCategory && $currentLocationSlug) {
            $filterBaseUrl = url($currentLocationSlug . '/' . $selectedCategory->slug);
        } elseif ($selectedCategory) {
            $filterBaseUrl = cityCategoryUrl($selectedCategory->slug);
        } elseif ($selectedSubcategory && $currentLocationSlug) {
            $filterBaseUrl = url($currentLocationSlug . '/' . $selectedSubcategory->slug);
        } elseif ($selectedSubcategory) {
            $filterBaseUrl = route('listings.subcategory', ['slug' => $selectedSubcategory->slug]);
        } else {
            $filterBaseUrl = route('listings.search');
        }

        $filterQuery = request()->query();

        unset($filterQuery['category']);
        unset($filterQuery['categorysearch']);
        unset($filterQuery['location_name']);
        unset($filterQuery['latitude']);
        unset($filterQuery['longitude']);
    @endphp

    <div class="main-card">
        <div class="filters-wrapper">

            <button class="scroll-btn left-btn d-md-none" type="button" onclick="scrollFilters(-1)">
                <i class="las la-angle-left"></i>
            </button>

            <div class="filters-viewport custom-right">
                <div class="filters-scroll" id="filtersScroll">

                    <div class="custom-dropdown {{ request('sort') ? 'active-filter' : '' }}">
                        <button type="button" class="filter-pill dropdown-btn" onclick="toggleDropdown(this)">
                            Sort By <i class="las la-angle-down ml-1"></i>
                        </button>

                        <div class="custom-dropdown-menu">
                            {{--
                                rel="nofollow" on every sort/filter link on this page: robots.txt
                                already disallows crawling ?sort= URLs, but that only stops the
                                fetch — Google still discovers and indexes the URL just from
                                finding this <a href> on a crawlable page, which is exactly the
                                "Indexed, though blocked by robots.txt" state seen in Search
                                Console. nofollow tells it not to follow/index the link at all,
                                so it stops rediscovering new ?sort= URLs across every
                                city/category page from here on.
                            --}}
                            <a rel="nofollow"
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['sort' => 'rating'])) }}">Rating</a>
                            <a rel="nofollow"
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['sort' => 'popular'])) }}">Popular</a>
                            <a rel="nofollow"
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['sort' => 'distance'])) }}">Distance</a>
                        </div>
                    </div>

                    <div class="custom-dropdown {{ request('rating') ? 'active-filter' : '' }}">
                        <button type="button" class="filter-pill dropdown-btn" onclick="toggleDropdown(this)">
                            Ratings <i class="las la-angle-down ml-1"></i>
                        </button>

                        <div class="custom-dropdown-menu">

                            <a
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['rating' => ''])) }}">
                                Any
                            </a>

                            <a
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['rating' => 3.5])) }}">
                                3.5+
                            </a>

                            <a
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['rating' => 4.0])) }}">
                                4.0+
                            </a>

                            <a
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['rating' => 4.5])) }}">
                                4.5+
                            </a>

                            <a
                                href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['rating' => 5.0])) }}">
                                5.0
                            </a>

                        </div>
                    </div>

                @php
    $topRatedQuery = $filterQuery;

    if (request('filter') == 'top_rated') {
        unset($topRatedQuery['filter']);
    } else {
        $topRatedQuery['filter'] = 'top_rated';
    }
@endphp

{{-- <button type="button"
    class="filter-pill {{ request('filter') == 'top_rated' ? 'active-filter' : '' }}"
    onclick="window.location='{{ $filterBaseUrl . '?' . http_build_query($topRatedQuery) }}'">
    Top Rated
</button> --}}

                   @php
    $verifiedQuery = $filterQuery;

    if (request('filter') == 'verified') {
        unset($verifiedQuery['filter']);
    } else {
        $verifiedQuery['filter'] = 'verified';
    }
@endphp

<button type="button"
    class="filter-pill {{ request('filter') == 'verified' ? 'active-filter' : '' }}"
    onclick="window.location='{{ $filterBaseUrl . '?' . http_build_query($verifiedQuery) }}'">
    Verified
</button>

@php
    $trustQuery = $filterQuery;

    if (request('filter') == 'trust') {
        unset($trustQuery['filter']);
    } else {
        $trustQuery['filter'] = 'trust';
    }
@endphp

<button type="button"
    class="filter-pill {{ request('filter') == 'trust' ? 'active-filter' : '' }}"
    onclick="window.location='{{ $filterBaseUrl . '?' . http_build_query($trustQuery) }}'">
    Trust
</button>
  <button class="all-filters-desktop ml-2" type="button" onclick="openSidebar()">
                <i class="las la-sliders-h mr-1"></i> All
            </button>

                </div>
            </div>
            <button class="scroll-btn right-btn d-md-none" type="button" onclick="scrollFilters(1)">
                <i class="las la-angle-right"></i>
            </button>

          <button class="all-filters-mobile" type="button" onclick="openSidebar()">
    <i class="las la-sliders-h mr-1"></i> All
</button>
        </div>
    </div>
    <div class="filter-overlay" id="overlay" onclick="closeSidebar()"></div>

    <div class="filter-sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">MORE FILTERS</h5>
            <button type="button" class="close-sidebar-btn" onclick="closeSidebar()">
                <i class="las la-times"></i>
            </button>
        </div>

        {{--
            rel="nofollow" throughout this sidebar: robots.txt disallows
            crawling ?sort=/?rating=/?filter= URLs, but that only blocks the
            fetch — Google still discovers and indexes the URL just from
            finding the <a href> here, which is exactly the "Indexed, though
            blocked by robots.txt" state Search Console flags. nofollow stops
            it from following/indexing these links at the source, across
            every city/category page that renders this sidebar.
        --}}
        <h6 class="mt-4">Sort By</h6>
        <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['sort' => 'rating'])) }}"
            class="filter-chip d-inline-block mb-2 {{ request('sort') == 'rating' ? 'active' : '' }}">
            Rating
        </a>

        {{-- SORT --}}
        <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['sort' => 'popular'])) }}"
            class="filter-chip d-inline-block mb-2 {{ request('sort') == 'popular' ? 'active' : '' }}">
            Popular
        </a>

        <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['sort' => 'distance'])) }}"
            class="filter-chip d-inline-block mb-2 {{ request('sort') == 'distance' ? 'active' : '' }}">
            Distance
        </a>

        <h6 class="mt-4">Ratings</h6>

        @for($i = 1; $i <= 5; $i++)
            <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['rating' => $i])) }}"
                class="filter-chip d-inline-block mb-2 {{ request('rating') == $i ? 'active' : '' }}">
                {{ $i }} Star
            </a>
        @endfor

        <h6 class="mt-4">More Filters</h6>

        {{-- <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['filter' => 'top_rated'])) }}"
            class="filter-chip d-inline-block mb-2 {{ request('filter') == 'top_rated' ? 'active' : '' }}">
            Top Rated
        </a> --}}

        <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['filter' => 'verified'])) }}"
            class="filter-chip d-inline-block mb-2 {{ request('filter') == 'verified' ? 'active' : '' }}">
            Verified
        </a>

        <a rel="nofollow" href="{{ $filterBaseUrl . '?' . http_build_query(array_merge($filterQuery, ['filter' => 'trust'])) }}"
            class="filter-chip d-inline-block mb-2 {{ request('filter') == 'trust' ? 'active' : '' }}">
            Trust
        </a>

        {{-- BUTTONS --}}
        <div class="bottom-buttons mt-4">

            <a href="{{ $filterBaseUrl }}" class="btn btn-reset">
                Reset Filters
            </a>

            <button class="btn btn-apply" type="button" onclick="closeSidebar()">
                Apply Filters
            </button>

        </div>
    </div>

    <section class="listing pt-70 pb-80  custom-right" id="listingSection">
        <div class="container-fluid">
            <div class="row mb__30">

                <div class="col-md-8" id="listingColumn">
                    <div class="listing-items">
                        <div class="row" id="listingWrapper">
                            @include($themeView . 'partials.ajax-listings')
                        </div>

                        {{-- Justdial-style infinite scroll — page-number
                        links were explicitly ruled out; the next batch
                        loads itself in as the sentinel below scrolls into
                        view. See #infiniteScrollLoader's script further
                        down this file. --}}
                        @if($listings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                            <div id="infiniteScrollLoader" class="infinite-scroll-loader d-none">
                                <div class="infinite-scroll-spinner"></div>
                            </div>

                            @if($listings->hasMorePages())
                                <div id="infiniteScrollSentinel"
                                     data-next-url="{{ $listings->nextPageUrl() }}">
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <style>
                    .infinite-scroll-loader{
                        display:flex;
                        justify-content:center;
                        padding:24px 0;
                    }
                    .infinite-scroll-loader.d-none{
                        display:none;
                    }
                    .infinite-scroll-spinner{
                        width:34px;
                        height:34px;
                        border:3px solid #e2e2e2;
                        border-top-color:#0d6efd;
                        border-radius:50%;
                        animation:infiniteScrollSpin .7s linear infinite;
                    }
                    @keyframes infiniteScrollSpin{
                        to{ transform:rotate(360deg); }
                    }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    /*
                     * Justdial-style infinite scroll for the category /
                     * subcategory listing page: no page-number links —
                     * scrolling the sentinel div (right after the listing
                     * cards) into view fetches the next paginate()d page
                     * from the same URL (Laravel reads ?page= automatically)
                     * and appends just its cards, with a spinner while that
                     * fetch is in flight.
                     */
                    const sentinel = document.getElementById('infiniteScrollSentinel');
                    const loader = document.getElementById('infiniteScrollLoader');
                    const wrapper = document.getElementById('listingWrapper');

                    if (!sentinel || !wrapper) {
                        return;
                    }

                    let loading = false;

                    const observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                loadNextPage();
                            }
                        });
                    }, { rootMargin: '600px 0px' }); // start the fetch well before it's actually visible

                    observer.observe(sentinel);

                    function loadNextPage() {
                        const nextUrl = sentinel.dataset.nextUrl;

                        if (loading || !nextUrl) {
                            return;
                        }

                        loading = true;
                        if (loader) loader.classList.remove('d-none');

                        fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function (response) { return response.text(); })
                            .then(function (html) {
                                const parsed = new DOMParser().parseFromString(html, 'text/html');
                                const newWrapper = parsed.getElementById('listingWrapper');
                                const newSentinel = parsed.getElementById('infiniteScrollSentinel');

                                if (newWrapper) {
                                    Array.from(newWrapper.children).forEach(function (child) {
                                        wrapper.appendChild(child);
                                    });

                                    // Re-run just the "hide arrow at the first/
                                    // last image" bit for the cards that were
                                    // just appended — the actual click handling
                                    // is delegated (see ajax-listings.blade.php)
                                    // so it already covers them.
                                    if (typeof window.initGalleryArrowVisibility === 'function') {
                                        window.initGalleryArrowVisibility(wrapper);
                                    }
                                }

                                if (newSentinel && newSentinel.dataset.nextUrl) {
                                    sentinel.dataset.nextUrl = newSentinel.dataset.nextUrl;
                                } else {
                                    observer.unobserve(sentinel);
                                    sentinel.remove();
                                }
                            })
                            .catch(function () {
                                // next-url stays put; scrolling the sentinel
                                // out of and back into view retries the fetch.
                            })
                            .finally(function () {
                                loading = false;
                                if (loader) loader.classList.add('d-none');
                            });
                    }
                });
                </script>

                <div class="col-md-4" id="sidebarColumn">

                    <div class="sidebar-sticky-wrapper">

                        {{-- ORIGINAL ENQUIRY FORM --}}
                        <div class="enquiry-card mt-3 sticky-enquiry" id="mainEnquiryForm">
                            <h6 class="enquiry-title">
                                Get the List of
                                <span>{{ $selectedCategory->name ?? request('categorysearch') ?? 'Listings' }}</span>
                            </h6>

                            <p class="enquiry-subtitle">
                                We'll send you contact details in seconds for free
                            </p>

                            @if(session('success'))
                                <div class="alert alert-success mt-2">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger mt-2">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form action="{{ route('contact.author') }}" method="POST">
                                @csrf
                                @include('themes.servicebazzar.partials.form-guard')

                                <input type="hidden" name="category_id" value="{{ $selectedCategory->id ?? '' }}">
                                <input type="hidden" name="category_name"
                                    value="{{ $selectedCategory->name ?? request('categorysearch') }}">
                                <input type="hidden" name="location_name" value="{{ request('location_name') }}">
                                <input type="hidden" name="location_id"
       value="{{ request('location_id') ?? ($location->id ?? '') }}">

                                <div class="mb-2 input-icon-group">
                                    <span class="input-icon"><i class="las la-user"></i></span>
                                    <input type="text" name="name" class="form-control enquiry-input" placeholder="Name"
                                        required>
                                </div>

                                <div class="mb-2 input-icon-group">
                                    <span class="input-icon"><i class="las la-mobile"></i></span>
                                    <input type="text" name="phone" class="form-control enquiry-input"
                                        placeholder="Mobile Number" required>
                                </div>

                                <div class="form-check enquiry-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="policyCheck" required>
                                    <label class="form-check-label" for="policyCheck">
                                        I Agree to <a href="{{ route('single.slug', 38) }}">T&C's Privacy Policy</a>
                                    </label>
                                </div>

                                <button type="submit" class="btn enquiry-btn w-100">
                                    Send Enquiry <i class="las la-angle-double-right"></i>
                                </button>
                            </form>
                        </div>

                        @php
                            // $listing (singular) is only ever populated by
                            // ListingController::cityCategoryArea() — this
                            // same view is also rendered by
                            // SiteController::search()/listingsCategory()/
                            // listingsSubcategory(), which never set it, so
                            // it must be guarded rather than assumed present.
                            $relatedCategories = \App\Models\Category::where('status', 1)
                                ->where('id', '!=', ($listing ?? null)?->category_id ?? 0)
                                ->withCount('listings')
                                ->take(5)
                                ->get();
                        @endphp
                        @php
                            $currentLocationName = request('location_name')
                                ?? optional(($listing ?? null)?->location)->name
                                ?? optional($location ?? null)->name
                                ?? '';
                        @endphp
                        {{-- TRIGGER SECTION --}}
                        <div class="related-search-card mt-3" id="relatedSearchTrigger">
                            <h6 class="related-search-title">People Also Search For</h6>

                            @foreach($relatedCategories as $category)
                                                <div class="related-search-item">
                                                    <div class="related-search-img">
                                                        <img src="{{ asset('assets/images/category/' . $category->image) }}"
                                                            alt="{{ $category->name }}" loading="lazy">
                                                    </div>

                                                    <div class="related-search-content">
                                                        <h6>{{ $category->name }}</h6>
                                                        <p>{{ $category->listings_count }}+ listings</p>

                                                        <a href="{{ !empty($currentLocationName)
                                ? url(Str::slug($currentLocationName) . '/' . $category->slug)
                                : cityCategoryUrl($category->slug) }}" class="related-search-btn">
                                                            {{ $loop->odd ? 'Get Best Deal' : 'View Details' }}
                                                        </a>
                                                    </div>
                                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                {{-- STICKY FLOATING ENQUIRY FORM --}}
                <div class="sticky-floating-form" id="stickyFloatingForm">
                    <div class="enquiry-card">
                        <h6 class="enquiry-title">
                            Get the List of
                            <span>{{ $selectedCategory->name ?? request('categorysearch') ?? 'Listings' }}</span>
                        </h6>

                        <p class="enquiry-subtitle">
                            We'll send you contact details in seconds for free
                        </p>

                        @if(session('success'))
                            <div class="alert alert-success mt-2">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger mt-2">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('contact.author') }}" method="POST">
                            @csrf
                            @include('themes.servicebazzar.partials.form-guard')

                            <input type="hidden" name="category_id" value="{{ $selectedCategory->id ?? '' }}">
                            <input type="hidden" name="category_name"
                                value="{{ $selectedCategory->name ?? request('categorysearch') }}">
                            <input type="hidden" name="location_name" value="{{ request('location_name') }}">
  <input type="hidden" name="location_id"
       value="{{ request('location_id') ?? ($location->id ?? '') }}">
                            <div class="mb-2 input-icon-group">
                                <span class="input-icon"><i class="las la-user"></i></span>
                                <input type="text" name="name" class="form-control enquiry-input" placeholder="Name"
                                    required>
                            </div>

                            <div class="mb-2 input-icon-group">
                                <span class="input-icon"><i class="las la-mobile"></i></span>
                                <input type="text" name="phone" class="form-control enquiry-input"
                                    placeholder="Mobile Number" required>
                            </div>

                            <div class="form-check enquiry-check mb-3">
                                <input class="form-check-input" type="checkbox" id="policyCheckSticky" required>
                                <label class="form-check-label" for="policyCheckSticky">
                                    I Agree to <a href="{{ route('single.slug', 38) }}">T&C's Privacy Policy</a>
                                </label>
                            </div>

                            <button type="submit" class="btn enquiry-btn w-100">
                                Send Enquiry <i class="las la-angle-double-right"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <style>
                    .sidebar-sticky-wrapper {
                        position: relative;
                    }

                    .sticky-floating-form {
                        position: fixed;
                        top: 95px;
                        z-index: 9999;
                        display: none;

                    }

                    .sticky-floating-form.show {
                        display: block;
                    }

                    .enquiry-card {
                        background: #f7f7f7;
                        border: 1px solid #cfcfcf;
                        border-radius: 10px;
                        padding: 14px;
                    }

                    .enquiry-title {
                        font-size: 18px;
                        font-weight: 700;
                        color: #111;
                        margin-bottom: 6px;
                        line-height: 1.4;
                    }

                    .enquiry-title span {
                        color: #0d6efd;
                    }

                    .enquiry-subtitle {
                        font-size: 13px;
                        color: #555;
                        margin-bottom: 16px;
                    }

                    .input-icon-group {
                        position: relative;
                    }

                    .input-icon {
                        position: absolute;
                        left: 12px;
                        top: 50%;
                        transform: translateY(-50%);
                        color: #111;
                        font-size: 18px;
                        z-index: 2;
                    }

                    .enquiry-input {
                        height: 44px;
                        border: 1px solid #9e9e9e;
                        border-radius: 3px;
                        padding-left: 38px;
                        background: #fff;
                        box-shadow: none !important;
                    }

                    .enquiry-check {
                        font-size: 12px;
                        color: #666;
                    }

                    .enquiry-check a {
                        color: #666;
                        text-decoration: underline;
                    }

                    .enquiry-btn {
                        background: #0d6efd;
                        color: #fff;
                        font-weight: 700;
                        border: none;
                        border-radius: 4px;
                        height: 44px;
                        font-size: 18px;
                    }

                    .enquiry-btn:hover {
                        background: #0b5ed7;
                        color: #fff;
                    }

                    .related-search-card {
                        background: #f7f7f7;
                        border: 1px solid #cfcfcf;
                        border-radius: 10px;
                        padding: 14px;
                    }

                    .related-search-title {
                        font-size: 20px;
                        font-weight: 700;
                        color: #111;
                        margin-bottom: 18px;
                    }

                    .related-search-item {
                        display: flex;
                        gap: 12px;
                        align-items: flex-start;
                        padding-bottom: 14px;
                        margin-bottom: 14px;
                        border-bottom: 1px solid #e5e5e5;
                    }

                    .related-search-item:last-child {
                        border-bottom: none;
                        margin-bottom: 0;
                        padding-bottom: 0;
                    }

                    .related-search-img {
                        width: 68px;
                        min-width: 68px;
                        height: 68px;
                        border-radius: 10px;
                        overflow: hidden;
                        background: #fff;
                    }

                    .related-search-img img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        display: block;
                    }

                    .related-search-content {
                        flex: 1;
                    }

                    .related-search-content h6 {
                        font-size: 17px;
                        font-weight: 600;
                        color: #111;
                        margin-bottom: 4px;
                        line-height: 1.4;
                    }

                    .related-search-content p {
                        font-size: 13px;
                        color: #666;
                        margin-bottom: 10px;
                    }

                    .related-search-btn {
                        display: inline-block;
                        padding: 6px 14px;
                        border: 1px solid #0d6efd;
                        color: #0d6efd;
                        border-radius: 6px;
                        font-size: 13px;
                        font-weight: 600;
                        text-decoration: none;
                        background: #fff;
                        transition: 0.2s ease;
                    }

                    .related-search-btn:hover {
                        background: #0d6efd;
                        color: #fff;
                    }

                    @media(max-width:991px) {
                        .sticky-floating-form {
                            left: 10px !important;
                            right: 10px !important;
                            width: auto !important;
                            max-width: none !important;
                            top: 80px;
                        }
                    }

                    @media(max-width:767px) {
                        .enquiry-title {
                            font-size: 16px;
                        }

                        .enquiry-btn {
                            font-size: 16px;
                        }

                        .related-search-title {
                            font-size: 18px;
                        }

                        .related-search-content h6 {
                            font-size: 15px;
                        }

                        .related-search-img {
                            width: 60px;
                            min-width: 60px;
                            height: 60px;
                        }
                    }
                    .category-content-box{
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 4px 20px rgba(0,0,0,0.06);
    border:1px solid #ececec;
}

.category-content-header{
    border-bottom:1px solid #eee;
    padding-bottom:15px;
    margin-bottom:20px;
}

.category-content-header h2{
    font-size:18px;
    font-weight:600;
    color:#111;
    margin-bottom:8px;
}

.category-content-header p{
    margin:0;
    color:#666;
    font-size:15px;
}

@media(max-width:767px){

    .category-content-box{
        padding:18px;
        border-radius:12px;
    }

    .category-content-header h2{
        font-size:22px;
    }
}

   .ww{font-size: 13px;
    font-weight: 500;
    line-height: 1.2;}
@media (max-width:767px){

    .deeke{
        margin-left: 15px;
        margin-right: 15px;
    }

}
                </style>
                <script>    
                    document.addEventListener("DOMContentLoaded", function () {
                        const stickyForm = document.getElementById("stickyFloatingForm");
                        const triggerSection = document.getElementById("relatedSearchTrigger");
                        const sidebarColumn = document.getElementById("sidebarColumn");
                        const listingSection = document.getElementById("listingSection");

                        function setStickyPosition() {
                            if (window.innerWidth <= 991) return;
                            
                            const rect = sidebarColumn.getBoundingClientRect();
                            stickyForm.style.width = sidebarColumn.offsetWidth + "px";
                            stickyForm.style.left = rect.left + "px";
                            stickyForm.style.right = "auto";
                        }

                        function toggleStickyForm() {
                            if (!stickyForm || !triggerSection || !listingSection) return;

                            const triggerRect = triggerSection.getBoundingClientRect();
                            const listingRect = listingSection.getBoundingClientRect();
                            const stickyTop = 95;
                            const stickyHeight = stickyForm.offsetHeight || 320;

                            const startSticky = triggerRect.bottom < -100;
                            const stopSticky = listingRect.bottom <= (stickyTop + stickyHeight + 20);

                            if (startSticky && !stopSticky) {
                                setStickyPosition();
                                stickyForm.classList.add("show");
                            } else {
                                stickyForm.classList.remove("show");
                            }
                        }

                        window.addEventListener("scroll", toggleStickyForm);
                        window.addEventListener("resize", function () {
                            setStickyPosition();
                            toggleStickyForm();
                        });

                        setStickyPosition();
                        toggleStickyForm();
                    });
                </script>
            </div>
        </div>
    </section>

@php
    $parentCategory = null;

    if (!empty($selectedSubcategory?->category_id)) {
        $parentCategory = \App\Models\Category::find(
            $selectedSubcategory->category_id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Heading ke liye current category/subcategory
    |--------------------------------------------------------------------------
    */
    $headingCategory = $selectedSubcategory
        ?? $selectedCategory
        ?? $parentCategory
        ?? null;

    /*
    |--------------------------------------------------------------------------
    | Description ke liye priority
    |--------------------------------------------------------------------------
    */
    if (!empty($selectedSubcategory?->description)) {
        $contentCategory = $selectedSubcategory;
    } elseif (!empty($selectedCategory?->description)) {
        $contentCategory = $selectedCategory;
    } elseif (!empty($parentCategory?->description)) {
        $contentCategory = $parentCategory;
    } else {
        $contentCategory = null;
    }

    $dynamicLocation = !empty($locationText)
        ? $locationText
        : 'Jaipur';

    if (str_contains($dynamicLocation, ',')) {
        $parts = explode(',', $dynamicLocation);
        $dynamicLocation = trim(end($parts));
    }

    $headingName = trim(
        $headingCategory?->name
        ?? $categoryText
        ?? request('categorysearch')
        ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | "Services Services" prevent karega, aur category name ekvachan
    | (singular) me rakhega — "Astrologers Services" nahi, "Astrologer
    | Services" chahiye.
    |--------------------------------------------------------------------------
    */
    $headingNameSingular = \Illuminate\Support\Str::singular($headingName);

    $headingWithServices = preg_match('/\bservices\s*$/i', $headingNameSingular)
        ? $headingNameSingular
        : $headingNameSingular . ' Services';

    /*
    |--------------------------------------------------------------------------
    | Description sirf available hone par process hogi
    |--------------------------------------------------------------------------
    */
    $description = $contentCategory?->description ?? '';

    $dynamicDescription = str_ireplace(
        'Jaipur',
        $dynamicLocation,
        $description
    );

    /*
    |--------------------------------------------------------------------------
    | Content normalise — har category ek jaisi dikhe
    |--------------------------------------------------------------------------
    |
    | Pehle sirf style attribute strip hota tha, jisse Word ka baaki markup
    | (<o:p>, <font size>, <font color>, nested <div>) bacha reh jaata tha —
    | isi wajah se kisi category ka font chhota, kisi ka rang alag, aur beech
    | me random gap aa jaate the. Ab poora markup normalise hota hai, taki
    | styling sirf neeche wali CSS decide kare.
    |
    | Render par bhi clean hota hai (sirf save par nahi), isliye jo 70
    | categories pehle se likhi hui hain unhe dobara save karne ki zarurat
    | nahi — wo apne aap sahi dikhengi.
    */
    $cleanDescription = \App\Support\ContentHtml::cleanCached($dynamicDescription);
@endphp

@if(!empty($headingName))
    @php
        // Lander context used by the TL;DR answer block and the nearby-cities
        // module below. A city×service page has $locationText; the nationwide
        // /[service] hub does not.
        $landerHasCity  = !empty($locationText);
        $landerCityName = $landerHasCity ? $dynamicLocation : null;
        $landerCatSlug  = $selectedCategory?->slug ?? $selectedSubcategory?->slug ?? null;
        $landerProviders = isset($listings)
            ? (method_exists($listings, 'total') ? $listings->total() : $listings->count())
            : 0;
        $landerServiceName = $selectedSubcategory?->name ?? $selectedCategory?->name ?? $headingName;

        // Popular cities to cross-link this service into (skip the current one).
        $landerNearbyCities = collect();
        if ($landerCatSlug && function_exists('activeLocations')) {
            $landerNearbyCities = activeLocations()
                ->where('is_popular', 1)
                ->when($landerCityName, fn ($c) => $c->reject(
                    fn ($loc) => strtolower($loc->name) === strtolower($landerCityName)
                ))
                ->sortBy('name')
                ->take(10)
                ->values();
        }
    @endphp

    <div class="deeke custom-right">

        <div class="category-content-header">
            <div class="listing-main-title">

                <h1 class="listing-main-title">
                    {{ $headingWithServices }}
                </h1>

                @if(!empty($dynamicLocation))
                    <span>in {{ $dynamicLocation }}</span>
                @endif

            </div>
        </div>

        {{-- Direct-answer TL;DR. AI Overviews and featured snippets preferentially
             cite a short, self-contained answer at the top of the page. --}}
        <p class="lander-answer-tldr">
            @if($landerHasCity)
                {{ $seoSiteName }} lists
                @if($landerProviders > 0) {{ $landerProviders }} verified @else verified @endif
                {{ $landerServiceName }} in {{ $landerCityName }}. Compare providers by rating, price
                and location, read genuine customer reviews, and contact them directly — {{ $seoSiteName }}
                is free to use and you pay only the professional at their listed rate.
            @else
                {{ $seoSiteName }} lists verified {{ $landerServiceName }} across India. Compare providers
                by rating, price and location, read genuine customer reviews, and contact them directly —
                {{ $seoSiteName }} is free to use and you pay only the professional at their listed rate.
            @endif
        </p>

        @if(!empty(trim(strip_tags($cleanDescription))))
            <div class="category-content-body rich-content">
                {!! $cleanDescription !!}
            </div>
        @endif

        {{-- Nearby-cities internal-link module: same service in other cities.
             Builds the siloed Service → City internal linking the SEO audit
             asked for, and gives crawlers a path into every city variant. --}}
        @if($landerCatSlug && $landerNearbyCities->isNotEmpty())
            <div class="lander-nearby-cities">
                <h2>{{ $landerServiceName }} in @if($landerHasCity)nearby @endif cities</h2>
                <ul class="lander-nearby-cities-list">
                    @foreach($landerNearbyCities as $nearbyCity)
                        <li>
                            <a href="{{ url('/' . $nearbyCity->slug . '/' . $landerCatSlug) }}">
                                {{ $landerServiceName }} in {{ $nearbyCity->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
@endif

<style>

      .listing-main-title {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.listing-main-title h2 {
    margin: 0;
    display: inline;
}

.lander-answer-tldr {
    background: #f6f8fb;
    border-left: 4px solid var(--primary-color, #2563eb);
    padding: 12px 16px;
    border-radius: 6px;
    margin: 10px 0 16px;
    font-size: 15px;
    line-height: 1.55;
    color: #1a1a1a;
}

.lander-nearby-cities {
    margin: 20px 0 8px;
}

.lander-nearby-cities h2 {
    font-size: 18px;
    margin-bottom: 10px;
}

.lander-nearby-cities-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px 10px;
}

.lander-nearby-cities-list li a {
    display: inline-block;
    padding: 6px 12px;
    background: #f1f5f9;
    border-radius: 20px;
    font-size: 13px;
    color: #334155;
    text-decoration: none;
    transition: background .15s ease;
}

.lander-nearby-cities-list li a:hover {
    background: #e2e8f0;
}
</style>

{{-- Content ki typography ab blog details ke saath shared hai. --}}
@include($themeView.'includes.rich-content')

<style>
    .category-content-body.rich-content.rich-content,
    .category-content-body.rich-content.rich-content p,
    .category-content-body.rich-content.rich-content li,
    .category-content-body.rich-content.rich-content span,
    .category-content-body.rich-content.rich-content div,
    .category-content-body.rich-content.rich-content blockquote {
        line-height: 1.45;
    }

    .category-content-body.rich-content.rich-content > * {
        margin-bottom: 6px;
    }

    .category-content-body.rich-content.rich-content h2,
    .category-content-body.rich-content.rich-content h3,
    .category-content-body.rich-content.rich-content h4,
    .category-content-body.rich-content.rich-content h5 {
        margin: 10px 0 3px;
        line-height: 1.25;
    }

    .category-content-body.rich-content.rich-content p {
        margin-bottom: 6px;
    }

    .category-content-body.rich-content.rich-content ul,
    .category-content-body.rich-content.rich-content ol {
        margin: 6px 0 8px;
    }

    .category-content-body.rich-content.rich-content li {
        margin-bottom: 4px;
    }

    /*
     * Table sizing/layout (display, column widths, word-break) is owned
     * entirely by includes/rich-content.blade.php now. A duplicate block
     * used to live here with different values (9.5px font, 35/38/27 column
     * split, no word-break rule) — same selectors, loaded after the
     * include, so it silently won every tie and fought the "properly
     * fixed" version there. Only the line-height/heading-margin overrides
     * that are actually unique to this page stay here.
     */
    @media (max-width: 767px) {
        .category-content-body.rich-content.rich-content,
        .category-content-body.rich-content.rich-content p,
        .category-content-body.rich-content.rich-content li,
        .category-content-body.rich-content.rich-content span,
        .category-content-body.rich-content.rich-content div,
        .category-content-body.rich-content.rich-content blockquote {
            line-height: 1.42;
        }

        .category-content-body.rich-content.rich-content h2,
        .category-content-body.rich-content.rich-content h3,
        .category-content-body.rich-content.rich-content h4,
        .category-content-body.rich-content.rich-content h5 {
            margin: 8px 0 3px;
        }
    }
</style>

   @if(!empty($starListings) && $starListings->count() > 0)
    <div class="featured-links-section">
        <div class="custom-right">
            <h2 class="featured-links-title">
            {{ $categoryText }}{{ !empty($locationText) ? ' in ' . $locationText : '' }}
        </h2>

        <div class="featured-links-list">
            @foreach($starListings as $star)
                <a href="{{ route('listings.details', [
                    'category' => $star->category->slug ?? $selectedCategory->slug ?? '#',
                    'slug' => $star->slug
                ]) }}">
                    {{ $star->name }}
                </a>

                @if(!$loop->last)
                    <span>|</span>
                @endif
            @endforeach
        </div>
        </div>
    </div>
@endif

@if(isset($relatedSubcategories) && $relatedSubcategories->isNotEmpty())

    <div class="featured-links-section featured-links-section--collapsible">
        <div class="custom-right">

            <h2 class="featured-links-title">
                <span>Related Categories in {{ $locationText }}</span>
                <span class="featured-links-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
            </h2>

            <div class="featured-links-list">

            @foreach($relatedSubcategories->take(50) as $relatedSubcategory)
                    @php
                        $citySlug = \Illuminate\Support\Str::slug(
                            optional($location ?? null)->name
                            ?? request()->segment(1)
                        );

                        $currentAreaSlug = '';

                        $secondSegment = request()->segment(2);

                        /*
                        |--------------------------------------------------------------------------
                        | Sirf current URL me "-in-" ho tabhi area preserve hoga
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($secondSegment) &&
                            str_contains($secondSegment, '-in-')
                        ) {
                            [, $currentAreaSlug] = explode(
                                '-in-',
                                $secondSegment,
                                2
                            );
                        }

                        if (!empty($currentAreaSlug)) {
                            $relatedUrl = url(
                                $citySlug . '/' .
                                $relatedSubcategory->slug .
                                '-in-' .
                                $currentAreaSlug
                            );
                        } else {
                            $relatedUrl = url(
                                $citySlug . '/' .
                                $relatedSubcategory->slug
                            );
                        }
                    @endphp

                    <a href="{{ $relatedUrl }}">
                        {{ $relatedSubcategory->name }}
                    </a>

                    @if(!$loop->last)
                        <span>|</span>
                    @endif

                @endforeach

            </div>

        </div>
    </div>

@endif

{{-- Category Localities --}}

@if(isset($categoryLocalities) && $categoryLocalities->isNotEmpty())

    @php
        $localityCategoryName =
            $selectedSubcategory->name
            ?? $selectedCategory->name
            ?? $categoryText
            ?? '';

        $localityCategorySlug =
            $selectedSubcategory->slug
            ?? $selectedCategory->slug
            ?? '';

        $localityCityName =
            optional($location ?? null)->name
            ?? request('location_name')
            ?? ucwords(str_replace('-', ' ', request()->segment(1) ?? ''));

        $localityCitySlug = \Illuminate\Support\Str::slug(
            $localityCityName
        );
    @endphp

    <div class="featured-links-section featured-links-section--collapsible">
        <div class="custom-right">

            <h2 class="featured-links-title">
                <span>{{ $localityCategoryName }} in {{ $localityCityName }} Localities</span>
                <span class="featured-links-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
            </h2>

            <div class="featured-links-list">

                @foreach($categoryLocalities as $locality)

                    <a href="{{ url(
                        $localityCitySlug . '/' .
                        $localityCategorySlug . '-in-' .
                        \Illuminate\Support\Str::slug($locality)
                    ) }}">
                        {{ $localityCategoryName }} in {{ $locality }}
                    </a>

                    @if(!$loop->last)
                        <span>|</span>
                    @endif

                @endforeach

            </div>

        </div>
    </div>

@endif

@if(isset($popularCities) && $popularCities->count() > 0)

    @php
        $categoryName = $selectedSubcategory->name
            ?? $selectedCategory->name
            ?? $categoryText;

        $categorySlug = $selectedSubcategory->slug
            ?? $selectedCategory->slug;
    @endphp

    <div class="featured-links-section featured-links-section--collapsible">
        <div class="custom-right">

            <h2 class="featured-links-title">
                <span>{{ $categoryName }} in Popular Cities</span>
                <span class="featured-links-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
            </h2>

            <div class="featured-links-list">

                @foreach($popularCities as $city)

                    <a href="{{ url($city->slug . '/' . $categorySlug) }}">
                        {{ $categoryName }} in {{ $city->name }}
                    </a>

                    @if(!$loop->last)
                        <span>|</span>
                    @endif

                @endforeach

            </div>

        </div>
    </div>

@endif

<style>
.featured-links-section {
    padding: 16px 0px 14px;
    border-top: 1px solid #eee;
    background: #fff;
}

.featured-links-title {
    font-size: 16px;
    font-weight: 600;
    color: #111;
   
}

.featured-links-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    font-weight: 400;
    font-size: 14px;
    line-height: 1.4;
}

.featured-links-list a {
    color: #555;
   
}

.featured-links-list span {
    color: #777;
}

/* Toggle icon (Related Categories / Localities / Popular Cities): hidden
   by default, only shown — and only clickable — on mobile below. */
.featured-links-toggle-icon {
    display: none;
}

@media(max-width:767px) {
    .featured-links-title {
        font-size: 15px;
    }

    .featured-links-list {
        font-size: 14px;
    }

    /*
     * Indentation fix: these sections had 0 horizontal padding, so their
     * text sat flush against the screen edge instead of lining up with
     * the listing cards above them (which have 15px of left/right
     * padding). 15px matches that existing gutter. Desktop is untouched —
     * .custom-right already handles its horizontal margin there (40px),
     * and it already lined up correctly with the listing cards.
     */
    .featured-links-section {
        padding-left: 15px;
        padding-right: 15px;
    }

    /*
     * Related Categories / Localities / Popular Cities only (the
     * .featured-links-section--collapsible marker keeps this off the
     * unrelated "{category} in {location}" star-listings block above,
     * which shares the same base classes but wasn't asked for here):
     * same expand-on-tap dropdown as "Popular Categories" elsewhere on
     * the site. Collapsed by default; JS below toggles .mobile-open.
     */
    .featured-links-section--collapsible .featured-links-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        margin-bottom: 0;
    }

    .featured-links-section--collapsible .featured-links-toggle-icon {
        display: inline-flex;
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        color: #111;
        font-size: 12px;
        line-height: 1;
    }

    .featured-links-section--collapsible .featured-links-list {
        display: none;
        margin-top: 10px;
    }

    .featured-links-section--collapsible .featured-links-list.mobile-open {
        display: flex;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Mobile-only dropdown for Related Categories / Localities / Popular
    // Cities (see CSS above — the toggle icon and .widget-body collapse
    // are both display:none outside max-width:767px, so this is a no-op
    // on desktop/tablet either way). Scoped to
    // .featured-links-section--collapsible so the unrelated star-listings
    // block above (same base classes, no --collapsible marker, no toggle
    // icon in its markup) is never bound.
    document.querySelectorAll('.featured-links-section--collapsible .featured-links-title').forEach(function (title) {
        var list = title.parentElement.querySelector('.featured-links-list');
        if (!list) {
            return;
        }

        title.setAttribute('aria-expanded', 'false');

        title.addEventListener('click', function () {
            var isOpen = list.classList.toggle('mobile-open');
            title.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            var icon = title.querySelector('.featured-links-toggle-icon i');
            if (icon) {
                icon.classList.toggle('fa-plus', !isOpen);
                icon.classList.toggle('fa-minus', isOpen);
            }
        });
    });
});
</script>

    <script>
        function openSidebar() {
            var sidebar = document.getElementById("sidebar");
            var overlay = document.getElementById("overlay");

            if (sidebar) sidebar.classList.add("active");
            if (overlay) overlay.classList.add("active");

            document.body.style.overflow = "hidden";
        }

        function closeSidebar() {
            var sidebar = document.getElementById("sidebar");
            var overlay = document.getElementById("overlay");

            if (sidebar) sidebar.classList.remove("active");
            if (overlay) overlay.classList.remove("active");

            document.body.style.overflow = "auto";
        }

        function scrollFilters(direction) {
            var viewport = document.querySelector('.filters-viewport');
            if (!viewport) return;

            viewport.scrollBy({
                left: direction * 180,
                behavior: 'smooth'
            });
        }

        function toggleDropdown(button) {
            var currentDropdown = button.closest('.custom-dropdown');
            var viewport = document.querySelector('.filters-viewport');
            if (!currentDropdown) return;

            var isOpen = currentDropdown.classList.contains('open');

            document.querySelectorAll('.custom-dropdown').forEach(function (drop) {
                drop.classList.remove('open');
            });

            if (viewport) {
                viewport.classList.remove('dropdown-open');
            }

            if (!isOpen) {
                currentDropdown.classList.add('open');
                if (viewport) {
                    viewport.classList.add('dropdown-open');
                }
            }
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.custom-dropdown')) {
                document.querySelectorAll('.custom-dropdown').forEach(function (drop) {
                    drop.classList.remove('open');
                });

                var viewport = document.querySelector('.filters-viewport');
                if (viewport) {
                    viewport.classList.remove('dropdown-open');
                }
            }
        });
    </script>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const backBtn = document.getElementById("ytSearchBackBtn");

    if(backBtn){
        backBtn.onclick = function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            window.location.href = "{{ route('home') }}";
        };
    }
});
</script>
@endsection
