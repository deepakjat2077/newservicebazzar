@if(isset($popularCategories) && $popularCategories->count() > 0)

<div class="popular-category-section mt-4">

    {{-- Mobile main heading --}}
    <button type="button"
            class="popular-mobile-main-toggle"
            aria-expanded="false"
            aria-controls="popularSubcategoryArea"
            data-target="#popularSubcategoryArea">

        <span>Popular Categories</span>

        <span class="popular-toggle-icon" aria-hidden="true">
            <i class="fas fa-plus"></i>
        </span>
    </button>
    {{-- Section heading: h5 straight after the page h1 skips levels. The size
         comes from .popular-title, so the tag can be correct. --}}
    <h2 class="popular-title">
        Popular Categories
    </h2>


    {{-- Desktop tabs --}}
    <div class="popular-category-tabs">

        @foreach($popularCategories as $key => $category)

            <button type="button"
                    class="popular-cat-tab {{ $key === 0 ? 'active' : '' }}"
                    data-tab="popular-cat-{{ $category->id }}">

                {{ $category->name }}

            </button>

        @endforeach

    </div>


    {{-- Mobile categories + desktop subcategory content --}}
    <div class="popular-subcategory-area" id="popularSubcategoryArea">

        @foreach($popularCategories as $key => $category)

            <div class="popular-mobile-category">

                {{-- Mobile category heading --}}
                <button type="button"
                        class="popular-mobile-category-toggle"
                        aria-expanded="false">

                    <span>{{ $category->name }}</span>

                    <span class="category-toggle-icon" aria-hidden="true">
                        <i class="fas fa-plus"></i>
                    </span>

                </button>


                {{-- Desktop and mobile subcategories --}}
                <div class="popular-subcat-box {{ $key === 0 ? 'active' : '' }}"
                     id="popular-cat-{{ $category->id }}">

                    @if($category->subcategories->count() > 0)

                        {{-- No ->take() here: the cap lives in
                             SiteController::POPULAR_SUBCATEGORY_LIMIT so the
                             cached payload is already trimmed. Trimming at
                             render time meant caching rows nobody drew. --}}
                        @foreach($category->subcategories as $subcat)

                            @php
                                // $popularCategoriesLocation is the location the
                                // tabs above were actually ranked for. The session
                                // fallback is only for callers that don't share it
                                // yet — reading the session here first is what let
                                // a Jaipur-ranked list link to /delhi/... when a
                                // stale detected_city was still sitting in session.
                                $currentLocationName =
                                    ($popularCategoriesLocation->name ?? null)
                                    ?: session('selected_location')
                                    ?: session('detected_city');

                                // The session value can be an "Area, City"
                                // string (e.g. "Hathoj, Jaipur"). Slugified
                                // whole it becomes "hathoj-jaipur" — not a real
                                // city slug — and the /{city}/{slug} URL 404s.
                                // Normalise to just the city before slugging.
                                $currentLocationName = $currentLocationName
                                    ? english_location_name($currentLocationName)
                                    : $currentLocationName;

                                $subcatUrl = !empty($currentLocationName)
                                    ? url(
                                        \Illuminate\Support\Str::slug(
                                            $currentLocationName
                                        ) . '/' . $subcat->slug
                                    )
                                    : route(
                                        'listings.subcategory',
                                        ['slug' => $subcat->slug]
                                    );
                            @endphp

                            {{-- Skip subcategories whose name names a city other
                                 than the one we're building the URL for (e.g.
                                 "Car Rental For Coimbatore" under /jaipur/). As a
                                 /{city}/{slug} link that's an internal link to a
                                 doorway page — already served noindex and kept
                                 out of the sitemap — so linking to it here would
                                 keep Google crawling the doorway pattern. Only
                                 applies to the city-prefixed URL; the
                                 /listings/subcategory fallback (no current city)
                                 is the canonical page and is left alone. --}}
                            @if(!empty($currentLocationName) && landerMentionsForeignCity($subcat->name, $currentLocationName))
                                @continue
                            @endif

                            @if(!empty($subcat->slug))

                                <a href="{{ $subcatUrl }}"
                                   class="spn popular-subcategory-link"
                                   {{-- Different anchor text per category helps search
                                        engines interpret link context. Same link text
                                        repeated 100s of times dilutes meaning. --}}
                                   title="{{ $category->name }} &raquo; {{ $subcat->name }}">

                                    {{ $subcat->name }}

                                </a>

                                <span class="spn desktop-separator">|</span>

                            @else

                                <span class="spn popular-subcategory-link"
                                      title="{{ $category->name }} &raquo; {{ $subcat->name }}">
                                    {{ $subcat->name }}
                                </span>

                                <span class="spn desktop-separator">|</span>

                            @endif

                        @endforeach

                    @else

                        <p class="no-subcategory">
                            No subcategories found.
                        </p>

                    @endif

                </div>

            </div>

        @endforeach

    </div>

</div>

@endif

