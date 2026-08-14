@extends($themeView.'layouts.app')
@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('Paystack Payment') }}</div>
                <div class="card-body">
                	<form action="{{ route('ipn.paystack') }}" method="POST" class="text-center">
                        @csrf

                        <button type="button" class=" mt-4 btn btn-success  btn-lg" id="btn-confirm">@lang('Pay Now')</button>
                        <script src="//js.paystack.co/v1/inline.js"
                                data-key="{{ $data['key'] }}"
                                data-email="{{ $data['email'] }}"
                                data-amount="{{$data['amount']}}"
                                data-currency="{{$data['currency']}}"
                                data-ref="{{ $data['ref'] }}"
                                data-custom-button="btn-confirm"
                        >
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection