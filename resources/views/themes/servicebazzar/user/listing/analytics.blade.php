@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Analytics — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
        </div>

        @include($themeView.'user.listing.partials.analytics-tabs', ['activeTab' => 'overview'])

        {{-- Interactions overview --}}
        <div class="sb-an-card">
            <h5>Interactions Overview</h5>
            <p class="sb-an-sub">Enquiries, views &amp; direction requests — last 6 months</p>
            <canvas id="sbChartOverview" height="90"></canvas>
        </div>

        {{-- Enquiries --}}
        <div class="sb-an-card" id="enquiries">
            <h5>Enquiries</h5>
            <p class="sb-an-sub">Enquiries received vs responded</p>
            <div class="sb-an-stat-row">
                <div><strong>{{ $totals['enquiries'] }}</strong><span>Enquiries Received</span></div>
                <div><strong>{{ $totals['responded'] }}</strong><span>Enquiries Responded</span></div>
                <div class="{{ $totals['responded_percent'] < 50 ? 'is-poor' : 'is-good' }}">
                    <strong>{{ $totals['responded_percent'] }}% <span class="sb-an-quality">{{ $totals['responded_percent'] < 50 ? 'Poor' : 'Good' }}</span></strong><span>Your Response Rate</span>
                </div>
            </div>
            <canvas id="sbChartEnquiries" height="90"></canvas>
        </div>

        {{-- Phone clicks --}}
        <div class="sb-an-card" id="calls">
            <h5>Calls</h5>
            <p class="sb-an-sub">"Call" button taps on your listing — this site can't confirm whether a call was answered, only that a visitor tapped to call</p>
            <div class="sb-an-stat-row">
                <div><strong>{{ $totals['phone_clicks'] }}</strong><span>Call Button Taps</span></div>
                <div><strong>{{ $totals['whatsapp_clicks'] }}</strong><span>WhatsApp Taps</span></div>
            </div>
            <canvas id="sbChartCalls" height="90"></canvas>
        </div>

        {{-- Business views --}}
        <div class="sb-an-card" id="views">
            <h5>Business Views</h5>
            <p class="sb-an-sub">How customers are viewing your business listing</p>
            <div class="sb-an-stat-row">
                <div><strong>{{ number_format($totals['views']) }}</strong><span>Total Views</span></div>
                <div><strong>{{ number_format($totals['unique_views']) }}</strong><span>Unique Visitors</span></div>
                <div><strong>{{ number_format($totals['website_clicks']) }}</strong><span>Website Clicks</span></div>
            </div>
        </div>

        {{-- Reviews --}}
        <div class="sb-an-card" id="reviews">
            <h5>Reviews</h5>
            <p class="sb-an-sub">Ratings left on your listing</p>
            <div class="sb-an-stat-row">
                <div><strong>{{ $totalReviews }}</strong><span>Total Reviews</span></div>
                <div><strong>{{ $avgRating }} <i class="fas fa-star sb-an-star"></i></strong><span>Average Rating</span></div>
            </div>
            <canvas id="sbChartReviews" height="90"></canvas>
        </div>

        {{-- Gallery / Directions --}}
        <div class="sb-an-card" id="media">
            <h5>Photos &amp; Directions</h5>
            <p class="sb-an-sub">Gallery views and "Get Directions" taps</p>
            <div class="sb-an-stat-row">
                <div><strong>{{ number_format($totals['gallery_clicks']) }}</strong><span>Gallery Views</span></div>
                <div><strong>{{ number_format($totals['direction_clicks']) }}</strong><span>Direction Taps</span></div>
                <div><strong>{{ number_format($totals['share_clicks']) }}</strong><span>Shares</span></div>
                <div><strong>{{ number_format($totals['favourite_clicks']) }}</strong><span>Favourited</span></div>
            </div>
            <canvas id="sbChartDirections" height="90"></canvas>
        </div>

        <p class="sb-an-note">
            <i class="fas fa-circle-info"></i>
            This is everything Service Bazzar actually tracks for your listing. There's no
            competitor comparison, call-answer tracking, or profile-score here — this site
            doesn't have that data, so it isn't shown as if it did.
        </p>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
@endpush

@push('js')
<script src="{{ asset('assets/admin/js/chartjs.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var monthly = @json($monthly->values());
    var labels = monthly.map(function (m) { return m.label; });

    function bar(canvasId, datasets) {
        var el = document.getElementById(canvasId);
        if (!el || typeof Chart === 'undefined') return;
        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    bar('sbChartOverview', [
        { label: 'Enquiries', data: monthly.map(function (m) { return m.enquiries; }), backgroundColor: '#1e90ff' },
        { label: 'Views', data: monthly.map(function (m) { return m.views; }), backgroundColor: '#7bc696' },
        { label: 'Directions', data: monthly.map(function (m) { return m.direction_clicks; }), backgroundColor: '#f2b400' },
    ]);

    bar('sbChartEnquiries', [
        { label: 'Received', data: monthly.map(function (m) { return m.enquiries; }), backgroundColor: '#f6a97a' },
        { label: 'Responded', data: monthly.map(function (m) { return m.responded; }), backgroundColor: '#1e90ff' },
    ]);

    bar('sbChartCalls', [
        { label: 'Call Taps', data: monthly.map(function (m) { return m.phone_clicks; }), backgroundColor: '#1e90ff' },
        { label: 'WhatsApp Taps', data: monthly.map(function (m) { return m.whatsapp_clicks; }), backgroundColor: '#25d366' },
    ]);

    bar('sbChartReviews', [
        { label: 'Reviews', data: monthly.map(function (m) { return m.reviews; }), backgroundColor: '#9b7ff0' },
    ]);

    bar('sbChartDirections', [
        { label: 'Gallery Views', data: monthly.map(function (m) { return m.gallery_clicks; }), backgroundColor: '#7fd0c2' },
        { label: 'Direction Taps', data: monthly.map(function (m) { return m.direction_clicks; }), backgroundColor: '#f2b400' },
    ]);
});
</script>
@endpush
