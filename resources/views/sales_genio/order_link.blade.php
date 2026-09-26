<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment link — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;} button{font-family:'Poppins',sans-serif;}
  body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .ol-wrap{max-width:760px;margin:0 auto;padding:24px 20px 60px;}
  .ol-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;}
  .ol-top{background:linear-gradient(90deg,#1f9d55,#27b768);color:#fff;padding:20px;display:flex;gap:14px;align-items:center;}
  .ol-top i{font-size:26px;}
  .ol-top b{display:block;font-size:17px;}
  .ol-top span{font-size:13px;opacity:.9;}
  .ol-body{padding:20px;}
  .ol-line{display:flex;justify-content:space-between;gap:10px;padding:10px 0;font-size:13.5px;color:#5a6478;
    border-bottom:1px dashed #eef0f6;}
  .ol-line:last-of-type{border-bottom:0;}
  .ol-line b{color:#12203f;}
  .ol-total{display:flex;justify-content:space-between;align-items:center;background:#12203f;color:#fff;
    border-radius:12px;padding:14px 18px;margin-top:12px;}
  .ol-total strong{font-size:22px;}
  .ol-link{margin-top:18px;background:#f7f9fc;border:1px dashed #d7dceb;border-radius:10px;padding:13px 15px;
    font-size:12.5px;color:#37415a;word-break:break-all;}
  .ol-actions{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;}
  .ol-wa{flex:1;min-width:180px;text-align:center;background:#25d366;color:#fff;border-radius:10px;padding:13px;
    font-weight:700;font-size:14px;text-decoration:none;}
  .ol-copy{flex:0 0 140px;border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:10px;padding:13px;
    font-weight:700;font-size:14px;cursor:pointer;}
  .ol-open{flex:0 0 150px;text-align:center;border:1px solid #d7dceb;color:#374151;border-radius:10px;padding:13px;
    font-weight:700;font-size:14px;text-decoration:none;}
  .ol-foot{display:flex;justify-content:space-between;gap:12px;padding:16px 20px;border-top:1px solid #eef0f6;
    font-size:13px;color:#7a8296;flex-wrap:wrap;}
  .ol-foot a{color:#1a73e8;font-weight:700;text-decoration:none;}
  .ol-status{display:inline-block;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;}
  .ol-status.pending{background:#fff7e8;color:#8a6412;}
  .ol-status.paid{background:#eafaf1;color:#1f7a45;}
  .ol-msg{display:flex;gap:9px;align-items:center;border-radius:10px;padding:12px 14px;font-size:13px;font-weight:600;margin-top:16px;}
  .ol-msg.ok{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;}
  .ol-msg.bad{background:#fdeceb;border:1px solid #f5c2c0;color:#b4261f;}
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

@php
  $waNumber = preg_replace('/\D/', '', (string) $business->mobile);
@endphp

<div class="ol-wrap">
  <div class="ol-card">
    <div class="ol-top">
      <i class="fas fa-link"></i>
      <div>
        <b>Payment link ready</b>
        <span>Send it to {{ $business->name }} — they pay online and everything switches on by itself.</span>
      </div>
    </div>

    <div class="ol-body">
      @foreach($order->items as $item)
        <div class="ol-line">
          <span>
            {{ $item->name }}<br><small style="color:#8a92a6;">{{ $item->plan }} · {{ $item->days }} days</small>
            @if(!empty($item->meta['lines']))
              @foreach($item->meta['lines'] as $line)
                <small style="display:block;color:#5a6478;">• {{ $line['tier'] }} — {{ $line['area'] }} ({{ $line['pincode'] }})</small>
              @endforeach
            @endif
          </span>
          <b>@if($item->is_free) FREE @else ₹{{ numberFormat($item->price) }} @endif</b>
        </div>
      @endforeach
      <div class="ol-line"><span>GST (18%)</span><b>₹{{ numberFormat($order->gst) }}</b></div>

      <div class="ol-total">
        <span>Total payable</span>
        <strong>₹{{ numberFormat($order->total) }}</strong>
      </div>

      @if(session('success'))<div class="ol-msg ok"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
      @if(session('error'))<div class="ol-msg bad"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif

      <div class="ol-actions">
        @if($waNumber)
          <form method="post" action="{{ route('sales_genio.business.order.whatsapp', ['id' => $business->id, 'order' => $order->id]) }}" style="flex:1;min-width:180px;">
            @csrf
            <button type="submit" class="ol-wa" style="width:100%;border:0;cursor:pointer;">
              <i class="fab fa-whatsapp"></i> Send on WhatsApp
            </button>
          </form>
        @endif
        <a class="ol-open" href="{{ $order->payUrl() }}" target="_blank" rel="noopener">Preview</a>
      </div>
    </div>

    <div class="ol-foot">
      <span>
        Order #{{ $order->id }} ·
        <span class="ol-status {{ $order->isPaid() ? 'paid' : 'pending' }}">{{ $order->isPaid() ? 'Paid' : 'Awaiting payment' }}</span>
      </span>
      <a href="{{ route('sales_genio.business.campaign', $business->id) }}">Back to packages</a>
    </div>
  </div>
</div>

</body>
</html>
