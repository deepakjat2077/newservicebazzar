@extends('admin.layouts.auth')
@section('content')
@section('title','Admin Login')
    <div class="row justify-content-center align-content-center">
      <div class="col-lg-5 col-md-6">
        <div class="favicon-area">
            <img src="{{asset('assets/images/logo/logo.png') }}">
        </div>
        <div class="auth-card">
          <div class="auth-card-body">
            <form action="{{ route('admin.login.submit') }}" method="post" id="adminLoginForm">
              @csrf
              {{-- Exact location for the admin login log, when the browser is allowed to share it. --}}
              <input type="hidden" name="geo_lat" id="geoLat">
              <input type="hidden" name="geo_lng" id="geoLng">
              <input type="hidden" name="geo_acc" id="geoAcc">
              <div class="form-group">
                <label>@lang('Username')</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="form-group">
                <label>@lang('Password')</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="form-group">
                <div class="float-left mt-2 mb-sm-0 mb-2">
                  @lang('Forgot password?') <a href="{{ route('admin.password.reset') }}">@lang('Reset Now')</a>
                </div>
                <div class="float-right">
                  <button type="submit" class="btn btn-primary">@lang('Login')</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
<script>
  (function () {
    if (!('geolocation' in navigator)) return;
    navigator.geolocation.getCurrentPosition(function (p) {
      document.getElementById('geoLat').value = p.coords.latitude;
      document.getElementById('geoLng').value = p.coords.longitude;
      document.getElementById('geoAcc').value = Math.round(p.coords.accuracy);
    }, function () {}, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
  })();
</script>
@endsection
