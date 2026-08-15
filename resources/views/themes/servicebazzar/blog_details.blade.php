@extends($themeView.'layouts.site')

@php
    $seoTitle = $blog->content->meta_title ?? $blog->content->title ?? 'Blog Details';
    $seoDescription = $blog->content->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($blog->content->content ?? ''), 160);
    $seoKeywords = $blog->content->meta_keywords ?? '';
    $seoImage = !empty($blog->content->image) ? loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) : asset('assets/images/logo/favicon_new.png');
    $seoUrl = url()->current();
    $seoType = 'article';
@endphp

@section('content')
@include($themeView.'includes.rich-content')
<style>
    h1 {
    font-size: 1.5rem;
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
@endsection