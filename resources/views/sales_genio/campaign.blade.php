<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Campaign — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input,select,textarea{font-family:'Poppins',sans-serif;} body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .cmp-wrap{max-width:1180px;margin:0 auto;padding:18px 20px 70px;}
  .cmp-lead{display:flex;align-items:center;gap:16px;}
  .cmp-logo{height:30px;width:auto;display:block;}
  .cmp-top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;}
  .cmp-back{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #d7dceb;border-radius:22px;
    padding:8px 16px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;}
  .cmp-biz{font-size:14px;color:#6b7285;}
  .cmp-biz strong{color:#12203f;font-size:16px;display:block;}
  .cmp-flash{border-radius:11px;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:14px;}
  .cmp-flash.ok{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;}
  .cmp-flash.warn{background:#fff7e8;border:1px solid #f2dca6;color:#8a6412;}
  .cmp-layout{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;}

  /* left menu */
  .cmp-side{flex:0 0 260px;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:14px 0;max-width:100%;}
  .cmp-side h3{font-size:12.5px;text-transform:none;color:#8a92a6;font-weight:600;margin:6px 0 6px;padding:0 18px;}
  .cmp-side a{display:flex;align-items:center;gap:11px;padding:11px 18px;color:#12203f;text-decoration:none;font-size:14px;
    font-weight:600;border-left:3px solid transparent;}
  .cmp-side a:hover{background:#f7f9fc;}
  .cmp-side a.is-on{border-left-color:#1a73e8;color:#1a73e8;background:#f2f7fe;}
  .cmp-side i{width:18px;text-align:center;color:#6b7285;}
  .cmp-side a.is-on i{color:#1a73e8;}
  .cmp-side .tick{margin-left:auto;color:#1f9d55;font-size:13px;}

  /* right panel */
  .cmp-panel{flex:1;min-width:300px;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:26px;}
  .cmp-blank{text-align:center;padding:40px 20px;color:#6b7285;font-size:15px;line-height:1.6;}
  .cmp-blank i{font-size:52px;color:#dbe3f0;display:block;margin-bottom:14px;}
  .cmp-detail{display:flex;gap:28px;flex-wrap:wrap;}
  .cmp-shot{flex:0 0 300px;max-width:100%;}
  .cmp-shot img{width:100%;border-radius:10px;}
  .cmp-info{flex:1;min-width:270px;}
  .cmp-info h1{font-size:21px;font-weight:800;margin:0 0 14px;color:#12203f;}
  .cmp-info h2{font-size:14.5px;font-weight:800;margin:16px 0 10px;color:#1a2235;}
  .cmp-ben{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;}
  .cmp-ben li{display:flex;gap:10px;font-size:13.5px;color:#37415a;line-height:1.45;}
  .cmp-ben i{color:#1f9d55;font-size:16px;flex-shrink:0;}
  .cmp-chip{display:inline-block;background:#eef4fb;color:#1c6fb8;border-radius:8px;padding:7px 13px;font-size:12.5px;
    font-weight:600;margin:10px 8px 0 0;}
  .cmp-plans{display:flex;gap:10px;flex-wrap:wrap;}
  .cmp-plan{position:relative;min-width:112px;border:1px solid #e6e9f2;background:#f8f9fc;border-radius:11px;padding:12px 15px;cursor:pointer;margin:0;}
  .cmp-plan input{position:absolute;opacity:0;}
  .cmp-plan.is-on{border-color:#1a73e8;background:#eef6fd;}
  .cmp-plan.is-on:after{content:"✓";position:absolute;top:-9px;right:-8px;width:22px;height:22px;border-radius:50%;
    background:#fff;border:1px solid #1a73e8;color:#1a73e8;font-size:12px;line-height:20px;text-align:center;}
  .cmp-plan span.l{display:block;font-size:12.5px;color:#6b7285;margin-bottom:4px;}
  .cmp-plan.is-on span.l{color:#1a73e8;}
  .cmp-plan span.p{display:block;font-size:18px;font-weight:800;color:#12203f;}
  .cmp-loc{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;}
  .cmp-loc select{border:1px solid #dfe3ec;border-radius:9px;padding:9px 12px;font-size:13.5px;min-width:150px;}
  .cmp-actions{display:flex;gap:12px;margin-top:22px;flex-wrap:wrap;}
  .cmp-skip{flex:0 0 140px;text-align:center;border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:10px;
    padding:12px;font-weight:700;text-decoration:none;font-size:14px;}
  .cmp-add{flex:1;min-width:180px;background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:12px;font-weight:700;
    font-size:14px;cursor:pointer;}
  .cmp-note{display:flex;gap:8px;background:#fff8e8;border:1px solid #f2dca6;color:#8a6412;border-radius:10px;
    padding:11px 13px;font-size:12.5px;margin-top:16px;}

  /* checklist + cart */
  .cmp-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:20px;margin-top:18px;}
  .cmp-card h2{font-size:15.5px;font-weight:800;margin:0 0 4px;color:#12203f;}
  .cmp-card p.hint{color:#6b7285;font-size:13px;margin:0 0 12px;}
  .cmp-list{list-style:none;padding:0;margin:0;}
  .cmp-list li{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f3f8;font-size:13.5px;}
  .cmp-list li:last-child{border-bottom:0;}
  .cmp-list .ok i{color:#1f9d55;}
  .cmp-list .no i{color:#e0b021;}
  .cmp-list .grow{flex:1;}
  .cmp-list .why{display:block;color:#8a92a6;font-size:12px;margin-top:2px;}
  .cmp-fix{border:1px solid #1a73e8;color:#1a73e8;border-radius:8px;padding:5px 12px;font-size:12.5px;font-weight:700;text-decoration:none;}
  .cmp-cart td{padding:9px 0;border-bottom:1px solid #f1f3f8;font-size:13.5px;}
  .cmp-cart td:last-child{text-align:right;font-weight:700;}
  .cmp-cart tr:last-child td{border-bottom:0;}
  .cmp-total{display:flex;justify-content:space-between;padding-top:12px;font-size:15px;font-weight:800;color:#12203f;}
  .cmp-dim{opacity:.45;pointer-events:none;}
  .cmp-missing{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;}
  .cmp-missing a{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #f0d49a;color:#8a6412;
    border-radius:8px;padding:7px 12px;font-size:12.5px;font-weight:700;text-decoration:none;}
  .cmp-missing a:hover{background:#fffaf0;}
  .cmp-x{background:none;border:0;color:#b4261f;cursor:pointer;font-size:13px;}
  .cmp-free{background:#eafaf1;color:#1f7a45;border-radius:20px;padding:3px 10px;font-size:11.5px;font-weight:700;}

  /* popup */
  .cmp-modal{position:fixed;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;padding:20px;z-index:80;}
  .cmp-modal__box{background:#fff;border-radius:14px;max-width:520px;width:100%;box-shadow:0 20px 50px rgba(16,24,40,.25);}
  .cmp-modal__head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eef0f6;}
  .cmp-modal__head strong{font-size:15px;color:#12203f;}
  .cmp-modal__head button{background:none;border:0;font-size:20px;color:#8a92a6;cursor:pointer;line-height:1;}
  .cmp-modal__body{padding:18px 20px;}
  .cmp-modal__body p{margin:0 0 12px;font-size:13.5px;color:#37415a;line-height:1.5;}
  .cmp-modal__foot{display:flex;gap:12px;padding:0 20px 20px;}
  .cmp-now{flex:1;text-align:center;background:#1a73e8;color:#fff;border-radius:9px;padding:11px;font-weight:700;text-decoration:none;font-size:14px;}
  @media (max-width:820px){.cmp-side{flex-basis:100%;}}

  /* ---------- phones ---------- */
  @media (max-width:760px){
    .cmp-wrap{padding:14px 12px 44px;}
    .cmp-top{gap:10px;margin-bottom:12px;}
    .cmp-biz{text-align:left;}
    .cmp-layout{gap:12px;}

    /* package menu becomes a scrolling strip of chips */
    .cmp-side{flex:1 1 100%;display:flex;gap:8px;overflow-x:auto;padding:10px;border-radius:12px;
      -webkit-overflow-scrolling:touch;}
    .cmp-side h3{display:none;}
    .cmp-side a{flex:0 0 auto;white-space:nowrap;border:1px solid #e6e9f2;border-radius:22px;padding:9px 14px;
      font-size:13px;border-left:1px solid #e6e9f2;}
    .cmp-side a.is-on{border-color:#1a73e8;background:#eef4fd;}
    .cmp-side .tick{margin-left:6px;}

    .cmp-panel{padding:16px 14px;border-radius:12px;}
    .cmp-blank{padding:26px 10px;font-size:14px;}
    .cmp-detail{gap:16px;}
    .cmp-shot{flex:1 1 100%;}
    .cmp-shot img{max-height:320px;object-fit:contain;}
    .cmp-info{flex:1 1 100%;min-width:0;}
    .cmp-info h1{font-size:19px;}
    .cmp-plan{flex:1 1 calc(50% - 5px);min-width:0;padding:11px 12px;}
    .cmp-plan span.p{font-size:16px;}
    .cmp-skip,.cmp-add{flex:1 1 100%;}
    .cmp-loc select{min-width:0;flex:1 1 100%;}
    .cmp-card{padding:16px 14px;}
    .cmp-cart td{font-size:13px;}
  }

  .cmp-mhead,.cmp-mlist{display:none;}
  /* Phone: app-style campaign selection — header with cart, a card per
     package on the landing screen, the chosen package on its own screen. */
  @media (max-width:760px){
    .sg-nav{display:none;}
    body{background:#fff;}
    .cmp-mhead{display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:50;background:#fff;
      padding:14px 16px;border-bottom:1px solid #eef0f4;}
    .cmp-mhead a{color:#1a1a2e;text-decoration:none;}
    .cmp-mhead .b{font-size:20px;}
    .cmp-mhead .t{flex:1;text-align:center;font-size:17px;font-weight:500;color:#1a1a2e;
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .cmp-mhead .c{position:relative;font-size:21px;padding-right:6px;}
    .cmp-mhead .c b{position:absolute;top:-8px;right:-6px;min-width:18px;height:18px;line-height:18px;border-radius:9px;
      background:#e53935;color:#fff;font-size:11px;text-align:center;padding:0 4px;}
    .cmp-wrap{padding:6px 16px 32px !important;}
    .cmp-top .cmp-back,.cmp-top .cmp-logo{display:none;}
    .cmp-top{margin-bottom:6px;}
    .cmp-biz{font-size:13px;color:#7a8296;}
    .cmp-side{display:none !important;}
    .cmp-wrap.is-list .cmp-panel{display:none;}

    .cmp-mlist{display:block;flex:1 1 100%;}
    .cmp-mlist h3{font-size:15px;font-weight:400;color:#6b7285;margin:18px 0 12px;}
    .cmp-mlist h3:first-child{margin-top:6px;}
    .cmp-mcard{display:flex;align-items:center;gap:16px;border:1px solid #dfe3ec;border-radius:6px;
      padding:16px;margin-bottom:12px;text-decoration:none;color:#1a1a2e;background:#fff;}
    .cmp-mcard:active{background:#f5f8fe;}
    .cmp-mcard .ic{width:24px;text-align:center;font-size:20px;color:#1a1a2e;}
    .cmp-mcard .tx{flex:1;min-width:0;}
    .cmp-mcard .tx b{display:block;font-size:16px;font-weight:500;}
    .cmp-mcard .tx small{display:block;font-size:13.5px;color:#7a8296;margin-top:3px;line-height:1.4;}
    .cmp-mcard .tx em{display:inline-block;margin-top:6px;font-style:normal;font-size:11.5px;font-weight:600;
      color:#1f7a45;background:#eafaf1;border-radius:10px;padding:2px 9px;}
    .cmp-mcard .go{font-size:16px;color:#37415a;}

    /* A package on its own screen: picture, benefits, one pinned action. */
    .cmp-wrap.is-detail{padding-bottom:110px !important;}
    .cmp-wrap.is-detail .cmp-top{display:none;}
    .cmp-wrap.is-detail .cmp-panel{border:0;padding:0;box-shadow:none;}
    .cmp-wrap.is-detail .cmp-shot{margin:0 -16px;padding:22px 16px;background:linear-gradient(180deg,#eef1f7,#f7f8fb);}
    .cmp-wrap.is-detail .cmp-info h1{display:none;}
    .cmp-wrap.is-detail .cmp-info h2{font-size:19px;font-weight:600;margin:20px 0 12px;color:#12203f;}
    .cmp-wrap.is-detail .cmp-ben li{font-size:15px;line-height:1.5;margin-bottom:12px;}
    .cmp-wrap.is-detail .cmp-actions{position:fixed;left:0;right:0;bottom:0;z-index:60;margin:0;background:#fff;
      border-top:1px solid #eef0f4;display:flex;gap:12px;padding:12px 16px calc(12px + env(safe-area-inset-bottom));}
    .cmp-wrap.is-detail .cmp-actions > *{flex:1 1 0 !important;border-radius:6px;padding:13px 10px;font-size:16.5px;font-weight:500;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')
@include('sales_genio.partials.flow_chrome')

@php
  $missingRequired = collect($required)->where('done', false)->values();
  $missingOptional = collect($optional)->where('done', false)->values();
  $locked = false;
  $sym = '₹';
@endphp

{{-- Phone header: back, title, this business's cart. --}}
<div class="cmp-mhead">
  <a href="{{ $selected ? route('sales_genio.business.campaign', $business->id) : route('sales_genio.business.profile', $business->id) }}"
     class="b" aria-label="Back"><i class="fas fa-arrow-left"></i></a>
  <span class="t">{{ $selected ? $selected->name : 'Campaign Selection' }}</span>
  <a href="{{ route('sales_genio.business.cart', $business->id) }}" class="c" aria-label="Cart for {{ $business->name }}">
    <i class="fas fa-cart-shopping"></i><b>{{ $cart->count() }}</b>
  </a>
</div>

<div class="cmp-wrap {{ $selected ? 'is-detail' : 'is-list' }}">
  <div class="cmp-top">
    <div class="cmp-lead">
      <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="cmp-back"><i class="fas fa-arrow-left"></i> Back to profile</a>
      <img class="cmp-logo" src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar" width="128" height="28">
    </div>
    @include('sales_genio.partials.campaign_dcart')
    <div class="cmp-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
    @include('sales_genio.partials.flow_avatar')
  </div>

  @if(session('success'))<div class="cmp-flash ok"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
  @if(session('error'))<div class="cmp-flash warn"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif

  <div class="cmp-layout">
    @include('sales_genio.partials.campaign_sidebar', ['mobileList' => !$selected])

    {{-- right: the chosen package --}}
    <div class="cmp-panel {{ $locked ? 'cmp-dim' : '' }}">
      @if(!$selected)
        <div class="cmp-blank">
          <i class="fas fa-puzzle-piece"></i>
          Please select one of the following product from the menu on<br>
          the left to view product details and be directed to the selected product flow.
        </div>
      @else
        @php
          $plans = $selected->planList();
          $tree = $selected->needsLocation() ? $selected->positionPriceTree() : [];
          $shot = $preview ?: $selected->imageUrl();
        @endphp
        <div class="cmp-detail">
          <div class="cmp-shot">
            @if($selected->id === 7)
              {{-- Combo: the web banner, then the mobile banner, taking turns. --}}
              <div class="bm-slides" id="bmSlides">
                <div class="bm-slide on">@include('sales_genio.partials.banner_mock', ['kind' => 'web'])<p>On the website</p></div>
                <div class="bm-slide">@include('sales_genio.partials.banner_mock', ['kind' => 'mobile'])<p>On mobile</p></div>
                <div class="bm-dots"><button type="button" class="on" aria-label="Website banner"></button><button type="button" aria-label="Mobile banner"></button></div>
              </div>
            @elseif($selected->id === 10)
              @php
                $topThumb = null;
                foreach ((array) $business->photos as $tp) {
                    $tf = public_path('assets/images/listing/photos/' . $tp);
                    if (is_file($tf) && ($ts = @imagecreatefromwebp($tf) ?: @imagecreatefromstring((string) file_get_contents($tf)))) {
                        $tt = imagescale($ts, 160);
                        ob_start(); imagewebp($tt, null, 70); $topThumb = 'data:image/webp;base64,' . base64_encode(ob_get_clean());
                        imagedestroy($ts); imagedestroy($tt);
                        break;
                    }
                }
              @endphp
              {{-- Top 3: the business shown at position 1, then 2, then 3, taking turns. --}}
              <div class="bm-slides" id="bmSlides">
                @foreach([1, 2, 3] as $r)
                  <div class="bm-slide {{ $r === 1 ? 'on' : '' }}">@include('sales_genio.partials.banner_mock', ['kind' => 'top', 'rank' => $r, 'thumb' => $topThumb ?: null])<p>Top {{ $r }} position</p></div>
                @endforeach
                <div class="bm-dots">@foreach([1, 2, 3] as $r)<button type="button" class="{{ $r === 1 ? 'on' : '' }}" aria-label="Top {{ $r }}"></button>@endforeach</div>
              </div>
            @elseif(in_array($selected->id, [5, 6], true))
              @include('sales_genio.partials.banner_mock', ['kind' => $selected->id === 6 ? 'mobile' : 'web'])
            @elseif(in_array($selected->id, [8, 9], true) && count($selected->imageUrls()) > 1)
              {{-- Website combos: the website first, then the Google / social picture, taking turns. --}}
              @php $urls = $selected->imageUrls(); $pics = array_merge(array_slice($urls, 1), [$urls[0]]); @endphp
              <div class="bm-slides" id="bmSlides">
                @foreach($pics as $k => $u)
                  <div class="bm-slide {{ $k === 0 ? 'on' : '' }}"><img src="{{ $u }}" alt="{{ $selected->name }}"><p>{{ $k === 0 ? 'Your website' : ($selected->id === 8 ? 'Google Business Profile' : 'Facebook & Instagram') }}</p></div>
                @endforeach
                <div class="bm-dots">@foreach($pics as $k => $u)<button type="button" class="{{ $k === 0 ? 'on' : '' }}" aria-label="Picture {{ $k + 1 }}"></button>@endforeach</div>
              </div>
            @elseif($shot)<img src="{{ $shot }}" alt="{{ $selected->name }}">@endif
              <style>
                .bm-slides{position:relative;}
                .bm-slide{display:none;}
                .bm-slide.on{display:block;animation:bmFade .4s ease;}
                .bm-slide p{text-align:center;font-size:13px;color:#6b7285;margin:8px 0 0;}
                .bm-dots{display:flex;justify-content:center;gap:8px;margin-top:10px;}
                .bm-dots button{width:9px;height:9px;border-radius:50%;border:0;padding:0;background:#cfd5e2;cursor:pointer;}
                .bm-dots button.on{background:#1a73e8;width:22px;border-radius:5px;}
                @keyframes bmFade{from{opacity:0}to{opacity:1}}
              </style>
              <script>
                (function () {
                  var box = document.getElementById('bmSlides');
                  if (!box) { return; }
                  var slides = box.querySelectorAll('.bm-slide');
                  var dots = box.querySelectorAll('.bm-dots button');
                  var i = 0, timer;
                  function show(n) {
                    i = n % slides.length;
                    slides.forEach(function (s, k) { s.classList.toggle('on', k === i); });
                    dots.forEach(function (d, k) { d.classList.toggle('on', k === i); });
                  }
                  function start() { clearInterval(timer); timer = setInterval(function () { show(i + 1); }, 3500); }
                  dots.forEach(function (d, k) { d.addEventListener('click', function () { show(k); start(); }); });
                  start();
                })();
              </script>
          </div>

          <div class="cmp-info">
            <h1>{{ $selected->name }}</h1>

            <h2>Benefits You get</h2>
            <ul class="cmp-ben">
              @foreach($selected->benefitList() as $benefit)
                <li><i class="fas fa-check"></i> {{ $benefit }}</li>
              @endforeach
            </ul>

            @foreach($selected->highlightList() as $hl)
              <span class="cmp-chip">{{ $hl }}</span>
            @endforeach

            @if($selected->needsTemplate())
              {{-- A website: the plan is chosen here, then the design on the next step. --}}
              <form method="get" action="{{ route('sales_genio.business.campaign.template', ['id' => $business->id, 'pkg' => $selected->id]) }}">
            @else
              <form method="post" action="{{ route('sales_genio.business.campaign.cart', $business->id) }}">
                @csrf
                <input type="hidden" name="package_id" value="{{ $selected->id }}">
            @endif

              @if(false)
                <h2>Where should it run?</h2>
                <div class="cmp-loc">
                  <select name="city" id="cmpCity">
                    @foreach(array_unique(array_merge([$business->city], array_keys($tree))) as $cityName)
                      @if($cityName)<option value="{{ $cityName }}" @selected($cityName === $business->city)>{{ $cityName }}</option>@endif
                    @endforeach
                  </select>
                  <select name="area" id="cmpArea">
                    <option value="">Whole city</option>
                    @if($business->area)<option value="{{ $business->area }}" selected>{{ $business->area }}</option>@endif
                  </select>
                </div>
              @endif

              @unless($selected->needsLocation())
              <h2>Select plan</h2>
              <div class="cmp-plans" id="cmpPlans">
                @if($selected->needsLocation())
                  @php
                    $cityPrices = ($tree[$business->city][$business->area] ?? $tree[$business->city][''] ?? []);
                    $tiers = $cityPrices ?: collect($plans)->pluck('price', 'label')->all();
                  @endphp
                  @foreach($tiers as $label => $price)
                    <label class="cmp-plan {{ $loop->first ? 'is-on' : '' }}">
                      <input type="radio" name="tier" value="{{ $label }}" {{ $loop->first ? 'checked' : '' }}>
                      <span class="l">{{ $label }}</span>
                      <span class="p">{{ $sym }}{{ numberFormat($price) }}</span>
                    </label>
                  @endforeach
                @else
                  @foreach($plans as $i => $pl)
                    <label class="cmp-plan {{ $i === 0 ? 'is-on' : '' }}">
                      <input type="radio" name="plan" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }}>
                      <span class="l">{{ $pl['label'] }}</span>
                      <span class="p">{{ $sym }}{{ numberFormat($pl['price']) }}</span>
                    </label>
                  @endforeach
                @endif
              </div>
              @endunless

              @if($selected->needs_ad_details)
                <div class="cmp-note"><i class="fas fa-info-circle"></i> After payment the business adds its banner text and photo, and the banner is created automatically.</div>
              @endif

              @if($selected->needsLocation())
                <div class="cmp-actions">
                  <a class="cmp-add" style="text-align:center;text-decoration:none;line-height:1.6;"
                     href="{{ route('sales_genio.business.campaign.areas', ['id' => $business->id, 'pkg' => $selected->id]) }}">Proceed</a>
                </div>
              @else
              <div class="cmp-actions">
                @php
                  $ids = $packages->pluck('id')->values();
                  $pos = $ids->search($selected->id);
                  $nextId = $ids[$pos + 1] ?? null;
                @endphp
                <a class="cmp-skip" href="{{ $nextId
                    ? route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $nextId])
                    : route('sales_genio.business.campaign', $business->id) }}">Skip</a>
                <button type="submit" class="cmp-add">{{ $selected->needsTemplate() ? 'Continue' : 'Add to Cart' }}</button>
              </div>
              @endif
            </form>
          </div>
        </div>
      @endif
    </div>
  </div>

</div>

{{-- A free add-on earned with a campaign — shown once, right after adding it. --}}
@if(session('free_addon'))
  @php $free = session('free_addon'); @endphp
  <div class="gift-wrap" id="giftModal">
    <div class="gift-box">
      <div class="gift-ribbon">
        <i class="fas fa-gift"></i>
        <span>Free add-on unlocked</span>
      </div>
      <div class="gift-body">
        @if($free['image'])<img src="{{ $free['image'] }}" alt="{{ $free['name'] }}">@endif
        <div>
          <h3>{{ $free['name'] }}</h3>
          <p>{{ $free['note'] }}</p>
          <span class="gift-tag">Worth ₹0 to the customer · valid {{ $free['days'] }} days</span>
        </div>
      </div>
      <div class="gift-foot">
        <span>Included free when this campaign is paid — no cart line, no charge.</span>
        <button type="button" onclick="document.getElementById('giftModal').remove()">Continue</button>
      </div>
    </div>
  </div>

  <style>
  .gift-wrap{position:fixed;inset:0;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;
    padding:20px;z-index:90;}
  .gift-box{background:#fff;border-radius:16px;max-width:460px;width:100%;overflow:hidden;
    box-shadow:0 24px 60px rgba(16,24,40,.3);}
  .gift-ribbon{display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#1f9d55,#27b768);color:#fff;
    padding:14px 20px;font-weight:700;font-size:15px;}
  .gift-ribbon i{font-size:18px;}
  .gift-body{display:flex;gap:16px;padding:20px;align-items:center;}
  .gift-body img{width:120px;border:1px solid #e6e9f2;border-radius:8px;}
  .gift-body h3{margin:0 0 5px;font-size:16px;font-weight:800;color:#12203f;}
  .gift-body p{margin:0 0 8px;font-size:13px;color:#5a6478;line-height:1.5;}
  .gift-tag{display:inline-block;background:#eafaf1;color:#1f7a45;border-radius:20px;padding:4px 11px;
    font-size:11.5px;font-weight:700;}
  .gift-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 20px;
    border-top:1px solid #eef0f6;background:#fbfcfe;font-size:13px;color:#5a6478;}
  .gift-foot b{color:#1f7a45;}
  .gift-foot button{background:#1a73e8;color:#fff;border:0;border-radius:9px;padding:10px 22px;font-weight:700;
    font-size:14px;cursor:pointer;font-family:'Poppins',sans-serif;}
  .gift-foot button:hover{background:#1662c4;}
  </style>
@endif

<script>
document.querySelectorAll('.cmp-plans').forEach(function (wrap) {
  wrap.addEventListener('click', function (e) {
    var label = e.target.closest('.cmp-plan');
    if (!label) return;
    wrap.querySelectorAll('.cmp-plan').forEach(function (l) { l.classList.remove('is-on'); });
    label.classList.add('is-on');
  });
});
</script>

</body>
</html>
