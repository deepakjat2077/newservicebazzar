@extends($themeView.'layouts.site')
@section('content')
@section('title','Listings')
  

    
   
    <style>
        /* ================= SEARCH SECTION ================= */

@media (max-width: 991px) {

    .search-section{
        width:100%;
    }

    .search-section .search-box{
        width:100%;
    }

    .search-section .search-box input{
        width:100%;
        min-width:0;
    }

}


/* ================= FILTER BAR ================= */

.main-card{
    margin:15px 12px;
    position:relative;
    overflow:visible !important;
}

.filters-wrapper{
    display:flex;
    align-items:center;
    gap:10px;
    background:#fff;
    padding:10px 12px;
    border-radius:16px;
    position:relative;
    overflow:visible !important;
}

/* scroll container */

.filters-scroll{
    display:flex;
    align-items:center;
    gap:10px;
    white-space:nowrap;
   
    scroll-behavior:smooth;
    flex:1;
    position:relative;
}

.filters-scroll::-webkit-scrollbar{
    display:none;
}


/* ================= FILTER BUTTON ================= */

.filter-pill,
.custom-dropdown{
    flex:0 0 auto;
}

.filter-pill{
    border:1px solid #d9d9d9;
    background:#fff;
    color:#111;
    padding:10px 18px;
    border-radius:999px;
    font-size:15px;
    line-height:1;
    cursor:pointer;
    white-space:nowrap;
    transition:0.2s ease;
}

.filter-pill:hover{
    background:#0e0101;
}


/* ================= CUSTOM DROPDOWN ================= */

.custom-dropdown{
    position:relative;
    display:inline-block;
    overflow:visible !important;
}

.dropdown-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.custom-dropdown-menu{

    display:none;

    position:absolute;

    top:calc(100% + 8px);

    left:0;

    min-width:190px;

    background:#fff;

    border:1px solid #ddd;

    border-radius:12px;

    box-shadow:0 10px 25px rgba(0,0,0,0.12);

    z-index:999999;

    padding:8px 0;

}

.custom-dropdown.open .custom-dropdown-menu{
    display:block;
}

.custom-dropdown-menu a{

    display:block;

    padding:10px 16px;

    font-size:14px;

    color:#111;

    text-decoration:none;

}

.custom-dropdown-menu a:hover{
    background:#f5f5f5;
}


/* ================= SCROLL BUTTON ================= */

.scroll-btn{

    background:#fff;

    border:1px solid #ddd;

    width:34px;

    height:34px;

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;

}


/* ================= ALL FILTER BUTTON ================= */

.all-filters-mobile{

    background:#000;

    color:#fff;

    border:none;

    padding:7px 14px;

    border-radius:20px;

    font-size:13px;

    white-space:nowrap;

}


/* ================= FILTER SIDEBAR ================= */

.filter-sidebar{

    position:fixed;

    top:0;

    right:-100%;

    width:85%;

    max-width:400px;

    height:100vh;

    background:#fff;

    z-index:1050;

    transition:0.3s ease;

    overflow-y:auto;

    padding:20px;

}

.filter-sidebar.active{
    right:0;
}


/* ================= OVERLAY ================= */

.filter-overlay{

    position:fixed;

    top:0;

    left:0;

    width:100%;

    height:100vh;

    background:rgba(0,0,0,0.4);

    z-index:1040;

    display:none;

}

.filter-overlay.active{
    display:block;
}


/* ================= DESKTOP ================= */

@media (min-width:768px){

    .scroll-btn{
        display:none !important;
    }

    .all-filters-mobile{

        display:inline-flex !important;

        align-items:center;

        justify-content:center;

    }

}


/* ================= MOBILE ================= */

