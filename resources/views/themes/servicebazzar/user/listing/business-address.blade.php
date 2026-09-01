@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Business Address — '.$listing->name)

@php
    // Mirror the public detail page: some listings store a single `address`,
    // others only have the separate component fields (plot_no, building_name,
    // street_name, landmark, area, city, state, pincode). Prefill the box with
    // whichever the listing actually has, so an existing address isn't shown
    // as blank.
    $composedAddress = collect([
        $listing->plot_no ?? null,
        $listing->building_name ?? null,
        $listing->street_name ?? null,
        $listing->landmark ?? null,
        $listing->area ?? null,
        $listing->city ?? null,
        $listing->state ?? null,
        $listing->pincode ?? null,
    ])->filter()->implode(', ');

    $prefillAddress = $listing->pending_address ?: ($listing->address ?: $composedAddress);
@endphp

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Business Address</h4>
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
            Changes to your address are reviewed by our team before they go live.
        </div>

        @if($listing->address_approval_status === 'pending')
            <div class="sb-biz-panel-info-banner sb-biz-pending-banner">
                <i class="fas fa-clock"></i>
                Your new address is waiting for admin approval: "{{ $listing->pending_address }}"
            </div>
        @endif

        <form action="{{ route('user.listing.request-address-change', $listing->id) }}" method="post">
            @csrf
            <div class="sb-biz-panel-field">
                <label>Enter Business Address</label>
                <textarea name="address" class="sb-input sb-biz-panel-textarea" required>{{ $prefillAddress }}</textarea>
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
