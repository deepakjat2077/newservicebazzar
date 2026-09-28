@extends($themeView.'layouts.app')
@section('content')
@section('title', $listing->name.' — Business Dashboard')

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.index') }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                @if(isset($ownerListings) && $ownerListings->count() > 1)
                    <div class="sb-biz-switcher-wrap">
                        <select class="sb-biz-switcher" id="sbBusinessSwitcher" aria-label="Select business profile">
                            @foreach($ownerListings as $ownerListing)
                                <option value="{{ route('user.listing.dashboard', $ownerListing->id) }}" @selected($ownerListing->id === $listing->id)>
                                    {{ $ownerListing->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                @else
                    <h4>{{ $listing->name }}</h4>
                @endif
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
        </div>

        <div class="sb-biz-stats">
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'today']) }}" class="sb-biz-stat">
                <span class="sb-biz-stat-label">Today's Enquiries</span>
                <strong class="sb-biz-stat-value">{{ $todayCount }}</strong>
            </a>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'hot']) }}" class="sb-biz-stat">
                <span class="sb-biz-stat-label">Hot Enquiries <small>(last 24h)</small></span>
                <strong class="sb-biz-stat-value">{{ $hotCount }}</strong>
            </a>
            <a href="{{ route('user.listing.leads', $listing->id) }}" class="sb-biz-stat">
                <span class="sb-biz-stat-label">Missed Enquiries</span>
                <strong class="sb-biz-stat-value sb-biz-stat-value--danger">{{ $missedCount }}</strong>
            </a>
            <a href="{{ route('user.listing.leads', $listing->id) }}" class="sb-biz-stat">
                <span class="sb-biz-stat-label">Total Enquiries</span>
                <strong class="sb-biz-stat-value">{{ $totalCount }}</strong>
            </a>
            <a href="{{ route('user.listing.analytics', $listing->id) }}" class="sb-biz-stat">
                <span class="sb-biz-stat-label">Responded</span>
                <strong class="sb-biz-stat-value">{{ $respondedCount }}</strong>
                <small class="sb-biz-stat-pct {{ $respondedPercent < 50 ? 'is-poor' : 'is-good' }}">{{ $respondedPercent }}% {{ $respondedPercent < 50 ? 'Poor' : 'Good' }}</small>
            </a>
            <button type="button" class="sb-biz-viewmore" id="sbBizViewMoreBtn">
                View More <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        @php
            $sbReviewsUrl = $listing->category
                ? route('listings.details', [$listing->category->slug, $listing->slug])
                : null;

            // Real, derived — not a fixed badge: this only says "Info
            // Missing" when a field the Business Profile panel actually
            // shows is genuinely empty.
            $sbProfileInfoMissing = blank($listing->category)
                || blank(collect($listing->mobile_numbers ?? [])->filter()->first())
                || empty($listing->business_days);
        @endphp

        {{-- Quick actions — Edit Profile, Reviews and Add Photos open real
             screens this site already has; the rest are the same shortcuts
             JustDial's owner panel offers but nothing exists behind them yet
             on this site, so they're marked coming soon rather than wired to
             nothing. --}}
        <div class="sb-biz-actions">
            <button type="button" class="sb-biz-action-card" id="sbOpenBusinessProfile">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--peach"><i class="fas fa-id-card"></i></span>
                <span>Edit Profile <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </button>
            <a href="{{ route('user.listing.advertise', $listing->id) }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--purple"><i class="fas fa-bullhorn"></i></span>
                <span>Advertise <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            <a href="{{ $sbReviewsUrl ?? '#' }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--blue"><i class="fas fa-star"></i></span>
                <span>Reviews <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            <a href="{{ route('user.listing.serviceable-area', $listing->id) }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--yellow"><i class="fas fa-location-dot"></i></span>
                <span>Serviceable Area / Pincodes <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            {{-- This pointed at the old all-in-one edit form's #images anchor. The
                 gallery screen (business-photos) is where photos are actually
                 managed — the same screen Add Video already opened. --}}
            <a href="{{ route('user.listing.business-photos', $listing->id) }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--green"><i class="fas fa-images"></i></span>
                <span>Add Photos <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--teal"><i class="fas fa-address-book"></i></span>
                <span>Add Contact</span>
                <small>Coming soon</small>
            </div>
            <a href="{{ route('user.listing.business-catalogue', $listing->id) }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--gray"><i class="fas fa-file-lines"></i></span>
                <span>Upload Catalogue <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--gold"><i class="fas fa-gear"></i></span>
                <span>Add Offer</span>
                <small>Coming soon</small>
            </div>
            <a href="{{ route('user.listing.business-photos', $listing->id) }}#video" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--pink"><i class="fas fa-video"></i></span>
                <span>Add Video <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            <a href="{{ route('user.listing.business-social', $listing->id) }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--mint"><i class="fas fa-share-nodes"></i></span>
                <span>Add Social Links <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
            <a href="{{ route('user.listing.ratings-qr', $listing->id) }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--indigo"><i class="fas fa-qrcode"></i></span>
                <span>Ratings QR <i class="fas fa-pen sb-biz-edit-badge" aria-hidden="true"></i></span>
            </a>
        </div>

        {{-- "My Business" — wide action rows, the other shape the same set
             of shortcuts takes on the dashboard (the circular grid above is
             the same actions, different layout). Leads and Support open
             real pages this site already has; Business Profile opens the
             same real side panel as the grid's Edit Profile card. The rest
             have no working feature behind them yet, same as above. --}}
        <div class="sb-biz-wide-list">
            <h5>My Business</h5>

            <a href="{{ route('user.listing.advertise', $listing->id) }}" class="sb-biz-wide-card">
                <i class="fas fa-bullhorn"></i>
                <div class="sb-biz-wide-body">
                    <div class="sb-biz-wide-title">Advertise on Service Bazzar</div>
                    <p>Reach more customers searching for your category</p>
                </div>
                <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
            </a>

            <a href="{{ route('user.guest-chats.index') }}" class="sb-biz-wide-card">
                <i class="fas fa-envelope"></i>
                <div class="sb-biz-wide-body">
                    <div class="sb-biz-wide-title">Leads &amp; Enquiries</div>
                    <p>View leads and respond to enquiries</p>
                </div>
                <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
            </a>

            <button type="button" class="sb-biz-wide-card" id="sbOpenBusinessProfileWide">
                <i class="fas fa-id-card"></i>
                <div class="sb-biz-wide-body">
                    <div class="sb-biz-wide-title">
                        Business Profile
                        @if($sbProfileInfoMissing)
                            <span class="sb-biz-wide-badge sb-biz-wide-badge--red"><i class="fas fa-circle-exclamation"></i> Info Missing</span>
                        @endif
                    </div>
                    <p>Update category, contact numbers &amp; business timings</p>
                </div>
                <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
            </button>

            <div class="sb-biz-wide-grid-2">
                <a href="{{ route('user.listing.business-catalogue', $listing->id) }}" class="sb-biz-wide-card">
                    <i class="fas fa-file-lines"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">Catalogue</div>
                        <p>Showcase products &amp; services</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </a>

                <div class="sb-biz-wide-card is-disabled">
                    <i class="fas fa-toolbox"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">Business Tools</div>
                        <p>Manage offers, reviews and more</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </div>

                <a href="{{ route('user.listing.kyc', $listing->id) }}" class="sb-biz-wide-card">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">KYC, Payments &amp; Invoices</div>
                        <p>Update KYC details</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </a>

                <div class="sb-biz-wide-card is-disabled">
                    <i class="fas fa-award"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">
                            Additional Business Info
                            <span class="sb-biz-wide-badge sb-biz-wide-badge--red"><i class="fas fa-circle-exclamation"></i> Info Missing</span>
                        </div>
                        <p>Financial service providers, broker &amp; insurance types and more</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </div>

                <a href="{{ route('contact') }}" class="sb-biz-wide-card">
                    <i class="fas fa-headset"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">Support</div>
                        <p>Connect with us</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </a>
            </div>
        </div>

        <div class="sb-biz-leads">
            <h5>Recent Enquiries</h5>

            @if($recentEnquiries->isEmpty())
                <p class="sb-wizard-empty">No enquiries yet for this business.</p>
            @else
                <div class="sb-biz-lead-list">
                    @foreach($recentEnquiries as $lead)
                        <div class="sb-biz-lead-row">
                            <div>
                                <strong>{{ $lead->name ?: 'Unknown' }}</strong>
                                <small>{{ $lead->phone }}</small>
                            </div>
                            <div class="sb-biz-lead-meta">
                                <span class="sb-biz-lead-status {{ $lead->is_read ? 'is-read' : 'is-unread' }}">
                                    {{ $lead->is_read ? 'Responded' : 'Missed' }}
                                </span>
                                <small>{{ $lead->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

{{-- "View More" — all 5 are now real, working features. Competitors ranks
     against the real other listings sharing this listing's category and
     city, built off the enquiries table, listing_daily_stats, ratings and
     extra_images — no invented search-volume trend or profile score. --}}
<div class="sb-biz-modal-overlay" id="sbBizModalOverlay">
    <div class="sb-biz-modal">
        <button type="button" class="sb-biz-modal-close" id="sbBizModalClose">&times;</button>
        <div class="sb-biz-modal-grid">
            <a href="{{ route('user.listing.response-time', $listing->id) }}" class="sb-biz-modal-card">
                <i class="fas fa-stopwatch"></i>
                <span>Response Time</span>
            </a>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'type' => 'quotation']) }}" class="sb-biz-modal-card">
                <i class="fas fa-file-invoice"></i>
                <span>Quotation Request</span>
                @if($quotationCount > 0)<small class="sb-biz-modal-count">{{ $quotationCount }}</small>@endif
            </a>
            <a href="{{ route('user.listing.analytics', $listing->id) }}" class="sb-biz-modal-card">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
            <a href="{{ route('user.listing.competitors', $listing->id) }}" class="sb-biz-modal-card">
                <i class="fas fa-users"></i>
                <span>Competitors</span>
            </a>
            <a href="{{ route('user.listing.import-leads', $listing->id) }}" class="sb-biz-modal-card">
                <i class="fas fa-file-import"></i>
                <span>Import Leads</span>
            </a>
        </div>
    </div>
