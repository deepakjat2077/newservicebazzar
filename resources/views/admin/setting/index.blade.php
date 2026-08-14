@extends('admin.layouts.master')
@section('content')
@section('title','Setting')
<div class="row justify-content-center">
	<div class="col-md-9">
		<div class="card">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('Basic Setting')</h6>
	        </div>
			<form action="{{ route('admin.setting.update') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Site Name')</label>
						<div class="col-sm-9">
							<input type="text" name="site_name" class="form-control" value="{{ $setting->site_name }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Site Currency')</label>
						<div class="col-sm-9">
							<input type="text" name="site_currency" class="form-control" value="{{ $setting->site_currency }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Currency Symbol')</label>
						<div class="col-sm-9">
							<input type="text" name="currency_symbol" class="form-control" value="{{ $setting->currency_symbol }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('First Color')</label>
						<div class="col-sm-9">
							<input type="color" name="first_color" class="form-control" value="{{ $setting->first_color }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Registration')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="registration" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small" @if($setting->registration) checked @endif data-width="110">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Email Verification')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="email_verification" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small" @if($setting->email_verification) checked @endif data-width="110">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Multiple Language')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="multi_lang" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small" @if($setting->multi_lang) checked @endif data-width="110">
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
