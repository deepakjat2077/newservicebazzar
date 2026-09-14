@extends($themeView.'layouts.app')
@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Cashfree Payment') }}</div>
                <div class="card-body text-center">
                    <div id="loading_msg" class="text-center mt-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Opening Cashfree secure checkout...</p>
                    </div>

                    {{--
                        Cashfree's own checkout (opened below, as an on-page
                        modal — see openCheckout() — not a redirect away to
                        their domain) already renders cards/UPI/netbanking/
                        wallets/EMI itself, the same JustDial-style payment
                        picker, so there is nothing to build here beyond
                        handing it the session id from cashfree() in
                        PaymentGateway.php.
                    --}}
                    <button type="button" id="pay_button" class="btn btn-primary btn-lg mt-4" style="display:none;">
                        <i class="fas fa-lock"></i> Pay with Cashfree (₹{{ $data['value']['amount'] }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
@push('js')
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
    (function(){
        "use strict";

        var cashfree = Cashfree({
            mode: "{{ $data['value']['mode'] }}" // "sandbox" or "production"
        });

        var opening = false;

        function openCheckout() {
            if (opening) return;
            opening = true;

            var payButton = document.getElementById('pay_button');
            payButton.disabled = true;

            /*
             * "_modal" keeps the payer on this page — Cashfree's checkout
             * opens as an overlay instead of navigating the browser away to
             * their domain, the same feel as Razorpay's own JS modal.
             * ("_self" would do a full-page redirect to Cashfree instead.)
             */
            cashfree.checkout({
                paymentSessionId: "{{ $data['value']['payment_session_id'] }}",
                redirectTarget: "_modal"
            }).then(function (result) {
                opening = false;
                payButton.disabled = false;

                if (result.paymentDetails) {
                    /*
                     * The modal reports an attempt finished (success OR
                     * failure — Cashfree does not say which here). Never
                     * trust that alone: send the browser to our own
                     * ipn.cashfree, which re-fetches the order from
                     * Cashfree server-to-server with the secret key and
                     * only then marks the payment successful.
                     */
                    window.location.href = "{{ $data['value']['ipn_url'] }}?order_id={{ $data['value']['order_id'] }}";
                    return;
                }

                if (result.error) {
                    // Payer closed the modal before paying — stay on this
                    // page so they can press Pay again.
                    console.log(result.error);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function(){
            document.getElementById('loading_msg').style.display = 'none';
            document.getElementById('pay_button').style.display = 'inline-block';

            // Auto-open, same seamless behaviour as the Razorpay page.
            setTimeout(openCheckout, 500);
        });

        document.getElementById('pay_button').addEventListener('click', function(){
            openCheckout();
        });
    })();
</script>
@endpush
