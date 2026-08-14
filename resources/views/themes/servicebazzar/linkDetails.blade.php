@extends($themeView . 'layouts.site')

@section('title', $title)

@section('content')
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
        font-size: 16px;
        line-height: 1.7;
        text-align: left;
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

    .privacy-policy-content ul,
    .privacy-policy-content ol {
        width: 100% !important;
        margin: 15px 0;
        padding-left: 30px;
    }

    .privacy-policy-content li {
        display: list-item !important;
        width: auto !important;
        margin-bottom: 10px;
        padding-left: 4px;
        float: none !important;
        position: static !important;
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
            font-size: 15px;
            line-height: 1.65;
        }

        .privacy-policy-content ul,
        .privacy-policy-content ol {
            padding-left: 24px;
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

                <div class="privacy-policy-content">
                    {!! $link->content->content !!}
                </div>

            </div>
        </div>
    </div>
</section>
@endsection