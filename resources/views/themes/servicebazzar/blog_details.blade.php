@extends($themeView.'layouts.site')

@php
    $seoTitle = $blog->content->meta_title ?? $blog->content->title ?? 'Blog Details';
    $seoDescription = $blog->content->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($blog->content->content ?? ''), 160);
    $seoKeywords = $blog->content->meta_keywords ?? '';
    $seoImage = !empty($blog->content->image) ? loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) : asset('assets/images/logo/favicon_new.png');
    $seoUrl = url()->current();
    $seoType = 'article';

    /*
     * Byline + Article schema ka data.
     *
     * Published date `created_at` se aata hai — wo blog row banne par ek
     * baar set hota hai aur view counter use nahi chhuta.
     *
     * Modified date `content_updated_at` se aata hai (Template::elementSave()
     * me admin ke save par likha jata hai). `updated_at` par fallback isliye
     * hai ki jo blogs is field ke aane se pehle ke hain unme ye key nahi hai;
     * un par purana updated_at hi best available signal hai.
     */
    $blogPublishedAt = $blog->created_at;

    $blogModifiedAt = !empty($blog->content->content_updated_at)
        ? \Illuminate\Support\Carbon::parse($blog->content->content_updated_at)
        : $blog->updated_at;

    // Modified date kabhi published se pehle na dikhe.
    if ($blogPublishedAt && $blogModifiedAt && $blogModifiedAt->lt($blogPublishedAt)) {
        $blogModifiedAt = $blogPublishedAt;
    }

    $blogAuthorName = trim($blog->content->author_name ?? '');
    $blogAuthorRole = trim($blog->content->author_designation ?? '');

    // Author naam na ho to schema me publisher (site) hi author banta hai —
    // Google ke liye ye khaali/fake Person naam se behtar hai.
    $blogAuthorSchema = $blogAuthorName !== ''
        ? array_filter([
            '@type'    => 'Person',
            'name'     => $blogAuthorName,
            'jobTitle' => $blogAuthorRole !== '' ? $blogAuthorRole : null,
        ])
        : [
            '@type' => 'Organization',
            'name'  => $setting->site_name,
            'url'   => url('/'),
        ];

    $blogPublisherLogo = !empty($setting->seo->image)
        ? asset('assets/images/seo/' . $setting->seo->image)
        : asset('assets/images/logo/logo.png');

    $blogArticleSchema = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $seoUrl],
        // Google headline ko 110 characters par cut karta hai.
        'headline'         => \Illuminate\Support\Str::limit($blog->content->title ?? '', 110, ''),
        'description'      => $seoDescription,
        'image'            => $seoImage ? [$seoImage] : null,
        'datePublished'    => $blogPublishedAt ? $blogPublishedAt->toIso8601String() : null,
        'dateModified'     => $blogModifiedAt ? $blogModifiedAt->toIso8601String() : null,
        'author'           => $blogAuthorSchema,
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => $setting->site_name,
            'logo'  => ['@type' => 'ImageObject', 'url' => $blogPublisherLogo],
        ],
    ]);
@endphp

@section('content')
@include($themeView.'includes.rich-content')

{{-- BlogPosting structured data. Page par $seoType pehle se 'article' tha,
     par koi Article JSON-LD emit hi nahi hota tha — yaani Google ko author,
     publish date ya update date ka koi structured signal nahi milta tha.
     str_ireplace wahi guard hai jo includes/seo.blade.php use karta hai,
     taaki content ke andar ka "</script" tag block ko todh na sake. --}}
