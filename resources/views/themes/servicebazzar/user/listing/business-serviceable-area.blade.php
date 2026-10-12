@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Serviceable Area — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'profile' ? route('user.listing.profile', $listing->id) : (request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id)) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Select Area / Pincodes</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('user.listing.serviceable-area.update', $listing->id) }}" method="post">
            @csrf

            <label class="sb-sa-option">
                <input type="radio" name="receive_leads_other_cities" value="0" {{ !$listing->receive_leads_other_cities ? 'checked' : '' }}>
                <span>
                    I want to receive buyer leads from selected city only
                    <small>{{ optional($listing->location)->name ?? 'Your city' }}</small>
                </span>
            </label>

            <label class="sb-sa-option">
                <input type="radio" name="receive_leads_other_cities" value="1" {{ $listing->receive_leads_other_cities ? 'checked' : '' }}>
                <span>I can receive relevant buyer leads from other cities</span>
            </label>

            <p class="sb-an-note sb-sa-note">
                <i class="fas fa-circle-info"></i>
                There's no pincode/area-boundary targeting on this site, so this is the one
                real choice here — it decides whether quote requests from outside
                {{ optional($listing->location)->name ?? 'your city' }} can ever reach you
                when your own city doesn't have enough matching businesses.
            </p>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save and Continue</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-sa-option{
        display:flex;
        align-items:flex-start;
        gap:12px;
        padding:16px 0;
        border-bottom:1px solid #f3f3f3;
        cursor:pointer;
        font-size:14px;
        color:#222;
    }
    .sb-sa-option input{ margin-top:3px; }
    .sb-sa-option small{ display:block; color:#1e90ff; font-weight:600; margin-top:2px; }
    .sb-sa-note{ margin-top:20px; }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:26px; }
</style>
@endpush
