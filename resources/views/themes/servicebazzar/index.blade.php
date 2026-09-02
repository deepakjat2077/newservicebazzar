@extends($themeView.'layouts.site')
@section('content')
@section('title','Find Businesses Near You')
@include($themeView.'includes.rich-content')
<!-- Banner Section -->
@php
    $currentLocationName = request('location_name');
    $currentLatitude = request('latitude');
    $currentLongitude = request('longitude');
@endphp

@php
    $usedCategoryIds = [];
@endphp
<style>
    
   @media (max-width: 991px) {

    .banner-section {
        padding-top: 15px !important;
     
        margin-top: 0 !important;
        min-height: auto !important;
    }

    .banner-section .row {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .banner-section .custom-right,
    .banner-section .col-md-8 {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    .banner-section .title-area {
        display: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .banner-section .search-area {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    .banner-section form {
        margin-top: 0 !important;
    }
}
@media (max-width: 767px){

    .banner-section .search-area{
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .banner-section form{
        width: 100%;
    }

    .banner-section .row{
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .banner-section .col-md-4,
    .banner-section .col-md-6{
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-bottom: 10px;
    }
}
</style>
<style>
    .banner-section .title-area h1{
    font-size: 26px ;
        font-weight: 600;
        margin-bottom: .5rem;
}
@media (max-width: 767px){
    #mobileHomeLocationBox{
        display: none !important;
    }

    #mobileHomeLocationBox.show{
        display: block !important;
    }
}

@media (max-width: 767px){

    #mobileHomeLocationBox{
        display: block !important;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: all .3s ease;
    }

    body.show-mobile-location #mobileHomeLocationBox{
        max-height: 80px;
        opacity: 1;
        margin-bottom: 10px;
    }
}
.hero-title{
    overflow: hidden;
}

.text-slider{
    position: relative;
    display: inline-block;
    height: 32px;
    overflow: hidden;
    vertical-align: bottom;
}

.text-slider span{
    display: block;
    color: #0076d7;
    line-height: 32px;
    animation: textSlide 4s infinite;
}

@keyframes textSlide{

    0%{
        transform: translate3d(0px, 0%, 0px);
    }

    45%{
        transform: translate3d(0px, 0%, 0px);
    }

    50%{
        transform: translate3d(0px, -100%, 0px);
    }

    95%{
        transform: translate3d(0px, -100%, 0px);
    }

    100%{
        transform: translate3d(0px, 0%, 0px);
    }
}
.text-slider span{
    display: block;
    line-height: 32px;
    animation: textSlide 4s infinite;
}

.text-slider span strong{
    color: #000;
    font-weight: 600;
}

.text-slider span small{
    color: #0076d7;
    font-weight: 600;
    
}
/* Chrome, Edge, Safari */

/* remove custom style issue */
.input_search{
    appearance: none !important;
    -webkit-appearance: none !important;
     padding-right: 95px !important;

}



</style>

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
            <small>Near Your Location</small>
        </span>

        <span>
            <strong>Verified Businesses</strong>
            <small>Across India</small>
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
<style>
 
 .input_location_box:after {
    position: absolute;
    content: "";
    background: -webkit-linear-gradient(left, rgba(255, 255, 255, 0) -4%, #f5f5f5 67%);
    background: -moz-linear-gradient(left,rgba(255,255,255,0)-4%,#f5f5f5 67%);
    background: -o-linear-gradient(left,rgba(255,255,255,0)-4%,#f5f5f5 67%);
    background: linear-gradient(to right, rgba(255, 255, 255, 0) -4%, #f5f5f5 67%);
    right: 0;
    top: 0;
    width: 40px;
    height: 100%;
    z-index: 1;
}
  .input_location_box{
    width: 100%;
    height: 50px;
    border: solid .5px #111;
    background-color: #f5f5f5;
    border-radius: 4px;
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
}
   .input_location_box:before {
    background-image: url(https://akam.cdn.jdmagicbox.com/images/icontent/newwap/web2022/location_icon.svg);
    width: 37px;
    height: 100%;
    position: absolute;
    left: 0px;
    top: 0px;
    content: "";
    background-repeat: no-repeat;
    background-position: center;
}
.input_location{
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    background: transparent;
    padding: 0 15px 0 42px;
    font-size: 15px;
}

    .input_search_box {
    width: 100%;
    
    height: 50px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    -webkit-box-shadow: 0 2px 4px 0 rgba(0, 0, 0, .15);
    -moz-box-shadow: 0 2px 4px 0 rgba(0,0,0,.15);
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, .15);
    border: solid .5px #111;
    background-color: #fff;
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    position: relative;
    -webkit-transition: all .15s;
    -moz-transition: all.15s;
    -o-transition: all.15s;
    transition: all .15s;
}
   
.position-relative {
    position: relative;
}

.location-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 16px;
    pointer-events: none;
}


</style>

<style>
    .location-dropdown {
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        width: 100%;
        display: none;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 0 0 8px 8px;
        max-height: 220px;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .location-dropdown::-webkit-scrollbar {
        display: none;
    }

    .location-dropdown.show {
        display: block;
    }

    .location-dropdown li {
        list-style: none;
    }

    .location-dropdown .dropdown-item {
        display: block;
        width: 100%;
        padding: 10px 14px;
        cursor: pointer;
        text-decoration: none;
        color: #222;
        background: #fff;
        border: 0;
    }

    .location-dropdown .dropdown-item:hover {
        background: #f5f5f5;
    }

    .detect-location-item {
        font-weight: 600;
        border-bottom: 1px solid #eee;
    }
    .banner-section {
    position: relative;
}

/* 👉 Button ko section ke last right me bhejna */
.banner-download-btn {
    position: absolute;
    right: 80px;
    bottom: 20px;
}

/* button design */
.download-btn {
    border: 1px solid #000;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    color: #000;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
}

/* mobile hide */
@media (max-width: 767px){
    .desktop-only{
        display: none !important;
    }
}
</style>
<style>
.banner-section {
    position: relative;
    z-index: 20;
    overflow: visible !important;
    transition: padding-bottom 0.2s ease;
}

.banner-section.dropdown-open {
    padding-bottom: 220px;
}

.search-area,
.search-area form,
.search-area .row,
.search-area .col-md-4,
.search-area .col-md-6,
.search-area .dropdown {
    position: relative;
    overflow: visible !important;
}

/* HOME DROPDOWN */

#homeCategorySuggestions {
    position: fixed !important;
    display: none;
    z-index: 999999 !important;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    max-height: 70vh;
    overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    padding: 0;
    margin: 0;
}


#homeLocationSuggestions{
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    width: 100%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    display: none;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999;
}#homeLocationSuggestions{
    width: 480px !important;
    max-height: 430px !important;
    padding: 10px 0 !important;
}

.location-section-title{
    font-size: 10px;
    color: #6b7280;
    text-transform: uppercase;
    padding: 12px 20px 6px;
    font-weight: 600;
}

.clear-recent-locations{
    float: right;
    color: #0076d7;
    font-size: 12px;
    cursor: pointer;
    text-transform: none;
    font-weight: 400;
}

#homeLocationSuggestions .dropdown-item{
    padding-left: 15px;
    font-size: 14px;
    font-weight:400;
    line-height: 1.3;
    letter-spacing: .1px;
    color: #111;
}

.detect-location-item{
    color: #0076d7 !important;
    font-weight: 500;
    border-bottom: 1px solid #eee;
    margin-bottom: 8px;
}

@media(max-width:767px){
    #homeLocationSuggestions{
        width: 100% !important;
    }
}
#homeLocationSuggestions.show,
#homeCategorySuggestions.show {
    display: block;
}


#homeLocationSuggestions::-webkit-scrollbar,
#homeCategorySuggestions::-webkit-scrollbar {
    width: 8px;
}
#homeLocationSuggestions::-webkit-scrollbar-thumb,
#homeCategorySuggestions::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}#homeLocationSuggestions::-webkit-scrollbar-track,
#homeCategorySuggestions::-webkit-scrollbar-track {
    background: #f1f1f1;
}
#homeLocationSuggestions,
#homeCategorySuggestions {
    scrollbar-width: thin;
}
#homeLocationSuggestions.show,
#homeCategorySuggestions.show {
    display: block;
}

#homeLocationSuggestions li,
#homeCategorySuggestions li {
    list-style: none;
}


#homeCategorySuggestions .dropdown-item {
    display: block;
    padding: 10px 14px;
    
}
.size{
       font-size:40px; color:red;
    }
    .search-row-custom{
    display: flex;
    align-items: center;
    gap: 12px;
}

.location-col-custom{
    width: 280px;
    flex: 0 0 280px;
}

.search-col-custom{
    width: 430px;
    flex: 0 0 430px;
}

@media (max-width: 767px){
    .search-row-custom{
        display: block;
    }

    .location-col-custom,
    .search-col-custom{
        width: 100%;
        flex: 0 0 100%;
        margin-bottom: 10px;
    }
}
#homeLocationSuggestions{
    max-height: 400px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

