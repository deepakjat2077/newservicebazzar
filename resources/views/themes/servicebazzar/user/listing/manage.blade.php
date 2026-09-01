@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Manage — '.$listing->name)

@php
    // Everything below is a real, already-saved field of this listing. The
    // "Info Missing" red tags are derived, not hard-coded: a row only shows
    // one when the column it renders is genuinely empty.
    $firstMobile   = collect($listing->mobile_numbers ?? [])->filter()->first();
    $firstWhatsapp = collect($listing->whatsapp_numbers ?? [])->filter()->first();
    $hasSocial     = collect($listing->social_links ?? [])->flatten()->filter()->isNotEmpty();
    $subCount      = $listing->subcategory->count();
    $yearsInBiz    = $listing->established_year ? (date('Y') - (int) $listing->established_year) : null;
    $openStatus    = $listing->open_24_hours
        ? 'Open 24 Hours'
        : (!empty($listing->business_days) ? 'Business hours set' : null);
    $fullAddress   = $listing->address ?: trim(collect([
        $listing->plot_no ?? null, $listing->building_name ?? null,
        $listing->street_name ?? null, $listing->landmark ?? null,
        $listing->area ?? null, optional($listing->location)->name,
    ])->filter()->implode(', '), ', ');
@endphp

<section class="sb-mng pt-30 pb-80">
    <div class="container">

        <div class="sb-mng-topbar">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-mng-back">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            @if($ownerListings->count() > 1)
                <div class="sb-mng-switcher-wrap">
                    <select class="sb-mng-switcher" id="sbMngSwitcher" aria-label="Select business">
                        @foreach($ownerListings as $ol)
                            <option value="{{ route('user.listing.manage', $ol->id) }}" @selected($ol->id === $listing->id)>{{ $ol->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            @endif
        </div>

        @foreach(['success' => 'success', 'danger' => 'danger', 'info' => 'info'] as $key => $cls)
            @if(session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
        @endforeach

        {{-- ===== Header card ===== --}}
        <div class="sb-mng-head-card">
            <div class="sb-mng-head-media">
                <img src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}" alt="{{ $listing->name }}">
            </div>
            <div class="sb-mng-head-info">
                <div class="sb-mng-head-titlerow">
                    <h1 class="sb-mng-name">{{ $listing->name }}</h1>
                    @if(optional($listing->category)->name)
                        <span class="sb-mng-cat-tag">{{ $listing->category->name }}</span>
                    @endif
                </div>

                <div class="sb-mng-meta">
                    @if($avgRating > 0)
                        <span class="sb-mng-rating">{{ number_format($avgRating, 1) }} ★</span>
                        <span class="sb-mng-rating-count">{{ $totalRatings }} Rating{{ $totalRatings == 1 ? '' : 's' }}</span>
                    @endif
                    @if($listing->trust == 1)<img src="{{ asset('assets/images/trust_2x.gif') }}" class="sb-mng-badge-img" alt="Trusted">@endif
                    @if($listing->verified == 1)<img src="{{ asset('assets/images/verified_2x.gif') }}" class="sb-mng-badge-img" alt="Verified">@endif
                </div>

                <div class="sb-mng-sub">
                    <i class="fas fa-location-dot"></i>
                    {{ $listing->area ? $listing->area.', ' : '' }}{{ optional($listing->location)->name }}
                    @if($yearsInBiz !== null)
                        <span class="sb-mng-dot">•</span>
                        <i class="fas fa-briefcase"></i> {{ $yearsInBiz }} Year{{ $yearsInBiz == 1 ? '' : 's' }} in Business
                    @endif
                </div>

                @if($openStatus)
                    <div class="sb-mng-open">{{ $openStatus }}</div>
                @endif

                <div class="sb-mng-actions">
                    @if($firstMobile)
                        <a href="tel:{{ $firstMobile }}" class="sb-mng-btn sb-mng-btn--call"><i class="fas fa-phone"></i> {{ ($listing->mobile_country_code[0] ?? '+91') }} {{ $firstMobile }}</a>
                    @endif
                    @if($firstWhatsapp)
                        <a href="https://wa.me/91{{ $firstWhatsapp }}" target="_blank" class="sb-mng-btn sb-mng-btn--wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    @endif
                    @if($publicUrl)
                        <a href="{{ $publicUrl }}" target="_blank" class="sb-mng-btn sb-mng-btn--view"><i class="fas fa-up-right-from-square"></i> View Public Page</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Tabs ===== --}}
        <div class="sb-mng-tabs">
            <a href="#sbQuickInfo" class="is-active">Overview</a>
            <a href="{{ route('user.listing.business-catalogue', $listing->id) }}">Catalogue</a>
            @if($publicUrl)<a href="{{ $publicUrl }}#reviewsSection" target="_blank">Reviews</a>@endif
            <a href="{{ route('user.listing.business-photos', $listing->id) }}">Photos</a>
        </div>

        {{-- ===== Promo cards ===== --}}
        <div class="sb-mng-promos">
            <a href="{{ route('user.listing.business-catalogue', $listing->id) }}" class="sb-mng-promo">
                <span class="sb-mng-promo-ico sb-mng-promo-ico--blue"><i class="fas fa-file-lines"></i></span>
                <span class="sb-mng-promo-body"><strong>Upload Rate Card / Catalogue</strong><small>Display clear pricing for informed customers</small></span>
            </a>
            <a href="{{ route('user.listing.business-contact', $listing->id) }}" class="sb-mng-promo">
                <span class="sb-mng-promo-ico sb-mng-promo-ico--green"><i class="fas fa-address-book"></i></span>
                <span class="sb-mng-promo-body"><strong>Add Contact Details</strong><small>Multiple contact details ensure easy customer access</small></span>
            </a>
            <a href="{{ route('user.listing.business-photos', $listing->id) }}" class="sb-mng-promo">
                <span class="sb-mng-promo-ico sb-mng-promo-ico--orange"><i class="fas fa-images"></i></span>
                <span class="sb-mng-promo-body"><strong>Add Photos</strong><small>Attract more customers by adding more photos</small></span>
            </a>
        </div>

        {{-- ===== For Your Business ===== --}}
        <div class="sb-mng-tiles">
            <a href="{{ route('user.listing.leads', $listing->id) }}" class="sb-mng-tile sb-mng-tile--mint"><i class="fas fa-inbox"></i> Leads &amp; Enquiries</a>
            <a href="{{ route('user.listing.analytics', $listing->id) }}" class="sb-mng-tile sb-mng-tile--peach"><i class="fas fa-chart-line"></i> Analytics</a>
            <a href="{{ route('user.listing.ratings-qr', $listing->id) }}" class="sb-mng-tile sb-mng-tile--sky"><i class="fas fa-qrcode"></i> Ratings QR</a>
        </div>

        {{-- ===== Quick Information ===== --}}
        <div class="sb-mng-section" id="sbQuickInfo">
            <h5 class="sb-mng-section-title">Quick Information</h5>

            @php
                $rows = [
                    ['icon' => 'fa-id-card',          'title' => 'Business Name',            'value' => $listing->name, 'route' => route('user.listing.business-name', $listing->id), 'pending' => $listing->name_approval_status === 'pending' ? 'Pending approval' : null],
                    ['icon' => 'fa-address-card',     'title' => 'Contact Details',          'value' => $firstMobile ?: (collect($listing->extra_emails ?? [])->filter()->first() ?: null), 'route' => route('user.listing.business-contact', $listing->id)],
                    ['icon' => 'fa-location-dot',     'title' => 'Business Address',         'value' => $fullAddress ?: null, 'route' => route('user.listing.business-address', $listing->id), 'pending' => $listing->address_approval_status === 'pending' ? 'Pending approval' : null],
                    ['icon' => 'fa-clock',            'title' => 'Business Hours',           'value' => $openStatus, 'route' => route('user.listing.business-timings', $listing->id)],
                    ['icon' => 'fa-tags',             'title' => 'Business Categories',      'value' => $subCount ? $subCount.' selected' : null, 'route' => route('user.listing.business-categories', $listing->id)],
                    ['icon' => 'fa-map',              'title' => 'Map Location',             'value' => $listing->latitude ? 'Pinned on map' : null, 'route' => route('user.listing.map-location', $listing->id)],
                    ['icon' => 'fa-map-location-dot', 'title' => 'Serviceable Area / Pincodes', 'value' => $listing->receive_leads_other_cities ? 'Also other cities' : (optional($listing->location)->name ? optional($listing->location)->name.' only' : null), 'route' => route('user.listing.serviceable-area', $listing->id)],
                    ['icon' => 'fa-calendar-check',   'title' => 'Year of Establishment',    'value' => $listing->established_year ?: null, 'route' => route('user.listing.established', $listing->id)],
                    ['icon' => 'fa-globe',            'title' => 'Website',                  'value' => $listing->website ?: null, 'route' => route('user.listing.business-website', $listing->id)],
                    ['icon' => 'fa-share-nodes',      'title' => 'Social Media',             'value' => $hasSocial ? 'Added' : null, 'route' => route('user.listing.business-social', $listing->id)],
                    ['icon' => 'fa-images',           'title' => 'Photos & Videos',          'value' => ($galleryCount ? $galleryCount.' photo'.($galleryCount == 1 ? '' : 's') : null).($listing->youtube_video_url ? ' + video' : ''), 'route' => route('user.listing.business-photos', $listing->id)],
                    ['icon' => 'fa-clipboard-list',   'title' => 'Rate Card / Catalogue',    'value' => $catalogueCount ? $catalogueCount.' item'.($catalogueCount == 1 ? '' : 's') : null, 'route' => route('user.listing.business-catalogue', $listing->id)],
                ];
            @endphp

            <div class="sb-mng-rows">
                @foreach($rows as $row)
                    @php $val = trim((string) ($row['value'] ?? '')); @endphp
                    <div class="sb-mng-row">
                        <span class="sb-mng-row-ico"><i class="fas {{ $row['icon'] }}"></i></span>
                        <div class="sb-mng-row-body">
                            <div class="sb-mng-row-title">
                                {{ $row['title'] }}
                                @if($val === '')
                                    <span class="sb-mng-tag sb-mng-tag--red"><i class="fas fa-circle-exclamation"></i> Info Missing</span>
                                @elseif(!empty($row['pending']))
                                    <span class="sb-mng-tag sb-mng-tag--amber">{{ $row['pending'] }}</span>
                                @endif
                            </div>
                            <div class="sb-mng-row-value">{{ $val !== '' ? $val : 'Not added yet' }}</div>
                        </div>
                        <a href="{{ $row['route'] }}" class="sb-mng-edit"><i class="fas fa-pen"></i> Edit</a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== Business Tools ===== --}}
        <div class="sb-mng-section">
            <h5 class="sb-mng-section-title">Business Tools</h5>
            <div class="sb-mng-tool-list">
                <a href="{{ route('user.listing.ratings-qr', $listing->id) }}" class="sb-mng-tool"><i class="fas fa-qrcode"></i><span>Share Ratings QR Code</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
                @if($publicUrl)
                    <a href="{{ $publicUrl }}#reviewsSection" target="_blank" class="sb-mng-tool"><i class="fas fa-comments"></i><span>Reply to Reviews</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
                @endif
                <a href="{{ route('user.listing.import-leads', $listing->id) }}" class="sb-mng-tool"><i class="fas fa-file-import"></i><span>Import Leads</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
                <a href="{{ route('user.listing.competitors', $listing->id) }}" class="sb-mng-tool"><i class="fas fa-users"></i><span>Compare Competitors</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
                <a href="{{ route('user.listing.analytics', $listing->id) }}" class="sb-mng-tool"><i class="fas fa-chart-column"></i><span>Analytics</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
            </div>
        </div>

        {{-- ===== Support ===== --}}
        <div class="sb-mng-section">
            <h5 class="sb-mng-section-title">Support</h5>
            <div class="sb-mng-tool-list">
                <a href="{{ route('user.listing.leads', $listing->id) }}" class="sb-mng-tool"><i class="fas fa-inbox"></i><span>Leads &amp; Enquiries</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
                <a href="{{ route('contact') }}" class="sb-mng-tool"><i class="fas fa-headset"></i><span>Customer Support</span><i class="fas fa-chevron-right sb-mng-tool-chev"></i></a>
            </div>
        </div>

    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-mng-topbar{ display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:18px; flex-wrap:wrap; }
    .sb-mng-back{ color:#444; font-size:14px; font-weight:600; }
    .sb-mng-back i{ margin-right:6px; }
    .sb-mng-switcher-wrap{ position:relative; display:inline-flex; align-items:center; min-width:220px; max-width:100%; }
    .sb-mng-switcher{ width:100%; appearance:none; -webkit-appearance:none; border:1px solid #d8e6ff; border-radius:10px; background:#f7fbff; color:#162033; font-size:14px; font-weight:700; padding:8px 38px 8px 14px; cursor:pointer; }
    .sb-mng-switcher-wrap > i{ position:absolute; right:14px; color:#1e90ff; font-size:12px; pointer-events:none; }

    /* Header card */
    .sb-mng-head-card{ display:flex; gap:20px; border:1px solid #e6e8eb; border-radius:12px; padding:20px; margin-bottom:22px; background:#fff; }
    .sb-mng-head-media{ width:190px; flex-shrink:0; }
    .sb-mng-head-media img{ width:190px; height:150px; object-fit:cover; border-radius:10px; }
    .sb-mng-head-info{ flex:1; min-width:0; }
    .sb-mng-head-titlerow{ display:flex; align-items:flex-start; gap:12px; flex-wrap:wrap; }
    .sb-mng-name{ font-size:26px; font-weight:800; color:#1a1a1a; margin:0; line-height:1.2; }
    .sb-mng-cat-tag{ background:#f1f3f5; color:#444; font-size:12px; font-weight:700; border-radius:6px; padding:5px 12px; margin-top:4px; }
    .sb-mng-meta{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-top:10px; }
    .sb-mng-rating{ background:#188f4b; color:#fff; font-size:13px; font-weight:700; border-radius:6px; padding:3px 9px; }
    .sb-mng-rating-count{ font-size:13px; color:#666; }
    .sb-mng-badge-img{ height:20px; }
    .sb-mng-sub{ margin-top:10px; font-size:14px; color:#555; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .sb-mng-sub i{ color:#1e90ff; }
    .sb-mng-dot{ color:#ccc; }
    .sb-mng-open{ margin-top:6px; font-size:14px; font-weight:700; color:#188f4b; }
    .sb-mng-actions{ display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
    .sb-mng-btn{ display:inline-flex; align-items:center; gap:8px; border-radius:8px; padding:10px 18px; font-size:14px; font-weight:700; }
    .sb-mng-btn--call{ background:#188f4b; color:#fff; }
    .sb-mng-btn--wa{ background:#fff; color:#222; border:1px solid #d5d9dd; }
    .sb-mng-btn--wa i{ color:#25d366; }
    .sb-mng-btn--view{ background:#1e90ff; color:#fff; }
    .sb-mng-btn:hover{ opacity:.92; text-decoration:none; }

    /* Tabs */
    .sb-mng-tabs{ display:flex; gap:26px; border-bottom:1px solid #e6e8eb; margin-bottom:22px; overflow-x:auto; }
    .sb-mng-tabs a{ padding:12px 2px; font-size:15px; font-weight:600; color:#666; border-bottom:2px solid transparent; white-space:nowrap; }
    .sb-mng-tabs a.is-active{ color:#1e90ff; border-bottom-color:#1e90ff; }
    .sb-mng-tabs a:hover{ color:#1e90ff; text-decoration:none; }

    /* Promo cards */
    .sb-mng-promos{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:22px; }
    @media (max-width:820px){ .sb-mng-promos{ grid-template-columns:1fr; } }
    .sb-mng-promo{ display:flex; gap:14px; align-items:center; border:1px solid #e6e8eb; border-radius:10px; padding:16px; background:#fff; color:#222; }
    .sb-mng-promo:hover{ border-color:#1e90ff; text-decoration:none; }
    .sb-mng-promo-ico{ width:46px; height:46px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; flex-shrink:0; }
    .sb-mng-promo-ico--blue{ background:#6fb1f2; }
    .sb-mng-promo-ico--green{ background:#7bc696; }
    .sb-mng-promo-ico--orange{ background:#f6a97a; }
    .sb-mng-promo-body strong{ display:block; font-size:14px; color:#222; }
    .sb-mng-promo-body small{ display:block; font-size:12px; color:#888; margin-top:3px; }

    /* Tiles */
    .sb-mng-tiles{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:30px; }
    @media (max-width:820px){ .sb-mng-tiles{ grid-template-columns:1fr; } }
    .sb-mng-tile{ display:flex; align-items:center; gap:12px; border-radius:10px; padding:16px 18px; font-size:15px; font-weight:700; color:#222; }
    .sb-mng-tile i{ font-size:18px; }
    .sb-mng-tile--mint{ background:#e7f7ef; } .sb-mng-tile--mint i{ color:#188f4b; }
    .sb-mng-tile--peach{ background:#fdeee4; } .sb-mng-tile--peach i{ color:#e07c3e; }
    .sb-mng-tile--sky{ background:#e8f1fe; } .sb-mng-tile--sky i{ color:#1e90ff; }
    .sb-mng-tile:hover{ text-decoration:none; opacity:.92; }

    /* Sections */
    .sb-mng-section{ margin-bottom:30px; }
    .sb-mng-section-title{ font-size:20px; font-weight:800; color:#1a1a1a; margin-bottom:16px; }

    /* Info rows */
    .sb-mng-rows{ border:1px solid #eceef0; border-radius:12px; overflow:hidden; }
    .sb-mng-row{ display:flex; align-items:center; gap:16px; padding:16px 18px; border-bottom:1px solid #f1f2f4; background:#fff; }
    .sb-mng-row:last-child{ border-bottom:0; }
    .sb-mng-row-ico{ width:38px; height:38px; border-radius:10px; background:#eef4ff; color:#1e90ff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
    .sb-mng-row-body{ flex:1; min-width:0; }
    .sb-mng-row-title{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-size:14.5px; font-weight:700; color:#222; }
    .sb-mng-row-value{ font-size:13px; color:#888; margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sb-mng-tag{ font-size:11px; font-weight:700; border-radius:4px; padding:2px 8px; }
    .sb-mng-tag--red{ background:#fdecec; color:#d9364f; }
    .sb-mng-tag--amber{ background:#fff6e8; color:#a8710a; }
    .sb-mng-edit{ display:inline-flex; align-items:center; gap:6px; border:1px solid #d5d9dd; border-radius:8px; padding:7px 16px; font-size:13px; font-weight:700; color:#1e90ff; flex-shrink:0; }
    .sb-mng-edit:hover{ border-color:#1e90ff; background:#eef4ff; text-decoration:none; color:#1e90ff; }

    /* Tool list */
    .sb-mng-tool-list{ border:1px solid #eceef0; border-radius:12px; overflow:hidden; }
    .sb-mng-tool{ display:flex; align-items:center; gap:14px; padding:15px 18px; border-bottom:1px solid #f1f2f4; background:#fff; color:#222; font-size:14.5px; font-weight:600; }
    .sb-mng-tool:last-child{ border-bottom:0; }
    .sb-mng-tool:hover{ background:#f7faff; text-decoration:none; color:#222; }
    .sb-mng-tool > i:first-child{ color:#1e90ff; width:22px; text-align:center; font-size:16px; }
    .sb-mng-tool span{ flex:1; }
    .sb-mng-tool-chev{ color:#bbb; font-size:13px; }

    @media (max-width:560px){
        .sb-mng-head-card{ flex-direction:column; }
        .sb-mng-head-media, .sb-mng-head-media img{ width:100%; }
        .sb-mng-name{ font-size:22px; }
        .sb-mng-row{ flex-wrap:wrap; }
        .sb-mng-edit{ margin-left:54px; }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var sw = document.getElementById('sbMngSwitcher');
    if (sw) sw.addEventListener('change', function(){ if (this.value) window.location.href = this.value; });
});
</script>
@endpush
