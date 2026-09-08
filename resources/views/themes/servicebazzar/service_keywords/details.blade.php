@extends($themeView.'layouts.site')

@php
    $seoTitle = $keyword->seo_title ?: $keyword->keyword;

    $seoDescription = $keyword->seo_description
        ?: \Illuminate\Support\Str::limit(
            trim(strip_tags($keyword->description)),
            160
        );

    $seoKeywords = $keyword->seo_keywords ?? '';

    $seoImage = !empty($keyword->image)
        ? asset('assets/images/service-keywords/'.$keyword->image)
        : asset('assets/images/logo/favicon_new.png');

    $seoUrl = url()->current();

    // Empty-tag stripping used to be a regex here. ContentHtml does that and
    // the rest of the Word cleanup, so the page matches every other content
    // page on the site.
    $cleanDescription = \App\Support\ContentHtml::cleanCached($keyword->description ?? '');

    $sidebarKeywords = $relatedKeywords->take(10);
    $sidebarCategories = $relatedCategories->take(10);

    $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags($keyword->description)) / 200));
    $currentCity = session('detected_city') ?? session('selected_location');
@endphp

@section('title', $seoTitle)

@section('content')

{{--
    Class names are prefixed `kw-` throughout: this page loads the site theme
    plus bootstrap, and generic names (card, hero, chip, tag) would inherit
    their rules.
--}}
<style>
    .kw-page{
        background:#f6f7f9;
        padding-bottom:60px;
    }

    /* ---------------------------------------------------------------- hero */
    .kw-hero{
        position:relative;
        padding:46px 0 90px;
        background:linear-gradient(120deg,#0b3d91 0%,#1c7ed6 55%,#22b8cf 100%);
        overflow:hidden;
    }
    .kw-hero::after{
        content:"";
        position:absolute;
        right:-90px;
        top:-90px;
        width:340px;
        height:340px;
        border-radius:50%;
        background:rgba(255,255,255,.07);
    }
    .kw-hero::before{
        content:"";
        position:absolute;
        left:-60px;
        bottom:-120px;
        width:260px;
        height:260px;
        border-radius:50%;
        background:rgba(255,255,255,.06);
    }
    .kw-hero-inner{
        position:relative;
        z-index:2;
    }
    .kw-crumb{
        font-size:13px;
        color:rgba(255,255,255,.75);
        margin-bottom:16px;
    }
    .kw-crumb a{
        color:#fff;
        text-decoration:none;
        opacity:.9;
    }
    .kw-crumb a:hover{ opacity:1; text-decoration:underline; }
    .kw-crumb span{ margin:0 7px; opacity:.55; }

    .kw-cat-badge{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:5px 14px;
        border-radius:30px;
        background:rgba(255,255,255,.16);
        border:1px solid rgba(255,255,255,.28);
        color:#fff;
        font-size:12.5px;
        font-weight:600;
        text-decoration:none;
        margin-bottom:14px;
    }
    .kw-cat-badge:hover{ background:rgba(255,255,255,.26); color:#fff; text-decoration:none; }

    .kw-h1{
        color:#fff;
        font-size:40px;
        font-weight:800;
        line-height:1.2;
        margin:0 0 14px;
        max-width:820px;
    }
    .kw-hero-meta{
        display:flex;
        flex-wrap:wrap;
        gap:10px 22px;
        color:rgba(255,255,255,.85);
        font-size:13.5px;
    }
    .kw-hero-meta span{
        display:inline-flex;
        align-items:center;
        gap:7px;
    }

    /* --------------------------------------------------------------- cards */
    .kw-shell{
        margin-top:-56px;
        position:relative;
        z-index:3;
    }
    .kw-card{
        background:#fff;
        border:1px solid #e9ecef;
        border-radius:16px;
        box-shadow:0 6px 24px rgba(16,24,40,.06);
        overflow:hidden;
    }
    .kw-card + .kw-card{ margin-top:24px; }
    .kw-card-body{ padding:28px; }

    .kw-section-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:10px;
        margin-bottom:20px;
    }
    .kw-section-title{
        display:flex;
        align-items:center;
        gap:10px;
        font-size:20px;
        font-weight:800;
        color:#111827;
        margin:0;
    }
    .kw-section-title::before{
        content:"";
        width:5px;
        height:22px;
        border-radius:4px;
        background:linear-gradient(#1c7ed6,#22b8cf);
    }
    .kw-section-sub{
        font-size:13px;
        color:#868e96;
        margin:4px 0 0 15px;
    }

    .kw-figure{
        margin:0 0 22px;
        border-radius:12px;
        overflow:hidden;
    }
    .kw-figure img{
        width:100%;
        max-height:400px;
        object-fit:cover;
        display:block;
    }

    .kw-content{
        color:#3f4650;
        font-size:16px;
        line-height:1.85;
    }
    .kw-content h2,
    .kw-content h3,
    .kw-content h4{
        margin:28px 0 12px;
        color:#111827;
        font-weight:700;
    }
    .kw-content h2{ font-size:23px; }
    .kw-content h3{ font-size:20px; }
    .kw-content p{ margin-bottom:15px; }
    .kw-content img{ max-width:100%; height:auto; border-radius:8px; }
    .kw-content ul, .kw-content ol{ padding-left:20px; margin-bottom:15px; }
    .kw-content li{ margin-bottom:6px; }

    /* ------------------------------------------------------- listing cards */
    /* 200px min fits three cards across the 8-column content area; 240 only
       ever managed two and left the row looking half empty. */
    .kw-listings{
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
        gap:16px;
    }
    .kw-listing{
        display:flex;
        flex-direction:column;
        border:1px solid #e9ecef;
        border-radius:14px;
        overflow:hidden;
        background:#fff;
        text-decoration:none;
        transition:.18s;
    }
    .kw-listing:hover{
        border-color:#a5d8ff;
        box-shadow:0 8px 22px rgba(28,126,214,.12);
        transform:translateY(-2px);
        text-decoration:none;
    }
    .kw-listing-thumb{
        position:relative;
        height:140px;
        background:#eef2f7;
    }
    .kw-listing-thumb img{
        width:100%;
        height:100%;
        object-fit:cover;
        display:block;
    }
    .kw-listing-flag{
        position:absolute;
        top:9px;
        left:9px;
        display:inline-flex;
        align-items:center;
        gap:5px;
        padding:3px 9px;
        border-radius:20px;
        background:rgba(17,24,39,.82);
        color:#fff;
        font-size:10.5px;
        font-weight:700;
        letter-spacing:.02em;
        text-transform:uppercase;
    }
    .kw-listing-flag.is-verified{ background:#2f9e44; }
    .kw-listing-body{ padding:13px 14px 15px; }
    .kw-listing-name{
        font-size:14.5px;
        font-weight:700;
        color:#111827;
        line-height:1.35;
        margin:0 0 6px;
    }
    .kw-listing-place{
        font-size:12.5px;
        color:#6b7280;
        display:flex;
        align-items:center;
        gap:6px;
        margin-bottom:9px;
    }
    .kw-listing-foot{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        border-top:1px solid #f1f3f5;
        padding-top:9px;
    }
    .kw-rating{
        display:inline-flex;
        align-items:center;
        gap:5px;
        font-size:12.5px;
        font-weight:700;
        color:#e8590c;
    }
    .kw-rating small{ color:#9ca3af; font-weight:500; }
    .kw-since{ font-size:11.5px; color:#9ca3af; }

    .kw-see-all{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:11px 22px;
        border-radius:30px;
        background:#1c7ed6;
        color:#fff;
        font-size:13.5px;
        font-weight:700;
        text-decoration:none;
    }
    .kw-see-all:hover{ background:#1971c2; color:#fff; text-decoration:none; }

    .kw-empty{
        padding:26px;
        border:1px dashed #dee2e6;
        border-radius:12px;
        text-align:center;
        color:#868e96;
        font-size:14px;
    }

    /* ------------------------------------------------------------- sidebar */
    .kw-side{ position:sticky; top:20px; }
    .kw-side-card{
        background:#fff;
        border:1px solid #e9ecef;
        border-radius:16px;
        padding:20px;
        margin-bottom:20px;
        box-shadow:0 4px 16px rgba(16,24,40,.04);
    }
    .kw-side-title{
        font-size:15.5px;
        font-weight:800;
        color:#111827;
        margin:0 0 14px;
        display:flex;
        align-items:center;
        gap:8px;
    }
    .kw-pills{ display:flex; flex-wrap:wrap; gap:8px; }
    .kw-pill{
        display:inline-block;
        padding:7px 13px;
        border-radius:30px;
        border:1px solid #e5e7eb;
        background:#f8f9fa;
        color:#495057;
        font-size:12.5px;
        font-weight:600;
        text-decoration:none;
        transition:.15s;
    }
    .kw-pill:hover{
        background:#1c7ed6;
        border-color:#1c7ed6;
        color:#fff;
        text-decoration:none;
    }
    .kw-cat-list{ margin:0; padding:0; list-style:none; }
    .kw-cat-list li + li{ border-top:1px solid #f1f3f5; }
    .kw-cat-list a{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        padding:11px 2px;
        color:#495057;
        font-size:13.5px;
        font-weight:600;
        text-decoration:none;
        transition:.15s;
    }
    .kw-cat-list a:hover{ color:#1c7ed6; padding-left:6px; text-decoration:none; }
    .kw-cat-list i{ color:#adb5bd; font-size:11px; }

    .kw-cta{
        background:linear-gradient(135deg,#111827,#1f2937);
        color:#fff;
        border-radius:16px;
        padding:22px;
        text-align:center;
    }
    .kw-cta h4{ font-size:16px; font-weight:800; margin:0 0 7px; color:#fff; }
    .kw-cta p{ font-size:12.5px; color:rgba(255,255,255,.7); margin:0 0 14px; }
    .kw-cta a{
        display:inline-block;
        padding:9px 20px;
        border-radius:30px;
        background:#fff;
        color:#111827;
        font-size:13px;
        font-weight:700;
        text-decoration:none;
    }
    .kw-cta a:hover{ background:#e9ecef; color:#111827; text-decoration:none; }

    .kw-empty-side{ margin:0; color:#868e96; font-size:13px; }

    @media (max-width:991px){
        .kw-side{ position:static; }
    }
    @media (max-width:767px){
        .kw-hero{ padding:32px 0 80px; }
        .kw-h1{ font-size:27px; }
        .kw-card-body{ padding:20px; }
        /* 145px keeps two cards per row on a 390px phone; 160 left only one. */
        .kw-listings{ grid-template-columns:repeat(auto-fill, minmax(145px, 1fr)); gap:12px; }
        .kw-listing-thumb{ height:110px; }
    }
</style>

{{-- ============================================================== HERO --}}
<section class="kw-hero">
    <div class="container kw-hero-inner">

        <nav class="kw-crumb" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ route('service.keywords.category', $keywordCategory->slug) }}">
                {{ $keywordCategory->name }}
            </a>
            <span>/</span>
            {{ \Illuminate\Support\Str::limit($keyword->keyword, 40) }}
        </nav>

        <a href="{{ route('service.keywords.category', $keywordCategory->slug) }}" class="kw-cat-badge">
            <i class="fas fa-layer-group"></i> {{ $keywordCategory->name }}
        </a>

        <h1 class="kw-h1">{{ $keyword->keyword }}</h1>

        <div class="kw-hero-meta">
            @if($keywordListingTotal > 0)
                <span><i class="fas fa-store"></i> {{ number_format($keywordListingTotal) }} verified business{{ $keywordListingTotal == 1 ? '' : 'es' }}</span>
            @endif

            @if($currentCity)
                <span><i class="fas fa-map-marker-alt"></i> {{ $currentCity }}</span>
            @endif

            <span><i class="far fa-clock"></i> {{ $readingMinutes }} min read</span>
        </div>

    </div>
</section>

@php
    $breadcrumbSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $keywordCategory->name, 'item' => route('service.keywords.category', $keywordCategory->slug)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $keyword->keyword, 'item' => $seoUrl],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>

{{-- ============================================================== BODY --}}
<section class="kw-page">
    <div class="container kw-shell">
        <div class="row">

            <div class="col-lg-8">

                {{-- Article --}}
                <article class="kw-card">
                    <div class="kw-card-body">

                        @if($keyword->image)
                            <figure class="kw-figure">
                                <img src="{{ asset('assets/images/service-keywords/'.$keyword->image) }}"
                                     alt="{{ $keyword->keyword }}">
                            </figure>
                        @endif

                        <div class="kw-content">
                            {!! $cleanDescription !!}
                        </div>

                    </div>
                </article>

                {{-- Businesses for this keyword's category --}}
                <section class="kw-card">
                    <div class="kw-card-body">

                        <div class="kw-section-head">
                            <div>
                                <h2 class="kw-section-title">
                                    {{ $keywordCategory->name }} near you
                                </h2>
                                <p class="kw-section-sub">
                                    @if($currentCity)
                                        Trusted businesses in {{ $currentCity }}
                                    @else
                                        Trusted businesses listed on Service Bazzar
                                    @endif
                                </p>
                            </div>

                            @if($keywordListingTotal > count($keywordListings))
                                <a href="{{ cityCategoryUrl($keywordCategory->slug) }}" class="kw-see-all">
                                    See all {{ number_format($keywordListingTotal) }}
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>

                        @if($keywordListings->isNotEmpty())

                            <div class="kw-listings">
                                @foreach($keywordListings as $item)
                                    <a class="kw-listing"
                                       href="{{ route('listings.details', [
                                            'category' => optional($item->category)->slug,
                                            'slug'     => $item->slug,
                                       ]) }}">

                                        <div class="kw-listing-thumb">
                                            {{-- loadImage() is what every other listing card uses; it
                                                 falls back to a placeholder when the file is missing. --}}
                                            <img src="{{ loadImage('assets/images/listing/thumb/'.$item->thumb) }}"
                                                 alt="{{ $item->name }}" loading="lazy">

                                            @if($item->verified)
                                                <span class="kw-listing-flag is-verified">
                                                    <i class="fas fa-check-circle"></i> Verified
                                                </span>
                                            @elseif($item->is_featured)
                                                <span class="kw-listing-flag">Featured</span>
                                            @endif
                                        </div>

                                        <div class="kw-listing-body">
                                            <h3 class="kw-listing-name">
                                                {{ \Illuminate\Support\Str::limit($item->name, 42) }}
                                            </h3>

                                            <div class="kw-listing-place">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ \Illuminate\Support\Str::limit(
                                                    trim(($item->area ? $item->area.', ' : '').(optional($item->location)->name ?? $item->city)), 34
                                                ) ?: 'India' }}
                                            </div>

                                            <div class="kw-listing-foot">
                                                @if($item->ratings_count > 0)
                                                    <span class="kw-rating">
                                                        <i class="fas fa-star"></i>
                                                        {{ number_format($item->ratings_avg_ratings, 1) }}
                                                        <small>({{ $item->ratings_count }})</small>
                                                    </span>
                                                @else
                                                    <span class="kw-since">New listing</span>
                                                @endif

                                                @if($item->established_year)
                                                    <span class="kw-since">Since {{ $item->established_year }}</span>
                                                @endif
                                            </div>
                                        </div>

                                    </a>
                                @endforeach
                            </div>

                        @else

                            <div class="kw-empty">
                                <i class="fas fa-store-slash mb-2 d-block" style="font-size:22px"></i>
                                No businesses listed under {{ $keywordCategory->name }} yet.
                            </div>

                        @endif

                    </div>
                </section>

            </div>

            {{-- ========================================================= SIDE --}}
            <div class="col-lg-4">
                <div class="kw-side">

                    <div class="kw-side-card">
                        <h2 class="kw-side-title">
                            <i class="fas fa-tags" style="color:#1c7ed6"></i> Related Searches
                        </h2>

                        @if($sidebarKeywords->isNotEmpty())
                            <div class="kw-pills">
                                @foreach($sidebarKeywords as $related)
                                    <a class="kw-pill"
                                       href="{{ route('service.keywords.details', [$keywordCategory->slug, $related->slug]) }}">
                                        {{ \Illuminate\Support\Str::limit($related->keyword, 38) }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="kw-empty-side">No related keywords found.</p>
                        @endif
                    </div>

                    <div class="kw-side-card">
                        <h2 class="kw-side-title">
                            <i class="fas fa-th-large" style="color:#1c7ed6"></i> Browse Categories
                        </h2>

                        @if($sidebarCategories->isNotEmpty())
                            <ul class="kw-cat-list">
                                @foreach($sidebarCategories as $category)
                                    <li>
                                        <a href="{{ route('service.keywords.category', $category->slug) }}">
                                            {{ \Illuminate\Support\Str::limit($category->name, 40) }}
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="kw-empty-side">No related categories found.</p>
                        @endif
                    </div>

                    <div class="kw-cta">
                        <h4>Apna business list karo</h4>
                        <p>Free me listing banao aur naye customers tak pahuncho.</p>
                        <a href="{{ route('free.listing') }}">Add Free Listing</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

@endsection
