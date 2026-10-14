@php
    $defaultTitle = $setting->site_name . (!empty(trim($__env->yieldContent('title'))) ? ' | ' . trim($__env->yieldContent('title')) : '');

    $defaultDescription = $setting->seo->meta_description ?? '';
    $defaultKeywords = !empty($setting->seo->keywords)
        ? implode(', ', $setting->seo->keywords)
        : '';
    $defaultImage = !empty($setting->seo->image)
        ? asset('assets/images/seo/' . $setting->seo->image)
        : asset('assets/images/logo/logo.png');

    $seoTitle = $seoTitle ?? $defaultTitle;
    $seoDescription = $seoDescription ?? $defaultDescription;
    $seoKeywords = $seoKeywords ?? $defaultKeywords;
    $seoImage = $seoImage ?? $defaultImage;
    $seoUrl = $seoUrl ?? url()->current();
    $seoType = $seoType ?? 'website';
    $seoRobots = $seoRobots ?? 'index, follow';
    $ogTitle = $ogTitle ?? $seoTitle;
    $ogDescription = $ogDescription ?? $seoDescription;
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoUrl }}">

@if(!Str::contains(strtolower($seoRobots), 'noindex'))
<link rel="alternate" hreflang="en-in" href="{{ $seoUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $seoUrl }}">
@endif
<meta name="google-site-verification" content="B178KugYDqjU7Y2Y6oIbKdwZa2vWRc_aJj5vmyjxmRE" />

<meta name="google-adsense-account" content="ca-pub-1828623221110116">
<script>
(function () {
    var done = false, ev = ['scroll', 'touchstart', 'pointerdown', 'keydown', 'mousemove'];
    function load() {
        if (done) return; done = true;
        ev.forEach(function (e) { removeEventListener(e, load, {passive: true}); });
        var s = document.createElement('script');
        s.async = true; s.crossOrigin = 'anonymous';
        s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1828623221110116';
        document.head.appendChild(s);
    }
    ev.forEach(function (e) { addEventListener(e, load, {passive: true}); });
})();
</script>
<link rel="shortcut icon" 
      href="{{ asset('assets/images/logo/favicon_new.png') }}" 
      type="image/x-icon">

<link rel="icon" 
      href="{{ asset('assets/images/logo/favicon_new.png') }}" 
      type="image/x-icon">

<link rel="apple-touch-icon" 
      href="{{ asset('assets/images/logo/favicon_new.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="{{ $seoTitle }}">

<meta itemprop="name" content="{{ $ogTitle }}">
<meta itemprop="description" content="{{ $ogDescription }}">
<meta itemprop="image" content="{{ $seoImage }}">

<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:site_name" content="{{ $setting->site_name }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:url" content="{{ $seoUrl }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-K89FJVC5E6"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-K89FJVC5E6');
</script>

@php
    $currentPage = Request::segment(1);

    if ($currentPage == null) {
        $currentPage = 'home';
    }

    $schemas = \App\Models\SchemaSetting::where('page', $currentPage)
                    ->where('status', 1)
                    ->get();
@endphp

@foreach($schemas as $schema)
    <script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', $schema->schema_json) !!}
    </script>
@endforeach

@php

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $setting->site_name,
        'url'      => url('/'),
        'logo'     => $defaultImage,
    ];

    
    $orgCompany = (array) config('invoice.company', []);
    if (!empty($orgCompany['address']) || !empty($orgCompany['city'])) {
        $organizationSchema['address'] = array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $orgCompany['address'] ?? null,
            'addressLocality' => $orgCompany['city'] ?? null,
            'addressRegion'   => $orgCompany['state'] ?? null,
            'postalCode'      => $orgCompany['pincode'] ?? null,
            'addressCountry'  => 'IN',
        ]);
    }
    if (!empty($orgCompany['phone'])) {
        $organizationSchema['telephone'] = preg_replace('/\s+/', '', $orgCompany['phone']);
    }
    if (!empty($orgCompany['email'])) {
        $organizationSchema['email'] = $orgCompany['email'];
    }
    if (!empty($orgCompany['phone']) || !empty($orgCompany['email'])) {
        $organizationSchema['contactPoint'] = array_filter([
            '@type'             => 'ContactPoint',
            'contactType'       => 'customer service',
            'telephone'         => !empty($orgCompany['phone']) ? preg_replace('/\s+/', '', $orgCompany['phone']) : null,
            'email'             => $orgCompany['email'] ?? null,
            'areaServed'        => 'IN',
            'availableLanguage' => ['en', 'hi'],
        ]);
    }
    $orgSameAs = collect($socialSites ?? [])->pluck('link')
        ->filter(fn ($l) => is_string($l) && preg_match('#^https?://#i', $l))->unique()->values()->all();
    if ($orgSameAs) {
        $organizationSchema['sameAs'] = $orgSameAs;
    }

    // The admin can publish a fuller WebSite block for this page; don't emit a second.
    $adminHasWebsite = $schemas->contains(fn ($s) => (json_decode((string) $s->schema_json, true)['@type'] ?? null) === 'WebSite');

    $websiteSchema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'WebSite',
        'name'          => $setting->site_name,
        'url'           => url('/'),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => route('listings.search') . '?categorysearch={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp

<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>
@unless($adminHasWebsite)
<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>
@endunless


@include('includes.schema')