
@extends($themeView . 'layouts.site')
@section('content')
@section('title', 'Verify OTP')
<style>.account-section .account-area .account-content-area {
background-color: #0e0f38;
}

</style>
    <section class="account-section pt-120 pb-120">
        <div class="container">
            <div class="account-area">
                <div class="row">
                    <div class="col-lg-6 p-0 px-3 px-lg-0">
                        <div class="account-content-area"
                           >
                            <h4 class="title">{{ __(@$login->content->title) }}</h4>
                            <p>{{ __(@$login->content->sub_title) }}</p>
                          
                        </div>
                    </div>
                    <div class="col-lg-6 p-0 px-3 px-lg-0">
                        <div class="account-form-area">
                            <div class="account-head-area">
                                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar logo">
                              
                            </div>
                             <form method="POST" action="{{ route('user.otp.login.verify') }}">
                                @csrf

                                <div class="input-area">
                                    <label><i class="far fa-user"></i></label>
                                    <input type="text" name="otp" class="form-control" placeholder="Enter 6 digit OTP"
                                        value="{{ old('OTP') }}" required>
                                </div>

                              

                               

                                @if(@$setting->recaptcha->status == 1)
                                    <div class="input-area">
                                        @include('includes.recaptcha')
                                    </div>
                                @endif

                                <button type="submit" class="btn btn__base btn-block">@lang('Verify OTP')</button>
                            </form>
  
                            <small class="mt-4"> <a
                                    href="{{ route('user.otp.login.form') }}">@lang('Change Email')</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection