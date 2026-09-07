@php
    // themeRows() is cached, so these three read one shared collection
    // instead of firing three queries on every page render.
    $themeRows = themeRows($setting->theme);

    $footer = $themeRows->where('name', 'footer')->where('is_multiple', 0)->first();
    $links = $themeRows->where('name', 'usefull_links')->where('is_multiple', 1);
    $contacts = $themeRows->where('name', 'contact')->where('is_multiple', 1);
@endphp


<style>
    /* Scoped to the footer (was a bare, site-wide "h5{color:#fff}" rule
       before — harmless while the footer background was dark, but would
       have silently turned every h5 on the page white too). */
    .footer-area h5{
        color: #222;
    }
</style>

<link rel="stylesheet" href="{{ asset($themeAsset.'css/components/popular-sections.css') }}?v={{ filemtime(public_path($themeAsset.'css/components/popular-sections.css')) }}">
<link rel="stylesheet" href="{{ asset($themeAsset.'css/components/footer-accordion.css') }}?v={{ filemtime(public_path($themeAsset.'css/components/footer-accordion.css')) }}">

{{--
    Single shared source for "Popular Categories" / "Popular Cities" — included
    once here in the footer so every page gets the same markup, styling, and
    mobile accordion behavior. $popularCategories / $locations are supplied by
    the view composer in AppServiceProvider::boot() bound to this view, so no
    controller needs to pass them in individually.

    #popularCategoriesWrapper's id is kept because the home page's existing
    location-change AJAX (loadPopularCategoriesByLocation() in index.blade.php)
    replaces this div's innerHTML in place — the id must stay stable for that
    to keep working.
--}}
<div id="popularCategoriesWrapper">
    @include($themeView.'partials.popular_categories_ajax')
</div>

@include($themeView.'partials.popular_cities')

<div class="social-follow-section py-4 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex align-items-center follow-us-row">
            <h5 class="mb-0 text-dark follow-title">
                Follow us on
            </h5>

            <div class="d-flex align-items-center social-icons">
                @foreach($socialSites as $social)
                    <a href="{{ $social->link }}" target="_blank" rel="noopener noreferrer" class="social-icon">
                        <img src="{{ asset($social->image) }}" alt="social" loading="lazy">
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
<style>
    .follow-title {
        font-size: 20px;
        font-weight: 500;
        margin-right: 18px;
    }

    .social-icons {
        display: flex;
        align-items: center;
    }

    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    .social-icon:last-child {
        margin-right: 0;
    }

    .social-icon img {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }

    @media (max-width: 767px) {
        /*
         * "Follow us on" sat 5px further left than "Site Link" / "Useful
         * Links" etc. below it — this section uses a plain Bootstrap
         * .container (15px padding), while the rest of the footer's
         * content sits inside .just, which nets out to 20px on the left
         * (measured live: 20px margin-left on .just, -15px from .row,
         * +15px from the Bootstrap column's own padding). Matching that
         * 20px here is what was needed to line the two up.
         */
        .social-follow-section .container {
            padding-left: 20px;
            padding-right: 20px;
        }

        /*
         * "Follow us on" + all the social icons don't reliably fit on one
         * line at this width, which was forcing the heading text itself
         * to wrap awkwardly across two lines. Letting the icon row wrap
         * onto its own line below the heading instead looks intentional.
         */
        .follow-us-row {
            flex-wrap: wrap;
            row-gap: 10px;
        }
    }

    .footer-contact-info{
    display:flex;
   justify-content: end;
    flex-wrap:wrap;
   
}
.footer-contact-item{
    display:flex;
    align-items:normal;
    gap:8px;
    color:#222;
    font-size:15px;
}

.footer-contact-item i{

    font-size:25px;
}

.footer-contact-item a,
.footer-contact-item span{
    color:#222;
    text-decoration:none;
}

.footer-contact-item a:hover{
    color:#ff4d4d;
}
.footer-bottom a:hover {
    color: #1e90ff;
    text-decoration: none;
}

/* The site name in the copyright line sat at 1.31:1 against the text around
   it, so colour alone did not mark it as a link. An underline does, and it
   only affects this one link in the footer. */
.footer-bottom p a.sh {
    text-decoration: underline;
}
</style>


