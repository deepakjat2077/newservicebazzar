@extends('admin.layouts.master')
@section('content')
@section('title','Edit Gateway Currency')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<form action="" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Method Name')</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="{{ $currency->paymentMethod->name }}" readonly>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Method Currency Name')</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="{{ $currency->currency_name }}" readonly>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Method Currency')</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="{{ $currency->currency }}" readonly>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Minimum Limit')</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="text" name="minimum" class="form-control" value="{{ numberFormat($currency->minimum) }}">
								<div class="input-group-append">
									<span class="input-group-text">{{ $setting->site_currency }}</span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Maximum Limit')</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="text" name="maximum" class="form-control" value="{{ numberFormat($currency->maximum) }}">
								<div class="input-group-append">
									<span class="input-group-text">{{ $setting->site_currency }}</span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Fixed Charge')</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="text" name="fixed_charge" class="form-control" value="{{ numberFormat($currency->f_charge) }}">
								<div class="input-group-append">
									<span class="input-group-text">{{ $setting->site_currency }}</span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Percent Charge')</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="text" name="percent_charge" class="form-control" value="{{ numberFormat($currency->p_charge) }}">
								<div class="input-group-append">
									<span class="input-group-text">%</span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Conversion Rate') <small class="color-primary">( @lang('1') {{__($currency->currency)}} = ? {{ $setting->site_currency }} )</small></label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="text" name="conversion_rate" class="form-control" value="{{ numberFormat($currency->cv_rate) }}">
								<div class="input-group-append">
									<span class="input-group-text">{{ $setting->site_currency }}</span>
								</div>
							</div>
						</div>
					</div>
					@if($currency->is_manual)
					<div class="form-group row">
						<label class="col-sm-3">@lang('Instruction')</label>
						<div class="col-sm-9">
							<textarea class="richEditor" name="instruction">@php echo $currency->instruction @endphp</textarea>
						</div>
					</div>
					@endif
					<div class="form-group row">
						<label class="col-sm-3">@lang('Status')</label>
						<div class="col-sm-9">
							<input type="checkbox" data-toggle="toggle" data-on="Enable" data-off="Disable" data-width="100%" name="status" data-onstyle="success" data-offstyle="secondary" @if($currency->enable_status) checked @endif>
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