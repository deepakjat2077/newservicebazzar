@extends($themeView.'layouts.app')

{{-- Styles go in <head> so the page never flashes unstyled while it loads. --}}
@push('css')
<style>.inner-banner{display:none !important;}</style>
<style>
.sbco-wrap{background:transparent;padding:24px 0 64px;min-height:70vh;}
.sbco-top{display:flex;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap;}
.sbco-title{font-size:24px;font-weight:800;color:#12203f;margin:0;}
.sbco-back{display:inline-flex;align-items:center;gap:6px;color:#1a73e8;font-weight:600;text-decoration:none;font-size:14px;}
.sbco-back:hover{text-decoration:underline;}

.sbco-grid{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;}
.sbco-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:20px 22px;margin-bottom:18px;box-shadow:0 1px 3px rgba(16,24,40,.05);}
.sbco-card-title{font-size:16px;font-weight:800;color:#12203f;margin:0 0 16px;}

.sbco-plan-line{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;
  border-bottom:1px dashed #e6e9f2;padding-bottom:14px;margin-bottom:14px;}
.sbco-plan-name{font-size:18px;font-weight:700;color:#1a2235;}
.sbco-incl{font-size:12px;color:#6b7285;}
.sbco-ebill{display:flex;gap:12px;align-items:flex-start;background:#f2f6ff;border-radius:10px;padding:12px 14px;}
.sbco-ebill i{font-size:22px;color:#1a73e8;}
.sbco-ebill small{display:block;font-size:11px;color:#8a92a6;}
.sbco-ebill strong{display:block;font-size:14px;color:#1a2235;}
.sbco-ebill span{font-size:12.5px;color:#6b7285;}

.sbco-methods{display:flex;flex-direction:column;gap:10px;}
.sbco-method{display:block;cursor:pointer;margin:0;}
.sbco-method input{position:absolute;opacity:0;pointer-events:none;}
.sbco-method-body{display:flex;align-items:center;gap:14px;border:1.5px solid #e6e9f2;border-radius:10px;padding:12px 14px;transition:.15s;}
.sbco-method:hover .sbco-method-body{border-color:#c3d3f5;background:#fafbff;}
.sbco-method input:checked + .sbco-method-body{border-color:#1a73e8;background:#f2f7ff;box-shadow:0 0 0 2px rgba(26,115,232,.12);}
.sbco-method-body img{width:46px;height:32px;object-fit:contain;flex-shrink:0;}
.sbco-method-text{flex:1;min-width:0;display:flex;flex-direction:column;}
.sbco-method-text strong{font-size:14.5px;color:#1a2235;}
.sbco-method-text small{font-size:12px;color:#8a92a6;}
.sbco-tick{font-size:22px;color:#d3d8e4;flex-shrink:0;}
.sbco-method input:checked + .sbco-method-body .sbco-tick{color:#1a73e8;}

.sbco-empty{color:#7a8296;font-size:14px;padding:8px 0;}

.sbco-price{position:sticky;top:20px;}
.sbco-save{background:#eafaf1;color:#1f9d55;font-size:13px;font-weight:600;border-radius:8px;padding:10px 12px;margin-bottom:16px;}
.sbco-row{display:flex;justify-content:space-between;font-size:14px;color:#37415a;padding:9px 0;border-bottom:1px solid #f0f2f7;}
.sbco-row.sbco-muted{color:#8a92a6;font-size:12.5px;}
.sbco-green{color:#1f9d55;font-weight:700;}
.sbco-total{display:flex;justify-content:space-between;align-items:center;font-size:18px;font-weight:800;color:#12203f;padding:14px 0 4px;}
.sbco-pay{width:100%;margin-top:16px;background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:14px;
  font-size:16px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:.15s;}
.sbco-pay:hover{background:#1662c4;}
.sbco-trust{display:flex;justify-content:center;gap:16px;margin-top:14px;flex-wrap:wrap;}
.sbco-trust span{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;color:#8a92a6;font-weight:600;}
.sbco-trust i{color:#1f9d55;font-size:15px;}

@media (max-width:860px){
  .sbco-grid{grid-template-columns:1fr;}
  .sbco-price{position:static;}
}
</style>
@endpush

@section('content')
{{-- Owner panel layout: the page has its own heading, so the layout's title banner is hidden
     (the layout then pads the content by the fixed navbar's height). --}}

@section('title','Checkout — '.$package->name)

@php
    $sym = $setting->currency_symbol ?? '₹';
    $u   = auth()->user();

    // Total the buyer pays = the package price. The old price (if any) is the
    // pre-discount "budget", and the gap is the saving. GST is shown as already
    // included in the price, so the charged amount never silently changes.
    $total    = (float) $choice['price'];
    $budget   = $total;
    $discount = 0;
    $discountPct = $budget > 0 ? round($discount / $budget * 100) : 0;
    $gstIncluded = round($total - ($total / 1.18), 2); // 18% GST already inside the price

    $maskEmail = function ($e) {
        if (!$e || !str_contains($e, '@')) return $e;
        [$name, $domain] = explode('@', $e, 2);
        $keep = mb_substr($name, 0, 2);
        return $keep.str_repeat('*', max(1, mb_strlen($name) - 2)).'@'.$domain;
    };
@endphp

<div class="sbco-wrap">
  <div class="container">

    <div class="sbco-top">
      @if(!empty($template))
        <a href="{{ route('user.listing.advertise.design', ['id' => $listing->id, 'pkg' => $package->id, 'plan' => request('plan'), 'template' => $template]) }}" class="sbco-back"><i class="las la-arrow-left"></i> Change design</a>
      @else
        <a href="{{ route('user.listing.advertise', $listing->id) }}" class="sbco-back"><i class="las la-arrow-left"></i> Back to plans</a>
      @endif
      <h1 class="sbco-title">Checkout</h1>
    </div>

    <form action="{{ route('user.deposit.submit') }}" method="post" id="sbcoForm">
      @csrf
      <input type="hidden" name="sales_package_id" value="{{ $package->id }}">
      <input type="hidden" name="listing_id" value="{{ $listing->id }}">
      @if($package->needsLocation())
        <input type="hidden" name="tier" value="{{ $choice['tier'] }}">
        <input type="hidden" name="city" value="{{ $choice['city'] }}">
        <input type="hidden" name="area" value="{{ $choice['area'] }}">
      @else
        <input type="hidden" name="plan" value="{{ request('plan') }}">
      @endif
      @if(!empty($template))
        <input type="hidden" name="template" value="{{ $template }}">
      @endif

      <div class="sbco-grid">

        {{-- LEFT: summary + payment methods --}}
        <div class="sbco-main">

          <div class="sbco-card">
            <h2 class="sbco-card-title">Payment Summary</h2>
            <div class="sbco-plan-line">
              <span class="sbco-plan-name">{{ $package->name }} — {{ $choice['label'] }}</span>
              <span class="sbco-incl">{{ $choice['days'] }} days — till {{ now()->addDays($choice['days'])->format('d M Y, h:i A') }}</span>
              <span class="sbco-incl">Including GST</span>
            </div>
            <div class="sbco-ebill">
              <i class="las la-envelope"></i>
              <div>
                <small>E-Bill will be mailed to</small>
                <strong>{{ $listing->name }}</strong>
                <span>{{ $maskEmail($u->email) }}@if($u->mobile) &nbsp;·&nbsp; {{ $u->mobile }}@endif</span>
              </div>
            </div>
          </div>

          <div class="sbco-card">
            <h2 class="sbco-card-title">Select Payment Method</h2>

            @if($methods->isEmpty())
              <div class="sbco-empty">No payment method is available right now. Please contact support.</div>
            @else
              <div class="sbco-methods">
                @foreach($methods as $i => $method)
                  <label class="sbco-method">
                    <input type="radio" name="method" value="{{ $method->id }}" @if($i === 0) checked @endif required>
                    <span class="sbco-method-body">
                      <img src="{{ loadImage('assets/images/paymentMethod/'.$method->paymentMethod->image) }}" alt="{{ $method->paymentMethod->name }}">
                      <span class="sbco-method-text">
                        <strong>{{ __($method->paymentMethod->name) }}</strong>
                        <small>{{ __($method->currency) }}</small>
                      </span>
                      <i class="las la-check-circle sbco-tick"></i>
                    </span>
                  </label>
                @endforeach
              </div>
            @endif
          </div>

        </div>

        {{-- RIGHT: price details --}}
        <aside class="sbco-side">
          <div class="sbco-card sbco-price">
            <h2 class="sbco-card-title">Price Details</h2>

            @if($discount > 0)
              <div class="sbco-save">😍 You will save {{ $sym }}{{ numberFormat($discount) }} on this order</div>
            @endif

            <div class="sbco-row">
              <span>Package Budget</span>
              <span>{{ $sym }}{{ numberFormat($budget) }}</span>
            </div>
            @if($discount > 0)
              <div class="sbco-row">
                <span>Discount ({{ $discountPct }}% OFF)</span>
                <span class="sbco-green">− {{ $sym }}{{ numberFormat($discount) }}</span>
              </div>
            @endif
            <div class="sbco-row sbco-muted">
              <span>GST (18%) included</span>
              <span>{{ $sym }}{{ numberFormat($gstIncluded) }}</span>
            </div>

            <div class="sbco-total">
              <span>Total Amount</span>
              <span>{{ $sym }}{{ numberFormat($total) }}</span>
            </div>

            @if($methods->isNotEmpty())
              <button type="submit" class="sbco-pay">Pay Now <i class="las la-lock"></i></button>
            @endif

            <div class="sbco-trust">
              <span><i class="las la-shield-alt"></i> PCI DSS Compliant</span>
              <span><i class="las la-lock"></i> Secure SSL</span>
            </div>
          </div>
        </aside>

      </div>
    </form>

    <!-- Razorpay Modal Container (Hidden) -->
    <div id="razorpay_container" style="display:none;">
        <form id="razorpay_form_inline" method="POST">
            @csrf
            <input type="hidden" id="razorpay_payment_id_inline" name="razorpay_payment_id">
            <input type="hidden" id="razorpay_order_id_inline" name="razorpay_order_id">
            <input type="hidden" id="razorpay_signature_inline" name="razorpay_signature">
        </form>
    </div>

  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('sbcoForm');

    form.addEventListener('submit', function(e) {
        var selectedMethod = form.querySelector('input[name="method"]:checked');
        var methodLabel = selectedMethod ? selectedMethod.closest('.sbco-method').querySelector('.sbco-method-text strong').textContent : '';

        var isRazorpay = methodLabel.includes('RazorPay') || methodLabel.includes('Razorpay');
        var isCashfree = methodLabel.includes('Cashfree') || methodLabel.includes('CashFree');

        // Neither gateway's modal — submit the form normally (this is also
        // where a manual/offline method still ends up going through
        // /user/deposit/confirm as before).
        if (!isRazorpay && !isCashfree) {
            return;
        }

        e.preventDefault();

        var payButton = form.querySelector('.sbco-pay');
        var originalText = payButton.innerHTML;
        payButton.disabled = true;
        payButton.innerHTML = '<i class="las la-spinner la-spin"></i> Processing...';

        function restoreButton() {
            payButton.disabled = false;
            payButton.innerHTML = originalText;
        }

        var formData = new FormData(form);

        fetch('{{ route("user.deposit.submit") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Error processing payment. Please try again.');
                restoreButton();
                return;
            }

            if (isRazorpay && data.payment) {
                // Open Razorpay modal with payment data
                var options = {
                    key: data.payment.key,
                    amount: data.payment.amount,
                    currency: data.payment.currency,
                    order_id: data.payment.order_id,
                    name: data.payment.user_name,
                    description: '{{ $package->name }} — {{ $choice['label'] }}',
                    image: '{{ asset("assets/images/logo/logo.png") }}',
                    prefill: {
                        name: data.payment.user_name,
                        email: data.payment.user_email,
                        contact: data.payment.user_phone
                    },
                    theme: {
                        color: '#1a73e8'
                    },
                    handler: function(response) {
                        // Submit payment to IPN handler
                        var ipnForm = document.getElementById('razorpay_form_inline');
                        ipnForm.querySelector('#razorpay_payment_id_inline').value = response.razorpay_payment_id;
                        ipnForm.querySelector('#razorpay_order_id_inline').value = response.razorpay_order_id;
                        ipnForm.querySelector('#razorpay_signature_inline').value = response.razorpay_signature;
                        ipnForm.action = '{{ route("ipn.razorpay") }}';
                        ipnForm.submit();
                    },
                    modal: {
                        ondismiss: function() {
                            // Restore button on cancel
                            restoreButton();
                        }
                    }
                };

                var rzp = new Razorpay(options);
                rzp.open();
                return;
            }

            if (isCashfree && data.cashfree) {
                var cf = data.cashfree;
                var cashfree = Cashfree({ mode: cf.mode }); // "sandbox" or "production"

                /*
                 * "_modal" keeps the buyer on this checkout page — Cashfree's
                 * own checkout (cards/UPI/netbanking/wallets/EMI) opens as an
                 * overlay instead of navigating away to their domain, the
                 * same on-page feel the Razorpay branch above has.
                 */
                cashfree.checkout({
                    paymentSessionId: cf.payment_session_id,
                    redirectTarget: '_modal'
                }).then(function (result) {
                    if (result.paymentDetails) {
                        /*
                         * An attempt finished (success OR failure — Cashfree
                         * does not say which here). Never trust that alone:
                         * send the browser to ipn/cashfree, which re-fetches
                         * the order from Cashfree server-to-server with the
                         * secret key and only then marks it paid.
                         */
                        window.location.href = cf.ipn_url + '?order_id=' + encodeURIComponent(cf.order_id);
                        return;
                    }

                    if (result.error) {
                        // Buyer closed the modal before paying.
                        console.log(result.error);
                    }

                    restoreButton();
                });
                return;
            }

            alert('Error processing payment. Please try again.');
            restoreButton();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing payment. Please try again.');
            restoreButton();
        });
    });
});
</script>

@endsection
