@extends('themes.servicebazzar.layouts.error')

@php
    $seoRobots = 'noindex, follow';
@endphp

@section('title','Page Not Found')

@section('content')

<style>
    body {
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 20px;
    }

    .error-section{
        text-align: center;
        padding: 40px 30px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        max-width: 500px;
    }

    .error-section img{
        max-width: 100%;
        height: auto;
        margin-bottom: 20px;
    }

    .error-section h2{
        font-size: 30px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #222;
    }

    .error-section p{
        color: #777;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .error-section button{
        background: #007bff;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }

    .error-section button:hover{
        background: #0056b3;
    }

    @media (max-width: 600px) {
        body {
            padding: 15px;
        }
        .error-section {
            padding: 30px 20px;
        }
        .error-section h2 {
            font-size: 24px;
        }
        .error-section p {
            font-size: 15px;
        }
    }
</style>

<section class="error-section">
    <img src="{{ asset('assets/images/dd.jpg') }}" alt="404 Not Found">

    <h2>Sorry! Page Not Found</h2>
    <p>The page you are looking for does not exist.</p>

    <button onclick="window.location.href='{{ route('home') }}'">
        Go Back Home
    </button>
</section>

@endsection
