@extends($themeView.'layouts.site')
@php
    $seoTitle = 'Home Services Directory | ServiceBazzar';
    $seoDescription = 'Browse home and professional services on ServiceBazzar. Find verified service providers in your area.';
@endphp
@section('title', $seoTitle)

@section('content')
<section class="jaipur-partner-section">
    <div class="container">
        <h1 class="sr-only">Home Services Directory</h1>
        <div class="top-line">Your Trusted Local Business Discovery Platform</div>

        <h2 class="main-heading">
             Built for local businesses <br>
            that want real visibility
        </h2>

        <div class="partner-cards">
            <div class="partner-card">
                <div class="card-image">
                    <img src="{{asset('assets/images/service1.png')}}" alt="Smart Business Listings Near You" width="100" height="100" loading="lazy">
                </div>
               <h3>Smart Business<br>Listings</h3>
                <p>
                   ServiceBazzar helps local businesses showcase their services, contact
                    details, locations, and key highlights so customers can discover
                    them quickly and easily.
                </p>
                <div class="card-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>

            <div class="partner-card">
                <div class="card-image">
                      <img src="{{asset('assets/images/service2.webp')}}" loading="lazy" alt="Easy Business Search Near You">
                  
                </div>
                <h3>Easy Search<br>& Discovery</h3>
                <p>
                     Users can search businesses by category, location, and services,
                    making it simple to find the right shops, professionals, and local
                    service providers in one place.
                </p>
                <div class="card-icon">
                    <i class="fas fa-link"></i>
                </div>
            </div>

            <div class="partner-card">
                <div class="card-image">
                    <img src="{{asset('assets/images/service3.webp')}}" loading="lazy" alt="Grow Your Local Presence with ServiceBazzar">
                </div>
                <h3>Grow Your<br>Local Presence</h3>
                <p>
                     From better online reach to more customer inquiries, ServiceBazzar gives
                    businesses a strong digital presence and helps people connect with
                    trusted local services faster.
                </p>
                <div class="card-icon">
                    <i class="fas fa-desktop"></i>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="blog-details pt-80 pb-80">
    <div class="container">
        <div class="row mb__30">
            @foreach($services as $service)
                <div class="col-md-4 mb-4">
                    <div class="blog-area h-100">
                        <div class="thumb">
                            <img 
                                src="{{ $service->hero_image ? asset($service->hero_image) : asset('assets/frontend/images/default.jpg') }}" 
                             loading="lazy"  alt="{{ $service->title }} Services Near Me"
                            >
                        </div>

                        <div class="content-area">
                            <h5>
                                <a href="{{ route('services.show', $service->slug) }}">
                                    {{ __($service->title) }}
                                </a>
                            </h5>

                            <p>
                                {{ \Illuminate\Support\Str::limit(strip_tags($service->short_description), 100) }}
                            </p>

                            <a href="{{ route('services.show', $service->slug) }}" class="btn btn__base">
                                @lang('Read')
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            
        </div>
    </div>
</section>
<style>
    .blog-area{
    display: flex;
    flex-direction: column;
    height: 100%;
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}

/* IMAGE FIX SIZE */
.blog-area .thumb{
    width: 100%;
    height: 200px; /* same height sabke liye */
    overflow: hidden;
}

.blog-area .thumb img{
    width: 100%;
    height: 100%;
    object-fit: cover; /* crop karega but size same rakhega */
}

/* CONTENT AREA SAME HEIGHT */
.blog-area .content-area{
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 15px;
}

/* TITLE FIX HEIGHT */
.blog-area h5{
    font-size: 18px;
    min-height: 48px;
}

/* DESCRIPTION FIX HEIGHT */
.blog-area p{
    flex: 1;
    font-size: 14px;
}

/* BUTTON ALWAYS BOTTOM */
.blog-area .btn{
    margin-top: auto;
}

.jaipur-partner-section {
    padding: 60px 15px 70px;
    background: #fff;
    font-family: Arial, sans-serif;
}

.jaipur-partner-section .container {
    max-width: 1180px;
    margin: 0 auto;
}

.jaipur-partner-section .top-line {
    text-align: center;
    font-size: 14px;
    color: #333;
    margin-bottom: 10px;
    font-weight: 500;
}

.jaipur-partner-section .main-heading {
    text-align: center;
    font-size: 46px;
    line-height: 1.15;
    font-weight: 800;
    color: #000;
    margin: 0 0 45px;
}

.partner-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}


.partner-card {
    background: #f9f9f9;
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    text-align: center;
    padding: 0px 0px 40px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: 0.3s ease;
    min-height: 100%;
}

.partner-card:hover {
    transform: translateY(-4px);
}

.partner-card .card-image {
    margin-bottom: 16px;
    min-height: 135px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.partner-card .card-image img {
    max-width: 100%;
    object-fit: cover;
}

.partner-card h3 {
    font-size: 20px;
    line-height: 1.3;
    font-weight: 700;
    color: #000;
    margin-bottom: 14px;
}

.partner-card p {
    font-size: 14px;
    line-height: 1.7;
    color: #666;
    margin: 0;
}

.partner-card .card-icon {
    width: 54px;
    height: 54px;
    border: 2px solid #ff6b6b;
    border-radius: 50%;
    background: #fff;
    color: #ff6b6b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    position: absolute;
    bottom: -27px;
    left: 50%;
    transform: translateX(-50%);
}

@media (max-width: 991px) {
    .jaipur-partner-section .main-heading {
        font-size: 36px;
    }

    .partner-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .jaipur-partner-section {
        padding: 45px 12px 60px;
    }

    .jaipur-partner-section .top-line {
        font-size: 13px;
    }

    .jaipur-partner-section .main-heading {
        font-size: 28px;
        line-height: 1.25;
        margin-bottom: 30px;
    }

    .partner-cards {
        grid-template-columns: 1fr;
        gap: 35px;
    }

    .partner-card {
        padding: 20px 15px 40px;
    }

    .partner-card .card-image {
        min-height: auto;
    }

    .partner-card .card-image img {
        max-height: 115px;
    }

    .partner-card h3 {
        font-size: 18px;
    }

    .partner-card p {
        font-size: 13px;
        line-height: 1.6;
    }

    .partner-card .card-icon {
        width: 50px;
        height: 50px;
        font-size: 18px;
        bottom: -25px;
    }
}</style>
@endsection