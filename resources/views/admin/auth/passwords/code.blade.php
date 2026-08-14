@extends('admin.layouts.auth')
@section('content')
@section('title','Verify')
    <div class="row justify-content-center align-content-center">
      <div class="col-lg-5 col-md-6">
        <div class="favicon-area">
            <img src="{{asset('assets/images/logo/logo_dark.png') }}">
        </div>
        <div class="auth-card">
          <div class="auth-card-body">
            <form action="{{ route('admin.password.verify.code') }}" method="post">
              @csrf
              <div class="form-group">
                <label>@lang('Enter Verification Code')</label>
                <input type="text" name="code" id="pincode-input3">
              </div>
              <div class="form-group">
                <div class="float-left mt-2 mb-sm-0 mb-2">
                  <a href="{{ route('admin.login') }}">@lang('Back to login')</a>
                </div>
                <div class="float-right">
                  <button class="btn btn-primary">@lang('Submit')</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
@endsection
@push('css-link')
  <!-- bootstrap-pincode css -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap-pincode-input.css')}}">
@endpush
@push('js-link')
  <script src="{{asset('assets/admin/js/bootstrap-pincode-input.js')}}"></script>
@endpush
@push('js')
<!-- bootstrap-pincode js -->
  <script>
    (function($){
      "use strict";
      $('#pincode-input3').pincodeInput({
        inputs:6,
        hidedigits:false
      });
    })(jQuery);
  </script>
@endpush
