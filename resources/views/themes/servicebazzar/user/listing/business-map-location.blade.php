@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Map and Location — '.$listing->name)

@php
    // Falls back to Jaipur (this install's own city, per the surrounding
    // conversation) only so the map has somewhere to open — the marker only
    // actually represents the listing once real coordinates exist.
    $sbLat = $listing->latitude ?: 26.9124;
    $sbLng = $listing->longitude ?: 75.7873;
@endphp

<section class="pt-30 pb-80">
    <div class="container" style="max-width:640px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Map and Location</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(!$listing->latitude)
            <div class="sb-biz-panel-info-banner">
                <i class="fas fa-circle-info"></i>
                No location pinned yet — drag the marker to your business's exact spot, then save.
            </div>
        @endif

        <div class="sb-map-wrap">
            <div id="sbMapLocation"></div>
            <div class="sb-map-coords" id="sbMapCoords">Lat : {{ number_format($sbLat, 6) }}<br>Lng : {{ number_format($sbLng, 6) }}</div>
        </div>

        <form action="{{ route('user.listing.map-location.update', $listing->id) }}" method="post" id="sbMapForm">
            @csrf
            <input type="hidden" name="latitude" id="sbMapLatInput" value="{{ $sbLat }}">
            <input type="hidden" name="longitude" id="sbMapLngInput" value="{{ $sbLng }}">
            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Update Map Location</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .sb-map-wrap{ position:relative; margin-bottom:20px; }
    #sbMapLocation{ height:360px; border-radius:10px; overflow:hidden; border:1px solid #eceef0; }
    .sb-map-coords{
        position:absolute;
        top:12px;
        right:12px;
        z-index:1000;
        background:rgba(0,0,0,.72);
        color:#fff;
        font-size:11px;
        line-height:1.6;
        padding:6px 10px;
        border-radius:6px;
    }
    .sb-biz-form-submit{ display:block; width:100%; }
</style>
@endpush

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (typeof L === 'undefined') return;

    var lat = {{ $sbLat }};
    var lng = {{ $sbLng }};

    var map = L.map('sbMapLocation').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    function updateCoords(latlng){
        document.getElementById('sbMapCoords').innerHTML = 'Lat : ' + latlng.lat.toFixed(6) + '<br>Lng : ' + latlng.lng.toFixed(6);
        document.getElementById('sbMapLatInput').value = latlng.lat;
        document.getElementById('sbMapLngInput').value = latlng.lng;
    }

    marker.on('dragend', function(){ updateCoords(marker.getLatLng()); });

    map.on('click', function(e){
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng);
    });
});
</script>
@endpush
