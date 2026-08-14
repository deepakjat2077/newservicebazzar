@extends($themeView.'layouts.site')
@section('title','Verify OTP')
@section('content')
<style>.account-section .account-area .account-content-area {
background-color: #0e0f38;
}

</style>
<section class="account-section pt-120 pb-120">
    <div class="container">
        <div class="account-area">
            <div class="row justify-content-center">
                <div class="col-lg-6 p-0 px-3 px-lg-0">
                    <div class="account-form-area">
                        <div class="account-head-area text-center">
                            <img src="{{ asset('assets/images/logo/logo.png') }}">
                            <p>Enter the OTP sent to {{ $email }}</p>
                        </div>

                        <form method="POST" action="{{ route('user.otp.verify') }}">
                            @csrf
                            <div class="input-area">
                                <label><i class="fas fa-key"></i></label>
                                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
                            </div>
                            <button type="submit" class="btn btn__base btn-block">Verify OTP</button>
                        </form>

                        <form method="POST" action="{{ route('user.otp.resend') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-dark btn-block">Resend OTP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection