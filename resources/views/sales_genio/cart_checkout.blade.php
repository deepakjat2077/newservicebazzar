<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Collect Payment — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input{font-family:'Poppins',sans-serif;}
  body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .pay-wrap{max-width:1080px;margin:0 auto;padding:22px 20px 60px;}
  .pay-top{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
  .pay-back{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #d7dceb;border-radius:22px;
    padding:8px 16px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;}
  .pay-biz{text-align:right;font-size:13px;color:#6b7285;}
  .pay-biz strong{display:block;color:#12203f;font-size:16px;}
  .pay-grid{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;}
  .pay-left{flex:1;min-width:320px;}
  .pay-right{flex:0 0 340px;max-width:100%;}
  .pay-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;margin-bottom:16px;}
  .pay-card h2{margin:0;padding:15px 18px;border-bottom:1px solid #eef0f6;font-size:14.5px;font-weight:800;color:#12203f;}
  .pay-methods{padding:12px;}
  .pay-method{display:flex;align-items:center;gap:13px;border:1.5px solid #e6e9f2;border-radius:12px;padding:14px;
    margin-bottom:10px;cursor:pointer;position:relative;}
  .pay-method:last-child{margin-bottom:0;}
  .pay-method input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .pay-method .dot{width:18px;height:18px;border-radius:50%;border:1.5px solid #cfd6e4;flex-shrink:0;}
  .pay-method.is-on{border-color:#1a73e8;background:#f3f8fe;}
  .pay-method.is-on .dot{border-color:#1a73e8;border-width:5px;}
  .pay-method b{display:block;font-size:14px;color:#12203f;}
  .pay-method span{font-size:12.5px;color:#7a8296;}
  .pay-method i{font-size:17px;color:#1a73e8;}
  .pay-steps{padding:16px 18px;font-size:13px;color:#5a6478;line-height:1.7;}
  .pay-steps li{margin-bottom:5px;}
  .pay-share{display:flex;gap:10px;padding:0 18px 18px;flex-wrap:wrap;}
  .pay-wa{flex:1;min-width:170px;text-align:center;background:#25d366;color:#fff;border-radius:10px;padding:12px;
    font-weight:700;font-size:14px;text-decoration:none;}
  .pay-copy{flex:0 0 130px;border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:10px;padding:12px;
    font-weight:700;font-size:14px;cursor:pointer;}
  .pay-link{margin:0 18px 18px;background:#f7f9fc;border:1px dashed #d7dceb;border-radius:9px;padding:10px 12px;
    font-size:12px;color:#5a6478;word-break:break-all;}
  .pay-line{display:flex;justify-content:space-between;gap:10px;padding:11px 18px;font-size:13.5px;color:#5a6478;
    border-bottom:1px dashed #eef0f6;}
  .pay-line:last-of-type{border-bottom:0;}
  .pay-line b{color:#12203f;}
  .pay-line small{display:block;color:#8a92a6;font-size:11.5px;margin-top:2px;}
  .pay-line.is-free b{color:#1f7a45;}
  .pay-gst{background:#fbfcfe;}
  .pay-total{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;background:#12203f;color:#fff;}
  .pay-total span{font-size:13.5px;opacity:.85;}
  .pay-total strong{font-size:24px;}
  .pay-go{display:block;width:100%;background:#1f9d55;color:#fff;border:0;padding:15px;font-size:15px;font-weight:700;
    text-align:center;text-decoration:none;}
  .pay-go:hover{background:#188746;color:#fff;}
  .pay-note{padding:12px 18px;font-size:12px;color:#8a92a6;text-align:center;line-height:1.55;}
  @media (max-width:900px){.pay-right{flex-basis:100%;}}
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

@php
  $total = $subtotal + $gst;
  $waText = rawurlencode('Hi, here is the Service Bazzar payment link for ' . $business->name . ' — ₹'
      . numberFormat($total) . ' (incl. GST): ' . $payLink);
  $waNumber = preg_replace('/\D/', '', (string) $business->mobile);
@endphp

<div class="pay-wrap">
  <div class="pay-top">
    <a href="{{ route('sales_genio.business.cart', $business->id) }}" class="pay-back"><i class="fas fa-arrow-left"></i> Back to cart</a>
    <div class="pay-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->mobile }}
    </div>
  </div>

  <div class="pay-grid">
    <div class="pay-left">
      <div class="pay-card">
        <h2>How the customer pays</h2>
        <div class="pay-methods">
          @forelse($methods as $i => $method)
            <label class="pay-method {{ $i === 0 ? 'is-on' : '' }}">
              <input type="radio" name="method" value="{{ $method->id }}" {{ $i === 0 ? 'checked' : '' }}>
              <span class="dot"></span>
              <i class="fas {{ $method->paymentMethod->act === 'razorpay' ? 'fa-credit-card' : 'fa-indian-rupee-sign' }}"></i>
              <span>
                <b>{{ $method->paymentMethod->name }}</b>
                <span>Card · UPI · Net banking · Wallet</span>
              </span>
            </label>
          @empty
            <p class="pay-note">No payment gateway is enabled right now.</p>
          @endforelse
        </div>
      </div>

    </div>

    <div class="pay-right">
      <div class="pay-card">
        <h2>Order summary</h2>
        @foreach($items as $item)
          <div class="pay-line {{ !empty($item['free']) ? 'is-free' : '' }}">
            <span>
              {{ $item['name'] }}
              <small>{{ $item['plan'] }}</small>
              @if(!empty($item['lines']))
                @foreach($item['lines'] as $line)
                  <small>• {{ $line['tier'] }} — {{ $line['area'] }} ({{ $line['pincode'] }})</small>
                @endforeach
              @endif
            </span>
            <b>@if(!empty($item['free'])) FREE @else ₹{{ numberFormat($item['price']) }} @endif</b>
          </div>
        @endforeach
        <div class="pay-line pay-gst"><span>GST (18%)</span><b>₹{{ numberFormat($gst) }}</b></div>
        <div class="pay-total">
          <span>Total payable</span>
          <strong>₹{{ numberFormat($total) }}</strong>
        </div>
        <form method="post" action="{{ route('sales_genio.business.cart.order', $business->id) }}">
          @csrf
          <button type="submit" class="pay-go">Generate payment link</button>
        </form>
        <p class="pay-note">Makes a link with these exact plans and amount. The customer opens it, pays by card or UPI, and every package switches on by itself.</p>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.pay-method').forEach(function (label) {
  label.addEventListener('click', function () {
    document.querySelectorAll('.pay-method').forEach(function (l) { l.classList.remove('is-on'); });
    label.classList.add('is-on');
  });
});
</script>

</body>
</html>
