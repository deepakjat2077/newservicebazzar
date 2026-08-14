@extends($themeView . 'layouts.site')
@section('content')
@section('title', 'OTP Login')


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
                                <img src="{{ asset('assets/images/logo/logo.png') }}">
                              
                            </div>
                            <form method="POST" action="{{ route('user.otp.login.send') }}">
                                @csrf

                                <div class="input-area">
                                    <label><i class="far fa-user"></i></label>
                                    <input type="email" name="email" class="form-control" placeholder="Enter your email"
                                        value="{{ old('email') }}" required>
                                </div>

                              

                               

                                @if(@$setting->recaptcha->status == 1)
                                    <div class="input-area">
                                        @include('includes.recaptcha')
                                    </div>
                                @endif

                                <button type="submit" class="btn btn__base btn-block">@lang('Send OTP')</button>
                            </form>

                          
                            <small class="mt-4"> <a
                                    href="{{ route('user.login') }}">@lang('Back to Login')</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection