@extends('admin.layouts.master')
@section('content')
@section('title','Edit Payment Method')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<form action="{{ route('admin.gateway.method.update',$method->id) }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-body">
					<div class="row">
			            <div class="col-md-3 col-sm-5">
			              <div class="form-group">
			                <div class="image-upload-area">
			                  <img id="preview" src="{{ loadImage('assets/images/paymentMethod/'.$method->image) }}" alt="Gateway Image"/>
			                  <div class="custom-file">
			                    <input type="file" name="image" class="custom-file-input upload-image" id="upload">
			                    <label class="pick-image" for="upload">@lang('Upload')</label>
			                  </div>
			                </div>
			              </div>
			            </div>
			            <div class="col-md-9 col-sm-7">
			              <div class="form-group row">
			                <label class="col-sm-3">@lang('Gateway Name')</label>
			                <div class="col-sm-9">
			                	<input type="text" name="name" class="form-control" value="{{ $method->name }}" required>
			                </div>
			              </div>
			              @if($method->act == 'payeer')
							<div class="form-group row">
				                <label class="col-sm-3">@lang('Success And Faild URL')</label>
				                <div class="col-sm-9">
				                	<div class="copyUrl">
				                		<input type="text" class="form-control" id="myInput" value="{{ route('home') }}" readonly>
				                		<span class="copyBtn">@lang('Copy')</span>
				                	</div>
				                </div>
				            </div>
			              @endif
			              @if($method->act == 'paystack')
							<div class="form-group row">
				                <label class="col-sm-3">@lang('Callback & Webhook URL')</label>
				                <div class="col-sm-9">
				                	<div class="copyUrl">
				                		<input type="text" class="form-control" id="myInput" value="{{ route('home') }}" readonly>
				                		<span class="copyBtn">@lang('Copy')</span>
				                	</div>
				                </div>
				            </div>
			              @endif
			              @if($method->is_manual)
							<div class="form-group row">
				                <label class="col-sm-3">@lang('Method Currencies')</label>
				                <div class="col-sm-9">
				                	<div class="text-right m-b-10">
				                		<button type="button" class="btn btn-outline-primary btn-xs addCurrency">@lang('Add Currencies')</button>
				                	</div>
				                	<div class="row currencies">
			            			@foreach($method->currencies as $currency)
				                		<div class="col-sm-4">
				                			<input type="text" name="currencies[]" value="{{ $currency->currency }}" class="form-control m-b-10" required>
				                		</div>
			            			@endforeach
				                	</div>
				                </div>
				            </div>
			              @endif
			              @foreach($method->setting as $key => $methodSetting)
							<div class="form-group row">
								<label class="col-sm-3">
									@if($method->is_manual == 1)
										@if($loop->first)
						                	@lang('Requirment')
						                @endif
									@else
					                	{{ __(strtoupper(str_replace('_', ' ', $key))) }}
									@endif
								</label>
				                <div class="col-sm-9">
				                	@if($method->is_manual == 1)
					                	@if($loop->first)
						                	<div class="text-right m-b-10">
						                		<button type="button" class="btn btn-outline-primary btn-xs addRequirment">@lang('Add Requirment')</button>
						                	</div>
					                	@endif
				                	@endif
				                	<div @if($loop->last) class="requirments" @endif>
				                		<div class="input-group @if($loop->last) m-b-10 @endif">
				                			@if($method->is_manual == 1)
				                			{{-- Offline/manual gateways: these are field LABELS (e.g. "Bank Name"), not secrets. --}}
				                			<input type="text" name="setting[]" class="form-control @if($loop->last) m-b-10 @endif" value="{{ Str::keyToStr($methodSetting) }}" required>
				                			@else
				                			{{-- Automatic gateways: these are real API keys/secrets. Never re-display the stored value. --}}
				                			<input type="password" name="setting[{{ $key }}]" class="form-control" placeholder="@lang('Leave blank to keep the current value')" autocomplete="new-password">
				                			@endif
				                			@if($method->is_manual == 1)
					                			@if(!$loop->first)
					                			<div class="input-group-append">
	    											<span class="input-group-text btn btn-danger removeRequirment"><i class="fas fa-times"></i></span>
					                			</div>
					                			@endif
				                			@endif
				                		</div>
				                	</div>
				                </div>
				            </div>
			              @endforeach
			            </div>
		          	</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Save')</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
@push('js')
<script>
	(function($){
		"use strict"

		$(document).on('click','.addCurrency',function(){
			var html = `
				<div class="col-sm-4 m-b-10">
        			<div class="input-group">
        				<input type="text" name="currencies[]" class="form-control" required>
        				<div class="input-group-append">
        					<span class="input-group-text btn btn-danger removeCurrency"><i class="fas fa-times"></i></span>
        				</div>
        			</div>
        		</div>
			`;
			$('.currencies').append(html);
		});
		$(document).on('click','.removeCurrency',function(){
			$(this).closest('.col-sm-4').remove();
		});

		$(document).on('click','.addRequirment',function(){
			var html = `
    			<div class="input-group m-b-10">
    				<input type="text" name="setting[]" class="form-control" required>
    				<div class="input-group-append">
    					<span class="input-group-text btn btn-danger removeRequirment"><i class="fas fa-times"></i></span>
    				</div>
    			</div>
			`;
			$('.requirments').append(html);
		});
		$(document).on('click','.removeRequirment',function(){
			$(this).closest('.input-group').remove();
		});
	})(jQuery);
</script>
@endpush