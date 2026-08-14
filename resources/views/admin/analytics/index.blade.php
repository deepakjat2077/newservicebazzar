@extends('admin.layouts.master')
@section('content')
@section('title','Analytics')

{{--
    Class names are prefixed `anf-` on purpose: the admin layout loads
    bootstrap, bootstrap-toggle and the theme's app.css, and a generic name
    here would inherit their rules.

    Bootstrap's .btn-group was dropped — it forces its children onto one
    line and, once wrapped, stretches the last item across the full width
    (which is what pushed "Custom Range" onto its own row).
--}}
<style>
    .anf-bar{
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        justify-content:space-between;
        gap:14px;
    }
    .anf-ranges{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin:0;
    }
    .anf-pill{
        display:inline-flex;
        align-items:center;
        height:34px;
        padding:0 15px;
        border-radius:30px;
        border:1px solid #dfe3e8;
        background:#fff;
        color:#4b5563;
        font-size:13px;
        font-weight:600;
        line-height:1;
        white-space:nowrap;
        text-decoration:none;
        transition:.15s;
    }
    .anf-pill:hover{
        border-color:#1c7ed6;
        color:#1c7ed6;
        text-decoration:none;
    }
    .anf-pill.is-active{
        background:#1c7ed6;
        border-color:#1c7ed6;
        color:#fff;
        box-shadow:0 2px 6px rgba(28,126,214,.3);
    }

    /* The two dates and Apply read as one control instead of three
       loose elements squeezed into a column. */
    .anf-daterange{
        display:flex;
        align-items:stretch;
        flex-wrap:nowrap;
        border:1px solid #dfe3e8;
        border-radius:30px;
        background:#fff;
        overflow:hidden;
        max-width:100%;
    }
    .anf-daterange input[type="date"]{
        border:none;
        outline:none;
        height:34px;
        padding:0 12px;
        font-size:13px;
        color:#374151;
        background:transparent;
        min-width:0;
        width:132px;
    }
    .anf-sep{
        display:flex;
        align-items:center;
        padding:0 2px;
        color:#adb5bd;
        font-size:12px;
        user-select:none;
    }
    .anf-apply{
        flex:0 0 auto;
        height:34px;
        padding:0 18px;
        border:none;
        background:#1c7ed6;
        color:#fff;
        font-size:13px;
        font-weight:600;
        white-space:nowrap;
        cursor:pointer;
    }
    .anf-apply:hover{ background:#1971c2; }

    .anf-note{
        font-size:12.5px;
        color:#868e96;
        margin:12px 0 0;
    }
    .anf-note b{ color:#495057; font-weight:600; }

    @media(max-width:575px){
        .anf-bar{ gap:10px; }
        .anf-daterange{ width:100%; }
        .anf-daterange input[type="date"]{ width:auto; flex:1 1 0; }
    }
</style>

<div class="card m-b-30">
    <div class="card-body">
        <div class="anf-bar">
            <div class="anf-ranges">
                @foreach(['today'=>'Today','yesterday'=>'Yesterday','last7'=>'Last 7 Days','last30'=>'Last 30 Days','thisMonth'=>'This Month','custom'=>'Custom Range'] as $key => $label)
                    <a href="{{ route('admin.analytics.index', ['range' => $key]) }}"
                       class="anf-pill {{ $range == $key ? 'is-active' : '' }}">
                        @lang($label)
                    </a>
                @endforeach
            </div>

            @if($range == 'custom')
                <form method="GET" action="{{ route('admin.analytics.index') }}" class="anf-daterange">
                    <input type="hidden" name="range" value="custom">

                    <input type="date" name="from_date" value="{{ $fromDate }}" aria-label="@lang('From date')">
                    <span class="anf-sep">&rarr;</span>
                    <input type="date" name="to_date" value="{{ $toDate }}" aria-label="@lang('To date')">

                    <button type="submit" class="anf-apply">@lang('Apply')</button>
                </form>
            @endif
        </div>

        <p class="anf-note">
            @lang('Showing data from')
            <b>{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</b>
            @lang('to')
            <b>{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</b>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted f-w-5 font-12">@lang('Total Visitors')</p>
                        <h4 class="mb-0">{{ number_format($totalVisitors) }}</h4>
                    </div>
                    <div class="icon-part"><span class="icon-area bg-color-primary"><i class="fas fa-user"></i></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted f-w-5 font-12">@lang('Active Visitors')</p>
                        <h4 class="mb-0">{{ number_format($activeVisitors) }}</h4>
                    </div>
                    <div class="icon-part"><span class="icon-area bg-color-success"><i class="fas fa-bolt"></i></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted f-w-5 font-12">@lang('Returning Visitors')</p>
                        <h4 class="mb-0">{{ number_format($returningVisitors) }}</h4>
                    </div>
                    <div class="icon-part"><span class="icon-area bg-color-info"><i class="fas fa-redo"></i></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted f-w-5 font-12">@lang('Bounce Rate')</p>
                        <h4 class="mb-0">{{ $bounceRate }}%</h4>
                    </div>
                    <div class="icon-part"><span class="icon-area bg-color-danger"><i class="fas fa-sign-out-alt"></i></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted f-w-5 font-12">@lang('Total Sessions')</p>
                        <h4 class="mb-0">{{ number_format($totalSessions) }}</h4>
                    </div>
                    <div class="icon-part"><span class="icon-area bg-color-warning"><i class="fas fa-stream"></i></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted f-w-5 font-12">@lang('Avg Session Duration')</p>
                        <h4 class="mb-0">{{ gmdate('i:s', $avgSessionDuration) }}</h4>
                    </div>
                    <div class="icon-part"><span class="icon-area bg-color-primary"><i class="fas fa-clock"></i></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Daily Visitors')</h6></div>
            <div class="card-body">
                <canvas id="dailyVisitorsChart" height="90"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Device Distribution')</h6></div>
            <div class="card-body">
                <canvas id="deviceChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card m-b-30">
            <div class="card-header">
                <div class="row">
                    <div class="col-6"><h6>@lang('Top Listings')</h6></div>
                    <div class="col-6 text-right">
                        <a href="{{ route('admin.analytics.export.excel', request()->query()) }}" class="btn btn-xs btn-outline-primary">@lang('Excel')</a>
                        <a href="{{ route('admin.analytics.export.csv', request()->query()) }}" class="btn btn-xs btn-outline-secondary">@lang('CSV')</a>
                        <a href="{{ route('admin.analytics.export.pdf', request()->query()) }}" class="btn btn-xs btn-outline-danger">@lang('PDF')</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Listing')</th><th class="text-center">@lang('Views')</th><th class="text-center">@lang('Unique Views')</th></tr></thead>
                        <tbody>
                            @forelse($topListings as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-center">{{ number_format($row->total_views) }}</td>
                                    <td class="text-center">{{ number_format($row->total_unique_views) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Top Categories')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Category')</th><th class="text-center">@lang('Views')</th><th class="text-center">@lang('Unique Views')</th></tr></thead>
                        <tbody>
                            @forelse($topCategories as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-center">{{ number_format($row->views) }}</td>
                                    <td class="text-center">{{ number_format($row->unique_views) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Top Locations')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Location')</th><th class="text-center">@lang('Views')</th></tr></thead>
                        <tbody>
                            @forelse($topLocations as $row)
                                <tr><td>{{ $row->name }}</td><td class="text-center">{{ number_format($row->views) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Top Pages')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Page')</th><th class="text-center">@lang('Views')</th><th class="text-center">@lang('Avg Time')</th></tr></thead>
                        <tbody>
                            @forelse($topPages as $row)
                                <tr>
                                    <td>{{ $row->page_name }}</td>
                                    <td class="text-center">{{ number_format($row->views) }}</td>
                                    <td class="text-center">{{ $row->avg_time ? gmdate('i:s', (int) $row->avg_time) : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Most Searched Keywords')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Keyword')</th><th class="text-center">@lang('Searches')</th></tr></thead>
                        <tbody>
                            @forelse($topKeywords as $row)
                                <tr><td>{{ $row->keyword }}</td><td class="text-center">{{ number_format($row->search_count) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('No-Result Searches')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Keyword')</th><th class="text-center">@lang('Occurrences')</th></tr></thead>
                        <tbody>
                            @forelse($noResultSearches as $row)
                                <tr><td>{{ $row->keyword }}</td><td class="text-center">{{ number_format($row->occurrences) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Browser Distribution')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Browser')</th><th class="text-center">@lang('Sessions')</th></tr></thead>
                        <tbody>
                            @forelse($browserDistribution as $row)
                                <tr><td>{{ $row->browser }}</td><td class="text-center">{{ number_format($row->total) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">@lang('No data')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Country Distribution')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Country')</th><th class="text-center">@lang('Sessions')</th></tr></thead>
                        <tbody>
                            @forelse($countryDistribution as $row)
                                <tr><td>{{ $row->country }}</td><td class="text-center">{{ number_format($row->total) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">@lang('No geo data yet')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Conversions')</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive widget-table">
                    <table class="table table-borderless">
                        <thead><tr><th>@lang('Type')</th><th class="text-center">@lang('Total')</th></tr></thead>
                        <tbody>
                            @forelse($conversionTotals as $row)
                                <tr><td>{{ ucwords(str_replace('_', ' ', $row->event_type)) }}</td><td class="text-center">{{ number_format($row->total) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">@lang('No data for this range')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-header"><h6>@lang('Conversion Funnel')</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    @php
                        $funnelLabels = ['visited'=>'Visited','viewed_listing'=>'Viewed a Listing','engaged'=>'Engaged (Call/WhatsApp/Enquiry)','account'=>'Registered/Logged In','paid'=>'Paid'];
                    @endphp
                    @foreach($funnelLabels as $key => $label)
                        <div class="col">
                            <h4 class="mb-0">{{ number_format($funnel[$key] ?? 0) }}</h4>
                            <p class="text-muted font-12">@lang($label)</p>
                        </div>
                        @if(!$loop->last)
                            <div class="col-auto d-flex align-items-center"><i class="fas fa-arrow-right text-muted"></i></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js-link')
<script src="{{asset('assets/admin/js/chartjs.min.js') }}"></script>
@endpush

@push('js')
<script>
    "use strict";

    var dailyVisitorsCtx = document.getElementById("dailyVisitorsChart").getContext('2d');
    new Chart(dailyVisitorsCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($dailyVisitors as $row)
                    '{{ \Carbon\Carbon::parse($row->day)->format('d M') }}',
                @endforeach
            ],
            datasets: [{
                label: 'Visitors',
                backgroundColor: 'rgba(110,129,220,0.15)',
                borderColor: '#6e81dc',
                fill: true,
                data: [
                    @foreach($dailyVisitors as $row)
                        {{ (int) $row->visitors }},
                    @endforeach
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            legend: { display: false },
            title: { display: false }
        }
    });

    var deviceCtx = document.getElementById("deviceChart").getContext('2d');
    new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($deviceDistribution as $row)
                    '{{ ucfirst($row->device_type) }}',
                @endforeach
            ],
            datasets: [{
                backgroundColor: ['#6e81dc', '#28a745', '#ffc107', '#dc3545'],
                data: [
                    @foreach($deviceDistribution as $row)
                        {{ (int) $row->total }},
                    @endforeach
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            legend: { display: true, position: 'bottom' }
        }
    });
</script>
@endpush
