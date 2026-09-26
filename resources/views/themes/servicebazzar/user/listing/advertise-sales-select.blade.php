@extends($themeView.'layouts.site')

@section('content')
@section('title',$package->name.' — '.$listing->name)

@php
  $sym    = $setting->currency_symbol ?? '₹';
  $images = $package->imageUrls();
  $plans  = $package->planList();
  $tree   = $package->needsLocation() ? $package->positionPriceTree() : [];
@endphp

<div class="sbsel-wrap">
  <div class="container">

    <a href="{{ route('user.listing.advertise', $listing->id) }}" class="sbsel-back"><i class="las la-arrow-left"></i> Back to plans</a>

    <div class="sbsel-card">

      {{-- left: creative --}}
      <div class="sbsel-media">
        @if($images)
          <img id="sbselImg" src="{{ $images[0] }}" alt="{{ $package->name }}">
          @if(count($images) > 1)
            <div class="sbsel-dots">
              @foreach($images as $i => $url)
                <span class="{{ $i === 0 ? 'active' : '' }}" data-src="{{ $url }}"></span>
              @endforeach
            </div>
          @endif
        @endif
      </div>

      {{-- right: what they get + what they choose --}}
      <div class="sbsel-body">
        <h1>{{ $package->name }}</h1>
        <p class="sbsel-for">for <strong>{{ $listing->name }}</strong></p>

        @if($active)
          <div class="sbsel-active"><i class="las la-check-circle"></i> Already active — {{ $active->plan }}, till {{ $active->expires_at->format('d M Y, h:i A') }}. Buying again extends from today.</div>
        @endif

        <h2>Benefits You get</h2>
        <ul class="sbsel-benefits">
          @foreach($package->benefitList() as $benefit)
            <li><i class="las la-check-circle"></i> {{ $benefit }}</li>
          @endforeach
        </ul>

        @foreach($package->highlightList() as $hl)
          <span class="sbsel-chip">{{ $hl }}</span>
        @endforeach

        <form method="get" action="{{ route('user.listing.advertise.sales.checkout', ['id' => $listing->id, 'pkg' => $package->id]) }}" id="sbselForm">

          @if($package->needsLocation())
            <h2>Where should it run?</h2>
            <div class="sbsel-loc">
              <div>
                <label>City</label>
                <select id="sbselCity" class="sbsel-select">
                  @foreach($cities as $cityName)
                    <option value="{{ $cityName }}" @selected($cityName === $listing->city)>{{ $cityName }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label>Area</label>
                <select id="sbselArea" class="sbsel-select"></select>
              </div>
            </div>
            <input type="hidden" name="city" value="">
            <input type="hidden" name="area" value="">

            <h2>Select plan</h2>
            <div class="sbsel-plans" id="sbselPlans"></div>
            <p class="sbsel-noprice" id="sbselNoPrice" style="display:none;">No price is set for this area yet. Please pick another area.</p>
          @else
            <h2>Select plan</h2>
            <div class="sbsel-plans">
              @foreach($plans as $i => $pl)
                <label class="sbsel-plan {{ $i === 0 ? 'is-on' : '' }}">
                  <input type="radio" name="plan" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }}>
                  <span class="sbsel-plan-label">{{ $pl['label'] }}</span>
                  <span class="sbsel-plan-price">{{ $sym }}{{ numberFormat($pl['price']) }}</span>
                </label>
              @endforeach
            </div>
          @endif

          @if($package->needs_ad_details)
            <p class="sbsel-note"><i class="las la-info-circle"></i> After payment you'll add your banner text and photo, and the banner is created for you.</p>
          @endif

          <div class="sbsel-actions">
            <a href="{{ route('user.listing.advertise', $listing->id) }}" class="sbsel-skip">Skip</a>
            <button type="submit" class="sbsel-buy" id="sbselBuy">Continue to Pay <i class="las la-arrow-right"></i></button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<style>
