<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cart — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input,select{font-family:'Poppins',sans-serif;}
  body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .ct-wrap{max-width:1180px;margin:0 auto;padding:22px 20px 60px;}
  .ct-top{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
  .ct-back{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #d7dceb;border-radius:22px;
    padding:8px 16px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;}
  .ct-biz{text-align:right;font-size:13px;color:#6b7285;}
  .ct-biz strong{display:block;color:#12203f;font-size:16px;}
  .ct-flash{border-radius:11px;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:14px;
    background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;}
  .ct-flash.warn{background:#fff7e8;border-color:#f2dca6;color:#8a6412;}

  .ct-grid{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;}
  .ct-main{flex:1;min-width:330px;}
  .ct-side{flex:0 0 320px;max-width:100%;position:sticky;top:22px;}

  .ct-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
  .ct-head h1{font-size:20px;font-weight:800;margin:0;color:#12203f;}
  .ct-clear{background:none;border:0;color:#b4261f;font-size:13px;font-weight:700;cursor:pointer;}

  .ct-item{display:flex;gap:16px;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:14px;
    margin-bottom:12px;align-items:center;}
  .ct-item img{width:92px;height:70px;object-fit:contain;background:#f7f9fc;border:1px solid #eef0f6;border-radius:9px;}
  .ct-item .info{flex:1;min-width:0;}
  .ct-item h3{margin:0 0 4px;font-size:15px;font-weight:800;color:#12203f;}
  .ct-item p{margin:0;font-size:12.5px;color:#7a8296;line-height:1.5;}
  .ct-item .price{text-align:right;white-space:nowrap;}
  .ct-item .price b{display:block;font-size:17px;color:#12203f;}
  .ct-free{background:#eafaf1;color:#1f7a45;border-radius:20px;padding:3px 11px;font-size:11.5px;font-weight:700;}
  .ct-remove{background:none;border:0;color:#9aa2b4;font-size:12.5px;font-weight:600;cursor:pointer;margin-top:6px;}
  .ct-remove:hover{color:#b4261f;}
  .ct-edit{display:inline-block;margin-top:6px;font-size:12px;font-weight:700;color:#1a73e8;text-decoration:none;}

  .ct-live{background:#eafaf1;color:#1f7a45;border-radius:20px;padding:2px 9px;font-size:11px;font-weight:700;
    margin-left:8px;vertical-align:middle;}
  .ct-empty{background:#fff;border:1px dashed #d7dceb;border-radius:14px;padding:46px 20px;text-align:center;color:#7a8296;}
  .ct-empty i{font-size:42px;color:#cbd2e0;display:block;margin-bottom:12px;}

  .ct-more{margin-top:26px;}
  .ct-more h2{font-size:14px;font-weight:800;color:#12203f;margin:0 0 12px;}
  .ct-rail{display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;}
  .ct-sug{position:relative;flex:0 0 172px;background:#fff;border:1px solid #e6e9f2;border-radius:13px;padding:12px;}
  .ct-sug img{width:100%;height:78px;object-fit:contain;background:#f7f9fc;border-radius:8px;margin-bottom:9px;}
  .ct-sug b{display:block;font-size:12.5px;color:#12203f;line-height:1.35;min-height:34px;}
  .ct-sug span{display:block;margin-top:6px;font-size:14px;font-weight:800;color:#1a73e8;}
  .ct-add{position:absolute;top:8px;right:8px;width:26px;height:26px;border-radius:50%;background:#1a73e8;color:#fff;
    border:0;font-size:14px;cursor:pointer;line-height:1;}
  .ct-add:hover{background:#1662c4;}

  .ct-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;}
  .ct-card h3{margin:0;padding:15px 18px;border-bottom:1px solid #eef0f6;font-size:14.5px;font-weight:800;color:#12203f;}
  .ct-line{display:flex;justify-content:space-between;gap:10px;padding:11px 18px;font-size:13.5px;color:#5a6478;}
  .ct-top .ct-biz{margin-left:auto;}
  .ct-line b{color:#12203f;}
  .ct-line small{display:block;color:#8a92a6;font-size:11.5px;margin-top:2px;}
  .ct-disc span,.ct-disc b{color:#1f7a45;}
  .ct-disc small a{color:#1a73e8;text-decoration:none;}
  .ct-inline{display:inline;margin:0;}
  .ct-linkbtn{background:none;border:0;padding:0;color:#c62828;font:inherit;font-size:11.5px;cursor:pointer;}
  .ct-apply{display:flex;align-items:center;gap:10px;width:calc(100% - 36px);margin:10px 18px 14px;padding:11px 14px;background:#f2f9f5;border:1.5px dashed #6fbf8c;
    border-radius:10px;color:#1f7a45;font:600 14px 'Poppins',sans-serif;cursor:pointer;text-align:left;}
  .ct-apply em{margin-left:auto;font-style:normal;font-weight:500;font-size:12px;color:#5a8d70;}
  .ct-apply:hover{background:#e8f5ee;}

  .dc-modal{position:fixed;inset:0;z-index:300;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;padding:18px;}
  .dc-modal.open{display:flex;}
  .dc-box{background:#fff;border-radius:16px;width:100%;max-width:440px;max-height:86vh;display:flex;flex-direction:column;overflow:hidden;}
  .dc-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #eef0f6;}
  .dc-head b{font-size:17px;color:#12203f;}
  .dc-x{border:0;background:#f1f3f8;width:32px;height:32px;border-radius:50%;font-size:19px;color:#62708c;cursor:pointer;}
  .dc-list{overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:12px;}
  .dc-item{border:1.5px solid #e3e7ef;border-radius:12px;padding:14px 16px;display:flex;gap:14px;align-items:center;justify-content:space-between;}
  .dc-item.is-on{border-color:#1f9d55;background:#f2f9f5;}
  .dc-item b{display:block;font-size:15px;color:#12203f;}
  .dc-item .off{font-size:15px;font-weight:700;color:#1f7a45;}
  .dc-item small{display:block;color:#7a8296;font-size:12px;margin-top:3px;line-height:1.4;}
  .dc-item form{margin:0;}
  .dc-item button{border:0;border-radius:8px;padding:9px 16px;background:#1a73e8;color:#fff;font:600 13px 'Poppins',sans-serif;cursor:pointer;white-space:nowrap;}
  .dc-item.is-on button{background:#1f9d55;cursor:default;}
  .ct-total{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;background:#12203f;color:#fff;}
  .ct-total span{font-size:13.5px;opacity:.85;}
  .ct-total strong{font-size:24px;}
  .ct-pay{display:block;width:100%;background:#1f9d55;color:#fff;border:0;padding:14px;font-size:15px;font-weight:700;
    cursor:pointer;text-align:center;text-decoration:none;}
  .ct-pay:hover{background:#188746;color:#fff;}
  .ct-note{padding:12px 18px;font-size:12px;color:#8a92a6;text-align:center;line-height:1.5;}
  @media (max-width:900px){.ct-side{position:static;flex-basis:100%;}}
  .ct-empty strong{display:block;margin-bottom:4px;color:#12203f;font-size:16px;}
  .ct-empty span{display:block;}

  .ct-mhead,.ct-mfoot{display:none;}
  @media (max-width:760px){
    .sg-nav{display:none;}
    body{background:#fff;padding-bottom:92px;}
    .ct-mhead{display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:50;background:#fff;
      padding:14px 16px;border-bottom:1px solid #eef0f4;}
    .ct-mhead .b{font-size:20px;color:#1a1a2e;text-decoration:none;width:70px;}
    .ct-mhead .t{flex:1;text-align:center;font-size:17px;font-weight:500;color:#1a1a2e;}
    .ct-mhead form{width:70px;text-align:right;margin:0;}
    .ct-mhead .x{background:none;border:0;color:#1a73e8;font-size:15px;font-family:inherit;cursor:pointer;padding:0;}
    .ct-mhead .x-ph{width:70px;}
    .ct-top .ct-back,.ct-head .ct-clear,.ct-side .ct-pay{display:none;}
    .ct-wrap.is-empty .ct-head,.ct-wrap.is-empty .ct-side,.ct-wrap.is-empty .ct-top{display:none;}
    .ct-wrap.is-empty .ct-empty{border:0;background:none;padding:22vh 20px 20px;font-size:15px;color:#1a1a2e;}
    .ct-wrap.is-empty .ct-empty i{font-size:56px;color:#1a73e8;margin-bottom:22px;}
    .ct-wrap.is-empty .ct-empty strong{font-size:18px;font-weight:600;}
    .ct-mfoot{display:flex;align-items:center;gap:14px;position:fixed;left:0;right:0;bottom:0;z-index:60;background:#fff;
      border-top:1px solid #eef0f4;padding:12px 16px calc(12px + env(safe-area-inset-bottom));}
    .ct-mfoot .sum{display:flex;flex-direction:column;line-height:1.25;}
    .ct-mfoot .sum span{font-size:14px;color:#6b7285;}
    .ct-mfoot .sum strong{font-size:19px;color:#1a1a2e;}
    .ct-mfoot .go{margin-left:auto;background:#1a73e8;color:#fff;border-radius:6px;padding:14px 26px;
      font-size:17px;font-weight:500;text-decoration:none;text-align:center;}
    .ct-mfoot .go.wide{margin:0;flex:1;}

    /* Cart line: picture, then name, price and what it includes. */
    .ct-item{border:0 !important;background:#f7f8fa !important;border-radius:10px;padding:16px !important;
      gap:14px !important;margin-bottom:14px;}
    .ct-item img{width:76px !important;height:auto !important;align-self:flex-start;}
    .ct-item .info h3{font-size:19px !important;font-weight:600;}
    .ct-item .info p{font-size:13.5px;color:#6b7285;}
    .ct-item .price b{font-size:19px;}
    .ct-more h2{font-size:16px;font-weight:500;color:#1a1a2e;margin:18px 0 12px;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')
@include('sales_genio.partials.flow_chrome')

@php
  // What the rep sees here is before GST; the discount comes off it. GST is added at checkout.
  $total = $items->sum('price') - $totals['discountAmount'];
  $payable = $items->where('price', '>', 0)->count();
@endphp

{{-- Phone header: back to packages, title, Clear All. --}}
<div class="ct-mhead">
  <a href="{{ route('sales_genio.business.campaign', $business->id) }}" class="b" aria-label="Back to packages"><i class="fas fa-arrow-left"></i></a>
  <span class="t">Cart</span>
  @if($items->isNotEmpty())
    <form method="post" action="{{ route('sales_genio.business.cart.clear', $business->id) }}">
      @csrf
      <button type="submit" class="x">Clear All</button>
    </form>
  @else
    <span class="x-ph"></span>
  @endif
</div>

<div class="ct-wrap {{ $items->isEmpty() ? 'is-empty' : '' }}">
  <div class="ct-top">
    <a href="{{ route('sales_genio.business.campaign', $business->id) }}" class="ct-back"><i class="fas fa-arrow-left"></i> Back to packages</a>
    <div class="ct-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
    @include('sales_genio.partials.flow_avatar')
  </div>

  @if(session('success'))<div class="ct-flash"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
  @if(session('error'))<div class="ct-flash warn"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif

  <div class="ct-grid">
    <div class="ct-main">
      <div class="ct-head">
        <h1>Cart · {{ $items->count() }} item{{ $items->count() == 1 ? '' : 's' }}</h1>
        @if($items->isNotEmpty())
          <form method="post" action="{{ route('sales_genio.business.cart.clear', $business->id) }}">
            @csrf
            <button type="submit" class="ct-clear">Clear all</button>
          </form>
        @endif
      </div>

      @if(!empty($pruned))
        <div class="ct-flash"><i class="fas fa-circle-check"></i>
          {{ $pruned }} item{{ $pruned > 1 ? 's' : '' }} removed — already paid for and active on this business.
        </div>
      @endif

      @forelse($items as $item)
        <div class="ct-item">
          @if($item['image'])<img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">@endif
          <div class="info">
            <h3>{{ $item['name'] }}
              @if($item['active_till'])
                <span class="ct-live">Active till {{ \Carbon\Carbon::parse($item['active_till'])->format('d M Y') }}</span>
              @endif
            </h3>
            <p>{{ $item['plan'] }}</p>
            @if(!empty($item['needs_design']))
              <p style="color:#c62828;font-weight:600;">Website design not chosen yet —
                <a href="{{ $item['design_url'] }}" style="color:#1a73e8;">choose a design</a></p>
            @endif
            <p>Runs for {{ $item['days'] }} days from the day it is paid</p>
            @if(!empty($item['lines']))
              <p>@foreach($item['lines'] as $line){{ $line['tier'] }} — {{ $line['area'] }} ({{ $line['pincode'] }}){{ !$loop->last ? ' · ' : '' }}@endforeach</p>
            @endif
          </div>
          <div class="price">
            @if(!empty($item['free']))
              <span class="ct-free">FREE</span>
            @else
              <b>₹{{ numberFormat($item['price']) }}</b>
            @endif
            <a class="ct-edit" href="{{ $item['position_priced']
                ? route('sales_genio.business.campaign.areas', ['id' => $business->id, 'pkg' => $item['package_id']])
                : route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $item['package_id']]) }}">Change</a>
            <form method="post" action="{{ route('sales_genio.business.campaign.cart.remove', $business->id) }}">
              @csrf
              <input type="hidden" name="package_id" value="{{ $item['package_id'] }}">
              <button type="submit" class="ct-remove">Remove</button>
            </form>
          </div>
        </div>
      @empty
        <div class="ct-empty">
          <i class="fas fa-cart-shopping"></i>
          <strong>Your Cart is Empty!</strong>
          <span>Looks like you haven't added anything in the cart yet!</span>
        </div>
      @endforelse

      @if($suggestions->isNotEmpty())
        <div class="ct-more">
          <h2>You might also be interested in</h2>
          <div class="ct-rail">
            @foreach($suggestions as $sug)
              <div class="ct-sug">
                <form method="post" action="{{ route('sales_genio.business.cart.add', $business->id) }}">
                  @csrf
                  <input type="hidden" name="package_id" value="{{ $sug['id'] }}">
                  <button type="submit" class="ct-add" title="{{ $sug['flow'] ? 'Choose areas' : 'Add to cart' }}">+</button>
                </form>
                @if($sug['image'])<img src="{{ $sug['image'] }}" alt="{{ $sug['name'] }}">@endif
                <b>{{ $sug['name'] }}</b>
                <span>@if($sug['from'])from ₹{{ numberFormat($sug['from']) }}@else See plans @endif</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    <div class="ct-side">
      <div class="ct-card">
        <h3>Order summary</h3>
        @foreach($items as $item)
          <div class="ct-line">
            <span>{{ $item['name'] }}<small>{{ $item['plan'] }}</small></span>
            <b>@if(!empty($item['free'])) FREE @else ₹{{ numberFormat($item['price']) }} @endif</b>
          </div>
        @endforeach
        @if($items->isEmpty())
          <div class="ct-line"><span>No items yet</span><b>₹0</b></div>
        @endif

        @if($payable)
          @if($totals['discount'])
            <div class="ct-line ct-disc">
              <span>
                Discount — {{ $totals['discount']->name }}
                <small>{{ $totals['discount']->label() }} ·
                  <a href="#" onclick="openDiscounts(); return false;">Change</a> ·
                  <form method="post" action="{{ route('sales_genio.business.cart.discount.remove', $business->id) }}" class="ct-inline">
                    @csrf <button type="submit" class="ct-linkbtn">Remove</button>
                  </form>
                </small>
              </span>
              <b>−₹{{ numberFormat($totals['discountAmount']) }}</b>
            </div>
          @elseif($availableDiscounts->isNotEmpty())
            <button type="button" class="ct-apply" onclick="openDiscounts()">
              <i class="fas fa-tags"></i> Apply discount <em>{{ $availableDiscounts->count() }} available</em>
            </button>
          @endif
        @endif

        <div class="ct-total">
          <span>Total price</span>
          <strong>₹{{ numberFormat($total) }}</strong>
        </div>
        @if($payable)
          <a class="ct-pay" href="{{ route('sales_genio.business.cart.checkout', $business->id) }}">Proceed to Pay</a>
          <p class="ct-note">GST is added on the next step.</p>
        @else
          <p class="ct-note">Add a paid package to collect payment.</p>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Discounts the admin has switched on, that cover something in this cart. --}}
<div class="dc-modal" id="dcModal" role="dialog" aria-modal="true" aria-label="Choose a discount" onclick="if (event.target === this) closeDiscounts();">
  <div class="dc-box">
    <div class="dc-head"><b>Choose a discount</b><button type="button" class="dc-x" onclick="closeDiscounts()" aria-label="Close">&times;</button></div>
    <div class="dc-list">
      @forelse($availableDiscounts as $d)
        @php
          $on = optional($totals['discount'])->id === $d->id;
          $covers = $d->coversAllPackages() ? 'All packages' : $packages->whereIn('id', $d->package_ids)->pluck('name')->implode(', ');
        @endphp
        <div class="dc-item {{ $on ? 'is-on' : '' }}">
          <div>
            <b>{{ $d->name }}</b>
            <span class="off">{{ $d->label() }}</span>
            <small>You save ₹{{ numberFormat($d->amountFor($items)) }} on this cart · {{ $covers }}@if($d->ends_at) · until {{ $d->ends_at->format('d M Y') }}@endif</small>
          </div>
          @if($on)
            <button type="button" disabled>Applied ✓</button>
          @else
            <form method="post" action="{{ route('sales_genio.business.cart.discount', $business->id) }}">
              @csrf <input type="hidden" name="discount_id" value="{{ $d->id }}">
              <button type="submit">Apply</button>
            </form>
          @endif
        </div>
      @empty
        <p style="color:#7a8296;font-size:14px;padding:6px 4px;">No discount covers what is in this cart.</p>
      @endforelse
    </div>
  </div>
</div>
<script>
  function openDiscounts() { document.getElementById('dcModal').classList.add('open'); }
  function closeDiscounts() { document.getElementById('dcModal').classList.remove('open'); }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDiscounts(); });
</script>

{{-- Phone: one big action pinned to the bottom. --}}
<div class="ct-mfoot">
  @if($payable)
    <div class="sum"><span>Total Price</span><strong>₹{{ numberFormat($total) }}</strong></div>
    <a class="go" href="{{ route('sales_genio.business.cart.checkout', $business->id) }}">Proceed to Pay</a>
  @else
    <a class="go wide" href="{{ route('sales_genio.business.campaign', $business->id) }}">Browse Products</a>
  @endif
</div>

</body>
</html>
