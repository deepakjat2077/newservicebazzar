@extends($themeView.'layouts.site')

@section('title', 'Browse by City & Services | ServiceBazzar')

@php
    $seoDescription = 'Browse businesses by city and category on ServiceBazzar. Select a city to see local services and listings.';
@endphp

@section('content')

<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 10px; color: #111;">Browse Services by Location</h1>
            <p style="color: #666; font-size: 16px;">Select a city to explore all services and businesses available</p>
        </div>
    </div>

    <!-- Step 1: Cities Selection -->
    <div id="step-cities" class="row">
        <div class="col-12 mb-4">
            <h3 style="font-weight: 600; margin-bottom: 20px; color: #111;">Step 1: Select a City</h3>
            <div id="cities-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                @foreach($locations->sortBy('name') as $location)
                    <button type="button" class="city-btn" data-city-slug="{{ $location->slug }}" data-city-name="{{ $location->name }}"
                        style="padding: 12px; border: 2px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer; color: #111; font-weight: 500; transition: all 0.2s;">
                        {{ $location->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Step 2: Categories Selection (Hidden Initially) -->
    <div id="step-categories" class="row" style="display: none;">
        <div class="col-12 mb-4">
            <button type="button" onclick="resetSteps()" style="padding: 8px 16px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; color: #111; margin-bottom: 15px;">← Back to Cities</button>
            <h3 style="font-weight: 600; margin-bottom: 20px; color: #111;">Step 2: Select a Category in <span id="selected-city-name" style="color: #0d6efd;"></span></h3>
            <div id="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
            </div>
        </div>
    </div>

    <!-- Step 3: Listings (Hidden Initially) -->
    <div id="step-listings" class="row" style="display: none;">
        <div class="col-12 mb-4">
            <button type="button" onclick="backToCategories()" style="padding: 8px 16px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; color: #111; margin-bottom: 15px;">← Back to Categories</button>
            <h3 style="font-weight: 600; margin-bottom: 20px; color: #111;"><span id="selected-category-name" style="color: #0d6efd;"></span> in <span id="selected-city-name-2" style="color: #0d6efd;"></span></h3>
            <div id="listings-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            </div>
        </div>
    </div>

    <!-- Main Navigation Links -->
    <div class="row mt-5 pt-4" style="border-top: 1px solid #eee;">
        <div class="col-12">
            <h3 style="font-weight: 600; margin-bottom: 15px; color: #111;">More Options</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px; display: inline-block; margin-right: 20px;"><a href="{{ route('home') }}" style="color: #0d6efd; text-decoration: none;">Home</a></li>
                <li style="margin-bottom: 10px; display: inline-block; margin-right: 20px;"><a href="{{ route('blogs') }}" style="color: #0d6efd; text-decoration: none;">Blog</a></li>
                <li style="margin-bottom: 10px; display: inline-block; margin-right: 20px;"><a href="{{ route('contact') }}" style="color: #0d6efd; text-decoration: none;">Contact</a></li>
                <li style="margin-bottom: 10px; display: inline-block; margin-right: 20px;"><a href="{{ route('free.listing') }}" style="color: #0d6efd; text-decoration: none;">Post Free Listing</a></li>
                <li style="margin-bottom: 10px; display: inline-block;"><a href="/sitemap.xml" style="color: #0d6efd; text-decoration: none;">XML Sitemap</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
    .city-btn, .category-btn, .subcategory-btn {
        transition: all 0.2s;
    }

    .city-btn:hover, .category-btn:hover, .subcategory-btn:hover {
        border-color: #0d6efd !important;
        background: #f0f7ff !important;
        color: #0d6efd !important;
    }

    .city-btn.active, .category-btn.active, .subcategory-btn.active {
        border-color: #0d6efd !important;
        background: #0d6efd !important;
        color: white !important;
    }

    a[style*="color: #0d6efd"] {
        transition: color 0.2s;
    }

    a[style*="color: #0d6efd"]:hover {
        color: #0a58ca !important;
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .container {
            padding: 20px 10px;
        }

        #cities-grid, #categories-grid, #subcategories-grid, #listings-container {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
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

                data.categories.forEach(cat => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'category-btn';
                    btn.dataset.categorySlug = cat.slug;
                    btn.dataset.categoryName = cat.name;
                    btn.style.cssText = 'padding: 12px; border: 2px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer; color: #111; font-weight: 500; transition: all 0.2s;';
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
            })
            .catch(err => console.error('Error loading categories:', err));
    }

    function loadListings(citySlug, categorySlug) {
        // Fetch listings from the actual listings page
        const url = `/${citySlug}/${categorySlug}`;
        fetch(url)
            .then(r => r.text())
            .then(html => {
                const container = document.getElementById('listings-container');
                // Extract the listings HTML from the page
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const listingsSection = doc.querySelector('[data-listings]') || doc.querySelector('.listings-grid') || doc.querySelector('.all-listings');

                if (listingsSection) {
                    container.innerHTML = listingsSection.innerHTML;
                } else {
                    container.innerHTML = '<p style="color: #666; padding: 20px;">No listings found for this category in ' + selectedCityName + '.</p>';
                }
            })
            .catch(err => {
                console.error('Error loading listings:', err);
                document.getElementById('listings-container').innerHTML = '<p style="color: #666; padding: 20px;">No listings found for this category in ' + selectedCityName + '.</p>';
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
