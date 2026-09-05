@extends($themeView.'layouts.site')

@section('title', 'Sitemap | ServiceBazzar')

@php
    $seoDescription = 'Browse all services, cities, and categories on ServiceBazzar. Find local businesses near you across India.';
@endphp

@section('content')

<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 10px; color: #111;">Sitemap</h1>
            <p style="color: #666; font-size: 16px;">Browse all services, locations, and businesses on ServiceBazzar</p>
        </div>
    </div>

    <div class="row">
        <!-- Main Navigation -->
        <div class="col-md-3 mb-4">
            <h5 style="font-weight: 600; margin-bottom: 15px; color: #111;">Main</h5>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;"><a href="{{ route('home') }}" style="color: #0d6efd; text-decoration: none;">Home</a></li>
                <li style="margin-bottom: 10px;"><a href="{{ route('blogs') }}" style="color: #0d6efd; text-decoration: none;">Blog</a></li>
                <li style="margin-bottom: 10px;"><a href="{{ route('faq') }}" style="color: #0d6efd; text-decoration: none;">FAQ</a></li>
                <li style="margin-bottom: 10px;"><a href="{{ route('contact') }}" style="color: #0d6efd; text-decoration: none;">Contact Us</a></li>
                <li style="margin-bottom: 10px;"><a href="/sitemap.xml" style="color: #0d6efd; text-decoration: none;">XML Sitemap</a></li>
            </ul>
        </div>

        <!-- Popular Cities -->
        <div class="col-md-3 mb-4">
            <h5 style="font-weight: 600; margin-bottom: 15px; color: #111;">Popular Cities</h5>
            <ul style="list-style: none; padding: 0;">
                @foreach($popularLocations as $location)
                    <li style="margin-bottom: 10px;">
                        <a href="{{ url($location->slug) }}" style="color: #0d6efd; text-decoration: none;">{{ $location->name }}</a>
                    </li>
                @endforeach
                <li style="margin-bottom: 10px;">
                    <a href="#all-cities" style="color: #0d6efd; text-decoration: none;"><strong>View All Cities →</strong></a>
                </li>
            </ul>
        </div>

        <!-- Popular Services -->
        <div class="col-md-3 mb-4">
            <h5 style="font-weight: 600; margin-bottom: 15px; color: #111;">Popular Services</h5>
            <ul style="list-style: none; padding: 0;">
                @foreach($popularCategories as $category)
                    <li style="margin-bottom: 10px;">
                        <a href="{{ cityCategoryUrl($category->slug) }}" style="color: #0d6efd; text-decoration: none;">{{ $category->name }}</a>
                    </li>
                @endforeach
                <li style="margin-bottom: 10px;">
                    <a href="#all-services" style="color: #0d6efd; text-decoration: none;"><strong>View All Services →</strong></a>
                </li>
            </ul>
        </div>

        <!-- Support & Info -->
        <div class="col-md-3 mb-4">
            <h5 style="font-weight: 600; margin-bottom: 15px; color: #111;">Information</h5>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;"><a href="{{ route('free-listing') }}" style="color: #0d6efd; text-decoration: none;">Post Free Listing</a></li>
                <li style="margin-bottom: 10px;"><a href="{{ route('contact') }}" style="color: #0d6efd; text-decoration: none;">Support</a></li>
                <li style="margin-bottom: 10px;"><a href="{{ route('blogs') }}" style="color: #0d6efd; text-decoration: none;">Tips & Guides</a></li>
                <li style="margin-bottom: 10px;"><a href="{{ route('home') }}#faq" style="color: #0d6efd; text-decoration: none;">Help Center</a></li>
            </ul>
        </div>
    </div>

    <!-- All Cities Section -->
    <div class="row mt-5 pt-4" style="border-top: 1px solid #eee;">
        <div class="col-12">
            <h3 id="all-cities" style="font-weight: 600; margin-bottom: 20px; color: #111;">All Cities</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                @foreach($locations->sortBy('name') as $location)
                    <a href="{{ url($location->slug) }}" style="color: #0d6efd; text-decoration: none; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; text-align: center; transition: all 0.2s;">
                        {{ $location->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- All Services Section -->
    <div class="row mt-5 pt-4" style="border-top: 1px solid #eee;">
        <div class="col-12">
            <h3 id="all-services" style="font-weight: 600; margin-bottom: 20px; color: #111;">All Services</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                @foreach($categories as $category)
                    <a href="{{ cityCategoryUrl($category->slug) }}" style="color: #0d6efd; text-decoration: none; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; text-align: center; transition: all 0.2s;">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- SEO Info -->
    <div class="row mt-5 pt-4" style="border-top: 1px solid #eee;">
        <div class="col-12">
            <p style="color: #666; font-size: 14px; text-align: center;">
                <strong>ServiceBazzar</strong> connects you with trusted local businesses and service providers across India.
                Browse our complete directory above or <a href="{{ route('free-listing') }}" style="color: #0d6efd;">post your business free</a>.
            </p>
        </div>
    </div>
</div>

<style>
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

        div[style*="grid-template-columns"] {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
        }
    }
</style>

@endsection
