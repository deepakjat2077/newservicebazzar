<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Position &amp; Budget — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('sales_genio.partials.campaign_styles')
<style>
  .pb-grid{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;}
  .pb-main{flex:1;min-width:320px;}
  .pb-side{flex:0 0 300px;max-width:100%;position:sticky;top:130px;}
  .pb-block{margin-bottom:22px;}
  .pb-block h2{font-size:13px;font-weight:700;color:#8a92a6;text-transform:uppercase;letter-spacing:.4px;margin:0 0 10px;}

  .pb-pos{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;}
  .pb-card{position:relative;border:1.5px solid #e6e9f2;border-radius:14px;padding:16px;cursor:pointer;margin:0;
    transition:.15s;background:#fff;}
  .pb-card:hover{border-color:#b9d3f5;}
  .pb-card input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .pb-card.is-on{border-color:#1a73e8;background:#f3f8fe;box-shadow:0 4px 14px rgba(26,115,232,.12);}
  .pb-rank{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:9px;
    background:#eef2f7;color:#5a6478;font-weight:800;font-size:14px;margin-bottom:10px;}
  .pb-card.is-on .pb-rank{background:#1a73e8;color:#fff;}
  .pb-card b{display:block;font-size:15px;color:#12203f;}
  .pb-card .rate{display:block;margin-top:6px;font-size:19px;font-weight:800;color:#12203f;}
  .pb-card .per{display:block;font-size:12px;color:#8a92a6;margin-top:2px;}

  .pb-chips{display:flex;gap:10px;flex-wrap:wrap;}
  .pb-chip{position:relative;border:1.5px solid #e6e9f2;border-radius:24px;padding:9px 18px;font-size:13.5px;
    font-weight:700;color:#5a6478;cursor:pointer;margin:0;background:#fff;}
  .pb-chip input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .pb-chip.is-on{border-color:#1a73e8;background:#1a73e8;color:#fff;}

  .pb-arealist{display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:12px;max-height:430px;overflow-y:auto;padding-right:4px;}
  .pb-areablock{border:1px solid #e6e9f2;border-radius:12px;background:#fff;overflow:hidden;}
  .pb-areahead{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:11px 14px;background:#fbfcfe;
    border-bottom:1px solid #eef0f6;font-size:13px;}
  .pb-areahead .nm{font-weight:700;color:#12203f;}
  .pb-areahead .nm b{color:#8a92a6;font-weight:600;margin-left:6px;}
  .pb-areahead .sum{font-size:12px;color:#8a92a6;font-weight:600;}
  .pb-areahead .sum.is-set{color:#1f7a45;}
  .pb-slots{padding:6px 8px;}
  .pb-slot{position:relative;display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;cursor:pointer;
    font-size:13px;color:#37415a;margin:0;}
  .pb-slot input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .pb-slot .dot{width:16px;height:16px;border-radius:50%;border:1.5px solid #cfd6e4;flex-shrink:0;}
  .pb-slot.is-on{background:#f2f7fe;}
  .pb-slot.is-on .dot{border-color:#1a73e8;border-width:5px;}
  .pb-slot .nm{flex:1;font-weight:600;}
  .pb-slot .amt{font-weight:700;color:#12203f;}
  .pb-slot .tag{font-size:11px;font-weight:700;color:#b4261f;background:#fdeceb;border-radius:20px;padding:3px 9px;}
  .pb-slot.is-busy{opacity:.65;cursor:not-allowed;}
  .pb-slot.is-busy .nm{color:#8a92a6;}
  .pb-slot.pb-none .nm{color:#8a92a6;font-weight:600;}
  .pb-breakdown{padding:10px 18px;background:#f2f7fe;color:#1a73e8;font-size:12.5px;font-weight:700;text-align:center;}
  .pb-head{display:flex;justify-content:space-between;align-items:center;gap:12px;}
  .pb-addarea{font-size:12.5px;font-weight:700;color:#1a73e8;text-decoration:none;margin-bottom:10px;}
  .pb-areas{border:1px solid #eef0f6;border-radius:12px;padding:10px;max-height:210px;overflow-y:auto;background:#fbfcfe;}
  .pb-area{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#fff;border:1px solid #e6e9f2;
    border-radius:10px;padding:9px 12px;font-size:12.5px;color:#37415a;margin-bottom:7px;}
  .pb-area:last-child{margin-bottom:0;}
  .pb-area b{color:#8a92a6;font-weight:600;margin-left:6px;}
  .pb-area .av{font-size:11.5px;font-weight:700;border-radius:20px;padding:3px 10px;white-space:nowrap;}
  .pb-area .av.free{background:#eafaf1;color:#1f7a45;}
  .pb-area .av.busy{background:#fdeceb;color:#b4261f;}
  .pb-area.is-busy{border-color:#f3cfcc;background:#fffafa;}
  .pb-availnote{display:flex;gap:7px;color:#8a92a6;font-size:12px;margin:9px 0 0;line-height:1.5;}
  .pb-cats{width:100%;border-collapse:collapse;}
  .pb-cats th{background:#fafbfc;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.3px;text-align:left;padding:9px 12px;}
  .pb-cats th:last-child,.pb-cats td:last-child{text-align:right;}
  .pb-cats td{padding:11px 12px;border-top:1px solid #f1f3f8;font-size:13px;color:#37415a;}
  .pb-cats i{color:#1f9d55;margin-right:7px;}
  .pb-share{color:#8a92a6;}
  .pb-catbudget{font-weight:700;color:#12203f;}
  .pb-main-tag{background:#eef4fb;color:#1c6fb8;border-radius:12px;padding:2px 8px;font-size:10.5px;font-weight:700;margin-left:7px;}

  .pb-summary{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;}
  .pb-summary h3{margin:0;padding:15px 18px;border-bottom:1px solid #eef0f6;font-size:14.5px;font-weight:800;color:#12203f;}
  .pb-rows{padding:6px 18px;}
  .pb-row{display:flex;justify-content:space-between;gap:10px;padding:10px 0;font-size:13.5px;color:#5a6478;
    border-bottom:1px dashed #eef0f6;}
  .pb-row:last-child{border-bottom:0;}
  .pb-row span:last-child{color:#12203f;font-weight:700;}
  .pb-calc{background:#f7f9fc;padding:12px 18px;font-size:12.5px;color:#6b7285;text-align:center;}
  .pb-busy{background:#fdeceb;color:#b4261f;padding:10px 18px;font-size:12px;font-weight:700;text-align:center;}
  .pb-total{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;background:#12203f;color:#fff;}
  .pb-total span{font-size:13.5px;opacity:.85;}
  .pb-total strong{font-size:24px;}
  .pb-go{display:block;width:100%;background:#1a73e8;color:#fff;border:0;padding:14px;font-size:15px;font-weight:700;
    cursor:pointer;font-family:'Poppins',sans-serif;}
  .pb-go:hover{background:#1662c4;}
  .pb-edit{display:block;text-align:center;padding:12px;font-size:13.5px;font-weight:600;color:#1a73e8;text-decoration:none;}
  @media (max-width:900px){.pb-side{position:static;flex-basis:100%;}}
</style>
<style>
  /* This flow needs the room: everything on one screen, full width. */
  .cmp-wrap{max-width:1560px;padding-left:26px;padding-right:26px;}
  .cmp-panel{padding:22px 26px;}
  .pb-side{flex:0 0 330px;top:118px;}
  .pb-block{margin-bottom:18px;}
  .pb-areas{max-height:240px;}
  @media (min-width:1200px){
    .pb-main{min-width:520px;}
    .pb-pos{grid-template-columns:repeat(auto-fit,minmax(170px,1fr));}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

@php
  $firstTier = array_key_first($rates);
  $defaultDays = array_key_last($tenures);
  $areaCount = count($groups);
@endphp

<div class="cmp-wrap">
  <div class="cmp-top">
    <a href="{{ route('sales_genio.business.campaign.areas', ['id' => $business->id, 'pkg' => $package->id]) }}" class="cmp-back"><i class="fas fa-arrow-left"></i> Change areas</a>
    <div class="cmp-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
  </div>

  @if(session('error'))<div class="cmp-flash warn"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif

  <div class="cmp-layout">
    @include('sales_genio.partials.campaign_sidebar', ['selected' => $package])

    <div class="cmp-panel">
      <h1 style="font-size:20px;font-weight:800;margin:0 0 20px;color:#12203f;">Position &amp; Budget</h1>

      <form method="post" action="{{ route('sales_genio.business.campaign.position.save', ['id' => $business->id, 'pkg' => $package->id]) }}">
        @csrf

        <div class="pb-grid">
          <div class="pb-main">

            <div class="pb-block">
              <h2>Tenure</h2>
              <div class="pb-chips" id="pbDays">
                @foreach($tenures as $tenureDays => $tenureLabel)
                  <label class="pb-chip {{ $tenureDays == $defaultDays ? 'is-on' : '' }}">
                    <input type="radio" name="days" value="{{ $tenureDays }}" {{ $tenureDays == $defaultDays ? 'checked' : '' }}>
                    {{ $tenureLabel }}
                  </label>
                @endforeach
              </div>
            </div>

            <div class="pb-block">
              <div class="pb-head">
                <h2>{{ optional($business->category)->name ?? 'Category' }} · {{ $areaCount }} area{{ $areaCount == 1 ? '' : 's' }}</h2>
                <a class="pb-addarea" href="{{ route('sales_genio.business.campaign.areas', ['id' => $business->id, 'pkg' => $package->id]) }}"><i class="fas fa-plus"></i> Add area</a>
              </div>

              <div class="pb-arealist" id="pbAreas">
                @foreach($groups as $group)
                  @php $held = $taken[$group['pincode']] ?? []; @endphp
                  <div class="pb-areablock">
                    <div class="pb-areahead">
                      <span class="nm">{{ $group['label'] }} <b>{{ $group['pincode'] }}</b></span>
                      <span class="sum" data-sum-for="{{ $group['pincode'] }}">Not selected</span>
                    </div>

                    <div class="pb-slots">
                      @foreach($rates as $tier => $rate)
                        @php $busy = $held[$tier] ?? null; @endphp
                        <label class="pb-slot {{ $busy ? 'is-busy' : '' }}"
                               title="{{ $busy ? $busy['business'].' holds this till '.$busy['till'] : '' }}">
                          <input type="radio" name="position[{{ $group['pincode'] }}]" value="{{ $tier }}"
                                 data-rate="{{ $rate }}" data-pincode="{{ $group['pincode'] }}" {{ $busy ? 'disabled' : '' }}>
                          <span class="dot"></span>
                          <span class="nm">{{ $tier }}</span>
                          @if($busy)
                            <span class="tag">Taken till {{ $busy['till'] }}</span>
                          @else
                            <span class="amt">₹{{ numberFormat($rate) }}</span>
                          @endif
                        </label>
                      @endforeach

                      <label class="pb-slot pb-none">
                        <input type="radio" name="position[{{ $group['pincode'] }}]" value="" data-rate="0" data-pincode="{{ $group['pincode'] }}" checked>
                        <span class="dot"></span>
                        <span class="nm">Skip this area</span>
                        <span class="amt">₹0</span>
                      </label>
                    </div>
                  </div>
                @endforeach
              </div>

              <p class="pb-availnote"><i class="fas fa-circle-info"></i> One business per position per area in this category. Positions already taken are locked — pick a free one, or skip that area.</p>
            </div>

          </div>

          <div class="pb-side">
            <div class="pb-summary">
              <h3>Campaign summary</h3>
              <div class="pb-rows" id="sRows">
                <div class="pb-row"><span>Areas selected</span><span id="sAreas">0 of {{ $areaCount }}</span></div>
                <div class="pb-row"><span>Tenure</span><span id="sDays">{{ $tenures[$defaultDays] }}</span></div>
                <div class="pb-row"><span>Category</span><span>{{ optional($business->category)->name ?? '—' }}</span></div>
              </div>
              <div class="pb-breakdown" id="sBreak"></div>
              <div class="pb-calc" id="sCalc">Pick a position in an area to start</div>
              <div class="pb-total">
                <span>Total</span>
                <strong id="sTotal">₹0</strong>
              </div>
              <button type="submit" class="pb-go">Add to pitch list</button>
              <a class="pb-edit" href="{{ route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]) }}">Cancel</a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var fullDays = {{ $fullDays }};
  var areaCount = {{ $areaCount }};
  var money = function (n) { return '₹' + Math.round(n).toLocaleString('en-IN'); };

  function days() {
    var d = document.querySelector('input[name="days"]:checked');
    return { value: Number(d.value), label: d.parentNode.textContent.trim() };
  }

  // Each area is priced on its own position, pro-rata for the tenure.
  function update() {
    var tenure = days();
    var share = tenure.value / fullDays;
    var total = 0;
    var picked = 0;
    var counts = {};

    document.querySelectorAll('#pbAreas input[type="radio"]').forEach(function (input) {
      var label = input.closest('.pb-slot');
      label.classList.toggle('is-on', input.checked);

      var amount = label.querySelector('.amt');
      if (amount && !label.classList.contains('pb-none')) {
        amount.textContent = money(Math.round(Number(input.dataset.rate || 0) * share));
      }

      if (!input.checked) return;

      var line = document.querySelector('[data-sum-for="' + input.dataset.pincode + '"]');
      if (input.value) {
        // Rounded per area, so the areas always add up to the total.
        var price = Math.round(Number(input.dataset.rate || 0) * share);
        total += price;
        picked++;
        counts[input.value] = (counts[input.value] || 0) + 1;
        line.textContent = input.value + ' · ' + money(price);
        line.classList.add('is-set');
      } else {
        line.textContent = 'Not selected';
        line.classList.remove('is-set');
      }
    });

    document.getElementById('sAreas').textContent = picked + ' of ' + areaCount;
    document.getElementById('sDays').textContent = tenure.label;
    document.getElementById('sTotal').textContent = money(total);

    var breakdown = Object.keys(counts).map(function (tier) {
      return tier + ' × ' + counts[tier];
    }).join('   ·   ');
    document.getElementById('sBreak').textContent = breakdown;
    document.getElementById('sBreak').style.display = breakdown ? 'block' : 'none';
    document.getElementById('sCalc').textContent = picked
      ? picked + ' area' + (picked === 1 ? '' : 's') + ' × ' + tenure.label
      : 'Pick a position in an area to start';

    document.querySelectorAll('.pb-chip').forEach(function (c) {
      c.classList.toggle('is-on', c.contains(document.querySelector('input[name="days"]:checked')));
    });
  }

  document.getElementById('pbAreas').addEventListener('change', update);
  document.getElementById('pbDays').addEventListener('change', update);
  update();
})();
</script>

</body>
</html>
