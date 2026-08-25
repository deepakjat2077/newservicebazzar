@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Year of Establishment — '.$listing->name)

@php
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
@endphp

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Year of Establishment</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('user.listing.established.update', $listing->id) }}" method="post">
            @csrf
            <div class="sb-contact-row">
                <div class="sb-biz-panel-field sb-contact-grow">
                    <label>Month</label>
                    <select name="established_month" class="sb-input">
                        <option value="">Month</option>
                        @foreach($months as $num => $label)
                            <option value="{{ $num }}" {{ (int) $listing->established_month === $num ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sb-biz-panel-field sb-contact-grow">
                    <label>Year</label>
                    <select name="established_year" class="sb-input">
                        <option value="">Year</option>
                        @for($y = date('Y'); $y >= 1900; $y--)
                            <option value="{{ $y }}" {{ (int) $listing->established_year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-biz-form-submit{ display:block; width:100%; margin-top:24px; }
</style>
@endpush
