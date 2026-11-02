
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
<style id="servicebazzar-listing-cta-before-search">
    .banner-section{
        padding:10px 0 1px;
        border-bottom:1px solid #edf1eb;
        background:linear-gradient(180deg,#fafcf9 0%,#f4f7f2 100%);
    }
    .home-search-kicker-area{width:100%;margin:0 auto 12px !important;padding-left:15px}
    .home-search-kicker{
        margin:0 0 8px;
        color:#344054;
        font-size:clamp(17px, 1.8vw, 23px);
        font-weight:600;
        line-height:1.25;
    }
    .home-search-controls-row{
        box-sizing:border-box;
        align-items:center;
        width:100%
        margin:10px auto 15px !important;
        padding:8px 12px;
        gap:10px;
        border:1px solid #e1e9df;
        border-radius:12px;
        background:rgba(255,255,255,.88);
        box-shadow:0 3px 12px rgba(19,136,8,.045);
    }
    .home-search-controls-row .input_location_box,
    .home-search-controls-row .input_search_box{
        box-sizing:border-box;
        border:1px solid #dce6da !important;
        border-radius:8px;
        background-color:#fff;
        box-shadow:none !important;
        transition:border-color .18s ease, box-shadow .18s ease;
    }
    .home-search-controls-row .input_location_box:after{
        background:linear-gradient(to right,rgba(255,255,255,0),#fff 67%);
    }
    .home-search-controls-row .input_location_box:focus-within,
    .home-search-controls-row .input_search_box:focus-within{
        border-color:#16820d !important;
        box-shadow:0 0 0 3px rgba(22,130,13,.1) !important;
    }
    .home-search-controls-row .search_buttonh i,
    .home-search-controls-row .voice-search-btn{
        color:#16820d;
    }
    .home-search-controls-row .download-btn{
        min-height:48px;
        border:1px solid #dce6da;
        border-radius:8px;
        color:#16820d;
        font-weight:600;
        transition:background-color .18s ease,border-color .18s ease;
    }
    .home-search-controls-row .download-btn:hover,
    .home-search-controls-row .download-btn:focus-visible{
        border-color:#b8d4b4;
        background:#f4faf3;
        color:#116b0a;
        text-decoration:none;
    }
    .home-search-listing-cta{
        display:flex;
        align-items:center;
        justify-content:center;
        width:100%;
        min-height:44px;
        padding:10px 12px;
        border:1px solid #16820d;
        border-radius:8px;
        background:#16820d;
        color:#fff;
        font-size:13px;
        font-weight:700;
        line-height:1.3;
        text-align:center;
        text-decoration:none;
        white-space:nowrap;
        transition:background-color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .home-search-listing-cta:hover,
    .home-search-listing-cta:focus-visible{
        border-color:#116b0a;
        background:#116b0a;
        color:#fff;
        text-decoration:none;
        transform:translateY(-1px);
    }
    .home-search-controls-row .home-search-main{min-width:0;margin-left:0 !important;margin-right:0 !important;}
    .home-search-controls-row #homeSearchOriginalPlace{width:100% !important;min-width:0;}
    .home-search-controls-row #homeSearchOriginalPlace form{width:100%;}
    @media (min-width:768px){
        .home-search-controls-row{flex-wrap:nowrap !important;}
        .home-search-controls-row > .col-md-2,
        .home-search-controls-row > .home-search-main{min-width:0 !important;}
        .home-search-controls-row .home-search-main{display:flex !important;align-items:center !important;padding-right:0 !important;}
        .home-search-controls-row #homeSearchOriginalPlace{margin-top:0 !important;padding:0 !important;}
        .home-search-controls-row #homeSearchOriginalPlace form{display:block !important;width:100% !important;}
        .home-search-controls-row .search-row-custom{display:flex !important;width:100% !important;align-items:center;gap:10px;}
        .home-search-controls-row .location-col-custom{width:auto !important;flex:0 0 36% !important;min-width:0 !important;margin:0 !important;}
        .home-search-controls-row .search-col-custom{width:auto !important;flex:1 1 0 !important;min-width:0 !important;margin:0 !important;}
        .home-search-controls-row .banner-download-btn{position:static !important;display:flex !important;align-items:center;align-self:center;padding-left:0 !important;right:auto;bottom:auto;}
    }
    @media (max-width:1023px){
        .home-search-controls-row{display:none !important;}
    }
</style>
<section class="banner-section">
    <div class="container-fulde">
   <div class="col-md-12 home-search-kicker-area title-area  text-left">

   <p class="hero-title home-search-kicker">
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

</p>

</div>
	<div class="row g-0 home-search-controls-row">
<div class="col-md-2">
	    <a class="home-search-listing-cta" href="{{ route('free.listing') }}">
	        @lang('+ Add Business – FREE')
    </a>
</div>
<div class="col-md-8 custom-right home-search-main">
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
   <div class="col-md-2 banner-download-btn desktop-only">
    <a href="https://play.google.com/store/apps/details?id=com.servicebazaar" class="download-btn"
       target="_blank" rel="noopener" aria-label="Download the Service Bazzar app on Google Play">
        Download App
        <i class="fas fa-download"></i>
    </a>
</div>
</div>
    </div>



</section>
<!-- Banner Section End -->


<style>
    .sb-home-hero-section { padding: 10px 17px; }
    .sb-home-hero {
        position: relative;
        display: grid;
        min-height: 320px;
        grid-template-columns: .92fr 1.08fr;
        align-items: center;
        gap: 28px;
        overflow: hidden;
        padding: 28px clamp(24px, 4vw, 60px);
        border: 1px solid rgba(19,136,8,.28);
        border-radius: 18px;
        background:
            radial-gradient(ellipse at 82% 48%, rgba(19,136,8,.075), transparent 38%),
            radial-gradient(ellipse at 5% 100%, rgba(23,105,210,.035), transparent 38%),
            linear-gradient(118deg, #f8faf8 0%, #f0f5ef 54%, #f8faf8 100%);
        box-shadow: 0 8px 24px rgba(19,136,8,.085);
    }
    .sb-home-hero::before {
        position: absolute;
        top: -170px;
        right: 14%;
        width: 350px;
        height: 350px;
        border: 1px solid rgba(19,136,8,.07);
        border-radius: 50%;
        content: "";
        pointer-events: none;
    }
    .sb-home-hero-copy, .sb-home-hero-art { position: relative; z-index: 1; min-width: 0; }
    .sb-home-hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #107306;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .55px;
        text-transform: uppercase;
    }
    .sb-home-hero-eyebrow::before {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #138808;
        content: "";
    }
    .sb-home-hero-title {
        max-width: 520px;
        margin: 0;
        color: #18243a;
        font-size: clamp(30px, 3.2vw, 41px);
        font-weight: 750;
        line-height: 1.1;
        letter-spacing: -1.8px;
    }
    .sb-home-hero-title span { color: #1769d2; }
    .sb-home-hero-description {
        max-width: 465px;
        margin: 12px 0 17px;
        color: #667085;
        font-size: 14px;
        line-height: 1.55;
    }
    .sb-home-hero-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 9px 12px; }
    .sb-home-hero-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 8px;
        background: #0e77cd;
        box-shadow: 0 5px 12px rgba(22,116,209,.16);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }
    .sb-home-hero-cta:hover, .sb-home-hero-cta:focus { background: #0965b4; color: #fff; text-decoration: none; }
    .sb-home-hero-note { color: #667085; font-size: 11px; }
    .sb-home-hero-art { display: flex; flex-direction: column; justify-content: center; gap: 5px; }
    .sb-home-hero-art-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; color: #344054; font-size: 12px; font-weight: 750; }
    .sb-home-hero-art-heading span { color: #107306; font-size: 10px; font-weight: 650; }
    .sb-home-business-orbit { position: relative; height: 375px; overflow: hidden; }
    .sb-home-orbit-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 78%;
        height: 72%;
        border: 1px dashed rgba(19,136,8,.22);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .sb-home-orbit-center {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 50%;
        display: grid;
        width: 90px;
        height: 90px;
        place-items: center;
        border: 3px solid #fff;
        border-radius: 50%;
        background: #138808;
        box-shadow: 0 3px 12px rgba(19,136,8,.24);
        color: #fff;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .5px;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .sb-home-business {
        position: absolute;
        top: 50%;
        left: 50%;
        display: flex;
        width: var(--business-size, 88px);
        height: var(--business-size, 88px);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 8px;
        border: 1px solid #dfe9f5;
        border-radius: 50%;
        background: rgba(255,255,255,.98);
        box-shadow: 0 5px 15px rgba(32,68,112,.1);
        color: inherit;
        text-align: center;
        text-decoration: none;
        opacity: 0;
        transform: translate(-50%, -50%) scale(.2);
        animation: sb-business-orbit-in .62s cubic-bezier(.2,.8,.25,1.2) forwards;
        animation-delay: var(--business-delay, 0s);
        transition: translate .18s ease, scale .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .sb-home-business:hover, .sb-home-business:focus-visible {
        z-index: 3;
        translate: 0 -4px;
        scale: 1.07;
        border-color: #8bbaf0;
        box-shadow: 0 10px 24px rgba(23,105,210,.22);
        color: inherit;
        text-decoration: none;
    }
    .sb-home-business:focus-visible { outline: 2px solid #1769d2; outline-offset: 2px; }
    .sb-home-business-image, .sb-home-business-initial {
        display: grid;
        width: 25px;
        height: 25px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: #edf5ff;
        color: #1769d2;
        font-size: 13px;
        font-weight: 700;
        object-fit: cover;
    }
    .sb-home-business-name {
        display: -webkit-box;
        overflow: hidden;
        max-width: 100%;
        color: #26364b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.15;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .sb-home-business-category { overflow: hidden; max-width: 100%; color: #116b0a; font-size: 10px; font-weight: 650; text-overflow: ellipsis; white-space: nowrap; }
    .sb-home-business-city { overflow: hidden; max-width: 100%; color: #26364b; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    .sb-home-business-position-1 { --business-x: 50%; --business-y: 18%; --business-size: 110px; --business-mobile-size: 72px; --business-delay: 0s; }
    .sb-home-business-position-2 { --business-x: 74%; --business-y: 25%; --business-size: 110px; --business-mobile-size: 68px; --business-delay: .08s; }
    .sb-home-business-position-3 { --business-x: 88%; --business-y: 50%; --business-size: 110px; --business-mobile-size: 70px; --business-delay: .16s; }
    .sb-home-business-position-4 { --business-x: 74%; --business-y: 75%; --business-size: 110px; --business-mobile-size: 72px; --business-delay: .24s; }
    .sb-home-business-position-5 { --business-x: 50%; --business-y: 82%; --business-size: 110px; --business-mobile-size: 68px; --business-delay: .32s; }
    .sb-home-business-position-6 { --business-x: 26%; --business-y: 75%; --business-size: 110px; --business-mobile-size: 70px; --business-delay: .4s; }
    .sb-home-business-position-7 { --business-x: 12%; --business-y: 50%; --business-size: 110px; --business-mobile-size: 68px; --business-delay: .48s; }
    .sb-home-business-position-8 { --business-x: 26%; --business-y: 25%; --business-size: 110px; --business-mobile-size: 72px; --business-delay: .56s; }
    @keyframes sb-business-orbit-in {
        0% { top: 50%; left: 50%; opacity: 0; transform: translate(-50%, -50%) scale(.2); }
        65% { opacity: 1; transform: translate(-50%, -50%) scale(1.08); }
        100% { top: var(--business-y); left: var(--business-x); opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    @media (max-width: 767px) {
        .sb-home-hero-section { padding: 0 10px; }
        .sb-home-hero { min-height: 0; grid-template-columns: 1fr; gap: 18px; padding: 25px 20px 16px; border-radius: 14px; }
        .sb-home-hero-eyebrow { margin-bottom: 10px; font-size: 10px; }
        .sb-home-hero-title { max-width: 450px; font-size: clamp(29px, 8vw, 37px); letter-spacing: -1.3px; }
        .sb-home-hero-description { margin: 11px 0 15px; font-size: 13px; }
        .sb-home-hero-cta { padding: 11px 14px; font-size: 12px; }
        .sb-home-hero-note { align-self: center; }
        .sb-home-hero-art { gap: 8px; }
        .sb-home-hero-art-heading { align-items: flex-start; flex-direction: column; gap: 2px; line-height: 1.25; }
        .sb-home-business-orbit {
            display: flex;
            height: auto;
            gap: 12px;
            margin: 0 -4px;
            padding: 4px 4px 12px;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            scrollbar-color: rgba(19,136,8,.35) transparent;
            scrollbar-width: thin;
            overscroll-behavior-inline: contain;
            -webkit-overflow-scrolling: touch;
        }
        .sb-home-orbit-ring, .sb-home-orbit-center { display: none; }
        .sb-home-business {
            position: relative;
            top: auto;
            left: auto;
            flex: 0 0 164px;
            width: 164px;
            height: 164px;
            padding: 12px;
            border-radius: 14px;
            opacity: 1;
            transform: none;
            animation: none;
            scroll-snap-align: start;
        }
        .sb-home-business:hover, .sb-home-business:focus-visible { translate: 0 0; scale: 1; }
        .sb-home-business-image, .sb-home-business-initial { width: 42px; height: 42px; font-size: 17px; }
        .sb-home-business-name { font-size: 14px; font-weight: 700; line-height: 1.2; }
        .sb-home-business-category {
            display: -webkit-box;
            padding: 3px 8px;
            border-radius: 12px;
            background: #eaf5e8;
            font-size: 11px;
            line-height: 1.2;
            text-overflow: clip;
            white-space: normal;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .sb-home-business-city { display: inline-flex; align-items: center; gap: 4px; color: #475467; font-size: 12px; font-weight: 500; }
        .sb-home-business-city i { flex: 0 0 auto; color: #d04444; font-size: 14px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .sb-home-business { opacity: 1; animation: none; top: var(--business-y); left: var(--business-x); transform: translate(-50%, -50%); transition: none; }
    }
    @media (max-width: 767px) and (prefers-reduced-motion: reduce) {
        .sb-home-business { position: relative; top: auto; left: auto; transform: none; }
    }
    @media (hover: hover) and (pointer: fine) {
        .sb-home-business:hover { z-index: 3; }
    }
</style>




<section class="sb-home-hero-section" aria-labelledby="sb-home-hero-title">
    <div class="sb-home-hero">
        <div class="sb-home-hero-copy">
            <div class="sb-home-hero-eyebrow">India bhar ke local businesses</div>
            <h1 class="sb-home-hero-title" id="sb-home-hero-title">Zaroorat Aapki, Bazzar Hamara.<span>All Services One Bazzar</span></h1>
            <p class="sb-home-hero-description">Apne sheher ke bharosemand businesses aur services dhoondhein. India bhar ke local options, ek hi jagah.</p>
            <div class="sb-home-hero-actions">
                <a class="sb-home-hero-cta" href="#featured-business-listings">Businesses Dekhein <span aria-hidden="true">&rarr;</span></a>
                <span class="sb-home-hero-note">Bharosemand local options, poore India se</span>
            </div>
        </div>
        <div class="sb-home-hero-art" aria-label="Featured businesses in cities across India">
            <div class="sb-home-hero-art-heading">
                <div>Top Verified Businesses Across India</div>
                <span>Multiple Cities &middot; One Place</span>
            </div>
            <div class="sb-home-business-orbit">
                <div class="sb-home-orbit-ring" aria-hidden="true"></div>
                <div class="sb-home-orbit-center" aria-hidden="true">INDIA</div>
                @foreach($featureds->take(8) as $index => $featured)
                    @php
                        $heroBusinessCategory = optional($featured->category)->slug;
                        $heroBusinessCategoryName = optional($featured->category)->name
                            ?: ucwords(str_replace('-', ' ', $heroBusinessCategory ?? 'service'));
                        $heroBusinessCity = optional($featured->location)->name ?: 'Across India';
                    @endphp
                    <a class="sb-home-business sb-home-business-position-{{ $index + 1 }}"
                       href="{{ route('listings.details', ['category' => $heroBusinessCategory, 'slug' => $featured->slug]) }}"
                       aria-label="{{ $featured->name }}, {{ $heroBusinessCategoryName }} in {{ $heroBusinessCity }}">
                        @if(!empty($featured->thumb))
                            <img class="sb-home-business-image" src="{{ loadImage('assets/images/listing/thumb/'.$featured->thumb) }}" alt="" loading="lazy" decoding="async">
                        @else
                            <span class="sb-home-business-initial" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($featured->name, 0, 1)) }}</span>
                        @endif
                        <span class="sb-home-business-name">{{ $featured->name }}</span>
                        <span class="sb-home-business-category">{{ $heroBusinessCategoryName }}</span>
                        <span class="sb-home-business-city"><i class="las la-map-marker-alt" aria-hidden="true"></i>{{ $heroBusinessCity }}</span>
                    </a>
                @endforeach
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
                                    src="{{ asset('assets/images/category/' . $categoryItem->image) }}"
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

@if($featureds->isNotEmpty())
<section class="featured featured-business-listings pt-4 pb-4" id="featured-business-listings">
   
    @include('themes.servicebazzar.partials.inline-css', ['file' => 'home/home-07.css'])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="section-title">
                    <h2 class="hh-h4-size premium-listings-title">{{ __($featured_listing?->content?->title ?? 'Featured Listings') }}</h2>
                </div>
            </div>
        </div>

        <div class="featured-listing-slider">
            @foreach($featureds as $featured)

                @php

                    $mobileNumbers = is_array($featured->mobile_numbers)
                        ? $featured->mobile_numbers
                        : json_decode($featured->mobile_numbers, true);

                    $whatsappNumbers = is_array($featured->whatsapp_numbers)
                        ? $featured->whatsapp_numbers
                        : json_decode($featured->whatsapp_numbers, true);

                    $mobilePhone = $mobileNumbers[0] ?? '';
                    $whatsappPhone = $whatsappNumbers[0] ?? '';

                    $cleanMobilePhone = preg_replace('/[^0-9]/', '', $mobilePhone);
                    $cleanWhatsappPhone = preg_replace('/[^0-9]/', '', $whatsappPhone);

                    $rating = (float) ($featured->verified_ratings_avg ?? 0);
                    $ratingCount = (int) ($featured->verified_ratings_count ?? 0);

                    

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
                        <img width="250" height="180" src="{{ loadImage('assets/images/listing/thumb/'.$featured->thumb) }}"
                         loading="lazy" alt="{{ $featured->name }}" class="listing-thumb">
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


<div class="listing-contact mt-3">

                                @if(!empty($cleanMobilePhone))

                                    <a href="tel:{{ $cleanMobilePhone }}" class="btn-call">
                                        <i class="las la-phone"></i>
                                        Contact for Service
                                    </a>

                                @endif


                                
 </div>


                        <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap">

                            @if($rating > 0 && $ratingCount > 0)

                                <span class="jd-rating-badge">
                                    {{ number_format($rating, 1) }} ★
                                </span>

                                <span class="jd-rating-count">
                                    {{ $ratingCount }} Ratings
                                </span>

                            @endif

                            @if(($featured->trust ?? 0) == 1)
                                <img  src="{{ asset('assets/images/trust_2x.gif') }}"
                                     class="jd-badge-img"
                                     loading="lazy"
                                     decoding="async"
                                     alt="Trust">
                            @endif

                            @if(($featured->verified ?? 0) == 1)
                                <img  src="{{ asset('assets/images/verified_2x.gif') }}"
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

                                    <a href="tel:{{ $cleanMobilePhone }}" class="btn-whatsapp">
                                        <i class="las la-phone"></i>
                                        Call Now
                                    </a>

                                @endif


                                @if(!empty($cleanWhatsappPhone))

                                    <a href="https://wa.me/{{ $cleanWhatsappPhone }}"
                                       target="_blank" rel="noopener noreferrer"
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
@endif



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





<section class="rr-section">
    <div class="container-fluid">
        <div class="row g-3">
            @foreach($categoryHeadings as $headingItem)
                <div class="cardhotkey__wrap {{ in_array(strtolower(trim($headingItem->heading)), ['transportation services', 'beauty services', 'repair and services', 'home services'], true) ? 'homepage-category-group' : '' }}">
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

    <section class="py-4  trending-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="trending-section-heading d-flex align-items-center mb-2">
                {{-- aria-level, not a different tag: the CSS styles h4 and the visual has to stay put. --}}
                <h2 class="trending-section-title fw-bold mb-0 hh-h4-size">Trending Searches Near You</h2>
                <span class="trending-new-badge badge ms-2">NEW</span>
            </div>

            <p class="text-muted">Stay updated with the latest local trends.</p>

            <div class="trending-wrapper position-relative">

                <button class="trend-nav prev-btn" type="button" onclick="scrollTrending(-1)" aria-label="Scroll left">
                    <i class="las la-angle-left"></i>
                </button>

                <div class="trending-scroll" id="trendingScroll" onscroll="updateTrendingProgress(this)">

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
            <div class="trending-progress" aria-hidden="true"><span></span></div>
        </div>
    </div>
</section>




@if($locations->isNotEmpty())
<section class="location-section top-location-section location-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="location-section-heading d-flex align-items-center mb-2">
                <h2 class="location-section-title fw-bold mb-0 hh-h4-size">{{ __($location_section?->content?->title ?? 'Our Top Locations') }}</h2>
                <span class="location-new-badge badge ms-2">NEW</span>
            </div>

            <p class="text-muted ">Discover popular locations around you.</p>

            <div class="trending-wrapper position-relative">
  <button class="trend-nav prev-btn" type="button" aria-label="Scroll left">
                        <i class="las la-angle-left"></i>
                    </button>
              

                <div class="trending-scroll" onscroll="updateTrendingProgress(this)">
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
            <div class="location-progress" aria-hidden="true"><span></span></div>
        </div>
    </div>
</section>
@endif

    <!-- =========================
     LATEST LISTINGS SECTION
========================= -->

<section class=" mt-2 mb-2 ps-section popular-search-section popular-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="popular-section-heading d-flex align-items-center mb-2">
                <h2 class="popular-section-title fw-bold mb-0 hh-h4-size">Popular Searches</h2>
            </div>

            <div class="trending-wrapper position-relative">

                <button class="trend-nav prev-btn"
                        type="button"
                        onclick="scrollPopular(-1)" aria-label="Scroll left">
                    <i class="las la-angle-left"></i>
                </button>

                <div id="popularScroll" class="trending-scroll" onscroll="updateTrendingProgress(this)">

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
            <div class="popular-progress" aria-hidden="true"><span></span></div>

        </div>
    </div>
</section>



@foreach($seasonalSections as $section)

<section class="location-section  location-same-section seasonal-same-section">
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
<!-- COMPLETE FAIL-SAFE SLIDER  -->

<div class="bazzar-slider-wrapper">
  <div class="bazzar-slides-container" id="bazzarSlider">
    
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
	
    <!-- Slide 1 -->
    <div class="bazzar-slide {{ $isFirst ? 'active' : '' }}">
      <div class="bazzar-overlay"></div>
	   @if(!empty($bannerLink))
                                        <a href="{{ $bannerLink }}"
                                           @if($bannerCatSlug) class="dynamic-category-link" data-slug="{{ $bannerCatSlug }}" @endif>
                                    @endif

      <img src="{{ loadImage($desktopSrc) }}" width="1200" height="534" alt="{{ $banner->content->title }}"  loading="{{ $loadPriority }}" fetchpriority="{{ $fetchPriority }}" decoding="async">
     
    </div>
 @endforeach
  
  </div>

  <!-- Manual Buttons (Left/Right) -->
  <button class="bazzar-arrow arrow-left" onclick="changeBazzarSlide(-1)">&#10094;</button>
  <button class="bazzar-arrow arrow-right" onclick="changeBazzarSlide(1)">&#10095;</button>
</div>

<!-- INLINE STYLES (No CSS file needed) -->
<style>
.bazzar-slider-wrapper {
  position: relative;
  width: 100%;
  max-width: 1230px;
  height: 350px;
  margin: 10px auto;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  font-family: Arial, sans-serif;
}
.bazzar-slides-container {
  position: relative;
  width: 100%;
  height: 100%;
}
.bazzar-slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 0.8s ease-in-out;
  z-index: 1;
}
.bazzar-slide.active-slide {
  opacity: 1;
  z-index: 2;
}
.bazzar-slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.bazzar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.15);
  z-index: 2;
}
.bazzar-text-box {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #ffffff;
  text-align: center;
  z-index: 3;
  width: 80%;
}
.bazzar-text-box h2 {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 10px;
  letter-spacing: 1px;
}
.bazzar-text-box p {
  font-size: 1.2rem;
  margin-bottom: 20px;
}
.bazzar-btn {
  display: inline-block;
  padding: 12px 30px;
  background: #ffc107;
  color: #000000;
  font-weight: bold;
  text-decoration: none;
  border-radius: 6px;
  text-transform: uppercase;
}
.bazzar-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255,255,255,0.2);
  color: #fff; 
  border: none;
  padding: 15px 20px;
  cursor: pointer;
  z-index: 10;
  font-size: 20px;
  border-radius: 100%;
  transition: 0.3s;
}
.bazzar-arrow:hover {
  background: #138808;
  color: black;
}
.arrow-left { left: 20px; }
.arrow-right { right: 20px; }
</style>

<!-- SELF CONTAINED INDEPENDENT JAVASCRIPT -->
<script>
let currentBazzarIndex = 0;
const bazzarSlides = document.querySelectorAll('.bazzar-slide');

function showBazzarSlide(index) {
  if (index >= bazzarSlides.length) currentBazzarIndex = 0;
  else if (index < 0) currentBazzarIndex = bazzarSlides.length - 1;
  else currentBazzarIndex = index;

  bazzarSlides.forEach((slide, i) => {
    if (i === currentBazzarIndex) {
      slide.classList.add('active-slide');
    } else {
      slide.classList.remove('active-slide');
    }
  });
}

function changeBazzarSlide(direction) {
  showBazzarSlide(currentBazzarIndex + direction);
}

// Automatic slide rotation every 4 seconds
setInterval(() => {
  changeBazzarSlide(1);
}, 4000);
</script>



<!-- End COMPLETE FAIL-SAFE SLIDER  -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  

   

    
   
    {{-- ================= CATEGORY CONTENT SECTION ================= --}}
<section class=" category-content-section py-4">
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
            <h2 class="popular-services-title mb-0">
                Explore Popular Services for Your Daily Needs
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

<style id="servicebazzar-home-section-theme">
    .trending-same-section .trending-scroll,
    .location-same-section .trending-scroll,
    .popular-same-section .trending-scroll{
        box-sizing:border-box;
        grid-auto-columns:calc((100% - 72px) / 7) !important;
        gap:20px !important;
        padding:0 50px 8px !important;
    }
    .trending-same-section .trend-card-item,
    .location-same-section .trend-card-item,
    .popular-same-section .trend-card-item{
        width:100% !important;
        max-width:100% !important;
    }
    .trending-same-section .trend-card-link,
    .location-same-section .trend-card-link,
    .popular-same-section .trend-card-link{
        box-sizing:border-box;
        width:100% !important;
    }
    @media (min-width:992px) and (max-width:1199px){
        .trending-same-section .trending-scroll,
        .location-same-section .trending-scroll,
        .popular-same-section .trending-scroll{
            grid-auto-columns:calc((100% - 48px) / 5) !important;
        }
    }
    @media (min-width:768px) and (max-width:991px){
        .trending-same-section .trending-scroll,
        .location-same-section .trending-scroll,
        .popular-same-section .trending-scroll{
            grid-auto-columns:calc((100% - 36px) / 4) !important;
            padding-right:44px !important;
            padding-left:44px !important;
        }
    }
    @media (max-width:767px){
        .trending-same-section .trending-scroll,
        .location-same-section .trending-scroll,
        .popular-same-section .trending-scroll{
            grid-auto-columns:calc((100% - 12px) / 2) !important;
            padding:0 0 8px !important;
        }
        .trending-same-section .trend-nav,
        .location-same-section .trend-nav,
        .popular-same-section .trend-nav{
            display:none !important;
        }
    }
    .category-area{
        width:calc(100% - 40px);
        max-width:1270px;
        margin:5px auto !important;
        padding:0 !important;
    }
    .category-area > .container-fildu{
        box-sizing:border-box;
        width:100%;
        padding:22px;
        border:1px solid #dce8d9;
        border-radius:18px;
        background:linear-gradient(145deg,#fff 0%,#f7faf6 100%);
        box-shadow:0 8px 24px rgba(19,136,8,.09);
    }
    .category-area .category-grid{
        grid-template-columns:repeat(10,minmax(0,1fr));
        gap:14px 12px;
        width:100%;
        margin:0 !important;
    }
    .category-area .category-item{
        width:auto;
        height:auto;
        min-height:150px;
        padding:8px 5px;
        border:0;
        border-radius:0;
        background:transparent;
        box-shadow:none;
    }
    .category-area .category-item:hover{
        transform:none;
        border-color:transparent;
        box-shadow:none;
    }
    .category-area .category-item a,
    .category-area .category-item .more-btn{
        height:100%;
        align-items:center;
        justify-content:flex-start;
        gap:8px;
    }
    .category-area .category-item a:focus-visible,
    .category-area .category-item .more-btn:focus-visible{
        border-radius:8px;
        outline:2px solid #16820d;
        outline-offset:2px;
    }
    .category-area .category-box{
        flex:0 0 90px;
        width:90px !important;
        height:90px !important;
        margin:0;
        border:0;
        border-radius:0;
        background:transparent;
        box-shadow:none;
        transition:transform .18s ease;
    }
    .category-area .category-item:hover .category-box{
        transform:translateY(-2px);
    }
    .category-area .category-box .category-icon-img,
    .category-area .category-box .category-icon-img:hover{
        width:84px;
        height:84px;
        object-fit:contain;
        padding:5px;
        border:1px solid #e5ede2;
        border-radius:12px;
        background:#fff;
        box-shadow:0 3px 9px rgba(31,77,27,.12);
        transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;
    }
    .category-area .category-item:hover .category-icon-img{
        transform:translateY(-2px) scale(1.06);
        border-color:#c5ddc1;
        box-shadow:0 7px 15px rgba(31,77,27,.2);
    }
    .category-area .category-box i,
    .category-area .category-box .more{
        color:#16820d;
        font-size:34px;
    }
    .category-area .category-item .font14{
        display:-webkit-box;
        width:100%;
        max-width:none;
        height:auto;
        min-height:34px;
        margin:0;
        overflow:hidden;
        color:#344b35;
        font-size:12px;
        font-weight:600;
        line-height:1.35;
        text-align:center;
        word-break:normal;
        overflow-wrap:normal;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
    }
    .category-area .more-category-item .category-box{
        border:0;
    }
    @media (min-width:992px) and (max-width:1199px){
        .category-area .category-grid{
            grid-template-columns:repeat(8,minmax(0,1fr));
        }
    }
    @media (min-width:768px) and (max-width:991px){
        .category-area .category-grid{
            grid-template-columns:repeat(6,minmax(0,1fr));
        }
    }
    @media (max-width:767px){
        .category-area{
            width:calc(100% - 24px);
            margin:14px auto !important;
            overflow:visible !important;
        }
        .category-area > .container-fildu{
            padding:12px;
            border-radius:15px;
        }
        .category-area .category-grid{
            grid-template-columns:repeat(4,minmax(0,1fr));
            gap:10px 8px;
        }
        .category-area .category-item{
            min-height:116px;
            padding:7px 3px;
            border-radius:0;
        }
        .category-area .category-item a,
        .category-area .category-item .more-btn{
            gap:6px;
        }
        .category-area .category-box{
            flex-basis:72px;
            width:72px !important;
            height:72px !important;
            border-radius:0;
        }
        .category-area .category-box .category-icon-img,
        .category-area .category-box .category-icon-img:hover{
            width:66px;
            height:66px;
            padding:4px;
            border-radius:10px;
        }
        .category-area .category-box i,
        .category-area .category-box .more{
            font-size:28px !important;
        }
        .category-area .category-item .font14{
            min-height:29px;
            font-size:10px !important;
            line-height:1.3;
        }
    }
    .rr-section{
        padding:5px 0;
        background:linear-gradient(180deg,#f7faf6 0%,#fff 100%);
    }
    .rr-section > .container-fluid{
        padding:22px;
        border:1px solid #dce8d9;
        border-radius:18px;
        background:linear-gradient(145deg,#fff 0%,#f7faf6 100%);
        box-shadow:0 8px 24px rgba(19,136,8,.09);
    }
    .rr-section .cardhotkey__wrap{
        padding:14px 12px;
        border:1px solid #e5ede2;
        border-radius:13px;
        background:rgba(255,255,255,.75);
        box-shadow:0 2px 8px rgba(19,136,8,.035);
    }
    .rr-section .cardhotkey__wrap h2{
        position:relative;
        margin:0 0 8px;
        padding-left:12px;
        color:#263b27;
        font-size:18px;
        font-weight:700;
        line-height:1.35;
    }
    .rr-section .cardhotkey__wrap h2::before{
        position:absolute;
        top:2px;
        bottom:2px;
        left:0;
        width:4px;
        border-radius:4px;
        background:#16820d;
        content:"";
    }
    .rr-section .subcategory-col{
        border-color:#e2ebdf;
        border-radius:12px;
        background:#fff;
        box-shadow:0 2px 8px rgba(19,136,8,.035);
        transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease;
    }
    .rr-section .subcategory-col:hover{
        border-color:#b8d4b4;
        box-shadow:0 6px 14px rgba(19,136,8,.1);
        transform:translateY(-2px);
    }
    .rr-section .subcategory-col .color111{
        color:#344b35;
        font-weight:600;
    }
    .trending-same-section > .container-fluid > .border,
    .location-same-section > .container-fluid > .border,
    .popular-same-section > .container-fluid > .border{
        border:1px solid #e2ebdf !important;
        border-radius:16px !important;
        background:linear-gradient(145deg,#fff 0%,#f9fbf8 100%) !important;
        box-shadow:0 6px 18px rgba(19,136,8,.06);
    }
    .trending-same-section .trending-section-title,
    .location-same-section .location-section-title,
    .popular-same-section .popular-section-title,
    .seasonal-same-section h2{
        color:#263b27 !important;
    }
    .location-same-section .location-section-heading,
    .popular-same-section .popular-section-heading,
    .seasonal-same-section .d-flex.align-items-center{
        gap:10px;
    }
    .location-same-section > .container-fluid > .border > p,
    .seasonal-same-section > .container-fluid > .border > p{
        color:#667085;
    }
    .location-same-section .trend-card-link,
    .popular-same-section .trend-card-link,
    .seasonal-same-section .trend-card-link{
        border:1px solid #e2ebdf;
        border-radius:14px;
        box-shadow:0 5px 14px rgba(19,136,8,.09);
    }
    .location-same-section .trend-nav,
    .popular-same-section .trend-nav,
    .seasonal-same-section .trend-nav{
        border:1px solid #dce8d9;
        background:#fff;
        color:#16820d;
        box-shadow:0 4px 12px rgba(19,136,8,.12);
    }
    .location-same-section .trend-nav:hover,
    .popular-same-section .trend-nav:hover,
    .seasonal-same-section .trend-nav:hover{
        border-color:#16820d;
        background:#16820d;
        color:#fff;
    }
    .category-banner-slider{
        overflow:hidden;
        border:1px solid #e2ebdf;
        border-radius:16px;
        box-shadow:0 6px 18px rgba(19,136,8,.07);
    }
    .category-banner-slider .category-banner-img{
        border-radius:15px;
    }
    .category-content-section{
        padding-top:5px !important;
        padding-bottom:0px !important;
        background:linear-gradient(180deg,#f7faf6 0%,#fff 100%);
    }
    .category-content-section > .container-fluid{
        padding-top:24px;
        padding-bottom:24px;
        border:1px solid #e2ebdf;
        border-radius:16px;
        background:rgba(255,255,255,.92);
        box-shadow:0 6px 18px rgba(19,136,8,.06);
    }
    .category-content-row{
        row-gap:14px !important;
		gap:10px
    }
    .category-content-row > [class*="col-"]{
        display:flex;
        min-height:155px;
        flex-direction:column;
        padding:16px;
        border:1px solid #e6eee3;
        border-radius:12px;
        background:linear-gradient(145deg,#fff 0%,#fafcf9 100%);
        box-shadow:0 3px 10px rgba(19,136,8,.04);
        transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease;
		max-width: 300px ! important;
    }
    .category-content-row > [class*="col-"]:hover{
        border-color:#c7dfc3;
        box-shadow:0 7px 16px rgba(19,136,8,.09);
        transform:translateY(-2px);
    }
    .category-content-row .category-content-title a,
    .category-content-row .category-content-icon i{
        color:#216a1b !important;
    }
    .category-content-row .spn{
        max-width:none;
    }
    .featured-business-listings{
        padding:10px 0 !important;
        background:linear-gradient(180deg,#f7faf6 0%,#fff 100%);
    }
    .featured-business-listings > .container-fluid{
        box-sizing:border-box;
        width:calc(100% - 40px);
        max-width:1270px;
        padding:24px;
        border:1px solid #dce8d9;
        border-radius:18px;
        background:linear-gradient(145deg,#fff 0%,#f8fbf7 100%);
        box-shadow:0 8px 24px rgba(19,136,8,.085);
    }
    .featured-business-listings .section-title{
        margin-bottom:20px;
    }
    .featured-business-listings .premium-listings-title{
        margin:0;
        color:#263b27 !important;
        font-size:clamp(20px,2vw,25px) !important;
        line-height:1.3 !important;
    }
    .featured-business-listings .premium-listings-title::before{
        background:#16820d;
    }
    .featured-business-listings .listing-area{
        display:flex;
        height:520px;
        flex-direction:column;
        overflow:hidden;
        border:1px solid #e1e9df;
        border-radius:14px;
        background:#fff;
        box-shadow:0 4px 14px rgba(31,77,27,.08);
        transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease;
    }
    .featured-business-listings .listing-area:hover{
        transform:translateY(-3px);
        border-color:#c4d9c0;
        box-shadow:0 10px 22px rgba(31,77,27,.14);
    }
    .featured-business-listings .listing-area .thumb{
        flex:0 0 220px;
        overflow:hidden;
        border-radius:0;
    }
    .featured-business-listings .listing-area .thumb img{
        transition:transform .35s ease;
    }
    .featured-business-listings .listing-area:hover .thumb img{
        transform:scale(1.035);
    }
    .featured-business-listings .listing-area .listing-content{
        display:flex;
        min-height:0;
        flex:1 1 auto;
        flex-direction:column;
        border:0;
        padding:16px;
    }
    .featured-business-listings .listing-area .title{
        display:flex;
        align-items:flex-start !important;
        gap:6px;
        min-height:52px;
        max-height:52px;
        margin:0 0 8px;
        overflow:hidden;
    }
    .featured-business-listings .listing-area .title a{
        display:-webkit-box;
        flex:1 1 0;
        min-width:0;
        overflow:hidden;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
    }
    .featured-business-listings .listing-area .title .ico{
        flex:0 0 auto;
        margin:3px 0 0;
        line-height:1;
    }
    .featured-business-listings .listing-content > .listing-contact:first-of-type{
        min-height:40px;
        margin:0 0 8px !important;
    }
    .featured-business-listings .jd-rating-wrap{
        display:flex;
        flex-wrap:nowrap !important;
        align-items:center;
        gap:6px;
        min-height:45px;
        max-height:45px;
        margin-bottom:8px !important;
        overflow:hidden;
    }
    .featured-business-listings .jd-rating-count{
        min-width:0;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .featured-business-listings .jd-badge-img{
        width:auto;
        height:24px;
        flex:0 0 auto;
        object-fit:contain;
    }
    .featured-business-listings .listing-location{
        display:flex;
        flex-wrap:nowrap;
        align-items:center;
        gap:5px;
        min-height:44px;
        max-height:44px;
        margin:0 0 8px;
        overflow:hidden;
    }
    .featured-business-listings .listing-location > i,
    .featured-business-listings .listing-location .dot-separator,
    .featured-business-listings .listing-location .business-years{
        flex:0 0 auto;
    }
    .featured-business-listings .listing-location > span:first-of-type{
        min-width:0;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .featured-business-listings .listing-location .business-years{
        white-space:nowrap;
    }
    .featured-business-listings .listing-content > .listing-contact:last-child{
        min-height:40px;
        margin-top:auto !important;
    }
    .featured-business-listings .listing-area .title a{
        color:#263b27;
        font-weight:700;
        text-decoration:none;
    }
    .featured-business-listings .listing-area .title a:hover{
        color:#16820d;
    }
    .featured-business-listings .listing-location{
        color:#667085;
    }
    .featured-business-listings .listing-location > i{
        color:#16820d;
    }
    .featured-business-listings .btn-call,
    .featured-business-listings .btn-whatsapp{
        min-height:40px;
        border:1px solid #dce8d9;
        border-radius:8px;
        font-size:13px;
        font-weight:600;
        transition:background-color .18s ease,border-color .18s ease,color .18s ease;
    }
    .featured-business-listings .btn-call{
        border-color:#16820d;
        background:#16820d;
        color:#fff;
    }
    .featured-business-listings .btn-call:hover{
        border-color:#116b0a;
        background:#116b0a;
        color:#fff;
    }
    .featured-business-listings .btn-whatsapp{
        background:#f7faf6;
        color:#344b35;
    }
    .featured-business-listings .btn-whatsapp:hover{
        border-color:#b8d4b4;
        background:#edf7eb;
        color:#216a1b;
    }
    @media (max-width:767px){
        .featured-business-listings{
            padding:18px 0 !important;
        }
        .featured-business-listings > .container-fluid{
            width:calc(100% - 24px);
            padding:16px 12px;
            border-radius:15px;
        }
        .featured-business-listings .section-title{
            margin-bottom:14px;
        }
        .featured-business-listings .listing-area .listing-content{
            padding:14px;
        }
        .featured-business-listings .listing-area{
            height:500px;
        }
        .featured-business-listings .listing-area .thumb{
            flex-basis:190px;
        }
        .rr-section{
            padding:14px 0;
        }
        .rr-section > .container-fluid{
            padding:14px 12px;
            border-radius:15px;
        }
        .rr-section .cardhotkey__wrap{
            padding:10px 0px 10px 10px
            border-radius:12px;
        }
        .category-content-section > .container-fluid{
            padding:16px 12px;
            border-radius:14px;
        }
        .category-content-row > [class*="col-"]{
            min-height:0;
            padding:14px;
        }
    }
	 .jd-rating-count {
            font-size: 14px ! important;
        }
</style>


@endsection
