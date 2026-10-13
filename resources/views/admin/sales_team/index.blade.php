@extends('admin.layouts.master')
@section('title', 'Sales Team Report')
@section('content')

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Sales Team Report — {{ $date->format('d M Y') }}</h5>
            <small class="text-muted">Locations come from the rep's phone/browser GPS while they use the SB Sales panel (the browser asks the rep for permission). Month figures match the rep's own dashboard.</small>
        </div>
        <form method="get" class="form-inline mt-2 mt-md-0">
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-primary mr-2">Show</button>
            <a href="{{ route('admin.sales_dispositions.index') }}" class="btn btn-sm btn-light">Dispositions</a>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-0">
        @if($mapPoints->isNotEmpty())
            <div id="teamMap" style="height:340px;border-radius:4px;"></div>
        @else
            <div class="p-4 text-center text-muted">No locations captured on this day yet.</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Sales user</th>
                    <th>Login ({{ $date->format('d M') }})</th>
                    <th>Last location</th>
                    <th class="text-center">Dispositions<br><small>that day</small></th>
                    <th class="text-center">Pitched<br><small>this month</small></th>
                    <th class="text-center">Deals<br><small>this month</small></th>
                    <th class="text-right">Revenue<br><small>this month</small></th>
                    <th class="text-center">Follow ups<br><small>pending</small></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php $rep = $row['rep']; $m = $row['month']; @endphp
                    <tr>
                        <td><strong>{{ $rep->name }}</strong><br><small class="text-muted">{{ $rep->empcode }} · {{ $rep->city ?: '—' }}</small></td>
                        <td style="max-width:220px;">
                            @if($row['login'])
                                <strong>{{ $row['login']->created_at->format('h:i A') }}</strong><br>
                                <small>{{ $row['login']->label() }}</small>
                                @if($row['login']->mapUrl())
                                    <br><a href="{{ $row['login']->mapUrl() }}" target="_blank" rel="noopener" class="small"><i class="fas fa-map-marker-alt"></i> Map</a>
                                @endif
                            @else
                                <small class="text-muted">No login recorded</small>
                            @endif
                        </td>
                        <td style="max-width:220px;">
                            @if($row['last'])
                                <strong>{{ $row['last']->created_at->format('h:i A') }}</strong><br>
                                <small>{{ $row['last']->label() }}</small>
                                @if($row['last']->mapUrl())
                                    <br><a href="{{ $row['last']->mapUrl() }}" target="_blank" rel="noopener" class="small"><i class="fas fa-map-marker-alt"></i> Map</a>
                                @endif
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $row['dispositions'] }}</td>
                        <td class="text-center">{{ $m['pitched'] }}</td>
                        <td class="text-center">{{ $m['deals'] }}</td>
                        <td class="text-right">₹{{ number_format($m['revenue']) }}</td>
                        <td class="text-center">{{ $m['follow_ups'] }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.sales_team.show', ['id' => $rep->id, 'date' => $date->toDateString()]) }}" class="btn btn-sm btn-outline-primary">Day details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No sales users.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($mapPoints->isNotEmpty())
@push('css-link')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var pts = @json($mapPoints);
    var map = L.map('teamMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var bounds = [];
    pts.forEach(function (p) {
        L.marker([p.lat, p.lng]).addTo(map)
            .bindPopup('<strong>' + p.name + '</strong><br>' + p.place + '<br>' + p.time);
        bounds.push([p.lat, p.lng]);
    });
    if (bounds.length === 1) { map.setView(bounds[0], 14); } else { map.fitBounds(bounds, { padding: [30, 30] }); }
})();
</script>
@endpush
@endif

@endsection
