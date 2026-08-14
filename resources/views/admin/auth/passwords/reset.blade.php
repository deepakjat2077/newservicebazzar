@extends('admin.layouts.auth')
@section('content')
@section('title','Reset Password')
    <div class="row justify-content-center align-content-center">
      <div class="col-lg-5 col-md-6">
        <div class="favicon-area">
            <img src="{{asset('assets/images/logo/logo_dark.png') }}">
        </div>
        <div class="auth-card">
          <div class="auth-card-body">
            <form action="{{ route('admin.password.reset.submit') }}" method="post">
              @csrf
              <input type="hidden" name="email" value="{{ $email }}">
              <input type="hidden" name="token" value="{{ $token }}">
              <div class="form-group">
                <label>@lang('New Password')</label>
                <input type="password" name="password" class="form-control">
              </div>
              <div class="form-group">
                <label>@lang('Confirm Password')</label>
                <input type="password" name="password_confirmation" class="form-control">
              </div>
              <div class="form-group">
                <div class="float-left mt-2 mb-sm-0 mb-2">
                  <a href="{{ route('admin.login') }}">@lang('Back to login')</a>
                </div>
                <div class="float-right">
                  <button class="btn btn-primary">@lang('Reset')</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
@endsection
