@extends($themeView.'layouts.app')
@section('content')
@section('title', $listing->name.' — Business Dashboard')

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.index') }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
        </div>

        <div class="sb-biz-stats">
            <div class="sb-biz-stat">
                <span class="sb-biz-stat-label">Today's Enquiries</span>
                <strong class="sb-biz-stat-value">{{ $todayCount }}</strong>
            </div>
            <div class="sb-biz-stat">
                <span class="sb-biz-stat-label">Hot Enquiries <small>(last 24h)</small></span>
                <strong class="sb-biz-stat-value">{{ $hotCount }}</strong>
            </div>
            <div class="sb-biz-stat">
                <span class="sb-biz-stat-label">Missed Enquiries</span>
                <strong class="sb-biz-stat-value sb-biz-stat-value--danger">{{ $missedCount }}</strong>
            </div>
            <div class="sb-biz-stat">
                <span class="sb-biz-stat-label">Total Enquiries</span>
                <strong class="sb-biz-stat-value">{{ $totalCount }}</strong>
            </div>
            <div class="sb-biz-stat">
                <span class="sb-biz-stat-label">Responded</span>
                <strong class="sb-biz-stat-value">{{ $respondedCount }}</strong>
                <small class="sb-biz-stat-pct {{ $respondedPercent < 50 ? 'is-poor' : 'is-good' }}">{{ $respondedPercent }}%</small>
            </div>
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

        {{-- "My Business" — wide action rows, the other shape this same set
             of shortcuts takes on the dashboard (the circular grid further
             down is the same actions, different layout). Leads and Support
             open real pages this site already has; Business Profile opens
             the same real side panel as the grid's Edit Profile card. The
             rest have no working feature behind them yet, same as below. --}}
        <div class="sb-biz-wide-list">
            <h5>My Business</h5>

            <div class="sb-biz-wide-card is-disabled">
                <i class="fas fa-bullhorn"></i>
                <div class="sb-biz-wide-body">
                    <div class="sb-biz-wide-title">
                        Advertise on Service Bazzar
                        <span class="sb-biz-wide-badge sb-biz-wide-badge--green">Coming soon</span>
                    </div>
                    <p>Reach more customers searching for your category</p>
                </div>
                <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
            </div>

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
                <div class="sb-biz-wide-card is-disabled">
                    <i class="fas fa-file-lines"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">Catalogue</div>
                        <p>Showcase products &amp; services</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </div>

                <div class="sb-biz-wide-card is-disabled">
                    <i class="fas fa-toolbox"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">Business Tools</div>
                        <p>Manage offers, reviews and more</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </div>

                <div class="sb-biz-wide-card is-disabled">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <div class="sb-biz-wide-body">
                        <div class="sb-biz-wide-title">KYC, Payments &amp; Invoices</div>
                        <p>Update KYC details</p>
                    </div>
                    <i class="fas fa-chevron-right sb-biz-wide-chevron"></i>
                </div>

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

        {{-- Quick actions — Edit Profile, Reviews and Add Photos open real
             screens this site already has; the rest are the same shortcuts
             JustDial's owner panel offers but nothing exists behind them yet
             on this site, so they're marked coming soon rather than wired to
             nothing. --}}
        <div class="sb-biz-actions">
            <button type="button" class="sb-biz-action-card" id="sbOpenBusinessProfile">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--peach"><i class="fas fa-id-card"></i></span>
                <span>Edit Profile</span>
            </button>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--purple"><i class="fas fa-bullhorn"></i></span>
                <span>Advertise</span>
                <small>Coming soon</small>
            </div>
            <a href="{{ $sbReviewsUrl ?? '#' }}" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--blue"><i class="fas fa-star"></i></span>
                <span>Reviews</span>
            </a>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--yellow"><i class="fas fa-location-dot"></i></span>
                <span>Serviceable Area / Pincodes</span>
                <small>Coming soon</small>
            </div>
            <a href="{{ route('user.listing.edit', $listing->id) }}#images" class="sb-biz-action-card">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--green"><i class="fas fa-images"></i></span>
                <span>Add Photos</span>
            </a>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--teal"><i class="fas fa-address-book"></i></span>
                <span>Add Contact</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--gray"><i class="fas fa-file-lines"></i></span>
                <span>Upload Catalogue</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--gold"><i class="fas fa-gear"></i></span>
                <span>Add Offer</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--pink"><i class="fas fa-video"></i></span>
                <span>Add Video</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--mint"><i class="fas fa-share-nodes"></i></span>
                <span>Add Social Links</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-action-card is-disabled">
                <span class="sb-biz-modal-icon sb-biz-modal-icon--indigo"><i class="fas fa-qrcode"></i></span>
                <span>Ratings QR</span>
                <small>Coming soon</small>
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

