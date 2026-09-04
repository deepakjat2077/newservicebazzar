@extends($themeView.'layouts.site')

@section('title', $service->meta_title ?: $service->title)
@php
    $seoTitle = $service->meta_title ?? $service->title ?? 'Service Details';

    $seoDescription = $service->meta_description 
        ?? \Illuminate\Support\Str::limit(strip_tags($service->description ?? ''), 160);

    $seoKeywords = $service->meta_keywords ?? '';

    $seoImage = !empty($service->hero_image) 
        ? asset($service->hero_image) 
        : asset('assets/images/logo/favicon_new.png');

    $seoUrl = url()->current();

    $seoType = 'service'; // ya 'article' bhi rakh sakta hai
@endphp
@section('content')
@php
   
    $cards = is_array($service->cards) ? $service->cards : [];
    $features = is_array($service->features) ? $service->features : [];
    $processSteps = is_array($service->process_steps) ? $service->process_steps : [];
    $faqs = is_array($service->faqs) ? $service->faqs : [];

    $partnerPoints = [];
    if (!empty($service->partner_points)) {
        $partnerPoints = preg_split('/\r\n|\r|\n/', $service->partner_points);
        $partnerPoints = array_filter(array_map('trim', $partnerPoints));
    }
@endphp

<style>
   .service-hero {
    position: relative;
    padding-top: 100px;
    padding-bottom: 100px;
    justify-content: center;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: #fff;
    display: flex;
    height: 400px;
    text-align: center;
}
    .service-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.55);
    }
    .service-hero .container {
        position: relative;
        z-index: 2;
    }
    .service-hero h1 {
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 0;
    }
    .service-section {
        padding: 10px 0;
    }
    .service-img {
        width: 100%;
        border-radius: 12px;
        object-fit: cover;
    }
    .service-sidebar-box {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .service-sidebar-box h4 {
        font-size: 22px;
        margin-bottom: 20px;
    }
    .service-menu-list {
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .service-menu-list li {
        border-bottom: 1px solid #eee;
    }
    .service-menu-list li:last-child {
        border-bottom: none;
    }
    .service-menu-list li a,
    .service-menu-list li span {
        display: block;
        padding: 12px 0;
        color: #222;
        font-weight: 500;
        text-decoration: none;
    }
    .service-menu-list li a:hover {
        color: #0d6efd;
    }
    .service-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        height: 100%;
    }
    .service-card img {
        width: 100%;
        height: 220px;
        object-fit: cover;
    }
    .service-card-body {
        padding: 22px;
    }
    .service-card-body h4 {
        font-size: 22px;
        margin-bottom: 12px;
    }
    .service-feature-box,
    .service-process-box,
    .service-faq-box {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        height: 100%;
    }
    .service-process-number {
        font-size: 28px;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 10px;
    }
    .service-cta {
        background: #131313;
        padding: 45px;
    }
    .service-cta img {
        width: 100%;
        border-radius: 12px;
        object-fit: cover;
    }
    .service-btn {
        display: inline-block;
        padding: 12px 28px;
        background: #0e0f38;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
    }
    .service-btn:hover {
        color: #fff;
        opacity: .9;
    }
    .section-title {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 18px;
        color: white;
    }
    .section-text {
        font-size: 16px;
        line-height: 1.8;
        color:white;
    }
    .service-list-points {
        padding-left: 18px;
        margin-bottom: 0;
    }
    .service-list-points li {
        margin-bottom: 10px;
        color: #555;
    }
    @media (max-width: 767px) {
        .service-hero {
            padding: 65px 0;
        }
        .service-hero h1 {
            font-size: 28px;
        }
        .service-section {
            padding: 45px 0;
        }
        .section-title {
            font-size: 26px;
        }
        .service-cta {
            padding: 25px;
        }
    }
</style>

{{-- HERO --}}
<section class="service-hero"
    style="background-image: url('{{ !empty($service->hero_image) ? asset($service->hero_image) : asset('assets/global/img/default.jpg') }}');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $service->hero_title ?: $service->title }}</h1>
            </div>
        </div>
    </div>
</section>

{{-- INTRO + SIDEBAR --}}
<section class="service-section">
    <div class="container">
        <div class="row g-4">
          <div class="col-lg-4">
    @if($menuItems->count())
        <div class="service-sidebar-box">
            <h4>{{ $service->menu_title ?: 'Our Services' }}</h4>
            <ul class="service-menu-list">
                @foreach($menuItems as $item)
                    <li class="{{ $service->slug == $item->slug ? 'active' : '' }}">
                        <a href="{{ url('services/' . $item->slug) }}">
                            {{ $item->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

            <div class="col-lg-8">
                @if(!empty($service->intro_image))
                    <div class="mb-4">
                        <img src="{{ asset($service->intro_image) }}" alt="{{ $service->title }} Services Near Me" width="800" height="450" loading="lazy" class="service-img">
                    </div>
                @endif

                @if(!empty($service->intro_content))
                    <div class="section-text text-dark">
                        {!! nl2br(e($service->intro_content)) !!}
                    </div>
                @elseif(!empty($service->description))
                    <div class="section-text text-dark">
                        {!! nl2br(e($service->description)) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- TRUSTED SECTION --}}
@if(!empty($service->trusted_title) || !empty($service->trusted_description) || !empty($service->trusted_image))
<section class="service-section trusted-section pt-0">
    <div class="container">
        <div class="trusted-wrapper">
            <div class="row align-items-center g-4">

                {{-- LEFT CONTENT --}}
                <div class="col-lg-6">
                    <div class="trusted-content">

                        @if(!empty($service->trusted_title))
                            <h2 class="section-title text-dark mb-3">
                                {{ $service->trusted_title }}
                            </h2>
                        @endif

                        @if(!empty($service->trusted_description))
                            <div class="section-text text-dark">
                                {!! nl2br(e($service->trusted_description)) !!}
                            </div>
                        @endif

                    </div>
                </div>

                {{-- RIGHT IMAGE --}}
                <div class="col-lg-6">
                    @if(!empty($service->trusted_image))
                        <div class="trusted-image-wrap">
                            <img src="{{ asset($service->trusted_image) }}"
                                alt="{{ $service->trusted_title }} Near Me"
                                loading="lazy"  class="trusted-image">
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</section>
@endif
<style>
    /* ===== TRUSTED SECTION ===== */
.trusted-section .trusted-wrapper {
    background: #ffffff;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
}

/* Content */
.trusted-section .trusted-content {
    padding-right: 10px;
}

.trusted-section .section-title {
    font-size: 28px;
    font-weight: 700;
}

.trusted-section .section-text {
    font-size: 15px;
    line-height: 1.8;
}

/* Image Wrapper */
.trusted-section .trusted-image-wrap {
    border-radius: 20px;
    overflow: hidden;
    background: #f5f5f5;
    max-height: 320px; /* 🔥 main control */
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

/* Image */
.trusted-section .trusted-image {
    width: 100%;
    height: 320px; /* 🔥 fixed height */
    object-fit: cover;
    display: block;
    transition: 0.3s ease;
}

/* Hover Effect */
.trusted-section .trusted-image:hover {
    transform: scale(1.03);
}

/* ===== TABLET ===== */
@media (max-width: 991.98px) {
    .trusted-section .trusted-wrapper {
        padding: 25px;
    }

    .trusted-section .trusted-content {
        padding-right: 0;
    }

    .trusted-section .trusted-image {
        height: 260px;
    }

    .trusted-section .trusted-image-wrap {
        max-height: 260px;
    }
}

/* ===== MOBILE ===== */
@media (max-width: 575.98px) {
    .trusted-section .trusted-wrapper {
        padding: 18px;
        border-radius: 18px;
    }

    .trusted-section .section-title {
        font-size: 22px;
    }

    .trusted-section .section-text {
        font-size: 14px;
    }

    .trusted-section .trusted-image {
        height: 200px;
    }

    .trusted-section .trusted-image-wrap {
        max-height: 200px;
    }
}
</style>

{{-- SERVICES CARDS --}}
@if(!empty($service->services_section_title) || count($cards))
<section class="service-section pt-0">
    <div class="container">
        @if(!empty($service->services_section_title))
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="section-title text-dark">{{ $service->services_section_title }}</h2>
                </div>
            </div>
        @endif

      @if(count($cards))
    <div class="row">
        @foreach($cards as $card)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card">
                    @if(!empty($card['image']))
                        <img src="{{ asset($card['image']) }}"  loading="lazy" alt="{{ ($card['title'] ?? 'Service') }} Near Me">
                    @endif
                    <div class="service-card-body">
                        @if(!empty($card['title']))
                            <h4>{{ $card['title'] }}</h4>
                        @endif

                        @if(!empty($card['description']))
                            <p class="section-text mb-0 text-dark">{{ $card['description'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
    </div>
</section>
@endif

{{-- SEO SECTION --}}
@if(!empty($service->seo_title) || !empty($service->seo_description) || !empty($service->seo_image))
<section class="service-section pt-0">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 order-2 order-lg-1">
                @if(!empty($service->seo_title))
                    <h2 class="section-title text-dark">{{ $service->seo_title }}</h2>
                @endif

                @if(!empty($service->seo_description))
                    <div class="section-text text-dark">
                        {!! nl2br(e($service->seo_description)) !!}
                    </div>
                @endif
            </div>

            <div class="col-lg-6 order-1 order-lg-2">
                @if(!empty($service->seo_image))
                    <img src="{{ asset($service->seo_image) }}" loading="lazy" alt="{{ $service->seo_title }} Near Me" width="500" height="300" class="service-img">
                @endif
            </div>
        </div>
    </div>
</section>
@endif
{{-- WHY CHOOSE US --}}
@if(!empty($service->why_choose_title) || count($features) || !empty($service->why_choose_image))
<section class="service-section why-choose-section pt-0">
    <div class="container">
        <div class="why-choose-wrapper">
            <div class="row align-items-center g-4">

                <div class="col-lg-6">
                    @if(!empty($service->why_choose_image))
                        <div class="why-choose-image-wrap">
                            <img src="{{ asset($service->why_choose_image) }}"
                              alt="{{ $service->why_choose_title }} Services"
                               loading="lazy"   class="why-choose-image">
                        </div>
                    @endif
                </div>

                <div class="col-lg-6">
                    <div class="why-choose-content">
                        @if(!empty($service->why_choose_title))
                            <h2 class="section-title text-dark mb-4">
                                {{ $service->why_choose_title }}
                            </h2>
                        @endif

                        @if(count($features))
                            <div class="why-choose-list">
                                @foreach($features as $index => $feature)
                                    <div class="why-choose-card">
                                        <div class="why-choose-icon">
                                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </div>

                                        <div class="why-choose-text">
                                            @if(!empty($feature['title']))
                                                <h4 class="text-dark mb-2">
                                                    {{ $feature['title'] }}
                                                </h4>
                                            @endif

                                            @if(!empty($feature['description']))
                                                <p class="section-text text-dark mb-0">
                                                    {{ $feature['description'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endif  

<style>
    .why-choose-section .why-choose-wrapper {
    background: #ffffff;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.why-choose-section .why-choose-image-wrap {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    background: #f4f4f4;
    height: 100%;
    min-height: 420px;
}

.why-choose-section .why-choose-image {
    width: 100%;
    height: 100%;
    min-height: 420px;
    object-fit: cover;
    display: block;
}

.why-choose-section .why-choose-content {
    padding-left: 10px;
}

.why-choose-section .why-choose-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.why-choose-section .why-choose-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: #f8f9fa;
    border: 1px solid #ececec;
    border-radius: 16px;
    padding: 18px 20px;
    transition: all 0.3s ease;
}

.why-choose-section .why-choose-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
    background: #ffffff;
}

.why-choose-section .why-choose-icon {
    width: 52px;
    height: 52px;
    min-width: 52px;
    border-radius: 50%;
    background: #212529;
    color: #ffffff;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.why-choose-section .why-choose-text h4 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 6px;
}

.why-choose-section .why-choose-text p {
    font-size: 15px;
    line-height: 1.7;
}

@media (max-width: 991.98px) {
    .why-choose-section .why-choose-wrapper {
        padding: 25px;
    }

    .why-choose-section .why-choose-image-wrap,
    .why-choose-section .why-choose-image {
        min-height: 300px;
    }

    .why-choose-section .why-choose-content {
        padding-left: 0;
    }
}

@media (max-width: 575.98px) {
    .why-choose-section .why-choose-wrapper {
        padding: 18px;
        border-radius: 18px;
    }

    .why-choose-section .why-choose-card {
        padding: 14px;
        gap: 12px;
    }

    .why-choose-section .why-choose-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        font-size: 14px;
    }

    .why-choose-section .why-choose-text h4 {
        font-size: 17px;
    }

    .why-choose-section .why-choose-text p {
        font-size: 14px;
    }
}
</style>

{{-- PROCESS STEPS --}}
@if(!empty($service->process_title) || count($processSteps))
<section class="service-section pt-0">
    <div class="container">

        @if(!empty($service->process_title))
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="section-title text-dark">{{ $service->process_title }}</h2>
                </div>
            </div>
        @endif

        @if(count($processSteps))
            <div class="row">
                @foreach($processSteps as $step)
                    <div class="col-12 mb-3">
                        <div class="process-step-box d-flex align-items-center">

                            {{-- Number --}}
                            @if(!empty($step['number']))
                                <div class="process-number">
                                    {{ str_pad($step['number'], 2, '0', STR_PAD_LEFT) }}
                                </div>
                            @endif

                            {{-- Title --}}
                            @if(!empty($step['title']))
                                <div class="process-text">
                                    {{ $step['title'] }}
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</section>
@endif

<style>
    .process-step-box {
    background: #f1f1f1;
    border-radius: 10px;
    padding: 15px 20px;
    gap: 15px;
}

.process-number {
    width: 45px;
    height: 45px;
    min-width: 45px;
    border-radius: 50%;
    background: #d9d9d9;
    color: #555;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.process-text {
    font-size: 16px;
    font-weight: 500;
    color: #333;
}   
</style>

{{-- PARTNER SECTION --}}
@if(!empty($service->partner_title) || count($partnerPoints) || !empty($service->partner_image))
<section class="  pt-5 pb-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                @if(!empty($service->partner_image))
                    <img src="{{ asset($service.partner_image) }}" loading="lazy" alt="{{ $service->partner_title }} Near Me" width="500" height="300" class="service-img">
                @endif
            </div>

            <div class="col-lg-6">
                @if(!empty($service->partner_title))
                    <h2 class="section-title text-dark">{{ $service->partner_title }}</h2>
                @endif

                @if(count($partnerPoints))
                    <ul class="service-list-points">
                        @foreach($partnerPoints as $point)
                            <li class="text-dark">{{ $point }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- FAQ --}}
@if(!empty($service->faq_title) || count($faqs))
<section class="faq">
    <div class="container-fulde">

        {{-- Title --}}
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-8 text-center">
                <div class="section-title">
                    <h4 class="text-dark">{{ $service->faq_title ?? ($faq_section?->content?->title ?? '') }}</h4>
                  
                </div>
            </div>
        </div>

        {{-- FAQs --}}
        @if(count($faqs))
            @php
                $faqsArray = is_array($faqs) ? $faqs : $faqs->toArray();
                $faqsCount = count($faqsArray);
                $halfCount = ceil($faqsCount / 2);
                $firstHalf = array_slice($faqsArray, 0, $halfCount);
                $secondHalf = array_slice($faqsArray, $halfCount);
            @endphp

            <div  class="row p-2">
                <div class="col-xl-12 col-lg-12">
                    <div class="accordion" id="accordionExample">
                        <div class="row">

                            {{-- Left Side --}}
                            <div class="col-xl-6 col-lg-6">
                                @foreach($firstHalf as $index => $faq)
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                                        data-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                                    {{ $faq['question'] ?? '' }}
                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse{{ $index }}" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                {{ $faq['answer'] ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Right Side --}}
                            <div class="col-xl-6 col-lg-6">
                                @foreach($secondHalf as $index => $faq)
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                                        data-target="#collapse{{ $index + $halfCount }}" aria-expanded="false" aria-controls="collapse{{ $index + $halfCount }}">
                                                    {{ $faq['question'] ?? '' }}
                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse{{ $index + $halfCount }}" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                {{ $faq['answer'] ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</section>
@endif

{{-- CTA --}}
@if(!empty($service->cta_title) || !empty($service->cta_description) || !empty($service->cta_image))
<section class="service-section pt-0">
    <div class="container-fulde">
        <div class="service-cta">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    @if(!empty($service->cta_title))
                        <h2 class="section-title mb-3">{{ $service->cta_title }}</h2>
                    @endif

                    @if(!empty($service->cta_description))
                        <p class="section-text mb-4">{{ $service->cta_description }}</p>
                    @endif

                    @if(!empty($service->cta_button_text) && !empty($service->cta_button_link))
                        <a href="{{ $service->cta_button_link }}" class="service-btn">
                            {{ $service->cta_button_text }}
                        </a>
                    @endif
                </div>

                <div class="col-lg-4">
                    @if(!empty($service->cta_image))
                        <img src="{{ asset($service.cta_image) }}" loading="lazy" alt="{{ $service->cta_title }}  Services" width="500" height="300">
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif


@endsection