</div>

{{-- Business Profile — a read-summary slide-in of this listing's real,
     already-saved fields. It links out to the existing edit form for actual
     changes rather than duplicating every field's save logic here. --}}
<div class="sb-biz-panel-overlay" id="sbBizProfilePanelOverlay">
    <div class="sb-biz-panel" id="sbBizProfilePanel">
        <div class="sb-biz-panel-head" id="sbBizPanelHeadMain">
            <button type="button" class="sb-biz-panel-back" id="sbBizProfilePanelClose"><i class="fas fa-arrow-left"></i></button>
            <h5>Business Profile</h5>
        </div>

        <div class="sb-biz-panel-body" id="sbBizPanelBodyMain">
            <a href="{{ route('user.listing.business-name', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-id-card"></i>
                <div>
                    <strong>Business Name</strong>
                    <span>{{ $listing->name }}</span>
                    @if($listing->name_approval_status === 'pending')
                        <small class="sb-biz-panel-pending">Pending approval: "{{ $listing->pending_name }}"</small>
                    @endif
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-contact', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-address-card"></i>
                <div>
                    <strong>Contact Details</strong>
                    <span>{{ collect($listing->mobile_numbers ?? [])->filter()->first() ?: (collect($listing->extra_emails ?? [])->filter()->first() ?: 'Not added yet') }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-address', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-location-dot"></i>
                <div>
                    <strong>Business Address</strong>
                    <span>{{ $listing->address ?: 'Not added yet' }}</span>
                    @if($listing->address_approval_status === 'pending')
                        <small class="sb-biz-panel-pending">Pending approval</small>
                    @endif
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.serviceable-area', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-map-location-dot"></i>
                <div>
                    <strong>Serviceable Area / Pincodes</strong>
                    <span>{{ $listing->receive_leads_other_cities ? 'Also receiving leads from other cities' : (optional($listing->location)->name ?? 'Your city').' only' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.map-location', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-map"></i>
                <div>
                    <strong>Map Location</strong>
                    <span>{{ $listing->latitude ? 'Pinned' : 'Not pinned yet' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-timings', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Business Timings</strong>
                    <span>{{ $listing->open_24_hours ? 'Open 24 Hours' : (!empty($listing->business_days) ? 'Set' : 'Not added yet') }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.established', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-calendar-check"></i>
                <div>
                    <strong>Year of Establishment</strong>
                    <span>{{ $listing->established_year ?: 'Not added yet' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-categories', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-tags"></i>
                <div>
                    <strong>Business Categories</strong>
                    <span>{{ $listing->subcategory->isNotEmpty() ? $listing->subcategory->count().' selected' : 'Not added yet' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-photos', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-images"></i>
                <div>
                    <strong>Photos and Videos</strong>
                    @php($sbGalleryCount = $listing->extraImages()->where('type', 'gallery')->count())
                    <span>{{ $sbGalleryCount }} photo{{ $sbGalleryCount === 1 ? '' : 's' }}{{ $listing->youtube_video_url ? ' + video' : '' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-catalogue', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-clipboard-list"></i>
                <div>
                    <strong>Rate Card / Catalogue</strong>
                    @php($sbCatalogueCount = $listing->extraImages()->where('type', 'catalogue')->count())
                    <span>{{ $sbCatalogueCount ? $sbCatalogueCount.' item(s)' : 'Not added yet' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.business-website', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-globe"></i>
                <div>
                    <strong>Business Website</strong>
                    <span>{{ $listing->website ?: 'Not added yet' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            @php($sbHasSocial = collect($listing->social_links ?? [])->flatten()->isNotEmpty())
            <a href="{{ route('user.listing.business-social', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-share-nodes"></i>
                <div>
                    <strong>Social Media</strong>
                    <span>{{ $sbHasSocial ? 'Added' : 'Not added yet' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-list"></i>
                <div>
                    <strong>Business Category</strong>
                    <span>{{ optional($listing->category)->name ?? 'Not set' }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-wizard-primary-btn sb-biz-panel-edit-btn">
                Edit Full Listing
            </a>
        </div>

    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var businessSwitcher = document.getElementById('sbBusinessSwitcher');
    if (businessSwitcher) {
        businessSwitcher.addEventListener('change', function(){
            if (this.value) window.location.href = this.value;
        });
    }

    var openBtn = document.getElementById('sbBizViewMoreBtn');
    var overlay = document.getElementById('sbBizModalOverlay');
    var closeBtn = document.getElementById('sbBizModalClose');

    if (openBtn) openBtn.addEventListener('click', function(){ overlay.classList.add('show'); });
    if (closeBtn) closeBtn.addEventListener('click', function(){ overlay.classList.remove('show'); });
    overlay.addEventListener('click', function(e){ if (e.target === overlay) overlay.classList.remove('show'); });

    var profileOverlay = document.getElementById('sbBizProfilePanelOverlay');
    var profileCloseBtn = document.getElementById('sbBizProfilePanelClose');

    ['sbOpenBusinessProfile', 'sbOpenBusinessProfileWide'].forEach(function(id){
        var btn = document.getElementById(id);
        if (!btn) return;
        btn.addEventListener('click', function(){
            overlay.classList.remove('show');
            profileOverlay.classList.add('show');
        });
    });
    if (profileCloseBtn) {
        profileCloseBtn.addEventListener('click', function(){ profileOverlay.classList.remove('show'); });
    }
    profileOverlay.addEventListener('click', function(e){ if (e.target === profileOverlay) profileOverlay.classList.remove('show'); });

    @if(request()->boolean('profile'))
        profileOverlay.classList.add('show');
    @endif
});
</script>
@endpush
