@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Response Time — '.$listing->name)

@php
    // A closure, not a named function — this view can render more than
    // once in a request (e.g. a future @include), and a named function
    // declared inline would fatal on redeclaration the second time.
    $sbFormatDuration = function ($seconds) {
        if ($seconds === null) return null;
        $seconds = (int) round($seconds);
        if ($seconds < 60) return $seconds.' secs';
        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) return $minutes.' min'.($minutes === 1 ? '' : 's');
        $hours = intdiv($minutes, 60);
        $remMinutes = $minutes % 60;
        return $hours.'h : '.str_pad($remMinutes, 2, '0', STR_PAD_LEFT).'m';
    };
@endphp

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
        </div>

        @include($themeView.'user.listing.partials.analytics-tabs', ['activeTab' => 'response-time'])

        <div class="sb-an-card">
            <h5><i class="fas fa-stopwatch"></i> Average Response Time</h5>
            <p class="sb-an-sub">Average time between an enquiry arriving and you opening its details</p>

            @if($overallAvgSeconds === null)
                <p class="sb-wizard-empty">
                    Not enough data yet — this starts counting from enquiries you open going
                    forward. It couldn't be measured for enquiries that arrived before this
                    feature existed.
                </p>
            @else
                <div class="sb-an-response-box">
                    <strong>{{ $sbFormatDuration($overallAvgSeconds) }}</strong>
                    <span class="sb-an-pill sb-an-pill--good">Good</span>
                    <p>Your Response Time ({{ $responded->count() }} {{ Str::plural('enquiry', $responded->count()) }})</p>
                </div>

                <canvas id="sbChartResponseTime" height="90"></canvas>
            @endif
        </div>

        <p class="sb-an-note">
            <i class="fas fa-circle-info"></i>
            There's no way to know how fast other businesses on this site respond, so
            unlike a JustDial-style page this doesn't show a "competitor" number next to
            yours — it only shows what's actually measurable here.
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
    var el = document.getElementById('sbChartResponseTime');
    if (!el || typeof Chart === 'undefined') return;

    var weekly = @json($weekly->values());

    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: weekly.map(function (w) { return w.label; }),
            datasets: [{
                label: 'Avg response time (minutes)',
                data: weekly.map(function (w) { return w.avg_seconds === null ? null : Math.round(w.avg_seconds / 60); }),
                borderColor: '#1e90ff',
                backgroundColor: 'rgba(30,144,255,.12)',
                fill: true,
                tension: 0.3,
                spanGaps: true,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
        },
    });
});
</script>
@endpush
