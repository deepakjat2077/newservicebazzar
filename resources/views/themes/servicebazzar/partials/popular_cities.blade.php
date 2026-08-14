{{--
    Shared "Popular Cities" section — desktop keeps its existing plain
    link-list appearance exactly as before; on mobile (<=767px) it becomes
    a single-level accordion: tapping "Popular Cities" reveals the city
    list, tapping it again hides it. Cities themselves have no nested
    toggle (see popular-accordion.js's bindMainToggle — it only wires the
    single main heading here, nothing per-city).
--}}
@if(isset($locations) && $locations->where('is_popular', 1)->count() > 0)

<div class="popular-city-section">

    {{-- Mobile-only heading button; hidden on desktop via CSS --}}
    <button type="button"
            class="popular-mobile-main-toggle"
            aria-expanded="false"
            aria-controls="popularCityListMobile"
            data-target="#popularCityListMobile">

        <span>Popular Cities</span>

        <span class="popular-toggle-icon" aria-hidden="true">
            <i class="fas fa-plus"></i>
        </span>
    </button>

    {{-- Desktop heading; hidden on mobile via CSS --}}
    <h5 class="popular-title">Popular Cities</h5>

    <div class="popular-city-list" id="popularCityListMobile">
        @foreach($locations->where('is_popular', 1) as $location)
            <a class="spn popular-city-link"
               href="{{ url(\Illuminate\Support\Str::slug($location->name)) }}"
               data-city="{{ $location->name }}"
               data-lat="{{ $location->latitude ?? '' }}"
               data-lng="{{ $location->longitude ?? '' }}"
               data-location-id="{{ $location->id }}">
                {{ $location->name }}
            </a>
            <span>|</span>
        @endforeach
    </div>

</div>

@endif
