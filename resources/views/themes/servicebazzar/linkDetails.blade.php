@extends($themeView . 'layouts.site')

@section('title', $title)

@section('content')
@include($themeView.'includes.rich-content')
<style>
    .privacy-policy-page h1 {
        font-size: 30px;
        font-weight: 500;
        line-height: 1.3;
        overflow-wrap: anywhere;
        -webkit-tap-highlight-color: transparent;
    }

    .privacy-policy-content {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.55;
        text-align: left;
    }

    .privacy-policy-content.rich-content.rich-content,
    .privacy-policy-content.rich-content.rich-content p,
    .privacy-policy-content.rich-content.rich-content li,
    .privacy-policy-content.rich-content.rich-content span,
    .privacy-policy-content.rich-content.rich-content div,
    .privacy-policy-content.rich-content.rich-content blockquote {
        font-size: 13px;
        font-weight: 400;
        color: #333;
        line-height: 1.45;
    }

    .privacy-policy-content.rich-content.rich-content > * {
        margin-bottom: 4px;
    }

    .privacy-policy-content.rich-content.rich-content h2,
    .privacy-policy-content.rich-content.rich-content h3,
    .privacy-policy-content.rich-content.rich-content h4,
    .privacy-policy-content.rich-content.rich-content h5 {
        margin: 8px 0 2px;
        line-height: 1.25;
    }

    .privacy-policy-content * {
        max-width: 100% !important;
        box-sizing: border-box;
    }

    .privacy-policy-content p,
    .privacy-policy-content li,
    .privacy-policy-content div,
    .privacy-policy-content span {
        white-space: normal !important;
        overflow-wrap: anywhere;
        word-break: normal;
        text-align: left !important;
    }

    .privacy-policy-content p {
        margin: 0 0 4px;
    }

    .privacy-policy-content.rich-content.rich-content p {
        margin: 0 0 4px;
    }

    .privacy-policy-content p:has(strong),
    .privacy-policy-content p:has(b),
    .privacy-policy-content.rich-content.rich-content p:has(strong),
    .privacy-policy-content.rich-content.rich-content p:has(b) {
        margin: 6px 0 1px;
    }

    .privacy-policy-content ul,
    .privacy-policy-content ol,
    .privacy-policy-content.rich-content.rich-content ul,
    .privacy-policy-content.rich-content.rich-content ol {
        width: 100% !important;
        margin: 4px 0 6px;
        padding-left: 38px;
        list-style-position: outside;
    }

    .privacy-policy-content li,
    .privacy-policy-content.rich-content.rich-content li {
        display: list-item !important;
        width: auto !important;
        margin-bottom: 3px;
        padding-left: 0;
        float: none !important;
        position: static !important;
    }

    .privacy-policy-content li:last-child {
        margin-bottom: 0;
    }

    .privacy-policy-content.rich-content.rich-content .rich-content-spacer {
        display: block;
        height: 28px;
        min-height: 28px;
        margin: 0;
    }

    .privacy-policy-content strong,
    .privacy-policy-content b {
        display: inline;
        font-weight: 700;
    }

    .privacy-policy-content img,
    .privacy-policy-content iframe,
    .privacy-policy-content video {
        max-width: 100% !important;
        height: auto !important;
    }

    .privacy-policy-content table {
        display: block;
        width: 100% !important;
        max-width: 100%;
        overflow-x: auto;
        border-collapse: collapse;
    }

    @media (max-width: 576px) {
        .privacy-policy-page h1 {
            font-size: 24px;
            margin-bottom: 20px !important;
        }

        .privacy-policy-content {
            font-size: 13px;
            line-height: 1.45;
        }

        .privacy-policy-content.rich-content.rich-content,
        .privacy-policy-content.rich-content.rich-content p,
        .privacy-policy-content.rich-content.rich-content li,
        .privacy-policy-content.rich-content.rich-content span,
        .privacy-policy-content.rich-content.rich-content div,
        .privacy-policy-content.rich-content.rich-content blockquote {
            line-height: 1.42;
        }

        .privacy-policy-content.rich-content.rich-content h2,
        .privacy-policy-content.rich-content.rich-content h3,
        .privacy-policy-content.rich-content.rich-content h4,
        .privacy-policy-content.rich-content.rich-content h5 {
            margin: 7px 0 2px;
        }

        .privacy-policy-content ul,
        .privacy-policy-content ol,
        .privacy-policy-content.rich-content.rich-content ul,
        .privacy-policy-content.rich-content.rich-content ol {
            margin: 4px 0 6px;
            padding-left: 32px;
        }
    }
</style>

<section class="privacy-policy-page pb-5">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <h1 class="mb-4 text-center">
                    {{ $link->content->title }}
                </h1>

                {{-- Payment Policy, Terms, Privacy — same typography as
                     category aur blog pages. --}}
                <div class="privacy-policy-content rich-content">
                    {!! \App\Support\ContentHtml::cleanCached($link->content->content ?? '') !!}
                </div>

                {{-- Cities We Cover: only on About, giving crawlers (and readers)
                     a direct path into every city hub and reinforcing the
                     multi-city footprint instead of a Jaipur-only signal. --}}
                @if(optional($link->content)->slug === 'about-us')
                    @php
                        $coveredCities = \App\Models\Location::where('status', 1)
                            ->orderBy('name')
                            ->select(['name', 'slug'])
                            ->get();
                    @endphp
                    @if($coveredCities->isNotEmpty())
                        <div class="cities-we-cover">
                            <h2>Cities We Cover</h2>
                            <p>ServiceBazzar connects you with verified local professionals across
                               {{ $coveredCities->count() }} Indian cities:</p>
                            <ul class="cities-we-cover-list">
                                @foreach($coveredCities as $coveredCity)
                                    <li>
                                        <a href="{{ url('/' . $coveredCity->slug) }}">{{ $coveredCity->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
</section>

<style>
    .cities-we-cover {
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    .cities-we-cover h2 {
        font-size: 22px;
        margin-bottom: 8px;
    }
    .cities-we-cover-list {
        list-style: none;
        padding: 0;
        margin: 14px 0 0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 10px;
    }
    .cities-we-cover-list li a {
        display: inline-block;
        padding: 6px 14px;
        background: #f1f5f9;
        border-radius: 20px;
        font-size: 13px;
        color: #334155;
        text-decoration: none;
        transition: background .15s ease;
    }
    .cities-we-cover-list li a:hover {
        background: var(--primary-color, #2563eb);
        color: #fff;
    }
</style>
@endsection
