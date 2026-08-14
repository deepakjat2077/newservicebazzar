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
                	<form action="{{$data['url']}}" method="{{$data['method']}}">
                        <script src="{{$data['checkout_js']}}"
                                @foreach($data['value'] as $key=>$value)
                                data-{{$key}}="{{$value}}"
                                @endforeach >

                        </script>

                        <input type="hidden" custom="{{$data['custom']}}" name="hidden">

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
            $('input[type="submit"]').addClass("ml-4 mt-4 btn btn-primary btn-custom2 text-center btn-lg");
        })(jQuery);
    </script>
@endpush