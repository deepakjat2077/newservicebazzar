@extends('admin.layouts.master')
@section('title', 'Sales Team Report')
@section('content')

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $rep->name }} <small class="text-muted">({{ $rep->empcode }})</small> — {{ $date->format('d M Y') }}</h5>
            <small class="text-muted">
                This month: {{ $month['pitched'] }} pitched · {{ $month['deals'] }} deals · ₹{{ number_format($month['revenue']) }} revenue · {{ $month['follow_ups'] }} follow ups pending
            </small>
        </div>
        <form method="get" class="form-inline mt-2 mt-md-0">
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-primary mr-2">Show</button>
            <a href="{{ route('admin.sales_team.index', ['date' => $date->toDateString()]) }}" class="btn btn-sm btn-light">All reps</a>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-0">
        @if($track->isNotEmpty() || $dispoPoints->isNotEmpty())
            <div id="repMap" style="height:380px;border-radius:4px;"></div>
            <div class="px-3 py-2 small text-muted">
                <span style="color:#2563eb;">●</span> Location points (line = order through the day) ·
                <span style="color:#16a34a;">●</span> Login ·
                <span style="color:#dc2626;">●</span> Dispositions
            </div>
        @else
            <div class="p-4 text-center text-muted">No locations captured for {{ $rep->name }} on this day.</div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><strong>Location timeline</strong> <span class="badge badge-light">{{ $locations->count() }}</span></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Time</th><th>Event</th><th>Place</th><th></th></tr></thead>
                    <tbody>
                        @forelse($locations as $l)
                            <tr>
                                <td style="white-space:nowrap;">{{ $l->created_at->format('h:i A') }}</td>
                                <td>
                                    @if($l->event === 'login')<span class="badge badge-success">Login</span>@else<span class="badge badge-light">Update</span>@endif
                                </td>
                                <td>
                                    <small>{{ $l->label() }}</small>
                                    @if($l->accuracy)<small class="text-muted"> (±{{ $l->accuracy }} m)</small>@endif
                                </td>
                                <td>@if($l->mapUrl())<a href="{{ $l->mapUrl() }}" target="_blank" rel="noopener" class="small">Map</a>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Nothing recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><strong>Dispositions</strong> <span class="badge badge-light">{{ $dispositions->count() }}</span></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Time</th><th>Business</th><th>Disposition</th><th>Where</th></tr></thead>
                    <tbody>
                        @forelse($dispositions as $d)
                            <tr>
                                <td style="white-space:nowrap;">{{ $d->created_at->format('h:i A') }}</td>
                                <td><small><strong>{{ optional($d->listing)->name ?? '—' }}</strong><br>{{ optional($d->listing)->city }}</small></td>
                                <td><span class="badge badge-info">{{ $d->disposition }}</span></td>
                                <td>
                                    @if($d->location && $d->location->hasPoint())
                                        <small>{{ $d->location->label() }}</small>
                                        <a href="{{ $d->location->mapUrl() }}" target="_blank" rel="noopener" class="small d-block">Map</a>
                                    @else
                                        <small class="text-muted">Not captured</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No dispositions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($track->isNotEmpty() || $dispoPoints->isNotEmpty())
@push('css-link')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var track = @json($track), dispos = @json($dispoPoints);
    var map = L.map('repMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var bounds = [];
    if (track.length > 1) {
        L.polyline(track.map(function (p) { return [p.lat, p.lng]; }), { color: '#2563eb', weight: 3, opacity: 0.6 }).addTo(map);
    }
    track.forEach(function (p) {
        L.circleMarker([p.lat, p.lng], { radius: 6, color: p.event === 'login' ? '#16a34a' : '#2563eb', fillOpacity: 0.9 })
            .addTo(map).bindPopup((p.event === 'login' ? '<strong>Login</strong><br>' : '') + p.place + '<br>' + p.time);
        bounds.push([p.lat, p.lng]);
    });
    dispos.forEach(function (p) {
        L.circleMarker([p.lat, p.lng], { radius: 8, color: '#dc2626', fillOpacity: 0.9 })
            .addTo(map).bindPopup('<strong>' + p.label + '</strong><br>' + p.time);
        bounds.push([p.lat, p.lng]);
    });
    if (bounds.length === 1) { map.setView(bounds[0], 15); } else { map.fitBounds(bounds, { padding: [30, 30] }); }
})();
</script>
@endpush
@endif

@endsection
