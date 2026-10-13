<script>
    document.addEventListener("DOMContentLoaded", function () {
    const homeInput = document.getElementById("homeLocationInput");
    const headerInput = document.getElementById("locationInput");
    const homeDropdown = document.getElementById("homeLocationSuggestions");
    const headerDropdown = document.getElementById("locationSuggestions");

    const categoryInput = document.getElementById("CategorySearch");
    const categoryDropdown = document.getElementById("categorySuggestions");

    const jdStrip = document.getElementById("jdMobileLocationStrip");
    const jdCityBtn = document.getElementById("jdMobileCityBtn");
    const jdCityText = document.getElementById("jdMobileCityText");
    const jdLocationBox = document.getElementById("jdMobileLocationBox");
    const jdInput = document.getElementById("jdMobileLocationInput");
    
    const jdDropdown = document.getElementById("jdMobileLocationSuggestions");

    document.addEventListener("click", function(e){

    const backBtn = e.target.closest("#ytSearchBackBtn");
    if (!backBtn) return;

    e.preventDefault();
    e.stopPropagation();

    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = "{{ route('home') }}";
    }

}, true);

if (jdCityBtn && jdLocationBox) {

    jdCityBtn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = jdLocationBox.classList.contains("show");

        const ytCat = document.getElementById("ytMobileCategorySuggestions");
        if (ytCat) {
            ytCat.classList.remove("show");
            ytCat.innerHTML = "";
        }

      if (isOpen) {

    jdLocationBox.classList.remove("show");
    closeDropdown(jdDropdown);

    jdCityBtn.style.setProperty("display", "flex", "important");
    document.body.classList.remove("show-yt-location");

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) {
        ytSearchBox.style.removeProperty("display");
    }

    return;
}

document.body.classList.remove("mobile-yt-search-open");

document.body.classList.add("show-yt-location");
document.documentElement.style.overflow = "hidden";
document.body.style.overflow = "hidden";

const ytSearchBox = document.getElementById("youtubeMobileSearch");
if (ytSearchBox) {
    ytSearchBox.style.setProperty("display", "none", "important");
}

if (jdStrip) {
    jdStrip.classList.add("show");
    jdStrip.style.setProperty("display", "block", "important");
}

        jdCityBtn.style.setProperty("display", "none", "important");
        jdLocationBox.classList.add("show");

 if (jdInput) {
    jdInput.value = "";
    jdInput.dataset.selectedLocation = "0";
}

if (jdDropdown) {
    renderJdLocationList([], jdDropdown);
    jdDropdown.classList.add("show");
}

setTimeout(function () {
    fetchLocations("", jdDropdown);
}, 100);

setTimeout(function () {
    if (jdInput) jdInput.focus();
}, 150);
    });

}

document.addEventListener("mousedown", function(e){

    const backBtn = e.target.closest("#jdLocationBackBtn");
    if (!backBtn) return;

    e.preventDefault();
    e.stopPropagation();

    document.body.classList.remove("show-yt-location");

    document.documentElement.style.removeProperty("overflow");
    document.body.style.removeProperty("overflow");

    if (jdLocationBox) jdLocationBox.classList.remove("show");
    if (jdDropdown) closeDropdown(jdDropdown);

    if (jdStrip) {
        jdStrip.classList.remove("show");
        jdStrip.style.removeProperty("display");
    }

    if (jdCityBtn) {
        jdCityBtn.style.setProperty("display", "flex", "important");
    }

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) {
        ytSearchBox.style.removeProperty("display");
    }

    const isHomePage = @json(request()->routeIs('home'));

    if (isHomePage) {
        document.body.classList.remove("mobile-yt-search-open");
    } else {
        document.body.classList.add("mobile-yt-search-open");
    }

}, true);

    const savedCityKey = "jf_city";
    const savedLatKey = "jf_lat";
    const savedLngKey = "jf_lng";
const sessionSelectedLocation = @json(session('selected_location', ''));
const sessionSelectedCategory = @json(session('selected_category', ''));
    function isMobile() {
        return window.innerWidth <= 991;
    }

    function formatSlug(slug) {
        return String(slug || "")
            .replace(/-/g, " ")
            .replace(/\b\w/g, function (c) {
                return c.toUpperCase();
            })
            .trim();
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

    /*
     * Only real cities may become a URL. Typed text that was never picked from
     * the list ("Xn, Ksj, B Dskj") used to be slugged straight into
     * /ksj/it-consultants-in-xn and land on "Page Not Found".
     */
    @php
        $sbCityKeys = \Illuminate\Support\Facades\Cache::remember('sb:city_keys:v1', 3600, fn () =>
            \App\Models\Location::where('status', 1)->get(['name', 'slug'])
                ->flatMap(fn ($l) => [strtolower((string) $l->slug), \Illuminate\Support\Str::slug((string) $l->name)])
                ->filter()->unique()->values()->all());
    @endphp
    const SB_CITY_KEYS = new Set(@json($sbCityKeys));
    function sbCityOf(text) {                 // "Vaishali Nagar, Jaipur" -> "Jaipur"
        const parts = String(text || "").split(",");
        return (parts.length > 1 ? parts[parts.length - 1] : parts[0]).trim();
    }
    function sbKnownCity(text) {
        const c = sbCityOf(text);
        if (!c) return false;
        return SB_CITY_KEYS.has(slugify(c)) || SB_CITY_KEYS.has(c.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, ""));
    }
    window.sbKnownCity = sbKnownCity;
    function sbAskForRealCity() {
        const msg = "Please choose your city or area from the suggestions list.";
        ["homeLocationInput", "headerLocationInput", "jdMobileLocationInput"].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.offsetParent !== null) { el.focus(); el.select && el.select(); }
        });
        if (window.Swal && Swal.fire) Swal.fire({ icon: "info", text: msg, timer: 3500, showConfirmButton: false });
        else alert(msg);
    }
    window.sbAskForRealCity = sbAskForRealCity;

    // Junk saved in this browser before the check existed: drop it.
    try {
        ["jf_city", "jf_detected_city", "jf_selected_location_name", "jf_user_city"].forEach(function (k) {
            const v = localStorage.getItem(k);
            if (v && !sbKnownCity(v)) { localStorage.removeItem(k); if (k === "jf_detected_city") { localStorage.removeItem("jf_lat"); localStorage.removeItem("jf_lng"); } }
        });
        const rec = JSON.parse(localStorage.getItem("service_bazzar_recent_locations") || "[]");
        if (Array.isArray(rec)) {
            const ok = rec.filter(function (r) { return r && sbKnownCity(r.value); });
            if (ok.length !== rec.length) localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(ok));
        }
    } catch (e) {}

    /*
     * Check the location BEFORE any search runs. The old search code builds the
     * results URL in several places straight from whatever is in the box, so
     * "sddddd" still searched and only bounced back afterwards. This runs first
     * (window, capture phase) for: picking a category suggestion, pressing
     * Enter in a search box, the search button, and form submit.
     */
    function sbLocInputFor(el) {
        // the suggestion lists may be moved elsewhere in the page for positioning
        if (el.closest("#homeCategorySuggestions")) return document.getElementById("homeLocationInput");
        if (el.closest("#headerCategorySuggestions")) return document.getElementById("headerLocationInput");
        if (el.closest("#homeSearchOriginalPlace, #homeSearchForm")) return document.getElementById("homeLocationInput");
        if (el.closest("#scrollSearchHolder, #headerSearchForm")) return document.getElementById("headerLocationInput");
        return null;
    }
    function sbClearLocError(input) {
        const box = input && input.closest(".input_location_box, .header-input_location_box") || (input && input.parentElement);
        if (!box) return;
        box.classList.remove("sb-loc-invalid");
        const holder = box.parentElement || box;
        holder.querySelectorAll(".sb-loc-tip").forEach(function (t) { t.remove(); });
    }
    function sbShowLocError(input, text) {
        if (!input) return;
        const box = input.closest(".input_location_box, .header-input_location_box") || input.parentElement;
        sbClearLocError(input);
        box.classList.add("sb-loc-invalid");
        const tip = document.createElement("div");
        tip.className = "sb-loc-tip";
        tip.setAttribute("role", "alert");
        tip.innerHTML = '<i class="las la-map-marker-alt" aria-hidden="true"></i><span></span>';
        tip.querySelector("span").textContent = text;
        // above the box when there is room (hero search), so the location list
        // that opens below it can't cover the message; below in the top bar
        if (input.getBoundingClientRect().top > 100) tip.classList.add("sb-loc-tip--up");
        // the box itself clips (overflow:hidden) — hang the message on its parent
        const holder = box.parentElement || box;
        if (getComputedStyle(holder).position === "static") holder.style.position = "relative";
        holder.appendChild(tip);
        input.focus();
        try { input.select(); } catch (e) {}
        input.dispatchEvent(new Event("focus"));
    }
    window.sbShowLocError = sbShowLocError;
    function sbLocationBlocked(el) {
        const input = sbLocInputFor(el);
        if (!input) return false;
        const v = input.value.trim();
        if (v === "" || sbKnownCity(v)) return false;
        sbShowLocError(input, '"' + v + '" is not a location we cover. Please pick your city or area from the list.');
        return true;
    }
    window.addEventListener("click", function (e) {
        const t = e.target;
        if (!t || !t.closest) return;
        const isSuggestion = t.closest("#homeCategorySuggestions a, #homeCategorySuggestions .dropdown-item, #homeCategorySuggestions li, #headerCategorySuggestions .header-dropdown-item, #headerCategorySuggestions a");
        const isButton = t.closest("#homeSearchForm button, #homeSearchForm [type=submit], #homeSearchForm .search-icon, #headerSearchForm button, #headerSearchForm [type=submit], #headerSearchForm .search-icon");
        if (!isSuggestion && !(isButton && !t.closest("#homeVoiceSearchBtn, #headerVoiceSearchBtn, #headerClearBtn"))) return;
        if (sbLocationBlocked(t)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);
    window.addEventListener("keydown", function (e) {
        if (e.key !== "Enter" || !e.target || !e.target.closest) return;
        if (!e.target.matches("#homeCategorySearch, #headerCategorySearch, #homeLocationInput, #headerLocationInput")) return;
        if (sbLocationBlocked(e.target)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);
    window.addEventListener("submit", function (e) {
        if (e.target && e.target.matches && e.target.matches("#homeSearchForm, #headerSearchForm") && sbLocationBlocked(e.target)) {
            e.preventDefault(); e.stopImmediatePropagation();
        }
    }, true);
    ["homeLocationInput", "headerLocationInput"].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", function () { sbClearLocError(el); });
    });

    const englishLocationNames = {
        "जयपुर": "Jaipur",
        "दिल्ली": "Delhi",
        "नई दिल्ली": "New Delhi",
        "मुंबई": "Mumbai",
        "बेंगलुरु": "Bengaluru",
        "बैंगलोर": "Bengaluru",
        "हैदराबाद": "Hyderabad",
        "अहमदाबाद": "Ahmedabad",
        "चेन्नई": "Chennai",
        "कोलकाता": "Kolkata",
        "पुणे": "Pune",
        "सूरत": "Surat",
        "लखनऊ": "Lucknow",
        "कानपुर": "Kanpur",
        "नागपुर": "Nagpur",
        "इंदौर": "Indore",
        "भोपाल": "Bhopal",
        "पटना": "Patna",
        "वडोदरा": "Vadodara",
        "गाजियाबाद": "Ghaziabad",
        "लुधियाना": "Ludhiana",
        "आगरा": "Agra",
        "नासिक": "Nashik",
        "फरीदाबाद": "Faridabad",
        "मेरठ": "Meerut",
        "राजकोट": "Rajkot",
        "वाराणसी": "Varanasi",
        "श्रीनगर": "Srinagar",
        "औरंगाबाद": "Aurangabad",
        "धनबाद": "Dhanbad",
        "अमृतसर": "Amritsar",
        "नवी मुंबई": "Navi Mumbai",
        "प्रयागराज": "Prayagraj",
        "इलाहाबाद": "Prayagraj",
        "रांची": "Ranchi",
        "हावड़ा": "Howrah",
        "कोयंबटूर": "Coimbatore",
        "जबलपुर": "Jabalpur",
        "ग्वालियर": "Gwalior",
        "विजयवाड़ा": "Vijayawada",
        "जोधपुर": "Jodhpur",
        "मदुरै": "Madurai",
        "रायपुर": "Raipur",
        "कोटा": "Kota",
        "चंडीगढ़": "Chandigarh",
        "गुवाहाटी": "Guwahati",
        "मैसूर": "Mysuru",
        "उदयपुर": "Udaipur",
        "अजमेर": "Ajmer",
        "अलवर": "Alwar",
        "भीलवाड़ा": "Bhilwara",
        "बीकानेर": "Bikaner"
    };

    window.normalizeEnglishLocationName = function(value) {
        let city = String(value || "").replace(/\s+/g, " ").trim();
        if (!city) return "";
        if (englishLocationNames[city]) return englishLocationNames[city];
        if (city.includes(",")) {
            const parts = city.split(",").map(function(part){ return part.trim(); }).filter(Boolean);
            for (let i = parts.length - 1; i >= 0; i--) {
                if (englishLocationNames[parts[i]]) return englishLocationNames[parts[i]];
            }
            // No Hindi part matched — this is an ordinary "Area, City"
            // string (the shape used everywhere in this app for a
            // suggestion/recent search), not a Hindi geocoder result that
            // needs translating. Return it exactly as given instead of
            // collapsing to just the last comma-separated part, which used
            // to silently drop "Vaishali Nagar" from "Vaishali Nagar,
            // Jaipur" down to just "Jaipur" — this function is called on
            // every location selection via cleanCity()/setCity(), so that
            // collapse showed up as the header always snapping back to the
            // bare city name no matter which area the admin actually picked.
            return city;
        }
        return englishLocationNames[city] || city;
    };

    function normalizeStoredCityValues() {
        ["jf_city", "jf_detected_city", "jf_selected_location_name", "jf_user_city"].forEach(function(key) {
            const value = localStorage.getItem(key);
            const normalized = window.normalizeEnglishLocationName(value);
            if (value && normalized && normalized !== value) {
                localStorage.setItem(key, normalized);
            }
        });
    }

    function cleanCity(value) {
        let city = window.normalizeEnglishLocationName(value);
        if (!city) return "";
        city = city.replace(/\s+/g, " ").trim();
        if (
            city.toLowerCase().includes("detect current location") ||
            city.toLowerCase().includes("no result") ||
            city.toLowerCase().includes("no location")
        ) {
            return "";
        }
        return city;
    }

    normalizeStoredCityValues();

    // ✅ SET CITY - ONLY INPUTS + LOCALSTORAGE (NO URL OVERRIDE)
  function setCity(value, save = false, lat = "", lng = "") {
    const city = cleanCity(value);
    if (!city) return;
    if (!sbKnownCity(city)) return;   // never show / store a place we don't have

    if (homeInput) {
        homeInput.value = city;
        homeInput.dataset.selectedLocation = "1";
    }

    if (headerInput) {
        headerInput.value = city;
        headerInput.dataset.selectedLocation = "1";
    }

    if (jdInput) {
        jdInput.value = city;
        jdInput.dataset.selectedLocation = "1";
    }

    if (jdCityText) {
        jdCityText.textContent = city;
    }

    // Location just changed — re-point every category link to the new city
    // immediately, so the visitor never has to refresh to see /delhi/... etc.
    if (typeof window.sbRefreshCategoryLinks === "function") {
        window.sbRefreshCategoryLinks();
    }

    if (save) {
        try {

            // ✅ Detect Location se aaya ho
            if (lat && lng) {
                localStorage.setItem("jf_detected_city", city);
                localStorage.setItem("jf_lat", lat);
                localStorage.setItem("jf_lng", lng);
                // When, so a stale city is not shown days later.
                localStorage.setItem("jf_detected_at", String(Date.now()));
            }

            // ❌ yaha jf_city set nahi karna
            // jf_city manual selected city ke liye alag set hoga

        } catch (e) {}
    }

    if (isMobile() && jdStrip) {
        jdStrip.classList.remove("show");
    }

    if (jdLocationBox) {
        jdLocationBox.classList.remove("show");
    }

    if (jdCityBtn) {
        jdCityBtn.style.display = "flex";
    }
}
    function clearCity() {
        if (homeInput) homeInput.value = "";
        if (headerInput) headerInput.value = "";
        if (jdInput) jdInput.value = "";
    }

  

function loadCityFromStorage() {
    try {
        const pathParts = window.location.pathname.split("/").filter(Boolean);
        const isRootHome = pathParts.length === 0;

        // Home page par purani location/session nahi dikhani
        if (isRootHome) {
            clearCity();
            return false;
        }

        // Search/listing page par session wali location hi dikhani
        if (sessionSelectedLocation) {
            setCity(sessionSelectedLocation, false);
            return true;
        }

    } catch (e) {}

    clearCity();
    return false;
}
const isListingDetailsPage = @json(request()->routeIs('listings.details'));

if (isListingDetailsPage) {
    const listingCity = @json($listing->city ?? $listing->location->name ?? '');

    if (listingCity) {
        setCity(listingCity, false);
    }
} else {
    loadCityFromStorage();
    sbAutoDetectCity();
}

/**
 * Fills the location box by itself when the visitor has already granted
 * location access — until now nothing detected on its own, the city only
 * appeared after someone tapped "Detect my location".
 *
 * It never asks for permission on load. A prompt nobody asked for is
 * usually dismissed, and Chrome on Android blocks repeat prompts after
 * that, which would break the button as well. So it only acts when the
 * browser already holds a granted permission.
 */
async function sbAutoDetectCity() {
    // Something was picked for this visit already — leave it alone.
    if (sessionSelectedLocation) return;
    if (!navigator.geolocation) return;

    // A city found earlier today shows at once, with no wait for GPS.
    try {
        const cached = localStorage.getItem("jf_detected_city");
        const when   = parseInt(localStorage.getItem("jf_detected_at") || "0", 10);

        // Anyone who detected before the timestamp was added has a city but no
        // date against it. Trust it once and stamp it, rather than ignoring it.
        if (cached && !when) {
            localStorage.setItem("jf_detected_at", String(Date.now()));
            setCity(cached, false);
        } else if (cached && (Date.now() - when) < 12 * 60 * 60 * 1000) {
            setCity(cached, false);
        }
    } catch (e) {}

    let granted = false;

    try {
        if (navigator.permissions && navigator.permissions.query) {
            const status = await navigator.permissions.query({ name: "geolocation" });
            granted = status.state === "granted";
        }
    } catch (e) {
        // Older browsers have no Permissions API; stay quiet rather than prompt.
    }

    if (!granted) return;

    navigator.geolocation.getCurrentPosition(async function (pos) {
        const city = await sbCityFromCoords(pos.coords.latitude, pos.coords.longitude);

        if (city) {
            setCity(city, true, pos.coords.latitude, pos.coords.longitude);
        }
    }, function () {
        // Nothing to say on a silent attempt.
    }, {
        enableHighAccuracy: false,
        // Without a timeout this can hang for ever on a phone indoors.
        timeout: 10000,
        maximumAge: 10 * 60 * 1000,
    });
}

/** Coordinates to a city name. Returns "" when it cannot be worked out. */
async function sbCityFromCoords(lat, lng) {
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat=${lat}&lon=${lng}`
        );

        if (!res.ok) return "";

        const data = await res.json();
        const a = data.address || {};

        return window.normalizeEnglishLocationName(
            a.city || a.town || a.village || a.state_district || a.county || a.district || a.state || ""
        );
    } catch (e) {
        return "";
    }
}

   
   
    // ✅ CLOSE DROPDOWN
    function closeDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.remove("show");
        dropdown.innerHTML = "";
    }

    function openDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.add("show");
    }

    function setDropdownPosition(input, dropdown) {
        if (!input || !dropdown) return;
        const rect = input.getBoundingClientRect();
        dropdown.style.position = "fixed";
        dropdown.style.top = rect.bottom + "px";
        dropdown.style.left = rect.left + "px";
        dropdown.style.width = rect.width + "px";
        dropdown.style.zIndex = "999999";
    }

    // ✅ RECENT LOCATIONS
    function getRecentLocations(){
        return JSON.parse(localStorage.getItem("service_bazzar_recent_locations") || "[]");
    }

    function saveRecentLocation(location){
        let recent = getRecentLocations();
        recent = recent.filter(item => item.value !== location.value);
        recent.unshift(location);
        recent = recent.slice(0, 5);
        localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(recent));
    }

    // ✅ RENDER JD LOCATION LIST
    function renderJdLocationList(list, dropdown) {
        if (!dropdown) return;

        const searchValue = jdInput ? jdInput.value.trim() : "";
        dropdown.innerHTML = "";

        if (searchValue === "") {
            dropdown.innerHTML = `
                <a href="javascript:void(0)"
                   class="dropdown-item smww jd-detect-location-item"
                   data-detect="1">
                    <i class="las la-crosshairs"></i>
                    Detect Location
                    <span class="jd-detect-spinner" style="display:none; margin-left:8px;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </a>
            `;
        }

        const recentLocations = getRecentLocations();
        if (searchValue === "" && recentLocations.length > 0) {
            dropdown.innerHTML += `
                <div class="jd-location-title">
                    <span>Recent Searches</span>
                    <span class="jd-clear-recent">Clear All</span>
                </div>
            `;
            recentLocations.forEach(function(item){
                dropdown.innerHTML += `
                    <a href="javascript:void(0)"
                       class="dropdown-item jd-recent-item"
                       data-value="${item.value}">
                        <i class="las la-map-marker"></i>
                        ${item.value}
                    </a>
                `;
            });
        }

        if (Array.isArray(list) && list.length > 0) {
            const showHeading = searchValue === "";
            if (showHeading) {
                dropdown.innerHTML += `<div class="jd-location-title">Trending Areas</div>`;
            }
            let usedLocations = [];
            list.forEach(function (item) {
                let name = "";
                if (typeof item === "string") {
                    name = item;
                } else if (item && typeof item === "object") {
                    name = item.name || item.location_name || item.city || item.area || item.address || item.display_name || "";
                }
                name = String(name).trim();
                if (!name || name === "[object Object]") return;
                let parentName = item.parent || "";
                let finalName = parentName ? name + ", " + parentName : name;
                let uniqueKey = finalName.toLowerCase();
                if (usedLocations.includes(uniqueKey)) return;
                usedLocations.push(uniqueKey);
                dropdown.innerHTML += `
                    <a href="javascript:void(0)"
                       class="dropdown-item"
                       data-value="${finalName}"
                       data-location-id="${item.location_id || item.id || item.locationId || ''}"
                       data-lat="${item.latitude || item.lat || ''}"
                       data-lng="${item.longitude || item.lng || ''}">
                        <i class="las la-map-marker"></i>
                        ${finalName}
                    </a>
                `;
            });
        }
        openDropdown(dropdown);
    }

    document.addEventListener("mousedown", function(e){
        if(e.target.classList.contains("jd-clear-recent")){
            e.preventDefault();
            e.stopPropagation();
            localStorage.removeItem("service_bazzar_recent_locations");
            const dropdown = document.getElementById("jdMobileLocationSuggestions");
            if(dropdown){
                dropdown.querySelectorAll(".jd-recent-item").forEach(el => el.remove());
                const recentTitle = e.target.closest(".jd-location-title");
                if(recentTitle) recentTitle.remove();
                dropdown.classList.add("show");
            }
            return false;
        }
    }, true);

    function getCurrentBaseCity(){
        let city = document.getElementById("jdMobileCityText")?.innerText.trim() ||
            localStorage.getItem("jf_user_city") ||
            localStorage.getItem("jf_city") ||
            localStorage.getItem("jf_detected_city") ||
            "";
        city = window.normalizeEnglishLocationName(city);
        if(city.includes(",")){
            city = city.split(",").pop().trim();
        }
        return city;
    }

    function fetchLocations(query, dropdown) {
        const baseCity = getCurrentBaseCity();
        const finalQuery = query && query.trim() !== "" ? query.trim() : baseCity;

        // A phone on mobile data drops a request now and then. This used to
        // close the whole list on any failure, which left the panel open with
        // nothing in it — not even "Detect Location" — and no way forward.
        const giveUp = setTimeout(function () { controller.abort(); }, 8000);
        const controller = new AbortController();

        fetch(`{{ route('nearby.location.suggestions') }}?q=${encodeURIComponent(finalQuery)}&base_city=${encodeURIComponent(baseCity)}&_=${Date.now()}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            signal: controller.signal
        })
        .then(function (res) {
            if (!res.ok) throw new Error("HTTP " + res.status);
            return res.json();
        })
        .then(function (data) {
            clearTimeout(giveUp);
            renderJdLocationList(Array.isArray(data) ? data : [], dropdown);
        })
        .catch(function (err) {
            clearTimeout(giveUp);
            console.log(err);
            // Fall back to what we can build without the server: Detect
            // Location, and whatever the visitor searched for before.
            renderJdLocationList([], dropdown);
            dropdown.classList.add("show");
        });
    }

  function attachLocationInput(input, dropdown) {
    if (!input || !dropdown) return;

    let timer;

    function showDefaultList() {
        renderJdLocationList([], dropdown);
        dropdown.classList.add("show");

        setTimeout(function () {
            fetchLocations("", dropdown);
        }, 100);
    }

    input.addEventListener("input", function () {
        clearTimeout(timer);

        const value = this.value.trim();
        this.dataset.selectedLocation = "0";

        if (value === "") {
            showDefaultList();
            return;
        }

        timer = setTimeout(function () {
            fetchLocations(value, dropdown);
        }, 300);
    });

    input.addEventListener("focus", function () {
        const value = input.value.trim();

        if (value === "") {
            showDefaultList();
            return;
        }

        fetchLocations(value, dropdown);
    });
}
    attachLocationInput(jdInput, jdDropdown);

    // ✅ DETECT CURRENT LOCATION
    async function detectCurrentLocation(detectItem = null) {
        const spinner = detectItem ? detectItem.querySelector(".jd-detect-spinner") : null;
        const showDetectPopup = function(message, blocked = false) {
            if (typeof window.showLocationPermissionPopup === "function") {
                window.showLocationPermissionPopup({
                    message: message,
                    blocked: blocked,
                    onRetry: function () {
                        detectCurrentLocation(detectItem);
                    }
                });
            }
        };
        if (!navigator.geolocation) {
            if (spinner) spinner.style.display = "none";
            showDetectPopup("This browser does not support location detection.");
            return;
        }
        const activeInput = document.activeElement === jdInput ? jdInput : document.activeElement === homeInput ? homeInput : headerInput;
        navigator.geolocation.getCurrentPosition(async function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            try {
                const city = await sbCityFromCoords(lat, lng);
                if (spinner) spinner.style.display = "none";
                if (!city) {
                    showDetectPopup("Could not work out your city. Please pick it from the list.");
                }
                if (city) {
    setCity(city, true, lat, lng);

    const isHomePage = @json(request()->routeIs('home'));

if (isHomePage && isMobile()) {
    document.body.classList.remove("show-yt-location");
    document.body.classList.add("mobile-yt-search-open");

    document.documentElement.style.removeProperty("overflow");
    document.body.style.removeProperty("overflow");

    closeDropdown(jdDropdown);

    if (jdLocationBox) jdLocationBox.classList.remove("show");
    if (jdStrip) jdStrip.classList.remove("show");
    if (jdCityBtn) jdCityBtn.style.display = "flex";

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) ytSearchBox.style.removeProperty("display");

    const ytInput = document.getElementById("ytMobileSearchInput");
    setTimeout(function () {
        if (ytInput) {
            ytInput.value = "";
            ytInput.focus();
            ytInput.click();
        }
    }, 150);

    return;
}

    const mobileCatInput = document.getElementById("ytMobileSearchInput");

    if (mobileCatInput && mobileCatInput.value.trim() !== "") {
        const citySlug = slugify(city);
        let catSlug  = slugify(mobileCatInput.value.trim());

        // Still the category this page is on: drop its own place ("...-in-south-delhi").
        if (catSlug === @json($sbCategoryRawSlug ?? '') && @json($sbCategorySlug ?? '')) catSlug = @json($sbCategorySlug ?? '');

        if (citySlug && catSlug) {
            if (!sbKnownCity(city)) { sbAskForRealCity(); return; }
            window.location.href = "/" + citySlug + "/" + catSlug;
            return;
        }
    }
} else if (activeInput) {
                    activeInput.value = "";
                }
            } catch (e) {
                console.error(e);
                if (activeInput) activeInput.value = "";
            }
            closeDropdown(homeDropdown);
            closeDropdown(headerDropdown);
            closeDropdown(jdDropdown);
        }, function (error) {
            if (spinner) spinner.style.display = "none";
            showDetectPopup(
                error && error.code === error.TIMEOUT
                    ? "Could not get a location fix. Please try again."
                    : "Please allow location access to detect your city.",
                error && error.code === error.PERMISSION_DENIED
            );
            if (activeInput) activeInput.value = "";
        }, {
            enableHighAccuracy: false,
            // The default is no timeout at all, which leaves the spinner
            // turning for ever when a phone cannot get a fix indoors.
            timeout: 15000,
            maximumAge: 5 * 60 * 1000,
        });
    }


    const ytInput = document.getElementById("ytMobileSearchInput");

if (ytInput) {

    ytInput.addEventListener("focus", function () {
        setTimeout(() => {
            this.scrollLeft = 0;
        }, 50);
    });

    ytInput.addEventListener("input", function () {
        this.scrollLeft = 0;
    });

}

    // ✅ MOUSE DOWN EVENTS
    document.addEventListener("mousedown", function (e) {
        const item = e.target.closest("#locationSuggestions .dropdown-item, #jdMobileLocationSuggestions .dropdown-item");
        if (!item) return;
        e.preventDefault();
        if (item.dataset.detect) {
            const spinner = item.querySelector(".jd-detect-spinner");
            if (spinner) spinner.style.display = "inline-block";
            detectCurrentLocation(item);
            return;
        }
       const value = cleanCity(item.dataset.value || item.textContent.trim());
if (!value) return;

setCity(value, true);

const isListingDetailsPage = @json(request()->routeIs('listings.details'));

if (isListingDetailsPage) {

    document.body.classList.remove("show-yt-location");
    document.body.classList.add("mobile-yt-search-open");

    document.documentElement.style.removeProperty("overflow");
    document.body.style.removeProperty("overflow");

    closeDropdown(jdDropdown);

    if (jdLocationBox) jdLocationBox.classList.remove("show");
    if (jdStrip) jdStrip.classList.remove("show");
    if (jdCityBtn) jdCityBtn.style.display = "flex";

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) ytSearchBox.style.removeProperty("display");

    const ytInput = document.getElementById("ytMobileSearchInput");
    setTimeout(function () {
        if (ytInput) {
            ytInput.focus();
            ytInput.click();
        }
    }, 150);

    return;
}
        const locationId = item.dataset.locationId || item.getAttribute("data-location-id") || "";
        if (jdInput) { jdInput.dataset.locationId = locationId; }
        const headerLocationIdInput = document.getElementById("headerSearchLocationId");
        if (headerLocationIdInput) { headerLocationIdInput.value = locationId; }
        saveRecentLocation({ value: value, lat: item.dataset.lat || "", lng: item.dataset.lng || "" });
    if (!isListingDetailsPage) {
    const mobileCatInput = document.getElementById("ytMobileSearchInput");

    if (mobileCatInput && mobileCatInput.value.trim() !== "") {

    let locationText = value.trim();
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
    let catSlug  = slugify(mobileCatInput.value.trim());

    // Still the category this page is on: drop its own place ("...-in-south-delhi").
    if (catSlug === @json($sbCategoryRawSlug ?? '') && @json($sbCategorySlug ?? '')) catSlug = @json($sbCategorySlug ?? '');

    if (citySlug && catSlug && !sbKnownCity(locationText)) { sbAskForRealCity(); return; }
    if (citySlug && catSlug) {
        let finalUrl = "/" + citySlug + "/" + catSlug;

        if (area !== "") {
            finalUrl += "-in-" + slugify(area);
        }

        window.location.href = finalUrl;
    }
}
}
document.body.classList.remove("show-yt-location");
document.documentElement.style.removeProperty("overflow");
document.body.style.removeProperty("overflow");

const isHomePage = @json(request()->routeIs('home'));

// Already on a category's results — reopen the same category in the place
// just picked. Without this the label changed but the listings underneath
// stayed on the old city, and nothing told the visitor why.
const sbCategorySlug = @json($sbCategorySlug ?? '');

if (!isHomePage && sbCategorySlug) {
    // "Vaishali Nagar, Jaipur" -> area + city; a bare name is the city.
    const picked = value.trim();
    const bits = picked.includes(",") ? picked.split(",") : [];
    const pickedArea = bits.length ? bits[0].trim() : "";
    const pickedCity = bits.length ? bits[1].trim() : picked;

    const target = sbCategoryUrl(sbCategorySlug, { city: pickedCity, area: pickedArea });

    if (target && target !== window.location.origin + window.location.pathname) {
        window.location.href = target;
        return;
    }
}

const ytSearchBox = document.getElementById("youtubeMobileSearch");
if (ytSearchBox) {
    ytSearchBox.style.removeProperty("display");
}

if (isHomePage) {
    // The home page opens its search itself; keep that behaviour on mobile.
    if (isMobile()) {
        document.body.classList.add("mobile-yt-search-open");

        const ytInput = document.getElementById("ytMobileSearchInput");

        setTimeout(function () {
            if (ytInput) {
                ytInput.focus();
                ytInput.click();
            }
        }, 150);
    }
} else {
    // Every other page shows the search box by default (see the page-load
    // rule above). Removing the class here was what made the category search
    // vanish as soon as the visitor changed location.
    document.body.classList.add("mobile-yt-search-open");
}

const jdStripNow = document.getElementById("jdMobileLocationStrip");
if(jdStripNow) jdStripNow.classList.remove("show");

closeDropdown(homeDropdown);
closeDropdown(headerDropdown);
closeDropdown(jdDropdown);

if (jdLocationBox) jdLocationBox.classList.remove("show");
if (jdCityBtn) jdCityBtn.style.display = "flex";
    });

    document.addEventListener("click", function (e) {
        if (!e.target.closest("#homeLocationInput") && !e.target.closest("#homeLocationSuggestions")) {
            closeDropdown(homeDropdown);
        }
        if (!e.target.closest("#locationInput") && !e.target.closest("#locationSuggestions")) {
            closeDropdown(headerDropdown);
        }
       if (
    !e.target.closest("#jdMobileCityBtn") &&
    !e.target.closest("#jdMobileLocationBox") &&
    !e.target.closest("#jdMobileLocationInput") &&
    !e.target.closest("#jdMobileLocationSuggestions")
) {
    closeDropdown(jdDropdown);
}
    });

    window.addEventListener("resize", function () {
        closeDropdown(homeDropdown);
        closeDropdown(headerDropdown);
        closeDropdown(jdDropdown);
    });

    document.querySelectorAll(".select-home-location").forEach(function (card) {
        card.addEventListener("click", function (e) {
            e.preventDefault();
            const locationName = cleanCity(this.dataset.locationName || "");
            if (!locationName) return;
            setCity(locationName, true);
            try {
                localStorage.removeItem("jf_selected_location_name");
                localStorage.removeItem("jf_selected_lat");
                localStorage.removeItem("jf_selected_lng");
            } catch (err) {}
            const bannerSection = document.querySelector(".banner-section");
            if (bannerSection) {
                bannerSection.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });

    // ✅ HOME CATEGORY SEARCH
    const homeCategoryInput = document.getElementById("homeCategorySearch");
    const homeCategoryDropdown = document.getElementById("homeCategorySuggestions");

    function closeCategoryDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.remove("show");
        dropdown.innerHTML = "";
    }

    function openCategoryDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.add("show");
    }

    function renderCategories(list, input, dropdown) {
        if (!input || !dropdown) return;
        dropdown.innerHTML = "";
        if (!list || list.length === 0) {
            dropdown.innerHTML = `<li role="option"><span class="dropdown-item" style="color:#888; cursor:default;">Not found</span></li>`;
            setDropdownPosition(input, dropdown);
            openCategoryDropdown(dropdown);
            return;
        }
        list.forEach(function (item) {
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.href = "javascript:void(0)";
            a.className = "dropdown-item";
            a.dataset.value = item.name;
            a.dataset.url = item.url || "";
            a.innerHTML = item.name;
            li.appendChild(a);
            dropdown.appendChild(li);
        });
        setDropdownPosition(input, dropdown);
        openCategoryDropdown(dropdown);
    }

    function fetchCategorySuggestions(search, input, dropdown) {
        const currentValue = search;
        const hasTrailingSpace = /\s$/.test(search || "");
        let locationId = "";
        const homeLocationId = document.getElementById("homeSearchLocationId");
        const headerLocationId = document.getElementById("headerSearchLocationId");
        if (homeLocationId && homeLocationId.value) locationId = homeLocationId.value;
        else if (headerLocationId && headerLocationId.value) locationId = headerLocationId.value;
        fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(search || "")}&location_id=${encodeURIComponent(locationId)}&exact=${hasTrailingSpace ? 1 : 0}&_=${Date.now()}`, {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (input.value !== currentValue) return;
            renderCategories(data, input, dropdown);
        })
        .catch(function (err) {
            console.error("Category suggestion error:", err);
            closeCategoryDropdown(dropdown);
        });
    }

    function attachCategoryInput(input, dropdown) {
        if (!input || !dropdown) return;
        let timer;
        input.addEventListener("input", function () {
            const value = this.value;
            clearTimeout(timer);
            if (value.trim().length < 1) {
                closeCategoryDropdown(dropdown);
                return;
            }
            timer = setTimeout(function () {
                fetchCategorySuggestions(value, input, dropdown);
            }, 250);
        });
        input.addEventListener("focus", function () {
            setDropdownPosition(input, dropdown);
        });
        dropdown.addEventListener("mousedown", function (e) { e.preventDefault(); });
        dropdown.addEventListener("click", function (e) {
            const item = e.target.closest(".dropdown-item");
            if (!item || !item.dataset.url) return;
            e.preventDefault();
            input.value = item.dataset.value || "";
            closeCategoryDropdown(dropdown);
            const baseUrl = item.dataset.url;
            const categorySlug = baseUrl.split("/").filter(Boolean).pop();
            let selectedLocation = "";
            const headerInputLoc = document.getElementById("locationInput");
            const homeInputLoc = document.getElementById("homeLocationInput");
            if (headerInputLoc && headerInputLoc.value.trim() !== "") {
                selectedLocation = headerInputLoc.value.trim();
            } else if (homeInputLoc && homeInputLoc.value.trim() !== "") {
                selectedLocation = homeInputLoc.value.trim();
            } else {
                selectedLocation = localStorage.getItem("jf_detected_city") || "";
            }
            if (selectedLocation) {
                let locationText = selectedLocation.trim();
                let city = "";
                let area = "";
                if (locationText.includes(",")) {
                    const parts = locationText.split(",");
                    area = parts[0].trim();
                    city = parts[1].trim();
                } else {
                    city = locationText.trim();
                    area = "";
                }
              window.location.href =
    `{{ route('listings.search') }}?categorysearch=${encodeURIComponent(input.value.trim())}&location_id=${encodeURIComponent(localStorage.getItem("jf_location_id") || "")}&location_name=${encodeURIComponent(selectedLocation)}`;
            } else {
                window.location.href = baseUrl;
            }
        });
    }

    document.addEventListener("click", function (e) {
        if (!e.target.closest("#CategorySearch") && !e.target.closest("#categorySuggestions")) {
            closeCategoryDropdown(categoryDropdown);
        }
        if (!e.target.closest("#homeCategorySearch") && !e.target.closest("#homeCategorySuggestions")) {
            closeCategoryDropdown(homeCategoryDropdown);
        }
    });

    // The homepage HTML is served from a shared full-page cache, so the
    // city-prefixed category URLs cannot be baked server-side — one visitor's
    // city would be cached and handed to everyone (that's how a Delhi visitor
    // was seeing /jaipur/... links). The location the visitor actually picked
    // lives client-side (the input value / localStorage), so the links are
    // resolved here, per visitor, instead.

    // Resolve the visitor's chosen location into {city, area}, or null when we
    // genuinely don't know it yet. Same precedence the location input uses.
    function sbResolveCityArea() {
        let selectedLocation = "";
        if (homeInput && homeInput.dataset.selectedLocation === "1" && homeInput.value.trim() !== "") {
            selectedLocation = homeInput.value.trim();
        } else if (headerInput && headerInput.dataset.selectedLocation === "1" && headerInput.value.trim() !== "") {
            selectedLocation = headerInput.value.trim();
        } else {
            selectedLocation = localStorage.getItem("jf_detected_city") || "";
        }
        selectedLocation = (selectedLocation || "").trim();
        if (!selectedLocation || !sbKnownCity(selectedLocation)) return null;

        let city = selectedLocation, area = "";
        if (selectedLocation.includes(",")) {
            const parts = selectedLocation.split(",");
            area = parts[0].trim();
            city = parts[1].trim();
        }
        return { city: city, area: area };
    }

    function sbCategoryUrl(categorySlug, loc) {
        const citySlug = slugify(loc.city);
        if (!citySlug || !categorySlug) return null;
        let url = `${window.location.origin}/${citySlug}/${categorySlug}`;
        if (loc.area !== "") url += `-in-${slugify(loc.area)}`;
        return url;
    }

    // Rewrite every category link's href to the visitor's city, so the
    // hover/status-bar URL, middle-click and normal click all match the
    // location shown in the input — not just a JS-intercepted left click.
    // Exposed on window so setCity() can re-run it the moment the visitor
    // changes location, with no page refresh needed.
    window.sbRefreshCategoryLinks = function () {
        const loc = sbResolveCityArea();
        if (!loc) return; // unknown city: leave the server's generic href as-is
        document.querySelectorAll(".dynamic-category-link").forEach(function (link) {
            const categorySlug = link.dataset.slug || "";
            const url = sbCategoryUrl(categorySlug, loc);
            if (url) link.setAttribute("href", url);
        });
    };

    window.sbRefreshCategoryLinks();

    // Update a single link's href from the CURRENT location, right now.
    function sbUpdateOneCategoryLink(link) {
        if (!link) return;
        const categorySlug = link.dataset.slug || "";
        if (!categorySlug) return;
        const loc = sbResolveCityArea();
        if (!loc) return; // unknown city: leave the server's generic href
        const url = sbCategoryUrl(categorySlug, loc);
        if (url) link.setAttribute("href", url);
    }

    // The real fix for "hover shows the old city until I refresh":
    // rewrite the link's href the instant the cursor (or keyboard focus, or a
    // touch) reaches it, reading the live location at that moment. This does
    // not depend on catching every place the location can change — whatever
    // the input says when you point at the link is what the status-bar URL
    // will show. Delegated on document so it also covers the banner carousel
    // and any links added after load.
    ["mouseover", "focusin", "touchstart"].forEach(function (evt) {
        document.addEventListener(evt, function (e) {
            const link = e.target.closest ? e.target.closest(".dynamic-category-link") : null;
            if (link) sbUpdateOneCategoryLink(link);
        }, true);
    });

    // Keep a click handler too, as a final safety net (e.g. a link clicked
    // without ever firing a hover, as on some touch flows).
    document.querySelectorAll(".dynamic-category-link").forEach(function (link) {
        link.addEventListener("click", function (e) {
            const categorySlug = this.dataset.slug || "";
            if (!categorySlug) return;
            const loc = sbResolveCityArea();
            if (!loc) return; // fall through to the anchor's own href
            const finalUrl = sbCategoryUrl(categorySlug, loc);
            if (!finalUrl) return;
            e.preventDefault();
            window.location.href = finalUrl;
        });
    });

    if (categoryInput && jdStrip) {
        categoryInput.addEventListener("focus", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            if (jdLocationBox) jdLocationBox.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
        homeCategoryInput.addEventListener("focus", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            jdLocationBox?.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
        homeCategoryInput.addEventListener("click", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            jdLocationBox?.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
        categoryInput.addEventListener("click", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            if (jdLocationBox) jdLocationBox.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
    }
});
</script>