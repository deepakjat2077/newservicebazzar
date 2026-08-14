@extends($themeView.'layouts.site')
@section('content')
@section('title','Verification')
<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="account-form-area">
                        <div class="account-head-area">
                            <img src="{{ asset('assets/images/logo/logo.png') }}">
                            <p>@lang('You have sent a verification code to your mail. Please provide the code to reset your password')</p>
                        </div>
                        <form method="POST" action="{{ route('user.password.verify.code') }}">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <div class="input-area">
                                <label><i class="fas fa-code"></i></label>
                                <input type="text" name="code" class="form-control" placeholder="Verify Code" value="{{ old('code') }}" required>
                            </div>
                            <button type="submit" class="btn btn__base btn-block"><i class="las la-check-circle"></i> @lang('Verify')</button>
                        </form>
                        <small class="mt-4"> <a href="{{ route('user.password.reset') }}">@lang('Try to send again')</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