@media (max-width:767px){

    .listing-items{
        margin-top:10px;
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
    </style>



@if(isset($banners) && $banners->count() > 0)
<section  class="category-banner-slider ">
    <div id="categoryBannerCarousel" class="carousel slide" data-bs-ride="carousel">

        {{-- Indicators --}}
        <div class="carousel-indicators">
            @foreach($banners as $key => $banner)
                <button type="button"
                        data-bs-target="#categoryBannerCarousel"
                        data-bs-slide-to="{{ $key }}"
                        class="{{ $key == 0 ? 'active' : '' }}"
                        aria-current="{{ $key == 0 ? 'true' : 'false' }}"
                        aria-label="Slide {{ $key + 1 }}">
                </button>
            @endforeach
        </div>

        {{-- Slides --}}
        <div class="carousel-inner">
            @foreach($banners as $key => $banner)
                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                    <a href="{{ $banner->link ?? '#' }}" target="_blank">
                        <img src="{{ asset('assets/images/category/banner/' . $banner->desktop_banner) }}"
                             class="d-block w-100"
                             alt="{{ $banner->title ?? 'Banner' }}">
                    </a>
                </div>
            @endforeach
        </div>

       
    </div>
</section>
@endif


@php
    $locationText = $location->name ?? 'Location';
    $listingCount = method_exists($listings, 'total') ? $listings->total() : $listings->count();
@endphp

<div class="listing-top-info">
    <div class="listing-breadcrumb">
        <span>{{ $locationText }}</span>
        <span class="sep">&gt;</span>
        <span>Businesses in {{ $locationText }}</span>
        <span class="sep">&gt;</span>
        <span>{{ $listingCount }}+ Listings</span>
    </div>

    <h1 class="listing-main-title">
        Popular Businesses in {{ $locationText }}
    </h1>
</div>

@php
    $breadcrumbSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $locationText, 'item' => url()->current()],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>

<style>
    .listing-top-info{
    padding: 10px 12px 0;
}

.listing-breadcrumb{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 13px;
    color: #444;
    margin-bottom: 8px;
}

.listing-breadcrumb .sep{
    color: #888;
    font-weight: 600;
}

.listing-main-title{
    font-size: 20px;
    font-weight: 700;
    color: #111;
    margin: 0 0 12px;
    line-height: 1.3;
}

@media (max-width: 767px){
    .listing-top-info{
        padding: 10px 10px 0;
    }

    .listing-breadcrumb{
        font-size: 12px;
        gap: 4px;
    }

    .listing-main-title{
        font-size: 18px;
        margin-bottom: 10px;
    }
}
</style>

   <div class="main-card">
    <div class="filters-wrapper">

        <button class="scroll-btn d-md-none" type="button" onclick="scrollFilters(-1)">
            <i class="las la-angle-left"></i>
        </button>

        <div class="filters-scroll" id="filtersScroll">

            <!-- SORT BY -->
            <div class="custom-dropdown {{ request('sort') ? 'active-filter' : '' }}">
                <button type="button" class="filter-pill dropdown-btn" onclick="toggleDropdown(this)">
                    Sort By <i class="las la-angle-down ms-1"></i>
                </button>

                <div class="custom-dropdown-menu">
                    {{--
                        rel="nofollow": robots.txt disallows crawling ?sort=
                        URLs, but that only blocks the fetch — Google still
                        discovers/indexes the URL just from finding this
                        <a href>, which is the "Indexed, though blocked by
                        robots.txt" state Search Console flags. nofollow
                        stops it being followed/indexed at the source.
                    --}}
                    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['sort' => 'rating'])) }}">Rating</a>
                    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['sort' => 'popular'])) }}">Popular</a>
                    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['sort' => 'distance'])) }}">Distance</a>
                </div>
            </div>

            <!-- RATINGS -->
            <div class="custom-dropdown {{ request('rating') ? 'active-filter' : '' }}">
                <button type="button" class="filter-pill dropdown-btn" onclick="toggleDropdown(this)">
                    Ratings <i class="las la-angle-down ms-1"></i>
                </button>

                <div class="custom-dropdown-menu">
                    <a href="{{ route('listings.search', array_merge(request()->query(), ['rating' => 1])) }}">1 Star</a>
                    <a href="{{ route('listings.search', array_merge(request()->query(), ['rating' => 2])) }}">2 Star</a>
                    <a href="{{ route('listings.search', array_merge(request()->query(), ['rating' => 3])) }}">3 Star</a>
                    <a href="{{ route('listings.search', array_merge(request()->query(), ['rating' => 4])) }}">4 Star</a>
                    <a href="{{ route('listings.search', array_merge(request()->query(), ['rating' => 5])) }}">5 Star</a>
                </div>
            </div>

            <button type="button"
                class="filter-pill {{ request('filter') == 'top_rated' ? 'active-filter' : '' }}"
                onclick="window.location='{{ route('listings.search', array_merge(request()->query(), ['filter' => 'top_rated'])) }}'">
                Top Rated
            </button>

            <button type="button"
                class="filter-pill {{ request('filter') == 'verified' ? 'active-filter' : '' }}"
                onclick="window.location='{{ route('listings.search', array_merge(request()->query(), ['filter' => 'verified'])) }}'">
                Verified
            </button>

            <button type="button"
                class="filter-pill {{ request('filter') == 'trust' ? 'active-filter' : '' }}"
                onclick="window.location='{{ route('listings.search', array_merge(request()->query(), ['filter' => 'trust'])) }}'">
                Trust
            </button>
        </div>

        <button class="scroll-btn d-md-none" type="button" onclick="scrollFilters(1)">
            <i class="las la-angle-right"></i>
        </button>

        <button class="all-filters-mobile ms-2" type="button" onclick="openSidebar()">
            <i class="las la-sliders-h me-1"></i> All
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
        rel="nofollow" throughout this sidebar too — same reason as the
        dropdown above: robots.txt blocks the fetch, not the discovery.
    --}}
    <h6 class="mt-4">Sort By</h6>

    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['sort' => 'rating'])) }}"
       class="filter-chip d-inline-block mb-2 {{ request('sort') == 'rating' ? 'active' : '' }}">
        Rating
    </a>

    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['sort' => 'popular'])) }}"
       class="filter-chip d-inline-block mb-2 {{ request('sort') == 'popular' ? 'active' : '' }}">
        Popular
    </a>

    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['sort' => 'distance'])) }}"
       class="filter-chip d-inline-block mb-2 {{ request('sort') == 'distance' ? 'active' : '' }}">
        Distance
    </a>

    <h6 class="mt-4">Ratings</h6>

    @for($i = 1; $i <= 5; $i++)
        <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['rating' => $i])) }}"
           class="filter-chip d-inline-block mb-2 {{ request('rating') == $i ? 'active' : '' }}">
            {{ $i }} Star
        </a>
    @endfor

    <h6 class="mt-4">More Filters</h6>

    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['filter' => 'top_rated'])) }}"
       class="filter-chip d-inline-block mb-2 {{ request('filter') == 'top_rated' ? 'active' : '' }}">
        Top Rated
    </a>

    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['filter' => 'verified'])) }}"
       class="filter-chip d-inline-block mb-2 {{ request('filter') == 'verified' ? 'active' : '' }}">
        Verified
    </a>

    <a rel="nofollow" href="{{ route('listings.search', array_merge(request()->query(), ['filter' => 'trust'])) }}"
       class="filter-chip d-inline-block mb-2 {{ request('filter') == 'trust' ? 'active' : '' }}">
        Trust
    </a>

    <div class="bottom-buttons mt-4">
        <a href="{{ route('listings.search') }}" class="btn btn-reset">
            Reset Filters
        </a>

        <button class="btn btn-apply" type="button" onclick="closeSidebar()">
            Apply Filters
        </button>
    </div>
