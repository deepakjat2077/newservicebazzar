@extends($themeView.'layouts.site')

@section('title', 'Browse Services by City | ServiceBazzar')

@php
    $seoDescription = 'Browse and find local businesses by city and category on ServiceBazzar.';
@endphp

@section('content')

<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 15px; color: #111;">Browse Services & Businesses</h1>
        </div>
    </div>

    <!-- Step 1: Cities Selection -->
    <div id="step-cities" class="row">
        <div class="col-12">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #333;">Select Your City</h2>
            <div id="cities-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;">
                @foreach($locations->sortBy('name') as $location)
                    <button type="button" class="city-btn" data-city-slug="{{ $location->slug }}" data-city-name="{{ $location->name }}"
                        style="padding: 10px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; color: #333; font-size: 14px; font-weight: 500; transition: all 0.2s; text-align: center;">
                        {{ $location->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Step 2: Categories Selection (Hidden Initially) -->
    <div id="step-categories" class="row" style="display: none; margin-top: 30px;">
        <div class="col-12">
            <button type="button" onclick="resetSteps()" style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px; cursor: pointer; color: #333; font-size: 13px; margin-bottom: 20px;">← Change City</button>
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 15px; color: #333;">Services in <span id="selected-city-name" style="color: #0d6efd; font-weight: 700;"></span></h2>

            <!-- Category Search -->
            <div style="margin-bottom: 20px;">
                <input type="text" id="category-search" placeholder="Search services..."
                    style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
            </div>

            <div id="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;">
            </div>
        </div>
    </div>

    <!-- Step 3: Listings (Hidden Initially) - JustDial Style -->
    <div id="step-listings" class="row" style="display: none; margin-top: 30px;">
        <div class="col-12">
            <button type="button" onclick="backToCategories()" style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px; cursor: pointer; color: #333; font-size: 13px; margin-bottom: 20px;">← Back</button>
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #333;"><span id="selected-category-name" style="color: #0d6efd; font-weight: 700;"></span> in <span id="selected-city-name-2" style="color: #0d6efd; font-weight: 700;"></span></h2>

            <div id="listings-container" style="background: white; border: 1px solid #eee; border-radius: 4px;">
                <div id="listings-list" style="list-style: none; padding: 0; margin: 0;">
                </div>
            </div>
            <p id="no-listings-msg" style="color: #666; padding: 20px; text-align: center; display: none;">No businesses found in this category.</p>
        </div>
    </div>

    <!-- More Options -->
    <div class="row mt-5 pt-4" style="border-top: 1px solid #eee;">
        <div class="col-12">
            <h3 style="font-weight: 600; margin-bottom: 15px; color: #111;">More Options</h3>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="display: inline-block; margin-right: 25px; margin-bottom: 8px;">
                    <a href="{{ route('home') }}" style="color: #0d6efd; text-decoration: none; font-size: 14px;">Home</a>
                </li>
                <li style="display: inline-block; margin-right: 25px; margin-bottom: 8px;">
                    <a href="{{ route('blogs') }}" style="color: #0d6efd; text-decoration: none; font-size: 14px;">Blog</a>
                </li>
                <li style="display: inline-block; margin-right: 25px; margin-bottom: 8px;">
                    <a href="{{ route('contact') }}" style="color: #0d6efd; text-decoration: none; font-size: 14px;">Contact</a>
                </li>
                <li style="display: inline-block; margin-right: 25px; margin-bottom: 8px;">
                    <a href="{{ route('free.listing') }}" style="color: #0d6efd; text-decoration: none; font-size: 14px;">Post Free Listing</a>
                </li>
                <li style="display: inline-block; margin-bottom: 8px;">
                    <a href="/sitemap.xml" style="color: #0d6efd; text-decoration: none; font-size: 14px;">XML Sitemap</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
    .city-btn:hover, .category-btn:hover {
        border-color: #0d6efd !important;
        background: #f0f7ff !important;
        color: #0d6efd !important;
    }

    a[style*="color: #0d6efd"] {
        transition: color 0.2s;
    }

    a[style*="color: #0d6efd"]:hover {
        color: #0a58ca !important;
        text-decoration: underline;
    }

    #listings-list a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        #cities-grid, #categories-grid {
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)) !important;
        }
    }
</style>

