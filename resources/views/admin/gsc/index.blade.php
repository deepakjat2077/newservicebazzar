@extends('admin.layouts.master')
@section('title','Search Console')
@section('content')

<style>
.gsc-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:22px; }
.gsc-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; }
.gsc-card-label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px; }
.gsc-card-value { font-size:25px; font-weight:700; color:#1e293b; }
.gsc-card.clicks .gsc-card-value { color:#2563eb; }
.gsc-card.impr   .gsc-card-value { color:#7c3aed; }
.gsc-card.ctr    .gsc-card-value { color:#059669; }
.gsc-card.pos    .gsc-card-value { color:#d97706; }
.gsc-section { background:#fff; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:20px; overflow:hidden; }
.gsc-section-head { padding:14px 18px; border-bottom:1px solid #f1f5f9; }
.gsc-section-title { font-size:15px; font-weight:700; color:#1e293b; margin:0; }
.gsc-section-sub { font-size:12.5px; color:#64748b; margin:3px 0 0; }
.gsc-table { width:100%; border-collapse:collapse; font-size:13px; }
.gsc-table th { background:#f8fafc; padding:9px 16px; text-align:left; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.gsc-table td { padding:9px 16px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.gsc-table td.num, .gsc-table th.num { text-align:right; }
.gsc-kw { word-break:break-word; color:#1e293b; }
.gsc-url { word-break:break-all; font-size:12px; color:#2563eb; }
.gsc-badge { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
.gsc-badge.p2 { background:#fef3c7; color:#b45309; }
.gsc-badge.ok { background:#dcfce7; color:#15803d; }
.gsc-badge.lo { background:#fee2e2; color:#b91c1c; }
.gsc-filter { display:flex; gap:8px; margin-bottom:18px; }
.gsc-filter a { padding:6px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#475569; text-decoration:none; }
.gsc-filter a.active { background:#2563eb; color:#fff; border-color:#2563eb; }
.gsc-trend { display:flex; align-items:flex-end; gap:3px; height:60px; padding:14px 18px 0; }
.gsc-trend .bar { flex:1; background:#bfdbfe; border-radius:2px 2px 0 0; min-height:2px; }
.gsc-empty { padding:22px 18px; color:#94a3b8; font-size:13px; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Search Console <small class="text-muted" style="font-size:13px;">{{ $from }} → {{ $end }}</small></h4>
    @if($lastPull)
        <span class="text-muted" style="font-size:12px;">Last pull: {{ \Carbon\Carbon::parse($lastPull)->format('d M, h:i A') }}</span>
    @endif
</div>

@if(!$configured)
    <div class="alert alert-danger">Google service-account key missing — Search Console data can't be pulled.</div>
@elseif(!$hasData)
    <div class="alert alert-info">
        No data yet. Run <code>php artisan seo:gsc-pull</code> once (it pulls automatically every day), then refresh.
    </div>
@endif

<div class="gsc-filter">
    <a href="{{ route('admin.gsc.index', ['days'=>7]) }}"  class="{{ $days==7  ? 'active' : '' }}">7 days</a>
    <a href="{{ route('admin.gsc.index', ['days'=>28]) }}" class="{{ $days==28 ? 'active' : '' }}">28 days</a>
    <a href="{{ route('admin.gsc.index', ['days'=>90]) }}" class="{{ $days==90 ? 'active' : '' }}">90 days</a>
</div>

<div class="gsc-cards">
    <div class="gsc-card clicks"><div class="gsc-card-label">Clicks</div><div class="gsc-card-value">{{ number_format($totals->clicks) }}</div></div>
    <div class="gsc-card impr"><div class="gsc-card-label">Impressions</div><div class="gsc-card-value">{{ number_format($totals->impressions) }}</div></div>
    <div class="gsc-card ctr"><div class="gsc-card-label">Avg CTR</div><div class="gsc-card-value">{{ $totals->ctr }}%</div></div>
    <div class="gsc-card pos"><div class="gsc-card-label">Avg Position</div><div class="gsc-card-value">{{ $totals->position }}</div></div>
</div>

@if($trend->count())
    <div class="gsc-section">
        <div class="gsc-section-head"><h5 class="gsc-section-title">Clicks per day</h5></div>
        @php $maxC = max(1, $trend->max('clicks')); @endphp
        <div class="gsc-trend">
            @foreach($trend as $t)
                <div class="bar" style="height:{{ max(2, round($t->clicks / $maxC * 100)) }}%"
                     title="{{ \Carbon\Carbon::parse($t->date)->format('d M') }}: {{ $t->clicks }} clicks"></div>
            @endforeach
        </div>
        <div class="gsc-empty" style="padding-top:6px;">Peak {{ number_format($maxC) }} clicks/day</div>
    </div>
@endif

{{-- ============ CLICK OPPORTUNITIES — the point of this page ============ --}}

<div class="gsc-section">
    <div class="gsc-section-head">
        <h5 class="gsc-section-title">🎯 Fastest clicks — fix the title/meta</h5>
        <p class="gsc-section-sub">Pages already on page 1 (pos ≤ 10) getting lots of impressions but almost no clicks. The title/snippet isn't tempting the click — rewrite it and clicks come <strong>without</strong> waiting for ranking.</p>
    </div>
    <table class="gsc-table">
        <thead><tr><th>Page</th><th class="num">Impressions</th><th class="num">Clicks</th><th class="num">CTR</th><th class="num">Pos</th></tr></thead>
        <tbody>
        @forelse($ctrOpps as $r)
            <tr>
                <td><a class="gsc-url" href="{{ $r->value }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::after($r->value, 'servicebazzar.com') ?: $r->value }}</a></td>
                <td class="num">{{ number_format($r->impressions) }}</td>
                <td class="num">{{ number_format($r->clicks) }}</td>
                <td class="num"><span class="gsc-badge lo">{{ $r->ctr }}%</span></td>
                <td class="num">{{ $r->position }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="gsc-empty">No page-1 low-CTR pages in this window — nice.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="gsc-section">
    <div class="gsc-section-head">
        <h5 class="gsc-section-title">⬆️ Page-2 opportunities — push the ranking</h5>
        <p class="gsc-section-sub">Queries ranking 8–20 (bottom of page 1 / top of page 2) with real demand. Strengthen these pages' content + internal links to lift them a few spots and win the clicks.</p>
    </div>
    <table class="gsc-table">
        <thead><tr><th>Query</th><th class="num">Impressions</th><th class="num">Clicks</th><th class="num">CTR</th><th class="num">Pos</th></tr></thead>
        <tbody>
        @forelse($page2 as $r)
            <tr>
                <td class="gsc-kw">{{ $r->value }}</td>
                <td class="num">{{ number_format($r->impressions) }}</td>
                <td class="num">{{ number_format($r->clicks) }}</td>
                <td class="num">{{ $r->ctr }}%</td>
                <td class="num"><span class="gsc-badge p2">{{ $r->position }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="gsc-empty">No page-2 queries with enough demand in this window.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- ============ Reference tables ============ --}}

<div class="row">
    <div class="col-lg-6">
        <div class="gsc-section">
            <div class="gsc-section-head"><h5 class="gsc-section-title">Top queries</h5></div>
            <table class="gsc-table">
                <thead><tr><th>Query</th><th class="num">Clicks</th><th class="num">Impr</th><th class="num">Pos</th></tr></thead>
                <tbody>
                @forelse($topQueries as $r)
                    <tr>
                        <td class="gsc-kw">{{ $r->value }}</td>
                        <td class="num">{{ number_format($r->clicks) }}</td>
                        <td class="num">{{ number_format($r->impressions) }}</td>
                        <td class="num">{{ $r->position }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="gsc-empty">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="gsc-section">
            <div class="gsc-section-head"><h5 class="gsc-section-title">Top pages</h5></div>
            <table class="gsc-table">
                <thead><tr><th>Page</th><th class="num">Clicks</th><th class="num">Impr</th><th class="num">Pos</th></tr></thead>
                <tbody>
                @forelse($topPages as $r)
                    <tr>
                        <td><a class="gsc-url" href="{{ $r->value }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::after($r->value, 'servicebazzar.com') ?: $r->value }}</a></td>
                        <td class="num">{{ number_format($r->clicks) }}</td>
                        <td class="num">{{ number_format($r->impressions) }}</td>
                        <td class="num">{{ $r->position }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="gsc-empty">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
