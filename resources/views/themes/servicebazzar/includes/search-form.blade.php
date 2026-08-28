@php
    $headerLocationValue = session('selected_location', '');
    $headerCategoryValue = session('selected_category', '');
@endphp

<div class="header-search-area" id="headerSearchOriginalPlace">
    <form action="{{ route('listings.search') }}" method="GET" id="headerSearchForm">
        <div class="header-search-row-custom">

            {{-- ======================== LOCATION INPUT ======================== --}}
            <div class="header-location-col-custom" id="headerLocationBox">
                <div class="header-input_location_box">
                    <input type="text"
                           name="location_name"
                           id="headerLocationInput"
                           data-location-id="{{ request()->location_id ?? ($listing->location_id ?? '') }}"
                           autocomplete="off"
                           placeholder="Search location..."
                           value="{{ $headerLocationValue }}"
                           class="header-input_location"
                           role="combobox"
                           aria-label="Search location"
                           aria-autocomplete="list"
                           aria-expanded="false"
                           aria-controls="headerLocationSuggestions">

                    <input type="hidden" name="latitude" id="headerSearchLatitude" value="{{ request()->latitude }}">
                    <input type="hidden" name="longitude" id="headerSearchLongitude" value="{{ request()->longitude }}">
                  <input type="hidden"
       name="location_id"
       id="headerSearchLocationId"
       value="{{ request()->location_id ?? ($listing->location_id ?? '') }}">
                    <div id="headerLocationSuggestions" class="header-suggestion-box"></div>
                </div>
            </div>

            {{-- ======================== CATEGORY INPUT ======================== --}}
            <div class="header-search-col-custom">
                <div class="header-input_search_box header-custom-search-wrap">
                    <input type="text"
                           name="categorysearch"
                           id="headerCategorySearch"
                           autocomplete="off"
                           placeholder="Search for Packers and Movers"
                           value="{{ $headerCategoryValue }}"
                           class="header-input_search"
                           role="combobox"
                           aria-label="Search category"
                           aria-autocomplete="list"
                           aria-expanded="false"
                           aria-controls="headerCategorySuggestions">

                    {{-- Clear Button --}}
                    <span class="header-custom-clear-btn" id="headerClearBtn">
                        <i class="fas fa-times"></i>
                    </span>

                    {{-- Voice Search Button --}}
                    <span class="header-voice-search-btn" id="headerVoiceSearchBtn" title="Voice Search">
                        <i class="fas fa-microphone"></i>
                    </span>

                    {{-- Search Button --}}
                    <div class="header-search_btnbox">
                        <button type="submit" class="header-search_buttonh" aria-label="search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <ul id="headerCategorySuggestions" class="header-shadow" role="listbox"></ul>
                </div>
            </div>

        </div>

        {{-- Error Message --}}
        <div id="headerSearchError" style="color:#dc3545; font-size:14px; margin-top:8px; display:none;">
            Please enter category / subcategory name for search.
        </div>
    </form>
</div>



