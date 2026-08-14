@extends($themeView.'layouts.site')
@section('content')
@section('title','Pricing plan')
<section class="pricing-area pt-80 pb-80">
	<div class="container">
		<div class="row mb__30 justify-content-center">
			@foreach($packages as $package)
			<div class="col-lg-4 col-md-6">
				<div class="pricing-card">
					<div class="pricing-head">
						<h6>{{ __($package->name) }}</h6>
						<h1>{{$setting->currency_symbol}}{{ numberFormat($package->price) }}</h1>
					</div>
					<div class="pricing-body">
						<ul>
							<li><i class="fas fa-check"></i> {{$package->listing_allow}} @lang('Listing Allowed')</li>
							<li><i class="fas fa-check"></i> {{$package->features_allow}} @lang('Features per Listing')</li>
							<li><i class="fas fa-check"></i> {{$package->image_allow}} @lang('Photos per Listing')</li>
							<li><i class="fas fa-check"></i> {{$package->contact_allow}} @lang('Contact Information per Listing')</li>
							<li><i class="fas fa-check"></i> {{$package->featured_allow}} @lang('Featured Listing Allow')</li>
							<li><i class="fas fa-check"></i> {{$package->validity}} @lang('Days validity')</li>
						</ul>
					</div>
					<div class="pricing-footer">
						<button type="button" class="btn btn__base btn-block purchaseBtn" data-price="{{ numberFormat($package->price) }}" data-package_id="{{ $package->id }}" @if(auth()->user() && auth()->user()->package_id == $package->id) disabled @endif> @if(auth()->user() && auth()->user()->package_id == $package->id) @lang('Current Package') @else @lang('Purchase') @endif</button>
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</section>


<!-- Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">@lang('Payment')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('user.deposit.submit') }}" method="post">
      	@csrf
      	<input type="hidden" name="package_id">
	      <div class="modal-body">
	        <div class="row">
	        	<div class="col-md-4">
	        		<label>@lang('Select Payment Method')</label>
	        		<select class="form-control" name="method">
                        @foreach($methods as $method)
                            <option value="{{ $method->id }}" 
                                data-limit="{{ $setting->currency_symbol }}{{ $method->minimum + 0 }} - {{ $setting->currency_symbol }}{{ $method->maximum + 0 }}"
                                data-f_charge="{{ $method->f_charge }}"
                                data-p_charge="{{ $method->p_charge }}"
                                data-rate="{{ $method->cv_rate }}"
                                data-currency="{{ $method->currency }}"
                                data-image="{{ loadImage('assets/images/paymentMethod/'.$method->paymentMethod->image) }}"
                                >{{ __($method->paymentMethod->name) }} ({{ __($method->currency) }})</option>
                        @endforeach
                    </select>
                    <div class="mt-3">
                    	<img src="" class="method-image" alt="Payment Method">
                    </div>
	        	</div>
	        	<div class="col-md-8">
	        		<ul class="list-group">
	        			<li class="list-group-item">
	        				<span class="float-left">@lang('Amount'):</span>
	        				<span class="float-right"><span class="amount"></span> {{ $setting->site_currency }}</span>
	        			</li>
	        			<li class="list-group-item">
	        				<span class="float-left">@lang('Charge'):</span>
	        				<span class="float-right charge"></span>
	        			</li>
	        			<li class="list-group-item">
	        				<span class="float-left">@lang('Total Charge')</span>
	        				<span class="float-right"><span class="totalCharge"></span> {{ $setting->site_currency }}</span>
	        			</li>
	        			<li class="list-group-item">
	        				<span class="float-left">@lang('Amount With Charge')</span>
	        				<span class="float-right"><span class="amoWithCharge"></span> {{ $setting->site_currency }}</span>
	        			</li>
	        			<li class="list-group-item">
	        				<span class="float-left">@lang('Rate')</span>
	        				<span class="float-right">1{{ $setting->site_currency }} = <span class="rate"></span> <span class="method_currency"></span></span>
	        			</li>
	        			<li class="list-group-item">
	        				<span class="float-left">@lang('Payable (after convert)')</span>
	        				<span class="float-right"><span class="afterConvert"></span> <span class="method_currency"></span></span>
	        			</li>
	        		</ul>
	        	</div>
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
	        <button type="submit" class="btn btn-primary">@lang('Pay Now')</button>
	      </div>
      </form>
    </div>
  </div>
</div>
@endsection
@push('js')
<script>
(function($){
"use strict";

$('.purchaseBtn').on('click', function(){

    var amount = parseFloat($(this).data('price')) || 0;
    var packageId = $(this).data('package_id');

    // ✅ FREE PLAN
    if(amount <= 0){
    var url = "{{ route('admin.package.free.activate', ':id') }}";
    url = url.replace(':id', packageId);
    window.location.href = url;
    return;
}
    var modal  = $('#paymentModal');

    $('input[name=package_id]').val(packageId);

    var element = $('select[name=method] option:selected');
    selectedMethodImage(element.data('image'));
    calculation(element, amount);

    $('select[name=method]').off('change').on('change', function(){
        var element = $('select[name=method] option:selected');
        selectedMethodImage(element.data('image'));
        calculation(element, amount);
    });

    modal.modal('show');
});



function calculation(element, amount){

    var fixed_charge   = parseFloat(element.data('f_charge')) || 0;
    var percent_charge = parseFloat(element.data('p_charge')) || 0;
    var currency       = element.data('currency');
    var rate           = parseFloat(element.data('rate')) || 1;

    amount = parseFloat(amount) || 0;

    var percentAmount = (amount * percent_charge) / 100;
    var totalCharge   = fixed_charge + percentAmount;
    totalCharge       = parseFloat(totalCharge.toFixed(2));

    var amoWithCharge = parseFloat((amount + totalCharge).toFixed(2));
    var afterConvert  = parseFloat((amoWithCharge * rate).toFixed(2));

    $('.rate').text(rate.toFixed(2));
    $('.method_currency').text(currency);
    $('.charge').text(
        fixed_charge.toFixed(2) + 
        ' {{ $setting->site_currency }} + ' + 
        percent_charge.toFixed(2) + '%'
    );
    $('.amount').text(amount.toFixed(2));
    $('.totalCharge').text(totalCharge.toFixed(2));
    $('.amoWithCharge').text(amoWithCharge.toFixed(2));
    $('.afterConvert').text(afterConvert.toFixed(2));

    console.log("Display Amount:", afterConvert);
}

function selectedMethodImage(img){
    $('.method-image').attr('src', img);
}

})(jQuery);
</script>
@endpush
