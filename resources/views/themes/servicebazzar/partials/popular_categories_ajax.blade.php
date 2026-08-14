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
    <h5 class="popular-title">
        Popular Categories
    </h5>


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


                            @if(!empty($subcat->slug))

                                <a href="{{ $subcatUrl }}"
                                   class="spn popular-subcategory-link">

                                    {{ $subcat->name }}

                                </a>

                                <span class="spn desktop-separator">|</span>

                            @else

                                <span class="spn popular-subcategory-link">
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

