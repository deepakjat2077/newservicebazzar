@extends('admin.layouts.master')
@section('content')
@section('title','Social Login Setting')
@php
    $social = $setting->social_login ?? null;
    $googleClientId = @$social->google_client_id;
    $googleClientSecret = @$social->google_client_secret;
    $facebookClientId = @$social->facebook_client_id;
    $facebookClientSecret = @$social->facebook_client_secret;
@endphp
<div class="row justify-content-center">
	<div class="col-md-9">
		<div class="card m-b-30">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white mb-0">@lang('Google Login')</h6>
	        </div>
			<form action="{{ route('admin.setting.social.login.update') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Enable')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="google_enabled" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small" data-width="110" value="1" @if(@$social->google_enabled) checked @endif>
							<small class="text-muted d-block">@lang('Shows the "Continue with Google" button on the login popup once a Client ID and Secret are saved below.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Client ID')</label>
						<div class="col-sm-9">
							<input type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', $googleClientId) }}" placeholder="@lang('e.g. xxxxxxxx.apps.googleusercontent.com')">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Client Secret')</label>
						<div class="col-sm-9">
							<input type="password" name="google_client_secret" class="form-control" autocomplete="new-password" placeholder="@lang('Leave blank to keep the current secret')">
							<small class="text-muted">
								@if($googleClientSecret)
									@lang('Currently saved'): <code>{{ str_repeat('•', 8) . substr($googleClientSecret, -4) }}</code>
								@else
									<span class="text-danger">@lang('Not set yet')</span>
								@endif
							</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Redirect URI')</label>
						<div class="col-sm-9">
							{{-- Paste this exact URL into Google Cloud Console →
							     Credentials → Authorized redirect URIs. --}}
							<code class="d-block p-2 bg-light">{{ url('/user/social-login/google/callback') }}</code>
							<small class="text-muted d-block mt-1">@lang('Add this exact URL under "Authorized redirect URIs" in Google Cloud Console → APIs & Services → Credentials.')</small>
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Save')</button>
				</div>
			</form>
		</div>

		<div class="card m-b-30">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white mb-0">@lang('Facebook Login')</h6>
	        </div>
			<form action="{{ route('admin.setting.social.login.update') }}" method="post">
				@csrf
				{{-- Both providers post to the same route and each save only
				     touches its own keys, so re-submitting this form never
				     clears the other provider's values (see the controller's
				     $keep() helper). --}}
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Enable')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="facebook_enabled" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small" data-width="110" value="1" @if(@$social->facebook_enabled) checked @endif>
							<small class="text-muted d-block">@lang('Shows the "Continue with Facebook" button on the login popup once an App ID and Secret are saved below.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('App ID')</label>
						<div class="col-sm-9">
							<input type="text" name="facebook_client_id" class="form-control" value="{{ old('facebook_client_id', $facebookClientId) }}" placeholder="@lang('Facebook App ID')">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('App Secret')</label>
						<div class="col-sm-9">
							<input type="password" name="facebook_client_secret" class="form-control" autocomplete="new-password" placeholder="@lang('Leave blank to keep the current secret')">
							<small class="text-muted">
								@if($facebookClientSecret)
									@lang('Currently saved'): <code>{{ str_repeat('•', 8) . substr($facebookClientSecret, -4) }}</code>
								@else
									<span class="text-danger">@lang('Not set yet')</span>
								@endif
							</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Redirect URI')</label>
						<div class="col-sm-9">
							<code class="d-block p-2 bg-light">{{ url('/user/social-login/facebook/callback') }}</code>
							<small class="text-muted d-block mt-1">@lang('Add this exact URL under "Valid OAuth Redirect URIs" in Facebook Developers → your App → Facebook Login → Settings.')</small>
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
