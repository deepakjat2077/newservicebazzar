{{--
    Shared "Popular Cities" section — desktop keeps its existing plain
    link-list appearance exactly as before; on mobile (<=767px) it becomes
    a single-level accordion: tapping "Popular Cities" reveals the city
    list, tapping it again hides it. Cities themselves have no nested
    toggle (see popular-accordion.js's bindMainToggle — it only wires the
    single main heading here, nothing per-city).
--}}
@php
    // Popular cities: filter by is_popular=1 AND has >= 1 active listing.
    // Empty cities (0 listings) should not appear — they're thin pages marked
    // noindex and have nothing for users to see.
    $popularCitiesWithListings = $locations
        ->where('is_popular', 1)
        ->filter(function ($loc) {
            $count = \App\Models\Listing::where('status', 1)
                ->where('location_id', $loc->id)
                ->count();
            return $count > 0;
        });
@endphp

@if(isset($locations) && $popularCitiesWithListings->count() > 0)

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
    <h2 class="popular-title">Popular Cities</h2>

    <div class="popular-city-list" id="popularCityListMobile">
        @foreach($popularCitiesWithListings as $location)
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
