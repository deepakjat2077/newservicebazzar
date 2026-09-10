@extends($themeView.'layouts.app')
@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Razorpay Payment') }}</div>
                <div class="card-body text-center">
                	<form id="razorpay_form" action="{{$data['url']}}" method="{{$data['method']}}">
                        @csrf
                        <script src="{{$data['checkout_js']}}"></script>

                        <input type="hidden" custom="{{$data['custom']}}" name="hidden">
                        <input type="hidden" id="razorpay_payment_id" name="razorpay_payment_id">
                        <input type="hidden" id="razorpay_order_id" name="razorpay_order_id">
                        <input type="hidden" id="razorpay_signature" name="razorpay_signature">

                        <button type="button" id="pay_button" class="btn btn-primary btn-lg mt-4">
                            <i class="fas fa-lock"></i> Pay with Razorpay (₹{{ $data['value']['amount'] / 100 }})
                        </button>
                    </form>
                    <p class="text-muted mt-3">Click the button above to complete your payment securely</p>
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

            // Click handler for Pay button
            $('#pay_button').on('click', function(e) {
                e.preventDefault(); // Prevent form submission
                rzp.open(); // Just open the modal
                return false;
            });

            // Prevent form from submitting on Enter key
            $('#razorpay_form').on('submit', function(e) {
                e.preventDefault();
                rzp.open();
                return false;
            });
        })(jQuery);
    </script>
@endpush