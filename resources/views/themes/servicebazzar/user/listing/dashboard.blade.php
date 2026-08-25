@extends($themeView.'layouts.app')
@section('content')
@section('title', $listing->name.' — Business Dashboard')

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.index') }}" class="sb-biz-back"><i class="fa-solid fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
            <a href="{{ route('user.listing.edit', $listing->id) }}" class="sb-biz-edit-link">Edit Listing</a>
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
                View More <i class="fa-solid fa-chevron-right"></i>
            </button>
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

{{-- "View More" action grid — only Import Leads is a real, working feature
     right now. The other four cards are the same options JustDial's owner
     panel shows, but this site has no call-tracking, quotation, or
     competitor data behind them, so they're marked coming soon rather than
     shown with invented numbers. --}}
<div class="sb-biz-modal-overlay" id="sbBizModalOverlay">
    <div class="sb-biz-modal">
        <button type="button" class="sb-biz-modal-close" id="sbBizModalClose">&times;</button>
        <div class="sb-biz-modal-grid">
            <div class="sb-biz-modal-card is-disabled">
                <i class="fa-solid fa-stopwatch"></i>
                <span>Response Time</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-modal-card is-disabled">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Quotation Request</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-modal-card is-disabled">
                <i class="fa-solid fa-chart-line"></i>
                <span>Analytics</span>
                <small>Coming soon</small>
            </div>
            <div class="sb-biz-modal-card is-disabled">
                <i class="fa-solid fa-users"></i>
                <span>Competitors</span>
                <small>Coming soon</small>
            </div>
            <a href="{{ route('user.listing.import-leads', $listing->id) }}" class="sb-biz-modal-card">
                <i class="fa-solid fa-file-import"></i>
                <span>Import Leads</span>
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
        padding:24px;
        width:100%;
        max-width:420px;
        position:relative;
    }
    .sb-biz-modal-close{
        position:absolute;
        top:14px;
        right:16px;
        border:0;
        background:transparent;
        font-size:22px;
        color:#888;
        cursor:pointer;
    }
    .sb-biz-modal-grid{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:14px;
        margin-top:10px;
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
    .sb-biz-modal-card.is-disabled{
        color:#aaa;
        cursor:default;
    }
    .sb-biz-modal-card.is-disabled i{ color:#c3c7cc; }
    .sb-biz-modal-card.is-disabled small{ color:#c3c7cc; font-weight:400; }
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
});
</script>
@endpush
