<!DOCTYPE html>
<html lang="en-IN">
<head>
	{{-- The LCP image's preload has to be the first thing the parser sees.
	     Pushed onto the css stack it landed after the analytics tag and the
	     three blocking stylesheets, and the measured cost was 1.36s of "load
	     delay" — the browser simply had not been told about the image yet
	     while it fetched everything else. --}}
	@stack('lcp')

	@include('includes.seo')
	{{-- ============================================================
	     CSS LOADING STRATEGY
	     Critical (render-blocking): bootstrap, index, style — needed for LCP layout.
	     Non-critical (async via preload/onload): everything else — icons, sliders,
	     toasts, galleries, colors — deferred so they never block first paint.
	     FontAwesome 7 alone is ~400KB; deferring it was the single biggest LCP win.
	     ============================================================ --}}

	{{-- Those three stylesheets are 254 KB and they blocked the first paint —
	     measured at 590-1090 ms, and half of the LCP's "render delay" on top.
	     What the first screen actually needs out of them is 31 KB, extracted
	     with penthouse against both the phone and the desktop viewport, so
	     neither paints unstyled. That goes inline (no request at all), and the
	     full files then load the same asynchronous way every other stylesheet
	     here already does — they arrive in time for anything below the fold. --}}
	@include('themes.servicebazzar.partials.inline-css', ['file' => 'critical.css'])

	<link rel="preload" href="{{ asset($themeAsset.'css/bootstrap.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<link rel="preload" href="{{ asset($themeAsset.'css/index.css') }}?v={{ filemtime(public_path($themeAsset.'css/index.css')) }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<link rel="preload" href="{{ asset($themeAsset.'css/style.css') }}?v={{ filemtime(public_path($themeAsset.'css/style.css')) }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript>
		<link rel="stylesheet" href="{{ asset($themeAsset.'css/bootstrap.min.css') }}">
		<link rel="stylesheet" href="{{ asset($themeAsset.'css/index.css') }}">
		<link rel="stylesheet" href="{{ asset($themeAsset.'css/style.css') }}">
	</noscript>

	{{-- LCP preload is set dynamically per-page (see index.blade.php @push('css')) --}}

	{{-- NON-CRITICAL CSS — async load, never blocks paint --}}
	{{-- FontAwesome 7 (~400KB) --}}
	<link rel="preload" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css') }}"></noscript>

	{{-- Line Awesome icons --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/line-awesome.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/line-awesome.min.css') }}"></noscript>

	{{-- Select2 dropdown --}}

	{{-- Slick slider --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/slick.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/slick.css') }}"></noscript>

	{{-- Toastr notifications --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/toastr.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/toastr.min.css') }}"></noscript>

	{{-- Lightcase gallery --}}

	{{-- Star ratings --}}

	{{-- Dynamic color CSS --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/colorCss.php') }}?color={{ str_replace('#', '', $setting->first_color) }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/colorCss.php') }}?color={{ str_replace('#', '', $setting->first_color) }}"></noscript>

	@stack('css')

	{{-- Google Fonts — non-blocking with font-display:swap --}}
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-01.css'])

	@include('themes.servicebazzar.layouts.site.site-01')
@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-02.css'])
</head>
@php
    $sbIsHomeLike = request()->routeIs('home') || request()->routeIs('home.city') || request()->routeIs('single.slug');

    /*
     * The mobile header has two shapes, and which one a page uses was decided
     * in JavaScript at the very end of a long script. So the phone painted the
     * plain header first and swapped in the search bar a moment later — a
     * visible jump on every page. The class is written here instead, so the
     * right header is there in the first paint. Every rule that reads it sits
     * inside @media (max-width: 991px), so this changes nothing on desktop.
     */
    $sbBodyClass = trim(($sbIsHomeLike ? 'home-page' : '')
        . (request()->routeIs('home') ? '' : ' mobile-yt-search-open')
        // Every page but the home page gets the two-row phone header (back +
        // logo + actions, then the search bar), like JustDial's.
        . (request()->routeIs('home') ? '' : ' jd-head'));
@endphp
<body class="{{ $sbBodyClass }}"
    data-page-name="{{ $pageName ?? request()->path() }}"
    @if(!empty($trackListingId)) data-listing-id="{{ $trackListingId }}" @endif
    @if(!empty($trackCategoryId)) data-category-id="{{ $trackCategoryId }}" @endif
    @if(!empty($trackLocationId)) data-location-id="{{ $trackLocationId }}" @endif
    @if(isset($searchResultsCount)) data-search-results-count="{{ $searchResultsCount }}" @endif
    @if(!empty($searchKeyword)) data-search-keyword="{{ $searchKeyword }}" @endif
    @if(!empty($searchCategoryId)) data-search-category-id="{{ $searchCategoryId }}" @endif
    @if(!empty($searchLocationId)) data-search-location-id="{{ $searchLocationId }}" @endif
>
@auth
    @php
        // The business named in the profile menu must be a live (approved)
        // one; a pending or rejected listing isn't the user's business yet.
        $sbHeaderBusiness = auth()->user()->listings()->where('status', 1)->latest()->first();
        // The header "My Business" link opens the user's listings page, which
        // shows pending ones with their status, so any listing counts there.
        $sbHasBusinessListing = $sbHeaderBusiness || auth()->user()->listings()->exists();
    @endphp
@endauth
    <a href="#main-content" class="skip-link">
    Skip to main content
</a>

{{-- Page-transition loader — category/subcategory pages are the biggest
     listing queries on the site, and a plain click gave no feedback while
     the next page rendered. Shown on any same-site link click, hidden again
     if the browser restores this page from bfcache (back button) instead of
     actually navigating. See the script right after this markup. --}}
<div id="pageLoader" class="page-loader" aria-hidden="true">
    <div class="page-loader-spinner"></div>
</div>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-03.css'])
@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-04.css'])
@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-05.css'])


@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-01.js'])

	<header class="header-area" id="stickyScrollHeader">
		<div class="bottom-header">
			<div class="container-fuldie custom-right .head-right ">
				<nav class="navbar navbar-expand-lg navbar-light">
                @if(!request()->routeIs('home'))
                {{-- Phone, every page but home: first header row — back, logo, share, bell/profile. --}}
                <div class="jdh-top">
                    <button type="button" class="jdh-ico" aria-label="Go back" onclick="goBackPage()"><i class="las la-angle-left"></i></button>
                    <a href="{{ route('home') }}" class="jdh-logo"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar"></a>
                    <button type="button" class="jdh-ico jdh-share" aria-label="Share this page" onclick="sbJdhShare()"><img src="{{ asset('assets/images/download.svg') }}" alt="" class="jdh-share-img"></button>
                    @auth
                        <button type="button" class="sb-bell jdh-ico" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="sbn-badge" hidden></span></button>
                    @else
                        <button type="button" class="jdh-ico" aria-label="Log in" onclick="openSbAuthPopup()"><i class="las la-user-circle"></i></button>
                    @endauth
                </div>
                @endif
                @if(!request()->routeIs('home') && !request()->routeIs('home.city') && !request()->routeIs('single.slug'))
<button type="button" class="mobile-back-btn" aria-label="Go back" onclick="goBackPage()">
    <i class="las la-angle-left"></i>
</button>
@endif
					<a class="navbar-brand" href="{{ route('home') }}">
						<img class="logo-hight" src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
					</a>
                  <div id="scrollSearchHolder">
    @include($themeView.'includes.search-form')
</div>

<button type="button" id="mobileHeaderSearchBtn" class="mobile-header-search-btn" aria-label="Search">
    <i class="fas fa-search"></i>
</button>

@php
    $mobileSearchValue = session('selected_category', '');
    $mobileLocationValue = english_location_name(session('selected_location', ''));
@endphp

<div class="youtube-mobile-search" id="youtubeMobileSearch">
    <button type="button" class="yt-search-back" id="ytSearchBackBtn" aria-label="Back">
        <i class="las la-angle-left"></i>
    </button>

    <form action="{{ route('listings.search') }}" method="GET" class="yt-search-form">
      <div class="yt-search-input-box">

    <button type="submit" class="yt-search-submit left-search" aria-label="Search">
        <i class="fas fa-search"></i>
    </button>

    <input type="text"
           name="categorysearch"
           id="ytMobileSearchInput"
           value="{{ $mobileSearchValue }}"
           placeholder="Search Service Bazzar"
           autocomplete="off">

    <button type="button" class="yt-mic-btn inside-mic" id="ytMicBtn" aria-label="Voice search">
        <i class="fas fa-microphone"></i>
    </button>

    <ul id="ytMobileCategorySuggestions" class="yt-mobile-suggestions"></ul>

</div>

        
    </form>
</div>

				@guest
<button class="navbar-toggler"
        type="button"
        onclick="openSbAuthPopup()">
    <img src="{{ asset('assets/images/149071.png') }}" alt="User profile" class="profile-icon">
</button>
@else
<button type="button" class="sb-bell sb-bell-mobile" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="sbn-badge" hidden></span></button>
<a href="{{ route('user.dashboard') }}" class="navbar-toggler profile-dashboard-link">
    <img src="{{ userAvatarUrl(auth()->user()->image ?? null) }}" alt="{{ auth()->user()->name ?? 'User profile' }}" class="profile-icon">
</a>
@endguest

				

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						@php
							$menuServices = activeServices();
						@endphp

						<ul class="navbar-nav ml-auto">
							<li class="nav-item dropdown hide-on-scroll-menu d-flex align-items-center">

    <a class="nav-link"
       href="{{ route('home.services.index') }}">
        Services
    </a>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-06.css'])
    <a class="nav-link dropdown-toggle dropdown-toggle-split"
       href="#"
       id="servicesDropdown"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="false"
       aria-label="Open the services menu">
    </a>

    <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
        @forelse($menuServices as $item)
            <li>
                <a class="dropdown-item"
                   href="{{ route('services.show', $item->slug) }}">
                    {{ $item->menu_title ?: $item->title }}
                </a>
            </li>
        @empty
            <li>
                <span class="dropdown-item">No Services Found</span>
            </li>
        @endforelse
    </ul>

</li>

						<li class="nav-item hide-on-scroll-menu">
								<a class="nav-link" href="{{ route('blogs') }}">@lang('Blog')</a>
							</li>

							<li class="nav-item hide-on-scroll-menu">
    <a class="nav-link" href="{{ route('contact') }}">Contact</a>
</li>

							<li class="nav-item">
    @guest
        <a href="{{ route('free.listing') }}"
           onclick="event.preventDefault(); openSbAuthPopup();"
           class="nav-link">
            <i class="fa-solid fa-arrow-trend-up me-1"></i>
            @lang('Free Listing')
        </a>
    @else
        @if($sbHasBusinessListing)
            <a href="{{ route('user.listing.index') }}" class="nav-link">
                <i class="fa-solid fa-briefcase me-1"></i>
                @lang('My Business')
            </a>
        @else
            <a href="{{ route('free.listing') }}" class="nav-link">
                <i class="fa-solid fa-arrow-trend-up me-1"></i>
                @lang('Free Listing')
            </a>
        @endif
    @endguest
</li>
							@guest
								<li class="nav-item">
						{{-- Real destination so the link is crawlable and can be
						     opened in a new tab; the popup still handles a click. --}}
						<a href="{{ route('user.login') }}"
						   onclick="event.preventDefault(); openSbAuthPopup();" class="nav-link nav-btn">
    Login / Sign Up
</a>
								</li>
							@else
								<li class="nav-item d-none d-lg-flex align-items-center">
									<button type="button" class="sb-bell" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="sbn-badge" hidden></span></button>
								</li>
								<li class="nav-item profile-dashboard-item">
									<a href="{{ route('user.dashboard') }}" class="nav-link nav-btn profile-dashboard-link d-flex align-items-center gap-2" title="{{ auth()->user()->fullname() }}">
										<img src="{{ userAvatarUrl(auth()->user()->image ?? null) }}" alt="{{ auth()->user()->fullname() }}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;">
									</a>
								</li>
							@endguest
						</ul>
					</div>
				</nav>

			
				<div class="jd-mobile-location-strip" id="jdMobileLocationStrip">
				<button type="button" class="jd-mobile-city-btn detecting" id="jdMobileCityBtn">
						<i class="las la-map-marker"></i>
						<span id="jdMobileCityText">{{ $mobileLocationValue }}</span>
						<i class="las la-angle-down"></i>
					</button>

                    
				<div class="jd-mobile-location-box" id="jdMobileLocationBox">

    <div class="jd-location-modal-head">
        <button type="button"
                class="jd-location-back"
                id="jdLocationBackBtn">
            <i class="las la-angle-left"></i>
        </button>

        <div class="jd-location-title-main">
            Your Location
        </div>
    </div>

    <input type="text"
           id="jdMobileLocationInput"
           placeholder="Enter location..."
           value="{{ $mobileLocationValue }}">

    <div id="jdMobileLocationSuggestions"
         class="jd-mobile-location-suggestions"></div>

</div>
				</div>
			</div>
		</div>
	</header>

    {{-- The skip link at the top points here. Without an element carrying this
	     id it had nothing to jump to, which is why it never took focus. --}}
    <main id="main-content">
    @yield('content')
    </main>


	@include($themeView.'includes.footer')

	<div class="mobile-bottom-action" id="mobileBottomAction">
		<a href="{{ route('home') }}" class="mobile-action-item {{ request()->routeIs('home') ? 'active' : '' }}">
			<i class="las la-home"></i>
			<span>Home</span>
		</a>

		<a href="{{ route('blogs') }}" class="mobile-action-item">
			<i class="las la-newspaper"></i>
			<span>Blogs</span>
		</a>

		<a href="{{ route('contact') }}" class="mobile-action-item">
			<i class="las la-phone"></i>
			<span>Contact</span>
		</a>

		@guest
			<a href="{{ route('free.listing') }}"
			   onclick="event.preventDefault(); openSbAuthPopup();" class="mobile-action-btn">
				Free Listing
			</a>
		@else
            @if($sbHasBusinessListing)
                <a href="{{ route('user.listing.index') }}" class="mobile-action-btn">
                    My Business
                </a>
            @else
                <a href="{{ route('free.listing') }}" class="mobile-action-btn">
                    Free Listing
                </a>
            @endif
		@endguest
	</div>


@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-07.css'])

<div class="location-permission-popup" id="locationPermissionPopup" hidden>
    <div class="location-permission-popup__box" role="dialog" aria-modal="true" aria-labelledby="locationPermissionPopupTitle">
        <div class="location-permission-popup__head">
            <h5 id="locationPermissionPopupTitle">Turn on location</h5>
            <button type="button" class="location-permission-popup__close" data-location-popup-close aria-label="Close">&times;</button>
        </div>
        <div class="location-permission-popup__body">
            <p class="location-permission-popup__message">
                <i class="fas fa-location-dot"></i>
                <span id="locationPermissionPopupMessage">Please allow location access to detect your city.</span>
            </p>
            <ol class="location-permission-popup__steps" id="locationPermissionPopupSteps" hidden>
                <li>Tap the lock or info icon near the website address.</li>
                <li>Open <strong>Site settings</strong> or <strong>Permissions</strong>.</li>
                <li>Set <strong>Location</strong> to <strong>Allow</strong>.</li>
                <li>Come back and tap <strong>Try again</strong>.</li>
            </ol>
        </div>
        <div class="location-permission-popup__actions">
            <button type="button" class="location-permission-popup__btn location-permission-popup__btn--muted" data-location-popup-close>Cancel</button>
            <button type="button" class="location-permission-popup__btn" id="locationPermissionPopupRetry">Try again</button>
        </div>
    </div>
</div>

@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-02.js'])

	{{-- ============================================================
	     JS LOADING STRATEGY
	     jquery + bootstrap + toastr: sync — inline scripts in this layout use
	     $ and toastr directly, so they must be ready before those <script> blocks.
	     app.js: deferred — it initialises the slick slider once the DOM is up.
	     at parse time, saving ~30KB of main-thread blocking.
	     tracking.js: defer (unchanged).
	     ============================================================ --}}
	{{-- SYNC (inline scripts below need these immediately): jQuery 88KB + Bootstrap 48KB + Toastr 5.5KB --}}
	<script src="{{ asset($themeAsset.'js/jquery-3.6.0.min.js') }}"></script>
	<script src="{{ asset($themeAsset.'js/bootstrap.min.js') }}"></script>
	<script src="{{ asset($themeAsset.'js/toastr.min.js') }}"></script>

	{{-- Every page used to carry select2 (72 KB), wow (8 KB) and
	     bootstrap-notify (8 KB). Nothing in the theme calls any of them:
	     no element carries class="select2" (app.js's `$('.select2').select2()`
	     matched nothing), `new WOW` appears nowhere, and neither does
	     `.notify(`. They were downloaded, parsed and evaluated on every visit
	     for no effect, which is main-thread time the LCP image was waiting on.

	     lightcase and starrr are real, but only on the listing detail page —
	     they are pushed from there now instead of loading site-wide. --}}
	<script src="{{ asset($themeAsset.'js/slick.min.js') }}" defer></script>
	<script src="{{ asset($themeAsset.'js/app.js') }}?v={{ filemtime(public_path($themeAsset.'js/app.js')) }}" defer></script>

	<script src="{{ asset('assets/js/tracking.js') }}" defer></script>

	@stack('js')


@include('themes.servicebazzar.layouts.site.site-02')

@include('themes.servicebazzar.layouts.site.site-03')

@include('themes.servicebazzar.layouts.site.site-04')


@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-08.css'])
@include('themes.servicebazzar.layouts.site.site-05')

@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-03.js'])

@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-04.js'])

@include('themes.servicebazzar.layouts.site.site-06')

@include('themes.servicebazzar.layouts.site.site-07')

@include('themes.servicebazzar.layouts.site.site-08')

@include('themes.servicebazzar.layouts.site.site-09')

<div id="voiceModal" class="voice-modal">
    <div class="voice-box">
        <button type="button" class="voice-close" id="voiceCloseBtn" aria-label="Close">×</button>
        <h3 id="voiceStatusText">Listening.....</h3>
        <div class="voice-loader">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>
</div>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-09.css'])

<div id="sbAuthPopup" class="sb-auth-popup">
    <div class="sb-auth-box">
        <button type="button" class="sb-auth-close" id="sbAuthClose">&times;</button>
        <div id="sbMsg"></div>
        <div id="sbStepOne">
            <div class="sb-auth-head">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
                <div>
                    <h4>Welcome</h4>
                    <p>Login for a seamless experience</p>
                </div>
            </div>
            @if(config('services.google.client_id') || config('services.facebook.client_id'))
                <div class="sb-social-login">
                    @if(config('services.google.client_id'))
                        <a href="{{ route('user.social.login.redirect', ['provider' => 'google', 'redirect' => url()->current()]) }}" class="sb-social-btn sb-social-google" title="Continue with Google" aria-label="Continue with Google">
                            <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
                        </a>
                    @endif
                    @if(config('services.facebook.client_id'))
                        <a href="{{ route('user.social.login.redirect', ['provider' => 'facebook', 'redirect' => url()->current()]) }}" class="sb-social-btn sb-social-facebook" title="Continue with Facebook" aria-label="Continue with Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#1877F2" d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.95.93-1.95 1.89v2.26h3.32l-.53 3.49h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                        </a>
                    @endif
                </div>
                <div class="sb-social-divider"><span>or</span></div>
            @endif
            <form id="sbAuthForm">
                @csrf
                <input type="hidden" name="current_url" id="sbCurrentUrl">
                <div class="sb-field">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="sb-field">
                    <input type="text" name="mobile" placeholder="Enter Mobile Number" required>
                </div>
                <div class="sb-terms">
                    <label>
                        <input type="checkbox" required checked>
                        I Agree to Terms and Conditions
                    </label>
                    <br>
                    <a href="{{ url('/terms-and-conditions') }}">Terms & Conditions</a> & <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
                </div>
                <button type="submit" class="sb-btn" id="sbSendBtn">Continue</button>
            </form>
            <button type="button" id="sbMaybeLater" class="sb-later">Maybe Later</button>
        </div>
        <div id="sbStepOtp" style="display:none;">
            <div class="sb-auth-head">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
                <div>
                    <h4>Welcome</h4>
                    <p>Login for a seamless<br>experience</p>
                </div>
            </div>
            <div class="sb-otp-text">
                Enter the code sent to <span id="sbOtpSentText" style="font-weight:600"></span>
            </div>
            <form id="sbOtpForm">
                @csrf
                <input type="hidden" name="otp" id="sbFullOtp">
                <div class="sb-otp-boxes">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                </div>
                <div class="sb-otp-bottom">
                    <span>Didn’t Receive the OTP?</span>
                    <button type="button" id="sbResendOtp">Resend OTP</button>
                </div>
                <button type="submit" class="sb-btn" id="sbVerifyBtn" disabled>Continue</button>
            </form>
        </div>
    </div>
</div>

{{-- OTP step every enquiry form goes through before it submits. Sits here so
     one copy covers every page, including forms injected after load. --}}
@include('themes.servicebazzar.partials.enquiry-otp')

@auth
{{--
    Logged-in profile panel — a JustDial-style slide-in from the right,
    opened by the avatar icon in the header instead of that icon linking
    straight to /dashboard. Every link below is a route that already
    exists elsewhere on the site (dashboard, profile, listings, wallet,
    leads inbox, password, contact) — nothing here is a placeholder.
--}}
<div id="sbProfilePanelOverlay" class="sb-profile-overlay"></div>
<div id="sbProfilePanel" class="sb-profile-panel">
    <div class="sb-profile-head">
        <a href="{{ route('user.profile.view', ['username' => Str::slug(auth()->user()->first_name . '-' . auth()->user()->last_name)]) }}" class="sb-profile-identity">
            <img src="{{ userAvatarUrl(auth()->user()->image ?? null) }}" alt="{{ auth()->user()->fullname() }}">
            <span>
                <strong>{{ auth()->user()->fullname() }}</strong>
                <small>Click to view profile</small>
            </span>
        </a>
        <button type="button" class="sb-profile-close" id="sbProfilePanelClose">&times;</button>
    </div>

    @php
        // Shown as "My Business" the way JustDial surfaces the listing you
        // already run — pulled straight from this user's own listings
        // rather than anything typed in on this page, so it's only ever as
        // current as the listing itself.
        $sbMyBusiness = $sbHeaderBusiness;
    @endphp

    <div class="sb-profile-menu">
        @if($sbMyBusiness)
            <a href="{{ route('user.listing.dashboard', $sbMyBusiness->id) }}" class="sb-profile-item sb-profile-item--business">
                <i class="fa-solid fa-store"></i>
                <span>
                    {{ $sbMyBusiness->name }}
                    <small>My Business</small>
                </span>
            </a>
            <a href="{{ route('free.listing') }}" class="sb-profile-item">
                <i class="fa-solid fa-plus"></i>
                <span>Add New Business</span>
            </a>
        @else
            <a href="{{ route('free.listing') }}" class="sb-profile-item sb-profile-item--highlight">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>List Your Business</span>
            </a>
        @endif

        <a href="{{ route('user.dashboard') }}" class="sb-profile-item">
            <i class="fa-solid fa-gauge"></i>
            <span>Dashboard</span>
        </a>

        {{-- Every enquiry this business has had (the Leads page, no filter) — not the guest chats. --}}
        <a href="{{ $sbMyBusiness ? route('user.listing.leads', $sbMyBusiness->id) : route('user.listing.index') }}" class="sb-profile-item">
            <i class="fa-solid fa-inbox"></i>
            <span>Leads &amp; Enquiries</span>
        </a>

        <a href="{{ route('user.deposit') }}" class="sb-profile-item">
            <i class="fa-solid fa-wallet"></i>
            <span>Wallet</span>
        </a>

        <a href="{{ route('user.profile') }}" class="sb-profile-item">
            <i class="fa-solid fa-user-pen"></i>
            <span>Edit Profile</span>
        </a>

        <a href="{{ route('contact') }}" class="sb-profile-item">
            <i class="fa-solid fa-headset"></i>
            <span>Customer Service</span>
        </a>

        <a href="{{ url('/privacy-policy') }}" class="sb-profile-item">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Policy</span>
        </a>

        <a href="{{ route('user.logout') }}" class="sb-profile-item sb-profile-item--danger">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-10.css'])

@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-05.js'])
@endauth

@include('themes.servicebazzar.layouts.site.site-10')

@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-06.js'])

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-11.css'])


@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/site-12.css'])
@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-07.js'])
@include('themes.servicebazzar.layouts.site.site-11')

@include('themes.servicebazzar.partials.notification_bell')
@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/site-08.js'])
</body>
</html>
