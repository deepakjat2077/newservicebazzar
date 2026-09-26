// Extracted from index.blade.php. Each block keeps its own scope (they
// declare the same names as each other, which only worked because they were
// separate <script> programs) and re-exports its top-level declarations onto
// window, which is where the inline onclick= handlers and the other blocks
// looked for them before.

/* ---- block 1 ---- */
(function () {

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

    fetch(SB.routes.popularCategories, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "text/html",
            "X-CSRF-TOKEN": SB.csrf,
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

        const url = `${SB.routes.nearbySuggestions}?q=${encodeURIComponent(search)}&latitude=${encodeURIComponent(finalLat)}&longitude=${encodeURIComponent(finalLng)}&_=${Date.now()}`;

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
            // Empty box = trending, not "search for the city's own name" —
            // passing the city name here made the backend run its
            // full-text-match branch (returns every area whose location
            // matches, uncapped) instead of the popularity-ranked top-7
            // trending branch. Same bug/fix as headerFetchLocationSuggestions()
            // in search-form.blade.php.
            fetchLocationSuggestions("");
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

        // Same reason as the "input" listener above — empty box means
        // trending (top-7 by popularity), so this must be an empty search.
        fetchLocationSuggestions("");
    });

    // ============================================================
    // ⭐ DETECT LOCATION - COMPLETE FIX ⭐
    // ============================================================
    locationDropdown.addEventListener("mousedown", function (e) {

        if (e.target.classList.contains("clear-recent-locations")) {
            e.preventDefault();
            localStorage.removeItem("service_bazzar_recent_locations");
            // Box is still empty after clearing "Recent" — trending, not a
            // search for the city name (see the "input" listener above).
            fetchLocationSuggestions("");
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

fetch(SB.routes.saveUserLocation, {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": SB.csrf,
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
fetch(SB.routes.saveUserLocation, {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": SB.csrf,
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

  try { window.restoreDetectedLocationOnHome = restoreDetectedLocationOnHome; } catch (e) {}
})();

/* ---- block 2 ---- */
(function () {

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
            ? `<span class="category-trending-icon"><img src="${SB.img.trending}" alt="Trending indicator"></span>`
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

    const image = item.image || item.thumb || item.logo || SB.img.def;
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

    const url = `${SB.routes.categorySuggestions}?q=${encodeURIComponent(cleanSearch)}&location_id=${encodeURIComponent(locationId)}&location_name=${encodeURIComponent(selectedLocation)}&exact=0&with_listings=1&_=${Date.now()}`;

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
            // Auto-fill location if listing has an area
            if (item.dataset.area) {
                const locationInput = document.getElementById("homeLocationInput");
                if (locationInput) {
                    locationInput.value = item.dataset.area;
                    // Trigger input event to update suggestions if needed
                    locationInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            // Small delay to allow location to be set before redirecting
            setTimeout(() => {
                window.location.href = item.dataset.url;
            }, 100);
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

  try { window.fetchCategorySuggestions = fetchCategorySuggestions; } catch (e) {}
  try { window.normalizeResponse = normalizeResponse; } catch (e) {}
  try { window.searchText = searchText; } catch (e) {}
})();

/* ---- block 3 ---- */
(function () {

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

  try { window.scrollTrending = scrollTrending; } catch (e) {}
})();

/* ---- block 4 ---- */
(function () {

function scrollTrending(direction) {
    const container = document.getElementById('trendingScroll');
    const scrollAmount = 320;
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

  try { window.scrollTrending = scrollTrending; } catch (e) {}
})();

/* ---- block 5 ---- */
(function () {

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

})();

/* ---- block 6 ---- */
(function () {

function scrollPopular(direction){

    const container = document.getElementById('popularScroll');

    if(!container) return;

    container.scrollBy({
        left: direction * 320,
        behavior: 'smooth'
    });
}

  try { window.scrollPopular = scrollPopular; } catch (e) {}
})();

/* ---- block 7 ---- */
(function () {

function scrollPopular(direction){
    const container=document.getElementById('popularScroll');
    const scrollAmount=350;

    container.scrollBy({
        left:direction*scrollAmount,
        behavior:'smooth'
    });
}

  try { window.scrollPopular = scrollPopular; } catch (e) {}
})();

/* ---- block 8 ---- */
(function () {

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

})();

/* ---- block 9 ---- */
(function () {

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

  try { window.clearSearch = clearSearch; } catch (e) {}
  try { window.items = items; } catch (e) {}
  try { window.noResult = noResult; } catch (e) {}
  try { window.searchInput = searchInput; } catch (e) {}
})();

/* ---- block 10 ---- */
(function () {

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

})();

/* ---- block 11 ---- */
(function () {

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

    /*
     * Ask the browser what it already decided before touching geolocation.
     *
     * This used to call getCurrentPosition straight away, which threw a
     * permission prompt at everyone the moment the page opened. Most people
     * dismiss that, and Chrome on Android then refuses to ask again — so the
     * "Detect my location" button stopped working too. Lighthouse flags it as
     * geolocation-on-start for the same reason.
     */
    async function sbLocateWhenAllowed() {
        try {
            if (!navigator.permissions || !navigator.permissions.query) return false;
            const status = await navigator.permissions.query({ name: "geolocation" });
            return status.state === "granted";
        } catch (e) {
            return false;
        }
    }

    sbLocateWhenAllowed().then(function (allowed) {
        if (!allowed) return;

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

});

})();

/* ---- block 12 ---- */
(function () {

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

  try { window.isHome = isHome; } catch (e) {}
})();

/* ---- block 13 ---- */
(function () {

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

})();

/* ---- block 14 ---- */
(function () {

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

})();

/* ---- block 15 ---- */
(function () {

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

})();

/* ---- block 16 ---- */
(function () {

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

})();

/* ---- block 17 ---- */
(function () {

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

})();

/* ---- block 18 ---- */
(function () {

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

})();

/* ---- block 19 ---- */
(function () {

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

        fetch(SB.routes.saveUserLocation, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": SB.csrf,
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

  try { window.applyCategoryPopupState = applyCategoryPopupState; } catch (e) {}
  try { window.clearHomeCategoryInput = clearHomeCategoryInput; } catch (e) {}
  try { window.closeCategoryPopup = closeCategoryPopup; } catch (e) {}
  try { window.initPopupLocationNotice = initPopupLocationNotice; } catch (e) {}
  try { window.openCategoryPopup = openCategoryPopup; } catch (e) {}
  try { window.popupScrollY = popupScrollY; } catch (e) {}
})();

/* ---- block 20 ---- */
(function () {

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

})();

/* ---- block 21 ---- */
(function () {

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

})();

/* ---- block 22 ---- */
(function () {

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

  try { window.citySlug = citySlug; } catch (e) {}
  try { window.homeLocationInput = homeLocationInput; } catch (e) {}
  try { window.initialLocation = initialLocation; } catch (e) {}
  try { window.updateCategoryLinks = updateCategoryLinks; } catch (e) {}
})();

/* ---- block 23 ---- */
(function () {

// Preload popup background — use WebP if supported, PNG fallback
(function(){
    var src = (typeof self !== 'undefined' && self.createImageBitmap !== undefined)
        ? SB.img.sbWebp
        : SB.img.sbPng;
    var img = new Image();
    img.src = src;
})();

})();

/* ---- block 24 ---- */
(function () {

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

  try { window.closeBuyerPopup = closeBuyerPopup; } catch (e) {}
})();
