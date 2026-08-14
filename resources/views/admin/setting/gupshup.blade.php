@extends('admin.layouts.master')
@section('content')
@section('title','WhatsApp (Gupshup) Setting')
@php
    $gupshup = $setting->gupshup ?? null;
    $apiKey = @$gupshup->api_key;
    $webhookSecret = @$gupshup->webhook_secret;
@endphp
<div class="row justify-content-center">
	<div class="col-md-9">
		<div class="card m-b-30">
			<div class="card-header bg-color-primary d-flex justify-content-between align-items-center">
	            <h6 class="color-white mb-0">@lang('WhatsApp (Gupshup) Setting')</h6>
	            @adminCan('whatsapp.view')
	            <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-sm btn-light">
	            	<i class="fab fa-whatsapp"></i> @lang('Open WhatsApp Panel')
	            </a>
	            @endadminCan
	        </div>
			<form action="{{ route('admin.setting.gupshup.update') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('API Key')</label>
						<div class="col-sm-9">
							<input type="password" name="api_key" class="form-control" autocomplete="new-password" placeholder="@lang('Leave blank to keep the current API key')">
							<small class="text-muted">
								@if($apiKey)
									@lang('Currently saved'): <code>{{ str_repeat('•', 8) . substr($apiKey, -4) }}</code>
								@else
									<span class="text-danger">@lang('Not set yet')</span>
								@endif
							</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('App Name')</label>
						<div class="col-sm-9">
							<input type="text" name="app_name" class="form-control" placeholder="@lang('e.g. servicebazzar')" value="{{ old('app_name', @$gupshup->app_name) }}" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Source Number')</label>
						<div class="col-sm-9">
							<input type="text" name="source" class="form-control" placeholder="@lang('e.g. 919251067682')" value="{{ old('source', @$gupshup->source) }}" required>
							<small class="text-muted">@lang('With country code, no + sign or spaces.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Template ID')</label>
						<div class="col-sm-9">
							<input type="text" name="template_id" class="form-control" placeholder="@lang('Marketing template id')" value="{{ old('template_id', @$gupshup->template_id) }}">
							<small class="text-muted">@lang('Used for WhatsApp messages sent from the listing panel.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('OTP Template ID')</label>
						<div class="col-sm-9">
							<input type="text" name="otp_template_id" class="form-control" placeholder="@lang('OTP template id')" value="{{ old('otp_template_id', @$gupshup->otp_template_id) }}">
							<small class="text-muted">@lang('Used for registration and resend OTP.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Enquiry Template ID')</label>
						<div class="col-sm-9">
							<input type="text" name="enquiry_template_id" class="form-control" placeholder="@lang('e.g. the id of servicebazzar_enquiry')" value="{{ old('enquiry_template_id', @$gupshup->enquiry_template_id) }}">
							<small class="text-muted">
								@lang('Used when forwarding an enquiry to a listing. Takes 7 variables:')
								<code>@{{1}}</code> @lang('contact person'),
								<code>@{{2}}</code> @lang('listing name'),
								<code>@{{3}}</code> @lang('customer name'),
								<code>@{{4}}</code> @lang('customer phone'),
								<code>@{{5}}</code> @lang('location'),
								<code>@{{6}}</code> @lang('category'),
								<code>@{{7}}</code> @lang('enquiry date & time').
							</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Webhook Secret')</label>
						<div class="col-sm-9">
							{{-- Shown in the clear on purpose: it has to be pasted into the
							     Gupshup dashboard as part of the callback URL, and hiding it
							     also made it impossible to clear a wrong value. --}}
							<input type="text" name="webhook_secret" class="form-control"
							       value="{{ old('webhook_secret', $webhookSecret) }}"
							       placeholder="@lang('A password you invent, e.g. sb-wh-9f2k41')">
							<small class="text-danger d-block">
								<strong>@lang('This is not the URL.')</strong>
								@lang('Invent any random word here, then paste the full Callback URL below into Gupshup. Leave it empty to accept webhooks without a token.')
							</small>
							<small class="text-muted d-block mt-2">
								@lang('Callback URL to paste into the Gupshup dashboard'):
								<code class="d-block p-2 bg-light">{{ url('/gupshup/webhook') }}@if($webhookSecret)?token={{ $webhookSecret }}@endif</code>
							</small>
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