</style>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const locationInput = document.getElementById("homeLocationInput");
    const locationDropdown = document.getElementById("homeLocationSuggestions");
    const latitudeInput = document.getElementById("homeSearchLatitude");
    const longitudeInput = document.getElementById("homeSearchLongitude");
    const locationIdInput = document.getElementById("homeSearchLocationId");

    if (!locationInput || !locationDropdown) return;
    let popularCategoriesRequestNumber = 0;
    function loadPopularCategoriesByLocation(
    city,
    latitude = "",
    longitude = "",
    locationId = ""
) {
    city = String(city || "").trim();

    if (!city) {
        return;
    }
    const currentRequestNumber =
    ++popularCategoriesRequestNumber;

    fetch("{{ route('ajax.popular.categories') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "text/html",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
            city: city,
            latitude: latitude || "",
            longitude: longitude || "",
            location_id: locationId || ""
        })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error("Popular categories request failed");
        }

        return res.text();
    })
   .then(html => {

    
    if (
        currentRequestNumber !==
        popularCategoriesRequestNumber
    ) {
        return;
    }

    const wrapper =
        document.getElementById("popularCategoriesWrapper");

    if (wrapper) {
        wrapper.innerHTML = html;
        // The mobile accordion buttons just replaced above need their
        // click handlers (re)bound — see popular-accordion.js.
        document.dispatchEvent(new CustomEvent("popularCategoriesRefreshed", {
            detail: { target: wrapper }
        }));
    }
})
    .catch(error => {
        console.error("Popular categories error:", error);
    });
}
function restoreDetectedLocationOnHome() {

    const pathParts = window.location.pathname
        .split("/")
        .filter(Boolean);

    const isRootHome = pathParts.length === 0;

    /*
    |--------------------------------------------------------------------------
    | ROOT HOME: hamesha detected location use hogi
    |--------------------------------------------------------------------------
    */
    if (isRootHome) {

        const detectedCity =
            localStorage.getItem("jf_detected_city") ||
            localStorage.getItem("jf_city") ||
            "";

        const detectedLat =
            localStorage.getItem("jf_lat") || "";

        const detectedLng =
            localStorage.getItem("jf_lng") || "";

        /*
         * Popular City ki purani selected values root home par use nahi hongi.
         */
        localStorage.removeItem("jf_selected_location_name");
        localStorage.removeItem("jf_selected_lat");
        localStorage.removeItem("jf_selected_lng");
        localStorage.removeItem("jf_selected_location_id");
        localStorage.removeItem("jf_from_popular_city");

        if (!detectedCity) {
            return;
        }

        locationInput.value = detectedCity;
        locationInput.dataset.selectedLocation = "1";

        /*
         * Detected city ke saath Delhi ya kisi selected city ki ID
         * bilkul pass nahi karni.
         */
        locationInput.dataset.locationId = "";

        if (locationIdInput) {
            locationIdInput.value = "";
        }

        if (latitudeInput) {
            latitudeInput.value = detectedLat;
        }

        if (longitudeInput) {
            longitudeInput.value = detectedLng;
        }

        updateCategoryLinks(detectedCity);

        loadPopularCategoriesByLocation(
            detectedCity,
            detectedLat,
            detectedLng,
            ""
        );

        closeLocationDropdown();
        locationInput.blur();

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | CITY URL: /delhi, /mumbai etc.
    |--------------------------------------------------------------------------
    */
    const cityFromUrl = pathParts.length === 1
        ? pathParts[0].replace(/-/g, " ")
        : "";

    if (cityFromUrl) {
        locationInput.value = cityFromUrl;
        locationInput.dataset.selectedLocation = "1";

        closeLocationDropdown();
        locationInput.blur();
    }
}
restoreDetectedLocationOnHome();
   locationInput.dataset.selectedLocation = locationInput.value.trim() !== "" ? "1" : "0";
    let previousLocationValue = locationInput.value.trim();
    let previousLocationId = locationIdInput ? locationIdInput.value : "";
    let previousLatitude = latitudeInput ? latitudeInput.value : "";
    let previousLongitude = longitudeInput ? longitudeInput.value : "";

    function closeLocationDropdown() {
        locationDropdown.classList.remove("show");
    }

    function openLocationDropdown() {
        locationDropdown.classList.add("show");
    }

    function getRecentLocations() {
        return JSON.parse(localStorage.getItem("service_bazzar_recent_locations") || "[]");
    }

    function saveRecentLocation(location) {
        let recent = getRecentLocations();
        recent = recent.filter(item => item.value !== location.value);
        recent.unshift(location);
        recent = recent.slice(0, 5);
        localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(recent));
    }

    function renderLocationItem(item) {
        let name = "";
        if (typeof item === "string") {
            name = item;
        } else if (item && typeof item === "object") {
            name = item.name || item.location_name || item.city || item.area || 
                   item.address || item.display_name || item.value || "";
        }
        name = String(name).trim();
        if (!name || name === "[object Object]") return null;

        let parentName = item.parent || "";

        const div = document.createElement("div");
        div.className = "dropdown-item";
        div.dataset.value = item.value || (parentName ? name + ", " + parentName : name);
        div.dataset.lat = item.latitude || item.lat || "";
        div.dataset.lng = item.longitude || item.lng || "";
        const finalLocationId = item.location_id || item.id || item.locationId || "";
        div.setAttribute("data-location-id", finalLocationId);
        div.dataset.locationId = finalLocationId;
        div.innerHTML = parentName ? `${name}, ${parentName}` : name;
        return div;
    }

    function renderLocations(list) {
        locationDropdown.innerHTML = "";

        // 1. Detect Location with spinner
        const detectDiv = document.createElement("div");
        detectDiv.className = "dropdown-item detect-location-item";
        detectDiv.innerHTML = `
            <i class="las la-crosshairs"></i> 
            Detect Location 
            <span class="detect-loading-spinner" style="display:none; margin-left:8px; color:#0076d7;">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        `;
        locationDropdown.appendChild(detectDiv);

        // 2. Recent Locations
        if (!locationInput.value.trim()) {
            const recentLocations = getRecentLocations();
            if (recentLocations.length > 0) {
                const recentTitle = document.createElement("div");
                recentTitle.className = "location-section-title";
                recentTitle.innerHTML = `Recent Locations <span class="clear-recent-locations">Clear All</span>`;
                locationDropdown.appendChild(recentTitle);
                recentLocations.forEach(item => {
                    const div = renderLocationItem(item);
                    if (div) locationDropdown.appendChild(div);
                });
            }
        }

        // 3. Trending Areas
        if (Array.isArray(list) && list.length > 0) {
            if (!locationInput.value.trim()) {
                const trendingTitle = document.createElement("div");
                trendingTitle.className = "location-section-title";
                trendingTitle.innerHTML = "Trending Areas";
                locationDropdown.appendChild(trendingTitle);
            }
            let usedLocations = [];
            list.forEach(item => {
                const div = renderLocationItem(item);
                if (!div) return;
                let uniqueKey = div.dataset.value.toLowerCase();
                if (usedLocations.includes(uniqueKey)) return;
                usedLocations.push(uniqueKey);
                locationDropdown.appendChild(div);
            });
        }

        openLocationDropdown();
    }

    function fetchLocationSuggestions(search = "") {
        const lat = latitudeInput ? latitudeInput.value : "";
        const lng = longitudeInput ? longitudeInput.value : "";
        const finalLat = lat || localStorage.getItem('jf_selected_lat') || localStorage.getItem('jf_lat') || "";
        const finalLng = lng || localStorage.getItem('jf_selected_lng') || localStorage.getItem('jf_lng') || "";

        const url = `{{ route('nearby.location.suggestions') }}?q=${encodeURIComponent(search)}&latitude=${encodeURIComponent(finalLat)}&longitude=${encodeURIComponent(finalLng)}&_=${Date.now()}`;

        fetch(url, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => renderLocations(data))
        .catch(err => {
            console.error("Location suggestion error:", err);
            locationDropdown.innerHTML = "";
            openLocationDropdown();
        });
    }

    let timer;

    locationInput.addEventListener("input", function () {
        const value = this.value.trim();
        this.dataset.selectedLocation = "0";
        clearTimeout(timer);
        if (value.length < 1) {
            const currentCity = localStorage.getItem("jf_detected_city") || "";
            fetchLocationSuggestions(currentCity);
            return;
        }
        timer = setTimeout(() => {
            fetchLocationSuggestions(value);
        }, 250);
    });

    locationInput.addEventListener("focus", function () {
        previousLocationValue = this.value.trim();
        previousLocationId = locationIdInput ? locationIdInput.value : "";
        previousLatitude = latitudeInput ? latitudeInput.value : "";
        previousLongitude = longitudeInput ? longitudeInput.value : "";

        this.value = "";
        this.dataset.selectedLocation = "0";

        const currentCity = localStorage.getItem("jf_detected_city") || "";
        fetchLocationSuggestions(currentCity);
    });

    // ============================================================
    // ⭐ DETECT LOCATION - COMPLETE FIX ⭐
    // ============================================================
    locationDropdown.addEventListener("mousedown", function (e) {

        if (e.target.classList.contains("clear-recent-locations")) {
            e.preventDefault();
            localStorage.removeItem("service_bazzar_recent_locations");
            const currentCity = localStorage.getItem("jf_detected_city") || localStorage.getItem("jf_selected_location_name") || "";
            fetchLocationSuggestions(currentCity);
            return;
        }

        const item = e.target.closest(".dropdown-item");
        if (!item) return;
        e.preventDefault();

      if (item.classList.contains("detect-location-item")) {

    const spinner = item.querySelector(".detect-loading-spinner");
    if (spinner) spinner.style.display = "inline-block";
    const showDetectPopup = function(message, blocked = false) {
        if (typeof window.showLocationPermissionPopup === "function") {
            window.showLocationPermissionPopup({
                message: message,
                blocked: blocked,
                onRetry: function () {
                    item.dispatchEvent(new MouseEvent("mousedown", {
                        bubbles: true,
                        cancelable: true
                    }));
                }
            });
        }
    };

    // dropdown abhi band mat karo, spinner dikhne do
    openLocationDropdown();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                if (latitudeInput) latitudeInput.value = lat;
                if (longitudeInput) longitudeInput.value = lng;

                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat=${lat}&lon=${lng}`, {
                    headers: { "Accept": "application/json" }
                })
                .then(res => res.json())
                .then(data => {
                    const address = data.address || {};
                    const city = window.normalizeEnglishLocationName
                        ? window.normalizeEnglishLocationName(address.city || address.town || address.village || address.state_district || address.state || '')
                        : (address.city || address.town || address.village || address.state_district || address.state || '');

                    if (city && locationInput) {
                        locationInput.value = city;
                        locationInput.dataset.selectedLocation = "1";

                    localStorage.setItem('jf_city', city);
localStorage.setItem('jf_detected_city', city);
localStorage.setItem('jf_lat', lat);
localStorage.setItem('jf_lng', lng);

fetch("{{ route('save.user.location') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        "X-Requested-With": "XMLHttpRequest"
    },
    body: JSON.stringify({
        city: city,
        latitude: lat,
        longitude: lng
    })
});
loadPopularCategoriesByLocation(city, lat, lng, "");
localStorage.removeItem('jf_selected_location_name');

                        if (spinner) spinner.style.display = "none";

                    
                        closeLocationDropdown();
                        locationInput.blur();
document.activeElement.blur();


                        const categoryInput = document.getElementById("homeCategorySearch");
                        if (categoryInput) {
                            categoryInput.dispatchEvent(new Event("input"));
                        }
                    }
                })
                .catch(() => {
                    if (spinner) spinner.style.display = "none";
                    closeLocationDropdown();
                });
            },
            function (error) {
                if (spinner) spinner.style.display = "none";
                showDetectPopup(
                    "Unable to detect location. Please allow location access.",
                    error && error.code === error.PERMISSION_DENIED
                );
                closeLocationDropdown();
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        if (spinner) spinner.style.display = "none";
        showDetectPopup("Geolocation is not supported by this browser.");
        closeLocationDropdown();
    }

    return;
}

        // ---------- NORMAL LOCATION SELECTION ----------
        const selectedLocationId = item.getAttribute("data-location-id") || "";
        locationInput.value = item.dataset.value || "";
        updateCategoryLinks(item.dataset.value || "");
        saveRecentLocation({
            value: item.dataset.value || "",
            latitude: item.dataset.lat || "",
            longitude: item.dataset.lng || "",
            location_id: selectedLocationId
        });
        locationInput.dataset.selectedLocation = "1";
        locationInput.dataset.locationId = selectedLocationId;
        previousLocationValue = locationInput.value.trim();
        previousLocationId = selectedLocationId;
        previousLatitude = item.dataset.lat || "";
        previousLongitude = item.dataset.lng || "";

        if (locationIdInput) locationIdInput.value = selectedLocationId;
        if (latitudeInput) latitudeInput.value = item.dataset.lat || "";
        if (longitudeInput) longitudeInput.value = item.dataset.lng || "";
fetch("{{ route('save.user.location') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        "X-Requested-With": "XMLHttpRequest"
    },
    body: JSON.stringify({
        city: locationInput.value,
        latitude: item.dataset.lat || 0,
        longitude: item.dataset.lng || 0
    })
});

loadPopularCategoriesByLocation(
    locationInput.value,
    item.dataset.lat || "",
    item.dataset.lng || "",
    selectedLocationId
);

closeLocationDropdown();
locationInput.blur();
document.activeElement.blur();

}); // locationDropdown mousedown event yahan close hoga

locationInput.addEventListener("blur", function () {

   
        setTimeout(() => {
            if (this.value.trim() === "" && previousLocationValue !== "") {
                this.value = previousLocationValue;
                this.dataset.selectedLocation = "1";
                this.dataset.locationId = previousLocationId;
                if (locationIdInput) locationIdInput.value = previousLocationId;
                if (latitudeInput) latitudeInput.value = previousLatitude;
                if (longitudeInput) longitudeInput.value = previousLongitude;
            }
            closeLocationDropdown();
        }, 200);
    });

    let homeLocationIndex = -1;

locationInput.addEventListener("input", function () {
    homeLocationIndex = -1;
});

locationInput.addEventListener("keydown", function(e){

    const items = locationDropdown.querySelectorAll(".dropdown-item:not(.detect-location-item)");
    if(!items.length) return;

    if(e.key === "ArrowDown"){
        e.preventDefault();
        homeLocationIndex++;
        if(homeLocationIndex >= items.length) homeLocationIndex = 0;
    }
    else if(e.key === "ArrowUp"){
        e.preventDefault();
        homeLocationIndex--;
        if(homeLocationIndex < 0) homeLocationIndex = items.length - 1;
    }
    else if(e.key === "Enter" && homeLocationIndex > -1){
        e.preventDefault();
        items[homeLocationIndex].dispatchEvent(
            new MouseEvent("mousedown", {
                bubbles: true,
                cancelable: true
            })
        );
        return;
    }else{
        return;
    }

    items.forEach(item => item.classList.remove("active"));

    const activeItem = items[homeLocationIndex];
    activeItem.classList.add("active");

    locationInput.value =
        activeItem.dataset.value ||
        activeItem.textContent.trim();

    activeItem.scrollIntoView({ block:"nearest" });
});



    document.addEventListener("mousedown", function (e) {
        if (!locationInput.contains(e.target) && !locationDropdown.contains(e.target)) {
            closeLocationDropdown();
        }
    });

});
</script>
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

        {{-- If JavaScript is disabled, the input field and submit button remain
             functional: the user types text and submits. The suggestions dropdown
             is gone, but form submission still works. Explicit fallback via noscript
             is shown below. --}}
        <noscript>
            {{-- When JavaScript is disabled, the text input above is fully functional
                 and form submission works. A server-side category dropdown is shown as
                 an extra convenience, but is NOT the primary fallback — the plain text
                 input and form submission are. The dropdown submits the form directly
                 (no JS required) by selecting a category and clicking a submit button. --}}
            <div style="margin-top: 8px;">
                <div style="display: flex; gap: 4px; align-items: center;">
                    {{-- Dropdown of top 20 active categories. When selected, this
                         form submits directly (standard HTML form behavior, no JS). --}}
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


<style>
.custom-search-wrap{
    position: relative;
}

/* Browser default cross hide */


/* Custom clear icon */
.custom-clear-btn{
    color: #5c79b5;
    position: absolute;
    right: 70px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.custom-clear-btn i{
    font-size: 15px;
}
.search_buttonh{
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;
    margin: 0;
    line-height: 1;
}
.search_buttonh i{
    font-size: 16px;
    display: block;
    line-height: 1;
}

.voice-search-btn{
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    color: #5c79b5;
    cursor: pointer;
}


.btnbox_text{
    position: absolute;
    bottom: -38px;
    right: -40px;
    background: #6b6b6b;
    color: #fff;
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 20px;
    white-space: nowrap;
    display: none;
    z-index: 99;
}

.btnbox_text::before{
    content: "";
    position: absolute;
    top: -5px;
    right: 48px;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-bottom: 5px solid #6b6b6b;
}

.search_btnbox:hover .btnbox_text{
    display: block;
}
     .btnbox_text {
    display: none;
}

.search_buttonh,
.search_button{
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    padding: 0;
}

    .search_button {
    width: 35px;
    height: 35px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    border: none;
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    cursor: pointer;
    
} 
.search_button {
  
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    border: none;
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    cursor: pointer;
    position: absolute;
    top: 4px;
    right: 4px;
   
}


.search_white_icon {
    background-image: url(https://akam.cdn.jdmagicbox.com/images/icontent/newwap/web2022/search_white_icon.svg);
    width: 16px;
    height: 16px;
}
.jdicon {
    background-repeat: no-repeat;
    -webkit-background-size: contain;
    -moz-background-size: contain;
    -o-background-size: contain;
    background-size: contain;
    background-position: center;
    -webkit-box-flex: 0;
    -webkit-flex: none;
    -moz-box-flex: 0;
    -ms-flex: none;
    flex: none;
}
.jdicon {
    vertical-align: middle;
}
.jdicon {
    display: inline-block;
}
    .input_search {
    width: 100%;
    height: 100%;
    border: none;
    background: transparent;
    padding: 0px 45px 0 15px;
    overflow: hidden;
    white-space: nowrap;
    -o-text-overflow: ellipsis;
    text-overflow: ellipsis;
}
.position-relative {
    position: relative;
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 15px;
    pointer-events: none;
}

#liveSearch {
    padding-right: 40px; /* icon ke liye space */
    height: 48px;
}

@media (max-width:767px){
    

    body.header-fixed{
        padding-top:45px !important;
    }

    #stickyScrollHeader{
        height:45px !important;
    }

   
}
@media (max-width:767px){

    .banner-section,
    .banner-section .search-area,
    #homeSearchOriginalPlace,
    #homeSearchForm,
    #homeCategorySearch,
    #mobileHomeLocationBox{
        display:none;
        visibility:hidden;
        height:0;
        overflow:hidden;
        margin:0;
        padding:0;
    }

    .custom-banner-section{
        display:block;
        margin-top:0;
        padding-top:10px;
    }
}


</style>
<style>
#homeCategorySuggestions{
    padding: 10px 0 !important;
}

#homeCategorySuggestions .category-suggestion-item{
    display: flex !important;
    align-items: center;
    gap: 12px;
    padding: 10px 18px !important;
    cursor: pointer;
    background: #fff;
}

#homeCategorySuggestions .category-suggestion-item:hover{
    background: #f5f5f5;
}

.category-suggestion-icon{
    width: 30px;
    height: 30px;
    min-width: 30px;
    border-radius: 5px;
    background: #8d949d;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-suggestion-icon img{
  width: 100%;
    height: 100%;
    object-fit: cover;
}




.category-suggestion-type{
  font-size: 12px;
    color: #777;
    margin-top: 2px;
}

.clear-recent-categories{
    float: right;
    color: #0076d7;
    font-size: 12px;
    cursor: pointer;
    text-transform: none;
    font-weight: 400;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("homeCategorySearch");
    const dropdown = document.getElementById("homeCategorySuggestions");
    const locationInput = document.getElementById("homeLocationInput");

    if (!input || !dropdown) return;

    if (locationInput && locationInput.value.trim() !== "") {
        locationInput.dataset.selectedLocation = "1";
    }

    let categoryTimer;
    let lastCategorySearch = "";
    let lastGoodCategories = [];
    let lastGoodListings = [];
    let homeCategoryIndex = -1;

    input.dataset.selectedCategory = "0";
    input.dataset.selectedUrl = "";

    function closeCategoryDropdown() {
        dropdown.classList.remove("show");
        dropdown.innerHTML = "";
    }

    function openCategoryDropdown() {
        dropdown.classList.add("show");
    }

    function slugify(text) {
        return String(text || "")
            .toLowerCase()
            .trim()
            .replace(/,/g, "")
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9-]/g, "")
            .replace(/-+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    function getSelectedLocation() {
        return locationInput && locationInput.value.trim() !== ""
            ? locationInput.value.trim()
            : "";
    }

    function getRecentCategories() {
        return JSON.parse(localStorage.getItem("service_bazzar_recent_categories") || "[]");
    }

    function saveRecentCategory(category) {
        let recent = getRecentCategories();
        recent = recent.filter(item => item.value !== category.value);
        recent.unshift(category);
        recent = recent.slice(0, 5);
        localStorage.setItem("service_bazzar_recent_categories", JSON.stringify(recent));
    }

    function renderSectionTitle(title, clearClass = "") {
        const div = document.createElement("div");
        div.className = "location-section-title";
        div.innerHTML = clearClass
            ? `${title} <span class="${clearClass}">Clear All</span>`
            : title;
        return div;
    }

    function renderNotFoundItem() {
        const li = document.createElement("li");
        li.innerHTML = '<span class="dropdown-item" style="color:#888; cursor:default; justify-content:center;">Not found</span>';
        return li;
    }

    function renderCategoryItem(item, iconType = "search") {
        if (!item || !item.name) return null;

        const li = document.createElement("li");
        const a = document.createElement("a");

        a.href = "javascript:void(0)";
        a.className = "dropdown-item category-suggestion-item";
        a.dataset.value = item.name;
        a.dataset.url = item.url || "";
        a.dataset.type = "category";

        const iconHtml = iconType === "trending"
            ? `<span class="category-trending-icon"><img src="{{ asset('assets/images/ffffjndjs.svg') }}" alt="Trending indicator"></span>`
            : `<span class="category-search-icon"><i class="fas fa-search"></i></span>`;

        a.innerHTML = `
            ${iconHtml}
            <span class="category-suggestion-content">
                <span class="category-suggestion-name">${item.name}</span>
                <span class="category-suggestion-type">Category</span>
            </span>
        `;

        li.appendChild(a);
        return li;
    }

   function renderListingItem(item) {
    if (!item || !item.name) return null;

    const image = item.image || item.thumb || item.logo || "{{ asset('assets/images/default.jpg') }}";
    const rating = item.rating || "";
    const reviews = item.reviews || "";
    const area = item.area || "";
    const distance = item.distance || "";

    const li = document.createElement("li");
    const a = document.createElement("a");

    a.href = "javascript:void(0)";
    a.className = "dropdown-item category-listing-item";

    a.dataset.value = item.name;
    a.dataset.url = item.url || "";
    a.dataset.type = "listing";
    a.dataset.image = image;
    a.dataset.area = area;
    a.dataset.rating = rating;
    a.dataset.reviews = reviews;

    let meta = "";

    if (rating) {
        meta += `<span class="listing-rating">${rating} ★</span>`;
    }

    if (reviews) {
        meta += ` <span class="listing-review">(${reviews})</span>`;
    }

    if (area) {
        meta += ` <span class="listing-dot">•</span> <span>${area}</span>`;
    }

    if (distance) {
        meta += ` <span class="listing-dot">•</span> <span>${distance}</span>`;
    }

    a.innerHTML = `
        <span class="listing-suggestion-img">
            <img src="${image}" alt="${item.name}">
        </span>

        <span class="category-suggestion-content">
            <span class="category-suggestion-name">${item.name}</span>
            <span class="category-suggestion-type">
                ${meta || "Business Listing"}
            </span>
        </span>
    `;

    li.appendChild(a);
    return li;
}

  function cleanText(text){
    return String(text || "")
        .toLowerCase()
        .replace(/\s+/g, " ")
        .trim();
}

function normalizeResponse(data) {
    if (Array.isArray(data)) {
        return {
            categories: data.filter(item => item.type !== "Listing"),
            listings: data.filter(item => item.type === "Listing")
        };
    }

    return {
        categories: data.categories || data.category || data.suggestions || [],
        listings: data.listings || data.businesses || data.results || []
    };
}
    function renderCategories(data) {
        dropdown.innerHTML = "";

        const response = normalizeResponse(data);
        let categories = response.categories;
        let listings = response.listings;

       

const searchText = cleanText(input.value);

if (searchText) {

    const searchWords = searchText.split(" ").filter(Boolean);

    function strictMatch(name) {
        const nameText = cleanText(name);
        return nameText.startsWith(searchText);
    }

    categories = categories.filter(item =>
        strictMatch(item.name)
    );

    listings = listings.filter(item =>
        strictMatch(item.name)
    );
}

        if (Array.isArray(categories) && categories.length > 0) {
            lastGoodCategories = categories;
        }

        // if (!Array.isArray(categories) || categories.length === 0) {
        //     categories = lastGoodCategories.length > 0 ? lastGoodCategories : [];
        // }

        if (!Array.isArray(listings)) {
            listings = [];
        }

        const isTyping = input.value.trim() !== "";

        if (!isTyping) {
            const recentCategories = getRecentCategories();

            if (recentCategories.length > 0) {
                dropdown.appendChild(renderSectionTitle("Recent Searches", "clear-recent-categories"));

             recentCategories.forEach(item => {
    let li = null;

    if (item.type === "listing") {
        li = renderListingItem(item);
    } else {
        li = renderCategoryItem(item, "search");
    }

    if (li) dropdown.appendChild(li);
});
            }

            if (categories.length > 0) {
                dropdown.appendChild(renderSectionTitle("Trending Searches"));
            }
        }

       let usedCategories = [];

categories.slice(0, 10).forEach(item => {
    if (!item || !item.name) return;

    const uniqueKey = item.name.toLowerCase();

    if (usedCategories.includes(uniqueKey)) return;

    usedCategories.push(uniqueKey);

    const li = renderCategoryItem(
        item,
        isTyping ? "search" : "trending"
    );

    if (li) dropdown.appendChild(li);
});

        listings.forEach(item => {
            const li = renderListingItem(item);
            if (li) dropdown.appendChild(li);
        });

        if (!dropdown.children.length) {
            if (isTyping) {
                dropdown.appendChild(renderNotFoundItem());
            } else {
                closeCategoryDropdown();
                return;
            }
        }

        openCategoryDropdown();
    }

function fetchCategorySuggestions(search = "") {

    const cleanSearch = cleanText(search);
    lastCategorySearch = cleanSearch;

    const selectedLocation = locationInput ? locationInput.value.trim() : "";

    const locationId =
        locationInput?.dataset.locationId ||
        document.getElementById("homeSearchLocationId")?.value ||
        "";

    const url = `{{ route('category.suggestions') }}?q=${encodeURIComponent(cleanSearch)}&location_id=${encodeURIComponent(locationId)}&location_name=${encodeURIComponent(selectedLocation)}&exact=0&with_listings=1&_=${Date.now()}`;

    fetch(url, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (cleanText(input.value) !== cleanSearch) return;
        renderCategories(data);
    })
    .catch(err => {
        console.error("Category suggestion error:", err);
        closeCategoryDropdown();
    });
}
   input.addEventListener("focus", function () {
    const value = input.value.trim();

    homeCategoryIndex = -1;

    if (value.length > 0) {
        fetchCategorySuggestions(value);
    } else {
        fetchCategorySuggestions("");
    }
});

input.addEventListener("click", function () {
    const value = input.value.trim();

    homeCategoryIndex = -1;

    if (value.length > 0) {
        fetchCategorySuggestions(value);
    } else {
        fetchCategorySuggestions("");
    }
});

    input.addEventListener("input", function () {
        const value = this.value;

        homeCategoryIndex = -1;
        input.dataset.selectedCategory = "0";
        input.dataset.selectedUrl = "";

        clearTimeout(categoryTimer);

        categoryTimer = setTimeout(() => {
            fetchCategorySuggestions(value);
        }, 250);
    });

    dropdown.addEventListener("mousedown", function (e) {
        e.preventDefault();
    });

    dropdown.addEventListener("click", function (e) {

        if (e.target.classList.contains("clear-recent-categories")) {
            localStorage.removeItem("service_bazzar_recent_categories");
            fetchCategorySuggestions("");
            return;
        }

        const item = e.target.closest(".dropdown-item");
        if (!item || !item.dataset.url) return;

        input.value = item.dataset.value || "";
        input.dataset.selectedCategory = "1";
        input.dataset.selectedUrl = item.dataset.url || "";

   saveRecentCategory({
    value: item.dataset.value || "",
    name: item.dataset.value || "",
    url: item.dataset.url || "",
    type: item.dataset.type || "category",
    image: item.dataset.image || "",
    area: item.dataset.area || "",
    rating: item.dataset.rating || "",
    reviews: item.dataset.reviews || ""
});

closeCategoryDropdown();

        if (item.dataset.type === "listing") {
            window.location.href = item.dataset.url;
            return;
        }

        const baseUrl = item.dataset.url;
        const selectedLocation = getSelectedLocation();

        if (!selectedLocation) return;

        const locationText = selectedLocation.trim();

        let city = "";
        let area = "";

        if (locationText.includes(",")) {
            const parts = locationText.split(",");
            area = parts[0].trim();
            city = parts[1].trim();
        } else {
            city = locationText;
        }

        const citySlug = slugify(city);
        const categorySlug = baseUrl.split("/").filter(Boolean).pop();

        let finalUrl = `${window.location.origin}/${citySlug}/${categorySlug}`;

        if (area !== "") {
            finalUrl += `-in-${slugify(area)}`;
        }

        window.location.href = finalUrl;
    });

    input.addEventListener("keydown", function(e) {

        const items = dropdown.querySelectorAll(".dropdown-item");
        if (!items.length) return;

        if (e.key === "ArrowDown") {
            e.preventDefault();
            homeCategoryIndex++;
            if (homeCategoryIndex >= items.length) homeCategoryIndex = 0;
        }
        else if (e.key === "ArrowUp") {
            e.preventDefault();
            homeCategoryIndex--;
            if (homeCategoryIndex < 0) homeCategoryIndex = items.length - 1;
        }
        else if (e.key === "Enter" && homeCategoryIndex > -1) {
            e.preventDefault();
            items[homeCategoryIndex].click();
            return;
        } else {
            return;
        }

        items.forEach(item => item.classList.remove("active"));

        const activeItem = items[homeCategoryIndex];
        activeItem.classList.add("active");

        input.value =
            activeItem.dataset.value ||
            activeItem.textContent.trim();

        activeItem.scrollIntoView({ block: "nearest" });
    });

    document.addEventListener("click", function (e) {
        if (
            !e.target.closest("#homeCategorySearch") &&
            !e.target.closest("#homeCategorySuggestions")
        ) {
            closeCategoryDropdown();
        }
    });

    input.addEventListener("blur", function () {
        setTimeout(() => {
            closeCategoryDropdown();
        }, 150);
    });

    window.addEventListener("resize", function () {
        closeCategoryDropdown();
    });

    const homeForm = document.getElementById("homeSearchForm");

    if (homeForm) {
        homeForm.addEventListener("submit", function(e) {

            const selectedCategory = input.dataset.selectedCategory === "1";
            const selectedLocation =
                locationInput && locationInput.dataset.selectedLocation === "1";

            if (!selectedCategory || !selectedLocation) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (!selectedLocation && locationInput) {
                    locationInput.focus();
                } else {
                    input.focus();
                }

                return false;
            }
        });
    }

});
</script>
<style>
#homeCategorySuggestions{
    padding: 10px 0 !important;
}

#homeCategorySuggestions .category-suggestion-item,
#homeCategorySuggestions .category-listing-item{
    display: flex !important;
    align-items: center;
    gap: 12px;
    padding: 9px 18px !important;
    cursor: pointer;
    background: #fff;
}

#homeCategorySuggestions .category-suggestion-item:hover,
#homeCategorySuggestions .category-listing-item:hover{
    background: #f5f5f5;
}

.category-search-icon{
    width: 26px;
    min-width: 26px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #111;
    font-size: 16px;
}

.category-trending-icon{
  width: 30px;
    height: 30px;
    margin: 0 10px 0 5px;
    border-radius: 6px;
    border: .5px solid #ddd;
    background-color: #fff;
    overflow: hidden;
    background-repeat: no-repeat;
    -webkit-background-size: contain;
    -moz-background-size: contain;
    -o-background-size: contain;
    background-size: contain;
    background-position: center;
    -webkit-box-flex: 0;
    -webkit-flex: none;
    -moz-box-flex: 0;
    -ms-flex: none;
    flex: none;
}

.category-trending-icon img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.listing-suggestion-img{
    width: 30px;
    height: 30px;
    min-width: 30px;
    border-radius: 5px;
    overflow: hidden;
    background: #eee;
}

.listing-suggestion-img img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-suggestion-content{

    font-size: 14px;
    font-weight: 400;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.category-suggestion-name{
   font-size: 14px;
    font-weight: 500;
    color: #111;
    line-height: 18px;
}



.listing-rating{
    background: #0a9f34;
    color: #fff;
    font-size: 11px;
    padding: 1px 4px;
    border-radius: 2px;
    font-weight: 600;
}

.listing-review,
.listing-dot{
    color: #777;
}

.clear-recent-categories{
    float: right;
    color: #0076d7;
    font-size: 12px;
    cursor: pointer;
    text-transform: none;
    font-weight: 400;
}
</style>
           
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
    <a href="{{ route('home') }}" class="download-btn">
        Download App
        <i class="fas fa-download"></i>
    </a>
</div>


</section>
<!-- Banner Section End -->

<style>
    #homeLocationSuggestions .dropdown-item.active,
#homeCategorySuggestions .dropdown-item.active{
    background:#ebebeb !important;
 
}
    .add-right {
        margin-left: 45px;
    }

    @media (max-width: 767px) {
        .add-right {
            margin-left: 0 !important;
        }
    }
    @media (min-width: 992px){
  

    .banner-section .title-area{
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}
@media (min-width: 992px){
    body:not(.header-fixed) #scrollSearchHolder{
        display: none !important;
    }

    body.header-fixed .banner-section .search-area{
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

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

                        @foreach($bannerImages as $imgData)
                            @php
                                $mobileImgData = $mobileBannerImages[$loop->index] ?? null;
                            @endphp

                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <div class="main-banner-box">

                                    @if(!empty($imgData['link']))
                                        <a href="{{ $imgData['link'] }}">
                                    @endif

                                        {{-- Desktop banner --}}
                                        <img
                                            src="{{ loadImage('assets/servicebazzar/images/banner/'.$imgData['image']) }}"
                                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                                            decoding="async"
                                            alt="{{ $banner->content->title }} Near Me"
                                            class="desktop-banner-img">

                                        {{-- Mobile banner --}}
                                        @if(!empty($mobileImgData['image']))
                                            <img
                                                src="{{ loadImage('assets/servicebazzar/images/banner/'.$mobileImgData['image']) }}"
                                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                                fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                                                decoding="async"
                                                alt="Best {{ $banner->content->title }} Near Me"
                                                class="mobile-banner-img">
                                        @else
                                            <img
                                                src="{{ loadImage('assets/servicebazzar/images/banner/'.$imgData['image']) }}"
                                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                                fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                                                decoding="async"
                                                alt="Find {{ $banner->content->title }} Near Me"
                                                class="mobile-banner-img">
                                        @endif

                                    @if(!empty($imgData['link']))
                                        </a>
                                    @endif

                                </div>
                            </div>
                        @endforeach

                    </div>

                    @if(count($bannerImages) > 1)
                        <a class="carousel-control-prev" href="#mainBannerCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </a>
                        <a class="carousel-control-next" href="#mainBannerCarousel" role="button" data-slide="next">
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

                <a href="{{ route('listings.category', ['slug' => $categoryItem->slug]) }}"
                   class="mini-service-card dynamic-category-link"
                   data-slug="{{ $categoryItem->slug }}"
                   style="background-color: {{ $bgColor }}; display:block; height:100%;">

                    <img src="{{ loadImage('assets/images/category/'.$categoryItem->image) }}"
                         loading="lazy"
                         alt="{{ $categoryItem->name }} Near Me">

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

                        <div class="home_banner_explorearw font11"
                             style="color:#fff;
                             background-image:url('https://akam.cdn.jdmagicbox.com/images/icontent/newwap/web2022/web_whiteright_arrow.svg');">
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

<style>
    .home_banner_explore{
    cursor: pointer;
    transition: all .3s ease;
}

.home_banner_explore:hover .home_banner_exploretxt{
    width: 55px;
    padding-left: 8px;
}

.home_banner_explore:hover{
    padding-right: 6px;
}
.mini-service-card{
    position: relative;
    overflow: hidden;
}
    .home_banner_explore {
    position: absolute;
    bottom: 25px;
    left: 0;
    min-width: 26px;
    height: 26px;
    -webkit-border-bottom-right-radius: 5px;
    -moz-border-radius-bottomright: 5px;
    border-bottom-right-radius: 5px;
    -webkit-border-top-right-radius: 5px;
    -moz-border-radius-topright: 5px;
    border-top-right-radius: 5px;
    -webkit-box-shadow: 0 3px 6px 0 rgba(0, 0, 0, .16);
    -moz-box-shadow: 0 3px 6px 0 rgba(0,0,0,.16);
    box-shadow: 0 3px 6px 0 rgba(0, 0, 0, .16);
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    z-index: 1;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
}
.home_banner_exploretxt {
    width: 0;
    overflow: hidden;
    font-size: 12px;
    color: #fff;
    -webkit-transition: all .3s linear;
    -moz-transition: all.3s linear;
    -o-transition: all.3s linear;
    transition: all .3s linear;
}
.home_banner_explorearw{
    display: inline-block;
    width: 14px;
    height: 14px;
    min-width: 14px;
    background-image: url('https://akam.cdn.jdmagicbox.com/images/icontent/newwap/web2022/web_whiteright_arrow.svg');
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
}
.home_banner_explore{
    display:flex;
    align-items:center;
}

.main-banner-box{
    height: 270px;
    border-radius: 18px;
    overflow: hidden;
    position: relative;
}

.main-banner-box img{
    width: 100%;
    height: 100%;
    object-fit: fill;
}

.mini-service-card{
   width: 100%;
    padding: 15px;
    width: 100%;
    height: 100%;
    display: block;
}

.mini-service-card img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mini-card-overlay{
    position: absolute;
    inset: 0;
    padding: 18px 14px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end; 
    background: linear-gradient(to bottom, rgba(0,0,0,0.10), rgba(0,0,0,0.18));
}

.mini-card-title{
         font-size: 20px;
    color: #fff;
    font-weight: 700;
    margin-bottom: 4px;
    -webkit-transform: scale(1);
    -moz-transform: scale(1);
    -ms-transform: scale(1);
    -o-transform: scale(1);
    transform: scale(1);
    -webkit-transition: .2s linear;
    -moz-transition: .2s linear;
    -o-transition: .2s linear;
    transition: .2s linear;
    -webkit-transform-origin: bottom left;
    -moz-transform-origin: bottom left;
    -ms-transform-origin: bottom left;
    -o-transform-origin: bottom left;
    transform-origin: bottom left;

}

.mini-card-subtitle{
    font-size: 13px;
    color: #fff;
    margin-top: 6px;
}

.mini-arrow-btn{
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255,255,255,0.95);
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 16px;
}

.main-banner-caption{
    position: absolute;
    left: 35px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    color: #123;
    max-width: 260px;
}

.main-banner-caption h2{
    font-size: 22px;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 14px;
}

.main-banner-caption .btn{
    border-radius: 8px;
    padding: 8px 18px;
}

.category-item{
    padding: 5px;
}

.desktop-banner-img{
    display: block;
}

.mobile-banner-img{
    display: none;
}

/* ONLY MOBILE */
@media (max-width: 991px){

    .custom-banner-section{
        padding: 10px;
        display: block !important;
       
        padding-bottom: 10px !important;
    }

    .mobile-banner-row{
        row-gap: 14px !important; /* banner aur categories ke beech gap */
    }

    .mobile-banner-col,
    .mobile-category-col{
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
    }

    .desktop-banner-img{
        display: none !important;
    }

    .mobile-banner-img{
        display: block !important;
    }

    .main-banner-box{
        height: 180px;
        border-radius: 14px;
    }

    .mobile-category-row{
        display: flex;
        flex-wrap: nowrap !important;
        margin: 0 -4px;
        row-gap: 0 !important;
    }

    .mobile-category-item{
        flex: 0 0 25% !important;
        max-width: 25% !important;
        padding: 0px !important;
    }

    .mini-service-card{
        height: 95px;
        border-radius: 12px;
    }

    .mini-card-overlay{
        padding: 8px 8px;
        background: linear-gradient(to bottom, rgba(0,0,0,0.12), rgba(0,0,0,0.28));
    }

    .mini-card-title{
        font-size: 10px;
        line-height: 1.2;
        margin: 0;
    }

    .mini-card-subtitle{
        display: none;
    }

    .mini-arrow-btn{
        width: 22px;
        height: 22px;
        font-size: 10px;
    }
}


@media (max-width: 767px){

    .mobile-category-row{
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 6px !important;
        margin: 0 !important;
    }

    .mobile-category-item{
        flex: 0 0 calc((100% - 18px) / 4) !important;
        max-width: calc((100% - 18px) / 4) !important;
        height: 95px !important;
        padding: 0 !important;
        border-radius: 10px !important;
        overflow: hidden !important;
        background: #0E77CD !important;
    }

    .mobile-category-item .mini-service-card{
        height: 100% !important;
        width: 100% !important;
        padding: 6px !important;
        position: relative !important;
        display: block !important;
        overflow: hidden !important;
    }

    .mobile-category-item .mini-service-card img{
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        transform: none !important;
        border-radius: 0 !important;
    }

   .mobile-category-item .mini-card-title{
    position: absolute;
    left: 8px;
    top: 8px;

    font-size: 9px;
    line-height: 1.2;
    font-weight: 600;

    color: #fff;

    text-shadow: 0 1px 3px rgba(0,0,0,0.5);
}
    .mobile-category-item .home_banner_subtext{
        display: none !important;
    }

    .mobile-category-item .home_banner_explore{
        display: none !important;
    }
}

@media (min-width: 992px){

    .mobile-category-row{
        display: flex;
        gap: 12px;
        align-items: stretch;
    }

    .mobile-category-item{
        flex: 1;
        padding: 0 !important;
        display: flex;
        background: #0E77CD;
        overflow: hidden;
        border-radius: 18px;
    }

    .mini-service-card{
        width: 100%;
        height: 100%;
        display: block;
    }

    .mini-service-card img{
           position: absolute;
    inset: 0px;
    box-sizing: border-box;
    padding: 0px;
    border-width: medium;
    border-style: none;
    border-color: currentcolor;
    border-image: initial;
    margin: auto;
    display: block;
    width: 0px;
    height: 0px;
    min-width: 100%;
    max-width: 100%;
    min-height: 100%;
    max-height: 100%;
    }
       .mobile-category-item{
        height: 270px;
    }

    .mini-service-card{
        height: 100%;
    }
   .home_banner_subtext {
    font-size: 14px;
    position: relative;
    z-index: 1;
    width: 50%;
    line-height: 1.4;
}

}


.mini-service-card{
    width: 100%;
}
</style>

<style>
@media (max-width: 767px){
    .category-area{
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .category-area .category-grid{
        margin-top: 0 !important;
    }
}
.font14 {
    font-size: 14px;
}.fw400 {
    font-weight: 400;
}.color111 {
    color: #111;
}

.category-item .font14{
    font-size: 11px;
    line-height: 14px;
    margin-top: 6px;

    text-align: center;
    max-width: 72px;
    height: 28px;

    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;

    word-break: break-word;
    overflow-wrap: break-word;
}

.category-item a{
    align-items: center;
}
</style>
    
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

{{-- Pure-CSS open/close switch for the "More Categories" panel. The
     label-driven checkbox keeps the panel usable when JS is off or
     has died earlier on the page; the JS below only syncs the blur +
     scroll lock on top of it. --}}
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
                    <a href="{{ route('listings.category', ['slug' => $categoryItem->slug]) }}"
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
                                <img
                                    src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                                    loading="lazy"
                                    alt="Top {{ $categoryItem->name }} Near Me"
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
                    role="button"
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

<style>
.category-box {
    position: relative;
}

.category-box .category-icon-img {
    width: 38px;
    height: 38px;
    object-fit: contain;
}

.category-box i {
    font-size: 32px;
}

.more {
    font-size: 40px;
    color: red;
}

.more-btn {
    border: 0;
    background: transparent;
    padding: 0;
    width: 100%;
    cursor: pointer;
}

/* ===== No-JS "More Categories" panel =====
 * #categoryPopupToggle sits before both the overlay and the panel, so the
 * two rules below open them with CSS only. Specificity (1,2,0) clears the
 * base .category-popup / .popup-overlay rules in css/index.css without
 * !important, and the .active class stays supported so the JS path and the
 * CSS path can never fight each other. */
.category-popup-toggle {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    padding: 0;
    border: 0;
    overflow: hidden;
    clip: rect(0 0 0 0);
    clip-path: inset(50%);
    white-space: nowrap;
    pointer-events: none;
}

#categoryPopupToggle:checked ~ .popup-overlay {
    opacity: 1;
    visibility: visible;
}

#categoryPopupToggle:checked ~ .category-popup {
    right: 0;
}

label.more-btn,
label.close-btn {
    cursor: pointer;
    -webkit-user-select: none;
    user-select: none;
}

/* The overlay is a <label> now (so it can close the panel without JS);
   labels are inline by default. */
label.popup-overlay {
    display: block;
    margin: 0;
}

/* ===== "Location off" strip inside the panel ===== */
.popup-location-notice {
    position: relative;
    margin: 12px 0 18px;
    padding: 14px 44px 14px 14px;
    border: 1px solid #ffd9a6;
    border-radius: 10px;
    background: #fff8ec;
    box-shadow: 0 12px 30px rgba(0, 0, 0, .12);
}

.popup-location-notice__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.popup-location-notice__steps {
    margin: 10px 0 0;
    padding-left: 20px;
    font-size: 13px;
    line-height: 1.6;
    color: #7a4a00;
}

.popup-location-notice__steps[hidden] {
    display: none;
}

/* Confirmation state — same strip, no longer a warning. */
.popup-location-notice--done {
    border-color: #b7e2c0;
    background: #f1faf3;
}

.popup-location-notice--done .popup-location-notice__text {
    color: #1d6b33;
}

.popup-location-notice--done .popup-location-notice__text i {
    color: #2f9e50;
}

.popup-location-notice[hidden] {
    display: none;
}

.popup-location-notice__text {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    line-height: 1.4;
    color: #7a4a00;
    min-width: 0;
}

.popup-location-notice__close {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #7a4a00;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
}

.popup-location-notice__close:hover,
.popup-location-notice__close:focus {
    background: rgba(122, 74, 0, .08);
    outline: none;
}

.popup-location-notice__text i {
    color: #e5581e;
    font-size: 16px;
    flex: 0 0 auto;
}

.popup-location-notice__btn {
    flex: 0 0 auto;
    border: 0;
    border-radius: 8px;
    background: #e5581e;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    padding: 8px 16px;
    cursor: pointer;
}

.popup-location-notice__btn:disabled {
    opacity: .6;
    cursor: default;
}

@media (max-width: 767px) {
    .popup-location-notice {
        margin: 10px 0 14px;
        padding: 14px 42px 14px 12px;
    }

    .popup-location-notice__text {
        font-size: 13px;
    }

    .popup-location-notice__btn {
        width: 100%;
        padding: 10px 14px;
    }
}

@media (max-width: 767px) {
    .hide-mobile {
        display: none !important;
    }

    .more-category-item {
        display: block !important;
    }
}
</style>

<label class="popup-overlay" id="popupOverlay" for="categoryPopupToggle" aria-label="Close categories"></label>

<!-- Panel -->
<div class="category-popup" id="categoryPopup">
    <div class="popup-top">
        <div class="d-flex align-items-center gap-3">
            <label for="categoryPopupToggle" class="close-btn" role="button" tabindex="0">✕</label>
            <h5 class="mb-0">Popular Categories</h5>
        </div>

        
        <div class="search-wrapper">
            <input type="text" id="categorySearch"
                   placeholder="Search"
                   class="search-input">
            <span class="clear-search" onclick="clearSearch()">✕</span>
        </div>
    </div>

    {{-- Shown only when no city is known: category links still work (their
         href redirects to a default city), but the results are not local
         until the visitor turns location on. --}}
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

        {{-- Only used for the blocked case: once a site is blocked the
             browser will not show its prompt again, so a button cannot fix
             it — the visitor has to change it in the browser itself. --}}
        <ol class="popup-location-notice__steps" id="popupLocationSteps" hidden>
            <li>Click the lock or info icon next to the website address.</li>
            <li>Open <strong>Site settings</strong> (or <strong>Permissions</strong>).</li>
            <li>Set <strong>Location</strong> to <strong>Allow</strong>.</li>
            <li>Reload this page.</li>
        </ol>
    </div>

    
   @php
    /*
     * Fallback city for a visitor whose location we don't know yet. Hardcoding
     * "jaipur" here made the homepage telegraph a Jaipur-first signal to Google
     * (every no-location category link resolved to Jaipur). Instead rotate the
     * default across the popular cities by day-of-year — deterministic, so the
     * 30-minute page cache stays stable, but no single city is the permanent
     * default. A visitor with a real detected/selected location is unaffected.
     */
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

        {{-- Real href, not "#": the JS handler below only takes over when it
             can build a city-prefixed URL. With location denied/unset — or
             with JS off entirely — the browser follows this link and
             listings.category redirects to the visitor's city. --}}
        <a href="{{ route('listings.category', ['slug' => $categoryItem->slug]) }}"
   class="popup-item dynamic-category-link"
   data-slug="{{ $categoryItem->slug }}">

            @if($categoryItem->icon && Str::endsWith($categoryItem->icon, ['.png', '.jpg', '.jpeg', '.svg']))

                <img src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                     loading="lazy"
                     alt="Browse {{ $categoryItem->name }} Near Me"
                     class="popup-category-icon">

            @else

                <i class="{{ $categoryItem->icon }}"></i>

            @endif

            <span>{{ $categoryItem->name }}</span>

        </a>

    @endforeach
</div>
   <style>

/* DESKTOP + COMMON */
.category-grid{
    display: grid;
    grid-template-columns: repeat(10, minmax(0, 1fr));
    gap: 30px 20px;
    width: 100%;
}

.category-item{
    text-align: center;
    min-width: 0;
}

.category-item a,
.category-item button,
.category-item label{
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    text-decoration: none;
    padding: 0;
    margin: 0;
    border: 0;
    background: transparent;
    box-sizing: border-box;
}

.category-box{
   display: inline-block;
    overflow: hidden;
    border: .5px solid #ccc;
    -webkit-border-radius: 14px;
    -moz-border-radius: 14px;
    border-radius: 14px;
    height: 74px;
    width: 74px;
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
}
.category-box i{
    font-size: 34px;
    color: red;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-title{
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
    margin: 0;
    text-align: center;
    min-height: 44px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    word-break: break-word;
    overflow-wrap: break-word;
}

.more-btn{
    background: transparent;
    border: 0;
    padding: 0;
    margin: 0;
    cursor: pointer;
}

/* TABLET */
@media (max-width: 991px){
    .category-grid{
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px 12px;
    }
}

/* CATEGORY GRID FIX - MOBILE */
@media (max-width: 767px){

     .category-grid{
        gap: 16px 8px;
    }

     .category-item .font14{
        font-size: 11px;
        line-height: 1.25;
        max-width: 72px;
        min-height: 30px;
        margin-top: 4px;

        display: block;
        text-align: center;

        overflow: hidden;
        word-break: normal;
    }

    .category-item a,
    .category-item button{
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    .category-box{
        width: 62px;
        height: 62px;
        margin-bottom: 6px;
    }

     .category-box .category-icon-img{
        width: 34px;
        height: 34px;
        object-fit: contain;
    }

    .category-box i{
        font-size: 20px !important;
        line-height: 1 !important;
    }

    .category-title{
        font-size: 9px !important;
        line-height: 1.25 !important;
        margin-top: 0 !important;
        text-align: center !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        min-height: 32px !important;
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 0 2px !important;
    }

    .category-area{
        padding-bottom: 20px !important;
        overflow: hidden !important;
    }

    .custom-right{
        overflow-x: hidden !important;
        /*
         * FOUND THE REAL BUG: this rule is meant for the category grid
         * section above (.custom-right is reused as a generic wrapper
         * class there), but .custom-right is ALSO the class on the
         * footer's own wrapper div (.container-fulde.ssss.custom-right
         * in includes/footer.blade.php) — so this unscoped selector was
         * hitting the footer too. Setting overflow-x here without also
         * setting overflow-y triggers a CSS spec rule: when one axis is
         * non-'visible' and the other isn't explicitly set, the browser
         * forces the unset one to computed 'auto' instead of 'visible'.
         * On the footer wrapper that turned into a real, visible internal
         * scrollbar (its height came out a few px shorter than its own
         * content). Explicitly pinning overflow-y to visible here closes
         * that off at the actual source, instead of only fighting it with
         * a competing override elsewhere (css/components/
         * footer-accordion.css) that depended on winning a specificity
         * comparison against this !important rule.
         */
        overflow-y: visible !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .hide-mobile{
        display: none !important;
    }

    /* ================= POPUP FIX ================= */

    .category-popup{
        position: fixed;
        top: 0;
        right: -100%;
        width: 100% !important;
        max-width: 100% !important;
        height: 100%;
        background: #fff;
        z-index: 99999999;
        padding: 14px 10px !important;
        box-sizing: border-box !important;
        overflow-y: auto;
        overflow-x: hidden !important;
        transition: 0.3s ease;
    }

    .category-popup.active{
        z-index: 100000;
        right: 0 !important;
    }

    .popup-grid{
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 14px 6px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .popup-item{
        min-width: 0 !important;
        width: 100% !important;
        padding: 8px 2px !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
        text-align: center !important;
    }

    .popup-item i{
        font-size: 22px !important;
        margin-bottom: 6px !important;
    }

    .popup-item span{
        font-size: 12px !important;
        line-height: 1.3 !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        display: block !important;
        text-align: center !important;
    }
}

.pb-15 {
    padding-bottom: 15px;
}
.pt-15 {
    padding-top: 15px;
}
.p-30 {
    padding: 30px;
}

.font18 {
    font-size: 18px;
}
.fw500 {
    font-weight: 500;
}
.color111 {
    color: #111;
}

</style>
    <!-- NO RESULT -->
    <div class="no-result text-center" id="noResult">
        <img src="{{ asset('assets/images/search_notfound.svg') }}" loading="lazy" alt="No Search Found">
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
                                    <a href="{{ route('listings.category', ['slug' => $category->slug]) }}"
   class="mini-service-card dynamic-category-link"
   data-slug="{{ $category->slug }}">

   
                                            <img src="{{ asset('assets/images/category/' . $category->image) }}"
                                              loading="lazy"   alt="{{ $category->name }} Services">

                                         
                                              

                                          
                                        </a>
                                        <figcaption class="color111 font14 pt-10">
    {{ $category->name }}
</figcaption>
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
<style>
    .pt-10 {
    padding-top: 10px;
}
    .cardhotkey__wrap{
        border: 1px solid #ccc;
    -webkit-border-radius: 10px;
    -moz-border-radius: 10px;
    border-radius: 10px;
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -moz-box-orient: vertical;
    -moz-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-flex: 1;
    -webkit-flex: 1 0 40%;
    -moz-box-flex: 1;
    -ms-flex: 1 0 40%;
    flex: 1 0 40%;
      margin-bottom: 20px;
    }
    .rr-section .row{
    gap: 10px;
    margin-right: 0px;
    margin-left: 0px;

}


.rr-section .mini-service-card{
   display: inline-block;
    width: 100%;
    -webkit-border-radius: 10px;
    -moz-border-radius: 10px;
    border-radius: 10px;
    overflow: hidden;
    height: 120px;
}

.rr-section .mini-service-card img{
    position: absolute;
    inset: 0px;
    box-sizing: border-box;
    padding: 0px;
    border-width: medium;
    border-style: none;
    border-color: currentcolor;
    border-image: initial;
    margin: auto;
    display: block;
    width: 0px;
    height: 0px;
    min-width: 100%;
    max-width: 100%;
    min-height: 100%;
    max-height: 100%;
}

.rr-section .mini-card-overlay{
    position: absolute;
    inset: 0;
    padding: 18px 14px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    background: linear-gradient(to bottom, rgba(0,0,0,0.10), rgba(0,0,0,0.18));
}

.rr-section .mini-card-title{
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
    margin: 0;
    color: #fff;
}

.rr-section .mini-card-subtitle{
    font-size: 13px;
    color: #fff;
    margin-top: 6px;
}

.rr-section .mini-arrow-btn{
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255,255,255,0.95);
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 16px;
}

.rr-section .subcategory-scroll{
    overflow: hidden;
}

.rr-section .subcategory-row{
   display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-flex-wrap: wrap;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
}

.rr-section .subcategory-col{
  width: 30%;
    margin-right: 5%;
    cursor: pointer;
}
.rr-section .subcategory-col:nth-child(3n){
    margin-right: 0;
}

.mt-10 {
    margin-top: 10px;
}
.text_center {
    text-align: center;
}
@media (max-width: 767px){

    .rr-section .row{
        gap: 0;
        margin-left: 0;
        margin-right: 0;
    }

    .rr-section .cardhotkey__wrap{
        padding: 12px 10px;
        margin-bottom: 14px;
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        box-sizing: border-box;
    }

    .rr-section .subcategory-row{
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 0;
        margin: 0;
    }

    .rr-section .subcategory-col{
        width: 100%;
        max-width: 100%;
        margin: 0;
        text-align: center;
    }

    .rr-section .mini-service-card{
        width: 100%;
        height: 95px;
        border-radius: 12px;
        overflow: hidden;
        display: block;
        background: #f3f3f3;
    }

    .rr-section .mini-service-card img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 12px;
    }

    .rr-section figcaption{
        font-size: 11px;
        line-height: 1.25;
        color: #111;
        font-weight: 500;
        text-align: center;
        padding-top: 6px;

        min-height: 32px;
        max-height: 34px;
        overflow: hidden;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
}
</style>
<style>
    .category-img {
        width: 100%;
        border-radius: 10px;
        height: 140px;
        object-fit: cover;
        display: block;
    }

    .category-card p {
        font-size: 18px;
        color: #111;
    }

    @media (max-width: 767px) {
        .category-img {
            height: 100px;
        }

        .category-card p {
            font-size: 14px;
        }
    }

 @media (max-width: 767px){

    .trending-same-section .trending-scroll{
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-x pan-y !important;
        scrollbar-width: none !important;
        overscroll-behavior-x: contain;
    }

    .trending-same-section .trend-card-link,
    .trending-same-section .trend-card-link img,
    .trending-same-section .trend-card-overlay{
        touch-action: auto !important;
    }

    .trending-same-section .trending-scroll::-webkit-scrollbar{
        display: none !important;
    }
}
@media (max-width:767px){
    .trending-wrapper{
        overflow: visible !important;
    }
}
@media (max-width: 767px){
    .trending-same-section .trending-scroll{
        touch-action: pan-y pan-x;
    }
}
</style>

    <section class="py-4 custom-right trending-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="d-flex align-items-center mb-2">
                <h4 class="fw-bold mb-0">Trending Searches Near You</h4>
                <span class="badge bg-danger text-white ms-2">NEW</span>
            </div>

            <p class="text-muted">Stay updated with the latest local trends.</p>

            <div class="trending-wrapper position-relative">

                <button class="trend-nav prev-btn" type="button" onclick="scrollTrending(-1)">
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

                            <a href="{{ route('listings.category', ['slug' => $category->slug]) }}"
                               class="trend-card-link dynamic-category-link"
                               data-slug="{{ $category->slug }}">

                                <img src="{{ asset('assets/images/category/' . $category->image) }}"
                                     loading="lazy"
                                     onerror="this.hidden=true;"
                                    alt="{{ $category->name }} Near Me">

                                <div class="trend-card-overlay">
                                    <div>
                                        <h5 class="trend-card-title {{ preg_match('/\s+/', trim($category->name)) ? 'trend-card-title--words' : 'trend-card-title--single' }}">
                                            {{ $category->name }}
                                        </h5>
                                    </div>
                                </div>

                            </a>
                        </div>
                    @endforeach

                </div>

                <button class="trend-nav next-btn" type="button" onclick="scrollTrending(1)">
                    <i class="las la-angle-right"></i>
                </button>

            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const locationInput =
        document.querySelector('input[name="location_name"]');

    const currentLocation = locationInput
        ? locationInput.value.toLowerCase().trim()
        : 'default';

    const trendingKey = 'trending_categories_' + currentLocation;

    const wrapper = document.getElementById('trendingScroll');

    if (!wrapper) return;

    let trends = JSON.parse(localStorage.getItem(trendingKey) || '{}');

    let cards = Array.from(
        wrapper.querySelectorAll('.trend-card-item')
    );

    cards.forEach(card => {

        const categoryId = card.dataset.categoryId;

        card.dataset.count = trends[categoryId] || 0;
    });

    cards.sort((a, b) => {
        return Number(b.dataset.count) - Number(a.dataset.count);
    });

    wrapper.innerHTML = '';

    cards.slice(0, 12).forEach(card => {
        wrapper.appendChild(card);
    });

   
});

function scrollTrending(direction) {

    const container = document.getElementById('trendingScroll');

    const scrollAmount = 320;

    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>
    <style>
    .trending-same-section .trending-wrapper{
        position: relative;
    }

    .trending-same-section .trending-scroll{
        display: flex;
        gap: 12px;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        padding: 0 4px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .trending-same-section .trending-scroll::-webkit-scrollbar{
        display: none;
    }

    .trending-same-section .trend-card-item{
        flex: 0 0 141px;
        max-width: 145px;
    }

    .trending-same-section .trend-card-link{
        height: 215px;
        border-radius: 18px;
        overflow: hidden;
        position: relative;
        display: block;
        color: #fff;
        text-decoration: none;
        background: #ddd;
    }

    .trending-same-section .trend-card-link img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .trending-same-section .trend-card-overlay{
        position: absolute;
        inset: 0;
        padding: 14px 12px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        background: linear-gradient(to bottom, rgba(0,0,0,0.14), rgba(0,0,0,0.22));
    }

    .trending-same-section .trend-card-overlay > div{
        min-height: 35px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .trending-same-section .trend-card-title{
        font-size: 14px;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
        color: #fff;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .trending-same-section .trend-card-subtitle{
        font-size: 12px;
        color: #fff;
        margin-top: 8px;
        font-weight: 500;
    }

    .trending-same-section .trend-arrow-btn{
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(255,255,255,0.95);
        color: #2b50d9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    .trending-same-section .trend-nav{
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 54px;
        border: none;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        border-radius: 10px;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #111;
        cursor: pointer;
    }

    .trending-same-section .prev-btn{
        left: -10px;
    }

    .trending-same-section .next-btn{
        right: -10px;
    }

    @media (max-width: 767px){
        .trending-same-section .trending-scroll{
            gap: 8px;
            padding: 0;
        }

       .trending-same-section .trend-card-item{
        flex: 0 0 calc((100% - 24px) / 4);
        max-width: calc((100% - 24px) / 4);
    }

       .trending-same-section .trend-card-link{
        height: 108px;
        border-radius: 12px;
    }

        .trending-same-section .trend-card-overlay{
            padding: 8px 8px;
        }

        .trending-same-section .trend-card-overlay > div{
            min-height: 21px;
        }

        .trending-same-section .trend-card-title{
            font-size: 9px;
            line-height: 1.15;
        }

        .trending-same-section .trend-card-subtitle{
            font-size: 8px;
            margin-top: 4px;
        }

       .trending-same-section .trend-arrow-btn{
        width: 20px;
        height: 20px;
        font-size: 8px;
    }

       .trending-same-section .trend-nav{
        display: none;
    }

        .trending-same-section .prev-btn{
            left: -6px;
        }

        .trending-same-section .next-btn{
            right: -6px;
        }
    }
    </style>

<script>
function scrollTrending(direction) {
    const container = document.getElementById('trendingScroll');
    const scrollAmount = 320;
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>


<section class="location-section  custom-right location-same-section">
    <div class="container-fluid">
        <div class="border rounded-4 p-2 bg-white">

            <div class="d-flex align-items-center mb-0">
                <h4 class="fw-bold mb-0">{{ __($location_section->content->title) }}</h4>
                <span class="badge bg-danger text-white ms-2">NEW</span>
            </div>

            <p class="text-muted ">Discover popular locations around you.</p>

            <div class="trending-wrapper position-relative">
  <button class="trend-nav prev-btn" type="button">
                        <i class="las la-angle-left"></i>
                    </button>
              

                <div class="trending-scroll" >
                    @foreach($locations as $location)
                        <div class="trend-card-item">
                      <a href="javascript:void(0)"
   class="trend-card-link select-home-location"
   data-location-name="{{ $location->name }}"
   data-location-slug="{{ $location->slug }}">
                                
     <img src="{{ loadImage('assets/images/location/'.$location->image) }}"
                                  loading="lazy"   alt="{{ $location->name }}">


                                <div class="trend-card-overlay">
                                    <div>
                                        <h5 class="trend-card-title">{{ $location->name }}</h5>
                                        <div class="trend-card-subtitle">Explore Now</div>
                                    </div>

                                   
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <button class="trend-nav next-btn" type="button" >
                        <i class="las la-angle-right"></i>
                    </button>

            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.querySelector(".location-same-section .trending-wrapper");
    if (!wrapper) return;

    const scrollBox = wrapper.querySelector(".trending-scroll");
    const prevBtn = wrapper.querySelector(".prev-btn");
    const nextBtn = wrapper.querySelector(".next-btn");
    const scrollAmount = 320;

    prevBtn?.addEventListener("click", function () {
        scrollBox.scrollBy({
            left: -scrollAmount,
            behavior: "smooth"
        });
    });

    nextBtn?.addEventListener("click", function () {
        scrollBox.scrollBy({
            left: scrollAmount,
            behavior: "smooth"
        });
    });
});
</script>
<style>
.location-same-section .trending-wrapper{
    position: relative;
}

.location-same-section .trending-scroll{
    display: flex;
    gap: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    padding: 0 4px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}

.location-same-section .trending-scroll::-webkit-scrollbar{
    display: none;
}

.location-same-section .trend-card-item{
    flex: 0 0 141px;
    max-width: 145px;
}

.location-same-section .trend-card-link{
    height: 215px;
    border-radius: 18px;
    overflow: hidden;
    position: relative;
    display: block;
    color: #fff;
    text-decoration: none;
    background: #ddd;
}

.location-same-section .trend-card-link img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.location-same-section .trend-card-overlay{
    position: absolute;
    inset: 0;
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    background: linear-gradient(to bottom, rgba(0,0,0,0.14), rgba(0,0,0,0.22));
}

.location-same-section .trend-card-title{
    font-size: 14px;
    font-weight: 700;
    line-height: 1.25;
    margin: 0;
    color: #fff;
}

.location-same-section .trend-card-subtitle{
    font-size: 12px;
    color: #fff;
    margin-top: 8px;
    font-weight: 500;
}

.location-same-section .trend-arrow-btn{
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255,255,255,0.95);
    color: #2b50d9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.location-same-section .trend-nav{
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 54px;
    border: none;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.12);
    border-radius: 10px;
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #111;
    cursor: pointer;
}

.location-same-section .prev-btn{
    left: -10px;
}

.location-same-section .next-btn{
    right: -10px;
}

@media (max-width: 767px){

    .location-same-section .trending-scroll{
        gap: 8px;
        padding: 0;
    }

    .location-same-section .trend-card-item{
        flex: 0 0 calc((100% - 24px) / 4);
        max-width: calc((100% - 24px) / 4);
    }

    .location-same-section .trend-card-link{
        height: 108px;
        border-radius: 12px;
    }

    .location-same-section .trend-card-overlay{
        padding: 8px;
    }

    .location-same-section .trend-card-title{
        font-size: 9px;
        line-height: 1.15;
    }

    .location-same-section .trend-card-subtitle{
        font-size: 8px;
        margin-top: 4px;
    }

    .location-same-section .trend-arrow-btn{
        width: 22px;
        height: 22px;
        font-size: 9px;
    }

    .location-same-section .trend-nav{
        display: none;
    }
}


</style>

    <!-- =========================
     LATEST LISTINGS SECTION
========================= -->

<section class="custom-right mt-2 mb-2 ps-section popular-same-section">
    <div class="container-fluid">

        <div class="d-flex align-items-center mb-2">
            <h4 class="fw-bold mb-0">Popular Searches</h4>
        </div>

        <div class="trending-wrapper position-relative">

            <button class="trend-nav prev-btn"
                    type="button"
                    onclick="scrollPopular(-1)">
                <i class="las la-angle-left"></i>
            </button>

            <div id="popularScroll" class="trending-scroll">

           @foreach($popularCategories as $category)
                  

                    <div class="trend-card-item">

                        <a href="{{ route('listings.category', ['slug' => $category->slug]) }}"
                           class="trend-card-link dynamic-category-link"
                           data-slug="{{ $category->slug }}">

                            <img src="{{ asset('assets/images/category/'.$category->image) }}"
                                 loading="lazy"
                                 onerror="this.hidden=true;"
                                 alt="Best {{ $category->name }} Near Me">

                            <div class="trend-card-overlay">
                                <div>
                                    <h5 class="trend-card-title {{ preg_match('/\s+/', trim($category->name)) ? 'trend-card-title--words' : 'trend-card-title--single' }}">
                                        {{ $category->name }}
                                    </h5>
                                </div>
                            </div>

                        </a>

                    </div>

                @endforeach

            </div>

            <button class="trend-nav next-btn"
                    type="button"
                    onclick="scrollPopular(1)">
                <i class="las la-angle-right"></i>
            </button>

        </div>

    </div>
</section>

<script>
function scrollPopular(direction){

    const container = document.getElementById('popularScroll');

    if(!container) return;

    container.scrollBy({
        left: direction * 320,
        behavior: 'smooth'
    });
}
</script>
<style>
    .popular-same-section .trending-wrapper{
    position: relative;
}

.popular-same-section .trend-nav{
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 54px;
    border: none;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
    border-radius: 10px;
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #111;
    cursor: pointer;
}

.popular-same-section .prev-btn{
    left: -10px;
}

.popular-same-section .next-btn{
    right: -10px;
}

    .popular-same-section .trending-scroll{
    display: flex;
    gap: 12px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 0 4px;
    scrollbar-width: none;
}

.popular-same-section .trending-scroll::-webkit-scrollbar{
    display: none;
}

/* CARD SIZE */
.popular-same-section .trend-card-item{
    flex: 0 0 143px;
    max-width: 150px;
}

/* CARD */
.popular-same-section .trend-card-link{
    height: 220px;
    border-radius: 18px;
    overflow: hidden;
    position: relative;
    display: block;
    color: #fff;
    text-decoration: none;
}

.popular-same-section .trend-card-link img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* OVERLAY */
.popular-same-section .trend-card-overlay{
    position: absolute;
    inset: 0;
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    background: linear-gradient(to bottom, rgba(0,0,0,0.15), rgba(0,0,0,0.25));
}

/* TITLE */
.popular-same-section .trend-card-title{
    font-size: 14px;
    font-weight: 700;
    color: #fff;
}

/* SUBTITLE */
.popular-same-section .trend-card-subtitle{
    font-size: 12px;
    margin-top: 6px;
}

/* ARROW */
.popular-same-section .trend-arrow-btn{
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #fff;
    color: #2b50d9;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* NAV BUTTON */
.popular-same-section .trend-nav{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    width:34px;
    height:54px;
    border:none;
    background:#fff;
    box-shadow:0 2px 10px rgba(0,0,0,0.12);
    border-radius:10px;
    z-index:5;
    display:flex;
    align-items:center;
    justify-content:center;
}

.popular-same-section .prev-btn{ left:-10px; }
.popular-same-section .next-btn{ right:-10px; }

/* MOBILE */
@media (max-width: 767px){

    .popular-same-section .trend-card-item{
        flex: 0 0 95px;
        max-width: 95px;
    }

    .popular-same-section .trend-card-link{
        height: 110px;
        border-radius: 12px;
    }

    .popular-same-section .trend-card-title{
        font-size: 9px;
    }

    .popular-same-section .trend-card-subtitle{
        font-size: 8px;
    }

    .popular-same-section .trend-arrow-btn{
        width: 20px;
        height: 20px;
        font-size: 9px;
    }

    .popular-same-section .trend-nav{
        width:28px;
        height:44px;
    }
}
</style>
<style>
.trending-same-section .trending-wrapper,
.location-same-section .trending-wrapper,
.popular-same-section .position-relative{
    overflow: hidden !important;
}

.trending-same-section .trending-scroll,
.location-same-section .trending-scroll,
.popular-same-section .trending-scroll{
    display: grid !important;
    grid-auto-flow: column !important;
    grid-auto-columns: calc((100% - 108px) / 10) !important;
    gap: 12px !important;
    overflow-x: hidden !important;
    overflow-y: hidden !important;
    padding: 0 0 8px 0 !important;
}

.trending-same-section .trend-card-item,
.location-same-section .trend-card-item,
.popular-same-section .trend-card-item{
    width: 100% !important;
    max-width: 100% !important;
    flex: unset !important;
}

@media (max-width: 767px){

   .trending-same-section .trending-scroll,
    .location-same-section .trending-scroll,
    .popular-same-section .trending-scroll{
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: auto !important;
        overscroll-behavior-x: contain !important;
        scrollbar-width: none !important;
    }

    .trending-same-section .trending-scroll::-webkit-scrollbar,
    .location-same-section .trending-scroll::-webkit-scrollbar,
    .popular-same-section .trending-scroll::-webkit-scrollbar{
        display: none !important;
    }
}

@media (max-width: 991px){
    .trending-same-section .trending-scroll,
    .location-same-section .trending-scroll,
    .popular-same-section .trending-scroll{
        grid-auto-columns: calc((100% - 36px) / 4) !important;
    }
}

@media (max-width: 767px){
    .trending-same-section .trending-scroll,
    .location-same-section .trending-scroll,
    .popular-same-section .trending-scroll{
        grid-auto-columns: calc((100% - 24px) / 3) !important;
    }
}

.trending-same-section .trend-card-overlay,
.seasonal-same-section .trend-card-overlay,
.popular-same-section .trend-card-overlay{
    align-items: stretch !important;
    justify-content: flex-end !important;
}

.trending-same-section .trend-card-link img,
.seasonal-same-section .trend-card-link img,
.popular-same-section .trend-card-link img{
    color: transparent !important;
    font-size: 0 !important;
}

.trending-same-section .trend-card-overlay > div,
.seasonal-same-section .trend-card-overlay > div,
.popular-same-section .trend-card-overlay > div{
    width: 100% !important;
    height: 35px !important;
    min-height: 35px !important;
    max-height: 35px !important;
    display: block !important;
    position: relative !important;
    align-self: flex-end !important;
}

.trending-same-section .trend-card-title,
.seasonal-same-section .trend-card-title,
.popular-same-section .trend-card-title{
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    height: 35px !important;
    min-height: 35px !important;
    max-height: 35px !important;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.25 !important;
    display: block !important;
    vertical-align: top !important;
    overflow: hidden !important;
    text-align: left !important;
    word-break: normal !important;
}

.trending-same-section .trend-card-title--words,
.seasonal-same-section .trend-card-title--words,
.popular-same-section .trend-card-title--words{
    white-space: normal !important;
    text-overflow: clip !important;
    overflow-wrap: normal !important;
}

.trending-same-section .trend-card-title--single,
.seasonal-same-section .trend-card-title--single,
.popular-same-section .trend-card-title--single{
    height: 18px !important;
    min-height: 18px !important;
    max-height: 18px !important;
    white-space: nowrap !important;
    text-overflow: ellipsis !important;
    overflow-wrap: normal !important;
}

.seasonal-same-section .trend-card-subtitle{
    flex: 0 0 auto;
}

@media (max-width: 767px){
    .trending-same-section .trend-card-overlay > div,
    .seasonal-same-section .trend-card-overlay > div,
    .popular-same-section .trend-card-overlay > div{
        height: 21px !important;
        min-height: 21px !important;
        max-height: 21px !important;
    }

    .trending-same-section .trend-card-title,
    .seasonal-same-section .trend-card-title,
    .popular-same-section .trend-card-title{
        height: 21px !important;
        min-height: 21px !important;
        max-height: 21px !important;
        line-height: 1.15 !important;
    }

    .trending-same-section .trend-card-title--single,
    .seasonal-same-section .trend-card-title--single,
    .popular-same-section .trend-card-title--single{
        height: 11px !important;
        min-height: 11px !important;
        max-height: 11px !important;
    }
}
</style>

<script>
function scrollPopular(direction){
    const container=document.getElementById('popularScroll');
    const scrollAmount=350;

    container.scrollBy({
        left:direction*scrollAmount,
        behavior:'smooth'
    });
}
</script>

@foreach($seasonalSections as $section)

<section class="location-section custom-right location-same-section seasonal-same-section">
    <div class="container-fluid">

        <div class="border rounded-4 p-2 bg-white">

            <div class="d-flex align-items-center mb-0">

                <h4 class="fw-bold mb-0">
                    {{ $section->title }}
                </h4>

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

                            <a href="{{ route('listings.category', ['slug' => $category->slug]) }}"
                               class="trend-card-link dynamic-category-link"
                               data-slug="{{ $category->slug }}">

                                <img src="{{ asset('assets/images/category/'.$category->image) }}"
                                  loading="lazy"  onerror="this.hidden=true;" alt="Find {{ $category->name }} Near Me">

                                <div class="trend-card-overlay">
                                    <div>

                                        <h5 class="trend-card-title {{ preg_match('/\s+/', trim($category->name)) ? 'trend-card-title--words' : 'trend-card-title--single' }}">
                                            {{ $category->name }}
                                        </h5>

                                       
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

<section class="featured pt-4 pb-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="section-title">
                    <h4>{{ __($featured_listing->content->title) }}</h4>
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

                    // =========================
                    // REAL RATING + REVIEW COUNT
                    // =========================

                    // Preloaded in SiteController@index (withAvg/withCount) so this
                    // loop does not fire two rating queries per card.
                    $rating = (float) ($featured->verified_ratings_avg ?? 0);
                    $ratingCount = (int) ($featured->verified_ratings_count ?? 0);

                    // =========================
                    // YEARS IN BUSINESS
                    // =========================

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
                        <img src="{{ loadImage('assets/images/listing/thumb/'.$featured->thumb) }}"
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
<style>
    .listing-area {
        position: relative;
    }

   .listing-area .thumb{
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
    border-radius: 10px;
}

.listing-area .thumb img{
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;

    position: relative !important;
    inset: unset !important;

    min-width: unset !important;
    max-width: unset !important;
    min-height: unset !important;
    max-height: unset !important;
}

    .listing-area h2 {
        font-size: 18px;
        line-height: 1.4;
        margin-bottom: 8px;
        color: #262630;
    }

    .listing-area h2 a {
        color: inherit;
        text-decoration: none;
    }

    .listing-star-icon {
        width: 18px;
        height: 18px;
        object-fit: contain;
        margin-right: 6px;
        vertical-align: middle;
        flex-shrink: 0;
    }

    .jd-rating-wrap {
        gap: 8px;
    }

    .jd-rating-badge {
        background: #1bbf73;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
    }

    .jd-rating-count {
        font-size: 12px;
        color: #555;
    }

    .jd-badge-img {
        height: 18px;
        object-fit: contain;
    }

    .listing-location {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        color: #666;
        font-size: 14px;
    }

    .dot-separator {
        margin: 0 4px;
        font-weight: 700;
        color: #555;
    }

    .business-years {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        color: #000;
        font-weight: 400;
    }

    .listing-contact {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .listing-contact a {
        flex: 1;
        justify-content: center;
        text-align: center;
    }

    .btn-call {
        background: #138808;
        color: #fff;
        padding: 8px 0;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-call:hover {
        background: #0f6e06;
        color: #fff;
    }

    .btn-whatsapp {
        background: #f8f9fa;
        border: 1px solid #cfcfcf;
        color: #333;
        padding: 8px 0;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-whatsapp i {
        color: #25D366;
    }

    .btn-whatsapp:hover {
        background: #e9ecef;
        color: #333;
    }

    @media (max-width: 767px) {
        .listing-area .thumb {
            height: 190px;
        }

        .listing-area h2 {
            font-size: 16px;
        }

        .jd-rating-badge {
            font-size: 11px;
            padding: 2px 6px;
        }

        .jd-rating-count {
            font-size: 11px;
        }

        .jd-badge-img {
            height: 16px;
        }

        .business-years {
            font-size: 12px;
        }

        .listing-contact {
            gap: 8px;
        }
    }
</style>

<style>.latest-scroll-section .tabs-wrapper {
    display: flex;
    align-items: center;
}

.latest-scroll-section .tabs-scroll {
    overflow-x: auto;
    flex: 1;
}

.latest-scroll-section .tabs-scroll::-webkit-scrollbar {
    display: none;
}

.latest-scroll-section .nav-tabs {
    display: flex;
    flex-wrap: nowrap;
    width: max-content;      /* Prevent cut */
}

.latest-scroll-section .nav-item {
    flex: 0 0 auto;          /* Prevent shrink */
}

.latest-scroll-section .nav-link {
    white-space: nowrap;
}

.latest-scroll-section .arrow-btn {
    border: 1px solid #dee2e6;
    background: #fff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    flex-shrink: 0;
    margin: 0 8px;
    cursor: pointer;
}</style>


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
<img src="{{ loadImage('assets/images/listing/thumb/'.$catListing->thumb) }}" alt="{{ $catListing->name }}" class="listing-thumb" loading="lazy">
</div>

<div class="listing-content">

<div class="listing-author">
<div class="author-thumb">
<a href="{{ route('listings.author',$catListing->user->id) }}">
<img src="{{ loadImage('assets/images/user/'.$catListing->user->image) }}" alt="{{ $catListing->user->name ?? 'Business owner' }}" loading="lazy">
</a>
</div>
</div>

<h1 class="title">
<a href="{{ route('listings.details',$catListing->slug) }}">
{{ __($catListing->name) }}
</a>
</h1>

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

<a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn-whatsapp">
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
                    <a href="{{ $hbb->link ?? '#' }}" target="_blank">
                        <picture>
                            @if(!empty($hbb->mobile_banner))
                                <source media="(max-width: 767px)"
                                        srcset="{{ asset('assets/images/category/hbb/' . $hbb->mobile_banner) }}">
                            @endif

                            <img src="{{ asset('assets/images/category/hbb/' . $hbb->desktop_banner) }}"
                                 class="d-block w-100 category-banner-img"
                             loading="lazy"    alt="{{ $hbb->title ?? 'Banner' }}">
                        </picture>
                    </a>
                </div>
            @endforeach
        </div>

       

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bannerCarousel = document.querySelector('#categoryBannerCarousel');
    if (bannerCarousel) {
        new bootstrap.Carousel(bannerCarousel, {
            interval: 2000,
            ride: 'carousel',
            wrap: true,
            pause: false
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  

   

    
    <!-- Blog section start -->
    <section class="blog-section pb-4 pt-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="section-title">
                        <h4>{{ __($blog_section->content->title) }}</h4>
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
                            <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) }}" loading="lazy" alt="blog">
                        </div>
                       
                       <div class="content-area">
    <h5>
        <a href="{{ route('blog.details', $blog->content->slug) }}">
            {{ __($blog->content->title) }}
        </a>
    </h5>

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


        <style>
            .cod{
                font-size: 18px;
    font-weight: 700;
            }
           .spn {
                line-height: 1.67;
                margin-top: 15px;
                font-size: 12px;
    
color: #555;
            }
            .bgl{
                font-size: 16px;
    font-weight: 600;
            }
        </style>
        @if(isset($homeContentSections) && $homeContentSections->count() > 0)

<div class="home-top-content-section mb-4">

    @foreach($homeContentSections as $homeContent)

        <div class="home-top-content-box">
         <h4 class="cod mb-0">
            {{ $homeContent->title }}
            </h4>
            <div class="home-top-content-text rich-content">
              {!! \App\Support\ContentHtml::cleanCached($homeContent->content ?? '') !!}
            </div>

        </div>

    @endforeach

</div>

@endif

        <div class="category-content-heading mb-4">
            <h4 class=" cod mb-0">
                Explore Popular Services That Can Help You In Your Daily Needs :
            </h4>
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
                        <img src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                             loading="lazy"
                             alt="{{ $categoryItem->name }} Near Me">
                    @elseif(!empty($categoryItem->icon) && Str::startsWith($categoryItem->icon, ['fa', 'fas', 'far', 'fab', 'las', 'la']))
                        <i class="{{ $categoryItem->icon }}"></i>
                    @else
                        <span>{{ $categoryItem->icon ?? '📌' }}</span>
                    @endif
                </span>

                <h5 class="category-content-title mb-0">
                    <a href="{{ !empty($currentCitySlug) ? url($currentCitySlug . '/' . $categoryItem->slug) : 'javascript:void(0)' }}"
                       class="text-dark dynamic-category-link"
                       data-slug="{{ $categoryItem->slug }}">

                        {{ $categoryItem->name }}
                    </a>
                </h5>

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

{{-- Popular Categories / Popular Cities now live once in the shared footer
     (resources/views/themes/servicebazzar/includes/footer.blade.php), not
     duplicated here — see popular_categories_ajax.blade.php / popular_cities.blade.php --}}

<style>
#homeLocationSuggestions::-webkit-scrollbar{
    width: 6px;
}

#homeLocationSuggestions::-webkit-scrollbar-track{
    background: transparent;
    margin: 8px 0;
}

#homeLocationSuggestions::-webkit-scrollbar-thumb{
    background: #bdbdbd;
    border-radius: 20px;
    border: 2px solid #fff;
}
</style>
</section>
<style>

.category-content-bottom p {
    font-size: 13px;
    font-weight: 600;
    color: #222;
    line-height: 1.5;
}

@media (max-width: 767px) {
    .category-content-bottom {
        margin-top: 18px !important;
    }

    .category-content-bottom p {
        font-size: 12px;
        line-height: 1.45;
    }
}

    .category-content-heading h4 {
    font-size: 20px;
    font-weight: 500;
    color: #333;
    line-height: 1.4;
}

.category-content-row {
    row-gap: 36px;
}

.category-content-card {
    display: block;
    color: #4b4b4b;
    min-height: 155px;
}

.category-content-title-wrap {
    gap: 10px;
    margin-bottom: 12px;
}

.category-content-icon {
    width: 26px;
    min-width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #555;
    font-size: 22px;
    line-height: 1;
}

.category-content-icon img {
    width: 24px;
    height: 24px;
    object-fit: contain;
    display: block;
}

.category-content-icon i {
    font-size: 22px;
    line-height: 1;
    color: #555;
}

.category-content-title {
    font-size: 16px;
    font-weight: 500;
    color: #111;
   
}

.category-content-desc {
    font-size: 14px;
    font-weight: 400;
    color: #4b5563;
    line-height: 1.55;
    max-width: 285px;
}

@media (max-width: 767px) {
    .category-content-heading {
        margin-bottom: 18px !important;
    }

    .category-content-heading h4 {
        font-size: 16px;
        line-height: 1.4;
    }

    .category-content-row {
        row-gap: 24px;
    }

    .category-content-title {
        font-size: 15px;
    }

    .category-content-desc {
        font-size: 13px;
        line-height: 1.5;
    }
}

    .blog-area .thumb {
    width: 100%;
    height: 220px; /* fix height */
    overflow: hidden;
    border-radius: 8px;
}.blog-area .thumb img {
    width: 100%;
    height: 100%;
        object-fit: inherit;
}


/* Mobile + Tablet me home location/category input hide */
@media (max-width: 991px){

    .banner-section .search-area,
    .banner-section #homeSearchForm,
    .banner-section .search-row-custom,
    #mobileHomeLocationBox,
    .search-col-custom{
        display: none !important;
    }

    .banner-section{
        padding-top: 0 !important;
    }
}
</style>
    <script>
/* The panel's open/close state now lives on #categoryPopupToggle so that a
 * label click alone opens it — no JS required. openCategoryPopup() /
 * closeCategoryPopup() are defined once further down and just drive that
 * checkbox; the overlay is itself a <label> for it, so it needs no handler.
 * Escape still has to be wired up by hand. */
document.addEventListener('keydown',function(e){
    if(e.key!=="Escape") return;

    const toggle=document.getElementById('categoryPopupToggle');
    if(toggle && toggle.checked){
        toggle.checked=false;
        toggle.dispatchEvent(new Event('change',{bubbles:true}));
    }
});

const searchInput=document.getElementById('categorySearch');
const items=document.querySelectorAll('#popupCategoryList .popup-item');
const noResult=document.getElementById('noResult');

searchInput.addEventListener('keyup',function(){
    let value=this.value.toLowerCase();
    let found=false;

    items.forEach(item=>{
        let text=item.innerText.toLowerCase();
        if(text.includes(value)){
            item.style.display="flex";
            found=true;
        }else{
            item.style.display="none";
        }
    });

    noResult.style.display=found?"none":"block";
});

function clearSearch(){
    searchInput.value="";
    searchInput.dispatchEvent(new Event('keyup'));
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function makeDropdownGlobal(inputId, dropdownId) {

        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);

        if (!input || !dropdown) return;

        if (dropdown.parentNode !== document.body) {
            document.body.appendChild(dropdown);
        }

        function updatePosition() {

            const rect = input.getBoundingClientRect();

            dropdown.style.position = "fixed";
            dropdown.style.top = (rect.bottom + 8) + "px";
            dropdown.style.left = rect.left + "px";
            dropdown.style.zIndex = "999999";
            dropdown.style.boxSizing = "border-box";

            // LOCATION DROPDOWN
            if (dropdownId === "homeLocationSuggestions") {

                dropdown.style.width = "480px";
                dropdown.style.borderRadius = "10px";
                dropdown.style.paddingRight = "10px";

            }

           else if (dropdownId === "homeCategorySuggestions") {

    const searchWrap = input.closest(".search-col-custom");

    if (searchWrap) {
        const wrapRect = searchWrap.getBoundingClientRect();

        dropdown.style.width = wrapRect.width + "px";
        dropdown.style.left = wrapRect.left + "px";
    } else {
        dropdown.style.width = rect.width + "px";
    }

    dropdown.style.borderRadius = "10px";
    dropdown.style.paddingRight = "10px";
}

        }

        input.addEventListener("focus", updatePosition);
        input.addEventListener("input", updatePosition);

        window.addEventListener("scroll", function () {
            if (dropdown.classList.contains("show")) {
                updatePosition();
            }
        }, true);

        window.addEventListener("resize", function () {
            if (dropdown.classList.contains("show")) {
                updatePosition();
            }
        });

    }

    makeDropdownGlobal(
        "homeLocationInput",
        "homeLocationSuggestions"
    );

    makeDropdownGlobal(
        "homeCategorySearch",
        "homeCategorySuggestions"
    );

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const locationInput = document.getElementById("homeLocationInput");
    const latitudeInput = document.getElementById("homeSearchLatitude");
    const longitudeInput = document.getElementById("homeSearchLongitude");

    if (!locationInput || !latitudeInput || !longitudeInput) return;

    if (locationInput.value.trim() !== '') return;

    const fromPopularCity = localStorage.getItem("jf_from_popular_city") === "1";

    let savedCity = "";
    let savedLat = "";
    let savedLng = "";

    if (fromPopularCity) {

        // Home par detected location dikhao
        savedCity = localStorage.getItem("jf_detected_city") || localStorage.getItem("jf_city");
        savedLat = localStorage.getItem("jf_lat");
        savedLng = localStorage.getItem("jf_lng");

        // Flag remove kar do
        localStorage.removeItem("jf_from_popular_city");

    } else {

        // Normal behaviour
        savedCity =
            localStorage.getItem("jf_selected_location_name") ||
            localStorage.getItem("jf_detected_city") ||
            localStorage.getItem("jf_city");

        savedLat =
            localStorage.getItem("jf_selected_lat") ||
            localStorage.getItem("jf_lat");

        savedLng =
            localStorage.getItem("jf_selected_lng") ||
            localStorage.getItem("jf_lng");
    }

    if (savedCity) {
        locationInput.value = savedCity;

        if (savedLat) latitudeInput.value = savedLat;
        if (savedLng) longitudeInput.value = savedLng;

        return;
    }

    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(
        async function (position) {

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            latitudeInput.value = lat;
            longitudeInput.value = lng;

            try {

                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat=${lat}&lon=${lng}`,
                    {
                        headers: {
                            "Accept": "application/json"
                        }
                    }
                );

                const data = await response.json();

                const address = data.address || {};

                const city = window.normalizeEnglishLocationName
                    ? window.normalizeEnglishLocationName(
                        address.city ||
                        address.town ||
                        address.village ||
                        address.state_district ||
                        address.state ||
                        ''
                    )
                    : (
                        address.city ||
                        address.town ||
                        address.village ||
                        address.state_district ||
                        address.state ||
                        ''
                    );
localStorage.setItem("jf_detected_city", city);
                if (city) {

                    locationInput.value = city;

                    // Detected location save karo
localStorage.setItem("jf_detected_city", city);
localStorage.setItem("jf_city", city);
localStorage.setItem("jf_lat", lat);
localStorage.setItem("jf_lng", lng);
                }

            } catch (error) {

                console.error("Location detect failed:", error);

            }

        },
        function (error) {

            console.log("User denied or location unavailable", error);

        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000
        }
    );

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const locationInput = document.getElementById("homeLocationInput");
    const latitudeInput = document.getElementById("homeSearchLatitude");
    const longitudeInput = document.getElementById("homeSearchLongitude");

    function slugify(text) {
        return String(text || '')
            .toLowerCase()
            .trim()
            .replace(/,/g, '')
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // LOCATION CARD CLICK
    document.querySelectorAll('.select-home-location').forEach(card => {
        card.addEventListener('click', function (e) {
            e.preventDefault();

            const locationName = this.dataset.locationName || '';

            if (locationInput) {
                locationInput.value = locationName;
            }

            localStorage.setItem('jf_selected_location_name', locationName);
            localStorage.removeItem('jf_city');

            if (latitudeInput) latitudeInput.value = '';
            if (longitudeInput) longitudeInput.value = '';

            localStorage.removeItem('jf_selected_lat');
            localStorage.removeItem('jf_selected_lng');

            console.log('Selected location:', locationName);

            const bannerSection = document.querySelector('.banner-section');
            if (bannerSection) {
                bannerSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // LOAD CURRENT LOCATION
const isHome =
    window.location.pathname === "/" ||
    window.location.pathname === "";

if (isHome) {

    const detectedCity = localStorage.getItem("jf_detected_city") || "";
    const detectedLat  = localStorage.getItem("jf_lat") || "";
    const detectedLng  = localStorage.getItem("jf_lng") || "";

    if (detectedCity) {

        locationInput.value = detectedCity;
        locationInput.dataset.selectedLocation = "1";

        if (latitudeInput) latitudeInput.value = detectedLat;
        if (longitudeInput) longitudeInput.value = detectedLng;

    }

} else {

    const savedLocationName = localStorage.getItem("jf_selected_location_name");
    const savedLat = localStorage.getItem("jf_selected_lat");
    const savedLng = localStorage.getItem("jf_selected_lng");

    if (savedLocationName) {
        locationInput.value = savedLocationName;
    }

    if (savedLat) {
        latitudeInput.value = savedLat;
    }

    if (savedLng) {
        longitudeInput.value = savedLng;
    }

}
// CATEGORY CLICK -> PRETTY URL
document.querySelectorAll('.dynamic-category-link').forEach(function (link) {

    link.addEventListener('click', function (e) {

        // preventDefault() is deliberately NOT called up here. If anything
        // below cannot produce a city-prefixed URL (location denied, never
        // set, or cleared) the click must fall through to the anchor's own
        // href instead of being swallowed and landing on "#".
        const categorySlug = this.dataset.slug || "";

        if (!categorySlug) {
            return;
        }

       let locationText = "";

if (locationInput) {
    locationText = locationInput.value.trim();
}

if (!locationText) {
    locationText =
        localStorage.getItem("jf_detected_city") ||
        localStorage.getItem("jf_selected_location_name") ||
        "";
}

        // Split Area, City
        let city = "";
        let area = "";

        if (locationText.includes(",")) {

            const parts = locationText.split(",");

            area = parts[0].trim();
            city = parts[1].trim();

        } else {

            city = locationText.trim();

        }

        if (!city) {
            // No city to build a pretty URL from — let the browser follow
            // the server-rendered href, which redirects to the right city.
            return;
        }

        let finalUrl =
            `${window.location.origin}/${slugify(city)}/${categorySlug}`;

        if (area !== "") {
            finalUrl += `-in-${slugify(area)}`;
        }

        e.preventDefault();
        window.location.href = finalUrl;

    });




});
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const categoryInput = document.getElementById("homeCategorySearch");
    if (!categoryInput) return;

   const placeholders = [
    "Search for Packers and Movers",
    "Search for Spa & Salons",
    "Search for Real Estate Agents",
    "Search for Gym Centers",
    "Search for Restaurants",
    "Search for Placement Services"
];

    let currentIndex = 0;

    function updatePlaceholder() {
        if (categoryInput.value.trim() === "") {
            categoryInput.setAttribute("placeholder", placeholders[currentIndex]);
            currentIndex = (currentIndex + 1) % placeholders.length;
        }
    }

    updatePlaceholder();
    setInterval(updatePlaceholder, 2000);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.listing-area').forEach(function(card) {

        card.addEventListener('click', function(e) {

            // agar button/link pe click hua to ignore kare
            if (e.target.closest('a')) return;

            let url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });

    });

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.blog-area').forEach(function(card) {

        card.addEventListener('click', function(e) {

            if (e.target.closest('a')) return;

            let url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });

    });

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function slugify(text) {
        return String(text || "")
            .toLowerCase()
            .trim()
            .replace(/,/g, "")
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9-]/g, "")
            .replace(/-+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    function getSelectedLocation() {
        const homeInput = document.getElementById("homeLocationInput");
        const headerInput = document.getElementById("locationInput");

        let locationName = "";

        if (homeInput && homeInput.value.trim() !== "") {
            locationName = homeInput.value.trim();
        } else if (headerInput && headerInput.value.trim() !== "") {
            locationName = headerInput.value.trim();
        } else {
            locationName = localStorage.getItem("jf_city") || "";
        }

        return locationName;
    }

   

    updateCategoryLinks();

    const homeInput = document.getElementById("homeLocationInput");
    const headerInput = document.getElementById("locationInput");

    [homeInput, headerInput].forEach(function (input) {
        if (!input) return;

        input.addEventListener("input", updateCategoryLinks);
        input.addEventListener("change", updateCategoryLinks);
    });

    document.addEventListener("mousedown", function () {
        setTimeout(updateCategoryLinks, 200);
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("homeCategorySearch");
    const clearBtn = document.getElementById("customClearBtn");

    function toggleClearBtn() {

        if(input.value.trim() !== ""){
            clearBtn.style.display = "block";
        }else{
            clearBtn.style.display = "none";
        }
    }

    input.addEventListener("input", toggleClearBtn);

    clearBtn.addEventListener("click", function () {

        input.value = "";   
        clearBtn.style.display = "none";

        input.focus();

        const dropdown = document.getElementById("homeCategorySuggestions");

        if(dropdown){
            dropdown.classList.remove("show");
            dropdown.innerHTML = "";
        }
    });

    toggleClearBtn();

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const voiceBtn = document.getElementById("homeVoiceSearchBtn");
    const categoryInput = document.getElementById("homeCategorySearch");
    const clearBtn = document.getElementById("customClearBtn");

    const voiceModal = document.getElementById("voiceModal");
    const voiceCloseBtn = document.getElementById("voiceCloseBtn");
    const voiceStatusText = document.getElementById("voiceStatusText");

    if (!voiceBtn || !categoryInput) return;

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert("Voice search not supported in this browser");
        return;
    }

    let recognition;

    function closeVoiceModal() {
        if (voiceModal) voiceModal.classList.remove("show");
        voiceBtn.classList.remove("listening");
    }

    voiceBtn.addEventListener("click", function (e) {
        e.preventDefault();

        recognition = new SpeechRecognition();
        recognition.lang = "en-IN";
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        if (voiceStatusText) voiceStatusText.innerText = "Listening.....";
        if (voiceModal) voiceModal.classList.add("show");

        voiceBtn.classList.add("listening");

        recognition.start();

        recognition.onresult = function (event) {
            const text = event.results[0][0].transcript;
            categoryInput.value = text;
            if (clearBtn) clearBtn.style.display = "block";
            categoryInput.dispatchEvent(new Event("input", { bubbles: true }));
            setTimeout(closeVoiceModal, 700);
        };

        recognition.onerror = function () {
            if (voiceStatusText) voiceStatusText.innerText = "Please try again";
            setTimeout(closeVoiceModal, 900);
        };

        recognition.onend = function () {
            setTimeout(closeVoiceModal, 700);
        };
    });

    if (voiceCloseBtn) {
        voiceCloseBtn.addEventListener("click", function () {
            try { if (recognition) recognition.stop(); } catch (e) {}
            closeVoiceModal();
        });
    }
});
</script>


<script>
function clearHomeCategoryInput() {
    const input = document.getElementById("homeCategorySearch");
    const dropdown = document.getElementById("homeCategorySuggestions");

    if (input) {
        input.value = "";
        input.defaultValue = "";
        input.setAttribute("autocomplete", "new-password");
    }

    if (dropdown) {
        dropdown.innerHTML = "";
        dropdown.classList.remove("show");
    }
}

document.addEventListener("DOMContentLoaded", clearHomeCategoryInput);

window.addEventListener("pageshow", function () {
    clearHomeCategoryInput();
});

let popupScrollY = 0;

/*
 * #categoryPopupToggle is the single source of truth for "is the panel
 * open" — CSS opens the overlay and the panel straight off :checked, so the
 * panel works with JS disabled. This function only layers the JS-only
 * extras (background blur + scroll lock) on top of that state.
 */
function applyCategoryPopupState(isOpen){
    const panel   = document.getElementById('categoryPopup');
    const overlay = document.getElementById('popupOverlay');

    if(isOpen){
        popupScrollY = window.scrollY;

        if(overlay) overlay.classList.add('active');
        if(panel)   panel.classList.add('active');

        document.documentElement.classList.add('popup-open');
        document.body.classList.add('popup-open');
        document.body.style.top = `-${popupScrollY}px`;

        // Opening via openCategoryPopup() sets .checked without firing
        // 'change', so re-check the location strip from here as well.
        if(typeof window.refreshPopupLocationNotice === 'function'){
            window.refreshPopupLocationNotice();
        }
    } else {
        if(overlay) overlay.classList.remove('active');
        if(panel)   panel.classList.remove('active');

        document.documentElement.classList.remove('popup-open');
        document.body.classList.remove('popup-open');
        document.body.style.top = '';

        window.scrollTo(0, popupScrollY);
    }
}

// Assigning .checked from script does not fire 'change', so these two can
// drive the checkbox without re-entering the change handler below.
function openCategoryPopup(){
    const toggle = document.getElementById('categoryPopupToggle');
    if(toggle) toggle.checked = true;
    applyCategoryPopupState(true);
}

function closeCategoryPopup(){
    const toggle = document.getElementById('categoryPopupToggle');
    if(toggle) toggle.checked = false;
    applyCategoryPopupState(false);
}

document.addEventListener('DOMContentLoaded', function(){
    const toggle = document.getElementById('categoryPopupToggle');

    if(toggle){
        toggle.addEventListener('change', function(){
            applyCategoryPopupState(this.checked);
        });

        // Browsers restore checkbox state on a back-navigation, so pick up
        // an already-open panel instead of leaving the blur/lock off.
        if(toggle.checked) applyCategoryPopupState(true);
    }

    // The trigger and close control are <label>s (that is what makes them
    // work without JS), and a label is not natively focusable — give them
    // Enter/Space so keyboard users still get button behaviour.
    document.querySelectorAll('label.more-btn, label.close-btn').forEach(function(el){
        el.addEventListener('keydown', function(ev){
            if(ev.key === 'Enter' || ev.key === ' '){
                ev.preventDefault();
                el.click();
            }
        });
    });

    initPopupLocationNotice(toggle);
});

// Back-navigation restores the checkbox (and can restore the body classes)
// from bfcache without firing DOMContentLoaded, so re-sync the JS-only
// extras against whatever state the checkbox came back in.
window.addEventListener('pageshow', function(){
    const toggle = document.getElementById('categoryPopupToggle');
    if(toggle) applyCategoryPopupState(toggle.checked);
});

/*
 * "Location off" strip inside the panel.
 *
 * Category links work either way — their href falls back to a server-side
 * redirect — but without a city the visitor lands on the default city's
 * listings instead of their own, and there was nothing on screen saying so.
 * This surfaces that state at the moment they are picking a category, and
 * offers the permission prompt right there.
 */
function initPopupLocationNotice(toggle){
    const notice  = document.getElementById('popupLocationNotice');
    const message = document.getElementById('popupLocationNoticeMsg');
    const button  = document.getElementById('popupLocationEnableBtn');
    const steps   = document.getElementById('popupLocationSteps');
    const close   = document.getElementById('popupLocationNoticeClose');
    let dismissed = false;

    if(!notice || !message || !button) return;

    const DEFAULT_MSG = 'Please turn on location to see services near you.';
    const DENIED_MSG  = 'Location permission is blocked. Allow location for this site, then tap the button again.';

    function knownCity(){
        const input = document.getElementById('homeLocationInput')
                   || document.getElementById('locationInput');

        if(input && input.value.trim() !== '') return input.value.trim();

        return (localStorage.getItem('jf_selected_location_name')
             || localStorage.getItem('jf_detected_city')
             || localStorage.getItem('jf_city')
             || '').trim();
    }

    // mode: 'enable'  -> the browser will still show its prompt
    //       'blocked'  -> only the browser's own settings can undo it
    //       'done'     -> location is sorted, confirm it and stop nagging
    //       'none'     -> nothing actionable, message only
    function setState(text, mode){
        message.textContent = text;

        button.hidden   = (mode === 'none');
        button.disabled = false;
        button.textContent = (mode === 'blocked') ? 'Try again' : 'Turn on location';

        if(steps) steps.hidden = (mode !== 'blocked');

        notice.classList.toggle('popup-location-notice--done', mode === 'done');
        notice.hidden = false;
    }

    function refresh(){
        if(knownCity()){
            notice.hidden = true;
            return;
        }

        if(dismissed) return;

        if(!navigator.geolocation){
            setState('This browser does not support location. Please pick your city from the search box above.', 'none');
            return;
        }

        setState(DEFAULT_MSG, 'enable');

        // permissions.query is advisory only (Safari lacks it) — the click
        // handler still deals with a denial on its own.
        if(navigator.permissions && navigator.permissions.query){
            navigator.permissions.query({ name: 'geolocation' }).then(function(status){
                if(status.state === 'denied' && !knownCity()){
                    setState(DENIED_MSG, 'blocked');
                }

                // If they flip it in browser settings while the tab is open,
                // drop the notice without needing a reload.
                status.onchange = function(){
                    if(status.state === 'granted') button.click();
                    else if(status.state === 'denied' && !knownCity()) setState(DENIED_MSG, 'blocked');
                };
            }).catch(function(){ /* ignore — keep the default prompt */ });
        }
    }

    function applyCity(city, lat, lng){
        localStorage.setItem('jf_detected_city', city);
        localStorage.setItem('jf_city', city);
        localStorage.setItem('jf_lat', lat);
        localStorage.setItem('jf_lng', lng);

        const input = document.getElementById('homeLocationInput')
                   || document.getElementById('locationInput');

        if(input){
            input.value = city;
            input.dataset.selectedLocation = '1';
        }

        const latInput = document.getElementById('homeSearchLatitude');
        const lngInput = document.getElementById('homeSearchLongitude');

        if(latInput) latInput.value = lat;
        if(lngInput) lngInput.value = lng;

        fetch("{{ route('save.user.location') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                city: city,
                latitude: lat,
                longitude: lng
            })
        }).catch(function(){});

        if(typeof loadPopularCategoriesByLocation === 'function'){
            loadPopularCategoriesByLocation(city, lat, lng, "");
        }
    }

    if(close){
        close.addEventListener('click', function(){
            dismissed = true;
            notice.hidden = true;
        });
    }

    button.addEventListener('click', function(){
        if(!navigator.geolocation) return;

        button.disabled = true;
        button.textContent = 'Getting location…';

        navigator.geolocation.getCurrentPosition(async function(position){
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat=${lat}&lon=${lng}`,
                    { headers: { "Accept": "application/json" } }
                );

                const data    = await response.json();
                const address = data.address || {};

                const city = window.normalizeEnglishLocationName
                    ? window.normalizeEnglishLocationName(
                        address.city
                        || address.town
                        || address.village
                        || address.state_district
                        || address.state
                        || ''
                    )
                    : (
                        address.city
                        || address.town
                        || address.village
                        || address.state_district
                        || address.state
                        || ''
                    );

                if(city){
                    applyCity(city, lat, lng);
                    setState('Showing services in ' + city + '.', 'done');
                    window.setTimeout(function(){
                        notice.hidden = true;
                    }, 1200);
                    return;
                }

                setState('We could not work out your city. Please pick it from the search box above.', 'none');

            } catch (error) {
                console.error('Location detect failed:', error);
                setState('We could not detect your location. Please try again.', 'enable');
            }

        }, function(error){
            if(error && error.code === error.PERMISSION_DENIED){
                setState(DENIED_MSG, 'blocked');
            } else if(error && error.code === error.TIMEOUT){
                setState('Getting your location took too long. Please try again.', 'enable');
            } else {
                setState('We could not get your location. Please try again.', 'enable');
            }
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 });
    });

    window.refreshPopupLocationNotice = refresh;

    // Re-check every time the panel opens: the city may have been detected
    // or chosen since the page loaded.
    if(toggle){
        toggle.addEventListener('change', function(){
            if(this.checked){
                dismissed = false;
                refresh();
            }
        });

        if(toggle.checked) refresh();
    } else {
        refresh();
    }
}
</script>
<script>
document.addEventListener("click", function(e){

    const tab = e.target.closest(".popular-cat-tab");
    if (!tab) return;

    const wrapper = tab.closest(".popular-category-section");
    if (!wrapper) return;

    const targetId = tab.dataset.tab;

    wrapper.querySelectorAll(".popular-cat-tab").forEach(btn => {
        btn.classList.remove("active");
    });

    wrapper.querySelectorAll(".popular-subcat-box").forEach(box => {
        box.classList.remove("active");
    });

    tab.classList.add("active");

    const targetBox = wrapper.querySelector("#" + targetId);
    if (targetBox) {
        targetBox.classList.add("active");
    }
});
</script>
<script>
document.addEventListener("click", function (e) {
    const cityLink = e.target.closest(".popular-city-link");

    if (!cityLink) {
        return;
    }

    e.preventDefault();

    const city = cityLink.dataset.city || "";

    if (!city) {
        return;
    }

    /*
     * Popular City sirf city page open karne ke liye hai.
     * Isko actual selected/detected location ke roop me save nahi karna.
     */
    localStorage.removeItem("jf_selected_location_name");
    localStorage.removeItem("jf_selected_lat");
    localStorage.removeItem("jf_selected_lng");
    localStorage.removeItem("jf_selected_location_id");

    /*
     * Ye flag batayega ki user Popular City page se
     * root home par wapas aa raha hai.
     */
    localStorage.setItem("jf_from_popular_city", "1");

    // Actual detected location ko change kiye bina city page open karo
    window.location.href = cityLink.href;
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const scrollBox = document.getElementById("trendingScroll");
    if (!scrollBox) return;

    let startX = 0;
    let startY = 0;

    scrollBox.addEventListener("touchstart", function (e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });

    scrollBox.addEventListener("touchmove", function (e) {
        const diffX = Math.abs(e.touches[0].clientX - startX);
        const diffY = Math.abs(e.touches[0].clientY - startY);

        if (diffY > diffX) {
            scrollBox.style.overflowX = "hidden";
        } else {
            scrollBox.style.overflowX = "auto";
        }
    }, { passive: true });

    scrollBox.addEventListener("touchend", function () {
        scrollBox.style.overflowX = "auto";
    }, { passive: true });
});

function citySlug(city) {
    if (!city) return "";
    
    
    const parts = city.split(",");
    const lastPart = parts[parts.length - 1].trim(); 
    
    return lastPart.toLowerCase().replace(/\s+/g, "-");
    
}
function updateCategoryLinks(city) {
   
    if (!city) {
        const locationInput = document.getElementById("homeLocationInput");
        if (locationInput) {
            city = locationInput.value.trim();  
        }
    }
    
  
    if (!city) {
        city = localStorage.getItem("jf_city") || 
               localStorage.getItem("jf_selected_location_name") || "";
    }
    
}
const homeLocationInput = document.getElementById("homeLocationInput");


homeLocationInput.addEventListener("input", function() {
    updateCategoryLinks(this.value.trim());
});

homeLocationInput.addEventListener("change", function() {
    updateCategoryLinks(this.value.trim());
});

homeLocationInput.addEventListener("blur", function() {
    updateCategoryLinks(this.value.trim());
});

const initialLocation = document.getElementById("homeLocationInput");
if (initialLocation && initialLocation.value.trim()) {
    updateCategoryLinks(initialLocation.value.trim());
} else {
 
    const savedCity = localStorage.getItem("jf_city") || "";
    if (savedCity) {
        updateCategoryLinks(savedCity);
    }
}

</script>   

<script>
const buyerPopupImage = new Image();
buyerPopupImage.src = "{{ asset('assets/servicebazzar.png') }}";
</script>
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
<a href="javascript:void(0);"
   onclick="openSbAuthPopup()"
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

<style>
#buyerPopup.buyer-popup{
    position: fixed;
    right: 80px;
    bottom: 30px;
    width: 390px;
    height: 200px;
    padding: 28px;
    border-radius: 12px;
    overflow: hidden;
    background: url("{{ asset('assets/servicebazzar.png?v=1') }}") no-repeat center center;
background-size: cover;
    background-size: cover;
    color: #fff;
    z-index: 999999;
    display: none;
    opacity: 0;
    transform: translateY(35px) scale(.9);
}

#buyerPopup.buyer-popup.buyer-popup-show{
    animation: sbBuyerPopupShow .55s cubic-bezier(.22,1,.36,1) forwards;
}

@keyframes sbBuyerPopupShow{
    from{
        opacity: 0;
        transform: translateY(35px) scale(.9);
    }
    to{
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

#buyerPopup .buyer-content{
    position: relative;
    z-index: 2;
}

#buyerPopup .buyer-content h3,
#buyerPopup .buyer-content p,
#buyerPopup .buyer-content .buyer-btn{
    opacity: 0;
    transform: translateX(-25px);
}

#buyerPopup.buyer-popup-show .buyer-content h3{
    animation: sbBuyerTextSlide .5s ease forwards;
    animation-delay: .2s;
}

#buyerPopup.buyer-popup-show .buyer-content p{
    animation: sbBuyerTextSlide .5s ease forwards;
    animation-delay: .45s;
}

#buyerPopup.buyer-popup-show .buyer-content .buyer-btn{
    animation: sbBuyerTextSlide .5s ease forwards;
    animation-delay: .7s;
}

@keyframes sbBuyerTextSlide{
    from{
        opacity: 0;
        transform: translateX(-25px);
    }
    to{
        opacity: 1;
        transform: translateX(0);
    }
}

#buyerPopup h3{
    font-size: 20px;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 15px;
}

#buyerPopup p{
    font-size: 14px;
    font-weight: 400;
    line-height: 1.5;
    margin-bottom: 20px;
}

#buyerPopup .buyer-btn{
    display: inline-block;
    background: #ffd54a;
    color: #000;
    text-decoration: none;
    padding: 10px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
    transition: .3s;
}

#buyerPopup .buyer-btn:hover{
    transform: translateY(-2px);
    color: #000;
    text-decoration: none;
}

#buyerPopup .buyer-close{
    position: absolute;
    top: 0;
    right: 0;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: #fff;
    color: #111;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    z-index: 5;
}

@media(max-width: 767px){
    #buyerPopup.buyer-popup{
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    setTimeout(function () {

        const popup = document.getElementById("buyerPopup");
        if (!popup) return;

        if (window.innerWidth <= 767) return;

        popup.style.display = "block";

        requestAnimationFrame(function () {
            popup.classList.add("buyer-popup-show");
        });

    }, 3000);

});

function closeBuyerPopup() {

    const popup = document.getElementById("buyerPopup");
    if (!popup) return;

    popup.classList.remove("buyer-popup-show");

    setTimeout(function () {
        popup.style.display = "none";
    }, 300);

}
</script>

@endsection
