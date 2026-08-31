@extends($themeView . 'layouts.site')
@section('content')
@section('title', 'Login')

<style>
.account-section .account-area .account-content-area {
    background-color: #0e0f38;
}

.mobile-register-box{
    display: none;
}

@media (max-width: 767px) {

    .account-content-area .btn{
        display: none !important;
    }

    .mobile-register-box{
        display: block;
        text-align: center;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .mobile-register-box a{
        font-weight: 600;
    }
    a {
        color: #000;
    }
}

</style>

<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area">
            <div class="row">

                <div class="col-lg-6 p-0 px-3 px-lg-0">
                    <div class="account-content-area">

                        <div class="content">
                            <h4 class="title">{{ __(@$login->content->title) }}</h4>
                            <p>{{ __(@$login->content->sub_title) }}</p>
                        </div>

                        <a href="{{ route('user.register') }}" class="btn btn__base">
                            @lang('Register')
                        </a>

                    </div>
                </div>

                <div class="col-lg-6 p-0 px-3 px-lg-0">
                    <div class="account-form-area">

                        <div class="account-head-area">
                            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar logo">
                            <p>{{ __(@$login->content->login_content) }}</p>
                        </div>

                        <form method="POST" action="{{ route('user.login') }}">
                            @csrf

                            <div class="input-area">
                                <label><i class="far fa-user"></i></label>
                                <input type="text"
                                       name="login"
                                       class="form-control"
                                       placeholder="Email or Username"
                                       value="{{ old('login') }}"
                                       required>
                            </div>

                            <div class="input-area">
                                <label><i class="fas fa-key"></i></label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Password"
                                       required>
                            </div>

                            <div class="text-right mb-3">
                                <a href="{{ route('user.otp.login.form') }}">
                                    Login with OTP
                                </a>
                            </div>

                            @if(@$setting->recaptcha->status == 1)
                                <div class="input-area">
                                    @include('includes.recaptcha')
                                </div>
                            @endif

                            <button type="submit" class="btn btn__base btn-block">
                                @lang('Sign In')
                            </button>
                        </form>

                        <div class="mobile-register-box">
                            Don’t have an account?
                            <a href="{{ route('user.register') }}">
                                Register now
                            </a>
                        </div>

                        <small class="mt-4">
                            @lang('Forgot your password?')
                            <a href="{{ route('user.password.reset') }}">
                                @lang('Reset now')
                            </a>
                        </small>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection