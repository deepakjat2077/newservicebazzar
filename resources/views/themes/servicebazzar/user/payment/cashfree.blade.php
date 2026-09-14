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
                        Cashfree's own hosted checkout (opened below) already
                        renders cards/UPI/netbanking/wallets/EMI itself — same
                        as their merchant demo and sites like JustDial — so
                        there is nothing to build here beyond handing it the
                        session id from cashfree() in PaymentGateway.php.
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

        function openCheckout() {
            cashfree.checkout({
                paymentSessionId: "{{ $data['value']['payment_session_id'] }}",
                redirectTarget: "_self"
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
