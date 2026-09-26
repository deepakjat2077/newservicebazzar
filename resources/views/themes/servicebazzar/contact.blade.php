@extends($themeView.'layouts.site')
@section('content')
@php
    $seoTitle = 'Contact Us | Service Bazzar Customer Support';
    $seoDescription = 'Get in touch with Service Bazzar. Contact our customer support team for help with listings and inquiries.';
@endphp
@section('title', $seoTitle)

@php
    // Same $contacts rows the old page listed (Address / Email / Phone,
    // managed from admin/theme/item/contact) — reused here as the
    // "Head Office / Support / Website" list on the right instead of the
    // three-icon row the page used to open with.
    $contactAddress = $contacts->first(function ($row) {
        return stripos($row->content->title ?? '', 'address') !== false
            || stripos($row->content->title ?? '', 'office') !== false;
    });

    $mapQuery = $contactAddress ? trim($contactAddress->content->content) : ($setting->site_name ?? '');
@endphp

<style>
    /*
     * Logo palette: navy/near-black wordmark ("Service") = #19407f /
     * #245399, red wordmark + pin badge ("Bazzar") = #f47f82 / #e0555a.
     * Every accent on this page pulls from these two instead of the
     * theme's generic purple-blue (#5964ff).
     *
     * Hardcoded as plain hex everywhere below rather than CSS custom
     * properties (var(--brand-navy) etc.) — those were resolving to
     * nothing on the live page, which made the whole
     * background: linear-gradient(...) declaration invalid and fall back
     * to transparent. That's why the form box rendered as near-white text
     * on a near-white background instead of navy.
     */
    .contact-page{
        background:#f6f7fb;
    }

    .page-header {
        background-size: cover;
        height: 250px;
        display: inline-block;
        background-repeat: no-repeat;
        width: 100%;
        position:relative;
    }

    .page-header::after{
        content:"";
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        height:5px;
        background:linear-gradient(90deg, #f47f82, #19407f);
    }

    .page-header .overlay {
        width: 100%;
        height: 100%;
        background: rgba(16,28,52,0.55);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0 15px;
    }

    .page-header h1 {
        color: #fff;
        font-size: 40px;
        font-weight: 800;
        margin: 0 0 8px;
        letter-spacing:.5px;
    }

    .page-header h2 {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        opacity: .95;
        letter-spacing:.5px;
    }
    .sks{
        text-align: center;
    }

    @media (max-width:575px){
        .page-header h1 { font-size: 28px; }
        .page-header h2 { font-size: 16px; }
    }

    /* ============================== GET IN TOUCH ============================== */
    .git-form-box{
        background:linear-gradient(155deg, #245399, #19407f 65%);
        border-radius:12px;
        padding:42px 38px;
        height:100%;
        position:relative;
        overflow:hidden;
        box-shadow:0 20px 45px -18px rgba(16,28,52,.55);
    }

    .git-form-box::before{
        content:"";
        position:absolute;
        top:0;
        left:0;
        right:0;
        height:5px;
        background:linear-gradient(90deg, #f47f82, #f9a3a6);
    }

    .git-form-box h2{
        color:#fff;
        font-size:26px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.5px;
        margin-bottom:6px;
    }

    .git-form-subtitle{
        color:rgba(255,255,255,.55);
        font-size:14px;
        margin-bottom:26px;
    }

    .git-form-box label{
        color:#b9c1d6;
        font-size:13px;
        margin-bottom:6px;
        display:block;
    }

    .git-form-box .form-control{
        background:rgba(255,255,255,.06);
        border:1px solid rgba(255,255,255,.14);
        color:#fff;
        border-radius:6px;
        height:48px;
        padding:0 16px;
        transition:border-color .2s ease, background .2s ease;
    }

    .git-form-box textarea.form-control{
        height:auto;
        padding:14px 16px;
        resize:vertical;
    }

    .git-form-box .form-control::placeholder{
        color:#8891a8;
    }

    .git-form-box .form-control:focus{
        background:rgba(255,255,255,.1);
        border-color:#f47f82;
        color:#fff;
        box-shadow:0 0 0 3px rgba(244,127,130,.18);
    }

    .git-form-actions{
        display:flex;
        gap:14px;
        margin-top:10px;
    }

    .git-btn-submit,
    .git-btn-cancel{
        border-radius:6px;
        padding:13px 36px;
        font-weight:700;
        font-size:14px;
        letter-spacing:.5px;
        text-transform:uppercase;
        cursor:pointer;
        transition:transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
    }

    .git-btn-submit{
        background:#f47f82;
        border:1px solid #f47f82;
        color:#fff;
        box-shadow:0 10px 22px -8px rgba(244,127,130,.7);
    }
    .git-btn-submit:hover{
        background:#e0555a;
        border-color:#e0555a;
        color:#fff;
        transform:translateY(-2px);
    }

    .git-btn-cancel{
        background:transparent;
        border:1px solid rgba(255,255,255,.35);
        color:#fff;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
    }
    .git-btn-cancel:hover{
        background:rgba(255,255,255,.1);
        border-color:rgba(255,255,255,.6);
        color:#fff;
        transform:translateY(-2px);
    }

    /* ============================== GET IN TOUCH WITH ============================== */
    .git-info-box{
        background:#fff;
        border-radius:12px;
        padding:42px 38px;
        height:100%;
        box-shadow:0 20px 45px -22px rgba(16,28,52,.25);
    }

    .git-info-box h3{
        font-size:21px;
        font-weight:800;
        text-transform:uppercase;
        color:#19407f;
        margin-bottom:6px;
        letter-spacing:.3px;
    }

    .git-info-underline{
        width:52px;
        height:4px;
        border-radius:4px;
        background:linear-gradient(90deg, #f47f82, #f9a3a6);
        margin-bottom:26px;
    }

    .git-info-row{
        display:flex;
        gap:18px;
        align-items:flex-start;
        padding:16px 10px;
        border-radius:10px;
        margin-bottom:8px;
        transition:background .2s ease, transform .2s ease;
        text-decoration:none;
        color:inherit;
    }

    a.git-info-row{
        cursor:pointer;
    }

    .git-info-row:hover{
        background:#fbf2f2;
        transform:translateX(4px);
    }

    .git-info-row + .git-info-row{
        border-top:1px solid #f0f1f5;
    }

    .git-info-icon{
        flex:0 0 48px;
        width:48px;
        height:48px;
        border-radius:50%;
        background:rgba(244,127,130,.1);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:19px;
        color:#f47f82;
    }

    .git-info-content h5{
        font-size:15px;
        font-weight:700;
        color:#19407f;
        margin-bottom:6px;
    }

    .git-info-content p,
    .git-info-content a{
        font-size:14px;
        color:#666;
        margin:0;
        line-height:1.6;
        text-decoration:none;
    }

    .git-info-content a:hover{
        color:#f47f82;
    }

    /* ============================== HOW TO REACH US ============================== */
    .git-reach-heading{
        text-align:center;
        margin:70px 0 30px;
    }

    .git-reach-heading h2{
        font-size:30px;
        font-weight:800;
        color:#19407f;
        text-transform:uppercase;
    }

    .git-reach-heading h2 span{
        color:#f47f82;
    }

    .git-reach-dots{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        margin-top:10px;
    }

    .git-reach-dots span{
        width:8px;
        height:8px;
        border-radius:50%;
        display:inline-block;
    }
    .git-reach-dots span:nth-child(1){ background:#17c1c9; }
    .git-reach-dots span:nth-child(2){ background:#f47f82; }
    .git-reach-dots span:nth-child(3){ background:#19407f; }

    .git-map-wrap{
        width:100%;
        line-height:0;
        border-top:4px solid #f47f82;
    }

    .git-map-wrap iframe{
        width:100%;
        height:450px;
        border:0;
        display:block;
    }

    .git-map-outer{
        margin-bottom:0;
    }

    @media (max-width:991px){
        .git-form-box,
        .git-info-box{
            margin-bottom:35px;
        }
    }
</style>

<div class="contact-page">

<section class="page-header"
    style="background: url('{{ asset('assets/images/calling.png') }}') center center/cover no-repeat;">
    <div class="overlay">
        <div class=" sks container">
            <h1>Contact Us</h1>
            <h2>Get in Touch with Service Bazzar</h2>
        </div>
    </div>
</section>

<section class="contact-section pt-80 pb-80">
    <div class="container">

        <div class="row align-items-stretch">

            {{-- ============================ GET IN TOUCH (form) ============================ --}}
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="git-form-box">
                    <h2>Get In Touch</h2>
                    <p class="git-form-subtitle">We'd love to hear from you — send us a message.</p>

                    <form action="{{ route('contact.sumbit') }}" method="post" class="captchaVerify">
                        @csrf
                        @include('themes.servicebazzar.partials.form-guard')

                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input type="text" name="name" class="form-control" placeholder="Your Name">
                        </div>

                        <div class="form-group">
                            <label>@lang('Email')</label>
                            <input type="email" name="email" class="form-control" placeholder="Your Mail">
                        </div>

                        <div class="form-group">
                            <label>@lang('Mobile Number')</label>
                            <input type="text" name="phone" class="form-control" placeholder="Your Mobile Number"
                                   maxlength="15" inputmode="numeric" value="{{ old('phone') }}">
                        </div>

                        <div class="form-group">
                            <label>@lang('Subject')</label>
                            <input type="text" name="subject" class="form-control" placeholder="Subject">
                        </div>

                        <div class="form-group">
                            <label>@lang('Message')</label>
                            <textarea class="form-control" name="message" rows="5" placeholder="Message" aria-label="Message"></textarea>
                        </div>

                        @if(@$setting->recaptcha->status == 1)
                            <div class="form-group">
                                @include('includes.recaptcha')
                            </div>
                        @endif

                        <div class="git-form-actions">
                            <button type="submit" class="git-btn-submit">@lang('Submit')</button>
                            <button type="reset" class="git-btn-cancel">@lang('Cancel')</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================ GET IN TOUCH WITH ============================ --}}
            <div class="col-lg-6">
                <div class="git-info-box">
                    <h3>Get In Touch With {{ $setting->site_name ?? config('app.name') }}</h3>
                    <div class="git-info-underline"></div>

                    @foreach($contacts as $contact)
                        @php
                            $ctitle = $contact->content->title ?? '';
                            $cval   = trim($contact->content->content ?? '');
                            $isPhone = stripos($ctitle, 'phone') !== false || stripos($ctitle, 'mobile') !== false || stripos($ctitle, 'call') !== false;
                            $isEmail = stripos($ctitle, 'email') !== false || stripos($ctitle, 'mail') !== false;
                            $telHref = '+' . ltrim(preg_replace('/[^0-9]/', '', $cval), '0');
                            $rowHref = $isPhone ? 'tel:' . $telHref : ($isEmail ? 'mailto:' . $cval : null);
                        @endphp
                        <{{ $rowHref ? 'a' : 'div' }} class="git-info-row"@if($rowHref) href="{{ $rowHref }}"@endif>
                            <div class="git-info-icon">
                                @php echo $contact->content->icon @endphp
                            </div>
                            <div class="git-info-content">
                                <h5>{{ __($ctitle) }}</h5>
                                <p>{{ __($cval) }}</p>
                            </div>
                        </{{ $rowHref ? 'a' : 'div' }}>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ============================ HOW TO REACH US ============================ --}}
<div class="git-reach-heading">
    <h2>How To <span>Reach Us</span></h2>
    <div class="git-reach-dots">
        <span></span><span></span><span></span>
    </div>
</div>

@if(!empty($mapQuery))
    <div class="git-map-wrap git-map-outer">
        <iframe
            title="Service Bazzar location on Google Maps"
            src="https://www.google.com/maps?q={{ urlencode($mapQuery) }}&output=embed"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen>
        </iframe>
    </div>
@endif

</div>
@endsection
