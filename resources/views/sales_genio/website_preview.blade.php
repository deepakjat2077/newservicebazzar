@php
    $fonts = [
        'classic' => 'family=Playfair+Display:wght@600;700&family=Lato:wght@400;700',
        'fresh'   => 'family=Nunito:wght@400;600;700;800',
        'bold'    => 'family=Montserrat:wght@600;700;800&family=Inter:wght@400;500;600',
        'minimal' => 'family=DM+Serif+Display&family=Inter:wght@300;400;500;600',
        'vibrant' => 'family=Poppins:wght@400;500;600;700;800',
    ];

    $name     = $business->name;
    $initial  = strtoupper(mb_substr($name, 0, 1));
    $category = optional($business->category)->name ?: 'Local business';
    $place    = collect([$business->area, $business->city])->filter()->implode(', ');
    $digits   = preg_replace('/\D/', '', (string) $business->mobile);
    $phone    = strlen($digits) === 10 ? '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5) : $business->mobile;
    $lead     = $about !== '' ? \Illuminate\Support\Str::before($about, '. ') . (str_contains($about, '. ') ? '.' : '') : "Welcome to {$name} — trusted {$category} services in " . ($business->city ?: 'your city') . '.';
    $lead     = \Illuminate\Support\Str::limit($lead, 150);
    $aboutText = $about !== '' ? $about : "{$name} is a {$category} business in {$place}. We take pride in friendly service, fair prices and work our customers can count on.";

    // Always six gallery tiles: the real photos first, repeated if there are fewer.
    $tiles = $photos->isEmpty() ? collect() : collect(range(0, 5))->map(fn ($i) => $photos[$i % $photos->count()]);
    $hero  = $photos->get(0);
    $side  = $photos->get(1) ?? $photos->get(0);

    $offer = $services->isNotEmpty() ? $services : collect([$category]);
    $why = [
        ['fa-handshake', 'Friendly, honest service', 'A team that listens first and recommends only what you need.'],
        ['fa-indian-rupee-sign', 'Fair, clear pricing', 'No surprises — you know the cost before we begin.'],
        ['fa-location-dot', 'Local and easy to reach', 'Right here in ' . ($business->area ?: $business->city) . ', a call or a visit away.'],
    ];
    $reviews = [
        ['Wonderful experience from start to finish. Highly recommended!', 5],
        ['Professional, polite and very helpful team. Will visit again.', 5],
        ['Great value and quick response. Exactly what we were looking for.', 4],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>{{ $name }} — website design preview</title>
<link href="https://fonts.googleapis.com/css2?{{ $fonts[$t] }}&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}
ul{list-style:none}

/* ---------- each design is a set of variables ---------- */
body.t-classic{--bg:#fbf8f2;--ink:#1d2a44;--muted:#6a6f7c;--accent:#b8860b;--accent-ink:#fff;--surface:#fff;--line:#e6dcc8;--deep:#1d2a44;--fh:'Playfair Display',Georgia,serif;--fb:'Lato',sans-serif;--r:3px}
body.t-fresh{--bg:#f3fbf5;--ink:#143120;--muted:#5b7766;--accent:#16a34a;--accent-ink:#fff;--surface:#fff;--line:#d6ecdd;--deep:#0f3d24;--fh:'Nunito',sans-serif;--fb:'Nunito',sans-serif;--r:18px}
body.t-bold{--bg:#0d1117;--ink:#f3f4f6;--muted:#9aa3b2;--accent:#f59e0b;--accent-ink:#111;--surface:#161b23;--line:#262d38;--deep:#05070a;--fh:'Montserrat',sans-serif;--fb:'Inter',sans-serif;--r:10px}
body.t-minimal{--bg:#fff;--ink:#111827;--muted:#6b7280;--accent:#111827;--accent-ink:#fff;--surface:#fff;--line:#e5e7eb;--deep:#111827;--fh:'DM Serif Display',Georgia,serif;--fb:'Inter',sans-serif;--r:0px}
body.t-vibrant{--bg:#fff7fb;--ink:#2b1236;--muted:#7d6189;--accent:#e11d74;--accent2:#7c3aed;--accent-ink:#fff;--surface:#fff;--line:#f3d9e8;--deep:#2b1236;--fh:'Poppins',sans-serif;--fb:'Poppins',sans-serif;--r:26px}

body{font-family:var(--fb);background:var(--bg);color:var(--ink);line-height:1.6;font-size:16px;min-width:320px}
.wrap{max-width:1120px;margin:0 auto;padding:0 28px}
h1,h2,h3{font-family:var(--fh);line-height:1.15;font-weight:700}
.btn{display:inline-flex;align-items:center;gap:9px;padding:13px 26px;border-radius:var(--r);font-weight:700;font-size:15px;cursor:default;border:2px solid transparent;white-space:nowrap}
.btn-primary{background:var(--accent);color:var(--accent-ink)}
.btn-ghost{border-color:currentColor;background:transparent}
.pv-tag{position:fixed;right:14px;bottom:14px;z-index:50;background:rgba(17,24,39,.88);color:#fff;font:600 11px/1 'Inter',sans-serif;letter-spacing:.4px;padding:8px 12px;border-radius:20px}
.ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font:700 64px var(--fh);color:rgba(255,255,255,.85);background:linear-gradient(135deg,var(--accent),var(--deep))}
.eyebrow{display:inline-block;font-size:13px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:var(--accent);margin-bottom:14px}
.sec{padding:84px 0}
.sec-title{font-size:38px;margin-bottom:12px}
.sec-sub{color:var(--muted);max-width:560px;margin-bottom:44px}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}

/* ---------- shared structure ---------- */
.nav{position:relative;z-index:10}
.nav-in{display:flex;align-items:center;justify-content:space-between;gap:24px;height:84px}
.brand{display:flex;align-items:center;gap:12px;font:700 21px var(--fh)}
.brand .mark{width:42px;height:42px;border-radius:var(--r);background:var(--accent);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;font-size:20px}
.links{display:flex;gap:30px;font-size:15px;font-weight:600}
.links a{opacity:.85}
.nav .btn{padding:11px 22px;font-size:14px}

.hero-in{display:grid;grid-template-columns:1.05fr .95fr;gap:56px;align-items:center;padding-top:56px;padding-bottom:84px}
.hero h1{font-size:60px;margin-bottom:18px}
.lead{font-size:19px;color:var(--muted);max-width:520px;margin-bottom:30px}
.cta{display:flex;gap:14px;flex-wrap:wrap}
.hero-media{aspect-ratio:5/4.4;overflow:hidden;position:relative}
.hero-media img{width:100%;height:100%;object-fit:cover}

.facts-in{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;padding:26px 28px;background:var(--surface);border:1px solid var(--line);border-radius:var(--r);margin-top:-44px;position:relative;z-index:3}
.fact b{display:block;font-size:12px;text-transform:uppercase;letter-spacing:1.4px;color:var(--accent);margin-bottom:4px}
.fact span{font-weight:600;font-size:15px}

.about-in{display:grid;grid-template-columns:.9fr 1.1fr;gap:64px;align-items:center}
.about-media{aspect-ratio:4/4.4;overflow:hidden;position:relative}
.about-media img{width:100%;height:100%;object-fit:cover}
.about p{color:var(--muted);font-size:17px;margin:14px 0 22px}
.ticks li{display:flex;gap:12px;align-items:center;margin-bottom:10px;font-weight:600}
.ticks i{color:var(--accent)}

.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r);padding:30px 28px}
.card .ico{width:54px;height:54px;border-radius:var(--r);background:var(--accent);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:18px}
.card h3{font-size:21px;margin-bottom:8px}
.card p{color:var(--muted);font-size:15px}

.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.grid .tile{aspect-ratio:4/3;overflow:hidden;border-radius:var(--r);position:relative}
.grid .tile img{width:100%;height:100%;object-fit:cover}
.grid .tile:nth-child(2) img{object-position:left center}
.grid .tile:nth-child(3) img{object-position:right center}
.grid .tile:nth-child(5) img{object-position:center top}
.grid .tile .ph{font-size:30px}

.why-in{display:grid;grid-template-columns:repeat(3,1fr);gap:34px}
.why-item i{width:58px;height:58px;border-radius:50%;background:var(--accent);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;font-size:21px;margin-bottom:18px}
.why-item h3{font-size:21px;margin-bottom:8px}
.why-item p{color:var(--muted);font-size:15px}

.rev{background:var(--surface);border:1px solid var(--line);border-radius:var(--r);padding:30px 28px;position:relative}
.rev .stars{color:#f5a524;margin-bottom:14px;font-size:15px;letter-spacing:2px}
.rev p{font-size:16px;margin-bottom:18px}
.rev small{color:var(--muted);font-weight:600}
.rev .chip{position:absolute;top:18px;right:18px;font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);border:1px solid var(--line);padding:3px 8px;border-radius:20px}

.contact-in{display:grid;grid-template-columns:1fr 1fr;gap:56px}
.info li{display:flex;gap:16px;margin-bottom:22px;align-items:flex-start}
.info li i{width:46px;height:46px;border-radius:var(--r);background:var(--accent);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.info b{display:block;font-size:12px;text-transform:uppercase;letter-spacing:1.4px;color:var(--muted);margin-bottom:2px}
.info span{font-weight:600}
.form{background:var(--surface);border:1px solid var(--line);border-radius:var(--r);padding:30px}
.form h3{font-size:24px;margin-bottom:18px}
.field{border:1px solid var(--line);border-radius:calc(var(--r) / 1.6);padding:14px 16px;color:var(--muted);margin-bottom:14px;font-size:15px;background:var(--bg)}
.field.tall{height:104px}
.form .btn{width:100%;justify-content:center}
.foot{padding:34px 0;border-top:1px solid var(--line);color:var(--muted);font-size:14px}
.foot-in{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap}
.foot b{color:var(--ink)}

/* =====================================================  CLASSIC  */
.t-classic .nav{background:#fff;border-bottom:1px solid var(--line)}
.t-classic .brand .mark{border-radius:50%}
.t-classic .hero{position:relative;color:#fff;background:var(--deep);overflow:hidden}
.t-classic .hero-media{position:absolute;inset:0;aspect-ratio:auto;z-index:0}
.t-classic .hero-copy{position:relative;z-index:2}
.t-classic .hero-media::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(14,22,40,.78),rgba(14,22,40,.88))}
.t-classic .hero-in{display:block;position:static;text-align:center;padding:130px 28px 150px}
.t-classic .hero h1{font-size:68px;max-width:860px;margin:0 auto 18px}
.t-classic .hero .eyebrow{color:#e5c76b}
.t-classic .lead{color:#dbe0ea;margin:0 auto 34px}
.t-classic .cta{justify-content:center}
.t-classic .btn-ghost{color:#fff}
.t-classic .facts-in{border-top:4px solid var(--accent)}
.t-classic .sec-title{position:relative;padding-bottom:18px}
.t-classic .sec-title::after{content:'';position:absolute;left:0;bottom:0;width:70px;height:3px;background:var(--accent)}
.t-classic .card{border-top:4px solid var(--accent)}
.t-classic .card .ico{border-radius:50%}
.t-classic .tile,.t-classic .about-media{border:6px solid #fff;box-shadow:0 0 0 1px var(--line)}
.t-classic .services,.t-classic .reviews{background:#f3ecdc}
.t-classic .foot{background:var(--deep);color:#aeb6c8;border:0}
.t-classic .foot b{color:#fff}

/* =====================================================  FRESH  */
.t-fresh .nav{background:#fff}
.t-fresh .nav .btn{border-radius:30px}
.t-fresh .hero{background:linear-gradient(180deg,#e7f8ec 0%,var(--bg) 100%)}
.t-fresh .hero h1{font-size:56px;color:var(--deep)}
.t-fresh .btn{border-radius:30px}
.t-fresh .hero-media{border-radius:200px 200px 28px 28px;box-shadow:0 30px 60px rgba(22,163,74,.25)}
.t-fresh .facts-in{box-shadow:0 18px 40px rgba(20,49,32,.08);border:0}
.t-fresh .about-media{border-radius:28px}
.t-fresh .card{border:0;box-shadow:0 12px 30px rgba(20,49,32,.07)}
.t-fresh .card .ico{border-radius:50%;background:#dcf5e4;color:var(--accent)}
.t-fresh .why{background:#e7f8ec}
.t-fresh .why-item i{background:#fff;color:var(--accent);box-shadow:0 8px 20px rgba(22,163,74,.2)}
.t-fresh .rev{border:0;box-shadow:0 12px 30px rgba(20,49,32,.07)}
.t-fresh .foot{background:var(--deep);border:0;color:#a9c9b5}
.t-fresh .foot b{color:#fff}

/* =====================================================  BOLD  */
.t-bold .nav{background:transparent;border-bottom:1px solid var(--line)}
.t-bold .links a{text-transform:uppercase;letter-spacing:1px;font-size:13px}
.t-bold .brand{text-transform:uppercase;letter-spacing:.5px}
.t-bold .hero-in{grid-template-columns:1.1fr .9fr;padding-top:70px}
.t-bold .hero h1{font-size:68px;text-transform:uppercase;font-weight:800;letter-spacing:-1px}
.t-bold .hero h1::after{content:'';display:block;width:90px;height:6px;background:var(--accent);margin-top:20px}
.t-bold .hero-media{border-radius:var(--r);box-shadow:16px 16px 0 var(--accent)}
.t-bold .btn{text-transform:uppercase;letter-spacing:1px;font-size:13px}
.t-bold .btn-ghost{color:#fff}
.t-bold .facts-in{background:var(--surface)}
.t-bold .about-media{border-radius:var(--r);box-shadow:-14px 14px 0 var(--accent)}
.t-bold .sec-title{text-transform:uppercase;font-weight:800}
.t-bold .card{border-left:5px solid var(--accent)}
.t-bold .card .ico{background:transparent;border:2px solid var(--accent);color:var(--accent)}
.t-bold .services,.t-bold .reviews{background:#0a0d12}
.t-bold .tile{border:2px solid var(--line)}
.t-bold .why-item i{background:transparent;border:2px solid var(--accent);color:var(--accent);border-radius:var(--r)}
.t-bold .foot{background:#05070a;border-top:1px solid var(--line)}

/* =====================================================  MINIMAL  */
.t-minimal .nav-in{height:96px}
.t-minimal .brand .mark{display:none}
.t-minimal .brand{font-size:22px}
.t-minimal .links{font-weight:500;font-size:14px;letter-spacing:.3px}
.t-minimal .nav .btn{background:transparent;color:var(--ink);border:1px solid var(--ink);padding:10px 22px}
.t-minimal .hero-in{display:block;padding-top:72px;padding-bottom:0}
.t-minimal .hero-copy{max-width:760px;margin-bottom:64px}
.t-minimal .hero h1{font-size:76px;font-weight:400;letter-spacing:-1.5px}
.t-minimal .eyebrow{color:var(--muted);font-weight:600;letter-spacing:2px}
.t-minimal .hero-media{aspect-ratio:21/9;border-radius:0}
.t-minimal .btn{border-radius:0}
.t-minimal .btn-ghost{color:var(--ink)}
.t-minimal .facts-in{margin-top:0;border:0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);border-radius:0;padding:28px 0;background:transparent}
.t-minimal .fact b{color:var(--muted)}
.t-minimal .sec-title{font-weight:400;font-size:44px}
.t-minimal .about-media{border-radius:0;aspect-ratio:1/1}
.t-minimal .cards{display:block}
.t-minimal .card{display:grid;grid-template-columns:70px 1fr 1.4fr;gap:20px;align-items:center;border:0;border-top:1px solid var(--line);border-radius:0;padding:26px 0;background:transparent}
.t-minimal .card:last-child{border-bottom:1px solid var(--line)}
.t-minimal .card .ico{background:transparent;color:var(--ink);border:1px solid var(--ink);width:44px;height:44px;margin:0;border-radius:50%;font-size:16px}
.t-minimal .card h3{margin:0;font-weight:400;font-size:24px}
.t-minimal .card p{margin:0}
.t-minimal .grid{grid-template-columns:repeat(2,1fr);gap:26px}
.t-minimal .tile{aspect-ratio:3/2}
.t-minimal .grid .tile:nth-child(n+5){display:none}
.t-minimal .why-item i{background:transparent;color:var(--ink);border:1px solid var(--ink)}
.t-minimal .rev{border:0;border-top:1px solid var(--ink);border-radius:0;padding:26px 0 0;background:transparent}
.t-minimal .form{border-radius:0}
.t-minimal .info li i{background:transparent;color:var(--ink);border:1px solid var(--ink);border-radius:50%}
.t-minimal .foot{background:#f6f6f7;border:0}

/* =====================================================  VIBRANT  */
.t-vibrant .nav{background:transparent;position:absolute;left:0;right:0;top:0;color:#fff}
.t-vibrant .brand .mark{background:#fff;color:var(--accent)}
.t-vibrant .nav .btn{background:#fff;color:var(--accent);border-radius:40px}
.t-vibrant .hero{background:linear-gradient(135deg,#e11d74 0%,#a21caf 52%,#7c3aed 100%);color:#fff;position:relative;overflow:hidden;padding-top:84px}
.t-vibrant .hero::before,.t-vibrant .hero::after{content:'';position:absolute;border-radius:50%;background:rgba(255,255,255,.1)}
.t-vibrant .hero::before{width:520px;height:520px;right:-140px;top:-160px}
.t-vibrant .hero::after{width:300px;height:300px;left:-90px;bottom:-120px}
.t-vibrant .hero-in{position:relative;z-index:2;padding-bottom:130px}
.t-vibrant .hero h1{font-size:58px;font-weight:800}
.t-vibrant .lead{color:rgba(255,255,255,.88)}
.t-vibrant .hero .eyebrow{color:#ffd1e8;background:rgba(255,255,255,.14);padding:7px 16px;border-radius:30px;letter-spacing:1.2px}
.t-vibrant .btn{border-radius:40px}
.t-vibrant .btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff}
.t-vibrant .hero .btn-primary{background:#fff;color:var(--accent)}
.t-vibrant .btn-ghost{color:#fff}
.t-vibrant .hero-media{border-radius:46% 54% 50% 50% / 52% 46% 54% 48%;box-shadow:0 30px 70px rgba(43,18,54,.4);border:8px solid rgba(255,255,255,.35)}
.t-vibrant .facts-in{box-shadow:0 20px 50px rgba(124,58,237,.18);border:0}
.t-vibrant .about-media{border-radius:46% 54% 50% 50% / 52% 46% 54% 48%}
.t-vibrant .card{border:0;box-shadow:0 14px 34px rgba(124,58,237,.1)}
.t-vibrant .card .ico{background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:20px}
.t-vibrant .services{background:#fdeef7}
.t-vibrant .tile{border-radius:28px}
.t-vibrant .why-item i{background:linear-gradient(135deg,var(--accent),var(--accent2))}
.t-vibrant .rev{border:0;box-shadow:0 14px 34px rgba(124,58,237,.1)}
.t-vibrant .foot{background:linear-gradient(135deg,#a21caf,#7c3aed);color:#e9d5ff;border:0}
.t-vibrant .foot b{color:#fff}

/* ---------- smaller screens ---------- */
@media (max-width:860px){
  .wrap{padding:0 20px}
  .nav-in{gap:12px}
  .brand{font-size:16px;min-width:0;flex:1}
  .brand > span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .nav .btn{padding:9px 16px;flex-shrink:0}
  .links{display:none}
  .hero h1,.t-classic .hero h1,.t-bold .hero h1,.t-minimal .hero h1,.t-vibrant .hero h1,.t-fresh .hero h1{font-size:40px}
  .hero-in,.about-in,.contact-in,.t-bold .hero-in{grid-template-columns:1fr;gap:34px}
  .t-classic .hero-in{padding:80px 20px 100px}
  .facts-in{grid-template-columns:1fr 1fr}
  .cards,.why-in,.grid{grid-template-columns:1fr}
  .grid,.t-minimal .grid{grid-template-columns:1fr 1fr}
  .t-minimal .card{grid-template-columns:50px 1fr}
  .t-minimal .card p{grid-column:2}
  .sec{padding:56px 0}
  .sec-title{font-size:30px}
  .t-minimal .hero-media{aspect-ratio:16/10}
}
</style>
</head>
<body class="t-{{ $t }}">
<span class="pv-tag">DESIGN PREVIEW</span>

<header class="nav">
    <div class="wrap nav-in">
        <div class="brand"><span class="mark">{{ $initial }}</span><span>{{ \Illuminate\Support\Str::limit($name, 28) }}</span></div>
        <nav class="links"><a href="#top">Home</a><a href="#about">About</a><a href="#services">Services</a><a href="#gallery">Gallery</a><a href="#contact">Contact</a></nav>
        <span class="btn btn-primary"><i class="fas fa-phone"></i> Call now</span>
    </div>
</header>

<section class="hero" id="top">
    <div class="wrap hero-in">
        <div class="hero-copy">
            <span class="eyebrow">{{ $category }}@if($place) · {{ $place }}@endif</span>
            <h1>{{ $name }}</h1>
            <p class="lead">{{ $lead }}</p>
            <div class="cta">
                <span class="btn btn-primary"><i class="fas fa-phone"></i> {{ $phone ?: 'Call us' }}</span>
                <span class="btn btn-ghost"><i class="fab fa-whatsapp"></i> WhatsApp us</span>
            </div>
        </div>
        <div class="hero-media">
            @if($hero)<img src="{{ $hero }}" alt="">@else<div class="ph">{{ $initial }}</div>@endif
        </div>
    </div>
</section>

<div class="wrap">
    <div class="facts-in">
        <div class="fact"><b>Find us</b><span>{{ $place ?: 'Our location' }}</span></div>
        <div class="fact"><b>Open</b><span>{{ $hours ?: 'Call to confirm' }}</span></div>
        <div class="fact"><b>Call</b><span>{{ $phone ?: '—' }}</span></div>
        <div class="fact"><b>Since</b><span>{{ $business->established_year ?: 'Serving locally' }}</span></div>
    </div>
</div>

<section class="sec about" id="about">
    <div class="wrap about-in">
        <div class="about-media">@if($side)<img src="{{ $side }}" alt="">@else<div class="ph">{{ $initial }}</div>@endif</div>
        <div class="about-copy">
            <span class="eyebrow">About us</span>
            <h2 class="sec-title">A {{ strtolower($category) }} you can trust</h2>
            <p>{{ $aboutText }}</p>
            <ul class="ticks">
                <li><i class="fas fa-circle-check"></i> Experienced, friendly team</li>
                <li><i class="fas fa-circle-check"></i> Clear and honest pricing</li>
                <li><i class="fas fa-circle-check"></i> Open {{ $days ?: 7 }} days a week</li>
            </ul>
        </div>
    </div>
</section>

<section class="sec services" id="services">
    <div class="wrap">
        <span class="eyebrow">What we do</span>
        <h2 class="sec-title">Our services</h2>
        <p class="sec-sub">Everything {{ $name }} offers, in one place.</p>
        <div class="cards">
            @foreach($offer as $i => $service)
                <div class="card">
                    <div class="ico"><i class="fas {{ ['fa-star', 'fa-gem', 'fa-bolt', 'fa-heart', 'fa-shield-halved', 'fa-thumbs-up'][$i % 6] }}"></i></div>
                    <h3>{{ $service }}</h3>
                    <p>Dependable {{ strtolower($service) }} from a team that cares about getting it right.</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="sec gallery" id="gallery">
    <div class="wrap">
        <span class="eyebrow">Gallery</span>
        <h2 class="sec-title">A look at our place</h2>
        <p class="sec-sub">Photos of {{ $name }}, straight from the business.</p>
        <div class="grid">
            @foreach(range(0, 5) as $i)
                <div class="tile">@if($tiles->isNotEmpty())<img src="{{ $tiles[$i] }}" alt="" loading="lazy">@else<div class="ph"><i class="fas fa-camera"></i></div>@endif</div>
            @endforeach
        </div>
    </div>
</section>

<section class="sec why">
    <div class="wrap">
        <span class="eyebrow">Why choose us</span>
        <h2 class="sec-title">Reasons customers come back</h2>
        <div class="why-in" style="margin-top:40px">
            @foreach($why as [$icon, $title, $text])
                <div class="why-item"><i class="fas {{ $icon }}"></i><h3>{{ $title }}</h3><p>{{ $text }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<section class="sec reviews">
    <div class="wrap">
        <span class="eyebrow">Reviews</span>
        <h2 class="sec-title">What customers say</h2>
        <div class="cards" style="margin-top:40px">
            @foreach($reviews as [$text, $stars])
                <div class="rev">
                    <span class="chip">Sample</span>
                    <div class="stars">{{ str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) }}</div>
                    <p>“{{ $text }}”</p>
                    <small>— A happy customer</small>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="sec contact" id="contact">
    <div class="wrap contact-in">
        <div>
            <span class="eyebrow">Contact</span>
            <h2 class="sec-title">Visit or get in touch</h2>
            <ul class="info" style="margin-top:34px">
                <li><i class="fas fa-location-dot"></i><div><b>Address</b><span>{{ $address ?: $place }}@if($business->pincode) – {{ $business->pincode }}@endif</span></div></li>
                <li><i class="fas fa-phone"></i><div><b>Phone</b><span>{{ $phone ?: '—' }}</span></div></li>
                @if($business->email)<li><i class="fas fa-envelope"></i><div><b>Email</b><span>{{ $business->email }}</span></div></li>@endif
                @if($hours)<li><i class="fas fa-clock"></i><div><b>Opening hours</b><span>{{ $hours }}</span></div></li>@endif
            </ul>
        </div>
        <div class="form">
            <h3>Send us an enquiry</h3>
            <div class="field">Your name</div>
            <div class="field">Your phone number</div>
            <div class="field tall">How can we help?</div>
            <span class="btn btn-primary">Send enquiry</span>
        </div>
    </div>
</section>

<footer class="foot">
    <div class="wrap foot-in">
        <span>© {{ date('Y') }} <b>{{ $name }}</b>. All rights reserved.</span>
        <span>{{ $place }}</span>
    </div>
</footer>
</body>
</html>
