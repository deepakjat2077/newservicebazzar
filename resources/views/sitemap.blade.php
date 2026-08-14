{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <url>
        <loc>{{ url('/') }}</loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>

    <url>
        <loc>{{ route('listings') }}</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>

    @if(Route::has('blogs'))
    <url>
        <loc>{{ route('blogs') }}</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>
    @endif

    @if(Route::has('services'))
    <url>
        <loc>{{ route('services') }}</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>
    @endif

    {{-- Categories are always city-scoped in this app (no bare category page
    exists); link straight to the primary-city landing page instead of the
    `/listings/{slug}` URL, which only 301-redirects to this same destination. --}}
    @foreach($categories as $category)
        @if(!empty($category->slug))
        <url>
            <loc>{{ url('/jaipur/' . $category->slug) }}</loc>
            @if($category->updated_at)
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            @endif
            <priority>0.8</priority>
            <changefreq>daily</changefreq>
        </url>
        @endif
    @endforeach

    @foreach($subcategories as $sub)
        @if(!empty($sub->slug))
        <url>
            <loc>{{ url('/jaipur/' . $sub->slug) }}</loc>
            @if($sub->updated_at)
            <lastmod>{{ $sub->updated_at->toAtomString() }}</lastmod>
            @endif
            <priority>0.7</priority>
            <changefreq>daily</changefreq>
        </url>
        @endif
    @endforeach

    @foreach($listings as $listing)
        @if(!empty($listing->slug) && !empty($listing->category?->slug))
        <url>
            <loc>{{ route('listings.details', ['category' => $listing->category->slug, 'slug' => $listing->slug]) }}</loc>
            @if($listing->updated_at)
            <lastmod>{{ $listing->updated_at->toAtomString() }}</lastmod>
            @endif
            <priority>0.7</priority>
            <changefreq>weekly</changefreq>
        </url>
        @endif
    @endforeach

    @foreach($services as $service)
        @if(!empty($service->slug))
        <url>
            <loc>{{ route('services.show', ['slug' => $service->slug]) }}</loc>
            @if($service->updated_at)
            <lastmod>{{ $service->updated_at->toAtomString() }}</lastmod>
            @endif
            <priority>0.6</priority>
            <changefreq>weekly</changefreq>
        </url>
        @endif
    @endforeach

    @foreach($blogs as $blog)
        @php
            $blogSlug = $blog->content->slug ?? $blog->slug ?? null;
        @endphp

        @if(!empty($blogSlug))
        <url>
            <loc>{{ route('blog.details', ['slug' => $blogSlug]) }}</loc>
            @if($blog->updated_at)
            <lastmod>{{ $blog->updated_at->toAtomString() }}</lastmod>
            @endif
            <priority>0.6</priority>
            <changefreq>weekly</changefreq>
        </url>
        @endif
    @endforeach

</urlset>
