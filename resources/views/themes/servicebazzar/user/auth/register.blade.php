@extends($themeView.'layouts.site')
@section('content')
@section('title','Register')
<style>.account-section .account-area .account-content-area {
background-color: #0e0f38;
}

</style>
<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area">
            <div class="row">
                <div class="col-lg-6 p-0 px-3 px-lg-0">
                    <div class="account-content-area">
                        <h4 class="title">{{ __(@$register->content->title) }}</h4>
                        <p>{{ __(@$register->content->sub_title) }}</p>
                        <a href="{{ route('user.login') }}" class="btn btn__base">@lang('Login')</a>
                    </div>
                </div>
                <div class="col-lg-6 p-0 px-3 px-lg-0">
                    <div class="account-form-area">
                        <div class="account-head-area">
                            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar logo">
                            <p>{{ __(@$register->content->register_content) }} </p>
                        </div>
                        <form method="POST" action="{{ route('user.register') }}">
                            @csrf
                            <div class="input-area">
                                <label><i class="fab fa-autoprefixer"></i></label>
                                <input type="text" name="first_name" class="form-control" placeholder="@lang('First Name')" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="input-area">
                                <label><i class="fab fa-autoprefixer"></i></label>
                                <input type="text" name="last_name" class="form-control" placeholder="@lang('Last Name')" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="input-area">
                                <label><i class="far fa-user"></i></label>
                                <input type="text" name="username" class="form-control" placeholder="@lang('Username')" value="{{ old('username') }}" required>
                            </div>
                            <div class="input-area">
                                <label><i class="fas fa-phone"></i></label>
                                <input type="text" name="mobile" class="form-control" placeholder="@lang('Mobile')" value="{{ old('mobile') }}" required>
                            </div>
                            <div class="input-area">
                                <label><i class="far fa-envelope"></i></label>
                                <input type="email" name="email" class="form-control" placeholder="@lang('Email')" value="{{ old('email') }}" required>
                            </div>
                            <div class="input-area">
                                <label><i class="fas fa-key"></i></label>
                                <input type="password" name="password" class="form-control" placeholder="@lang('Password')" required>
                            </div>
                            <div class="input-area">
                                <label><i class="fas fa-key"></i></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="@lang('Confirm Password')" required>
                            </div>
                            @if(@$setting->recaptcha->status == 1)
                            <div class="input-area">
                                @include('includes.recaptcha')
                            </div>
                            @endif
                            <button type="submit" class="btn btn__base btn-block">@lang('Sign Up')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
