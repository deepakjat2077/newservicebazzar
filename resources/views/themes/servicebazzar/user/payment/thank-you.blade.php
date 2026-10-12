@extends($themeView.'layouts.app')

{{-- Styles go in <head> so the page never flashes unstyled while it loads. --}}
@push('css')
<style>.inner-banner{display:none !important;}</style>
<style>
  .sbty-wrap{padding:24px 0 64px;min-height:calc(100vh - 90px);}
  .sbty-card{max-width:620px;margin:0 auto;background:#fff;border:1px solid #e6e9f2;border-radius:18px;padding:34px 30px 26px;text-align:center;}
  .sbty-tick{width:72px;height:72px;border-radius:50%;background:#e8f7ee;color:#1f9d55;font-size:40px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;}
  .sbty-card h1{font-size:24px;font-weight:700;color:#12203f;margin:0 0 6px;}
  .sbty-sub{color:#5a6478;font-size:15px;margin:0 0 22px;}
  .sbty-box{display:flex;gap:18px;align-items:center;text-align:left;background:#f8f9fc;border:1px solid #eef0f6;border-radius:14px;padding:16px;}
  .sbty-box img{width:120px;height:120px;object-fit:contain;border-radius:10px;background:#fff;flex:none;}
  .sbty-rows{flex:1;min-width:0;}
  .sbty-rows div{display:flex;justify-content:space-between;gap:12px;padding:6px 0;border-bottom:1px dashed #e3e7ef;font-size:14px;}
  .sbty-rows div:last-child{border-bottom:0;}
  .sbty-rows span{color:#7a8296;}
  .sbty-rows b{color:#12203f;text-align:right;word-break:break-word;}
  .sbty-actions{display:flex;gap:12px;justify-content:center;margin-top:22px;flex-wrap:wrap;}
  .sbty-btn{display:inline-flex;align-items:center;gap:7px;border-radius:10px;padding:12px 22px;font-weight:600;font-size:15px;text-decoration:none !important;}
  .sbty-btn.pri{background:#1a73e8;color:#fff !important;}
  .sbty-btn.sec{background:#fff;color:#1a73e8 !important;border:1px solid #1a73e8;}
  .sbty-note{margin:16px 0 0;font-size:13px;color:#8a92a6;}
  .sbty-note a{color:#1a73e8;}
  @media (max-width:575px){
    .sbty-card{padding:26px 16px 20px;}
    .sbty-box{flex-direction:column;text-align:left;}
    .sbty-box img{width:100%;height:150px;}
    .sbty-rows{width:100%;}
    .sbty-btn{flex:1;justify-content:center;}
  }
</style>
@endpush

@section('title', 'Payment successful')

@section('content')
{{-- The layout's title banner is hidden; the layout pads the content below its fixed navbar. --}}


@php
  $listing  = $payment->listing;
  $package  = $payment->salesPackage;
  $sym      = $setting->currency_symbol ?? '₹';
  $expiry   = $payment->purchaseExpiry();
  $plansUrl = route('user.listing.advertise', $listing->id);
  $where    = collect([$payment->sales_area, $payment->sales_city])->filter()->implode(', ');
@endphp

<div class="sbty-wrap">
  <div class="container">
    <div class="sbty-card">
      <div class="sbty-tick"><i class="las la-check"></i></div>
      <h1>Thank you! Payment successful</h1>
      <p class="sbty-sub">{{ $package->name ?? 'Your plan' }} is now active for <strong>{{ $listing->name }}</strong>.</p>

      <div class="sbty-box">
        @if($package && $package->imageUrl())
          <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}">
        @endif
        <div class="sbty-rows">
          <div><span>Plan</span><b>{{ $package->name ?? '—' }}{{ $payment->sales_plan ? ' — ' . $payment->sales_plan : '' }}</b></div>
          @if($where)<div><span>Location</span><b>{{ $where }}</b></div>@endif
          @if($expiry)<div><span>Valid till</span><b>{{ $expiry->format('d M Y, h:i A') }}</b></div>@endif
          <div><span>Amount paid</span><b>{{ $sym }}{{ numberFormat($payment->amount) }}</b></div>
          <div><span>Paid via</span><b>{{ optional(optional($payment->method_currency)->paymentMethod)->name ?? '—' }}</b></div>
          <div><span>Transaction</span><b>#{{ $payment->trx_number }}</b></div>
        </div>
      </div>

      <div class="sbty-actions">
        <a href="{{ $plansUrl }}" class="sbty-btn pri"><i class="las la-arrow-left"></i> Back to plans</a>
        <a href="{{ route('user.deposit.invoice', $payment->id) }}" class="sbty-btn sec" target="_blank" rel="noopener"><i class="las la-file-invoice"></i> Download Invoice</a>
      </div>

      <p class="sbty-note" id="sbtyNote">Taking you back to the plans in <b id="sbtyCount">8</b> seconds… <a href="#" id="sbtyStay">Stay on this page</a></p>
    </div>
  </div>
</div>



<script>
  (function () {
    var left = 8, timer;
    var count = document.getElementById('sbtyCount');
    timer = setInterval(function () {
      left--;
      if (count) count.textContent = left;
      if (left <= 0) { clearInterval(timer); window.location.href = @json($plansUrl); }
    }, 1000);
    document.getElementById('sbtyStay').addEventListener('click', function (e) {
      e.preventDefault();
      clearInterval(timer);
      document.getElementById('sbtyNote').textContent = 'You can go back to the plans whenever you like.';
    });
  })();
</script>
@endsection