<style>
    /* ========== HEADER SEARCH BASE STYLES ========== */
    #scrollSearchHolder .header-input_search {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #scrollSearchHolder {
        max-width: 850px;
        width: 100%;
        flex: 1;
    }

    #scrollSearchHolder .header-search-area {
        padding: 0 !important;
        margin: 0 !important;
    }

    #scrollSearchHolder .header-search-row-custom {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #scrollSearchHolder .header-location-col-custom,
    #scrollSearchHolder .header-search-col-custom {
        width: 100% !important;
        flex: 0 0 100% !important;
    }

    #scrollSearchHolder .header-input_location_box,
    #scrollSearchHolder .header-input_search_box {
        height: 40px;
    }

    /* ========== LOCATION INPUT BOX ========== */
    .header-input_location_box {
        width: 100%;
        height: 50px;
        border: solid .5px #111;
        background-color: #f5f5f5;
        border-radius: 4px;
        position: relative;
        display: flex;
        align-items: center;
        overflow: visible !important;
    }

    .header-input_location_box:before {
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

    .header-input_location_box:after {
        position: absolute;
        content: "";
        background: -webkit-linear-gradient(left, rgba(255, 255, 255, 0) -4%, #f5f5f5 67%);
        background: -moz-linear-gradient(left, rgba(255, 255, 255, 0) -4%, #f5f5f5 67%);
        background: -o-linear-gradient(left, rgba(255, 255, 255, 0) -4%, #f5f5f5 67%);
        background: linear-gradient(to right, rgba(255, 255, 255, 0) -4%, #f5f5f5 67%);
        right: 0;
        top: 0;
        width: 40px;
        height: 100%;
        z-index: 1;
    }

    .header-input_location {
        width: 100%;
        height: 100%;
        border: none;
        outline: none;
        background: transparent;
        padding: 0 15px 0 42px;
        font-size: 15px;
    }

    /* ========== SEARCH INPUT BOX ========== */
    .header-input_search_box {
        width: 100%;
        height: 50px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        -webkit-box-shadow: 0 2px 4px 0 rgba(0, 0, 0, .15);
        -moz-box-shadow: 0 2px 4px 0 rgba(0, 0, 0, .15);
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
        -moz-transition: all .15s;
        -o-transition: all .15s;
        transition: all .15s;
    }

    .header-input_search {
        width: 100%;
        height: 100%;
        border: none;
        outline: none;
        background: transparent;
        padding: 0 95px 0 15px;
        overflow: hidden;
        white-space: nowrap;
        -o-text-overflow: ellipsis;
        text-overflow: ellipsis;
        appearance: none !important;
        -webkit-appearance: none !important;
    }

    /* ========== CLEAR BUTTON ========== */
    .header-custom-clear-btn {
        position: absolute;
        right: 70px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        display: none;
        cursor: pointer;
        color: #5c79b5;
        font-size: 15px;
    }

    .header-custom-clear-btn i {
        font-size: 15px;
    }

    /* ========== VOICE SEARCH BUTTON ========== */
    .header-voice-search-btn {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        cursor: pointer;
        color: #5c79b5;
        font-size: 16px;
    }

    .header-voice-search-btn.listening {
        color: #ea020b;
        animation: headerPulse 1s infinite;
    }

    @keyframes headerPulse {
        0% { transform: translateY(-50%) scale(1); }
        50% { transform: translateY(-50%) scale(1.2); }
        100% { transform: translateY(-50%) scale(1); }
    }

    /* ========== SEARCH BUTTON ========== */
    .header-search_buttonh {
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
        cursor: pointer;
    }

    .header-search_buttonh i {
        font-size: 16px;
        display: block;
        line-height: 1;
        color: #5c79b5;
    }

    /* ========== LOCATION SUGGESTIONS ========== */
   #headerLocationSuggestions {
    position: absolute;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    background-color: #fff;
    width: 100%;
    top: -webkit-calc(100% + 5px);
    top: -moz-calc(100% + 5px);
    top: calc(100% + 5px);
    left: 0;
    display: none;
    display: inline-block;
    width: 480px;
   
    -webkit-box-shadow: 0px 2px 4px rgba(0, 0, 0, .1);
    -moz-box-shadow: 0px 2px 4px rgba(0,0,0,.1);
     box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
    overflow-y: auto;
    max-height: 400px;
}
.header-location-col-custom{
    position: relative;
    flex: 0 0 370px !important;
    max-width: 370px !important;
}

    #headerLocationSuggestions.show {
        display: block;
    }

    #headerLocationSuggestions .header-dropdown-item {
        display: block;
        padding: 10px 18px;
        cursor: pointer;
        text-decoration: none;
        color: #222;
        background: #fff;
        border: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.3;
        letter-spacing: .1px;
    }

    #headerLocationSuggestions .header-dropdown-item:hover,
    #headerLocationSuggestions .header-dropdown-item.active {
        background: #f5f5f5 !important;
    }

    .header-detect-location-item {
        color: #0076d7 !important;
        font-weight: 500 !important;
        border-bottom: 1px solid #eee;
        margin-bottom: 8px;
    }

    .header-location-section-title {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        padding: 12px 20px 6px;
        font-weight: 600;
    }

    .header-clear-recent-locations {
        float: right;
        color: #0076d7;
        font-size: 12px;
        cursor: pointer;
        text-transform: none;
        font-weight: 400;
    }

    /* ========== CATEGORY SUGGESTIONS ========== */
    #headerCategorySuggestions {
        position: absolute;
        top: -webkit-calc(100% + 5px);
    top: -moz-calc(100% + 5px);
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
        z-index: 99999;
        padding: 10px 0 !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
    }

    #headerCategorySuggestions.show {
        display: block;
    }

    #headerCategorySuggestions .header-dropdown-item {
        display: block;
        padding: 10px 18px;
        cursor: pointer;
        text-decoration: none;
        color: #222;
        background: #fff;
        border: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.3;
        letter-spacing: .1px;
    }

    #headerCategorySuggestions .header-dropdown-item:hover,
    #headerCategorySuggestions .header-dropdown-item.active {
        background: #f5f5f5 !important;
    }

    /* ========== CATEGORY SUGGESTION ITEMS ========== */
    #headerCategorySuggestions .header-category-suggestion-item,
    #headerCategorySuggestions .header-category-listing-item {
        display: flex !important;
        width: 100%;
        align-items: center;
        gap: 12px;
        padding: 10px 0 9px;
        cursor: pointer;
        background: #fff;
        display: inline-flex;
        min-height: 38px;
        transition: .8s;
        border-radius: 4px;
        font-weight: 400;
    }

    #headerCategorySuggestions .header-category-suggestion-item:hover,
    #headerCategorySuggestions .header-category-listing-item:hover {
        background: #f5f5f5 !important;
    }

    .header-category-search-icon {
        width: 30px;
        height: 16px;
        margin: 0 10px 0 5px;
        position: relative;
        top: 1px;
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
        display: inline-block;
        vertical-align: middle;
    }

    .header-category-trending-icon {
        width: 30px;
        height: 30px;
        margin: 0 10px 0 5px;
        border-radius: 6px;
        border: .5px solid #ddd;
        background-color: #fff;
        overflow: hidden;
        flex: none;
    }

    .header-category-trending-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .header-listing-suggestion-img {
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
        display: inline-block;
        vertical-align: middle;
    }

    .header-listing-suggestion-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .header-category-suggestion-content {
        font-size: 14px;
        font-weight: 400;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .header-category-suggestion-name {
        font-size: 13px;
        font-weight: 500;
        color: #111;
        line-height: 18px;
    }

    .header-category-suggestion-type {
        font-size: 12px;
        color: #777;
        margin-top: 2px;
    }

    .header-listing-rating {
        background: #0a9f34;
        color: #fff;
        font-size: 11px;
        padding: 1px 4px;
        border-radius: 2px;
        font-weight: 600;
    }

    .header-listing-review,
    .header-listing-dot {
        color: #777;
    }

    .header-clear-recent-categories {
        float: right;
        color: #0076d7;
        font-size: 12px;
        cursor: pointer;
        text-transform: none;
        font-weight: 400;
    }

    /* ========== VOICE MODAL ========== */
  

    /* ========== MOBILE RESPONSIVE ========== */
    @media (max-width: 767px) {
        .header-search-row-custom {
            display: block;
        }

        .header-location-col-custom,
        .header-search-col-custom {
            width: 100%;
            flex: 0 0 100%;
            margin-bottom: 10px;
        }

        #headerLocationSuggestions {
            width: 100% !important;
        }

        #headerCategorySuggestions {
            width: 100% !important;
        }
    }

    /* ========== SCROLLBAR STYLING ========== */
    #headerLocationSuggestions::-webkit-scrollbar,
    #headerCategorySuggestions::-webkit-scrollbar {
        width: 8px;
    }

    #headerLocationSuggestions::-webkit-scrollbar-thumb,
    #headerCategorySuggestions::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    #headerLocationSuggestions::-webkit-scrollbar-track,
    #headerCategorySuggestions::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #headerLocationSuggestions,
    #headerCategorySuggestions {
        scrollbar-width: thin;
    }

    @media (max-width: 767px) {

    .header-search-area,
    #headerSearchOriginalPlace {
        display: none !important;
    }

}
</style>


