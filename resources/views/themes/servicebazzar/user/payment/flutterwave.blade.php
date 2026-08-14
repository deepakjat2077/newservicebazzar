@extends($themeView.'layouts.app')
@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('Flutterwave Payment') }}</div>
                <div class="card-body">
                	<button type="button" class="btn btn-primary  btn-custom2 " id="btn-confirm" onClick="payWithRave()">@lang('Pay Now')</button>
                        <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
                        <script>
                            "use strict";
                            
                            var btn = document.querySelector("#btn-confirm");
                            btn.setAttribute("type", "button");
                            const API_publicKey = "{{$data['public_key']}}";

                            function payWithRave() {
                                var x = getpaidSetup({
                                    PBFPubKey: API_publicKey,
                                    customer_email: "{{$data['email']}}",
                                    amount: "{{$data['amount'] }}",
                                    customer_phone: "{{$data['phone']}}",
                                    currency: "{{$data['currency']}}",
                                    txref: "{{$data['trx']}}",
                                    onclose: function () {
                                    },
                                    callback: function (response) {
                                        var txref = response.tx.txRef;
                                        var status = response.tx.status;
                                        var chargeResponse = response.tx.chargeResponseCode;
                                        if (chargeResponse == "00" || chargeResponse == "0") {
                                            window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                                        } else {
                                            window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                                        }
                                        // x.close(); // use this to close the modal immediately after payment.
                                    }
                                });
                            }
                        </script>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection