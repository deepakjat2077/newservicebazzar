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

        {{-- "My Business" — wide action rows, the other shape the same set
             of shortcuts takes on the dashboard (the circular grid above is
             the same actions, different layout). Leads and Support open
             real pages this site already has; Business Profile opens the
             same real side panel as the grid's Edit Profile card. The rest
             have no working feature behind them yet, same as above. --}}
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
            <button type="button" class="sb-biz-panel-row" id="sbOpenNameEdit">
                <i class="fas fa-id-card"></i>
                <div>
                    <strong>Business Name</strong>
                    <span>{{ $listing->name }}</span>
                    @if($listing->name_approval_status === 'pending')
                        <small class="sb-biz-panel-pending">Pending approval: "{{ $listing->pending_name }}"</small>
                    @endif
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </button>

            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-panel-row">
                <i class="fas fa-address-card"></i>
                <div>
                    <strong>Contact Details</strong>
                    <span>{{ collect($listing->mobile_numbers ?? [])->filter()->first() ?: (collect($listing->extra_emails ?? [])->filter()->first() ?: 'Not added yet') }}</span>
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </a>

            <button type="button" class="sb-biz-panel-row" id="sbOpenAddressEdit">
                <i class="fas fa-location-dot"></i>
                <div>
                    <strong>Business Address</strong>
                    <span>{{ $listing->address ?: 'Not added yet' }}</span>
                    @if($listing->address_approval_status === 'pending')
                        <small class="sb-biz-panel-pending">Pending approval</small>
                    @endif
                </div>
                <i class="fas fa-chevron-right sb-biz-panel-chevron"></i>
            </button>

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

        {{-- Business Name sub-screen — the name never changes the moment
             this is submitted; it's sent to requestNameChange(), which
             stores it as pending_name and leaves name_approval_status
             pending, exactly like the main edit form's name field already
             does (see ListingController::update()). --}}
        <div class="sb-biz-panel-sub" id="sbBizPanelNameSub" hidden>
            <div class="sb-biz-panel-head">
                <button type="button" class="sb-biz-panel-back sb-biz-subpanel-back" data-back-to="sbBizPanelNameSub"><i class="fas fa-arrow-left"></i></button>
                <h5>Business Name</h5>
            </div>
            <div class="sb-biz-panel-body">
                <div class="sb-biz-panel-info-banner">
                    <i class="fas fa-circle-info"></i>
                    Enter your business name exactly as you want it to appear to customers.
                    Changes are reviewed by our team before they go live.
                </div>

                <div class="sb-biz-panel-field">
                    <label>Enter Business Name</label>
                    <input type="text" id="sbBizNameInput" class="sb-input" value="{{ $listing->pending_name ?: $listing->name }}">
                </div>

                <p class="sb-biz-panel-msg" id="sbBizNameMsg"></p>
            </div>
            <div class="sb-biz-panel-footer">
                <button type="button" class="sb-wizard-primary-btn" id="sbBizNameSaveBtn">Save and Continue</button>
            </div>
        </div>

        {{-- Business Address sub-screen — same pending-approval rule. --}}
        <div class="sb-biz-panel-sub" id="sbBizPanelAddressSub" hidden>
            <div class="sb-biz-panel-head">
                <button type="button" class="sb-biz-panel-back sb-biz-subpanel-back" data-back-to="sbBizPanelAddressSub"><i class="fas fa-arrow-left"></i></button>
                <h5>Business Address</h5>
            </div>
            <div class="sb-biz-panel-body">
                <div class="sb-biz-panel-info-banner">
                    <i class="fas fa-circle-info"></i>
                    Changes to your address are reviewed by our team before they go live.
                </div>

                <div class="sb-biz-panel-field">
                    <label>Enter Business Address</label>
                    <textarea id="sbBizAddressInput" class="sb-input sb-biz-panel-textarea">{{ $listing->pending_address ?: $listing->address }}</textarea>
                </div>

                <p class="sb-biz-panel-msg" id="sbBizAddressMsg"></p>
            </div>
            <div class="sb-biz-panel-footer">
                <button type="button" class="sb-wizard-primary-btn" id="sbBizAddressSaveBtn">Save and Continue</button>
            </div>
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

    // Business Profile sub-screens (Business Name / Business Address) —
    // slide over the row list, back arrow returns to it.
    var csrf = '{{ csrf_token() }}';

    function openSubPanel(id){ document.getElementById(id).hidden = false; }
    function closeSubPanel(id){ document.getElementById(id).hidden = true; }

    var openNameBtn = document.getElementById('sbOpenNameEdit');
    if (openNameBtn) openNameBtn.addEventListener('click', function(){ openSubPanel('sbBizPanelNameSub'); });

    var openAddressBtn = document.getElementById('sbOpenAddressEdit');
    if (openAddressBtn) openAddressBtn.addEventListener('click', function(){ openSubPanel('sbBizPanelAddressSub'); });

    document.querySelectorAll('.sb-biz-subpanel-back').forEach(function(btn){
        btn.addEventListener('click', function(){ closeSubPanel(btn.getAttribute('data-back-to')); });
    });

    function postJson(url, body){
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        }).then(function(r){ return r.json(); });
    }

    var nameSaveBtn = document.getElementById('sbBizNameSaveBtn');
    if (nameSaveBtn) {
        nameSaveBtn.addEventListener('click', function(){
            var msg = document.getElementById('sbBizNameMsg');
            var value = document.getElementById('sbBizNameInput').value.trim();

            if (!value) { msg.textContent = 'Business name cannot be empty.'; msg.className = 'sb-biz-panel-msg is-error'; return; }

            postJson('{{ route('user.listing.request-name-change', $listing->id) }}', { name: value })
                .then(function(data){
                    if (data.status === 'unchanged') {
                        msg.textContent = 'That\'s already your business name.';
                        msg.className = 'sb-biz-panel-msg';
                    } else {
                        msg.textContent = data.message || 'Sent for admin approval.';
                        msg.className = 'sb-biz-panel-msg is-success';
                    }
                })
                .catch(function(){
                    msg.textContent = 'Something went wrong — please try again.';
                    msg.className = 'sb-biz-panel-msg is-error';
                });
        });
    }

    var addressSaveBtn = document.getElementById('sbBizAddressSaveBtn');
    if (addressSaveBtn) {
        addressSaveBtn.addEventListener('click', function(){
            var msg = document.getElementById('sbBizAddressMsg');
            var value = document.getElementById('sbBizAddressInput').value.trim();

            if (!value) { msg.textContent = 'Business address cannot be empty.'; msg.className = 'sb-biz-panel-msg is-error'; return; }

            postJson('{{ route('user.listing.request-address-change', $listing->id) }}', { address: value })
                .then(function(data){
                    if (data.status === 'unchanged') {
                        msg.textContent = 'That\'s already your business address.';
                        msg.className = 'sb-biz-panel-msg';
                    } else {
                        msg.textContent = data.message || 'Sent for admin approval.';
                        msg.className = 'sb-biz-panel-msg is-success';
                    }
                })
                .catch(function(){
                    msg.textContent = 'Something went wrong — please try again.';
                    msg.className = 'sb-biz-panel-msg is-error';
                });
        });
    }
});
</script>
@endpush
