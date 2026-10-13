
    document.addEventListener("DOMContentLoaded", function () {

     const isListingDetailsPage = __SB_IS_DETAILS__;
    const headerLocationInput = document.getElementById("headerLocationInput");
    const headerLocationDropdown = document.getElementById("headerLocationSuggestions");
    const headerLatitudeInput = document.getElementById("headerSearchLatitude");
    const headerLongitudeInput = document.getElementById("headerSearchLongitude");
    const headerLocationIdInput = document.getElementById("headerSearchLocationId");

    if (headerLocationInput && headerLocationDropdown) {

        // ---- Restore Detected Location ----
       function headerRestoreDetectedLocation() {
    const sessionLocation = __SB_HEADER_LOCATION__;

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

            /*
             * `q` must stay empty when the box is empty — the backend
             * (ListingController::nearbyLocationSuggestions) only returns
             * "Trending Areas" (nearest-by-distance) when q === '' ; a
             * non-empty q makes it run a plain text search instead. This
             * used to fall back to the current city name here (e.g.
             * "Jaipur"), which silently turned every empty-box open into a
             * text search and meant Trending Areas never appeared in the
             * header dropdown, even though the home page's own search box
             * (home.js, same endpoint) always sends the literal empty
             * string and shows Trending Areas correctly.
             */
            const finalLat = lat
                || localStorage.getItem('jf_selected_lat')
                || localStorage.getItem('jf_lat')
                || "";
            const finalLng = lng
                || localStorage.getItem('jf_selected_lng')
                || localStorage.getItem('jf_lng')
                || "";

            const url = `__SB_URL_NEARBY__?q=${encodeURIComponent(search)}&latitude=${encodeURIComponent(finalLat)}&longitude=${encodeURIComponent(finalLng)}&_=${Date.now()}`;

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
                // Empty box -> empty q, so the backend returns Trending
                // Areas (nearest-by-distance) instead of text-searching
                // the current city's own name.
                headerFetchLocationSuggestions("");
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

            headerFetchLocationSuggestions("");
        });

        // ---- Detect Location ----
        headerLocationDropdown.addEventListener("mousedown", function (e) {
            if (e.target.classList.contains("header-clear-recent-locations")) {
                e.preventDefault();
                localStorage.removeItem("service_bazzar_recent_locations");
                headerFetchLocationSuggestions("");
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
                                            'X-CSRF-TOKEN': '__SB_CSRF__',
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
                    'X-CSRF-TOKEN': '__SB_CSRF__',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ location: selectedValue })
            });

            headerCloseLocationDropdown();
            headerLocationInput.blur();
            document.activeElement.blur();

       

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

        function headerRenderNotFoundItem() {
            const li = document.createElement("li");
            li.innerHTML = '<span class="header-dropdown-item" style="color:#888; cursor:default; justify-content:center;">Not found</span>';
            return li;
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
                ? `<span class="header-category-trending-icon"><img src="__SB_IMG_FFFFJNDJS__" alt="Trending indicator"></span>`
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

            const image = item.image || item.thumb || item.logo || "__SB_IMG_DEFAULT__";
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
                    <img src="${image}" alt="${item.name}">
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
                if (isTyping) {
                    headerCategoryDropdown.appendChild(headerRenderNotFoundItem());
                } else {
                    headerCloseCategoryDropdown();
                    return;
                }
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

    const url = `__SB_URL_CATEGORY__?q=${encodeURIComponent(cleanSearch)}&location_id=${encodeURIComponent(locationId)}&location_name=${encodeURIComponent(selectedLocation)}&exact=0&with_listings=1&_=${Date.now()}`;

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

// Typed text that is not one of our cities must never become /{junk}/{category}.
if (typeof window.sbKnownCity === "function" && !window.sbKnownCity(locationText)) {
    if (headerErrorBox) {
        headerErrorBox.innerText = "Please select location from suggestions.";
        headerErrorBox.style.display = "block";
    }
    headerLocationInput.dataset.selectedLocation = "0";
    headerLocationInput.focus();
    return;
}

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
                        'X-CSRF-TOKEN': '__SB_CSRF__',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ category: headerCategoryInput.value.trim() })
                });
            }

            if (headerLocationInput && headerLocationInput.value.trim()) {
                fetch('/set-location-session', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '__SB_CSRF__',
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
