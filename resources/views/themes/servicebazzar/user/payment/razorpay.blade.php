@extends($themeView.'layouts.app')
@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Razorpay Payment') }}</div>
                <div class="card-body">
                	<form id="razorpay_form" action="{{$data['url']}}" method="{{$data['method']}}">
                        @csrf
                        <script src="{{$data['checkout_js']}}"></script>

                        <input type="hidden" custom="{{$data['custom']}}" name="hidden">
                        <input type="hidden" id="razorpay_payment_id" name="razorpay_payment_id">
                        <input type="hidden" id="razorpay_order_id" name="razorpay_order_id">
                        <input type="hidden" id="razorpay_signature" name="razorpay_signature">

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
@push('js')
    <script>
        (function($){
            "use strict";

            // Override Razorpay's default handler with our own
            var checkoutOptions = {
                key: "{{ $data['value']['key'] }}",
                amount: {{ $data['value']['amount'] }},
                currency: "{{ $data['value']['currency'] }}",
                order_id: "{{ $data['value']['order_id'] }}",
                name: "{{ $data['value']['name'] }}",
                description: "{{ $data['value']['description'] }}",
                image: "{{ $data['value']['image'] }}",
                prefill: {
                    name: "{{ $data['value']['prefill.name'] }}",
                    email: "{{ $data['value']['prefill.email'] }}",
                    contact: "{{ $data['value']['prefill.contact'] }}"
                },
                theme: {
                    color: "{{ $data['value']['theme.color'] }}"
                },
                handler: function(response) {
                    // Fill in the hidden fields with payment details
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                    document.getElementById('razorpay_signature').value = response.razorpay_signature;

                    // Submit the form to our IPN handler
                    document.getElementById('razorpay_form').submit();
                },
                modal: {
                    ondismiss: function() {
                        // User cancelled - stay on this page, don't redirect
                        console.log('Payment cancelled');
                    }
                }
            };

            // Create Razorpay checkout
            var rzp = new Razorpay(checkoutOptions);

            // Allow reopening modal on cancel
            checkoutOptions.modal.ondismiss = function() {
                console.log('Payment cancelled - user can retry');
                // Modal will close but user can click Pay button to retry
            };

            // Open immediately
            rzp.open();

            $('input[type="submit"]').addClass("ml-4 mt-4 btn btn-primary btn-custom2 text-center btn-lg");
        })(jQuery);
    </script>
@endpush