<script>
    let selectedCity = null;
    let selectedCityName = null;
    let selectedCategory = null;
    let selectedCategoryName = null;

    // City selection
    document.querySelectorAll('.city-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedCity = this.dataset.citySlug;
            selectedCityName = this.dataset.cityName;

            // Show categories
            document.getElementById('step-cities').style.display = 'none';
            document.getElementById('step-categories').style.display = 'block';
            document.getElementById('step-listings').style.display = 'none';

            document.getElementById('selected-city-name').textContent = selectedCityName;

            // Load categories for this city
            loadCategories(selectedCity, selectedCityName);
        });
    });

    function loadCategories(citySlug, cityName) {
        // Fetch categories from server
        fetch(`/api/city/${citySlug}/categories`)
            .then(r => r.json())
            .then(data => {
                const grid = document.getElementById('categories-grid');
                grid.innerHTML = '';

                if (data.categories.length === 0) {
                    grid.innerHTML = '<p style="color: #666; padding: 20px;">No services available in this city.</p>';
                    return;
                }

                // Store all categories for search functionality
                window.allCategories = data.categories;

                data.categories.forEach(cat => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'category-btn';
                    btn.dataset.categorySlug = cat.slug;
                    btn.dataset.categoryName = cat.name;
                    btn.dataset.searchText = cat.name.toLowerCase();
                    btn.style.cssText = 'padding: 10px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; color: #333; font-size: 14px; font-weight: 500; transition: all 0.2s; text-align: center;';
                    btn.textContent = cat.name;

                    btn.addEventListener('click', function() {
                        selectedCategory = this.dataset.categorySlug;
                        selectedCategoryName = this.dataset.categoryName;

                        document.getElementById('step-categories').style.display = 'none';
                        document.getElementById('step-listings').style.display = 'block';
                        document.getElementById('selected-category-name').textContent = selectedCategoryName;
                        document.getElementById('selected-city-name-2').textContent = selectedCityName;

                        loadListings(selectedCity, selectedCategory);
                    });

                    grid.appendChild(btn);
                });

                // Setup search filter
                const searchBox = document.getElementById('category-search');
                searchBox.value = '';
                searchBox.addEventListener('input', function() {
                    filterCategories(this.value.toLowerCase());
                });
            })
            .catch(err => {
                console.error('Error loading categories:', err);
                document.getElementById('categories-grid').innerHTML = '<p style="color: red;">Error loading categories</p>';
            });
    }

    function filterCategories(searchText) {
        const buttons = document.querySelectorAll('.category-btn');
        let visibleCount = 0;

        buttons.forEach(btn => {
            const categoryName = btn.dataset.searchText;
            if (categoryName.includes(searchText)) {
                btn.style.display = 'block';
                visibleCount++;
            } else {
                btn.style.display = 'none';
            }
        });

        // Show "no results" message if nothing matches
        if (visibleCount === 0 && searchText.length > 0) {
            const grid = document.getElementById('categories-grid');
            if (!grid.querySelector('.no-results')) {
                const msg = document.createElement('p');
                msg.className = 'no-results';
                msg.style.cssText = 'color: #666; padding: 20px; grid-column: 1 / -1;';
                msg.textContent = 'No services found matching "' + searchText + '"';
                grid.appendChild(msg);
            }
        } else {
            const noResults = document.querySelector('.no-results');
            if (noResults) noResults.remove();
        }
    }

    function loadListings(citySlug, categorySlug) {
        // Fetch listings from API
        fetch(`/api/city/${citySlug}/category/${categorySlug}/listings`)
            .then(r => r.json())
            .then(data => {
                const listContainer = document.getElementById('listings-list');
                const noMsg = document.getElementById('no-listings-msg');

                listContainer.innerHTML = '';

                if (!data.listings || data.listings.length === 0) {
                    noMsg.style.display = 'block';
                    document.getElementById('listings-container').style.display = 'none';
                    return;
                }

                noMsg.style.display = 'none';
                document.getElementById('listings-container').style.display = 'block';

                // JustDial-style list
                data.listings.forEach((listing, index) => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding: 12px 15px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; cursor: pointer; transition: all 0.2s;';

                    item.onmouseover = () => item.style.backgroundColor = '#f9f9f9';
                    item.onmouseout = () => item.style.backgroundColor = 'transparent';

                    item.innerHTML = `
                        <span style="color: #999; min-width: 30px; margin-right: 12px; font-size: 13px;">${index + 1}.</span>
                        <a href="${listing.url}" style="color: #0d6efd; text-decoration: none; font-size: 14px; flex-grow: 1;">
                            ${listing.name}
                        </a>
                    `;

                    item.onclick = () => window.location.href = listing.url;

                    listContainer.appendChild(item);
                });
            })
            .catch(err => {
                console.error('Error loading listings:', err);
                document.getElementById('listings-list').innerHTML = '<p style="color: red; padding: 20px;">Error loading listings</p>';
            });
    }

    function resetSteps() {
        selectedCity = null;
        selectedCategory = null;

        document.getElementById('step-cities').style.display = 'block';
        document.getElementById('step-categories').style.display = 'none';
        document.getElementById('step-listings').style.display = 'none';
    }

    function backToCategories() {
        selectedCategory = null;

        document.getElementById('step-listings').style.display = 'none';
        document.getElementById('step-categories').style.display = 'block';
    }
</script>

@endsection
