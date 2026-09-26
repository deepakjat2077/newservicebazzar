@extends('admin.layouts.master')
@section('content')
@section('title','Traffic Sources')

<style>
    .sr-bar{ display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; }
    .sr-pills{ display:flex; flex-wrap:wrap; gap:8px; }
    .sr-pill{
        display:inline-flex; align-items:center; height:34px; padding:0 15px; border-radius:30px;
        border:1px solid #dfe3e8; background:#fff; color:#4b5563; font-size:13px; font-weight:600;
        white-space:nowrap; text-decoration:none; transition:.15s;
    }
    .sr-pill:hover{ border-color:#1c7ed6; color:#1c7ed6; text-decoration:none; }
    .sr-pill.is-active{ background:#1c7ed6; border-color:#1c7ed6; color:#fff; }
    .sr-dates{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .sr-dates input{ height:34px; border:1px solid #dfe3e8; border-radius:6px; padding:0 8px; }
    .sr-dates button{ height:34px; border:0; border-radius:6px; background:#1c7ed6; color:#fff; padding:0 16px; font-weight:600; }
    .sr-note{ font-size:12.5px; color:#868e96; margin:12px 0 0; }
    .sr-note b{ color:#495057; }
    .sr-num{ text-align:right; white-space:nowrap; }
    .sr-tag{ display:inline-block; padding:2px 9px; border-radius:20px; background:#e7f1fb; color:#1c7ed6; font-size:12px; font-weight:600; }
    .sr-tot td{ font-weight:700; background:#f8f9fa; }
    .sr-url{ display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
    .sr-url input[type=text]{ flex:1 1 320px; height:38px; border:1px solid #dfe3e8; border-radius:6px; padding:0 12px; }
    .sr-url button{ height:38px; border:0; border-radius:6px; background:#1c7ed6; color:#fff; padding:0 22px; font-weight:600; }
    .sr-tiles{ display:flex; flex-wrap:wrap; gap:12px; margin-top:18px; }
    .sr-tile{ flex:1 1 130px; border:1px solid #eef0f3; border-radius:8px; padding:12px 14px; }
    .sr-tile b{ display:block; font-size:22px; color:#212529; }
    .sr-tile span{ font-size:12px; color:#868e96; }
</style>

<div class="card m-b-30">
    <div class="card-body">
        <div class="sr-bar">
            <div class="sr-pills">
                @foreach(['today'=>'Today','yesterday'=>'Yesterday','last7'=>'Last 7 Days','last30'=>'Last 30 Days','thisMonth'=>'This Month','custom'=>'Custom Range'] as $key => $label)
                    <a href="{{ route('admin.analytics.sources', ['range' => $key, 'group' => $group, 'url' => $urlInput, 'prefix' => $urlPrefix ? 1 : null]) }}" class="sr-pill {{ $range == $key ? 'is-active' : '' }}">@lang($label)</a>
                @endforeach
            </div>
            <div class="sr-pills">
                <a href="{{ route('admin.analytics.sources', ['range' => $range, 'group' => 'source', 'from_date' => $fromDate, 'to_date' => $toDate, 'url' => $urlInput, 'prefix' => $urlPrefix ? 1 : null]) }}" class="sr-pill {{ $group == 'source' ? 'is-active' : '' }}">@lang('By Source')</a>
                <a href="{{ route('admin.analytics.sources', ['range' => $range, 'group' => 'campaign', 'from_date' => $fromDate, 'to_date' => $toDate, 'url' => $urlInput, 'prefix' => $urlPrefix ? 1 : null]) }}" class="sr-pill {{ $group == 'campaign' ? 'is-active' : '' }}">@lang('By Campaign')</a>
            </div>
        </div>

        @if($range == 'custom')
            <form method="GET" action="{{ route('admin.analytics.sources') }}" class="sr-dates m-t-15">
                <input type="hidden" name="range" value="custom">
                <input type="hidden" name="group" value="{{ $group }}">
                <input type="hidden" name="url" value="{{ $urlInput }}">
                @if($urlPrefix)<input type="hidden" name="prefix" value="1">@endif
                <input type="date" name="from_date" value="{{ $fromDate }}">
                <span>&rarr;</span>
                <input type="date" name="to_date" value="{{ $toDate }}">
                <button type="submit">@lang('Apply')</button>
            </form>
        @endif

        <p class="sr-note">
            @lang('Showing data from') <b>{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</b> @lang('to') <b>{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</b>.
            @lang('Calls, WhatsApp, directions and enquiries are counted in the session they happened in. Payments go to the visitor\'s latest campaign/referral session in the 30 days before paying. Lead rate = (calls + WhatsApp + enquiries) / sessions. Bots and admin traffic are excluded.')
        </p>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header"><h6>@lang('Check traffic of a URL')</h6></div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.analytics.sources') }}" class="sr-url">
            <input type="hidden" name="range" value="{{ $range }}">
            <input type="hidden" name="group" value="{{ $group }}">
            @if($range == 'custom')
                <input type="hidden" name="from_date" value="{{ $fromDate }}">
                <input type="hidden" name="to_date" value="{{ $toDate }}">
            @endif
            <input type="text" name="url" value="{{ $urlInput }}" placeholder="https://www.servicebazzar.com/plumber-in-jaipur  or  /plumber-in-jaipur">
            <label class="m-0"><input type="checkbox" name="prefix" value="1" {{ $urlPrefix ? 'checked' : '' }}> @lang('Include all pages under it')</label>
            <button type="submit">@lang('Check')</button>
        </form>

        @if($urlReport)
            <p class="sr-note">
                <b>{{ $urlTarget['label'] }}</b>@if($urlPrefix) @lang('and everything under it')@endif,
                {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
            </p>

            @if($urlReport['totals']['views'] == 0)
                <p class="text-muted m-t-15">@lang('No visits recorded for this URL in this range. Check the spelling, or widen the date range.')</p>
            @else
                <div class="sr-tiles">
                    <div class="sr-tile"><b>{{ number_format($urlReport['totals']['views']) }}</b><span>@lang('Page views')</span></div>
                    <div class="sr-tile"><b>{{ number_format($urlReport['totals']['visitors']) }}</b><span>@lang('Unique visitors')</span></div>
                    <div class="sr-tile"><b>{{ $urlReport['totals']['avg_time'] === null ? '—' : $urlReport['totals']['avg_time'] . 's' }}</b><span>@lang('Avg time on page')</span></div>
                    <div class="sr-tile"><b>{{ $urlReport['totals']['exit_rate'] === null ? '—' : $urlReport['totals']['exit_rate'] . '%' }}</b><span>@lang('Exit rate')</span></div>
                    <div class="sr-tile"><b>{{ number_format($urlReport['actions']['calls']) }}</b><span>@lang('Calls')</span></div>
                    <div class="sr-tile"><b>{{ number_format($urlReport['actions']['whatsapp']) }}</b><span>@lang('WhatsApp')</span></div>
                    <div class="sr-tile"><b>{{ number_format($urlReport['actions']['directions']) }}</b><span>@lang('Directions')</span></div>
                    <div class="sr-tile"><b>{{ number_format($urlReport['actions']['enquiries']) }}</b><span>@lang('Enquiries')</span></div>
                </div>

                <div class="row m-t-20">
                    <div class="col-lg-7">
                        <h6 class="m-b-10">@lang('Where the visits came from')</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead><tr><th>@lang('Source')</th><th>@lang('Medium')</th><th class="sr-num">@lang('Views')</th><th class="sr-num">@lang('Visitors')</th></tr></thead>
                                <tbody>
                                    @foreach(array_slice($urlReport['channels'], 0, 15) as $c)
                                        <tr><td><span class="sr-tag">{{ $c['source'] }}</span></td><td>{{ $c['medium'] }}</td><td class="sr-num">{{ number_format($c['views']) }}</td><td class="sr-num">{{ number_format($c['visitors']) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <h6 class="m-b-10">@lang('Day by day')</h6>
                        <div class="table-responsive" style="max-height:340px;overflow:auto">
                            <table class="table table-borderless">
                                <thead><tr><th>@lang('Date')</th><th class="sr-num">@lang('Views')</th><th class="sr-num">@lang('Visitors')</th></tr></thead>
                                <tbody>
                                    @foreach($urlReport['daily'] as $d)
                                        <tr><td>{{ \Carbon\Carbon::parse($d->d)->format('d M') }}</td><td class="sr-num">{{ number_format($d->views) }}</td><td class="sr-num">{{ number_format($d->visitors) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <p class="sr-note">@lang('Google search keywords for this URL are not available here yet: that data lives in Google Search Console, which is not connected. "google / organic" above shows how many visits came from Google search overall.')</p>
            @endif
        @endif
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header"><h6>@lang('Source-wise Conversions')</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        @if($group == 'campaign')<th>@lang('Campaign')</th>@endif
                        <th>@lang('Source')</th>
                        <th>@lang('Medium')</th>
                        <th class="sr-num">@lang('Sessions')</th>
                        <th class="sr-num">@lang('Visitors')</th>
                        <th class="sr-num">@lang('Calls')</th>
                        <th class="sr-num">@lang('WhatsApp')</th>
                        <th class="sr-num">@lang('Directions')</th>
                        <th class="sr-num">@lang('Enquiries')</th>
                        <th class="sr-num">@lang('Payments')</th>
                        <th class="sr-num">@lang('Revenue')</th>
                        <th class="sr-num">@lang('Lead rate')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            @if($group == 'campaign')<td>{{ $r['campaign'] }}</td>@endif
                            <td><span class="sr-tag">{{ $r['source'] }}</span></td>
                            <td>{{ $r['medium'] }}</td>
                            <td class="sr-num">{{ number_format($r['sessions']) }}</td>
                            <td class="sr-num">{{ number_format($r['visitors']) }}</td>
                            <td class="sr-num">{{ number_format($r['calls']) }}</td>
                            <td class="sr-num">{{ number_format($r['whatsapp']) }}</td>
                            <td class="sr-num">{{ number_format($r['directions']) }}</td>
                            <td class="sr-num">{{ number_format($r['enquiries']) }}</td>
                            <td class="sr-num">{{ number_format($r['payments']) }}</td>
                            <td class="sr-num">{{ number_format($r['revenue'], 2) }}</td>
                            <td class="sr-num">{{ $r['lead_rate'] === null ? '—' : $r['lead_rate'] . '%' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted p-4">@lang('No traffic in this range.')</td></tr>
                    @endforelse
                    @if(!empty($hidden))
                        <tr><td colspan="12" class="text-center text-muted">{{ $hidden }} @lang('smaller rows not shown; totals below include them.')</td></tr>
                    @endif
                    @if(count($rows))
                        <tr class="sr-tot">
                            <td @if($group == 'campaign') colspan="3" @else colspan="2" @endif>@lang('Total')</td>
                            <td class="sr-num">{{ number_format($totals['sessions']) }}</td>
                            <td class="sr-num">{{ number_format($totals['visitors']) }}</td>
                            <td class="sr-num">{{ number_format($totals['calls']) }}</td>
                            <td class="sr-num">{{ number_format($totals['whatsapp']) }}</td>
                            <td class="sr-num">{{ number_format($totals['directions']) }}</td>
                            <td class="sr-num">{{ number_format($totals['enquiries']) }}</td>
                            <td class="sr-num">{{ number_format($totals['payments']) }}</td>
                            <td class="sr-num">{{ number_format($totals['revenue'], 2) }}</td>
                            <td class="sr-num">{{ $totals['lead_rate'] === null ? '—' : $totals['lead_rate'] . '%' }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
