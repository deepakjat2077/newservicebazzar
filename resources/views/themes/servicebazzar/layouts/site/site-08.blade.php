<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchBtn = document.getElementById("mobileHeaderSearchBtn");
    const backBtn = document.getElementById("ytSearchBackBtn");

    if(backBtn){
    backBtn.addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        document.body.classList.remove("show-yt-location");

        document.documentElement.style.removeProperty("overflow");
        document.body.style.removeProperty("overflow");

        jdStrip?.classList.remove("show");
        closeJdLocationBox();
        closeLocationSuggestions();
        closeCategorySuggestions();

        if (isHomePage) {
            document.body.classList.remove("mobile-yt-search-open");
        } else {
            document.body.classList.add("mobile-yt-search-open");
        }

    }, true);
}
    const input = document.getElementById("ytMobileSearchInput");
    const isHomePage = @json(request()->routeIs('home'));
    if (window.innerWidth <= 991) {
        if (isHomePage) {
            document.body.classList.remove("mobile-yt-search-open");
        } else {
            document.body.classList.add("mobile-yt-search-open");
        }
    }
    const suggestions = document.getElementById("ytMobileCategorySuggestions");
    let ytAllowBlankSuggestion = false;
    let ytLastSearchValue = "";

function showRecentTrendingInYtSearch(){
    if (!input) return;

    ytLastSearchValue = input.value;

    ytAllowBlankSuggestion = true;
    fetchMobileCategorySuggestions("");
}

    let ytSelectedCategoryUrl = "";
    
    const micBtn = document.getElementById("ytMicBtn");
    const jdStrip = document.getElementById("jdMobileLocationStrip");
    const jdCityBtn = document.getElementById("jdMobileCityBtn");
    const jdLocationBox = document.getElementById("jdMobileLocationBox");
    const jdInput = document.getElementById("jdMobileLocationInput");
    function slugify(text){
        return String(text || "").toLowerCase().trim().replace(/,/g, "").replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "").replace(/-+/g, "-").replace(/^-+|-+$/g, "");
    }
    function closeLocationSuggestions(){
        const locSug = document.getElementById("jdMobileLocationSuggestions");
        if(locSug){
            locSug.classList.remove("show");
            locSug.innerHTML = "";
        }
    }
    function closeCategorySuggestions(){
        if(suggestions){
            suggestions.classList.remove("show");
            suggestions.innerHTML = "";
        }
    }
    function closeJdLocationBox(){
        if(jdLocationBox) jdLocationBox.classList.remove("show");
        if(jdCityBtn) jdCityBtn.style.display = "flex";
        closeLocationSuggestions();
    }
