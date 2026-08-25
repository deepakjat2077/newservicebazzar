{{--
    Shared tab bar across the Analytics-family pages (Analytics itself and
    Response Time). $activeTab is passed in by whichever page includes this.

    Banner and Business Profile Score are shown as disabled tabs rather than
    left out entirely — being visible-but-unavailable says "not tracked
    here" instead of silently pretending the tab was never a JustDial
    feature to begin with.
--}}
<div class="sb-an-tabs">
    <a href="{{ route('user.listing.analytics', $listing->id) }}" class="sb-an-tab {{ ($activeTab ?? '') === 'overview' ? 'is-active' : '' }}">Overview</a>
    <a href="{{ route('user.listing.analytics', $listing->id) }}#enquiries" class="sb-an-tab {{ ($activeTab ?? '') === 'enquiries' ? 'is-active' : '' }}">Enquiries</a>
    <a href="{{ route('user.listing.analytics', $listing->id) }}#calls" class="sb-an-tab {{ ($activeTab ?? '') === 'calls' ? 'is-active' : '' }}">Calls</a>
    <a href="{{ route('user.listing.response-time', $listing->id) }}" class="sb-an-tab {{ ($activeTab ?? '') === 'response-time' ? 'is-active' : '' }}">Avg Response Time</a>
    <span class="sb-an-tab is-disabled" title="No banner/ad system on this site yet">Banner</span>
    <a href="{{ route('user.listing.analytics', $listing->id) }}#views" class="sb-an-tab {{ ($activeTab ?? '') === 'views' ? 'is-active' : '' }}">Business Views</a>
    <a href="{{ route('user.listing.analytics', $listing->id) }}#reviews" class="sb-an-tab {{ ($activeTab ?? '') === 'reviews' ? 'is-active' : '' }}">Reviews</a>
    <a href="{{ route('user.listing.analytics', $listing->id) }}#media" class="sb-an-tab {{ ($activeTab ?? '') === 'media' ? 'is-active' : '' }}">Media</a>
    <a href="{{ route('user.listing.analytics', $listing->id) }}#media" class="sb-an-tab {{ ($activeTab ?? '') === 'directions' ? 'is-active' : '' }}">Directions</a>
    <span class="sb-an-tab is-disabled" title="No scoring algorithm on this site yet">Business Profile Score</span>
</div>

<div class="sb-an-daterange">
    <span>{{ now()->subYear()->format('d M Y') }} - {{ now()->format('d M Y') }}</span>
    <span class="sb-an-daterange-badge"><i class="fas fa-calendar"></i> 1 Year</span>
</div>

@once
<style>
    .sb-an-tabs{
        display:flex;
        flex-wrap:wrap;
        gap:22px;
        border-bottom:1px solid #eceef0;
        margin-bottom:16px;
        padding-bottom:12px;
    }
    .sb-an-tab{
        font-size:12.5px;
        font-weight:600;
        color:#777;
        white-space:nowrap;
        padding-bottom:4px;
    }
    .sb-an-tab.is-active{ color:#1e90ff; border-bottom:2px solid #1e90ff; }
    .sb-an-tab.is-disabled{ color:#ccc; cursor:default; }
    .sb-an-daterange{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
        font-size:13px;
        color:#333;
        font-weight:600;
    }
    .sb-an-daterange-badge{
        border:1px solid #d5d9dd;
        border-radius:16px;
        padding:6px 14px;
        font-size:12px;
        font-weight:600;
        color:#444;
    }
</style>
@endonce
