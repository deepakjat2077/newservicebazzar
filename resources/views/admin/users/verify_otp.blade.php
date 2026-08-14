@extends('admin.layouts.master')

@section('title','Verify OTP')

@section('content')

<div class="row">
    <div class="col-md-5 mx-auto">
        <div class="card">

            <div class="card-header text-center">
                <h5>Verify OTP</h5>
                <p class="mb-0">Enter the OTP sent to {{ $email }}</p>
            </div>

            <div class="card-body">

                <!-- Verify OTP Form -->
                <form method="POST" action="{{ route('admin.users.otp.verify') }}">
                    @csrf

                    <div class="form-group text-center">
                        <label><i class="fas fa-key"></i> Enter OTP</label>
                        <input type="text" name="otp" class="form-control text-center" placeholder="Enter OTP" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Verify OTP
                    </button>
                </form>

                <!-- Resend OTP -->
                <form method="POST" action="{{ route('admin.users.otp.resend') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-dark btn-block">
                        Resend OTP
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>

@endsection