function getRecentMobileCategories(){
    const raw = JSON.parse(localStorage.getItem("service_bazzar_recent_categories") || "[]");
    
    return raw
        .filter(function(item){
            let type = "category";
            
            if (typeof item === "string") {
                return false; // Purane string format ko skip karo
            }
            
            if (item && typeof item === "object") {
                type = item.type || "category";
            }
            
            type = String(type).toLowerCase(); // ✅ YEH IMPORTANT HAI
            
            // ✅ SIRF CATEGORY TYPE WALE ITEMS LO
            return type === "category" || type === "subcategory";
        })
        .map(function(item){
            return item.name || item.value || "";
        })
        .filter(Boolean);
}
function saveRecentMobileCategory(item){
    let name = "";
    let type = "category";

    if (typeof item === "string") {
        name = item;
        type = "category";
    } else if (item && typeof item === "object") {
        name = item.name || item.value || "";
        type = item.type || "category";
    }

    name = String(name || "").trim();
    type = String(type || "category").toLowerCase(); // ✅ YEH IMPORTANT HAI

    if (!name) return;

    // ✅ LISTING ITEMS KO SAVE MAT KARO
    if (type === "listing") {
        console.log("Skipping listing:", name);
        return;
    }

    // ✅ SIRF CATEGORY TYPE SAVE KARO
    if (type !== "category" && type !== "subcategory") {
        console.log("Skipping unknown type:", type, name);
        return;
    }

    let recent = JSON.parse(localStorage.getItem("service_bazzar_recent_categories") || "[]");

    recent = recent.filter(function(oldItem){
        const oldName = typeof oldItem === "string" ? oldItem : (oldItem.name || oldItem.value || "");
        return oldName.toLowerCase() !== name.toLowerCase();
    });

    recent.unshift({
        name: name,
        type: type
    });

    recent = recent.slice(0, 5);

    localStorage.setItem(
        "service_bazzar_recent_categories",
        JSON.stringify(recent)
    );
}
function renderSuggestions(data) {
    if (!suggestions) return;

    suggestions.innerHTML = "";

 const searchValue = (input?.value || "").trim();
const showRecentTrending = ytAllowBlankSuggestion === true;

if (searchValue.length === 0 && ytAllowBlankSuggestion === false) {
    suggestions.innerHTML = "";
    suggestions.classList.remove("show");
    return;
}
    // ===== RECENT SEARCHES =====
    if (showRecentTrending) {
        const recent = getRecentMobileCategories();

        if (recent.length > 0) {
            suggestions.innerHTML += `
                <li>
                    <div class="yt-section-header">
                        Recent Searches
                        <span class="yt-clear-btn" id="ytClearRecentCat">Clear All</span>
                    </div>
                </li>
            `;

            recent.forEach(function (name) {
                if (
    name.toLowerCase().includes("dealer") ||
    name.toLowerCase().includes("showroom") ||
    name.toLowerCase().includes("consultant") ||
    name.toLowerCase().includes("service in") ||
    name.toLowerCase().includes("near me")
) {
    return;
}

                suggestions.innerHTML += `
                    <li>
                        <a href="javascript:void(0)"
                           class="yt-recent-item"
                           data-name="${name}"
                           data-url="">
                            <i class="las la-history"></i>
                            ${name}
                        </a>
                    </li>
                `;
            });
        }
    }

    // ===== TRENDING SEARCHES =====
    if (showRecentTrending && data && data.length > 0) {
        suggestions.innerHTML += `
            <li>
                <div class="yt-section-header">Trending Searches</div>
            </li>
        `;

        data.forEach(function (item) {
            const isListing = item.type === "listing" || item.rating || item.reviews || item.area;

            if (isListing) {
                // ===== LISTING ITEM (2nd image style) =====
                const rating = item.rating || "";
                const reviews = item.reviews || item.review || "";
                const area = item.area || "";
                const category = item.category || "";

                let metaHtml = "";

                if (rating) {
                    metaHtml += `<span class="yt-rating">${rating} ★</span>`;
                }

                if (reviews) {
                    metaHtml += ` <span class="yt-review">(${reviews})</span>`;
                }

                if (area) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-area">${area}</span>`;
                }

                if (category) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-category-tag">${category}</span>`;
                }

                suggestions.innerHTML += `
                    <li>
                        <a href="${item.url || 'javascript:void(0)'}"
                           class="yt-listing-item"
                           data-name="${item.name}"
                            data-type="listing"
                           data-url="${item.url || ''}">

                            <div class="yt-listing-title">
                                ${item.name}
                            </div>

                            <div class="yt-listing-meta">
                                ${metaHtml}
                            </div>

                        </a>
                    </li>
                `;

            } else {
                // ===== CATEGORY ITEM =====
                suggestions.innerHTML += `
                    <li>
                      <a href="javascript:void(0)"
   class="yt-category-item"
   data-name="${item.name}"
   data-type="category"
   data-url="${item.url || ''}">

                            <img src="/assets/images/ffffjndjs.svg"
                                 alt="Trending indicator"
                                 class="yt-category-icon">

                            <span>${item.name}</span>

                        </a>
                    </li>
                `;
            }
        });
    }

    // ===== TYPING SEARCH (only exact matches) =====
// ===== TYPING SEARCH (only exact matches) =====
if (!showRecentTrending && data && data.length > 0) {
    suggestions.innerHTML = "";

    const rawSearch = input ? input.value : "";
    const searchValue = rawSearch.toLowerCase().replace(/\s+/g, " ").trim();
    const hasTrailingSpace = /\s$/.test(rawSearch);

    data.forEach(function (item) {

        const itemName = String(item.name || "")
            .toLowerCase()
            .replace(/\s+/g, " ")
            .trim();

        if (hasTrailingSpace) {
            if (itemName !== searchValue && !itemName.startsWith(searchValue + " ")) {
                return;
            }
        } else {
            if (searchValue === "car" && itemName.startsWith("career")) {
                return;
            }

            if (searchValue && !itemName.includes(searchValue)) {
                return;
            }
        }

        const isListing = item.type === "listing" || item.rating || item.reviews || item.area;

            if (isListing) {
                const rating = item.rating || "";
                const reviews = item.reviews || item.review || "";
                const area = item.area || "";
                const category = item.category || "";

                let metaHtml = "";

                if (rating) {
                    metaHtml += `<span class="yt-rating">${rating} ★</span>`;
                }

                if (reviews) {
                    metaHtml += ` <span class="yt-review">(${reviews})</span>`;
                }

                if (area) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-area">${area}</span>`;
                }

                if (category) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-category-tag">${category}</span>`;
                }

                suggestions.innerHTML += `
                    <li>
                       <a href="${item.url || 'javascript:void(0)'}"
   class="yt-listing-item"
   data-name="${item.name}"
   data-type="listing"
   data-url="${item.url || ''}">

                            <div class="yt-listing-title">
                                ${item.name}
                            </div>

                            <div class="yt-listing-meta">
                                ${metaHtml}
                            </div>

                        </a>
                    </li>
                `;

            } else {
                suggestions.innerHTML += `
                    <li>
                       <a href="javascript:void(0)"
   class="yt-category-item"
   data-name="${item.name}"
   data-type="category"
   data-url="${item.url || ''}">

                            <img src="/assets/images/ffffjndjs.svg"
                                 alt="Trending indicator"
                                 class="yt-category-icon">

                            <span>${item.name}</span>

                        </a>
                    </li>
                `;
            }
        });
    }

    // ===== NO RESULT =====
    if (suggestions.innerHTML.trim() === "") {
        suggestions.innerHTML = `
            <li>
                <span style="display:block;padding:20px 16px;color:#999;font-size:14px;text-align:center;">
                    Not found
                </span>
            </li>
        `;
    }

    // #ytMobileCategorySuggestions' CSS `top` was a hardcoded 95px,
    // assuming the search bar above it is always that tall. On pages
    // where it's actually shorter (~50px), that left a gap between the
    // search bar and the dropdown with the underlying page's own content
    // (e.g. its "Popular X in Y" heading) visible through it. Position it
    // from the search bar's real, current bottom edge instead — same
    // pattern already used for the location dropdown via
    // setDropdownPosition() above.
    const ytSearchBar = document.getElementById("youtubeMobileSearch");
    if (ytSearchBar) {
        suggestions.style.top = ytSearchBar.getBoundingClientRect().bottom + "px";
    }

    suggestions.classList.add("show");
    suggestions.style.display = "";
}
function getMobileSelectedLocationData(){
    const locationText =
        document.getElementById("jdMobileLocationInput")?.value.trim() ||
        document.getElementById("jdMobileCityText")?.innerText.trim() ||
        localStorage.getItem("jf_selected_location_name") ||
        localStorage.getItem("jf_city") ||
        "";

    const locationId =
        document.getElementById("jdMobileLocationInput")?.dataset.locationId ||
        document.getElementById("headerSearchLocationId")?.value ||
        localStorage.getItem("jf_location_id") ||
        "";

    return {
        locationText: locationText,
        locationId: locationId
    };
}

function fetchMobileCategorySuggestions(value){
    const loc = getMobileSelectedLocationData();

    fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value || "")}&location_id=${encodeURIComponent(loc.locationId)}&location_name=${encodeURIComponent(loc.locationText)}&_=${Date.now()}`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(function(data) {
        console.log("Backend data:", data); // ✅ YE CHECK KARO
        renderSuggestions(data);
    })
    .catch(() => closeCategorySuggestions());
}

   let timer;

if(searchBtn){
    searchBtn.addEventListener("click", function(e){
        e.preventDefault();

        document.body.classList.add("mobile-yt-search-open");
        document.body.classList.remove("show-yt-location");

        if (jdLocationBox) {
            jdLocationBox.classList.remove("show");
        }

        if (jdCityBtn) {
            jdCityBtn.style.display = "flex";
        }

        closeLocationSuggestions();
        closeJdLocationBox();

        setTimeout(function(){

            if(input){
                input.focus();
                input.click();

                setTimeout(function(){
                    input.blur();
                    input.scrollLeft = 0;
                }, 50);
            }

        }, 100);
    });
}


 if(input){

   input.addEventListener("click", function(){
    closeLocationSuggestions();
    closeJdLocationBox();

    document.body.classList.add("show-yt-location");
    document.documentElement.style.overflow = "";
    document.body.style.overflow = "";

    if(jdStrip) jdStrip.classList.add("show");
    if(jdCityBtn) jdCityBtn.style.display = "flex";

    showRecentTrendingInYtSearch();
});

 input.addEventListener("focus", function(){
    showRecentTrendingInYtSearch();
});

 input.addEventListener("input", function(e){
    closeLocationSuggestions();

    this.dataset.selectedSuggestion = "0";
    ytSelectedCategoryUrl = "";

    const value = this.value.trim();
    clearTimeout(timer);

    ytAllowBlankSuggestion = false;

    if(value === ""){
        closeCategorySuggestions();
        return;
    }

    timer = setTimeout(function(){
        fetchMobileCategorySuggestions(value);
    }, 250);
});
}


 
    if(jdInput){
        jdInput.addEventListener("click", function(e){ e.stopPropagation(); closeCategorySuggestions(); });
        jdInput.addEventListener("input", function(){ closeCategorySuggestions(); });
        jdInput.addEventListener("focus", function(){ closeCategorySuggestions(); });
    }

document.addEventListener("pointerdown", function(e){
    const clickedCityBtn = e.target.closest("#jdMobileCityBtn");
    const clickedLocationInput = e.target.closest("#jdMobileLocationInput");
    const clickedLocationSuggestion = e.target.closest("#jdMobileLocationSuggestions");
    const clickedMobileSearch = e.target.closest("#youtubeMobileSearch");
    const clickedLocationBox = e.target.closest("#jdMobileLocationBox"); 
    if (clickedCityBtn || clickedLocationInput || clickedLocationSuggestion || clickedMobileSearch || clickedLocationBox) {
        return;
    }

    document.body.classList.remove("show-yt-location");
    if (jdStrip) jdStrip.classList.remove("show");
    closeJdLocationBox();
    closeCategorySuggestions();
}, true);


    document.addEventListener("click", function(e){

    // Category Clear All
    if(e.target.closest("#ytClearRecentCat")){
        e.preventDefault();
        e.stopPropagation();

        localStorage.removeItem("service_bazzar_recent_categories");

        const input = document.getElementById("ytMobileSearchInput");
        const value = input ? input.value.trim() : "";

       fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value)}&_=${Date.now()}`, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => renderSuggestions(data));

        return;
    }

    // Location Clear All
    if(e.target.closest(".jd-clear-recent")){
        e.preventDefault();
        e.stopPropagation();

        localStorage.removeItem("service_bazzar_recent_locations");

        fetchLocations(getCurrentBaseCity(), jdDropdown);
        return;
    }

    // Single Location Remove
    const removeLoc = e.target.closest(".jd-remove-recent");
    if(removeLoc){
        e.preventDefault();
        e.stopPropagation();

        const value = removeLoc.dataset.value || "";
        let recent = getRecentLocations();
        recent = recent.filter(item => item.value !== value);

        localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(recent));

        fetchLocations(getCurrentBaseCity(), jdDropdown);
        return;
    }

});
   if(suggestions){
    suggestions.addEventListener("mousedown", function(e){
        const item = e.target.closest("a");
        if(!item) return;

        e.preventDefault();

        const url = item.dataset.url || "";
        const name = item.dataset.name || "";
        const type = item.dataset.type || "category";

        if(!name) return;

        if(input) {
            input.value = name;
            input.dataset.selectedSuggestion = "1";
        }

        ytSelectedCategoryUrl = url;

        saveRecentMobileCategory({
            name: name,
            type: type
        });

const categorySlug = url
    ? url.split("/").filter(Boolean).pop()
    : slugify(name);

let currentCity =
    document.getElementById("jdMobileCityText")?.innerText.trim() ||
    document.getElementById("jdMobileLocationInput")?.value.trim() ||
    localStorage.getItem("jf_city") ||
    localStorage.getItem("jf_selected_location_name") ||
    localStorage.getItem("jf_detected_city") ||
    "jaipur";

if (currentCity.includes(",")) {
    currentCity = currentCity.split(",").pop().trim();
}

const citySlug = slugify(currentCity);

window.location.href = `${window.location.origin}/${citySlug}/${categorySlug}`;
    });
}
    if(micBtn){
        micBtn.addEventListener("click", function(){
            const voiceModal = document.getElementById("voiceModal");
            const voiceCloseBtn = document.getElementById("voiceCloseBtn");
            const voiceStatusText = document.getElementById("voiceStatusText");
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if(!SpeechRecognition){
                alert("Voice search is not supported in this browser");
                return;
            }
            const recognition = new SpeechRecognition();
            recognition.lang = "en-IN";
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            if(voiceModal) voiceModal.classList.add("show");
            if(voiceStatusText) voiceStatusText.innerText = "Listening.....";
            recognition.start();
            recognition.onresult = function(event){
                const text = event.results[0][0].transcript;
                if(input){
                    input.value = text;
                    input.dispatchEvent(new Event("input"));
                }
                if(voiceStatusText) voiceStatusText.innerText = text;
                setTimeout(function(){ if(voiceModal) voiceModal.classList.remove("show"); },700);
            };
            recognition.onerror = function(){
                if(voiceStatusText) voiceStatusText.innerText = "Please try again";
                setTimeout(function(){ if(voiceModal) voiceModal.classList.remove("show"); },900);
            };
            recognition.onend = function(){
                setTimeout(function(){ if(voiceModal) voiceModal.classList.remove("show"); },800);
            };
            if(voiceCloseBtn){
                voiceCloseBtn.onclick = function(){
                    recognition.stop();
                    if(voiceModal) voiceModal.classList.remove("show");
                };
            }
        });
    }
});
</script>