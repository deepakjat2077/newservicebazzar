@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Business Timings — '.$listing->name)

@php
    $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $selectedDays = $listing->business_days ?? [];
    $openTimes = $listing->open_time ?? [];
    $closeTimes = $listing->close_time ?? [];

    // Guard against legacy rows where the JSON never got array-cast and comes
    // back as a raw string — same defensive decode the main edit form does.
    foreach (['selectedDays', 'openTimes', 'closeTimes'] as $var) {
        if (is_string($$var)) { $$var = json_decode($$var, true) ?: []; }
        $$var = (array) $$var;
    }

    // Treat legacy 12:00 AM–11:59 PM data as Open 24 Hours, exactly as the
    // public detail page does, so the checkbox matches what customers see.
    $isOpen24Hours = $listing->isOpen24HoursDerived();

    if (empty($openTimes)) {
        $openTimes = [''];
    }
@endphp

<section class="pt-30 pb-80">
    <div class="container" style="max-width:640px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Update Business Timings</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="sb-biz-panel-info-banner">
            <i class="fas fa-circle-info"></i>
            Let your customers know when to reach you. You can also configure dual timing
            slots in a single day.
        </div>

        <form action="{{ route('user.listing.business-timings.update', $listing->id) }}" method="post" id="sbTimingsForm">
            @csrf

            <p class="sb-contact-section-label">Select Days of the Week</p>
            <div class="sb-day-chips">
                @foreach($days as $day)
                    <label class="sb-day-chip">
                        <input type="checkbox" name="business_days[]" value="{{ $day }}" class="sb-day-checkbox" {{ in_array($day, $selectedDays, true) ? 'checked' : '' }}>
                        <span>{{ $day }}</span>
                    </label>
                @endforeach
            </div>

            <label class="sb-select-all-row">
                <input type="checkbox" id="sbSelectAllDays" {{ count(array_intersect($days, $selectedDays)) === count($days) ? 'checked' : '' }}>
                Select All Days
            </label>

            <label class="sb-select-all-row" style="margin-bottom:20px;">
                <input type="checkbox" name="open_24_hours" value="1" id="sbOpen24" {{ $isOpen24Hours ? 'checked' : '' }}>
                Open 24 Hours
            </label>

            <div id="sbTimeSlots" {{ $isOpen24Hours ? 'style=display:none' : '' }}>
                @foreach($openTimes as $i => $openTime)
                    <div class="sb-contact-row sb-time-slot">
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Opens at</label>
                            <select name="open_time[]" class="sb-input sb-time-select" data-selected="{{ $openTime }}"></select>
                        </div>
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Closes at</label>
                            <select name="close_time[]" class="sb-input sb-time-select" data-selected="{{ $closeTimes[$i] ?? '' }}"></select>
                        </div>
                        @if($i > 0)
                            <button type="button" class="sb-contact-remove sb-remove-slot"><i class="fas fa-trash"></i></button>
                        @endif
                    </div>
                @endforeach
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddSlot" {{ $isOpen24Hours ? 'style=display:none' : '' }}>
                <i class="fas fa-plus"></i> Add Another Time Slot
            </button>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save and Continue</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-day-chips{ display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
    .sb-day-chip{ cursor:pointer; }
    .sb-day-checkbox{ display:none; }
    .sb-day-chip span{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:42px;
        height:42px;
        border-radius:50%;
        background:#e6e8eb;
        color:#666;
        font-size:12px;
        font-weight:700;
    }
    .sb-day-checkbox:checked + span{ background:#1e90ff; color:#fff; }
    .sb-select-all-row{ display:flex; align-items:center; gap:8px; font-size:13.5px; color:#333; margin-bottom:14px; }
    .sb-time-slot{ margin-bottom:12px; }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:24px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    function timeOptions(){
        var html = '<option value="">Select Time</option>';
        for (var hour = 0; hour < 24; hour++) {
            for (var minute = 0; minute < 60; minute += 30) {
                var period = hour >= 12 ? 'PM' : 'AM';
                var displayHour = hour % 12; if (displayHour === 0) displayHour = 12;
                var t = String(displayHour).padStart(2,'0') + ':' + String(minute).padStart(2,'0') + ' ' + period;
                html += '<option value="'+t+'">'+t+'</option>';
            }
        }
        return html;
    }

    function initSelect(select){
        var selected = select.getAttribute('data-selected') || '';
        select.innerHTML = timeOptions();
        if (selected) {
            // A saved time that isn't on the 30-minute grid (e.g. "11:59 PM",
            // or an old 24-hour value) matches no <option>, so the select
            // would silently fall back to blank and the owner's real hours
            // would look unset. Inject the saved value as its own option so
            // it shows and still round-trips on save.
            var exists = Array.prototype.some.call(select.options, function(o){ return o.value === selected; });
            if (!exists) {
                var opt = document.createElement('option');
                opt.value = selected;
                opt.textContent = selected;
                select.appendChild(opt);
            }
            select.value = selected;
        }
    }

    document.querySelectorAll('.sb-time-select').forEach(initSelect);

    // Select All Days.
    var selectAll = document.getElementById('sbSelectAllDays');
    var dayBoxes = document.querySelectorAll('.sb-day-checkbox');
    selectAll.addEventListener('change', function(){
        dayBoxes.forEach(function(b){ b.checked = selectAll.checked; });
    });
    dayBoxes.forEach(function(b){
        b.addEventListener('change', function(){
            selectAll.checked = Array.from(dayBoxes).every(function(x){ return x.checked; });
        });
    });

    // Open 24 Hours toggles the time slots away.
    var open24 = document.getElementById('sbOpen24');
    var slotsWrap = document.getElementById('sbTimeSlots');
    var addBtn = document.getElementById('sbAddSlot');
    open24.addEventListener('change', function(){
        slotsWrap.style.display = open24.checked ? 'none' : '';
        addBtn.style.display = open24.checked ? 'none' : '';
    });

    // Remove slot.
    document.querySelectorAll('.sb-remove-slot').forEach(function(btn){
        btn.addEventListener('click', function(){ btn.closest('.sb-time-slot').remove(); });
    });

    // Add another slot.
    addBtn.addEventListener('click', function(){
        var row = document.createElement('div');
        row.className = 'sb-contact-row sb-time-slot';
        row.innerHTML = '<div class="sb-biz-panel-field sb-contact-grow"><label>Opens at</label><select name="open_time[]" class="sb-input sb-time-select"></select></div>' +
            '<div class="sb-biz-panel-field sb-contact-grow"><label>Closes at</label><select name="close_time[]" class="sb-input sb-time-select"></select></div>' +
            '<button type="button" class="sb-contact-remove sb-remove-slot"><i class="fas fa-trash"></i></button>';
        slotsWrap.appendChild(row);
        row.querySelectorAll('.sb-time-select').forEach(initSelect);
        row.querySelector('.sb-remove-slot').addEventListener('click', function(){ row.remove(); });
    });
});
</script>
@endpush
