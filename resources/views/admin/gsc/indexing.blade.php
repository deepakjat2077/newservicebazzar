@extends('admin.layouts.master')
@section('title','Google Indexing')
@section('content')

<style>
.gi-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:20px; }
.gi-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; }
.gi-card-label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px; }
.gi-card-value { font-size:24px; font-weight:700; color:#1e293b; }
.gi-card-note { font-size:12px; color:#94a3b8; margin-top:2px; }
.gi-card.ok .gi-card-value { color:#059669; }
.gi-card.bad .gi-card-value { color:#dc2626; }
.gi-card.wait .gi-card-value { color:#d97706; }
.gi-card.blue .gi-card-value { color:#2563eb; }
.gi-url { word-break:break-all; font-size:12px; }
.gi-table td, .gi-table th { vertical-align:middle; }
</style>

@if(!$configured)
    <div class="alert alert-danger">The Google key file is missing or not readable, so nothing is being sent to Google.</div>
@endif

@if(!empty($lastRun['error']))
    <div class="alert alert-warning">
        <strong>Last send run ({{ \Carbon\Carbon::parse($lastRun['at'])->format('d M, h:i A') }}) had a problem:</strong>
        {{ $lastRun['error'] }}
    </div>
@endif

@if($inspectError)
    <div class="alert alert-warning">
        <strong>"Indexed on Google?" check is not working yet</strong>
        (HTTP {{ $inspectError['status'] }}, {{ \Carbon\Carbon::parse($inspectError['at'])->format('d M, h:i A') }}).
        @if(str_contains((string) $inspectError['error'], 'has not been used') || str_contains((string) $inspectError['error'], 'disabled'))
            Turn on <strong>Google Search Console API</strong> in the Google Cloud project of the indexing key:
            <a href="https://console.cloud.google.com/apis/library/searchconsole.googleapis.com?project=servicebazzar" target="_blank" rel="noopener">open it in Google Cloud</a> → Enable.
            Sending to Google is not affected.
        @else
            <span class="d-block small mt-1">{{ $inspectError['error'] }}</span>
        @endif
    </div>
@endif

<div class="gi-cards">
    <div class="gi-card blue">
        <div class="gi-card-label">Sent today</div>
        <div class="gi-card-value">{{ $sentToday }} <small style="font-size:14px;color:#94a3b8;">/ 200</small></div>
        <div class="gi-card-note">Google's daily limit</div>
    </div>
    <div class="gi-card ok">
        <div class="gi-card-label">Sent to Google</div>
        <div class="gi-card-value">{{ number_format($totals->sent) }}</div>
        <div class="gi-card-note">Accepted by the Indexing API</div>
    </div>
    <div class="gi-card bad">
        <div class="gi-card-label">Send failed</div>
        <div class="gi-card-value">{{ number_format($totals->failed) }}</div>
        <div class="gi-card-note">Retried automatically</div>
    </div>
    <div class="gi-card wait">
        <div class="gi-card-label">Waiting to send</div>
        <div class="gi-card-value">{{ isset($lastRun['waiting']) ? number_format($lastRun['waiting']) : '-' }}</div>
        <div class="gi-card-note">
            @if($lastRun)
                Last run {{ \Carbon\Carbon::parse($lastRun['at'])->diffForHumans() }}
            @else
                Updated after the next hourly run
            @endif
        </div>
    </div>
    <div class="gi-card ok">
        <div class="gi-card-label">Indexed on Google</div>
        <div class="gi-card-value">{{ number_format($totals->indexed) }}</div>
        <div class="gi-card-note">As per Search Console</div>
    </div>
    <div class="gi-card bad">
        <div class="gi-card-label">Not indexed yet</div>
        <div class="gi-card-value">{{ number_format($totals->not_indexed) }}</div>
        <div class="gi-card-note">Re-checked every 3 days</div>
    </div>
    <div class="gi-card">
        <div class="gi-card-label">Not checked yet</div>
        <div class="gi-card-value">{{ number_format($totals->unchecked) }}</div>
        <div class="gi-card-note">Checked 1 day after sending</div>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white mb-0">By page type</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 gi-table">
            <thead>
                <tr>
                    <th>Page type</th>
                    <th class="text-center">Sent</th>
                    <th class="text-center">Failed</th>
                    <th class="text-center">Indexed</th>
                    <th class="text-center">Not indexed</th>
                    <th class="text-center">Not checked</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sources as $key => $label)
                    @php $s = $bySource->get($key); @endphp
                    <tr>
                        <td><a href="{{ route('admin.gsc.indexing', ['source' => $key]) }}">{{ $label }} pages</a></td>
                        <td class="text-center">{{ number_format($s->sent ?? 0) }}</td>
                        <td class="text-center">{{ number_format($s->failed ?? 0) }}</td>
                        <td class="text-center text-success font-weight-bold">{{ number_format($s->indexed ?? 0) }}</td>
                        <td class="text-center text-danger">{{ number_format($s->not_indexed ?? 0) }}</td>
                        <td class="text-center text-muted">{{ number_format($s->unchecked ?? 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-body">
        <form method="get" class="form-row">
            <div class="form-group col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search URL, e.g. jaipur or body-massage">
            </div>
            <div class="form-group col-md-2">
                <select name="source" class="form-control">
                    <option value="">All page types</option>
                    @foreach($sources as $key => $label)
                        <option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <select name="status" class="form-control">
                    <option value="">Any send status</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <select name="google" class="form-control">
                    <option value="">Any Google status</option>
                    <option value="indexed" @selected(request('google') === 'indexed')>Indexed</option>
                    <option value="not_indexed" @selected(request('google') === 'not_indexed')>Not indexed</option>
                    <option value="unchecked" @selected(request('google') === 'unchecked')>Not checked</option>
                </select>
            </div>
            <div class="form-group col-md-1">
                <button class="btn btn-primary btn-block">Filter</button>
            </div>
            <div class="form-group col-md-1">
                <a href="{{ route('admin.gsc.indexing') }}" class="btn btn-light btn-block">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white mb-0">Pages sent to Google <span class="badge badge-light ml-1">{{ number_format($rows->total()) }}</span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 gi-table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Type</th>
                    <th>Sent at</th>
                    <th>Send result</th>
                    <th>On Google</th>
                    <th>Google last crawled</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="gi-url">
                            <a href="{{ $row->url }}" target="_blank" rel="noopener">{{ str_replace('https://www.servicebazzar.com', '', $row->url) ?: '/' }}</a>
                        </td>
                        <td>{{ $sources[$row->source] ?? ucfirst($row->source) }}</td>
                        <td><small>{{ $row->notified_at ? \Carbon\Carbon::parse($row->notified_at)->format('d M Y, h:i A') : '-' }}</small></td>
                        <td>
                            @if((int) $row->http_status === 200)
                                <span class="badge badge-success">Sent</span>
                            @else
                                <span class="badge badge-danger" title="{{ $row->error }}">Failed{{ $row->http_status ? ' (' . $row->http_status . ')' : '' }}</span>
                                @if($row->error)
                                    <small class="d-block text-muted" style="max-width:220px;">{{ \Illuminate\Support\Str::limit($row->error, 80) }}</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if(!$row->inspected_at)
                                <span class="badge badge-secondary">Not checked</span>
                            @elseif($row->index_verdict === 'PASS')
                                <span class="badge badge-success">Indexed</span>
                            @else
                                <span class="badge badge-warning">Not indexed</span>
                            @endif
                            @if($row->coverage_state)
                                <small class="d-block text-muted">{{ $row->coverage_state }}</small>
                            @endif
                            @if($row->inspected_at)
                                <small class="d-block text-muted">checked {{ \Carbon\Carbon::parse($row->inspected_at)->format('d M, h:i A') }}</small>
                            @endif
                        </td>
                        <td><small>{{ $row->last_crawl_at ? \Carbon\Carbon::parse($row->last_crawl_at)->format('d M Y, h:i A') : '-' }}</small></td>
                        <td class="text-right">
                            @if((int) $row->http_status === 200)
                                <form action="{{ route('admin.gsc.indexing.inspect', $row->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Check now</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="100%" class="text-center">Nothing sent yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $rows->links() }}</div>
</div>

<p class="small text-muted">
    Every hour up to 9 pages are sent (category pages first, then blogs, subcategories and listings), and up to 50 sent pages are checked in Search Console.
    "Sent" only means Google received the notice. Whether a page gets indexed is Google's decision, and it can take days.
</p>
@endsection