{{-- "View More" — back to the original 5 options. There's no
     call-tracking, quotation, or competitor data behind this site, so
     Response Time / Quotation Request / Analytics / Competitors are marked
     coming soon rather than shown with invented numbers; Import Leads is a
     real, working feature. --}}
<div class="sb-biz-modal-overlay" id="sbBizModalOverlay">
    <div class="sb-biz-modal">
        <button type="button" class="sb-biz-modal-close" id="sbBizModalClose">&times;</button>
        <div class="sb-biz-modal-grid">
            <div class="sb-biz-modal-card is-disabled">
                <i class="fas fa-stopwatch"></i>
                <span>Response Time</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-modal-card is-disabled">
                <i class="fas fa-file-invoice"></i>
                <span>Quotation Request</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-modal-card is-disabled">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-modal-card is-disabled">
                <i class="fas fa-users"></i>
                <span>Competitors</span>
                <small>Coming soon</small>
            </div>
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
        <div class="sb-biz-panel-head">
            <button type="button" class="sb-biz-panel-back" id="sbBizProfilePanelClose"><i class="fas fa-arrow-left"></i></button>
            <h5>Business Profile</h5>
        </div>

        <div class="sb-biz-panel-body">
            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-id-card"></i>
                <div>
                    <strong>Business Name</strong>
                    <span>{{ $listing->name }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-address-card"></i>
                <div>
                    <strong>Contact Details</strong>
                    <span>{{ collect($listing->mobile_numbers ?? [])->filter()->first() ?: (collect($listing->extra_emails ?? [])->filter()->first() ?: 'Not added yet') }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-location-dot"></i>
                <div>
                    <strong>Business Address</strong>
                    <span>{{ $listing->address ?: 'Not added yet' }}</span>
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

            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Business Timings</strong>
                    <span>{{ !empty($listing->business_days) ? 'Set' : 'Not added yet' }}</span>
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
<style>
    .sb-biz-header{
        display:flex;
        align-items:center;
        gap:14px;
        padding-bottom:18px;
        margin-bottom:20px;
        border-bottom:1px solid #eceef0;
    }
    .sb-biz-back{ color:#444; font-size:16px; }
    .sb-biz-header-title{ flex:1; }
    .sb-biz-header-title h4{ margin:0; font-size:18px; font-weight:700; color:#222; }
    .sb-biz-header-title span{ font-size:12px; color:#888; }
    .sb-biz-edit-link{
        font-size:13px;
        color:#1e90ff;
        font-weight:600;
        border:1px solid #1e90ff;
        border-radius:6px;
        padding:7px 14px;
    }

    .sb-biz-stats{
        display:grid;
        grid-template-columns:repeat(5,1fr) auto;
        gap:14px;
        align-items:stretch;
        margin-bottom:30px;
    }
    @media (max-width:900px){
        .sb-biz-stats{ grid-template-columns:repeat(2,1fr); }
        .sb-biz-viewmore{ grid-column:span 2; }
    }
    .sb-biz-stat{
        border:1px solid #e6e8eb;
        border-radius:8px;
        padding:14px;
    }
    .sb-biz-stat-label{ display:block; font-size:12px; color:#888; margin-bottom:6px; }
    .sb-biz-stat-label small{ color:#aaa; }
    .sb-biz-stat-value{ font-size:22px; font-weight:700; color:#222; }
    .sb-biz-stat-value--danger{ color:#d9364f; }
    .sb-biz-stat-pct{ display:block; font-size:11px; margin-top:4px; font-weight:600; }
    .sb-biz-stat-pct.is-good{ color:#22a45d; }
    .sb-biz-stat-pct.is-poor{ color:#d9364f; }
    .sb-biz-viewmore{
        border:1px solid #d5d9dd;
        background:#f7f8f9;
        border-radius:8px;
        padding:14px 18px;
        font-size:13px;
        font-weight:600;
        color:#1e90ff;
        cursor:pointer;
        white-space:nowrap;
    }

    .sb-biz-wide-list{ margin-bottom:30px; }
    .sb-biz-wide-list h5{ font-size:20px; font-weight:700; color:#222; margin-bottom:16px; }
    .sb-biz-wide-card{
        display:flex;
        align-items:center;
        gap:16px;
        border:1px solid #e6e8eb;
        border-radius:8px;
        padding:16px 18px;
        margin-bottom:14px;
        text-align:left;
        background:#fff;
        width:100%;
        color:#222;
    }
    .sb-biz-wide-card > i:first-child{ font-size:20px; color:#1e90ff; width:22px; text-align:center; flex-shrink:0; }
    .sb-biz-wide-card.is-disabled{ cursor:default; }
    .sb-biz-wide-card.is-disabled > i:first-child{ color:#bbb; }
    .sb-biz-wide-body{ flex:1; min-width:0; }
    .sb-biz-wide-title{
        display:flex;
        align-items:center;
        flex-wrap:wrap;
        gap:8px;
        font-size:15px;
        font-weight:700;
        color:#222;
    }
    .sb-biz-wide-body p{ margin:3px 0 0; font-size:12.5px; color:#888; }
    .sb-biz-wide-badge{
        font-size:11px;
        font-weight:700;
        border-radius:4px;
        padding:2px 8px;
    }
    .sb-biz-wide-badge--green{ background:#e6f7ec; color:#22a45d; }
    .sb-biz-wide-badge--red{ background:#fdecec; color:#d9364f; }
    .sb-biz-wide-chevron{
        width:30px;
        height:30px;
        border:1px solid #e0e3e7;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        color:#555;
        flex-shrink:0;
    }
    .sb-biz-wide-grid-2{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:0 16px;
    }
    @media (max-width:700px){
        .sb-biz-wide-grid-2{ grid-template-columns:1fr; }
    }
    .sb-biz-wide-grid-2 .sb-biz-wide-card{ align-items:flex-start; }

    .sb-biz-leads h5{ font-size:15px; font-weight:700; color:#222; margin-bottom:14px; }
    .sb-biz-lead-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        border:1px solid #eceef0;
        border-radius:8px;
        padding:12px 14px;
        margin-bottom:8px;
    }
    .sb-biz-lead-row strong{ font-size:14px; color:#222; display:block; }
    .sb-biz-lead-row small{ font-size:12px; color:#888; }
    .sb-biz-lead-meta{ text-align:right; }
    .sb-biz-lead-status{
        display:block;
        font-size:11px;
        font-weight:700;
        margin-bottom:2px;
    }
    .sb-biz-lead-status.is-read{ color:#22a45d; }
    .sb-biz-lead-status.is-unread{ color:#d9364f; }

    .sb-biz-modal-overlay{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.4);
        z-index:3000;
        align-items:center;
        justify-content:center;
        padding:20px;
    }
    .sb-biz-modal-overlay.show{ display:flex; }
    .sb-biz-modal{
        background:#fff;
        border-radius:10px;
        padding:30px 24px 24px;
        width:100%;
        max-width:460px;
        position:relative;
    }
    .sb-biz-modal-close{
        position:absolute;
        top:-16px;
        right:-16px;
        width:34px;
        height:34px;
        border-radius:50%;
        border:0;
        background:#fff;
        box-shadow:0 2px 10px rgba(0,0,0,.18);
        font-size:20px;
        line-height:1;
        color:#333;
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .sb-biz-modal-grid{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:18px 14px;
        margin-top:6px;
    }
    .sb-biz-modal-card{
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:8px;
        text-align:center;
        border:1px solid #e6e8eb;
        border-radius:8px;
        padding:16px 10px;
        font-size:12px;
        font-weight:600;
        color:#333;
    }
    .sb-biz-modal-card i{ font-size:20px; color:#1e90ff; }
    .sb-biz-modal-card.is-disabled{ color:#aaa; cursor:default; }
    .sb-biz-modal-card.is-disabled i{ color:#c3c7cc; }
    .sb-biz-modal-card.is-disabled small{ color:#c3c7cc; font-weight:400; }

    /* Quick-actions grid on the dashboard itself (Edit Profile, Advertise,
       Reviews, ...) — the colourful circular badges. */
    .sb-biz-actions{
        display:grid;
        grid-template-columns:repeat(6,1fr);
        gap:20px 10px;
        margin-bottom:30px;
        padding-bottom:26px;
        border-bottom:1px solid #eceef0;
    }
    @media (max-width:900px){
        .sb-biz-actions{ grid-template-columns:repeat(4,1fr); }
    }
    @media (max-width:560px){
        .sb-biz-actions{ grid-template-columns:repeat(3,1fr); }
    }
    .sb-biz-action-card{
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:8px;
        text-align:center;
        border:0;
        background:transparent;
        padding:0;
        font-size:11.5px;
        font-weight:600;
        color:#333;
    }
    .sb-biz-modal-icon{
        width:52px;
        height:52px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:20px;
        color:#fff;
    }
    .sb-biz-modal-icon--peach{ background:#f6a97a; }
    .sb-biz-modal-icon--purple{ background:#9b7ff0; }
    .sb-biz-modal-icon--blue{ background:#6fb1f2; }
    .sb-biz-modal-icon--yellow{ background:#e9c65f; }
    .sb-biz-modal-icon--green{ background:#7bc696; }
    .sb-biz-modal-icon--teal{ background:#5fbfae; }
    .sb-biz-modal-icon--gray{ background:#b7bcc2; }
    .sb-biz-modal-icon--gold{ background:#d9b45a; }
    .sb-biz-modal-icon--pink{ background:#ea94b4; }
    .sb-biz-modal-icon--mint{ background:#7fd0c2; }
    .sb-biz-modal-icon--indigo{ background:#6f8ff0; }
    .sb-biz-action-card.is-disabled{ color:#aaa; cursor:default; }
    .sb-biz-action-card.is-disabled .sb-biz-modal-icon{ background:#e4e6e9; color:#bbb; }
    .sb-biz-action-card.is-disabled small{ color:#c3c7cc; font-weight:400; display:block; }

    .sb-biz-panel-overlay{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.4);
        z-index:3100;
        justify-content:flex-end;
    }
    .sb-biz-panel-overlay.show{ display:flex; }
    .sb-biz-panel{
        background:#fff;
        width:380px;
        max-width:92vw;
        height:100%;
        overflow-y:auto;
    }
    .sb-biz-panel-head{
        display:flex;
        align-items:center;
        gap:14px;
        padding:18px;
        border-bottom:1px solid #eceef0;
    }
    .sb-biz-panel-back{
        border:0;
        background:transparent;
        font-size:16px;
        color:#333;
        cursor:pointer;
    }
    .sb-biz-panel-head h5{ margin:0; font-size:16px; font-weight:700; color:#222; }
    .sb-biz-panel-body{ padding:6px 18px 24px; }
    .sb-biz-panel-row{
        display:flex;
        align-items:center;
        gap:14px;
        padding:14px 0;
        border-bottom:1px solid #f3f3f3;
        color:#222;
    }
    .sb-biz-panel-row > i:first-child{ font-size:16px; color:#1e90ff; width:20px; text-align:center; }
    .sb-biz-panel-row div{ flex:1; min-width:0; }
    .sb-biz-panel-row strong{ display:block; font-size:13px; font-weight:600; color:#222; }
    .sb-biz-panel-row span{ display:block; font-size:12px; color:#888; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sb-biz-panel-chevron{ font-size:12px; color:#bbb; }
    .sb-biz-panel-edit-btn{ display:block; text-align:center; margin-top:20px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
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
});
</script>
@endpush
