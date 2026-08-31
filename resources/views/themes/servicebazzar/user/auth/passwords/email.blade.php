@extends($themeView.'layouts.site')
@section('content')
@section('title','Password Reset Via Email')
<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="account-form-area">
                        <div class="account-head-area">
                            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar logo">
                            <p>@lang('Do you forget your password? Please provide your email to the below box and submit the form. We will send a verification code to your mail. You can reset your password using that verification code')</p>
                        </div>
                        <form method="POST" action="">
                            @csrf
                            <div class="input-area">
                                <label><i class="far fa-envelope"></i></label>
                                <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
                            </div>
                            <button type="submit" class="btn btn__base btn-block">@lang('Send Password Reset Code')</button>
                        </form>
                        <small class="mt-4">@lang('Back to ') <a href="{{ route('user.login') }}">@lang('Login')</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
