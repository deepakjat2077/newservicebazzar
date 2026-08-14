@extends($themeView.'layouts.site')
@section('content')
@section('title','Verification')

<style>.account-section .account-area .account-content-area {
background-color: #0e0f38;
}

</style>
<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="account-form-area">
                        <div class="account-head-area">
                            <img src="{{ asset('assets/images/logo/logo.png') }}">
                            <p>@lang('We have got the verification code. You can change your password now. Please provide a strong password for the best security. This is our recommendation but not required.')</p>
                        </div>
                        <form method="POST" action="{{ route('user.password.change') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                            <div class="input-area">
                                <label><i class="fas fa-key"></i></label>
                                <input type="password" name="password" class="form-control" placeholder="@lang('Password')" required>
                            </div>
                            <div class="input-area">
                                <label><i class="fas fa-key"></i></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="@lang('Confirm Password')" required>
                            </div>
                            <button type="submit" class="btn btn__base btn-block">@lang('Reset Password')</button>
                        </form>
                        <small class="mt-4">@lang('Back to ') <a href="{{ route('user.login') }}">@lang('login')</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
