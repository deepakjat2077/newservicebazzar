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



@include('themes.servicebazzar.partials.inline-css', ['file' => 'search-form/search-form.css'])


@php
    // Values the search-form script needs from the server, encoded exactly the
    // way the old inline @json / {{ }} did (json_encode flags 15 = Blade's @json).
    $sfJsVars = [
        '__SB_IS_DETAILS__'      => json_encode(request()->routeIs('listings.details'), 15),
        '__SB_HEADER_LOCATION__' => json_encode($headerLocationValue, 15),
        '__SB_URL_NEARBY__'      => e(route('nearby.location.suggestions')),
        '__SB_URL_CATEGORY__'    => e(route('category.suggestions')),
        '__SB_CSRF__'            => e(csrf_token()),
        '__SB_IMG_FFFFJNDJS__'   => e(asset('assets/images/ffffjndjs.svg')),
        '__SB_IMG_DEFAULT__'     => e(asset('assets/images/default.jpg')),
    ];
@endphp
@include('themes.servicebazzar.partials.inline-js', ['file' => 'search-form/search-form.js', 'vars' => $sfJsVars])
