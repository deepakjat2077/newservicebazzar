<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex,nofollow">
<title>Payment — {{ optional($order->listing)->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .so-bar{background:#fff;border-bottom:1px solid #e8eaf0;padding:12px 20px;}
  .so-bar img{height:34px;}
  .so-wrap{max-width:820px;margin:0 auto;padding:24px 20px 60px;}
  .so-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;margin-bottom:16px;}
  .so-card h2{margin:0;padding:16px 20px;border-bottom:1px solid #eef0f6;font-size:15px;font-weight:800;color:#12203f;}
  .so-hero{padding:22px 20px;}
  .so-hero h1{margin:0 0 4px;font-size:21px;font-weight:800;color:#12203f;}
  .so-hero p{margin:0;color:#6b7285;font-size:13.5px;}
  .so-paid{display:flex;align-items:center;gap:12px;background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;
    border-radius:14px;padding:18px 20px;margin-bottom:16px;font-weight:700;}
  .so-paid i{font-size:26px;}
  .so-paid span{display:block;font-weight:500;font-size:13px;color:#2f7a52;margin-top:2px;}
  .so-item{display:flex;justify-content:space-between;gap:14px;padding:15px 20px;border-bottom:1px dashed #eef0f6;}
  .so-item:last-of-type{border-bottom:0;}
  .so-item b{display:block;font-size:14.5px;color:#12203f;}
  .so-item small{display:block;color:#7a8296;font-size:12.5px;margin-top:3px;line-height:1.5;}
  .so-item .amt{font-weight:700;color:#12203f;white-space:nowrap;}
  .so-areas{display:flex;flex-direction:column;gap:5px;margin-top:8px;}
  .so-areas span{background:#f4f7fc;border-radius:8px;padding:6px 10px;font-size:12px;color:#5a6478;}
  .so-areas b{color:#12203f;}
  .so-free{background:#eafaf1;color:#1f7a45;border-radius:20px;padding:3px 11px;font-size:11.5px;font-weight:700;}
  .so-sums{padding:6px 20px 14px;}
  .so-row{display:flex;justify-content:space-between;padding:8px 0;font-size:13.5px;color:#5a6478;}
  .so-row b{color:#12203f;}
  .so-total{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#12203f;color:#fff;}
  .so-total span{font-size:14px;opacity:.85;}
  .so-total strong{font-size:26px;}
  .so-pay{padding:18px 20px;}
  .so-method{display:flex;align-items:center;gap:13px;border:1.5px solid #e6e9f2;border-radius:12px;padding:14px;
    margin-bottom:10px;cursor:pointer;position:relative;}
  .so-method input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .so-method .dot{width:18px;height:18px;border-radius:50%;border:1.5px solid #cfd6e4;flex-shrink:0;}
  .so-method.is-on{border-color:#1a73e8;background:#f3f8fe;}
  .so-method.is-on .dot{border-color:#1a73e8;border-width:5px;}
  .so-method b{display:block;font-size:14px;color:#12203f;}
  .so-method small{color:#7a8296;font-size:12.5px;}
  .so-method.is-off{opacity:.55;cursor:not-allowed;}
  .so-go{display:block;width:100%;background:#1f9d55;color:#fff;border:0;border-radius:11px;padding:15px;
    font-size:16px;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;}
  .so-go:hover{background:#188746;}
  .so-go:disabled{opacity:.6;cursor:not-allowed;}
  .so-secure{text-align:center;color:#8a92a6;font-size:12px;margin-top:12px;}
  .so-err{background:#fdeceb;border:1px solid #f5c2c0;color:#b4261f;border-radius:10px;padding:12px 14px;
    font-size:13px;margin-bottom:12px;display:none;}
</style>
</head>
<body>

<div class="so-bar"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar"></div>

<div class="so-wrap">

  @if($order->isPaid())
    <div class="so-paid">
      <i class="fas fa-circle-check"></i>
      <div>
        Payment received — thank you!
        <span>
          Paid on {{ $order->paid_at?->format('d M Y, h:i A') }} · everything below is now active.
          @if($order->invoice_sent_at)
            <br>Invoice {{ $order->invoice_no }} emailed to {{ $order->invoice_email }}.
          @endif
        </span>
      </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin:-6px 0 18px;">
      <a href="{{ route('sales.order.invoice', $order->token) }}" target="_blank"
         style="display:inline-flex;align-items:center;gap:8px;background:#0d1b2a;color:#fff;
                border-radius:9px;padding:11px 20px;font-size:14px;font-weight:600;text-decoration:none;">
        <i class="fas fa-file-invoice"></i> Download Invoice
      </a>
      <a href="{{ route('sales.order.invoice.resend', $order->token) }}"
         style="display:inline-flex;align-items:center;gap:8px;border:1px solid #d5dae5;color:#3d4763;
                border-radius:9px;padding:11px 20px;font-size:14px;font-weight:600;text-decoration:none;">
        <i class="fas fa-envelope"></i> Email it to me again
      </a>
    </div>

    @if(session('success'))
      <div style="background:#eafaf1;color:#1f7a45;border-radius:9px;padding:12px 16px;
                  font-size:14px;margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div style="background:#fdeaea;color:#c62828;border-radius:9px;padding:12px 16px;
                  font-size:14px;margin-bottom:16px;">{{ session('error') }}</div>
    @endif
  @endif

  <div class="so-card">
    <div class="so-hero">
      <h1>{{ optional($order->listing)->name }}</h1>
      <p>
        {{ optional($order->listing)->area ? optional($order->listing)->area.', ' : '' }}{{ optional($order->listing)->city }}
        @if($order->salesUser) · prepared by {{ $order->salesUser->name }} ({{ $order->salesUser->empcode }}) @endif
      </p>
    </div>
  </div>

  <div class="so-card">
    <h2>What you are buying</h2>
    @foreach($order->items->reject->is_free as $item)
      <div class="so-item">
        <div>
          <b>{{ $item->name }}</b>
          <small>{{ $item->plan }}</small>
          <small>Runs for {{ $item->days }} days from the day this is paid</small>
          @if(!empty($item->meta['lines']))
            <div class="so-areas">
              @foreach($item->meta['lines'] as $line)
                <span><b>{{ $line['tier'] }}</b> · {{ $line['area'] }} — {{ $line['pincode'] }}</span>
              @endforeach
            </div>
          @endif
        </div>
        <div class="amt">
          @if($item->is_free)<span class="so-free">FREE</span>@else ₹{{ numberFormat($item->price) }} @endif
        </div>
      </div>
    @endforeach

    <div class="so-sums">
      <div class="so-row"><span>Subtotal</span><b>₹{{ numberFormat($order->subtotal) }}</b></div>
      @if($order->discount_amount > 0)
        <div class="so-row" style="color:#1f7a45;"><span>Discount — {{ $order->discount_name }}</span><b>−₹{{ numberFormat($order->discount_amount) }}</b></div>
      @endif
      <div class="so-row"><span>GST (18%)</span><b>₹{{ numberFormat($order->gst) }}</b></div>
    </div>
    <div class="so-total">
      <span>{{ $order->isPaid() ? 'Amount paid' : 'Total payable' }}</span>
      <strong>₹{{ numberFormat($order->total) }}</strong>
    </div>
  </div>

  @unless($order->isPaid())
    <div class="so-card">
      <h2>Pay with</h2>
      <div class="so-pay">
        <div class="so-err" id="soErr"></div>

        @forelse($gateways as $i => $gateway)
          <label class="so-method {{ $i === 0 ? 'is-on' : '' }}">
            <input type="radio" name="gateway" value="{{ $gateway->act }}" {{ $i === 0 ? 'checked' : '' }}>
            <span class="dot"></span>
            <span>
              <b>{{ $gateway->name }}</b>
              <small>Card · UPI · Net banking · Wallet</small>
            </span>
          </label>
        @empty
          <p style="color:#b4261f;font-size:13.5px;">No payment gateway is available right now. Please contact us.</p>
        @endforelse

        @foreach($pending ?? [] as $other)
          <label class="so-method is-off">
            <span class="dot"></span>
            <span>
              <b>{{ $other->name }}</b>
              <small>Not available for this link yet</small>
            </span>
          </label>
        @endforeach

        @if($gateways->isNotEmpty())
          <button type="button" class="so-go" id="soPay">Proceed to Pay ₹{{ numberFormat($order->total) }}</button>
          <p class="so-secure"><i class="fas fa-lock"></i> Secure payment · your card details never reach Service Bazzar</p>
        @endif
      </div>
    </div>
  @endunless
</div>

@if(!$order->isPaid() && $gateways->isNotEmpty())
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
  var button = document.getElementById('soPay');
  var errorBox = document.getElementById('soErr');
  var csrf = document.querySelector('meta[name="csrf-token"]').content;

  document.querySelectorAll('.so-method input').forEach(function (input) {
    input.addEventListener('change', function () {
      document.querySelectorAll('.so-method').forEach(function (l) { l.classList.remove('is-on'); });
      input.closest('.so-method').classList.add('is-on');
    });
  });

  function fail(message) {
    errorBox.textContent = message;
    errorBox.style.display = 'block';
    button.disabled = false;
    button.textContent = 'Proceed to Pay ₹{{ numberFormat($order->total) }}';
  }

  button.addEventListener('click', function () {
    button.disabled = true;
    button.textContent = 'Starting…';
    errorBox.style.display = 'none';

    fetch('{{ route('sales.order.start', $order->token) }}', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.success) return fail(data.error || 'Could not start the payment.');

      var checkout = new Razorpay({
        key: data.key,
        order_id: data.order_id,
        amount: data.amount,
        currency: 'INR',
        name: 'Service Bazzar',
        description: data.name || 'Business package',
        prefill: {contact: data.contact || '', email: data.email || ''},
        theme: {color: '#1a73e8'},
        modal: {ondismiss: function () { fail('Payment cancelled — you can try again.'); }},
        handler: function (response) {
          button.textContent = 'Confirming…';

          fetch('{{ route('sales.order.confirm', $order->token) }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify(response)
          })
          .then(function (r) { return r.json(); })
          .then(function (out) {
            if (out.success) { window.location = out.redirect; }
            else { fail(out.error || 'We could not verify the payment. Please contact us.'); }
          })
          .catch(function () { fail('We could not verify the payment. Please contact us.'); });
        }
      });

      checkout.open();
    })
    .catch(function () { fail('Could not start the payment.'); });
  });
})();
</script>
@endif

</body>
</html>
