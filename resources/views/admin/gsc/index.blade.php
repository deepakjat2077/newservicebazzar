@extends('admin.layouts.master')
@section('title','Google Search Console')
@section('content')

<style>
.gsc-wrap { padding: 24px; }
.gsc-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.gsc-title { font-size:20px; font-weight:600; color:#1e293b; }
.gsc-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:28px; }
.gsc-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; }
.gsc-card-label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
.gsc-card-value { font-size:26px; font-weight:700; color:#1e293b; }
.gsc-card.clicks  .gsc-card-value { color:#2563eb; }
.gsc-card.impr    .gsc-card-value { color:#7c3aed; }
.gsc-card.ctr     .gsc-card-value { color:#059669; }
.gsc-card.pos     .gsc-card-value { color:#d97706; }
.gsc-section { background:#fff; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:20px; overflow:hidden; }
.gsc-section-title { font-size:14px; font-weight:600; color:#1e293b; padding:14px 18px; border-bottom:1px solid #f1f5f9; }
.gsc-table { width:100%; border-collapse:collapse; font-size:13px; }
.gsc-table th { background:#f8fafc; padding:10px 16px; text-align:left; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0; }
.gsc-table td { padding:10px 16px; border-bottom:1px solid #f1f5f9; color:#334155; }
.gsc-table tr:last-child td { border-bottom:none; }
.gsc-table tr:hover td { background:#f8fafc; }
.gsc-table td.url { color:#2563eb; word-break:break-all; max-width:320px; font-size:12px; }
.badge-clicks { background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:12px; font-weight:600; font-size:12px; }
.badge-pos    { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:12px; font-weight:600; font-size:12px; }
.connect-box  { text-align:center; padding:60px 20px; }
.connect-box h2 { font-size:18px; font-weight:600; margin-bottom:10px; color:#1e293b; }
.connect-box p  { color:#64748b; margin-bottom:24px; }
.btn-connect { display:inline-flex; align-items:center; gap:8px; background:#2563eb; color:#fff; padding:12px 28px; border-radius:8px; font-weight:600; text-decoration:none; }
.btn-connect:hover { background:#1d4ed8; color:#fff; }
.btn-disconnect { background:none; border:1px solid #e2e8f0; color:#64748b; padding:8px 16px; border-radius:6px; font-size:13px; cursor:pointer; }
.btn-disconnect:hover { background:#fee2e2; border-color:#fca5a5; color:#dc2626; }
</style>

<div class="gsc-wrap">

    <div class="gsc-header">
        <div class="gsc-title">
            <img src="https://www.google.com/favicon.ico" style="width:18px;vertical-align:-3px;margin-right:8px" alt="">
            Google Search Console
        </div>
        @if($connected)
        <form action="{{ route('admin.gsc.disconnect') }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn-disconnect" onclick="return confirm('Disconnect GSC?')">Disconnect</button>
        </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    @if(!$connected)
        <div class="gsc-section">
            <div class="connect-box">
                <h2>Google Search Console Connect Nahi Hai</h2>
                <p>Last 28 days ke clicks, impressions, top pages aur keywords dekhne ke liye connect karo.</p>
                <a href="{{ route('admin.gsc.auth') }}" class="btn-connect">
                    <img src="https://www.google.com/favicon.ico" style="width:16px" alt=""> Connect with Google
                </a>
            </div>
        </div>
    @else

        {{-- Performance Cards --}}
        @if($performance)
        <div class="gsc-cards">
            <div class="gsc-card clicks">
                <div class="gsc-card-label">Total Clicks</div>
                <div class="gsc-card-value">{{ number_format($performance['clicks']) }}</div>
            </div>
            <div class="gsc-card impr">
                <div class="gsc-card-label">Impressions</div>
                <div class="gsc-card-value">{{ number_format($performance['impressions']) }}</div>
            </div>
            <div class="gsc-card ctr">
                <div class="gsc-card-label">Avg CTR</div>
                <div class="gsc-card-value">{{ $performance['ctr'] }}%</div>
            </div>
            <div class="gsc-card pos">
                <div class="gsc-card-label">Avg Position</div>
                <div class="gsc-card-value">{{ $performance['position'] }}</div>
            </div>
        </div>
        <p style="color:#94a3b8;font-size:12px;margin-bottom:20px;">Last 28 days ka data ({{ now()->subDays(28)->format('d M') }} – {{ now()->subDays(1)->format('d M Y') }})</p>

        {{-- Daily Chart --}}
        @if(!empty($performance['rows']))
        <div class="gsc-section">
            <div style="padding:16px 20px 0;">
                <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;">
                    <button onclick="switchMetric('clicks',this)" class="gsc-tab active" style="color:#1a73e8;border-bottom:2px solid #1a73e8;">
                        <span class="gsc-tab-val" id="tab-clicks">{{ number_format($performance['clicks']) }}</span>
                        <span class="gsc-tab-label">Total clicks</span>
                    </button>
                    <button onclick="switchMetric('impressions',this)" class="gsc-tab">
                        <span class="gsc-tab-val" id="tab-impressions">{{ number_format($performance['impressions']) }}</span>
                        <span class="gsc-tab-label">Total impressions</span>
                    </button>
                    <button onclick="switchMetric('ctr',this)" class="gsc-tab">
                        <span class="gsc-tab-val" id="tab-ctr">{{ $performance['ctr'] }}%</span>
                        <span class="gsc-tab-label">Average CTR</span>
                    </button>
                    <button onclick="switchMetric('position',this)" class="gsc-tab">
                        <span class="gsc-tab-val" id="tab-position">{{ $performance['position'] }}</span>
                        <span class="gsc-tab-label">Average position</span>
                    </button>
                </div>
            </div>
            <div style="padding:20px;">
                <canvas id="gscChart" height="70"></canvas>
            </div>
        </div>
        <style>
        .gsc-tab { background:none; border:none; border-bottom:2px solid transparent; padding:10px 24px 12px; cursor:pointer; text-align:left; margin-bottom:-2px; }
        .gsc-tab-val { display:block; font-size:20px; font-weight:600; color:#1e293b; }
        .gsc-tab-label { display:block; font-size:12px; color:#64748b; margin-top:2px; }
        .gsc-tab.active .gsc-tab-label { color:#1a73e8; }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
        (function(){
            var rows = @json($performance['rows']);
            var labels = rows.map(function(r){ return r.keys[0].slice(5); });
            var datasets = {
                clicks:      { data: rows.map(function(r){ return r.clicks; }),      color:'#1a73e8' },
                impressions: { data: rows.map(function(r){ return r.impressions; }), color:'#9334e6' },
                ctr:         { data: rows.map(function(r){ return +(r.ctr*100).toFixed(2); }), color:'#1e8e3e' },
                position:    { data: rows.map(function(r){ return +r.position.toFixed(1); }), color:'#e37400' }
            };
            var current = 'clicks';
            var chart = new Chart(document.getElementById('gscChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: datasets.clicks.data,
                        borderColor: '#1a73e8',
                        backgroundColor: 'rgba(26,115,232,.08)',
                        tension: .4, pointRadius: 3, pointHoverRadius: 5,
                        borderWidth: 2, fill: true
                    }]
                },
                options:{
                    responsive:true,
                    interaction:{ mode:'index', intersect:false },
                    plugins:{ legend:{ display:false } },
                    scales:{
                        x:{ grid:{ color:'#f1f5f9' }, ticks:{ color:'#64748b', font:{size:11} } },
                        y:{ grid:{ color:'#f1f5f9' }, ticks:{ color:'#64748b', font:{size:11} } }
                    }
                }
            });
            window.switchMetric = function(metric, btn) {
                current = metric;
                chart.data.datasets[0].data        = datasets[metric].data;
                chart.data.datasets[0].borderColor  = datasets[metric].color;
                chart.data.datasets[0].backgroundColor = datasets[metric].color.replace(')',',0.08)').replace('rgb','rgba');
                chart.update();
                document.querySelectorAll('.gsc-tab').forEach(function(b){
                    b.classList.remove('active');
                    b.style.color = '';
                    b.style.borderBottomColor = 'transparent';
                    b.querySelector('.gsc-tab-label').style.color = '';
                });
                btn.classList.add('active');
                btn.style.borderBottomColor = datasets[metric].color;
                btn.querySelector('.gsc-tab-val').style.color = datasets[metric].color;
                btn.querySelector('.gsc-tab-label').style.color = datasets[metric].color;
            };
        })();
        </script>
        @endif
        @endif

        {{-- Top Pages --}}
        @if(!empty($topPages))
        <div class="gsc-section">
            <div class="gsc-section-title">Top 10 Pages (by Clicks)</div>
            <table class="gsc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Page URL</th>
                        <th>Clicks</th>
                        <th>Impressions</th>
                        <th>CTR</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topPages as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="url">{{ str_replace('https://www.servicebazzar.com', '', $row['keys'][0]) ?: '/' }}</td>
                        <td><span class="badge-clicks">{{ number_format($row['clicks']) }}</span></td>
                        <td>{{ number_format($row['impressions']) }}</td>
                        <td>{{ round($row['ctr'] * 100, 1) }}%</td>
                        <td><span class="badge-pos">{{ round($row['position'], 1) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Top Queries --}}
        @if(!empty($topQueries))
        <div class="gsc-section">
            <div class="gsc-section-title">Top 10 Search Queries (by Clicks)</div>
            <table class="gsc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Keyword</th>
                        <th>Clicks</th>
                        <th>Impressions</th>
                        <th>CTR</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topQueries as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['keys'][0] }}</td>
                        <td><span class="badge-clicks">{{ number_format($row['clicks']) }}</span></td>
                        <td>{{ number_format($row['impressions']) }}</td>
                        <td>{{ round($row['ctr'] * 100, 1) }}%</td>
                        <td><span class="badge-pos">{{ round($row['position'], 1) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    @endif
</div>

@endsection