.sbsel-wrap{background:#f4f6fb;padding:28px 0 64px;}
.sbsel-back{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #d7dceb;border-radius:24px;
  padding:8px 16px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;margin-bottom:18px;}
.sbsel-card{display:flex;gap:34px;background:#fff;border:1px solid #e6e9f2;border-radius:18px;padding:26px;flex-wrap:wrap;}
.sbsel-media{flex:0 0 380px;max-width:100%;}
.sbsel-media img{width:100%;border-radius:10px;}
.sbsel-dots{display:flex;justify-content:center;gap:8px;margin-top:14px;}
.sbsel-dots span{width:10px;height:10px;border-radius:50%;background:#d6d6d6;cursor:pointer;}
.sbsel-dots span.active{background:#1a73e8;}
.sbsel-body{flex:1;min-width:290px;}
.sbsel-body h1{font-size:24px;font-weight:800;color:#12203f;margin:0 0 4px;}
.sbsel-for{color:#6b7285;font-size:14px;margin:0 0 16px;}
.sbsel-body h2{font-size:15px;font-weight:800;color:#1a2235;margin:20px 0 12px;}
.sbsel-active{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;border-radius:10px;padding:12px 14px;font-size:13.5px;font-weight:600;}
.sbsel-benefits{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:11px;}
.sbsel-benefits li{display:flex;gap:10px;font-size:14px;color:#37415a;line-height:1.45;}
.sbsel-benefits i{color:#1f9d55;font-size:18px;flex-shrink:0;}
.sbsel-chip{display:inline-block;background:#eef4fb;color:#1c6fb8;border-radius:8px;padding:7px 13px;font-size:13px;font-weight:600;margin:12px 8px 0 0;}
.sbsel-loc{display:flex;gap:12px;flex-wrap:wrap;}
.sbsel-loc>div{flex:1;min-width:170px;}
.sbsel-loc label{display:block;font-size:12.5px;color:#6b7285;margin-bottom:5px;}
.sbsel-select{width:100%;border:1px solid #dfe3ec;border-radius:9px;padding:10px 12px;font-size:14px;color:#1a2235;background:#fff;}
.sbsel-plans{display:flex;gap:10px;flex-wrap:wrap;}
.sbsel-plan{position:relative;min-width:120px;border:1px solid #e6e9f2;background:#f8f9fc;border-radius:12px;padding:13px 16px;cursor:pointer;margin:0;}
.sbsel-plan input{position:absolute;opacity:0;}
.sbsel-plan.is-on{border-color:#1a73e8;background:#eef6fd;}
.sbsel-plan.is-on:after{content:"✓";position:absolute;top:-9px;right:-8px;width:22px;height:22px;border-radius:50%;
  background:#fff;border:1px solid #1a73e8;color:#1a73e8;font-size:12px;line-height:20px;text-align:center;}
.sbsel-plan-label{display:block;font-size:13px;color:#6b7285;margin-bottom:5px;}
.sbsel-plan.is-on .sbsel-plan-label{color:#1a73e8;}
.sbsel-plan-price{display:block;font-size:20px;font-weight:800;color:#12203f;}
.sbsel-noprice{color:#b4461f;font-size:13.5px;margin:10px 0 0;}
.sbsel-note{display:flex;gap:8px;align-items:flex-start;background:#fff8e8;border:1px solid #f2dca6;color:#8a6412;
  border-radius:10px;padding:11px 13px;font-size:13px;margin:18px 0 0;}
.sbsel-actions{display:flex;gap:14px;margin-top:24px;flex-wrap:wrap;}
.sbsel-skip{flex:0 0 150px;text-align:center;border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:10px;
  padding:13px;font-weight:700;text-decoration:none;}
.sbsel-buy{flex:1;min-width:190px;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#1a73e8;
  color:#fff;border:0;border-radius:10px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;}
.sbsel-buy:hover{background:#1662c4;}
.sbsel-buy:disabled{opacity:.5;cursor:not-allowed;}
@media (max-width:760px){.sbsel-media{flex-basis:100%;}}
</style>

<script>
(function(){
  var sym = '{{ $sym }}';

  // image slides
  document.querySelectorAll('.sbsel-dots span').forEach(function(dot){
    dot.addEventListener('click', function(){
      document.getElementById('sbselImg').src = dot.getAttribute('data-src');
      document.querySelectorAll('.sbsel-dots span').forEach(function(d){ d.classList.remove('active'); });
      dot.classList.add('active');
    });
  });

  // plan choice (static plans)
  document.querySelectorAll('.sbsel-plans').forEach(function(wrap){
    wrap.addEventListener('click', function(e){
      var label = e.target.closest('.sbsel-plan');
      if (!label) return;
      wrap.querySelectorAll('.sbsel-plan').forEach(function(l){ l.classList.remove('is-on'); });
      label.classList.add('is-on');
    });
  });

@if($package->needsLocation())
  var tree = @json($tree);              // admin's city/area overrides
  var base = @json($plans);             // the package's own plans
  var areaUrl = '{{ route('user.listing.advertise.areas') }}';
  var citySel = document.getElementById('sbselCity');
  var areaSel = document.getElementById('sbselArea');
  var plans = document.getElementById('sbselPlans');
  var noPrice = document.getElementById('sbselNoPrice');
  var buy = document.getElementById('sbselBuy');
  var form = document.getElementById('sbselForm');

  function setAreas(list){
    var seen = {}, areas = [''];
    (list || []).forEach(function(a){ if (a && !seen[a]) { seen[a] = 1; areas.push(a); } });
    Object.keys(tree[citySel.value] || {}).forEach(function(a){ if (a && !seen[a]) { seen[a] = 1; areas.push(a); } });

    areaSel.innerHTML = '';
    areas.forEach(function(a){
      var o = document.createElement('option');
      o.value = a; o.textContent = a === '' ? 'Whole city' : a;
      areaSel.appendChild(o);
    });
    drawPlans();
  }

  function fillAreas(){
    areaSel.innerHTML = '<option value="">Loading…</option>';
    fetch(areaUrl + '?city=' + encodeURIComponent(citySel.value), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(setAreas)
      .catch(function(){ setAreas([]); });
  }

  function drawPlans(){
    form.querySelector('input[name="city"]').value = citySel.value;
    form.querySelector('input[name="area"]').value = areaSel.value;

    var cityTree = tree[citySel.value] || {};
    var areaPrices = cityTree[areaSel.value] || {};
    var cityPrices = cityTree[''] || {};

    // The package's own plans, unless this area (or this city) is priced apart.
    var tiers = [];
    base.forEach(function(p){
      var price = areaPrices[p.label] != null ? areaPrices[p.label]
                : (cityPrices[p.label] != null ? cityPrices[p.label] : p.price);
      tiers.push({label: p.label, price: price});
    });
    // Tiers that exist only as a priced row (e.g. Top 1/2/3).
    Object.keys(areaPrices).concat(Object.keys(cityPrices)).forEach(function(t){
      if (!tiers.some(function(x){ return x.label === t; })) {
        tiers.push({label: t, price: areaPrices[t] != null ? areaPrices[t] : cityPrices[t]});
      }
    });

    plans.innerHTML = '';
    tiers.forEach(function(t, i){
      var label = document.createElement('label');
      label.className = 'sbsel-plan' + (i === 0 ? ' is-on' : '');
      label.innerHTML = '<input type="radio" name="tier"' + (i === 0 ? ' checked' : '') + '>'
        + '<span class="sbsel-plan-label">' + t.label + '</span>'
        + '<span class="sbsel-plan-price">' + sym + Number(t.price).toLocaleString('en-IN') + '</span>';
      label.querySelector('input').value = t.label;
      plans.appendChild(label);
    });

    noPrice.style.display = tiers.length ? 'none' : 'block';
    buy.disabled = tiers.length === 0;
  }

  citySel.addEventListener('change', fillAreas);
  areaSel.addEventListener('change', drawPlans);
  fillAreas();
@endif
})();
</script>

@endsection