<footer class="footer-area">
    <div  class="container-fulde ssss custom-right ">
        <div class="pt-5 just pb-5">
            <div class="row mb__30 justify-content-between">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        {{-- The logo image is hidden by CSS on small screens, which leaves this link
                             in the tab order with no name at all. --}}
                        <a href="{{ route('home') }}" aria-label="{{ $setting->site_name ?? 'Service Bazzar' }} home"><img src="{{ asset('assets/images/logo/logo.png') }}" width="150" height="45" class="site-logo" alt="logo"></a>
                        <p>{{ __($footer->content->description) }}</p>
                     
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <div class="widget-title" aria-expanded="false">
                            <h5>@lang('Site Link')</h5>
                            <span class="footer-widget-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="widget-body">
                            <ul>
                                <li><a href="{{ route('home') }}">@lang('Home')</a></li>
            
                             
                                <li><a href="{{ route('blogs') }}">@lang('News')</a></li>
                                <li><a href="{{ route('contact') }}">@lang('Contact')</a></li>
                                <li><a href="{{ route('faq') }}">@lang('FAQ')</a></li>
                                <li><a href="{{ route('sitemap.page') }}">@lang('Sitemap')</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <div class="widget-title" aria-expanded="false">
                            <h5>@lang('Useful Links')</h5>
                            <span class="footer-widget-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="widget-body">
                            <ul>
                                @foreach($links as $link)
                                <li><a href="{{ route('single.slug', $link->content->slug) }}">{{ __($link->content->title) }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
    <div class="footer-widget">
        <div class="widget-title" aria-expanded="false">
            <h5>@lang('Services')</h5>
            <span class="footer-widget-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
        </div>
        <div class="widget-body">
            <ul>
                @php
                    $footerServices = activeServices()->take(6);
                @endphp

                @forelse($footerServices as $service)
                    <li>
                        <a href="{{ route('services.show', $service->slug) }}">
                            {{ $service->menu_title ?: $service->title }}
                        </a>
                    </li>
                @empty
                    <li>No Services Found</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

                
             <div class="col-lg-3 col-md-6">
    <div class="footer-widget">

        <div class="widget-title" aria-expanded="false">
            <h5>@lang('Contact Info')</h5>
            <span class="footer-widget-toggle-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
        </div>

        <div class="widget-body">

            @foreach($contacts as $contact)

                <div class="footer-contact-item mb-3">

                    @if(!empty($contact->content->icon))
                        @php echo $contact->content->icon @endphp
                    @else
                        <i class="las la-info-circle"></i>
                    @endif

                    @if(strtolower($contact->content->title) == 'email')

                        <a href="mailto:{{ $contact->content->content }}">
                            {{ $contact->content->content }}
                        </a>

                    @elseif(strtolower($contact->content->title) == 'phone')

                        <a href="tel:{{ $contact->content->content }}">
                            {{ $contact->content->content }}
                        </a>

                    @else

                        <span>
                            {{ $contact->content->content }}
                        </span>

                    @endif

                </div>

            @endforeach

        </div>

    </div>
</div>
            </div>
        </div>

        <div class="footer-contact-info">

      {{-- <div class="col-lg-4 col-md-6">
                <div class="footer-widget">
                    <div class="widget-body">
                     
                        <form action="{{ route('subscribe') }}" method="post" class="subscribe-form">
                            @csrf
                            <div class="form-group">
                                <div class="input-group">
                                  <input type="email" name="email" placeholder="@lang('Enter your mail')" class="form-control" autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text"><i class="las la-angle-right"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>



</div>

        <div class="footer-bottom">
    <p>
        Copyright ©{{ date('Y') }}
        <a class="sh" href="{{ route('home') }}">
            {{ __($setting->site_name) }}
        </a>.
        All Rights Reserved.
    </p>
</div>
    </div>
</footer>

@push('js')
    <script src="{{ asset($themeAsset.'js/components/popular-accordion.js') }}?v={{ filemtime(public_path($themeAsset.'js/components/popular-accordion.js')) }}"></script>
    <script src="{{ asset($themeAsset.'js/components/footer-accordion.js') }}?v={{ filemtime(public_path($themeAsset.'js/components/footer-accordion.js')) }}"></script>
@endpush