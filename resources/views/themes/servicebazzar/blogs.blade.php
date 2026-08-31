@extends($themeView.'layouts.site')

@section('title', 'Blogs')

@section('content')

<section class="blog-details pt-80 pb-80">
    <div class="container">

        {{-- SEO Intro Section: long-form copy shown only on the first pagination page, to avoid repeating the same paragraphs as duplicate content across every page. The H1 itself is kept outside this guard so every paginated page still has one.

             Only the H1 + first paragraph sit above the blog cards now — the
             rest of this copy moved below the grid (see "SEO Outro Section"
             further down) so a visitor lands on the actual blog posts
             first instead of scrolling past a wall of text to reach them. --}}
        @if(request()->get('page', 1) == 1)
            <div class="row">
                <div class="col-12">
                    <div class="blog-intro">
                        <h1>
                            Service Bazzar Blog – Business Growth, Local SEO and Digital Marketing Tips
                        </h1>

                        <p>
                            Welcome to the Service Bazzar Blog, a helpful resource for small business
                            owners, local service providers, entrepreneurs, startups, and professionals
                            who want to grow their business online. Here, you can discover practical
                            business tips, local SEO strategies, digital marketing ideas, customer
                            acquisition methods, and useful guidance designed to improve your online
                            visibility and attract more customers.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="blog-intro">
                        <h1>
                            Service Bazzar Blog – Business Growth, Local SEO and Digital Marketing Tips
                        </h1>
                    </div>
                </div>
            </div>
        @endif

        {{-- Dynamic Blogs --}}
        <div class="row mb__30">

            @forelse($blogs as $blog)
                <div class="col-lg-4 col-md-6 mb-4">
                    <article class="blog-area">

                        <div class="thumb">
                            <a href="{{ route('blog.details', $blog->content->slug) }}">
                                <img
                                    src="{{ loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) }}"
                                    loading="lazy"
                                    alt="{{ __($blog->content->title) }} Blog"
                                >
                            </a>
                        </div>

                        <div class="content-area">

                            <h2 class="blog-title">
                                <a href="{{ route('blog.details', $blog->content->slug) }}">
                                    {{ __($blog->content->title) }}
                                </a>
                            </h2>

                            {{-- Card par author + date: detail page ke byline ka
                                 chhota version, taaki listing par bhi freshness
                                 aur authorship dikhe. --}}
                            @if($blog->created_at || !empty($blog->content->author_name))
                                <p class="blog-card-meta">
                                    @if(!empty($blog->content->author_name))
                                        <span class="blog-card-author">{{ $blog->content->author_name }}</span>
                                        @if($blog->created_at)
                                            <span class="blog-card-sep">|</span>
                                        @endif
                                    @endif

                                    @if($blog->created_at)
                                        <time datetime="{{ $blog->created_at->toDateString() }}">
                                            {{ $blog->created_at->format('d M Y') }}
                                        </time>
                                    @endif
                                </p>
                            @endif

                            <p class="blog-description">
                                {{ __($blog->content->sub_title) }}
                            </p>

                            <a
                                href="{{ route('blog.details', $blog->content->slug) }}"
                                class="btn btn__base"
                                aria-label="Read {{ __($blog->content->title) }}"
                            >
                                @lang('Read')
                            </a>

                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="no-blogs">
                        <h2>No blogs found</h2>
                        <p>New articles will be published soon.</p>
                    </div>
                </div>
            @endforelse

            {{-- Pagination --}}
            @if($blogs->hasPages())
                <div class="col-12 mt-3">
                    <div class="blog-pagination">
                        {{ $blogs->links() }}
                    </div>
                </div>
            @endif

        </div>

        {{-- SEO Outro Section: the rest of the intro copy that used to sit
             above the blog cards — moved here (still page-1-only, same
             reasoning as the guard above) so the actual posts are the
             first thing on the page. --}}
        @if(request()->get('page', 1) == 1)
            <div class="row">
                <div class="col-12">
                    <div class="blog-intro">
                        <p>
                            Building a successful business requires more than offering quality products
                            or services. Customers must also be able to find your business when they
                            search online. Our articles explain how business listings, search engine
                            optimization, online directories, Google Business Profile optimization,
                            content marketing, and local search strategies can help your company become
                            more visible to potential customers.
                        </p>

                        <p>
                            The Service Bazzar Blog covers important topics such as local business
                            promotion, online reputation management, lead generation, website
                            optimization, branding, customer engagement, social media marketing, and
                            business growth. Our goal is to make complicated digital marketing concepts
                            simple and easy to understand, so business owners can apply them without
                            needing advanced technical knowledge.
                        </p>

                        <p>
                            Whether you are starting a new business or managing an established company,
                            our content can help you make better decisions. You can learn how to create
                            a strong online presence, improve your local search ranking, reach the right
                            audience, generate qualified leads, and convert visitors into customers.
                            We also share useful ideas for improving customer trust, increasing brand
                            awareness, and building long-term relationships with your audience.
                        </p>

                        <p>
                            Local business listings are especially valuable for companies that serve
                            customers in specific cities or areas. A complete and accurate business
                            profile can help customers find important details such as your services,
                            contact information, business location, operating hours, and website.
                            Our guides explain how to create effective listings, maintain consistent
                            business information, and improve the chances of appearing in relevant
                            local searches.
                        </p>

                        <p>
                            We regularly publish informative articles based on common business
                            challenges and current online marketing practices. Each article focuses on
                            practical information that can be used to improve visibility, credibility,
                            customer reach, and overall business performance. Instead of relying on
                            complicated theories, we aim to provide clear steps and actionable
                            recommendations that business owners can implement.
                        </p>

                        <p>
                            Explore the latest Service Bazzar articles above to learn more about business
                            listings, local SEO, digital marketing, online promotion, customer growth,
                            and modern business strategies. New articles are added regularly, so you can
                            continue discovering useful information that supports your business goals
                            and helps your company grow in an increasingly competitive digital market.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>


<style>
h1 {
    font-size: 16px;
}
h2 {
    font-size: 16px;
}

    a {
    color: #000;
    text-decoration: none;
    background-color: transparent;
}

    .blog-card-meta{
    font-size: 12px;
    color: #777;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}

.blog-card-meta .blog-card-author{
    color: #333;
    font-weight: 600;
}

.blog-card-meta .blog-card-sep{
    color: #c4c4c4;
}

    .blog-area .thumb {
    width: 100%;
    height: 220px; /* fix height */
    overflow: hidden;
    border-radius: 8px;
}.blog-area .thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* IMPORTANT */
}
</style>
@endsection