@extends($themeView.'layouts.app')
@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('Vougepay Payment') }}</div>
                <div class="card-body">
                	<button type="button" class="mt-4 btn btn-primary custom-success text-center btn-lg" id="btn-confirm">@lang('Pay Now')</button>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
@push('js')

    <script src="//voguepay.com/js/voguepay.js"></script>
    <script>
        "use strict";

        var closedFunction = function () {
        }
        var successFunction = function (transaction_id) {
            window.location.href = '{{ route('user.deposit') }}';
        }
        var failedFunction = function (transaction_id) {
            window.location.href = '{{ route('user.deposit') }}';
        }

        function pay(item, price) {
            //Initiate voguepay inline payment
            Voguepay.init({
                v_merchant_id: "{{ $data['v_merchant_id']}}",
                total: price,
                notify_url: "{{ $data['notify_url'] }}",
                cur: "{{$data['cur']}}",
                merchant_ref: "{{ $data['merchant_ref'] }}",
                memo: "{{$data['memo']}}",
                recurrent: true,
                frequency: 10,
                developer_code: '$$mland4587',
                store_id: "{{ $data['store_id'] }}",
                custom: "{{ $data['custom'] }}",

                closed: closedFunction,
                success: successFunction,
                failed: failedFunction
            });
        }

        $(document).ready(function () {
            "use strict";
            $(document).on('click', '#btn-confirm', function (e) {
                e.preventDefault();
                pay('Buy', {{ $data['buy'] }});
            });
        });
    </script>
@endpush