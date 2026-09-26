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

  .ct-pending{background:#fff8ec;border:1px solid #f2dca6;border-radius:14px;padding:14px 16px;margin-bottom:14px;}
  .ct-pending__head{font-size:13.5px;font-weight:700;color:#8a6412;margin-bottom:8px;}
  .ct-pending__row{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#fff;
    border:1px solid #f0e2c4;border-radius:10px;padding:11px 13px;margin-bottom:8px;font-size:13px;color:#5a6478;
    text-decoration:none;}
  .ct-pending__row b{color:#12203f;white-space:nowrap;}
  .ct-pending p{margin:4px 0 0;font-size:12px;color:#94743a;}
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
  .ct-line b{color:#12203f;}
  .ct-total{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;background:#12203f;color:#fff;}
  .ct-total span{font-size:13.5px;opacity:.85;}
  .ct-total strong{font-size:24px;}
  .ct-pay{display:block;width:100%;background:#1f9d55;color:#fff;border:0;padding:14px;font-size:15px;font-weight:700;
    cursor:pointer;text-align:center;text-decoration:none;}
  .ct-pay:hover{background:#188746;color:#fff;}
  .ct-note{padding:12px 18px;font-size:12px;color:#8a92a6;text-align:center;line-height:1.5;}
  @media (max-width:900px){.ct-side{position:static;flex-basis:100%;}}
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

@php
  $total = $items->sum('price');
  $payable = $items->where('price', '>', 0)->count();
@endphp

<div class="ct-wrap">
  <div class="ct-top">
    <a href="{{ route('sales_genio.business.campaign', $business->id) }}" class="ct-back"><i class="fas fa-arrow-left"></i> Back to packages</a>
    <div class="ct-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
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

      @if($pendingOrders->isNotEmpty())
        <div class="ct-pending">
          <div class="ct-pending__head"><i class="fas fa-clock"></i> Payment links waiting to be paid</div>
          @foreach($pendingOrders as $po)
            <a class="ct-pending__row" href="{{ route('sales_genio.business.order', ['id' => $business->id, 'order' => $po->id]) }}">
              <span>Order #{{ $po->id }} · {{ $po->items_count }} item{{ $po->items_count == 1 ? '' : 's' }} · sent {{ $po->created_at->diffForHumans() }}</span>
              <b>₹{{ numberFormat($po->total) }} <i class="fas fa-chevron-right"></i></b>
            </a>
          @endforeach
          <p>The cart below is kept until one of these is paid, so nothing is lost if the customer pays later.</p>
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
          Nothing in the cart yet — pick a package to start the pitch.
        </div>
      @endforelse

      @if($suggestions->isNotEmpty())
        <div class="ct-more">
          <h2>Also worth pitching</h2>
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
            <span>{{ $item['name'] }}</span>
            <b>@if(!empty($item['free'])) FREE @else ₹{{ numberFormat($item['price']) }} @endif</b>
          </div>
        @endforeach
        @if($items->isEmpty())
          <div class="ct-line"><span>No items yet</span><b>₹0</b></div>
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

</body>
</html>
