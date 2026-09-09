@extends($themeView.'layouts.site')

@php
    // includes/seo.blade.php (rendered in <head> by the layout) defaults
    // $seoRobots to 'index, follow' when nothing sets it — which is exactly
    // what a 404 page must never send. The <meta> tag that used to sit here,
    // inside @section('content'), rendered in the <body>, not the <head>;
    // Google's own docs say a robots meta tag outside <head> has undefined
    // behaviour, and in practice this page was being indexed anyway (audit
    // #40). Setting it here, before @section('content'), keeps it in scope
    // when the layout includes includes/seo.blade.php.
    $seoRobots = 'noindex, follow';
@endphp

@section('title','Page Not Found')
@section('hide_inner_banner', '1')

@section('content')

<style>
    .error-section{
        text-align:center;
        padding:20px;
    }
    .error-section img{
        max-width:100%;
        margin-bottom:20px;
    }
    .error-section h2{
        font-size:30px;
        font-weight:600;
        margin-bottom:10px;
    }
    .error-section p{
        color:#777;
        margin-bottom:20px;
    }
    .error-section button{
        background:#007bff;
        color:#fff;
        padding:10px 20px;
        border-radius:5px;
        text-decoration:none;
    }
</style>

<section class="error-section">
    <img src="{{ asset('assets/images/dd.jpg') }}" alt="404">

    <h2>Sorry! Page Not Found</h2>
    <p>The page you are looking for does not exist.</p>

    <button onclick="window.location.href='{{ route('home') }}'">
    Go Back Home
</button>
</section>

@endsection