</div>

 <section class="listing pt-70 pb-80 custom-right">
    <div class="container-fluid">
        <div class="row mb__30">

            <div class="col-md-8" id="listingColumn">
                <div class="listing-items">
                    <div class="row" id="listingWrapper">
                        @include($themeView . 'partials.ajax-listings')
                    </div>
                </div>
            </div>

         <div class="col-md-4">

   <div class="enquiry-card mt-3 sticky-enquiry">
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
    <input type="hidden" name="category_name" value="{{ $selectedCategory->name ?? request('categorysearch') }}">
    <input type="hidden" name="location_name" value="{{ request('location_name') }}">

    <div class="mb-2 input-icon-group">
        <span class="input-icon"><i class="las la-user"></i></span>
        <input type="text" name="name" class="form-control enquiry-input" placeholder="Name" required>
    </div>

    <div class="mb-2 input-icon-group">
        <span class="input-icon"><i class="las la-mobile"></i></span>
        <input type="text" name="phone" class="form-control enquiry-input" placeholder="Mobile Number" required>
    </div>

    <div class="form-check enquiry-check mb-3">
        <input class="form-check-input" type="checkbox" id="policyCheck" required>
        <label class="form-check-label" for="policyCheck">
            I Agree to <a href="{{ route('single.slug', 'privacy-policy') }}">T&C's Privacy Policy</a>
        </label>
    </div>

    <button type="submit" class="btn enquiry-btn w-100">
        Send Enquiry <i class="las la-angle-double-right"></i>
    </button>
