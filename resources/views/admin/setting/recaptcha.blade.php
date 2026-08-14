@extends('admin.layouts.master')
@section('content')
@section('title','Setting')
<div class="row justify-content-center">
	<div class="col-md-9">
		<div class="card">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('Basic Setting')</h6>
	        </div>
			<form action="{{ route('admin.setting.recaptcha.update') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Site Key')</label>
						<div class="col-sm-9">
							<input type="text" name="site_key" class="form-control" value="{{ @$setting->recaptcha->site_key }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Secret Key')</label>
						<div class="col-sm-9">
							<input type="text" name="secret_key" class="form-control" value="{{ @$setting->recaptcha->secret_key }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Status')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="status" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small" @if(@$setting->recaptcha->status) checked @endif data-width="110">
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