<script>
    document.addEventListener("DOMContentLoaded", function () {

     const isListingDetailsPage = @json(request()->routeIs('listings.details'));
    const headerLocationInput = document.getElementById("headerLocationInput");
    const headerLocationDropdown = document.getElementById("headerLocationSuggestions");
    const headerLatitudeInput = document.getElementById("headerSearchLatitude");
    const headerLongitudeInput = document.getElementById("headerSearchLongitude");
    const headerLocationIdInput = document.getElementById("headerSearchLocationId");

    if (headerLocationInput && headerLocationDropdown) {

        // ---- Restore Detected Location ----
       function headerRestoreDetectedLocation() {
    const sessionLocation = @json($headerLocationValue);

    if (sessionLocation) {
        headerLocationInput.value = sessionLocation;
        headerLocationInput.dataset.selectedLocation = "1";
        headerCloseLocationDropdown();
        return;
    }

    headerLocationInput.value = "";
    headerLocationInput.dataset.selectedLocation = "0";
}

        headerRestoreDetectedLocation();
        window.addEventListener("pageshow", headerRestoreDetectedLocation);

        // ---- Sync with Home Location ----
        const homeLocation = document.getElementById("homeLocationInput");
        if (homeLocation) {
            homeLocation.addEventListener("input", function () {
                if (this.value.trim() !== "") {
                    headerLocationInput.value = this.value;
                    headerLocationInput.dataset.selectedLocation = "1";
                }
            });
            homeLocation.addEventListener("change", function () {
                if (this.value.trim() !== "") {
                    headerLocationInput.value = this.value;
                    headerLocationInput.dataset.selectedLocation = "1";
                }
            });
        }

        headerLocationInput.addEventListener("input", function () {
            const homeLoc = document.getElementById("homeLocationInput");
            if (homeLoc) {
                homeLoc.value = this.value;
                homeLoc.dataset.selectedLocation = this.dataset.selectedLocation;
            }
        });

        // ---- Dropdown Functions ----
        function headerCloseLocationDropdown() {
            headerLocationDropdown.classList.remove("show");
            headerLocationDropdown.innerHTML = "";
        }

        function headerOpenLocationDropdown() {
            headerLocationDropdown.classList.add("show");
        }

        function headerGetRecentLocations() {
            return JSON.parse(localStorage.getItem("service_bazzar_recent_locations") || "[]");
        }

        function headerSaveRecentLocation(location) {
            let recent = headerGetRecentLocations();
            recent = recent.filter(item => item.value !== location.value);
            recent.unshift(location);
            recent = recent.slice(0, 5);
            localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(recent));
        }

        function headerRenderLocationItem(item) {
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
            div.className = "header-dropdown-item";
            div.dataset.value = item.value || (parentName ? name + ", " + parentName : name);
            div.dataset.lat = item.latitude || item.lat || "";
            div.dataset.lng = item.longitude || item.lng || "";
            const finalLocationId = item.location_id || item.id || item.locationId || "";
            div.setAttribute("data-location-id", finalLocationId);
            div.dataset.locationId = finalLocationId;
            div.innerHTML = parentName ? `${name}, ${parentName}` : name;
            return div;
        }

        function headerRenderLocations(list) {
            headerLocationDropdown.innerHTML = "";

            // 1. Detect Location
            const detectDiv = document.createElement("div");
            detectDiv.className = "header-dropdown-item header-detect-location-item";
            detectDiv.innerHTML = `
                <i class="las la-crosshairs"></i> 
                Detect Location 
                <span class="header-detect-loading-spinner" style="display:none; margin-left:8px; color:#0076d7;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            `;
            headerLocationDropdown.appendChild(detectDiv);

            // 2. Recent Locations
            if (!headerLocationInput.value.trim()) {
                const recentLocations = headerGetRecentLocations();
                if (recentLocations.length > 0) {
                    const recentTitle = document.createElement("div");
                    recentTitle.className = "header-location-section-title";
                    recentTitle.innerHTML = `Recent Locations <span class="header-clear-recent-locations">Clear All</span>`;
                    headerLocationDropdown.appendChild(recentTitle);
                    recentLocations.forEach(item => {
                        const div = headerRenderLocationItem(item);
                        if (div) headerLocationDropdown.appendChild(div);
                    });
                }
            }

            // 3. Trending Areas - 👇 CURRENT LOCATION के हिसाब से
            if (Array.isArray(list) && list.length > 0) {
                if (!headerLocationInput.value.trim()) {
                    const trendingTitle = document.createElement("div");
                    trendingTitle.className = "header-location-section-title";
                    trendingTitle.innerHTML = "Trending Areas";
                    headerLocationDropdown.appendChild(trendingTitle);
                }
                let usedLocations = [];
                list.forEach(item => {
                    const div = headerRenderLocationItem(item);
                    if (!div) return;
                    let uniqueKey = div.dataset.value.toLowerCase();
                    if (usedLocations.includes(uniqueKey)) return;
                    usedLocations.push(uniqueKey);
                    headerLocationDropdown.appendChild(div);
                });
            }

            headerOpenLocationDropdown();
        }

        function headerFetchLocationSuggestions(search = "") {
            // 👇 CURRENT LOCATION से suggestions fetch करें
            const lat = headerLatitudeInput ? headerLatitudeInput.value : "";
            const lng = headerLongitudeInput ? headerLongitudeInput.value : "";
            
            // 👇 LocalStorage से latest location लें
            const currentCity = localStorage.getItem("jf_selected_location_name") || 
                               localStorage.getItem("jf_city") || 
                               "";
            
            const finalLat = lat || localStorage.getItem('jf_lat') || "";
            const finalLng = lng || localStorage.getItem('jf_lng') || "";

            const url = `{{ route('nearby.location.suggestions') }}?q=${encodeURIComponent(search || currentCity)}&latitude=${encodeURIComponent(finalLat)}&longitude=${encodeURIComponent(finalLng)}&_=${Date.now()}`;

            fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => headerRenderLocations(data))
            .catch(err => {
                console.error("Header location suggestion error:", err);
                headerLocationDropdown.innerHTML = "";
                headerOpenLocationDropdown();
            });
        }

        let headerLocationTimer;
        let headerPreviousLocationValue = headerLocationInput.value.trim();
        let headerPreviousLocationId = headerLocationIdInput ? headerLocationIdInput.value : "";
        let headerPreviousLatitude = headerLatitudeInput ? headerLatitudeInput.value : "";
        let headerPreviousLongitude = headerLongitudeInput ? headerLongitudeInput.value : "";

        headerLocationInput.addEventListener("input", function () {
            const value = this.value.trim();
            this.dataset.selectedLocation = "0";
            clearTimeout(headerLocationTimer);
            if (value.length < 1) {
                const currentCity = localStorage.getItem("jf_selected_location_name") || 
                                   localStorage.getItem("jf_city") || 
                                   "";
                headerFetchLocationSuggestions(currentCity);
                return;
            }
            headerLocationTimer = setTimeout(() => {
                headerFetchLocationSuggestions(value);
            }, 250);
        });

        headerLocationInput.addEventListener("focus", function () {
            headerPreviousLocationValue = this.value.trim();
            headerPreviousLocationId = headerLocationIdInput ? headerLocationIdInput.value : "";
            headerPreviousLatitude = headerLatitudeInput ? headerLatitudeInput.value : "";
            headerPreviousLongitude = headerLongitudeInput ? headerLongitudeInput.value : "";

            this.value = "";
            this.dataset.selectedLocation = "0";

            const currentCity = localStorage.getItem("jf_selected_location_name") || 
                               localStorage.getItem("jf_city") || 
                               "";
            headerFetchLocationSuggestions(currentCity);
        });

        // ---- Detect Location ----
        headerLocationDropdown.addEventListener("mousedown", function (e) {
            if (e.target.classList.contains("header-clear-recent-locations")) {
                e.preventDefault();
                localStorage.removeItem("service_bazzar_recent_locations");
                const currentCity = localStorage.getItem("jf_selected_location_name") || 
                                   localStorage.getItem("jf_city") || 
                                   "";
                headerFetchLocationSuggestions(currentCity);
                return;
            }

            const item = e.target.closest(".header-dropdown-item");
            if (!item) return;
            e.preventDefault();

            if (item.classList.contains("header-detect-location-item")) {
                const spinner = item.querySelector(".header-detect-loading-spinner");
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
                headerOpenLocationDropdown();

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            if (headerLatitudeInput) headerLatitudeInput.value = lat;
                            if (headerLongitudeInput) headerLongitudeInput.value = lng;

                            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat=${lat}&lon=${lng}`, {
                                headers: { "Accept": "application/json" }
                            })
                            .then(res => res.json())
                            .then(data => {
                                const address = data.address || {};
                                const city = window.normalizeEnglishLocationName
                                    ? window.normalizeEnglishLocationName(address.city || address.town || address.village || address.state_district || address.state || '')
                                    : (address.city || address.town || address.village || address.state_district || address.state || '');

                                if (city && headerLocationInput) {
                                    headerLocationInput.value = city;
                                    headerLocationInput.dataset.selectedLocation = "1";

                                    // 👇 LOCALSTORAGE UPDATE - IMPORTANT!
                                    localStorage.setItem('jf_selected_location_name', city);
                                    localStorage.setItem('jf_city', city);
                                    localStorage.setItem('jf_lat', lat);
                                    localStorage.setItem('jf_lng', lng);

                                    if (spinner) spinner.style.display = "none";
                                    headerCloseLocationDropdown();
                                    headerLocationInput.blur();
                                    document.activeElement.blur();

                                    // Sync with Home
                                    const homeLoc = document.getElementById("homeLocationInput");
                                    if (homeLoc) {
                                        homeLoc.value = city;
                                        homeLoc.dataset.selectedLocation = "1";
                                    }

                                   headerCloseLocationDropdown();

const categoryDropdown = document.getElementById("headerCategorySuggestions");
if (categoryDropdown) {
    categoryDropdown.classList.remove("show");
    categoryDropdown.innerHTML = "";
}

                                    // Session save
                                    fetch('/set-location-session', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({ location: city })
                                    });
                                }
                            })
                            .catch(() => {
                                if (spinner) spinner.style.display = "none";
                                showDetectPopup("Unable to detect location. Please try again.");
                                headerCloseLocationDropdown();
                            });
                        },
                        function (error) {
                            if (spinner) spinner.style.display = "none";
                            showDetectPopup(
                                "Unable to detect location. Please allow location access.",
                                error && error.code === error.PERMISSION_DENIED
                            );
                            headerCloseLocationDropdown();
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
                    headerCloseLocationDropdown();
                }
                return;
            }

            // ---- NORMAL LOCATION SELECTION ----
            const selectedLocationId = item.getAttribute("data-location-id") || "";
            const selectedValue = item.dataset.value || "";
            
            // 👇 SET VALUE
            headerLocationInput.value = selectedValue;
            headerLocationInput.dataset.selectedLocation = "1";
            headerLocationInput.dataset.locationId = selectedLocationId;
            
            // 👇 LOCALSTORAGE UPDATE - IMPORTANT!
            localStorage.setItem('jf_selected_location_name', selectedValue);
            localStorage.setItem('jf_city', selectedValue);
            localStorage.setItem('jf_lat', item.dataset.lat || "");
            localStorage.setItem('jf_lng', item.dataset.lng || "");
            
            // 👇 SAVE RECENT
            headerSaveRecentLocation({
                value: selectedValue,
                latitude: item.dataset.lat || "",
                longitude: item.dataset.lng || "",
                location_id: selectedLocationId
            });
            
            // 👇 UPDATE HIDDEN FIELDS
            if (headerLocationIdInput) headerLocationIdInput.value = selectedLocationId;
            if (headerLatitudeInput) headerLatitudeInput.value = item.dataset.lat || "";
            if (headerLongitudeInput) headerLongitudeInput.value = item.dataset.lng || "";

            // 👇 SYNC WITH HOME
            const homeLoc = document.getElementById("homeLocationInput");
            if (homeLoc) {
                homeLoc.value = selectedValue;
                homeLoc.dataset.selectedLocation = "1";
            }

            // 👇 SESSION SAVE
            fetch('/set-location-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ location: selectedValue })
            });

            headerCloseLocationDropdown();
            headerLocationInput.blur();
            document.activeElement.blur();

       
            // headerFetchLocationSuggestions(selectedValue);

          if (!isListingDetailsPage) {
    const headerForm = document.getElementById("headerSearchForm");
    if (headerForm) {
        setTimeout(function() {
            headerForm.requestSubmit();
        }, 100);
    }
}
        });

        headerLocationInput.addEventListener("blur", function () {
            setTimeout(() => {
                if (this.value.trim() === "" && headerPreviousLocationValue !== "") {
                    this.value = headerPreviousLocationValue;
                    this.dataset.selectedLocation = "1";
                    this.dataset.locationId = headerPreviousLocationId;
                    if (headerLocationIdInput) headerLocationIdInput.value = headerPreviousLocationId;
                    if (headerLatitudeInput) headerLatitudeInput.value = headerPreviousLatitude;
                    if (headerLongitudeInput) headerLongitudeInput.value = headerPreviousLongitude;
                }
                headerCloseLocationDropdown();
            }, 200);
        });

        // ---- Keyboard Navigation ----
        let headerLocationIndex = -1;

        headerLocationInput.addEventListener("input", function () {
            headerLocationIndex = -1;
        });

        headerLocationInput.addEventListener("keydown", function(e){
            const items = headerLocationDropdown.querySelectorAll(".header-dropdown-item:not(.header-detect-location-item)");
            if(!items.length) return;

            if(e.key === "ArrowDown"){
                e.preventDefault();
                headerLocationIndex++;
                if(headerLocationIndex >= items.length) headerLocationIndex = 0;
            }
            else if(e.key === "ArrowUp"){
                e.preventDefault();
                headerLocationIndex--;
                if(headerLocationIndex < 0) headerLocationIndex = items.length - 1;
            }
            else if(e.key === "Enter" && headerLocationIndex > -1){
                e.preventDefault();
                items[headerLocationIndex].dispatchEvent(
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
            const activeItem = items[headerLocationIndex];
            activeItem.classList.add("active");
            headerLocationInput.value = activeItem.dataset.value || activeItem.textContent.trim();
            activeItem.scrollIntoView({ block:"nearest" });
        });

        // ---- Close dropdown on outside click ----
        document.addEventListener("mousedown", function (e) {
            if (!headerLocationInput.contains(e.target) && !headerLocationDropdown.contains(e.target)) {
                headerCloseLocationDropdown();
            }
        });

    }


    // ============================================================
    // 2. HEADER CATEGORY SUGGESTIONS - FULL FEATURED
    // ============================================================

    const headerCategoryInput = document.getElementById("headerCategorySearch");
    const headerCategoryDropdown = document.getElementById("headerCategorySuggestions");
    const headerClearBtn = document.getElementById("headerClearBtn");
    const headerErrorBox = document.getElementById("headerSearchError");

    if (headerCategoryInput && headerCategoryDropdown) {

        if (headerCategoryInput.value.trim() !== "") {
            headerCategoryInput.dataset.selectedCategory = "1";
        }

        function headerCloseCategoryDropdown() {
            headerCategoryDropdown.classList.remove("show");
            headerCategoryDropdown.innerHTML = "";
        }

        function headerOpenCategoryDropdown() {
            headerCategoryDropdown.classList.add("show");
        }

        function headerSlugify(text) {
            return String(text || "")
                .toLowerCase()
                .trim()
                .replace(/,/g, "")
                .replace(/\s+/g, "-")
                .replace(/[^a-z0-9-]/g, "")
                .replace(/-+/g, "-")
                .replace(/^-+|-+$/g, "");
        }

    function headerGetSelectedLocation() {
    return headerLocationInput && headerLocationInput.value.trim() !== ""
        ? headerLocationInput.value.trim()
        : "";
}

        function headerGetRecentCategories() {
            return JSON.parse(localStorage.getItem("service_bazzar_recent_categories") || "[]");
        }

        function headerSaveRecentCategory(category) {
            let recent = headerGetRecentCategories();
            recent = recent.filter(item => item.value !== category.value);
            recent.unshift(category);
            recent = recent.slice(0, 5);
            localStorage.setItem("service_bazzar_recent_categories", JSON.stringify(recent));
        }

        function headerRenderSectionTitle(title, clearClass = "") {
            const div = document.createElement("div");
            div.className = "header-location-section-title";
            div.innerHTML = clearClass
                ? `${title} <span class="${clearClass}">Clear All</span>`
                : title;
            return div;
        }

        function headerRenderCategoryItem(item, iconType = "search") {
            if (!item || !item.name) return null;

            const li = document.createElement("li");
            const a = document.createElement("a");

            a.href = "javascript:void(0)";
            a.className = "header-dropdown-item header-category-suggestion-item";
            a.dataset.value = item.name;
            a.dataset.url = item.url || "";
            a.dataset.type = "category";

            const iconHtml = iconType === "trending"
                ? `<span class="header-category-trending-icon"><img src="{{ asset('assets/images/ffffjndjs.svg') }}" alt=""></span>`
                : `<span class="header-category-search-icon"><i class="fas fa-search"></i></span>`;

            a.innerHTML = `
                ${iconHtml}
                <span class="header-category-suggestion-content">
                    <span class="header-category-suggestion-name">${item.name}</span>
                    <span class="header-category-suggestion-type">Category</span>
                </span>
            `;

            li.appendChild(a);
            return li;
        }

        function headerRenderListingItem(item) {
            if (!item || !item.name) return null;

            const image = item.image || item.thumb || item.logo || "{{ asset('assets/images/default.jpg') }}";
            const rating = item.rating || "";
            const reviews = item.reviews || "";
            const area = item.area || "";
            const distance = item.distance || "";

            const li = document.createElement("li");
            const a = document.createElement("a");

            a.href = "javascript:void(0)";
            a.className = "header-dropdown-item header-category-listing-item";
            a.dataset.value = item.name;
            a.dataset.url = item.url || "";
            a.dataset.type = "listing";
            a.dataset.image = image;
            a.dataset.area = area;
            a.dataset.rating = rating;
            a.dataset.reviews = reviews;

            let meta = "";
            if (rating) {
                meta += `<span class="header-listing-rating">${rating} ★</span>`;
            }
            if (reviews) {
                meta += ` <span class="header-listing-review">(${reviews})</span>`;
            }
            if (area) {
                meta += ` <span class="header-listing-dot">•</span> <span>${area}</span>`;
            }
            if (distance) {
                meta += ` <span class="header-listing-dot">•</span> <span>${distance}</span>`;
            }

            a.innerHTML = `
                <span class="header-listing-suggestion-img">
                    <img src="${image}" alt="">
                </span>
                <span class="header-category-suggestion-content">
                    <span class="header-category-suggestion-name">${item.name}</span>
                    <span class="header-category-suggestion-type">${meta || "Business Listing"}</span>
                </span>
            `;

            li.appendChild(a);
            return li;
        }

        function headerNormalizeResponse(data) {
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

        let headerLastCategorySearch = "";
        let headerLastGoodCategories = [];

        function headerRenderCategories(data) {
            headerCategoryDropdown.innerHTML = "";

            const response = headerNormalizeResponse(data);
            let categories = response.categories;
            let listings = response.listings;
            function headerCleanText(text) {
    return String(text || "")
        .toLowerCase()
        .replace(/\s+/g, " ")
        .trim();
}

const searchText = headerCleanText(headerCategoryInput.value);

if (searchText) {

    function scoreItem(item) {
        const name = headerCleanText(item.name);

        if (name === searchText) return 1;
        if (name.startsWith(searchText)) return 2;
        if (name.includes(searchText)) return 3;

        return 999;
    }

    categories = categories
        .map(item => ({ ...item, _score: scoreItem(item) }))
        .filter(item => item._score < 999)
        .sort((a, b) => a._score - b._score);

    listings = listings
        .map(item => ({ ...item, _score: scoreItem(item) }))
        .filter(item => item._score < 999)
        .sort((a, b) => a._score - b._score);
}

            if (Array.isArray(categories) && categories.length > 0) {
                headerLastGoodCategories = categories;
            }

           

            if (!Array.isArray(listings)) {
                listings = [];
            }

            const isTyping = headerCategoryInput.value.trim() !== "";

            if (!isTyping) {
                const recentCategories = headerGetRecentCategories();
                if (recentCategories.length > 0) {
                    headerCategoryDropdown.appendChild(headerRenderSectionTitle("Recent Searches", "header-clear-recent-categories"));
                    recentCategories.forEach(item => {
                        let li = null;
                        if (item.type === "listing") {
                            li = headerRenderListingItem(item);
                        } else {
                            li = headerRenderCategoryItem(item, "search");
                        }
                        if (li) headerCategoryDropdown.appendChild(li);
                    });
                }

                if (categories.length > 0) {
                    headerCategoryDropdown.appendChild(headerRenderSectionTitle("Trending Searches"));
                }
            }

            let usedCategories = [];
            categories.slice(0, 10).forEach(item => {
                if (!item || !item.name) return;
                const uniqueKey = item.name.toLowerCase();
                if (usedCategories.includes(uniqueKey)) return;
                usedCategories.push(uniqueKey);
                const li = headerRenderCategoryItem(item, isTyping ? "search" : "trending");
                if (li) headerCategoryDropdown.appendChild(li);
            });

            listings.forEach(item => {
                const li = headerRenderListingItem(item);
                if (li) headerCategoryDropdown.appendChild(li);
            });

            if (!headerCategoryDropdown.children.length) {
                headerCloseCategoryDropdown();
                return;
            }

            headerOpenCategoryDropdown();
        }

      function headerCleanText(text) {
    return String(text || "")
        .toLowerCase()
        .replace(/\s+/g, " ")
        .trim();
}

function headerFetchCategorySuggestions(search = "") {

    const cleanSearch = headerCleanText(search);
    headerLastCategorySearch = cleanSearch;

    const selectedLocation = headerLocationInput
        ? headerLocationInput.value.trim()
        : "";

    const locationId =
        headerLocationInput?.dataset.locationId ||
        document.getElementById("headerSearchLocationId")?.value ||
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
        if (headerCleanText(headerCategoryInput.value) !== cleanSearch) return;
        headerRenderCategories(data);
    })
    .catch(err => {
        console.error("Header category suggestion error:", err);
        headerCloseCategoryDropdown();
    });
}

        let headerCategoryTimer;
        let headerCategoryIndex = -1;

        headerCategoryInput.addEventListener("focus", function () {
            const value = this.value.trim();
            headerCategoryIndex = -1;
            if (value.length > 0) {
                headerFetchCategorySuggestions(value);
            } else {
                headerFetchCategorySuggestions("");
            }
        });

        headerCategoryInput.addEventListener("click", function () {
            const value = this.value.trim();
            headerCategoryIndex = -1;
            if (value.length > 0) {
                headerFetchCategorySuggestions(value);
            } else {
                headerFetchCategorySuggestions("");
            }
        });

        headerCategoryInput.addEventListener("input", function () {
            const value = this.value;
            headerCategoryIndex = -1;
            this.dataset.selectedCategory = "0";
            this.dataset.selectedUrl = "";

            if (headerErrorBox) headerErrorBox.style.display = "none";

            if (headerClearBtn) {
                headerClearBtn.style.display = value.trim() ? "block" : "none";
            }

            clearTimeout(headerCategoryTimer);
            headerCategoryTimer = setTimeout(() => {
                headerFetchCategorySuggestions(value);
            }, 250);
        });

        headerCategoryDropdown.addEventListener("mousedown", function (e) {
            e.preventDefault();
        });

        headerCategoryDropdown.addEventListener("click", function (e) {
            if (e.target.classList.contains("header-clear-recent-categories")) {
                localStorage.removeItem("service_bazzar_recent_categories");
                headerFetchCategorySuggestions("");
                return;
            }

            const item = e.target.closest(".header-dropdown-item");
            if (!item || !item.dataset.url) return;

            headerCategoryInput.value = item.dataset.value || "";
            headerCategoryInput.dataset.selectedCategory = "1";
            headerCategoryInput.dataset.selectedUrl = item.dataset.url || "";

            headerSaveRecentCategory({
                value: item.dataset.value || "",
                name: item.dataset.value || "",
                url: item.dataset.url || "",
                type: item.dataset.type || "category",
                image: item.dataset.image || "",
                area: item.dataset.area || "",
                rating: item.dataset.rating || "",
                reviews: item.dataset.reviews || ""
            });

            headerCloseCategoryDropdown();

            if (item.dataset.type === "listing") {
                window.location.href = item.dataset.url;
                return;
            }

            const baseUrl = item.dataset.url;
            const selectedLocation = headerGetSelectedLocation();

            if (!selectedLocation) {
                if (headerErrorBox) {
                    headerErrorBox.innerText = "Please select a location first.";
                    headerErrorBox.style.display = "block";
                }
                const locInput = document.getElementById("headerLocationInput");
                if (locInput) locInput.focus();
                return; 
            }

       if (headerLocationIdInput && headerLocationInput.dataset.locationId) {
    headerLocationIdInput.value = headerLocationInput.dataset.locationId;
}

const categorySlug = item.dataset.url.split("/").filter(Boolean).pop();

const locationText = headerLocationInput.value.trim();

let city = "";
let area = "";

if (locationText.includes(",")) {

    const parts = locationText.split(",");

    area = parts[0].trim();
    city = parts[1].trim();

} else {

    city = locationText;
}

const citySlug = headerSlugify(city);

let finalUrl =
    window.location.origin +
    "/" +
    citySlug +
    "/" +
    categorySlug;

if (area !== "") {
    finalUrl += "-in-" + headerSlugify(area);
}

window.location.href = finalUrl;
        });

        // ---- Keyboard Navigation for Category ----
        headerCategoryInput.addEventListener("keydown", function(e) {
            const items = headerCategoryDropdown.querySelectorAll(".header-dropdown-item");
            if (!items.length) return;

            if (e.key === "ArrowDown") {
                e.preventDefault();
                headerCategoryIndex++;
                if (headerCategoryIndex >= items.length) headerCategoryIndex = 0;
            }
            else if (e.key === "ArrowUp") {
                e.preventDefault();
                headerCategoryIndex--;
                if (headerCategoryIndex < 0) headerCategoryIndex = items.length - 1;
            }
            else if (e.key === "Enter" && headerCategoryIndex > -1) {
                e.preventDefault();
                items[headerCategoryIndex].click();
                return;
            } else {
                return;
            }

            items.forEach(item => item.classList.remove("active"));
            const activeItem = items[headerCategoryIndex];
            activeItem.classList.add("active");
            headerCategoryInput.value = activeItem.dataset.value || activeItem.textContent.trim();
            activeItem.scrollIntoView({ block: "nearest" });
        });

        // ---- Close dropdown on outside click ----
        document.addEventListener("click", function (e) {
            if (
                !e.target.closest("#headerCategorySearch") &&
                !e.target.closest("#headerCategorySuggestions")
            ) {
                headerCloseCategoryDropdown();
            }
        });

        headerCategoryInput.addEventListener("blur", function () {
            setTimeout(() => {
                headerCloseCategoryDropdown();
            }, 150);
        });

        window.addEventListener("resize", function () {
            headerCloseCategoryDropdown();
        });

        // ---- Clear Button ----
        if (headerClearBtn) {
            headerClearBtn.addEventListener("click", function () {
                headerCategoryInput.value = "";
                headerClearBtn.style.display = "none";
                headerCloseCategoryDropdown();
                headerCategoryInput.focus();
                if (headerErrorBox) headerErrorBox.style.display = "none";
            });

            if (headerCategoryInput.value.trim()) {
                headerClearBtn.style.display = "block";
            }
        }

    }


 

    const headerVoiceBtn = document.getElementById("headerVoiceSearchBtn");
   const headerVoiceModal = document.getElementById("voiceModal");
   const headerVoiceCloseBtn =
    document.getElementById("voiceCloseBtn") ||
    document.getElementById("headerVoiceCloseBtn");
    const headerVoiceStatusText = document.getElementById("headerVoiceStatusText");

    if (headerVoiceBtn && headerCategoryInput) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            recognition.lang = "en-IN";
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            headerVoiceBtn.addEventListener("click", function (e) {
                e.preventDefault();
                if (headerVoiceStatusText) headerVoiceStatusText.innerText = "Listening.....";
                if (headerVoiceModal) headerVoiceModal.style.display = "block";
                try {
                    recognition.start();
                    headerVoiceBtn.classList.add("listening");
                } catch (err) {
                    if (headerVoiceModal) headerVoiceModal.style.display = "none";
                }
            });

            recognition.onresult = function (event) {
                const text = event.results[0][0].transcript;

                headerCategoryInput.value = text;
                headerCategoryInput.dataset.selectedCategory = "0";

                if (headerClearBtn) {
                    headerClearBtn.style.display = "block";
                }
                if (headerErrorBox) headerErrorBox.style.display = "none";

                headerCategoryInput.dispatchEvent(new Event("input", { bubbles: true }));

                // Sync with Home Category
                const homeCategory = document.getElementById("homeCategorySearch");
                if (homeCategory) {
                    homeCategory.value = text;
                    homeCategory.dispatchEvent(new Event("input", { bubbles: true }));
                }

                if (headerVoiceModal) headerVoiceModal.style.display = "none";
            };

            recognition.onerror = function () {
                headerVoiceBtn.classList.remove("listening");
                if (headerVoiceModal) headerVoiceModal.style.display = "none";
            };

            recognition.onend = function () {
                headerVoiceBtn.classList.remove("listening");
                if (headerVoiceModal) headerVoiceModal.style.display = "none";
            };

            if (headerVoiceCloseBtn) {
    headerVoiceCloseBtn.addEventListener("click", function () {
        try { recognition.stop(); } catch (e) {}

        headerVoiceBtn.classList.remove("listening");

        if (headerVoiceModal) {
            headerVoiceModal.style.display = "none";
        }
    });
}
        } else {
            // Voice search not supported - hide button
            headerVoiceBtn.style.display = "none";
        }
    }


    // ============================================================
    // 4. HEADER FORM SUBMIT VALIDATION
    // ============================================================

    const headerForm = document.getElementById("headerSearchForm");

    if (headerForm) {
        headerForm.addEventListener("submit", function(e) {
            const selectedCategory = headerCategoryInput && headerCategoryInput.dataset.selectedCategory === "1";
            const selectedLocation = headerLocationInput && headerLocationInput.dataset.selectedLocation === "1";

            if (!selectedCategory || !selectedLocation) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (headerErrorBox) {
                    if (!selectedLocation) {
                        headerErrorBox.innerText = "Please select location from suggestions.";
                    } else if (!selectedCategory) {
                        headerErrorBox.innerText = "Please select category from suggestions.";
                    } else {
                        headerErrorBox.innerText = "Please select both location and category.";
                    }
                    headerErrorBox.style.display = "block";
                }

                if (!selectedLocation && headerLocationInput) {
                    headerLocationInput.focus();
                } else if (!selectedCategory && headerCategoryInput) {
                    headerCategoryInput.focus();
                }

                return false;
            }

            // Category and Location session save
            if (headerCategoryInput && headerCategoryInput.value.trim()) {
                fetch('/set-category-session', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ category: headerCategoryInput.value.trim() })
                });
            }

            if (headerLocationInput && headerLocationInput.value.trim()) {
                fetch('/set-location-session', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ location: headerLocationInput.value.trim() })
                });
            }
        }, true);
    }


    // ============================================================
    // 5. CLOSE ALL DROPDOWNS ON SCROLL
    // ============================================================

    window.addEventListener("scroll", function (e) {
        const target = e.target;
        if (
            target &&
            (
                target.id === "homeLocationSuggestions" ||
                target.id === "headerLocationSuggestions" ||
                target.id === "homeCategorySuggestions" ||
                target.id === "headerCategorySuggestions"
            )
        ) {
            return;
        }
        
        const dropdowns = [
            document.getElementById("homeCategorySuggestions"),
            document.getElementById("homeLocationSuggestions"),
            document.getElementById("headerCategorySuggestions"),
            document.getElementById("headerLocationSuggestions")
        ];

        dropdowns.forEach(function (dropdown) {
            if (dropdown) {
                dropdown.classList.remove("show");
                dropdown.innerHTML = "";
            }
        });
    }, true);

});
</script>