</form>
</div>

    @php
        $relatedCategories = \App\Models\Category::where('status', 1)
            ->where('id', '!=', $listing->category_id ?? 0)
            ->withCount('listings')
            ->take(5)
            ->get();
    @endphp

    <div class="related-search-card mt-3">
        <h6 class="related-search-title">People Also Search For</h6>

        @foreach($relatedCategories as $category)
            <div class="related-search-item">
                <div class="related-search-img">
                    <img src="{{ asset('assets/images/category/' . $category->image) }}"
                         alt="{{ $category->name }}">
                </div>

                <div class="related-search-content">
                    <h6>{{ $category->name }}</h6>
                    <p>{{ $category->listings_count }}+ listings</p>

                    <a href="{{ route('listings.search', ['category' => [$category->id]]) }}"
                       class="related-search-btn">
                        {{ $loop->odd ? 'Get Best Deal' : 'View Details' }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
        </div>
    </div>
</section>

<style>
.enquiry-card{
    background:#f7f7f7;
    border:1px solid #cfcfcf;
    border-radius:10px;
    padding:14px;
}

.enquiry-title{
    font-size:18px;
    font-weight:700;
    color:#111;
    margin-bottom:6px;
    line-height:1.4;
}

.enquiry-title span{
    color:#0d6efd;
}

.enquiry-subtitle{
    font-size:13px;
    color:#555;
    margin-bottom:16px;
}

.input-icon-group{
    position:relative;
}

.input-icon{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:#111;
    font-size:18px;
    z-index:2;
}

.enquiry-input{
    height:44px;
    border:1px solid #9e9e9e;
    border-radius:3px;
    padding-left:38px;
    background:#fff;
    box-shadow:none !important;
}

.enquiry-check{
    font-size:12px;
    color:#666;
}

.enquiry-check a{
    color:#666;
    text-decoration:underline;
}

.enquiry-btn{
    background:#0d6efd;
    color:#fff;
    font-weight:700;
    border:none;
    border-radius:4px;
    height:44px;
    font-size:18px;
}

.enquiry-btn:hover{
    background:#0b5ed7;
    color:#fff;
}

.related-search-card{
    background:#f7f7f7;
    border:1px solid #cfcfcf;
    border-radius:10px;
    padding:14px;
}

.related-search-title{
    font-size:20px;
    font-weight:700;
    color:#111;
    margin-bottom:18px;
}

.related-search-item{
    display:flex;
    gap:12px;
    align-items:flex-start;
    padding-bottom:14px;
    margin-bottom:14px;
    border-bottom:1px solid #e5e5e5;
}

.related-search-item:last-child{
    border-bottom:none;
    margin-bottom:0;
    padding-bottom:0;
}

.related-search-img{
    width:68px;
    min-width:68px;
    height:68px;
    border-radius:10px;
    overflow:hidden;
    background:#fff;
}

.related-search-img img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.related-search-content{
    flex:1;
}

.related-search-content h6{
    font-size:17px;
    font-weight:600;
    color:#111;
    margin-bottom:4px;
    line-height:1.4;
}

.related-search-content p{
    font-size:13px;
    color:#666;
    margin-bottom:10px;
}

.related-search-btn{
    display:inline-block;
    padding:6px 14px;
    border:1px solid #0d6efd;
    color:#0d6efd;
    border-radius:6px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    background:#fff;
    transition:0.2s ease;
}

.related-search-btn:hover{
    background:#0d6efd;
    color:#fff;
}

@media(max-width:767px){
    .enquiry-title{
        font-size:16px;
    }

    .enquiry-btn{
        font-size:16px;
    }

    .related-search-title{
        font-size:18px;
    }

    .related-search-content h6{
        font-size:15px;
    }

    .related-search-img{
        width:60px;
        min-width:60px;
        height:60px;
    }
}
</style>

    @include($themeView . 'includes.footer')

    <script>
        function openSidebar() {
            document.getElementById("sidebar").classList.add("active");
            document.getElementById("overlay").classList.add("active");
            document.body.style.overflow = "hidden";
        }

        function closeSidebar() {
            document.getElementById("sidebar").classList.remove("active");
            document.getElementById("overlay").classList.remove("active");
            document.body.style.overflow = "auto";
        }

        function scrollFilters(direction) {
            const container = document.getElementById('filtersScroll');
            const scrollAmount = 180;

            container.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }
    </script>



   
<script>
    function toggleDropdown(button) {
        const currentDropdown = button.closest('.custom-dropdown');
        const isOpen = currentDropdown.classList.contains('open');

        document.querySelectorAll('.custom-dropdown').forEach(drop => {
            drop.classList.remove('open');
        });

        if (!isOpen) {
            currentDropdown.classList.add('open');
        }
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.custom-dropdown')) {
            document.querySelectorAll('.custom-dropdown').forEach(drop => {
                drop.classList.remove('open');
            });
        }
    });

    function scrollFilters(direction) {
        const container = document.getElementById('filtersScroll');
        const scrollAmount = 180;

        container.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
</script>

 
@endsection