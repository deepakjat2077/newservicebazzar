<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map and Location – {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #1a1a2e; }
        .page { max-width: 820px; margin: 0 auto; }

        .topbar {
            display: flex; align-items: center; padding: 16px 18px;
            border-bottom: 1px solid #eef0f4; position: sticky; top: 0; background: #fff; z-index: 1000;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; margin-right: 16px; }
        .topbar .title { font-size: 17px; font-weight: 600; flex: 1; text-align: center; margin-right: 34px; }

        .content { padding: 18px; }
        .search-box { margin-bottom: 12px; display: flex; gap: 8px; }
        .search-box input {
            flex: 1; border: 1.5px solid #d5dae5; border-radius: 8px; padding: 12px 14px;
            font-size: 14px; font-family: 'Poppins', sans-serif; outline: none;
        }
        .search-box button {
            background: #eef2f8; border: none; border-radius: 8px; padding: 0 16px;
            color: #1a73e8; cursor: pointer; font-size: 15px;
        }

        .map-wrap { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid #e6e9f0; }
        #map { width: 100%; height: 360px; z-index: 1; }
        .latlng {
            position: absolute; top: 12px; right: 12px; z-index: 500;
            background: rgba(40,44,52,.85); color: #fff; font-size: 12px;
            padding: 6px 10px; border-radius: 6px; line-height: 1.5;
        }
        .hint { font-size: 12px; color: #888; margin-top: 10px; text-align: center; }

        .btn-update {
            display: block; width: 100%; margin-top: 18px;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-update:hover { background: #1557b0; }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ sg_route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
        <div class="title">Map and Location</div>
    </div>

    <form method="POST" action="{{ sg_route('sales_genio.business.map.update', $business->id) }}" class="content">
        @csrf
        <div class="search-box">
            <input type="text" id="mapSearch" placeholder="Search a place / address to move the pin" onkeydown="if(event.key==='Enter'){event.preventDefault();searchPlace();}">
            <button type="button" onclick="searchPlace()"><i class="fas fa-magnifying-glass"></i></button>
        </div>

        <div class="map-wrap">
            <div id="map"></div>
            <div class="latlng">
                Lat : <span id="latText">{{ $business->latitude ?? '—' }}</span><br>
                Lng : <span id="lngText">{{ $business->longitude ?? '—' }}</span>
            </div>
        </div>

        <input type="hidden" name="latitude"  id="latInput"  value="{{ $business->latitude }}">
        <input type="hidden" name="longitude" id="lngInput" value="{{ $business->longitude }}">

        <div class="hint">Drag the pin or search to set the exact business location.</div>

        <button type="submit" class="btn-update">Update Map Location</button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    const startLat = {{ $business->latitude ?: 26.9124 }};
    const startLng = {{ $business->longitude ?: 75.7873 }};
    const hasCoords = {{ ($business->latitude && $business->longitude) ? 'true' : 'false' }};
    const defaultSearch = @json(trim(($business->area ? $business->area.', ' : '').($business->city ?: '').' '.($business->pincode ?: '')));

    const map = L.map('map').setView([startLat, startLng], hasCoords ? 16 : 13);
    // Google map tiles via Leaflet (no API key needed for tiles).
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps'
    }).addTo(map);

    const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function setLatLng(lat, lng) {
        document.getElementById('latInput').value = lat.toFixed(6);
        document.getElementById('lngInput').value = lng.toFixed(6);
        document.getElementById('latText').textContent = lat.toFixed(6);
        document.getElementById('lngText').textContent = lng.toFixed(6);
    }
    if (hasCoords) setLatLng(startLat, startLng);

    marker.on('dragend', () => { const p = marker.getLatLng(); setLatLng(p.lat, p.lng); });
    map.on('click', (e) => { marker.setLatLng(e.latlng); setLatLng(e.latlng.lat, e.latlng.lng); });

    // Free geocoding via OSM Nominatim.
    function searchPlace(query) {
        const q = query || document.getElementById('mapSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.length) {
                const lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
                setLatLng(lat, lng);
            } else if (!query) {
                alert('Place not found.');
            }
        })
        .catch(() => { if (!query) alert('Search failed. Try again.'); });
    }

    // If no coords yet, try to centre on the business address once.
    if (!hasCoords && defaultSearch) {
        searchPlace(defaultSearch);
    }

    setTimeout(() => map.invalidateSize(), 200);
</script>
</body>
</html>