<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($blogArticleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>

<style>
    h1 {
    font-size: 1.5rem;
}

/* Author byline — heading ke neeche, content se pehle. */
.blog-byline{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    margin:10px 0 18px;
    padding-bottom:14px;
    border-bottom:1px solid #ececec;
    font-size:14px;
    color:#555;
    line-height:1.5;
}

.blog-byline .byline-author{
    color:#111;
    font-weight:600;
}

.blog-byline .byline-role{
    color:#666;
    font-weight:400;
}

.blog-byline .byline-sep{
    color:#c4c4c4;
}

.blog-byline time{
    color:#555;
}

.blog-byline .byline-updated{
    color:#0d6efd;
    font-weight:500;
}

@media (max-width:767px){
    .blog-byline{
        font-size:13px;
        gap:6px;
    }
}
</style>
<section class="blog-details pt-80 pb-80">
    <div class="container">
        <div class="row mb__30">
            <div class="col-lg-8">
                <div class="blog-details-area">
                    <div class="thumb">
                        <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) }}" alt="{{ $blog->content->title }} Blog" fetchpriority="high">
                    </div>
                    <div class="details">
                       
                        <h1>{{ __($blog->content->title) }}</h1>

                        {{-- Author byline + dates. Ye wahi values dikhata hai
                             jo upar BlogPosting schema me jaati hain, taaki
                             visible content aur structured data match karein. --}}
                        <div class="blog-byline">
                            @if($blogAuthorName !== '')
                                <span>
                                    Written by
                                    <span class="byline-author">{{ $blogAuthorName }}</span>@if($blogAuthorRole !== ''), <span class="byline-role">{{ $blogAuthorRole }}</span>@endif
                                </span>
                            @else
                                <span>
                                    Published by
                                    <span class="byline-author">{{ $setting->site_name }}</span>
                                </span>
                            @endif

                            @if($blogPublishedAt)
                                <span class="byline-sep">|</span>
                                <time datetime="{{ $blogPublishedAt->toDateString() }}">
                                    {{ $blogPublishedAt->format('d M Y') }}
                                </time>
                            @endif

                            {{-- "Updated" tabhi dikhta hai jab wo publish date se
                                 alag din ka ho — warna har naye blog par
                                 "Published X | Updated X" do baar same date
                                 dikhata, jo freshness signal ko kamzor karta hai. --}}
                            @if($blogPublishedAt && $blogModifiedAt && !$blogModifiedAt->isSameDay($blogPublishedAt))
                                <span class="byline-sep">|</span>
                                <time class="byline-updated" datetime="{{ $blogModifiedAt->toDateString() }}">
                                    Updated {{ $blogModifiedAt->format('d M Y') }}
                                </time>
                            @endif
                        </div>

                        {{--
                            Blog content bhi Word se paste hota hai, isliye
                            category pages jaisa hi normalise hota hai — warna
                            har blog apna font-size aur apna spacing leke aata
                            hai. Tables ko .rich-content-table wrapper milta
                            hai, jisse mobile par aakhri column cut hone ke
                            bajaye scroll karke dikhta hai.
                        --}}
                        <div class="rich-content">
                            {!! \App\Support\ContentHtml::cleanCached($blog->content->content ?? '') !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="blog-widget">
                    <h6 class="widget-title">@lang('Recent Blog')</h6>
                    <div class="widget-body">
                        @foreach($recent_blogs as $recent)
                        <div class="single-blog-area">
                            <div class="blog-thumb-area">
                                <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$recent->content->image) }}" alt="{{ $recent->content->title }} Blog" loading="lazy">
                            </div>
                            <div class="blog-title-area">
                               <a href="{{ route('blog.details', $recent->content->slug) }}">
    {{ __(Str::limit($recent->content->title,40)) }}
</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="blog-widget">
                    <h6 class="widget-title">@lang('Popular Blog')</h6>
                    <div class="widget-body">
                        @foreach($popular_blogs as $popular)
                        <div class="single-blog-area">
                            <div class="blog-thumb-area">
                                <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$popular->content->image) }}"      loading="lazy" alt="{{ $popular->content->title }} Blog">
                            </div>
                            <div class="blog-title-area">
                                <a href="{{ route('blog.details', $popular->content->slug) }}">{{ __(Str::limit($popular->content->title,40)) }}</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Related content section: contextual internal links to relevant categories,
     subcategories, or service pages that complement this blog post topic. --}}
<section class="related-content-section mt-5 pt-4 border-top">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="related-title mb-4">
                    Related Services & Categories
                </h3>

                <div class="related-links">
                    {{-- Blog author can optionally link to relevant service categories.
                         For now, this shows a generic placeholder encouraging
                         readers to explore services mentioned in the blog. --}}
                    <p class="text-muted">
                        Looking for professional services? Explore our directory to find verified
                        {{ $setting->site_name ?? 'service providers' }} in your area.
                    </p>
                    <a href="{{ route('listings.search') }}" class="btn btn-sm btn-outline-primary">
                        Browse Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.related-content-section {
    background: #f9f9f9;
    padding: 40px 0;
    margin-top: 50px;
}

.related-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
}

.related-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.related-links a {
    display: inline-block;
    width: fit-content;
}
</style>

@endsection