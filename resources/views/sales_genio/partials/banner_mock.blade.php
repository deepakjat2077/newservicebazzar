{{-- Picture of where a banner shows, carrying THIS business's name and category.
     $kind: 'mobile' (phone) or 'web' (desktop page). --}}
@php
  $bmName = $business->name;
  $bmCat  = optional($business->category)->name ?: 'Business';
  $bmCity = $business->city ?: 'your city';
@endphp
<div class="bm bm-{{ $kind }}" aria-label="Preview of the banner on Service Bazzar">
  @if($kind === 'top')
    @php
      // A small thumbnail of the first photo, inlined: uploads sit on one server's disk only.
      // Passed in when the same picture is drawn several times (Top 1/2/3), so it is made only once.
      $bmThumb = $thumb ?? null;
      $bmRank = $rank ?? 1;
      foreach ($bmThumb ? [] : (array) $business->photos as $bmP) {
          $bmFile = public_path('assets/images/listing/photos/' . $bmP);
          if (is_file($bmFile) && ($bmSrc = @imagecreatefromwebp($bmFile) ?: @imagecreatefromstring((string) file_get_contents($bmFile)))) {
              $bmT = imagescale($bmSrc, 160);
              ob_start(); imagewebp($bmT, null, 70); $bmThumb = 'data:image/webp;base64,' . base64_encode(ob_get_clean());
              imagedestroy($bmSrc); imagedestroy($bmT);
              break;
          }
      }
      $bmWhere = trim(($business->area ? $business->area . ', ' : '') . ($business->city ?? ''), ', ');
    @endphp
    <div class="bm-phone"><div class="bm-screen">
      <div class="bm-notch"></div>
      <div class="bm-status"><span>2:51 PM</span><span>● ● ▮</span></div>
      <div class="bm-search">← &nbsp;{{ \Illuminate\Support\Str::limit($bmCat, 26) }}</div>
      <div class="bm-chips"><span>Sort By</span><span>Ratings</span><span class="d">All</span></div>
      @for($bmI = 1; $bmI <= 3; $bmI++)
        @if($bmI === $bmRank)
      <div class="bm-card bm-me">
        <span class="bm-rank">#{{ $bmRank }}</span>
        <div class="bm-row">
          @if($bmThumb)<img src="{{ $bmThumb }}" alt="">@else<i></i>@endif
          <div class="bm-meta">
            <b>{{ $bmName }}</b>
            <small>{{ \Illuminate\Support\Str::limit($bmCat, 28) }}</small>
            @if($bmWhere)<small><i class="fas fa-location-dot"></i> {{ \Illuminate\Support\Str::limit($bmWhere, 30) }}</small>@endif
          </div>
        </div>
        <div class="bm-btns"><u class="t"><i class="fas fa-phone"></i>&nbsp;Call Now</u><u class="b t">Get Best Price</u></div>
      </div>
        @else
        <div class="bm-card"><div class="bm-row"><i></i><div><s></s><s class="s"></s></div></div><div class="bm-btns"><u></u><u class="b"></u></div></div>
        @endif
      @endfor
    </div></div>
  @elseif($kind === 'mobile')
    <div class="bm-phone"><div class="bm-screen">
      <div class="bm-notch"></div>
      <div class="bm-status"><span>2:31 PM</span><span>● ● ▮</span></div>
      <div class="bm-search">← &nbsp;{{ \Illuminate\Support\Str::limit($bmCat, 26) }}</div>
      <div class="bm-ban">
        <div class="bm-ph"></div>
        <div class="bm-tx"><b>{{ $bmName }}</b><em>{{ \Illuminate\Support\Str::limit($bmCat, 30) }}</em></div>
        <span class="bm-go">Call Now</span>
      </div>
      <div class="bm-h">Popular {{ \Illuminate\Support\Str::limit($bmCat, 22) }} in {{ \Illuminate\Support\Str::limit($bmCity, 14) }}</div>
      <div class="bm-chips"><span>Sort By</span><span>Ratings</span><span class="d">All</span></div>
      @for($i = 0; $i < 3; $i++)
        <div class="bm-card"><div class="bm-row"><i></i><div><s></s><s class="s"></s></div></div><div class="bm-btns"><u></u><u class="b"></u></div></div>
      @endfor
    </div></div>
  @else
    <div class="bm-mon"><div class="bm-screen">
      <div class="bm-nav"><img src="{{ asset('assets/images/logo/logo.png') }}" alt=""><span class="in">{{ \Illuminate\Support\Str::limit($bmCity, 12) }}</span><span class="in w">{{ \Illuminate\Support\Str::limit($bmCat, 28) }}</span></div>
      <div class="bm-ban">
        <div class="bm-ph"></div>
        <div class="bm-tx"><b>{{ $bmName }}</b><em>{{ \Illuminate\Support\Str::limit($bmCat, 40) }}</em></div>
        <span class="bm-go">Click Here ›</span>
      </div>
      <div class="bm-h">Popular {{ \Illuminate\Support\Str::limit($bmCat, 30) }} in {{ \Illuminate\Support\Str::limit($bmCity, 16) }}</div>
      <div class="bm-chips"><span>Sort By</span><span>Ratings</span><span>Verified</span><span class="d">All</span></div>
      <div class="bm-cards">
        @for($i = 0; $i < 2; $i++)<div class="bm-card"><div class="bm-row"><i></i><div><s></s><s class="s"></s></div></div></div>@endfor
      </div>
    </div></div>
    <div class="bm-stand"></div>
  @endif
