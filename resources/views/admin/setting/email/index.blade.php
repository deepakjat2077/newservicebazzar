@extends('admin.layouts.master')
@section('content')
@section('title','Email Setting')
<div class="row mb__30">
	<div class="col-sm-6">
		<div class="card m-b-30">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('Email Setting')</h6>
	        </div>
			<form action="{{ route('admin.setting.email.update') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Host name')</label>
						<div class="col-sm-9">
							<input type="text" name="host" class="form-control" placeholder="@lang('e.g. yourdomain.com')" value="{{ @$setting->mail->host }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Port')</label>
						<div class="col-sm-9">
							<input type="text" name="port" class="form-control" placeholder="@lang('e.g. 465')" value="{{ @$setting->mail->port }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Encryption')</label>
						<div class="col-sm-9">
							<select class="form-control" name="encryption" required>
								<option value="ssl">@lang('SSL')</option>
								<option value="tls">@lang('TLS')</option>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Username')</label>
						<div class="col-sm-9">
							<input type="text" name="username" class="form-control" placeholder="@lang('e.g. mail@domain.com')" value="{{ @$setting->mail->username }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Password')</label>
						<div class="col-sm-9">
							<input type="password" name="password" class="form-control" placeholder="@lang('Leave blank to keep the current password')" autocomplete="new-password">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Email From')</label>
						<div class="col-sm-9">
							<input name="email_from" class="form-control" placeholder="@lang('Email From')" value="{{ @$setting->mail->email_from }}" required>
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Update')</button>
				</div>
			</form>
		</div>
		<div class="card m-b-30">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('Test Mail')</h6>
	        </div>
	        <form action="{{ route('admin.setting.email.test') }}" method="post">
	        	@csrf
				<div class="card-body">
					<div class="form-group">
						<input type="text" name="email" placeholder="@lang('Test Mail')" class="form-control">
					</div>
				</div>
				<div class="card-footer text-right">
					<button class="btn btn-primary">@lang('Send Mail')</button>
				</div>
	        </form>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="card m-b-30">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('Email Template')</h6>
	        </div>
			<form action="{{ route('admin.setting.email.update.template') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="mb-3">
						<ul class="list-group">
						  <li class="list-group-item"><p class="float-left">@lang('{:name:}')</p> <p class="float-right">@lang('Users Name. System will take from database')</p>
						  </li>
						  <li class="list-group-item"><p class="float-left">@lang('{:message:}')</p> <p class="float-right">@lang('Main message of users')</p>
						  </li>
						</ul>
					</div>
					<div class="form-group row">
						<div class="col-sm-12">
							<textarea class="richEditor" name="template">@php echo $setting->email_template @endphp</textarea>
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
@push('script')
<script>
	(function($){
		"use strict";

		$('select[name=encryption]').val('{{ @$setting->mail->encryption }}');
	})(jQuery);
</script>
@endpush