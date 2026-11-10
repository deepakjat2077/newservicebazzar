
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
        color:#012d71;
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
        border-color:#012d71 !important;
        box-shadow:0 0 0 3px rgba(22,130,13,.1) !important;
    }
    .home-search-controls-row .search_buttonh i,
    .home-search-controls-row .voice-search-btn{
        color:#012d71;
    }
    .home-search-controls-row .download-btn{
        min-height:48px;
        border:1px solid #dce6da;
        border-radius:8px;
        color:#012d71;
        font-weight:600;
        transition:background-color .18s ease,border-color .18s ease;
    }
    .home-search-controls-row .download-btn:hover,
    .home-search-controls-row .download-btn:focus-visible{
        border-color:#b8d4b4;
        background:#f4faf3;
        color:#012d71;
        text-decoration:none;
    }
    .home-search-listing-cta{
        display:flex;
        align-items:center;
        justify-content:center;
        width:100%;
        min-height:44px;
        padding:10px 12px;
        border:1px solid #012d71;
        border-radius:8px;
        background:#012d71;
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
        border-color:#012d71;
        background:#012d71;
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
        padding: 5px clamp(24px, 4vw, 60px);
        border: 1px solid #012d71;
        border-radius: 18px;
        background:#fff;);
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
        color: #012d71;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .55px;
        text-transform: uppercase;
    }
    .sb-home-hero-eyebrow::before {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #012d71;
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
    .sb-home-hero-title span { color: #012d71; }
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
        background: #012d71;
        box-shadow: 0 5px 12px rgba(22,116,209,.16);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }
    .sb-home-hero-cta:hover, .sb-home-hero-cta:focus { background: #012d71; color: #fff; text-decoration: none; }
    .sb-home-hero-note { color: #667085; font-size: 11px; }
    .sb-home-hero-art { display: flex; flex-direction: column; justify-content: center; gap: 5px; }
    .sb-home-hero-art-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; color: #344054; font-size: 12px; font-weight: 750; }
    .sb-home-hero-art-heading span { color: #012d71; font-size: 10px; font-weight: 650; }
    .sb-home-business-orbit { position: relative; height: 375px; overflow: hidden; }
    .sb-home-business-nav { display: none; }
    .sb-home-business-controls { display: none; }
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
        background: #012d71;
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
    .sb-home-business:focus-visible { outline: 2px solid #012d71; outline-offset: 2px; }
    .sb-home-business-image, .sb-home-business-initial {
        display: grid;
        width: 25px;
        height: 25px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: #edf5ff;
        color: #012d71;
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
    .sb-home-business-category { overflow: hidden; max-width: 100%; color: #012d71; font-size: 10px; font-weight: 650; text-overflow: ellipsis; white-space: nowrap; }
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
            box-sizing: border-box;
            gap: 8px;
            width: 100%;
            margin: 0;
            padding: 4px 0 12px;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            overscroll-behavior-inline: contain;
            -webkit-overflow-scrolling: touch;
        }
        .sb-home-business-orbit::-webkit-scrollbar { display: none; }
        .sb-home-business-slider { display: flex; flex-direction: column; gap: 6px; }
        .sb-home-business-controls { display: flex; justify-content: flex-end; gap: 8px; }
        .sb-home-business-nav {
            display: grid;
            width: 28px;
            height: 28px;
            padding: 0;
            place-items: center;
            border: 1px solid #012d71;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 3px 10px rgba(19,136,8,.15);
            color: #012d71;
            font-size: 15px;
        }
        .sb-home-business-nav:focus-visible { outline: 2px solid #012d71; outline-offset: 2px; }
        .sb-home-orbit-ring, .sb-home-orbit-center { display: none; }
        .sb-home-business {
            position: relative;
            top: auto;
            left: auto;
            box-sizing: border-box;
            flex: 0 0 calc((100% - 8px) / 2) !important;
            width: calc((100% - 8px) / 2) !important;
            min-width: 0;
            height: 160px;
            padding: 8px 6px;
            border-radius: 14px;
            opacity: 1;
            transform: none;
            animation: none;
            scroll-snap-align: start;
        }
        .sb-home-business:hover, .sb-home-business:focus-visible { translate: 0 0; scale: 1; }
        .sb-home-business-image, .sb-home-business-initial { width: 34px; height: 34px; font-size: 14px; }
        .sb-home-business-name { font-size: 11px; font-weight: 700; line-height: 1.2; }
        .sb-home-business-category {
            display: -webkit-box;
            max-width: 100%;
            overflow: hidden;
            padding: 3px 8px;
            border-radius: 12px;
            background: #eaf5e8;
            font-size: 9px;
            line-height: 1.2;
            white-space: normal;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .sb-home-business-city { display: inline-flex; align-items: center; gap: 4px; max-width: 100%; overflow: hidden; color: #475467; font-size: 10px; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
        .sb-home-business-city i { flex: 0 0 auto; color: #d04444; font-size: 12px; }
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
            <h1 class="sb-home-hero-title" id="sb-home-hero-title">Zaroorat Aapki,<br class="hx-br"> Bazzar Hamara.<span>All Services One Bazzar</span></h1>
            <p class="sb-home-hero-description">Apne sheher ke bharosemand businesses aur services dhoondhein. India bhar ke local options, ek hi jagah.</p>
            <div class="sb-home-hero-actions">
                <a class="sb-home-hero-cta" href="#featured-business-listings">Businesses Dekhein <span aria-hidden="true">&rarr;</span></a>
                <a class="hx-cta2" href="{{ route('free.listing') }}"><span aria-hidden="true">+</span> Add Listing Free</a>
                <span class="sb-home-hero-note">Bharosemand local options, poore India se</span>
            </div>
            @php
                // Real counts, rounded DOWN ("14,392" -> "14,000+"), cached 6h.
                $hxStats = \Illuminate\Support\Facades\Cache::remember('hx_hero_stats_v1', 21600, function () {
                    return [
                        'listings' => \App\Models\Listing::where('status', 1)->count(),
                        'cities'   => \App\Models\Listing::where('status', 1)->distinct()->count('location_id'),
                        'services' => \App\Models\Subcategory::where('status', 1)->count(),
                    ];
                });
                $hxRound = function ($n) {
                    $n = (int) $n;
                    if ($n >= 1000) return number_format(intdiv($n, 1000) * 1000) . '+';
                    if ($n >= 10) return (intdiv($n, 10) * 10) . '+';
                    return (string) $n;
                };
            @endphp
            <ul class="hx-stats" aria-label="Service Bazzar in numbers">
                <li><i><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.8"/></svg></i><b>{{ $hxRound($hxStats['listings']) }}</b><span>Businesses</span></li>
                <li><i><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0113 0c0 5.4-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.4"/></svg></i><b>{{ $hxRound($hxStats['cities']) }}</b><span>Sheher</span></li>
                <li><i><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 00-5.3 5.3L3.5 17.5a1.8 1.8 0 002.6 2.6l5.9-5.9a4 4 0 005.3-5.3l-2.6 2.6-2.3-.3-.3-2.3z"/></svg></i><b>{{ $hxRound($hxStats['services']) }}</b><span>Services</span></li>
            </ul>
        </div>
        <div class="hx-stage">
            @php
                $hxSlides = [];
                foreach ((json_decode(optional(optional($banner)->content)->image ?? '[]', true) ?: []) as $hxRow) {
                    $hxImg = is_array($hxRow) ? ($hxRow['image'] ?? '') : $hxRow;
                    if (!empty($hxImg)) {
                        $hxSlides[] = ['image' => $hxImg, 'link' => is_array($hxRow) ? ($hxRow['link'] ?? '') : ''];
                    }
                }
                $hxCats = ($categories ?? collect())->take(4);
                $hxCity = \Illuminate\Support\Str::slug($cityHomeName ?? 'jaipur') ?: 'jaipur';
            @endphp
            <div class="hx-show" aria-label="Featured services and popular categories">
                <div class="hx-frame">
                    @foreach($hxSlides as $hxSlide)
                        @if(!empty($hxSlide['link']))<a class="hx-slide {{ $loop->first ? 'is-on' : '' }}" href="{{ $hxSlide['link'] }}">@else<div class="hx-slide {{ $loop->first ? 'is-on' : '' }}">@endif
                            <img src="{{ loadImage('assets/servicebazzar/images/banner/'.$hxSlide['image']) }}" width="2000" height="480" alt="{{ optional(optional($banner)->content)->title ?: 'Service Bazzar' }}" loading="lazy" decoding="async">
                        @if(!empty($hxSlide['link']))</a>@else</div>@endif
                    @endforeach
                    @if(count($hxSlides) > 1)
                        <button type="button" class="hx-arrow hx-arrow--prev" aria-label="Previous banner" data-hx-step="-1"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
                        <button type="button" class="hx-arrow hx-arrow--next" aria-label="Next banner" data-hx-step="1"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
                        <div class="hx-segs">
                            @foreach($hxSlides as $hxSlide)
                                <button type="button" class="hx-seg" aria-label="Banner {{ $loop->iteration }}"><span><i></i></span></button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="hx-dock">
                    <div class="hx-dock-head">
                        <span>Popular in {{ $cityHomeName ?? 'Jaipur' }}</span>
                        <a href="{{ route('service.keywords') }}">Sab services <span aria-hidden="true">&rarr;</span></a>
                    </div>
                    <div class="hx-cats">
                        @foreach($hxCats as $hxCat)
                            <a class="hx-cat" href="{{ url('/'.$hxCity.'/'.$hxCat->slug) }}">
                                <span class="hx-cat-ic">@if($hxCat->image)<img src="{{ asset('assets/images/category/'.$hxCat->image) }}" alt="" loading="lazy">@endif</span>
                                <span class="hx-cat-text">
                                    <span class="hx-cat-name">{{ $hxCat->name }}</span>
                                    <span class="hx-cat-go">Dekhein <span class="hx-cat-arrow" aria-hidden="true">&rarr;</span></span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="sb-home-hero-art" aria-label="Featured businesses in cities across India">
            <div class="sb-home-hero-art-heading">
                <div>Top Verified Businesses Across India</div>
                <span>Multiple Cities &middot; One Place</span>
            </div>
            <div class="sb-home-business-slider">
                @if($featureds->take(8)->count() > 2)
                    <div class="sb-home-business-controls">
                        <button class="sb-home-business-nav sb-home-business-nav--prev" type="button" aria-label="Show previous two businesses" aria-controls="sbHomeBusinessOrbit">
                            <i class="las la-angle-left" aria-hidden="true"></i>
                        </button>
                        <button class="sb-home-business-nav sb-home-business-nav--next" type="button" aria-label="Show next two businesses" aria-controls="sbHomeBusinessOrbit">
                            <i class="las la-angle-right" aria-hidden="true"></i>
                        </button>
                    </div>
                @endif
                <div class="sb-home-business-orbit" id="sbHomeBusinessOrbit">
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
    </div>
</section>

<style>
    /* Desktop only. Phones and tablets keep the original hero (copy + orbit). */
    .hx-stage, .hx-stats, .hx-cta2, .hx-br { display: none; }
    @media (min-width: 992px) {
        /* full-bleed: no box edge, the background runs edge to edge */
        .sb-home-hero-section { padding: 0 !important; }
        .sb-home-hero {
            grid-template-columns: minmax(0, 1.12fr) minmax(0, 1.45fr);
            gap: 48px;
            min-height: 0;
            padding: 48px max(40px, 4vw) 52px max(44px, 4.5vw);
            align-items: center;
            border: 0 !important;
            border-radius: 0 !important;
            background:
                radial-gradient(520px 260px at 82% 8%, rgba(1, 45, 113, .10), transparent 70%),
                radial-gradient(380px 220px at 96% 100%, rgba(229, 88, 30, .10), transparent 70%),
                linear-gradient(115deg, #f5f8ff 0%, #ffffff 45%, #f3f7ff 100%);
            box-shadow: none;
        }
        .sb-home-hero::after {
            content: "";
            position: absolute;
            inset: 0 0 0 40%;
            background-image: radial-gradient(rgba(1, 45, 113, .16) 1px, transparent 1.2px);
            background-size: 18px 18px;
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 45%, transparent 95%);
            mask-image: linear-gradient(90deg, transparent, #000 45%, transparent 95%);
            pointer-events: none;
        }
        .sb-home-hero > .sb-home-hero-art { display: none; }
        .hx-br { display: inline; }

        /* copy */
        .sb-home-hero-eyebrow { padding: 5px 11px; border-radius: 999px; background: #fff; border: 1px solid #e3eaf6; }
        .sb-home-hero-title { max-width: none; color: #012d70 !important; font-size: clamp(32px, 3.15vw, 52px); font-weight: 800; line-height: 1.08; letter-spacing: -1.6px; }
        .sb-home-hero-title span {
            position: relative;
            display: block;
            width: fit-content;
            white-space: nowrap;
            margin-top: 2px;
            color: #e8050d !important;
        }
        .sb-home-hero-title span::after {
            content: "";
            position: absolute; left: 2px; right: 2px; bottom: .02em;
            height: .2em; border-radius: 999px;
            background: #e8050d;
            opacity: .14;
            z-index: -1;
            display: none;
        }
        .sb-home-hero-description { max-width: 500px; margin-top: 16px; font-size: 15px; line-height: 1.65; }
        .sb-home-hero-note { display: none; }
        .hx-cta2 {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 11px 16px; border: 1.5px solid #012d71; border-radius: 8px;
            background: #fff; color: #012d71; font-size: 13px; font-weight: 700; text-decoration: none;
            transition: background-color .18s ease, color .18s ease;
        }
        .hx-cta2:hover, .hx-cta2:focus-visible { background: #012d71; color: #fff; text-decoration: none; }
        .hx-stats { display: flex; margin: 22px 0 0; padding: 0; list-style: none; }
        .hx-stats li {
            display: grid;
            grid-template-columns: 38px auto;
            grid-template-rows: auto auto;
            column-gap: 10px;
            align-items: center;
            padding: 0 18px;
            border-left: 1px solid #dfe7f3;
        }
        .hx-stats li:first-child { padding-left: 0; border-left: 0; }
        .hx-stats i {
            grid-row: 1 / span 2;
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e9f7ef;
            color: #0a9f34;
            font-size: 20px;
        }
        .hx-stats li:nth-child(2) i { background: #fff1e8; color: #e5581e; }
        .hx-stats li:nth-child(3) i { background: #eaf0ff; color: #012d71; }
        .hx-stats b { color: #012d71; font-size: 21px; font-weight: 800; line-height: 1.05; letter-spacing: -.5px; }
        .hx-stats span { color: #667085; font-size: 12px; font-weight: 600; line-height: 1.3; }

        /* showcase */
        .hx-stage { display: block; position: relative; z-index: 1; min-width: 0; container-type: inline-size; }
        .hx-show {
            display: flex; flex-direction: column; overflow: hidden;
            border: 0; border-radius: 20px;
            background: #fff;
            box-shadow: 0 30px 60px -34px rgba(1, 45, 113, .5), 0 0 0 1px rgba(1, 45, 113, .05);
        }
        .hx-frame { position: relative; width: 100%; aspect-ratio: 25 / 6; overflow: hidden; background: #eef3fb; }
        .hx-slide { position: absolute; inset: 0; display: block; opacity: 0; transition: opacity .7s ease; }
        .hx-slide.is-on { opacity: 1; z-index: 1; }
        .hx-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .hx-arrow {
            position: absolute; top: 50%; z-index: 3; display: grid; place-items: center;
            width: 34px; height: 34px; padding: 0; border: 0; border-radius: 50%;
            background: rgba(255, 255, 255, .92); color: #012d71; font-size: 17px;
            box-shadow: 0 4px 12px rgba(1, 45, 113, .2); transform: translateY(-50%);
            opacity: 0; cursor: pointer; transition: opacity .18s ease, background-color .18s ease, color .18s ease;
        }
        .hx-arrow--prev { left: 12px; }
        .hx-arrow--next { right: 12px; }
        .hx-frame:hover .hx-arrow, .hx-arrow:focus-visible { opacity: 1; }
        .hx-arrow:hover { background: #012d71; color: #fff; }
        .hx-count {
            position: absolute; top: 12px; right: 12px; z-index: 3;
            padding: 3px 9px; border-radius: 999px; background: rgba(1, 45, 113, .55);
            color: rgba(255, 255, 255, .8); font-size: 11px; font-weight: 600; letter-spacing: .5px;
            -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px);
        }
        .hx-count b { color: #fff; }
        .hx-progress { position: absolute; left: 0; right: 0; bottom: 0; z-index: 3; height: 3px; background: rgba(255, 255, 255, .35); }
        .hx-progress i { display: block; width: 100%; height: 100%; background: #e5581e; transform-origin: left; transform: scaleX(0); }
        .hx-progress i.run { animation: hx-fill 4.5s linear forwards; }
        .hx-frame:hover .hx-progress i { animation-play-state: paused; }
        @keyframes hx-fill { from { transform: scaleX(0); } to { transform: scaleX(1); } }

        .hx-dock { padding: 14px 16px 16px; }
        .hx-dock-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; color: #667085; font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
        .hx-dock-head a { color: #012d71; font-size: 12px; font-weight: 700; letter-spacing: 0; text-transform: none; text-decoration: none; }
        .hx-dock-head a:hover { text-decoration: underline; text-underline-offset: 2px; }
        .hx-cats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .hx-cat {
            --tint: #eef3ff; --ink: #012d71;
            position: relative; display: flex; flex-direction: column; align-items: flex-start; gap: 8px;
            min-width: 0; padding: 12px 12px 11px; overflow: hidden;
            border-radius: 14px; background: var(--tint); text-decoration: none;
            transition: background-color .22s ease, transform .18s ease, box-shadow .18s ease;
        }
        .hx-cat::after {
            content: ""; position: absolute; right: -18px; bottom: -18px; width: 64px; height: 64px;
            border-radius: 50%; background: var(--ink); opacity: .07; transition: opacity .22s ease, transform .3s ease;
        }
        .hx-cat:nth-child(2) { --tint: #fff2ea; --ink: #c2410c; }
        .hx-cat:nth-child(3) { --tint: #eaf7f0; --ink: #067647; }
        .hx-cat:nth-child(4) { --tint: #f3eeff; --ink: #5b21b6; }
        .hx-cat:hover, .hx-cat:focus-visible { background: var(--ink); transform: translateY(-3px); box-shadow: 0 16px 28px -16px var(--ink); text-decoration: none; }
        .hx-cat:hover::after { opacity: .18; background: #fff; transform: scale(1.4); }
        .hx-cat-ic { width: 44px; height: 44px; border-radius: 12px; background: #fff; display: grid; place-items: center; box-shadow: 0 4px 10px -6px rgba(1, 45, 113, .35); }
        .hx-cat-ic img { width: 30px; height: 30px; object-fit: contain; }
        .hx-cat-text { display: flex; flex-direction: column; width: 100%; min-width: 0; }
        .hx-cat-name { color: #17213f; font-size: 13.5px; font-weight: 700; line-height: 1.2; min-height: 2.4em; overflow-wrap: anywhere; overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; transition: color .22s ease; }
        .hx-cat-go { margin-top: 4px; color: var(--ink); font-size: 11.5px; font-weight: 700; transition: color .22s ease; }
        .hx-cat:hover .hx-cat-name, .hx-cat:hover .hx-cat-go { color: #fff; }
        .hx-cat-arrow { display: inline-block; transition: transform .18s ease; }
        .hx-cat:hover .hx-cat-arrow { transform: translateX(4px); }

        /* entrance */
        @keyframes hx-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .sb-home-hero-copy { animation: hx-rise .55s ease-out both; }
        .hx-show { animation: hx-rise .65s .12s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .sb-home-hero-copy, .hx-show { animation: none; }
            .hx-progress { display: none; }
        }
    }
</style>
<style id="hx-banner-e">
    /* Banner inside the hero card: inset with its own rounded frame,
       story-style segments, wipe-in transition and a slow settle zoom. */
    .hx-segs { display: none; }
    @media (min-width: 992px) {
        .hx-frame {
            margin: 12px 12px 0; width: auto; border-radius: 16px;
            box-shadow: 0 18px 30px -22px rgba(1, 45, 113, .6);
        }
        .hx-frame::after {
            content: ""; position: absolute; inset: 0; z-index: 3; pointer-events: none; border-radius: inherit;
            box-shadow: inset 0 0 0 1px rgba(1, 45, 113, .08);
        }
        .hx-slide { opacity: 0; transition: none; }
        .hx-slide.was-on { opacity: 1; z-index: 1; }
        .hx-slide.is-on { opacity: 1; z-index: 2; animation: hx-wipe .9s cubic-bezier(.7, 0, .2, 1) both; }
        .hx-slide.is-on img { animation: hx-settle 5.5s ease-out both; }
        @keyframes hx-wipe { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0 0 0); } }
        @keyframes hx-settle { from { transform: scale(1.06); } to { transform: none; } }

        .hx-segs {
            position: absolute; top: 10px; left: 50%; z-index: 4; display: flex; gap: 6px;
            width: min(240px, 40%); padding: 0 10px; border-radius: 999px; transform: translateX(-50%);
            background: rgba(255, 255, 255, .7); box-shadow: 0 4px 12px -6px rgba(1, 45, 113, .4);
            -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px);
        }
        .hx-seg { flex: 1; height: 14px; padding: 5px 0; border: 0; background: none; cursor: pointer; }
        .hx-seg span { display: block; height: 4px; overflow: hidden; border-radius: 4px; background: rgba(1, 45, 113, .16); }
        .hx-seg i { display: block; width: 100%; height: 100%; background: #012d71; transform-origin: left; transform: scaleX(0); }
        .hx-seg.is-done i { transform: none; }
        .hx-seg i.run { background: linear-gradient(90deg, #f59e0b, #e5581e); animation: hx-fill 4.5s linear forwards; }
        .hx-frame:hover .hx-seg i.run { animation-play-state: paused; }
        .hx-seg:hover span { background: rgba(1, 45, 113, .3); }

        .hx-arrow { z-index: 5; top: auto; bottom: 12px; transform: none; width: 32px; height: 32px; }
        .hx-arrow--prev { left: auto; right: 52px; }
        .hx-arrow--next { right: 12px; }
        .hx-dock { padding-top: 16px; }
        @media (prefers-reduced-motion: reduce) {
            .hx-slide.is-on, .hx-slide.is-on img { animation: none; }
        }
    }
</style>
<script>
(function () {
    if (!window.matchMedia('(min-width: 992px)').matches) return;

    // banner: story-style segments on top; the current segment's fill is the
    // timer, so hover-pause is exact. New slides wipe in over the old one.
    var slides = document.querySelectorAll('.hx-slide');
    var segs = document.querySelectorAll('.hx-seg');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var at = 0;
    function restartBar() {
        segs.forEach(function (s, n) {
            var fill = s.querySelector('i');
            fill.classList.remove('run');
            s.classList.toggle('is-done', n < at);
        });
        if (!segs[at] || reduce) return;
        var fill = segs[at].querySelector('i');
        void fill.offsetWidth;
        fill.classList.add('run');
    }
    function show(i) {
        var next = (i + slides.length) % slides.length;
        if (next === at && slides[at].classList.contains('is-on')) { restartBar(); return; }
        slides.forEach(function (el, n) {
            el.classList.toggle('was-on', n === at);
            el.classList.toggle('is-on', n === next);
        });
        at = next;
        restartBar();
    }
    if (slides.length > 1) {
        document.querySelectorAll('[data-hx-step]').forEach(function (b) {
            b.addEventListener('click', function () { show(at + (+b.dataset.hxStep)); });
        });
        segs.forEach(function (s, n) {
            s.addEventListener('click', function () { show(n); });
            s.querySelector('i').addEventListener('animationend', function () { show(at + 1); });
        });
        if (reduce) {
            setInterval(function () { show(at + 1); }, 6000);
        } else {
            restartBar();
        }
    }

})();
</script>

<script>
(function () {
    var orbit = document.getElementById('sbHomeBusinessOrbit');
    if (!orbit) return;

    document.querySelectorAll('.sb-home-business-nav').forEach(function (button) {
        button.addEventListener('click', function () {
            var card = orbit.querySelector('.sb-home-business');
            if (!card) return;

            var gap = parseFloat(getComputedStyle(orbit).columnGap) || 0;
            var direction = button.classList.contains('sb-home-business-nav--next') ? 1 : -1;
            orbit.scrollBy({
                left: direction * (card.getBoundingClientRect().width + gap) * 2,
                behavior: 'smooth'
            });
        });
    });
})();
</script>

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

                    $whatsappCountryCodes = is_array($featured->whatsapp_country_code)
                        ? $featured->whatsapp_country_code
                        : json_decode($featured->whatsapp_country_code, true);

                    $mobilePhone = $mobileNumbers[0] ?? '';
                    $whatsappPhone = $whatsappNumbers[0] ?? '';
                    $whatsappCountryCode = $whatsappCountryCodes[0] ?? '+91';

                    $cleanMobilePhone = preg_replace('/[^0-9]/', '', $mobilePhone);
                    $cleanWhatsappPhone = preg_replace('/[^0-9]/', '', $whatsappCountryCode . $whatsappPhone);

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
                                       class="btn-whatsapp"
                                       title="WhatsApp {{ $whatsappCountryCode }} {{ $whatsappPhone }}"
                                       aria-label="WhatsApp {{ $whatsappCountryCode }} {{ $whatsappPhone }}">

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
{{-- lower banner slider removed: the banner is in the hero now --}}


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
                    @if(!empty($categoryItem->image))
                        <img class="ex-photo" width="64" height="64" src="{{ asset('assets/images/category/' . $categoryItem->image) }}" loading="lazy" alt="{{ $categoryItem->name }}">
                    @elseif(!empty($categoryItem->icon) && Str::endsWith(strtolower($categoryItem->icon), ['.png', '.jpg', '.jpeg', '.svg', '.webp']))
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
            <span class="ex-go" aria-hidden="true">Providers dekhein <span class="ex-go-arrow">&rarr;</span></span>

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
        gap:11px !important;
        padding:5px 0px 8px !important;
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
			gap:10px !important;
        }
		
        .trending-same-section .trend-nav,
        .location-same-section .trend-nav,
        .popular-same-section .trend-nav{
            display:flex !important;
            width:25px;
            height:25px;
            border-radius:50%;
            z-index:6;
        }
        .trending-same-section .prev-btn,
        .location-same-section .prev-btn,
        .popular-same-section .prev-btn{
            left:2px;
        }
        .trending-same-section .next-btn,
        .location-same-section .next-btn,
        .popular-same-section .next-btn{
            right:2px;
        }
        .trending-same-section .trending-progress,
        .location-same-section .location-progress,
        .popular-same-section .popular-progress{
            display:none !important;
        }
    }
    .category-area{
        width:calc(100% - 40px);
        
        margin:5px auto !important;
        padding:0 !important;
    }
    .category-area > .container-fildu{
        box-sizing:border-box;
        width:100%;
        padding:22px;
        border:1px solid #012d71;
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
        outline:2px solid #012d71;
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
        color:#012d71;
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
        color:#012d71;
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
            grid-template-columns:repeat(3,minmax(0,1fr));
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
        padding:5px;
        border:1px solid #012d71;
        border-radius:18px;
        background:linear-gradient(145deg,#fff 0%,#f7faf6 100%);
        box-shadow:0 8px 24px rgba(19,136,8,.09);
    }
    .rr-section .cardhotkey__wrap{
        padding:14px 4px ! important;
        border:1px solid #e5ede2;
        border-radius:13px;
        background:rgba(255,255,255,.75);
        box-shadow:0 2px 8px rgba(19,136,8,.035);
    }
    .rr-section .cardhotkey__wrap h2{
        position:relative;
        margin:0 0 8px;
        padding-left:12px;
        color:#012d71 ;
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
        background:#012d71;
        content:"";
    }
    .rr-section .subcategory-col{
        border-color:#012d71;
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
        color:#012d71;
        font-weight:600;
    }
    .trending-same-section > .container-fluid > .border,
    .location-same-section > .container-fluid > .border,
    .popular-same-section > .container-fluid > .border{
        border:1px solid #012d71 !important;
        border-radius:16px !important;
        background:linear-gradient(145deg,#fff 0%,#f9fbf8 100%) !important;
        box-shadow:0 6px 18px rgba(19,136,8,.06);
    }
    .trending-same-section .trending-section-title,
    .location-same-section .location-section-title,
    .popular-same-section .popular-section-title,
    .seasonal-same-section h2{
        color:#012d71 !important;
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
        border:1px solid #012d71;
        border-radius:14px;
        box-shadow:0 5px 14px rgba(19,136,8,.09);
    }
    .location-same-section .trend-nav,
    .popular-same-section .trend-nav,
    .seasonal-same-section .trend-nav{
        border:1px solid #012d71;
        background:#fff;
        color:#012d71;
        box-shadow:0 4px 12px rgba(19,136,8,.12);
    }
    .location-same-section .trend-nav:hover,
    .popular-same-section .trend-nav:hover,
    .seasonal-same-section .trend-nav:hover{
        border-color:#012d71;
        background:#012d71;
        color:#fff;
    }
    .category-banner-slider{
        overflow:hidden;
        border:1px solid #012d71;
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
        padding-top:10px;
        padding-bottom:10px;
        border:1px solid #012d71;
        border-radius:16px;
        background:rgba(255,255,255,.92);
        box-shadow:0 6px 18px rgba(19,136,8,.06);
    }
    .category-content-row{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        row-gap:14px !important;
        column-gap:10px;
        margin-right:0;
        margin-left:0;
    }
    .category-content-row > [class*="col-"]{
        display:flex;
        min-height:155px;
        width:auto;
        max-width:none !important;
        flex-direction:column;
        flex:none;
        min-width:0;
        padding:16px;
        border:1px solid #e6eee3;
        border-radius:12px;
        background:linear-gradient(145deg,#fff 0%,#fafcf9 100%);
        box-shadow:0 3px 10px rgba(19,136,8,.04);
        transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease;
    }
    .category-content-row > [class*="col-"]:hover{
        border-color:#c7dfc3;
        box-shadow:0 7px 16px rgba(19,136,8,.09);
        transform:translateY(-2px);
    }
    .category-content-row .category-content-title a,
    .category-content-row .category-content-icon i{
        color:#012d71 !important;
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
        
        padding:10px;
        border:1px solid #012d71;
        border-radius:18px;
        background:linear-gradient(145deg,#fff 0%,#f8fbf7 100%);
        box-shadow:0 8px 24px rgba(19,136,8,.085);
    }
    .featured-business-listings .section-title{
        margin-bottom:20px;
    }
    .featured-business-listings .premium-listings-title{
        margin:0;
        color:#012d71 !important;
        font-size:clamp(20px,2vw,25px) !important;
        line-height:1.3 !important;
    }
    .featured-business-listings .premium-listings-title::before{
        background:#012d71;
    }
    .featured-business-listings .listing-area{
        display:flex;
        height:480px;
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
        min-height:25px;
        
       
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
        margin-top:0 !important;
    }
    .featured-business-listings .listing-area .title a{
        color:#012d71 ;
        font-weight:700;
        text-decoration:none;
    }
    .featured-business-listings .listing-area .title a:hover{
        color:#012d71;
    }
    .featured-business-listings .listing-location{
        color:#012d71;
    }
    .featured-business-listings .listing-location > i{
        color:#012d71;
    }
    .featured-business-listings .btn-call,
    .featured-business-listings .btn-whatsapp{
        min-height:40px;
        border:1px solid #012d71;
        border-radius:8px;
        font-size:13px;
        font-weight:600;
        transition:background-color .18s ease,border-color .18s ease,color .18s ease;
    }
    .featured-business-listings .btn-call{
        border-color:#012d71;
        background:#012d71;
        color:##012d71;
    }
    .featured-business-listings .btn-call:hover{
        border-color:#012d71 ;
        background:#fff;
        color:#fff;
    }
    .featured-business-listings .btn-whatsapp{
        background:#f7faf6;
        color:#012d71;
    }
    .featured-business-listings .btn-whatsapp:hover{
        border-color:#b8d4b4;
        background:#fff ;
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
            padding:10px 0px 10px 10px;
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
        .category-content-row{
            grid-template-columns:minmax(0,1fr);
        }
    }
    @media (min-width:768px) and (max-width:1199px){
        .category-content-row{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
    }
	 .jd-rating-count {
            font-size: 14px ! important;
        }
@media (max-width: 767px) {
  .popup-grid {grid-template-columns: repeat(4, minmax(0, 1fr)) !important;}
  .popup-grid img {
    width: 50px !important;
  }
}
@media screen and (min-width: 1200px) and (max-width: 1280px) {
	 .category-area{ max-width:1270px ! important; }
	 .featured-business-listings > .container-fluid{ max-width:1270px ! important;}
	 .bazzar-slider-wrapper { max-width: 1230px ! important;}
	 .rr-section{width: 1240px !important;margin-left: 20px !important;}
	 .category-content-section > .container-fluid{width: 1230px !important;}
	 .popular-category-section {width: 1230px !important;}
	 .popular-city-section { width: 1230px !important; border-radius: 10px;}
	 .container_socia{ max-width:1250px ! important;margin-left: 30px ! important}

}
    body.home-page .banner-section{
        border-bottom-color:#e2e9f2;
        background:linear-gradient(180deg,#f8faff 0%,#f3f7fc 100%);
    }
    body.home-page .sb-home-hero-eyebrow::before{
        background:#e4060d;
    }
    body.home-page .sb-home-hero-cta{
        box-shadow:0 5px 12px rgba(8,47,112,.16);
    }
    body.home-page .home-search-controls-row{
        border-color:#dce5f0;
        box-shadow:0 3px 12px rgba(8,47,112,.05);
    }
    body.home-page .category-area > .container-fildu,
    body.home-page .sb-home-hero,
    body.home-page .rr-section > .container-fluid,
    body.home-page .trending-same-section > .container-fluid > .border,
    body.home-page .location-same-section > .container-fluid > .border,
    body.home-page .popular-same-section > .container-fluid > .border,
    body.home-page .seasonal-same-section > .container-fluid > .border,
    body.home-page .category-banner-slider,
    body.home-page .category-content-section > .container-fluid,
    body.home-page .featured-business-listings > .container-fluid{
        border-color:#dce5f0 !important;
        background:linear-gradient(145deg,#fff 0%,#f7f9fd 100%) !important;
        box-shadow:0 8px 24px rgba(8,47,112,.07) !important;
    }
    body.home-page .popular-category-section,
    body.home-page .popular-city-section{
        border-color:#dce5f0;
        background:linear-gradient(145deg,#fff 0%,#f7f9fd 100%);
        box-shadow:0 8px 24px rgba(8,47,112,.07);
    }
    body.home-page .category-area .category-box .category-icon-img,
    body.home-page .rr-section .cardhotkey__wrap,
    body.home-page .rr-section .subcategory-col,
    body.home-page .trending-same-section .trend-card-link,
    body.home-page .location-same-section .trend-card-link,
    body.home-page .popular-same-section .trend-card-link,
    body.home-page .seasonal-same-section .trend-card-link,
    body.home-page .category-content-row > [class*="col-"],
    body.home-page .featured-business-listings .listing-area{
        border-color:#e2e9f2;
        box-shadow:0 4px 14px rgba(8,47,112,.07);
    }
    body.home-page .category-area .category-item:hover .category-icon-img,
    body.home-page .rr-section .subcategory-col:hover,
    body.home-page .category-content-row > [class*="col-"]:hover,
    body.home-page .featured-business-listings .listing-area:hover{
        border-color:#b9cce3;
        box-shadow:0 8px 20px rgba(8,47,112,.12);
    }
    body.home-page .rr-section,
    body.home-page .category-content-section,
    body.home-page .featured-business-listings{
        background:linear-gradient(180deg,#f4f7fc 0%,#fff 100%);
    }
    body.home-page .rr-section .subcategory-col,
    body.home-page .featured-business-listings .btn-whatsapp{
        color:#012d71 !important;
		
    }
    body.home-page .category-content-row > [class*="col-"]{
        background:linear-gradient(145deg,#fff 0%,#f8fafd 100%);
    }
    body.home-page .featured-business-listings .btn-call{
        color:#fff;
    }
    body.home-page .featured-business-listings .btn-whatsapp{
        border-color:#dce5f0;
        color:#012d71;
        white-space:nowrap;
        transition:background-color .2s ease, border-color .2s ease, color .2s ease;
    }
    body.home-page .featured-business-listings .btn-call:hover,
    body.home-page .featured-business-listings .btn-whatsapp:hover{
        border-color:#012d71 !important;
        background:#012d71 !important;
        color:#fff !important;
    }
    body.home-page .featured-business-listings .btn-call:hover i,
    body.home-page .featured-business-listings .btn-whatsapp:hover i{
        color:#fff !important;
    }
	.rr-section .subcategory-col{}
</style>



<style id="hx-soft-sections">
    /* Open sections (preview): no outline, no shadow, just space. */
    body.home-page .category-area > .container-fildu,
    body.home-page .rr-section > .container-fluid,
    body.home-page .trending-same-section > .container-fluid > .border,
    body.home-page .location-same-section > .container-fluid > .border,
    body.home-page .popular-same-section > .container-fluid > .border,
    body.home-page .category-banner-slider,
    body.home-page .category-content-section > .container-fluid,
    body.home-page .featured-business-listings > .container-fluid,
    body.home-page .popular-category-section,
    body.home-page .popular-city-section {
        border: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    body.home-page .location-same-section .trend-card-link,
    body.home-page .popular-same-section .trend-card-link,
    body.home-page .seasonal-same-section .trend-card-link {
        border: 1px solid #e6ecf6 !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    body.home-page .location-same-section .trend-card-link:hover,
    body.home-page .popular-same-section .trend-card-link:hover,
    body.home-page .seasonal-same-section .trend-card-link:hover {
        border-color: #012d71 !important;
        box-shadow: 0 12px 24px -18px rgba(1, 45, 113, .55);
        transform: translateY(-2px);
    }
    body.home-page .location-same-section .trend-nav,
    body.home-page .popular-same-section .trend-nav,
    body.home-page .seasonal-same-section .trend-nav {
        border: 1px solid #dfe7f3 !important;
        box-shadow: 0 4px 12px -6px rgba(1, 45, 113, .35);
    }
    body.home-page .rr-section .subcategory-col { border-color: #e6ecf6 !important; }
</style>

<style id="hx-explore">
    body.home-page .category-content-row { row-gap: 16px !important; column-gap: 16px; }
    body.home-page .category-content-row > [class*="col-"] {
        --tint: #eef3ff; --ink: #012d71;
        position: relative;
        overflow: hidden;
        min-height: 0;
        padding: 20px 20px 18px;
        border: 1px solid #e6ecf6 !important;
        border-radius: 18px;
        background: #fff !important;
        box-shadow: 0 6px 18px -14px rgba(1, 45, 113, .35) !important;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    body.home-page .category-content-row > [class*="col-"]:nth-child(4n+2) { --tint: #fff2ea; --ink: #c2410c; }
    body.home-page .category-content-row > [class*="col-"]:nth-child(4n+3) { --tint: #eaf7f0; --ink: #067647; }
    body.home-page .category-content-row > [class*="col-"]:nth-child(4n+4) { --tint: #f3eeff; --ink: #5b21b6; }
    /* accent bar that fills on hover */
    body.home-page .category-content-row > [class*="col-"]::before {
        content: ""; position: absolute; left: 0; top: 0; height: 3px; width: 0;
        background: var(--ink); transition: width .3s ease;
    }
    body.home-page .category-content-row > [class*="col-"]:hover {
        transform: translateY(-4px);
        border-color: transparent !important;
        box-shadow: 0 22px 40px -24px rgba(1, 45, 113, .55) !important;
    }
    body.home-page .category-content-row > [class*="col-"]:hover::before { width: 100%; }
    body.home-page .category-content-title-wrap { gap: 12px; margin-bottom: 12px; }
    body.home-page .category-content-icon {
        flex: 0 0 52px; width: 52px; height: 52px; margin: 0 !important;
        display: grid; place-items: center; border-radius: 14px; background: var(--tint);
        transition: background-color .2s ease, transform .2s ease;
    }
    body.home-page .category-content-icon img { width: 30px !important; height: 30px !important; object-fit: contain; }
    body.home-page .category-content-icon:has(.ex-photo) { flex-basis: 64px; width: 64px; height: 64px; overflow: hidden; background: #fff; box-shadow: 0 6px 14px -10px rgba(1, 45, 113, .5); }
    body.home-page .category-content-icon img.ex-photo { width: 100% !important; height: 100% !important; object-fit: cover; }
    body.home-page .category-content-row > [class*="col-"]:hover .category-content-icon { background: #fff; box-shadow: inset 0 0 0 2px var(--ink); transform: rotate(-6deg) scale(1.04); }
    body.home-page .category-content-title { font-size: 16px !important; font-weight: 700; line-height: 1.25; }
    body.home-page .category-content-row .category-content-title a { color: #17213f !important; text-decoration: none; }
    /* whole card clicks through to the category */
    body.home-page .category-content-row .category-content-title a::after { content: ""; position: absolute; inset: 0; z-index: 1; }
    body.home-page .category-content-row .spn {
        color: #667085 !important; font-size: 13px !important; line-height: 1.6 !important;
        display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;
    }
    body.home-page .ex-go {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; width: fit-content;
        padding: 6px 12px; border-radius: 999px; background: var(--tint); color: var(--ink);
        font-size: 12px; font-weight: 700; transition: background-color .2s ease, color .2s ease;
    }
    body.home-page .ex-go-arrow { display: inline-block; transition: transform .2s ease; }
    body.home-page .category-content-row > [class*="col-"]:hover .ex-go { background: var(--ink); color: #fff; }
    body.home-page .category-content-row > [class*="col-"]:hover .ex-go-arrow { transform: translateX(4px); }
    body.home-page .category-content-heading .popular-services-title { font-size: clamp(20px, 2vw, 26px) !important; }
    body.home-page .category-content-heading .popular-services-title {
        display: flex !important; flex-wrap: wrap; align-items: center;
        padding-left: 12px !important;
        border-left: 3px solid #e5581e !important;
        color: #101828 !important; font-weight: 800 !important; letter-spacing: -.3px;
    }
    body.home-page .category-content-heading .popular-services-title::after {
        content: "Roz ki zaroorat ki services, ek hi jagah";
        flex-basis: 100%; margin-top: 4px;
        color: #667085; font-size: 13px; font-weight: 500; letter-spacing: 0; line-height: 1.4;
    }
    body.home-page .category-content-bottom {
        padding: 12px 16px; border-left: 3px solid #e5581e; border-radius: 0 10px 10px 0; background: #fff8f3;
    }
    body.home-page .category-content-bottom .spn { color: #667085 !important; font-size: 12.5px !important; line-height: 1.6; }
    @media (prefers-reduced-motion: reduce) {
        body.home-page .category-content-row > [class*="col-"],
        body.home-page .category-content-icon, body.home-page .ex-go-arrow { transition: none; }
    }
</style>

<style id="hx-popular">
    body.home-page .popular-search-section .trend-card-link {
        border: 0 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 22px -16px rgba(1, 45, 113, .38) !important;
        isolation: isolate;
        transition: transform .22s ease, box-shadow .22s ease !important;
    }
    body.home-page .popular-search-section .trend-card-link img { transition: transform .45s ease !important; }
    body.home-page .popular-search-section .trend-card-overlay {
        background: linear-gradient(180deg, rgba(1, 45, 113, 0) 50%, rgba(1, 45, 113, .26) 70%, rgba(1, 32, 82, .8) 100%) !important;
        transition: background .25s ease;
    }
    /* title: white text on the gradient, no solid navy bar */
    body.home-page #popularScroll .trend-card-title,
    body.home-page #popularScroll .trend-card-title--single,
    body.home-page #popularScroll .trend-card-link:hover .trend-card-title,
    body.home-page #popularScroll .trend-card-link:hover .trend-card-title--single {
        background: transparent !important;
        border-radius: 0 !important;
        padding: 0 12px 4px !important;
        color: #fff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        white-space: normal !important;
        text-shadow: 0 1px 6px rgba(0, 0, 0, .35);
    }
    /* "Dekhein ->" slides up under the title on hover */
    body.home-page .popular-search-section .trend-card-overlay > div::after {
        content: "Dekhein \2192";
        display: block;
        max-height: 0; overflow: hidden; opacity: 0;
        padding: 0 12px;
        color: #ffb38a; font-size: 11.5px; font-weight: 700; line-height: 16px;
        transition: max-height .28s ease, opacity .28s ease, padding-top .28s ease;
    }
    body.home-page .popular-search-section .trend-card-overlay > div { padding-bottom: 10px !important; }
    body.home-page .popular-search-section .trend-card-link:hover { transform: translateY(-5px) !important; box-shadow: 0 18px 30px -18px rgba(1, 45, 113, .45), 0 0 0 2px #fff, 0 0 0 4px rgba(229, 88, 30, .55) !important; }
    body.home-page .popular-search-section .trend-card-link:hover img { transform: scale(1.09) !important; }
    body.home-page .popular-search-section .trend-card-link:hover .trend-card-overlay > div::after { max-height: 24px; opacity: 1; padding-top: 3px; }
    body.home-page .popular-search-section .trend-nav {
        width: 40px !important; height: 40px !important; border: 0 !important; border-radius: 50% !important;
        background: rgba(255, 255, 255, .95) !important; color: #012d71 !important;
        box-shadow: 0 8px 18px -8px rgba(1, 45, 113, .55) !important;
    }
    body.home-page .popular-search-section .trend-nav:hover { background: #012d71 !important; color: #fff !important; }

    /* hover lifts 5px and draws a 4px ring: give the scroller room so it isn't clipped */
    body.home-page .popular-search-section .trending-scroll,
    body.home-page .top-location-section .trending-scroll {
        padding-top: 12px !important;
        padding-bottom: 14px !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
    }
    @media (max-width: 767px) {
        body.home-page #popularScroll .trend-card-title,
        body.home-page #popularScroll .trend-card-title--single { font-size: 12px !important; padding: 0 9px 2px !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        body.home-page .popular-search-section .trend-card-link,
        body.home-page .popular-search-section .trend-card-link img { transition: none !important; }
    }
</style>

<style id="hx-location">
    body.home-page .top-location-section .trend-card-link {
        border: 0 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 22px -16px rgba(1, 45, 113, .38) !important;
        isolation: isolate;
        transition: transform .22s ease, box-shadow .22s ease !important;
    }
    body.home-page .top-location-section .trend-card-link img { transition: transform .5s ease !important; }
    body.home-page .top-location-section .trend-card-overlay {
        background: linear-gradient(180deg, rgba(1, 45, 113, 0) 50%, rgba(1, 45, 113, .26) 70%, rgba(1, 32, 82, .8) 100%) !important;
    }
    /* title + "Explore Now": no navy bars, text on the gradient */
    body.home-page .top-location-section .trend-card-title,
    body.home-page .top-location-section .trend-card-link:hover .trend-card-title {
        background: transparent !important;
        border: 0 !important;
        padding: 0 12px 6px !important;
        color: #fff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        text-shadow: 0 1px 6px rgba(0, 0, 0, .35);
    }
    body.home-page .top-location-section .trend-card-subtitle,
    body.home-page .top-location-section .trend-card-link:hover .trend-card-subtitle {
        display: block !important;
        margin: 0 !important;
        padding: 0 12px 12px !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #ffb38a !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
    }
    body.home-page .top-location-section .trend-card-subtitle::after { content: " \2192"; display: inline-block; transition: transform .22s ease; }
    body.home-page .top-location-section .trend-card-link:hover .trend-card-subtitle::after { transform: translateX(4px); }
    body.home-page .top-location-section .trend-card-link:hover { transform: translateY(-5px) !important; box-shadow: 0 18px 30px -18px rgba(1, 45, 113, .45), 0 0 0 2px #fff, 0 0 0 4px rgba(229, 88, 30, .55) !important; }
    body.home-page .top-location-section .trend-card-link:hover img { transform: scale(1.09) !important; }
    body.home-page .top-location-section .trend-nav {
        width: 40px !important; height: 40px !important; border: 0 !important;
        box-shadow: 0 8px 18px -8px rgba(1, 45, 113, .55) !important;
    }
    @media (max-width: 767px) {
        body.home-page .top-location-section .trend-card-title { font-size: 13px !important; padding: 0 9px 4px !important; }
        body.home-page .top-location-section .trend-card-subtitle { padding: 0 9px 9px !important; font-size: 10px !important; }
        body.home-page .top-location-section .trend-nav { width: 28px !important; height: 28px !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        body.home-page .top-location-section .trend-card-link,
        body.home-page .top-location-section .trend-card-link img { transition: none !important; }
    }
</style>

<style id="hx-trending">
    body.home-page #trendingScroll {
        padding-top: 12px !important;
        padding-bottom: 14px !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
    }
    body.home-page #trendingScroll .trend-card-link {
        border: 0 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 22px -16px rgba(1, 45, 113, .38) !important;
        isolation: isolate;
        transition: transform .22s ease, box-shadow .22s ease !important;
    }
    body.home-page #trendingScroll .trend-card-link img { transition: transform .45s ease !important; }
    body.home-page #trendingScroll .trend-card-overlay {
        padding: 0 !important;
        background: linear-gradient(180deg, rgba(1, 45, 113, 0) 50%, rgba(1, 45, 113, .26) 70%, rgba(1, 32, 82, .8) 100%) !important;
    }
    body.home-page #trendingScroll .trend-card-overlay > div {
        position: static !important;
        width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        padding-bottom: 10px !important;
    }
    body.home-page #trendingScroll .trend-card-title,
    body.home-page #trendingScroll .trend-card-title--single,
    body.home-page #trendingScroll .trend-card-link:hover .trend-card-title,
    body.home-page #trendingScroll .trend-card-link:hover .trend-card-title--single {
        position: static !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        margin: 0 !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        padding: 0 12px 4px !important;
        color: #fff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        white-space: normal !important;
        text-shadow: 0 1px 6px rgba(0, 0, 0, .35);
    }
    body.home-page #trendingScroll .trend-card-overlay > div::after {
        content: "Dekhein \2192";
        display: block;
        max-height: 0; overflow: hidden; opacity: 0;
        padding: 0 12px;
        color: #ffb38a; font-size: 11.5px; font-weight: 700; line-height: 16px;
        transition: max-height .28s ease, opacity .28s ease, padding-top .28s ease;
    }
    body.home-page #trendingScroll .trend-card-link:hover { transform: translateY(-5px) !important; box-shadow: 0 18px 30px -18px rgba(1, 45, 113, .45), 0 0 0 2px #fff, 0 0 0 4px rgba(229, 88, 30, .55) !important; }
    body.home-page #trendingScroll .trend-card-link:hover img { transform: scale(1.09) !important; }
    body.home-page #trendingScroll .trend-card-link:hover .trend-card-overlay > div::after { max-height: 24px; opacity: 1; padding-top: 3px; }
    body.home-page .trending-same-section .trend-nav {
        width: 40px !important; height: 40px !important; border: 0 !important; border-radius: 50% !important;
        background: rgba(255, 255, 255, .95) !important; color: #012d71 !important;
        box-shadow: 0 8px 18px -8px rgba(1, 45, 113, .55) !important;
    }
    body.home-page .trending-same-section .trend-nav:hover { background: #012d71 !important; color: #fff !important; }
    @media (max-width: 767px) {
        body.home-page #trendingScroll .trend-card-title,
        body.home-page #trendingScroll .trend-card-title--single { font-size: 12px !important; padding: 0 9px 2px !important; }
        body.home-page .trending-same-section .trend-nav { width: 28px !important; height: 28px !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        body.home-page #trendingScroll .trend-card-link,
        body.home-page #trendingScroll .trend-card-link img { transition: none !important; }
    }
</style>

<style id="hx-rr">
    /* Category groups: same layout as before (photo, name below), one brand
       colour, cleaner cards. */
    body.home-page .rr-section .cardhotkey__wrap {
        padding: 18px 16px 18px !important;
        border: 1px solid #e6ecf6 !important;
        border-radius: 20px !important;
        background: #fff !important;
        box-shadow: 0 14px 30px -26px rgba(1, 45, 113, .5) !important;
    }
    body.home-page .rr-section .cardhotkey__wrap h2 {
        display: flex; align-items: center; gap: 10px;
        margin: 0 0 14px !important;
        padding: 0 0 12px !important;
        border-bottom: 1px solid #eef2f8;
        color: #012d71 !important;
        font-size: 17px !important;
        font-weight: 800 !important;
    }
    body.home-page .rr-section .cardhotkey__wrap h2::before {
        position: static !important;
        flex: 0 0 8px; width: 8px !important; height: 8px !important;
        border-radius: 50% !important;
        background: #e5581e !important;
        box-shadow: 0 0 0 4px rgba(229, 88, 30, .15);
    }
    body.home-page .rr-section .subcategory-scroll { overflow: visible !important; }
    body.home-page .rr-section .subcategory-row { gap: 14px !important; }
    body.home-page .rr-section .subcategory-col {
        min-height: 0 !important;
        margin: 0 !important;
        padding: 8px 8px 4px !important;
        border: 0 !important;
        border-radius: 16px !important;
        background: #fff !important;
        box-shadow: 0 6px 18px -12px rgba(1, 45, 113, .38) !important;
        transition: transform .22s ease, box-shadow .22s ease !important;
    }
    body.home-page .rr-section .mini-service-card {
        height: 130px !important;
        border-radius: 12px !important;
        background: #f3f6fb !important;
    }
    body.home-page .rr-section .mini-service-card img { border-radius: 12px !important; transition: transform .45s ease; }
    body.home-page .rr-section .subcategory-col .font14 {
        position: relative;
        padding: 10px 4px 8px !important;
        color: #17213f !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        transition: color .2s ease;
    }
    body.home-page .rr-section .subcategory-col .font14::after {
        content: ""; display: block; width: 0; height: 2px; margin: 6px auto 0; border-radius: 2px;
        background: #e5581e; transition: width .25s ease;
    }
    body.home-page .rr-section .subcategory-col:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 18px 32px -18px rgba(1, 45, 113, .5) !important;
    }
    body.home-page .rr-section .subcategory-col:hover .mini-service-card img { transform: scale(1.07); }
    body.home-page .rr-section .subcategory-col:hover .font14 { color: #012d71 !important; }
    body.home-page .rr-section .subcategory-col:hover .font14::after { width: 28px; }
    @media (max-width: 767px) {
        body.home-page .rr-section .cardhotkey__wrap { padding: 14px 10px !important; }
        body.home-page .rr-section .cardhotkey__wrap h2 { font-size: 15px !important; }
        body.home-page .rr-section .subcategory-row { gap: 8px !important; }
        body.home-page .rr-section .subcategory-col { padding: 5px 5px 2px !important; border-radius: 12px !important; }
        body.home-page .rr-section .mini-service-card { height: 90px !important; border-radius: 9px !important; }
        body.home-page .rr-section .subcategory-col .font14 { font-size: 11px !important; padding: 7px 2px 5px !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        body.home-page .rr-section .subcategory-col,
        body.home-page .rr-section .mini-service-card img { transition: none !important; }
    }
</style>

<style id="hx-featured">
    /* Featured Listings, premium card: framed photo, rating pill on the
       photo, clean info stack, buttons pinned to the bottom. */
    body.home-page .featured-business-listings .slick-list { padding: 12px 2px 22px !important; }
    body.home-page .featured-business-listings .slick-track { display: flex !important; }
    body.home-page .featured-business-listings .slick-slide { height: auto !important; }
    body.home-page .featured-business-listings .slick-slide > div { height: 100%; }
    body.home-page .featured-business-listings .listing-area {
        position: relative;
        height: 100% !important;
        min-height: 0 !important;
        padding: 10px !important;
        border: 1px solid #edf1f7 !important;
        border-radius: 22px !important;
        background: #fff !important;
        box-shadow: 0 12px 30px -22px rgba(16, 24, 40, .45) !important;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease !important;
    }
    body.home-page .featured-business-listings .listing-area:hover {
        transform: translateY(-6px) !important;
        border-color: #e1e8f3 !important;
        box-shadow: 0 28px 44px -26px rgba(1, 45, 113, .5) !important;
    }
    /* framed photo */
    body.home-page .featured-business-listings .listing-area .thumb {
        position: relative;
        flex: 0 0 196px !important;
        height: 196px;
        overflow: hidden;
        border-radius: 16px !important;
        background: #eef2f8;
    }
    body.home-page .featured-business-listings .listing-area .thumb img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: transform .6s ease !important;
    }
    body.home-page .featured-business-listings .listing-area:hover .thumb img { transform: scale(1.06) !important; }

    body.home-page .featured-business-listings .listing-area .listing-content {
        display: flex !important; flex-direction: column; flex: 1 1 auto;
        padding: 14px 6px 4px !important;
    }
    /* title */
    body.home-page .featured-business-listings .listing-area .title {
        order: 1;
        min-height: 46px !important; max-height: 46px !important;
        margin: 0 0 8px !important;
        gap: 6px;
    }
    body.home-page .featured-business-listings .listing-area .title a {
        color: #101828 !important; font-size: 17px !important; font-weight: 800 !important;
        line-height: 1.35 !important; letter-spacing: -.2px;
        transition: color .2s ease;
    }
    body.home-page .featured-business-listings .listing-area:hover .title a { color: #012d71 !important; }
    body.home-page .featured-business-listings .listing-area .title .ico { color: #f59e0b !important; font-size: 14px; margin-top: 4px !important; }

    /* location + years */
    body.home-page .featured-business-listings .listing-location {
        order: 3;
        min-height: 0 !important; max-height: none !important;
        margin: 0 0 14px !important;
        color: #667085 !important; font-size: 13px !important; font-weight: 500;
    }
    body.home-page .featured-business-listings .listing-location > i,
    body.home-page .featured-business-listings .listing-location .la-map-marker { color: #e5581e !important; }
    body.home-page .featured-business-listings .listing-location .business-years i { color: #98a2b3 !important; }
    body.home-page .featured-business-listings .listing-location .dot-separator { color: #d0d5dd; }

    /* rating row under the title (in flow, never over the name) */
    body.home-page .featured-business-listings .jd-rating-wrap {
        order: 2;
        position: static !important;
        display: flex !important; flex-wrap: wrap !important; align-items: center;
        gap: 6px 8px !important;
        min-height: 0 !important;
        margin: 0 0 8px !important;
        overflow: visible !important;
    }
    body.home-page .featured-business-listings .jd-rating-wrap .jd-badge-img { height: 20px !important; width: auto !important; }
    /* every card keeps the same rating-row height; with no ratings the row
       stays as empty space, so all cards are the same size */
    body.home-page .featured-business-listings .jd-rating-wrap { height: 26px !important; min-height: 26px !important; flex-wrap: nowrap !important; overflow: hidden !important; }
    body.home-page .featured-business-listings .jd-rating-wrap:not(:has(.jd-rating-badge)):not(:has(img)) { visibility: hidden !important; }
    body.home-page .featured-business-listings .jd-rating-badge {
        display: inline-flex !important; align-items: center; justify-content: center;
        width: auto !important; min-width: 0 !important; height: auto !important;
        white-space: nowrap !important;
        padding: 3px 8px !important; border-radius: 999px !important;
        background: #0a9f34 !important; color: #fff !important;
        font-size: 12px !important; font-weight: 800 !important; line-height: 1.2 !important;
    }
    body.home-page .featured-business-listings .jd-rating-count { color: #344054 !important; font-size: 12px !important; font-weight: 600; }

    /* Contact for Service: blue primary */
    body.home-page .featured-business-listings .listing-content > .listing-contact:first-of-type { order: 4; min-height: 0 !important; margin: 0 0 10px !important; }
    body.home-page .featured-business-listings .btn-call {
        display: flex !important; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 44px;
        border: 0 !important; border-radius: 12px !important;
        background: #012d71 !important;
        color: #fff !important; font-size: 14px !important; font-weight: 700 !important;
        transition: background-color .18s ease, box-shadow .18s ease;
    }
    body.home-page .featured-business-listings .btn-call:hover {
        background: #011f52 !important; color: #fff !important;
        box-shadow: 0 12px 20px -12px rgba(1, 45, 113, .9);
    }

    /* Call Now (light) + WhatsApp (green), pinned to the bottom */
    body.home-page .featured-business-listings .listing-content > .listing-contact:last-child {
        order: 5; display: grid !important; grid-template-columns: 1fr 1fr; gap: 10px;
        min-height: 0 !important; margin-top: auto !important;
    }
    body.home-page .featured-business-listings a.btn-whatsapp {
        display: flex !important; align-items: center; justify-content: center; gap: 6px;
        min-height: 40px; width: auto !important;
        border: 1px solid #e4e9f2 !important; border-radius: 12px !important;
        background: #fff !important; color: #344054 !important;
        font-size: 13px !important; font-weight: 700 !important;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }
    body.home-page .featured-business-listings a.btn-whatsapp:hover {
        border-color: #012d71 !important; background: #f3f6fc !important; color: #012d71 !important;
    }
    body.home-page .featured-business-listings a.btn-whatsapp[href*="wa.me"],
    body.home-page .featured-business-listings a.btn-whatsapp[href*="whatsapp"] {
        border-color: #1fa855 !important; background: #1fa855 !important; color: #fff !important;
    }
    body.home-page .featured-business-listings a.btn-whatsapp[href*="wa.me"]:hover,
    body.home-page .featured-business-listings a.btn-whatsapp[href*="whatsapp"]:hover {
        border-color: #178a45 !important; background: #178a45 !important; color: #fff !important;
    }


    /* button icons: the icon-font glyphs render thin/odd, use crisp SVGs */
    body.home-page .featured-business-listings .btn-call > i,
    body.home-page .featured-business-listings a.btn-whatsapp > i { display: none !important; }
    body.home-page .featured-business-listings .btn-call::before,
    body.home-page .featured-business-listings a.btn-whatsapp::before {
        content: ""; flex: 0 0 16px; width: 16px; height: 16px;
        background: currentColor;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z'/%3E%3C/svg%3E") center / contain no-repeat;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z'/%3E%3C/svg%3E") center / contain no-repeat;
    }
    body.home-page .featured-business-listings a.btn-whatsapp[href*="wa.me"]::before,
    body.home-page .featured-business-listings a.btn-whatsapp[href*="whatsapp"]::before {
        flex-basis: 17px; width: 17px; height: 17px;
        -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.04 21.5a9.4 9.4 0 01-4.8-1.31l-.34-.2-3.57.94.95-3.48-.22-.36a9.4 9.4 0 1117.43-5 9.42 9.42 0 01-9.44 9.41zm8.02-17.43A11.3 11.3 0 002.25 17.58L.65 23.4l5.95-1.56a11.3 11.3 0 005.4 1.38c6.23 0 11.3-5.07 11.3-11.3 0-3.02-1.18-5.86-3.25-8z'/%3E%3C/svg%3E");
        mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.04 21.5a9.4 9.4 0 01-4.8-1.31l-.34-.2-3.57.94.95-3.48-.22-.36a9.4 9.4 0 1117.43-5 9.42 9.42 0 01-9.44 9.41zm8.02-17.43A11.3 11.3 0 002.25 17.58L.65 23.4l5.95-1.56a11.3 11.3 0 005.4 1.38c6.23 0 11.3-5.07 11.3-11.3 0-3.02-1.18-5.86-3.25-8z'/%3E%3C/svg%3E");
    }
    /* slider arrows */
    body.home-page .featured-business-listings .slick-arrow {
        width: 44px !important; height: 44px !important; border: 0 !important; border-radius: 50% !important;
        background: #fff !important; color: #012d71 !important;
        box-shadow: 0 12px 24px -12px rgba(16, 24, 40, .55) !important;
        z-index: 5;
    }
    body.home-page .featured-business-listings .slick-arrow:hover { background: #012d71 !important; color: #fff !important; }
    @media (prefers-reduced-motion: reduce) {
        body.home-page .featured-business-listings .listing-area,
        body.home-page .featured-business-listings .listing-area .thumb img { transition: none !important; }
    }
</style>

<style id="hx-more-cat">
    body.home-page .category-area .more-category-item .category-box i.more {
        display: flex !important; align-items: center; justify-content: center;
        width: calc(100% - 6px); height: calc(100% - 6px);
        border: 1px solid #e5ede2; border-radius: 12px; background: #fff;
        box-shadow: 0 3px 9px rgba(31, 77, 27, .12);
        color: #012d71 !important;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    body.home-page .category-area .more-category-item:hover .category-box i.more {
        transform: translateY(-2px) scale(1.06);
        border-color: #c5ddc1;
        box-shadow: 0 7px 15px rgba(31, 77, 27, .2);
    }
    @media (max-width: 767px) {
        body.home-page .category-area .more-category-item .category-box i.more { border-radius: 10px; }
    }
</style>

<style id="hx-footer-popular">
@media (min-width: 768px) {
    /* soft panels */
    body.home-page .popular-category-section,
    body.home-page .popular-city-section {
        padding: 26px 26px 28px !important;
        border: 0 !important;
        border-radius: 22px !important;
        background: linear-gradient(135deg, #f4f7ff 0%, #ffffff 55%, #fff6ef 100%) !important;
        box-shadow: 0 16px 36px -30px rgba(1, 45, 113, .45) !important;
    }
    body.home-page .popular-category-section .popular-title,
    body.home-page .popular-city-section .popular-title {
        flex-wrap: wrap;
        font-size: 21px !important; font-weight: 800 !important; letter-spacing: -.3px;
        color: #101828 !important;
        border-left-color: #e5581e !important;
        margin-bottom: 18px !important;
    }
    body.home-page .popular-category-section .popular-title::after,
    body.home-page .popular-city-section .popular-title::after {
        flex-basis: 100%;
        margin-top: 4px;
        color: #667085;
        font-size: 13px; font-weight: 500; letter-spacing: 0;
    }
    body.home-page .popular-category-section .popular-title::after { content: "Sabse zyada dhoondhi jaane wali services"; }
    body.home-page .popular-city-section .popular-title::after { content: "Apne sheher ki bharosemand services dekhein"; }

    /* tabs: segmented control */
    body.home-page .popular-category-tabs {
        display: inline-flex !important;
        max-width: 100%;
        gap: 4px !important;
        padding: 5px !important;
        margin-bottom: 18px;
        border-radius: 14px;
        background: #e9eef7;
        scrollbar-width: none;
    }
    body.home-page .popular-category-tabs::-webkit-scrollbar { display: none; }
    /* scroll arrows around the tab row (added by JS) */
    body.home-page .pct-wrap { position: relative; display: inline-block; max-width: 100%; margin-bottom: 18px; vertical-align: top; }
    body.home-page .pct-wrap > .popular-category-tabs { margin-bottom: 0 !important; overflow-x: auto !important; scroll-behavior: smooth; }
    body.home-page .pct-arr {
        position: absolute; top: 50%; z-index: 3;
        display: grid; place-items: center;
        width: 34px; height: 34px; padding: 0;
        border: 0; border-radius: 50%;
        background: #fff; color: #012d71;
        box-shadow: 0 8px 18px -8px rgba(16, 24, 40, .45);
        cursor: pointer; transform: translateY(-50%);
        transition: background-color .18s ease, color .18s ease, box-shadow .18s ease, opacity .18s ease;
    }
    body.home-page .pct-arr[hidden] { display: none; }
    body.home-page .pct-prev { left: -10px; }
    body.home-page .pct-next { right: -10px; }
    body.home-page .pct-arr:hover, body.home-page .pct-arr:focus-visible {
        background: #e5581e; color: #fff; box-shadow: 0 12px 22px -12px rgba(229, 88, 30, .75); outline: none;
    }
    body.home-page .pct-arr svg { width: 16px; height: 16px; }
    body.home-page .popular-cat-tab,
    body.home-page .popular-cat-tab:hover:not(.active) {
        padding: 9px 16px !important;
        border: 0 !important;
        border-radius: 10px !important;
        background: transparent !important;
        box-shadow: none !important;
        color: #475467 !important;
        font-size: 13px !important; font-weight: 700 !important;
        transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
    }
    body.home-page .popular-cat-tab:hover:not(.active) { color: #012d71 !important; background: rgba(255, 255, 255, .55) !important; }
    body.home-page .popular-cat-tab.active {
        border: 0 !important;
        border-radius: 10px !important;
        background: #fff !important;
        color: #012d71 !important;
        box-shadow: 0 6px 14px -8px rgba(1, 45, 113, .55) !important;
    }

    /* tinted chips, four colours in turn */
    body.home-page .popular-subcat-box.active,
    body.home-page .popular-city-list { gap: 10px !important; }
    body.home-page .popular-subcat-box .popular-subcategory-link,
    body.home-page .popular-city-list a.popular-city-link {
        --tint: #eaf0ff; --ink: #1d4ed8;
        padding: 8px 16px !important;
        border: 0 !important;
        border-radius: 999px !important;
        background: var(--tint) !important;
        color: var(--ink) !important;
        font-weight: 700 !important;
        box-shadow: none !important;
        transition: background-color .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease !important;
    }
    body.home-page .popular-subcat-box .popular-subcategory-link:nth-of-type(4n+2),
    body.home-page .popular-city-list a.popular-city-link:nth-of-type(4n+2) { --tint: #fff0e6; --ink: #c2410c; }
    body.home-page .popular-subcat-box .popular-subcategory-link:nth-of-type(4n+3),
    body.home-page .popular-city-list a.popular-city-link:nth-of-type(4n+3) { --tint: #e7f6ee; --ink: #067647; }
    body.home-page .popular-subcat-box .popular-subcategory-link:nth-of-type(4n+4),
    body.home-page .popular-city-list a.popular-city-link:nth-of-type(4n+4) { --tint: #f2ecff; --ink: #6d28d9; }
    body.home-page .popular-subcat-box a.popular-subcategory-link:hover,
    body.home-page .popular-subcat-box a.popular-subcategory-link:focus-visible,
    body.home-page .popular-city-list a.popular-city-link:hover,
    body.home-page .popular-city-list a.popular-city-link:focus-visible {
        background: var(--ink) !important;
        color: #fff !important;
        text-decoration: none !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 18px -12px var(--ink) !important;
    }
    body.home-page .popular-city-list a.popular-city-link::before { content: none !important; display: none !important; }
    /* clean link index: plain text in four columns, hairline rules, no boxes */
    body.home-page .popular-subcat-box.active,
    body.home-page .popular-city-list {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0 36px !important;
        padding: 6px 4px 0;
    }
    body.home-page .popular-subcat-box .popular-subcategory-link,
    body.home-page .popular-city-list a.popular-city-link {
        position: relative;
        display: block !important;
        min-width: 0;
        padding: 11px 22px 11px 0 !important;
        border: 0 !important;
        border-bottom: 1px solid #e8edf5 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        color: #344054 !important;
        font-size: 14px !important; font-weight: 500 !important; line-height: 1.35 !important;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        transition: color .2s ease, padding-left .25s ease !important;
    }
    body.home-page .popular-subcat-box .popular-subcategory-link::after,
    body.home-page .popular-city-list a.popular-city-link::after {
        content: "\2192";
        position: absolute; right: 2px; top: 50%;
        color: #e5581e; font-size: 14px; line-height: 1;
        opacity: 0; transform: translate(-6px, -50%);
        transition: opacity .2s ease, transform .25s ease;
    }
    body.home-page .popular-subcat-box a.popular-subcategory-link:hover,
    body.home-page .popular-subcat-box a.popular-subcategory-link:focus-visible,
    body.home-page .popular-city-list a.popular-city-link:hover,
    body.home-page .popular-city-list a.popular-city-link:focus-visible {
        background: transparent !important;
        color: #012d71 !important;
        font-weight: 600 !important;
        padding-left: 6px !important;
        transform: none;
        box-shadow: none !important;
        border-bottom-color: #012d71 !important;
    }
    body.home-page .popular-subcat-box a.popular-subcategory-link:hover::after,
    body.home-page .popular-city-list a.popular-city-link:hover::after { opacity: 1; transform: translate(0, -50%); }
    @media (max-width: 1199px) {
        body.home-page .popular-subcat-box.active,
        body.home-page .popular-city-list { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 991px) {
        body.home-page .popular-subcat-box.active,
        body.home-page .popular-city-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    /* Popular Categories: flat, full width; a feature card for the chosen
       category on the left, its services in three columns on the right */
    body.home-page .popular-category-section {
        margin: 24px 0 0 !important;
        padding: 34px max(24px, 2.2vw) 6px !important;
        border: 0 !important; border-radius: 0 !important;
        background: #fff !important; box-shadow: none !important;
    }
    body.home-page .popular-subcat-box.active.pcx {
        grid-template-columns: 290px repeat(3, minmax(0, 1fr)) !important;
        gap: 2px 34px !important;
        align-items: start;
    }
    body.home-page .pcx-feature {
        position: relative; overflow: hidden;
        grid-column: 1; grid-row: 1 / span var(--rows, 5);
        align-self: stretch;
        display: flex; flex-direction: column; gap: 14px;
        min-height: 210px; padding: 24px;
        border-radius: 22px;
        background:
            radial-gradient(180px 140px at 100% 0, rgba(229, 88, 30, .16), transparent 70%),
            linear-gradient(150deg, #eaf0ff 0%, #f7f9ff 55%, #fff3ea 100%);
        color: #0b1b3f; text-decoration: none !important;
    }
    body.home-page .pcx-img {
        display: grid; place-items: center; width: 66px; height: 66px;
        border-radius: 18px; background: #fff;
        box-shadow: 0 10px 20px -14px rgba(1, 45, 113, .55);
    }
    body.home-page .pcx-img img { width: 44px; height: 44px; object-fit: contain; }
    body.home-page .pcx-wm {
        position: absolute; right: -26px; bottom: -26px; width: 150px; height: 150px;
        object-fit: contain; opacity: .09; pointer-events: none;
        transition: transform .5s ease, opacity .3s ease;
    }
    body.home-page .pcx-name { font-size: 21px; font-weight: 800; line-height: 1.2; letter-spacing: -.3px; }
    body.home-page .pcx-meta { margin-top: -8px; color: #667085; font-size: 13px; font-weight: 500; }
    body.home-page .pcx-btn {
        align-self: flex-start; margin-top: auto;
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; border-radius: 999px;
        background: #012d71; color: #fff; font-size: 13px; font-weight: 700;
        transition: background-color .2s ease, gap .2s ease;
    }
    body.home-page .pcx-feature:hover .pcx-btn { background: #e5581e; gap: 12px; }
    body.home-page .pcx-feature:hover .pcx-wm { opacity: .14; transform: scale(1.08) rotate(-6deg); }
    body.home-page .pcx a.popular-subcategory-link { font-size: 14.5px !important; padding-top: 13px !important; padding-bottom: 13px !important; border-bottom-color: #eef2f8 !important; }
    @media (max-width: 1199px) {
        body.home-page .popular-subcat-box.active.pcx { grid-template-columns: 240px repeat(2, minmax(0, 1fr)) !important; }
    }

    /* Every section heading matches the footer ones: orange bar and
       near-black text; each section keeps its own description and badges */
    body.home-page .featured-business-listings .premium-listings-title,
    body.home-page .popular-search-section .popular-section-title,
    body.home-page .trending-same-section .trending-section-title,
    body.home-page .top-location-section .location-section-title {
        position: relative;
        padding-left: 12px !important;
        color: #101828 !important;
        font-size: clamp(20px, 2vw, 26px) !important;
        font-weight: 800 !important; letter-spacing: -.3px;
        line-height: 1.25 !important;
        text-transform: none !important;
    }
    body.home-page .featured-business-listings .premium-listings-title::before,
    body.home-page .popular-search-section .popular-section-title::before,
    body.home-page .trending-same-section .trending-section-title::before,
    body.home-page .top-location-section .location-section-title::before {
        content: "" !important; position: absolute; top: 2px; bottom: 2px; left: 0;
        width: 3px !important; border-radius: 2px;
        background: #e5581e !important;
    }
    /* category groups: same orange bar instead of the dot */
    body.home-page .rr-section .cardhotkey__wrap h2 { color: #101828 !important; letter-spacing: -.2px; }
    body.home-page .rr-section .cardhotkey__wrap h2::before {
        flex: 0 0 3px; width: 3px !important; height: 20px !important;
        border-radius: 2px !important; box-shadow: none !important;
    }

    /* Popular Cities: landmark photo in a circle above the name */
    body.home-page .popular-city-section {
        margin: 24px 0 0 !important;
        padding: 10px max(24px, 2.2vw) 34px !important;
        border: 0 !important; border-radius: 0 !important;
        background: #fff !important; box-shadow: none !important;
    }
    body.home-page .popular-city-list {
        grid-template-columns: repeat(auto-fill, minmax(104px, 1fr)) !important;
        gap: 24px 12px !important;
        padding-top: 12px;
    }
    body.home-page .popular-city-list a.popular-city-link {
        display: flex !important; flex-direction: column; align-items: center; gap: 10px;
        padding: 0 !important; border: 0 !important;
        white-space: normal; overflow: visible; text-align: center;
        color: #344054 !important; font-size: 13.5px !important; font-weight: 600 !important;
    }
    body.home-page .popular-city-list a.popular-city-link::after { content: none !important; }
    body.home-page .popular-city-list .pc-ic {
        display: block; width: 86px; height: 86px; overflow: hidden; border-radius: 50%;
        background: #eef3fb;
        transition: transform .25s ease;
    }
    body.home-page .popular-city-list .pc-ic img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .45s ease; }
    body.home-page .popular-city-list a.popular-city-link:hover,
    body.home-page .popular-city-list a.popular-city-link:focus-visible {
        padding-left: 0 !important; color: #012d71 !important; font-weight: 700 !important;
    }
    body.home-page .popular-city-list a.popular-city-link:hover .pc-ic {
        transform: translateY(-4px);
    }
    body.home-page .popular-city-list a.popular-city-link:hover .pc-ic img { transform: scale(1.12); }
}
</style>
<style id="hx-search-dd">
/* Home search dropdown: section labels + Clear All sat flush against the edges */
@media (min-width: 768px) {
    body.home-page #homeCategorySuggestions .location-section-title,
    body.home-page #homeLocationSuggestions .location-section-title {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        margin: 6px 0 2px;
        padding: 10px 20px 6px 23px !important;
        color: #667085 !important;
        font-size: 11px !important; font-weight: 700 !important; letter-spacing: .08em;
        line-height: 1.2;
    }
    body.home-page #homeCategorySuggestions .location-section-title:not(:first-child),
    body.home-page #homeLocationSuggestions .location-section-title:not(:first-child) {
        margin-top: 8px; padding-top: 14px !important; border-top: 1px solid #eef2f7;
    }
    body.home-page .location-section-title .clear-recent-categories,
    body.home-page .location-section-title .clear-recent-locations {
        float: none !important;
        padding: 4px 10px; border-radius: 999px;
        background: #fff1ea; color: #e5581e !important;
        font-size: 11.5px !important; font-weight: 700 !important; letter-spacing: 0;
        transition: background-color .18s ease, color .18s ease;
    }
    body.home-page .location-section-title .clear-recent-categories:hover,
    body.home-page .location-section-title .clear-recent-locations:hover { background: #e5581e; color: #fff !important; }
    /* search + location boxes: orange accent instead of the old green glow */
    body.home-page .home-search-controls-row .input_location_box,
    body.home-page .home-search-controls-row .input_search_box { border-color: #e3e8f0 !important; }
    body.home-page .home-search-controls-row .input_location_box:hover,
    body.home-page .home-search-controls-row .input_search_box:hover { border-color: #f3b896 !important; }
    body.home-page .home-search-controls-row .input_location_box:focus-within,
    body.home-page .home-search-controls-row .input_search_box:focus-within {
        border-color: #e5581e !important;
        box-shadow: 0 0 0 3px rgba(229, 88, 30, .14) !important;
    }
    /* category tiles: no upward lift (the row clipped its top), orange hover border */
    body.home-page .category-area .category-item:hover .category-box { transform: none !important; }
    body.home-page .category-area .category-box { overflow: visible !important; align-items: center; justify-content: center; }
    body.home-page .category-area .category-item:hover .category-icon-img {
        transform: scale(1.05) !important;
        border-color: #e5581e !important;
        box-shadow: 0 8px 18px -8px rgba(229, 88, 30, .55) !important;
    }
    body.home-page .category-area .category-item a:focus-visible,
    body.home-page .category-area .category-item .more-btn:focus-visible { outline-color: #e5581e !important; }
    /* recent-search icon gets the same 30px box as the trending icon, so names line up */
    body.home-page #homeCategorySuggestions .category-search-icon {
        width: 30px; min-width: 30px; height: 30px;
        margin: 0 10px 0 5px;
        border-radius: 6px; background: #f2f4f7;
        color: #475467; font-size: 13px;
    }
}
</style>
<div class="hx-side-tabs">
    <a class="hx-side-wa" href="https://wa.me/919251067682" target="_blank" rel="noopener" aria-label="WhatsApp par baat karein">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span>WhatsApp</span>
    </a>
    <button type="button" class="hx-side-wa hx-side-bot" aria-label="Chat with SB assistant" onclick="var l=document.getElementById('sbBotLauncher'); if (l) l.click();">
        <i class="fa-solid fa-robot" aria-hidden="true"></i><span>Chat with SB</span>
    </button>
</div>
@include('themes.servicebazzar.partials.ai-assistant')
<style id="hx-side-wa">
/* JustDial-style vertical tab on the right edge, desktop only */
.hx-side-tabs { display: none; }
/* the round corner launcher stays hidden; the side tab opens the panel */
body.home-page .sb-bot-launcher { display: none !important; }
@media (min-width: 992px) {
    body.home-page .hx-side-tabs {
        position: fixed; right: 0; top: 190px; z-index: 1040;
        display: flex; flex-direction: column; align-items: flex-end; gap: 8px;
    }
    body.home-page .hx-side-wa {
        display: flex; border: 0; cursor: pointer; font-family: inherit; align-items: center; gap: 8px;
        writing-mode: vertical-rl; transform: rotate(180deg);
        padding: 16px 10px;
        border-radius: 0 10px 10px 0;
        background: #25d366;
        color: #fff !important;
        font-size: 13px; font-weight: 700; letter-spacing: .02em;
        text-decoration: none !important;
        box-shadow: 0 10px 22px -10px rgba(18, 140, 70, .8);
        transition: padding .18s ease, background-color .18s ease;
    }
    body.home-page .hx-side-wa i { font-size: 18px; transform: rotate(90deg); }
    body.home-page .hx-side-wa:hover,
    body.home-page .hx-side-wa:focus-visible { background: #1ebe5b; padding-left: 14px; color: #fff !important; }
    body.home-page:has(#sbBotPanel.is-open) .hx-side-tabs { display: none; }
    body.home-page .hx-side-bot { background: #012d70; box-shadow: 0 10px 22px -10px rgba(1, 45, 112, .8); }
    body.home-page .hx-side-bot i { font-size: 16px; }
    body.home-page .hx-side-bot:hover,
    body.home-page .hx-side-bot:focus-visible { background: #e5581e; }
}
</style>
<style id="hx-nav">
/* Desktop header menu: orange underline that grows on hover; Free Listing as an orange pill */
@media (min-width: 992px) {
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item,
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item:hover,
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item:focus-within { background-color: transparent !important; }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .nav-link:not(.nav-btn):not(.dropdown-toggle) {
        position: relative;
        color: #344054 !important;
        font-weight: 600 !important; letter-spacing: .04em;
        transition: color .18s ease;
    }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .nav-link:not(.nav-btn):not(.dropdown-toggle)::after {
        content: ""; position: absolute; left: 10px; right: 10px; bottom: 2px;
        height: 2px; border-radius: 2px; background: #e5581e;
        transform: scaleX(0); transform-origin: left center;
        transition: transform .22s ease;
    }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item:hover > .nav-link:not(.nav-btn),
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item:focus-within > .nav-link:not(.nav-btn) { color: #012d70 !important; }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item:hover > .nav-link:not(.nav-btn):not(.dropdown-toggle)::after,
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .nav-link:not(.nav-btn):focus-visible::after { transform: scaleX(1); }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-toggle { transition: color .18s ease, transform .18s ease; }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item:hover > .dropdown-toggle { color: #e5581e !important; }

    /* Services dropdown: white card, orange top edge, footer-style hover (slide + orange dot) */
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu {
        min-width: 260px !important;
        margin-top: 10px !important;
        padding: 8px !important;
        border: 1px solid #e8edf5 !important;
        border-top: 3px solid #e5581e !important;
        border-radius: 14px !important;
        background: #fff !important;
        box-shadow: 0 22px 44px -18px rgba(1, 45, 112, .35) !important;
    }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu .dropdown-item {
        position: relative;
        padding: 10px 14px !important;
        border-radius: 9px;
        color: #344054 !important;
        font-size: 13px !important; font-weight: 600 !important; letter-spacing: .03em;
        transition: background-color .18s ease, color .18s ease, padding-left .18s ease;
    }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu .dropdown-item::before {
        content: ""; position: absolute; left: 12px; top: 50%;
        width: 6px; height: 6px; margin-top: -3px; border-radius: 50%;
        background: #e5581e; opacity: 0; transform: scale(0);
        transition: opacity .18s ease, transform .18s ease;
    }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu .dropdown-item:hover,
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu .dropdown-item:focus {
        padding-left: 28px !important;
        background: #f5f7fb !important;
        color: #012d70 !important;
    }
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu .dropdown-item:hover::before,
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .dropdown-menu .dropdown-item:focus::before { opacity: 1; transform: scale(1); }
    /* Free Listing / My Business: same plain link + underline as Contact */
    body.home-page .header-area .bottom-header .navbar .navbar-nav > .nav-item > .nav-link:has(> .fa-arrow-trend-up, > .fa-briefcase) i { color: inherit; }
}
</style>
<style id="hx-btn-hover">
/* Desktop: buttons hover to the orange accent used across the page */
@media (min-width: 992px) {
    body.home-page .sb-home-hero-cta,
    body.home-page .hx-cta2,
    body.home-page .featured-business-listings .btn-call,
    body.home-page .featured-business-listings a.btn-whatsapp,
    body.home-page .featured-business-listings .slick-arrow,
    body.home-page .hx-arrow,
    body.home-page .trend-nav,
    body.home-page .header-area a.nav-btn,
    body.home-page a.home-search-listing-cta,
    body.home-page a.download-btn,
    body.home-page .arrow-btn {
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease !important;
    }
    body.home-page .sb-home-hero-cta:hover, body.home-page .sb-home-hero-cta:focus-visible,
    body.home-page .hx-cta2:hover, body.home-page .hx-cta2:focus-visible,
    body.home-page .featured-business-listings .btn-call:hover,
    body.home-page .featured-business-listings .slick-arrow:hover,
    body.home-page .hx-arrow:hover,
    body.home-page .header-area .bottom-header .navbar ul li a.nav-btn:not(.profile-dashboard-link):hover,
    body.home-page .header-area .bottom-header .navbar ul li a.nav-btn:not(.profile-dashboard-link):focus-visible,
    body.home-page #stickyScrollHeader .navbar-nav > .nav-item > .nav-link.nav-btn:not(.profile-dashboard-link):hover,
    body.home-page #stickyScrollHeader .navbar-nav > .nav-item > .nav-link.nav-btn:not(.profile-dashboard-link):focus-visible,
    body.home-page #stickyScrollHeader .add-business:hover,
    body.home-page #stickyScrollHeader .add-business:focus-visible,
    body.home-page a.home-search-listing-cta:hover, body.home-page a.home-search-listing-cta:focus-visible,
    body.home-page .banner-download-btn a.download-btn:hover, body.home-page .banner-download-btn a.download-btn:focus-visible,
    body.home-page .trend-nav:hover, body.home-page .trend-nav:focus-visible,
    body.home-page .popular-search-section .trend-nav:hover,
    body.home-page .trending-same-section .trend-nav:hover,
    body.home-page .top-location-section .trend-nav:hover,
    body.home-page .seasonal-same-section .trend-nav:hover,
    body.home-page .popular-same-section .trend-nav:hover,
    body.home-page .arrow-btn:hover {
        background: #e5581e !important; border-color: #e5581e !important; color: #fff !important;
        box-shadow: 0 12px 22px -12px rgba(229, 88, 30, .75) !important;
        text-decoration: none;
    }
    body.home-page .sb-home-hero-cta:hover, body.home-page .hx-cta2:hover,
    body.home-page .featured-business-listings .btn-call:hover,
    body.home-page a.home-search-listing-cta:hover,
    body.home-page .banner-download-btn a.download-btn:hover,
    body.home-page .header-area a.nav-btn:not(.profile-dashboard-link):hover { transform: translateY(-2px); }
    body.home-page .banner-download-btn a.download-btn:hover i { color: #fff !important; }
    body.home-page .featured-business-listings a.btn-whatsapp:not([href*="wa.me"]):hover {
        background: #fff4ee !important; border-color: #e5581e !important; color: #e5581e !important;
    }
}
</style>
<style id="hx-footer-main">
/* Main footer (preview, desktop): deep navy band with rounded top, white
   logo chip, orange-underlined headings, quiet links that slide on hover,
   contact icons in glass circles, darker copyright strip. Text unchanged. */
@media (min-width: 992px) {
    body.home-page .social-follow-section { padding: 26px 0 !important; border: 0 !important; background: #fff !important; }
    body.home-page .social-follow-section .follow-us-row { justify-content: center; gap: 18px; }
    body.home-page .social-follow-section .follow-title { margin: 0 !important; color: #101828 !important; font-size: 18px !important; font-weight: 700 !important; }
    body.home-page .social-follow-section .social-icons { gap: 12px; }
    body.home-page .social-follow-section .social-icon { margin: 0 !important; transition: transform .2s ease; }
    body.home-page .social-follow-section .social-icon img { width: 38px; height: 38px; }
    body.home-page .social-follow-section .social-icon:hover { transform: translateY(-4px); }

    body.home-page footer.footer-area {
        position: relative; overflow: hidden;
        margin-top: 8px;
        border-radius: 34px 34px 0 0;
        background:
            radial-gradient(520px 260px at 95% 0%, rgba(229, 88, 30, .10), transparent 70%),
            radial-gradient(600px 300px at 0% 100%, rgba(1, 45, 112, .08), transparent 70%),
            linear-gradient(180deg, #f3f6fd 0%, #f9fbff 55%, #fff8f2 100%) !important;
        color: #475467 !important;
        box-shadow: inset 0 1px 0 #e6ecf5;
    }
    body.home-page footer.footer-area::before {
        content: ""; position: absolute; inset: 0; pointer-events: none;
        background-image: radial-gradient(rgba(1, 45, 112, .08) 1px, transparent 1.2px);
        background-size: 20px 20px;
        -webkit-mask-image: linear-gradient(180deg, #000, transparent 70%);
        mask-image: linear-gradient(180deg, #000, transparent 70%);
    }
    body.home-page footer.footer-area > * { position: relative; }
    body.home-page footer.footer-area .just { padding-top: 64px !important; padding-bottom: 40px !important; }

    body.home-page footer.footer-area .footer-widget .site-logo {
        padding: 10px 14px !important; border-radius: 16px !important; background: #fff !important;
        box-shadow: 0 14px 30px -20px rgba(1, 45, 112, .45);
    }
    body.home-page footer.footer-area .footer-widget p { margin-top: 14px; color: #475467 !important; font-size: 14px; line-height: 1.75; }

    body.home-page footer.footer-area .footer-widget .widget-title { margin-bottom: 20px !important; padding: 0 !important; border: 0 !important; }
    body.home-page footer.footer-area .footer-widget .widget-title::before { display: none !important; }
    body.home-page footer.footer-area .footer-widget .widget-title h4 {
        position: relative; margin: 0 !important; padding-bottom: 12px;
        color: #012d70 !important; font-size: 18px !important; font-weight: 800 !important;
    }
    body.home-page footer.footer-area .footer-widget .widget-title h4::after {
        content: ""; position: absolute; left: 0; bottom: 0; width: 30px; height: 3px; border-radius: 3px;
        background: linear-gradient(90deg, #e5581e, #f59e0b);
    }
    body.home-page footer.footer-area .footer-widget .widget-title::after { display: none !important; }

    body.home-page footer.footer-area .footer-widget .widget-body ul li { margin-bottom: 4px !important; }
    body.home-page footer.footer-area .footer-widget .widget-body ul li a {
        display: inline-block; margin-left: 0 !important; padding: 4px 0 !important;
        background: transparent !important; border-radius: 0 !important; box-shadow: none !important;
        color: #475467 !important;
        font-size: 14px !important; font-weight: 500 !important; text-transform: none !important; letter-spacing: 0 !important;
        transition: color .2s ease, transform .2s ease;
    }
    body.home-page footer.footer-area .footer-widget .widget-body ul li a::first-letter { text-transform: uppercase; }
    body.home-page footer.footer-area .footer-widget .widget-body ul li a::before {
        content: "" !important; position: absolute; left: -14px !important; top: 50%;
        width: 6px; height: 6px; border-radius: 50%;
        background: #e5581e; opacity: 0; transform: translateY(-50%) scale(.4);
        transition: opacity .2s ease, transform .2s ease;
    }
    body.home-page footer.footer-area .footer-widget .widget-body ul li a:hover {
        padding: 4px 0 !important; background: transparent !important;
        color: #012d70 !important; transform: translateX(14px);
    }
    body.home-page footer.footer-area .footer-widget .widget-body ul li a:hover::before { opacity: 1; transform: translateY(-50%) scale(1); }

    body.home-page footer.footer-area .footer-contact-item { align-items: flex-start; gap: 14px; margin-bottom: 14px !important; }
    body.home-page footer.footer-area .footer-contact-item > i {
        flex: 0 0 40px; display: grid; place-items: center; width: 40px; height: 40px;
        border-radius: 50%; background: #fff;
        color: #e5581e !important; font-size: 20px !important;
        box-shadow: 0 8px 18px -12px rgba(1, 45, 112, .5);
        transition: background-color .2s ease, color .2s ease;
    }
    body.home-page footer.footer-area .footer-contact-item:hover > i { background: #e5581e; color: #fff !important; }
    body.home-page footer.footer-area .footer-contact-item a,
    body.home-page footer.footer-area .footer-contact-item span {
        align-self: center; padding: 0 !important; background: transparent !important;
        color: #344054 !important; font-size: 14.5px; line-height: 1.65;
    }
    body.home-page footer.footer-area .footer-contact-item a:hover { color: #012d70 !important; background: transparent !important; text-decoration: underline !important; text-underline-offset: 3px; }

    body.home-page footer.footer-area .footer-bottom {
        padding: 16px !important; border-top: 0 !important;
        background: #012d70 !important;
    }
    body.home-page footer.footer-area .footer-bottom p { margin: 0 !important; color: rgba(255, 255, 255, .8) !important; font-size: 13.5px; }
    body.home-page footer.footer-area .footer-bottom p a.sh { color: #fff !important; text-decoration-color: #f59e0b; }
}
</style>
@php
    $pcxCats = \App\Models\Category::where('status', 1)->get(['id', 'slug', 'image'])
        ->mapWithKeys(function ($c) {
            return [$c->id => ['s' => $c->slug, 'i' => $c->image ? asset('assets/images/category/' . $c->image) : '']];
        });
@endphp
<script>
(function () {
    if (!window.matchMedia('(min-width: 768px)').matches) return;
    var cats = @json($pcxCats);
    function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }
    function build() {
        var cols = window.matchMedia('(max-width: 1199px)').matches ? 2 : 3;
        document.querySelectorAll('#popularCategoriesWrapper .popular-subcat-box').forEach(function (box) {
            if (box.querySelector('.pcx-feature')) return;
            var links = box.querySelectorAll('a.popular-subcategory-link');
            if (!links.length) return;
            var c = cats[box.id.replace('popular-cat-', '')] || {};
            var tab = document.querySelector('.popular-cat-tab[data-tab="' + box.id + '"]');
            var name = tab ? tab.textContent.trim() : '';
            var href = c.s ? '/listings/' + c.s : links[0].href;
            try {
                var seg = new URL(links[0].href).pathname.split('/').filter(Boolean);
                if (seg.length === 2 && c.s) href = '/' + seg[0] + '/' + c.s;
            } catch (e) {}
            var f = el('a', 'pcx-feature'); f.href = href;
            if (c.i) {
                var ic = el('span', 'pcx-img'), im = new Image();
                im.src = c.i; im.alt = ''; im.loading = 'lazy'; ic.appendChild(im); f.appendChild(ic);
                var wm = new Image(); wm.src = c.i; wm.alt = ''; wm.className = 'pcx-wm'; wm.loading = 'lazy'; f.appendChild(wm);
            }
            f.appendChild(el('span', 'pcx-name', name));
            f.appendChild(el('span', 'pcx-meta', links.length + ' popular services'));
            f.appendChild(el('span', 'pcx-btn', 'Sab dekhein →'));
            box.classList.add('pcx');
            box.style.setProperty('--rows', Math.max(1, Math.ceil(links.length / cols)));
            box.insertBefore(f, box.firstChild);
        });
    }
    function arrowBtn(cls, label, d) {
        var b = el('button', 'pct-arr ' + cls); b.type = 'button'; b.setAttribute('aria-label', label);
        b.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
        return b;
    }
    function tabArrows() {
        document.querySelectorAll('#popularCategoriesWrapper .popular-category-tabs').forEach(function (tabs) {
            if (tabs.parentNode.classList.contains('pct-wrap')) return;
            var wrap = el('div', 'pct-wrap');
            tabs.parentNode.insertBefore(wrap, tabs);
            wrap.appendChild(tabs);
            var prev = arrowBtn('pct-prev', 'Pichhli categories', 'M15 18l-6-6 6-6');
            var next = arrowBtn('pct-next', 'Agli categories', 'M9 6l6 6-6 6');
            wrap.appendChild(prev); wrap.appendChild(next);
            function update() {
                var max = tabs.scrollWidth - tabs.clientWidth;
                prev.hidden = tabs.scrollLeft <= 2;
                next.hidden = tabs.scrollLeft >= max - 2;
            }
            prev.addEventListener('click', function () { tabs.scrollBy({ left: -tabs.clientWidth * 0.7 }); });
            next.addEventListener('click', function () { tabs.scrollBy({ left: tabs.clientWidth * 0.7 }); });
            tabs.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        });
    }
    function start() {
        build();
        tabArrows();
        var wrap = document.getElementById('popularCategoriesWrapper');
        if (wrap) new MutationObserver(function () { build(); tabArrows(); }).observe(wrap, { childList: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
</script>
@php
    $pcImgs = \App\Models\Location::where('is_popular', 1)->whereNotNull('image')->pluck('image', 'id')
        ->map(function ($img) { return asset('assets/images/location/' . $img); });
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.matchMedia('(min-width: 768px)').matches) return;
    var imgs = @json($pcImgs);
    document.querySelectorAll('.popular-city-list a.popular-city-link').forEach(function (a) {
        var src = imgs[a.dataset.locationId];
        if (!src || a.querySelector('.pc-ic')) return;
        var ring = document.createElement('span');
        ring.className = 'pc-ic';
        var img = new Image();
        img.src = src; img.alt = ''; img.loading = 'lazy'; img.decoding = 'async';
        ring.appendChild(img);
        a.insertBefore(ring, a.firstChild);
    });
});
</script>
@endsection