</div>
<style>
  .bm{width:100%;max-width:300px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;color:#111;}
  .bm *{box-sizing:border-box;margin:0;}
  .bm-phone{background:#1b1b1b;border-radius:34px;padding:8px;}
  .bm-mon{background:#1f1f1f;border-radius:14px;padding:9px;}
  .bm-screen{background:#fff;border-radius:26px;overflow:hidden;position:relative;}
  .bm-mon .bm-screen{border-radius:5px;}
  .bm-mobile{padding:14px 0;}
  /* The phone is drawn at full size and shrunk, so it sits in the panel like the combo's phone. */
  .bm-top{padding:14px 0;}
  .bm-top .bm-phone{width:290px;margin:0 auto;zoom:.66;}
  .bm-top .bm-screen{height:500px;}
  .bm-me{position:relative;border:2px solid #f5b800;box-shadow:0 4px 14px rgba(245,184,0,.25);}
  .bm-rank{position:absolute;top:-1px;right:-1px;background:#12203f;color:#fff;font-size:9px;font-weight:bold;padding:3px 7px;border-radius:0 5px 0 6px;}
  .bm-row img{width:58px;height:58px;border-radius:6px;object-fit:cover;flex:none;}
  .bm-me .bm-row > i{width:58px;height:58px;}
  .bm-meta{min-width:0;padding-right:16px;}
  .bm-meta b{display:block;font-size:11px;line-height:1.2;color:#111;}
  .bm-meta small{display:block;font-size:8px;color:#6b7285;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .bm-btns u.t{display:flex;align-items:center;justify-content:center;color:#fff;font-size:8px;font-weight:bold;text-decoration:none;}
  .bm-mobile .bm-phone{width:290px;margin:0 auto;zoom:.66;}
  .bm-mobile .bm-screen{height:500px;}
  .bm-notch{position:absolute;left:50%;top:6px;width:64px;height:17px;margin-left:-32px;background:#111;border-radius:10px;}
  .bm-status{display:flex;justify-content:space-between;padding:7px 20px 0;height:30px;font-size:9px;font-weight:bold;}
  .bm-search{margin:2px 10px 8px;border:1px solid #bbb;border-radius:6px;padding:7px 8px;font-size:9.5px;color:#333;white-space:nowrap;overflow:hidden;}
  .bm-nav{display:flex;align-items:center;gap:5px;padding:6px 8px;}
  .bm-nav img{height:15px;width:auto;}
  .bm-nav .in{border:1px solid #bbb;border-radius:4px;background:#f4f4f4;font-size:6.5px;padding:4px 5px;flex:0 0 46px;white-space:nowrap;overflow:hidden;}
  .bm-nav .in.w{flex:1;background:#fff;}
  .bm-ban{display:flex;align-items:center;gap:8px;background:linear-gradient(90deg,#f5b800,#ffd54a 60%,#f5b800);border-top:2px solid #12203f;border-bottom:2px solid #12203f;height:58px;padding-right:8px;position:relative;}
  .bm-ph{align-self:stretch;width:50px;background:linear-gradient(135deg,#8c2a18,#d8a07a 50%,#6b1d12);flex:none;}
  .bm-tx{flex:1;min-width:0;line-height:1.15;}
  .bm-tx b{display:block;font-size:10.5px;color:#12203f;font-weight:900;text-transform:uppercase;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
  .bm-tx em{display:block;font-style:normal;font-size:7.5px;color:#c4161c;font-weight:bold;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .bm-go{flex:none;background:#c4161c;color:#fff;font-size:7px;font-weight:bold;padding:5px 7px;border-radius:10px;}
  .bm-h{font-size:11px;font-weight:bold;padding:10px 10px 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .bm-chips{display:flex;gap:5px;padding:0 10px 9px;}
  .bm-chips span{border:1px solid #ccc;border-radius:11px;font-size:7.5px;padding:4px 8px;}
  .bm-chips span.d{background:#111;color:#fff;border-color:#111;}
  .bm-card{margin:0 10px 9px;border:1px solid #e3e3e3;border-radius:6px;padding:8px;}
  .bm-row{display:flex;gap:8px;}.bm-row > i{width:44px;height:44px;border-radius:6px;background:#e7ebf3;flex:none;}
  .bm-row s{display:block;height:8px;width:90px;background:#c9d1e2;border-radius:3px;margin:4px 0;text-decoration:none;}
  .bm-row s.s{width:56px;background:#e1e6f0;}
  .bm-btns{display:flex;gap:7px;margin-top:8px;}.bm-btns u{flex:1;height:18px;border-radius:4px;background:#1f8f3a;}.bm-btns u.b{background:#1a73e8;}
  .bm-cards{display:flex;gap:8px;padding:0 8px;}.bm-cards .bm-card{flex:1;margin:0;}
  .bm-mon .bm-screen{height:190px;}
  .bm-mon .bm-h{padding:7px 8px 5px;font-size:9px;}.bm-mon .bm-chips{padding:0 8px 6px;}
  .bm-stand{width:34%;height:22px;margin:0 auto;background:#2b2b2b;clip-path:polygon(18% 0,82% 0,100% 100%,0 100%);}
  .bm-stand:after{content:"";display:block;}
</style>
