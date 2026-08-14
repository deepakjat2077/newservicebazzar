@extends($themeView.'layouts.site')
@section('content')
@section('title','Verify Your Email Address')
<style>.account-section .account-area .account-content-area {
background-color: #0e0f38;
}

</style>
<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area verification">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="account-form-area">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('A fresh verification link has been sent to your email address.') }}
                            </div>
                        @endif

                        <div class="account-head-area">
                            <i class="far fa-envelope-open"></i>
                        </div>

                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        {{ __('If you did not receive the email') }},

                        <a href="{{ route('user.verify.resend') }}" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

