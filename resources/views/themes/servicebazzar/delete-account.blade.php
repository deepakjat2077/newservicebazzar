@extends($themeView.'layouts.site')

@section('content')
@php
    $seoTitle = 'Delete Account - ' . ($setting->site_name ?? 'Service Bazzar');
    $seoDescription = 'Request to delete your Service Bazzar account and all associated data.';
@endphp
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">⚠️ Delete Your Account</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        Once you delete your account, there is no going back. Please be certain.
                    </p>

                    <p class="mb-3">
                        <strong>This will:</strong>
                    </p>
                    <ul class="text-muted small mb-4">
                        <li>Delete your profile and all personal data</li>
                        <li>Remove all your listings</li>
                        <li>Delete all your reviews and ratings</li>
                        <li>Remove you from all favorites</li>
                    </ul>

                    <form method="POST" action="{{ route('delete.account.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <strong>Registered Phone Number</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" placeholder="Enter your phone number"
                                   value="{{ session('phone') ?? old('phone') }}" required>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                We'll send a verification OTP to this number
                            </small>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                I understand that this action is <strong>permanent</strong> and cannot be undone
                            </label>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 mb-3">
                            Continue to Delete Account
                        </button>
                    </form>

                    <div class="border-top pt-3 mt-3">
                        <p class="text-muted small mb-0">
                            <strong>Changed your mind?</strong>
                            <a href="/">Go back to homepage</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4" role="alert">
                <h6 class="alert-heading">ℹ️ About Account Deletion</h6>
                <small>
                    Your account deletion request will be processed within 24 hours.
                    We may retain some data as required by law. For questions,
                    please contact <a href="mailto:{{ $setting->support_email ?? 'support@servicebazzar.com' }}">our support team</a>.
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border-radius: 8px;
    }
</style>
@endsection
