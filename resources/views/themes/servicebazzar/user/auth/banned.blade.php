@extends($themeView.'layouts.site')
@section('content')
@section('title','Banned')

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
                            <i class="fas fa-ban text-danger"></i>
                        </div>

                        {{ __('Your account has been banned by admin') }}
                    
                        <a href="{{ route('user.logout') }}" class="btn btn-link p-0 m-0 align-baseline">{{ __('Logout') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

