<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cart — SB Sales</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;} button{font-family:'Poppins',sans-serif;}
  body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .cs-wrap{max-width:900px;margin:0 auto;padding:22px 20px 60px;}
  .cs-wrap h1{font-size:21px;font-weight:800;margin:0 0 4px;color:#12203f;}
  .cs-sub{color:#6b7285;font-size:13.5px;margin:0 0 18px;}
  .cs-row{display:flex;justify-content:space-between;align-items:center;gap:14px;background:#fff;border:1px solid #e6e9f2;
    border-radius:14px;padding:16px 18px;margin-bottom:12px;text-decoration:none;flex-wrap:wrap;}
  .cs-row:hover{border-color:#b9d3f5;}
  .cs-row b{display:block;color:#12203f;font-size:15.5px;}
  .cs-row span{color:#7a8296;font-size:13px;}
  .cs-amt{text-align:right;white-space:nowrap;}
  .cs-amt strong{display:block;font-size:17px;color:#12203f;}
  .cs-amt em{font-style:normal;font-size:12px;color:#8a92a6;}
  .cs-empty{background:#fff;border:1px dashed #d7dceb;border-radius:14px;padding:46px 20px;text-align:center;color:#7a8296;}
  .cs-empty i{font-size:42px;color:#cbd2e0;display:block;margin-bottom:12px;}
  .cs-empty a{display:inline-block;margin-top:14px;background:#1a73e8;color:#fff;border-radius:10px;padding:11px 20px;
    font-weight:700;font-size:14px;text-decoration:none;}
  .cs-head{font-size:13px;font-weight:700;color:#8a92a6;text-transform:uppercase;letter-spacing:.4px;margin:26px 0 10px;}
  .cs-pending{background:#fff8ec;border-color:#f2dca6;}
  .cs-tag{background:#fff;border:1px solid #f0d49a;color:#8a6412;border-radius:20px;padding:4px 11px;font-size:12px;font-weight:700;}
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="cs-wrap">
  <h1>Cart</h1>
  <p class="cs-sub">Pitch lists you are carrying right now.</p>

  @if(!empty($pruned))
    <div class="cs-row" style="background:#eafaf1;border-color:#bde5cd;color:#1f7a45;font-weight:600;">
      <span><i class="fas fa-circle-check"></i> {{ $pruned }} item{{ $pruned > 1 ? 's' : '' }} removed — already paid for.</span>
    </div>
  @endif

  @forelse($carts as $cart)
    <a class="cs-row" href="{{ route('sales_genio.business.cart', $cart['listing']->id) }}">
      <div>
        <b>{{ $cart['listing']->name }}</b>
        <span>{{ $cart['listing']->area ? $cart['listing']->area.', ' : '' }}{{ $cart['listing']->city }} · {{ $cart['names'] }}</span>
      </div>
      <div class="cs-amt">
        <strong>₹{{ numberFormat($cart['total']) }}</strong>
        <em>{{ $cart['count'] }} item{{ $cart['count'] == 1 ? '' : 's' }}</em>
      </div>
    </a>
  @empty
    <div class="cs-empty">
      <i class="fas fa-cart-shopping"></i>
      Nothing in the cart yet — open a business and pick a package to start a pitch.
      <br><a href="{{ route('sales_genio.leads', 'new-business') }}">Go to my businesses</a>
    </div>
  @endforelse

  @if($orders->isNotEmpty())
    <p class="cs-head">Payment links waiting to be paid</p>
    @foreach($orders as $order)
      <a class="cs-row cs-pending" href="{{ route('sales_genio.business.order', ['id' => $order->listing_id, 'order' => $order->id]) }}">
        <div>
          <b>{{ optional($order->listing)->name ?? 'Business' }}</b>
          <span>Order #{{ $order->id }} · sent {{ $order->created_at->diffForHumans() }}</span>
        </div>
        <div class="cs-amt">
          <strong>₹{{ numberFormat($order->total) }}</strong>
          <em class="cs-tag">Awaiting payment</em>
        </div>
      </a>
    @endforeach
  @endif
</div>

</body>
</html>
