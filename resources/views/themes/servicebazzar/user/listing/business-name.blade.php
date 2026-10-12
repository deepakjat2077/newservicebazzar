@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Business Name — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'profile' ? route('user.listing.profile', $listing->id) : (request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id)) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Business Name</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('danger'))
            <div class="alert alert-danger">{{ session('danger') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <div class="sb-biz-panel-info-banner">
            <i class="fas fa-circle-info"></i>
            Enter your business name exactly as you want it to appear to customers.
            Changes are reviewed by our team before they go live.
        </div>

        @if($listing->name_approval_status === 'pending')
            <div class="sb-biz-panel-info-banner sb-biz-pending-banner">
                <i class="fas fa-clock"></i>
                "{{ $listing->pending_name }}" is waiting for admin approval.
            </div>
        @endif

        <form action="{{ route('user.listing.request-name-change', $listing->id) }}" method="post">
            @csrf
            <div class="sb-biz-panel-field">
                <label>Enter Business Name</label>
                <input type="text" name="name" class="sb-input" value="{{ $listing->pending_name ?: $listing->name }}" required>
            </div>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save and Continue</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-biz-pending-banner{ background:#fff6e8; color:#a8710a; margin-top:-10px; }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:20px; }
</style>
@endpush
