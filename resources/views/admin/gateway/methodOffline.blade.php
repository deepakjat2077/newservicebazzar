@extends('admin.layouts.master')
@section('content')
@section('title','Add Offline Payment Method')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<form action="{{ route('admin.gateway.method.offline.store') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-body">
					<div class="row">
			            <div class="col-md-3 col-sm-5">
			              <div class="form-group">
			                <div class="image-upload-area">
			                  <img id="preview" src="{{ loadImage('assets/images/default.png') }}" alt="Gateway Image"/>
			                  <div class="custom-file">
			                    <input type="file" name="image" class="custom-file-input upload-image" id="upload">
			                    <label class="pick-image" for="upload">@lang('Upload')</label>
			                  </div>
			                </div>
			              </div>
			            </div>
			            <div class="col-md-9 col-sm-7">
			              <div class="form-group row">
			                <label class="col-sm-3">@lang('Method Name')</label>
			                <div class="col-sm-9">
			                	<input type="text" name="name" class="form-control" required>
			                </div>
			              </div>
			              <div class="form-group row">
			                <label class="col-sm-3">@lang('Method Currencies')</label>
			                <div class="col-sm-9">
			                	<div class="text-right m-b-10">
			                		<button type="button" class="btn btn-outline-primary btn-xs addCurrency">@lang('Add Currencies')</button>
			                	</div>
			                	<div class="row currencies">
			                		<div class="col-sm-4">
			                			<input type="text" name="currencies[]" class="form-control" required>
			                		</div>
			                	</div>
			                </div>
			              </div>
			              <div class="form-group row">
			                <label class="col-sm-3">@lang('Requirments')</label>
			                <div class="col-sm-9">
			                	<div class="text-right m-b-10">
			                		<button type="button" class="btn btn-outline-primary btn-xs addRequirment">@lang('Add Requirment')</button>
			                	</div>
			                	<div class="requirments">
			                		<input type="text" name="setting[]" class="form-control m-b-10" required>
			                	</div>
			                </div>
			              </div>
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