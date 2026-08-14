@extends('admin.layouts.master')

@section('title', 'SEO Audit')

@section('content')

<style>
    .seo-stat-card{
        border:1px solid #e9ecef;
        border-radius:12px;
        padding:14px 16px;
        background:#fff;
        height:100%;
    }
    .seo-stat-value{
        font-size:28px;
        font-weight:800;
        line-height:1.1;
    }
    .seo-stat-label{
        font-size:12px;
        color:#6c757d;
        margin-top:3px;
    }

    /* Issue filter strip -------------------------------------------------- */
    .issue-chips{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }
    .issue-chip{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:6px 12px;
        border-radius:30px;
        border:1px solid #e5e7eb;
        background:#fff;
        font-size:12.5px;
        font-weight:600;
        color:#374151;
        text-decoration:none;
        transition:.15s;
    }
    .issue-chip:hover{
        border-color:#adb5bd;
        color:#111827;
        text-decoration:none;
    }
    .issue-chip .dot{
        width:8px;
        height:8px;
        border-radius:50%;
        flex:0 0 8px;
    }
    .issue-chip .dot.error{ background:#e03131; }
    .issue-chip .dot.warning{ background:#f59f00; }
    .issue-chip .count{
        background:#f1f3f5;
        color:#495057;
        border-radius:20px;
        padding:1px 8px;
        font-size:11.5px;
        font-weight:700;
    }
    .issue-chip.active{
        background:#1c7ed6;
        border-color:#1c7ed6;
        color:#fff;
    }
    .issue-chip.active .count{
        background:rgba(255,255,255,.25);
        color:#fff;
    }
    .issue-chip.active .dot{ background:#fff; }

    /* Table --------------------------------------------------------------- */
    .seo-table{
        table-layout:fixed;
        width:100%;
        margin-bottom:0;
    }
    .seo-table th{
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.03em;
        color:#6c757d;
        border-top:none;
        white-space:nowrap;
    }
    .seo-table td{
        vertical-align:middle;
        word-break:break-word;
    }
    .seo-table tbody tr:hover{
        background:#f8f9fa;
    }
    .col-page{ width:30%; }
    .col-score{ width:88px; }
    .col-issues{ width:auto; }
    .col-action{ width:96px; }

    .page-name{
        font-weight:600;
        color:#212529;
        line-height:1.35;
    }
    .page-meta{
        font-size:11.5px;
        color:#868e96;
        margin-top:2px;
    }
    .type-badge{
        display:inline-block;
        font-size:10.5px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.04em;
        padding:2px 7px;
        border-radius:5px;
        background:#eef2f7;
        color:#5c7099;
        margin-bottom:4px;
    }

    .issue-badge{
        display:inline-block;
        font-size:11.5px;
        font-weight:600;
        padding:3px 9px;
        border-radius:20px;
        margin:2px 5px 2px 0;
        white-space:nowrap;
        cursor:default;
    }
    .issue-badge.error{
        background:#fdeaea;
        color:#c92a2a;
    }
    .issue-badge.warning{
        background:#fff4e0;
        color:#b45309;
    }
    .issue-badge .detail{
        opacity:.7;
        font-weight:500;
    }
    .issue-more{
        font-size:11.5px;
        font-weight:600;
        color:#868e96;
        white-space:nowrap;
    }

    .score-pill{
        display:inline-block;
        min-width:44px;
        text-align:center;
        font-weight:800;
        font-size:13px;
        padding:5px 8px;
        border-radius:8px;
        color:#fff;
    }
    .score-good{ background:#2f9e44; }
    .score-ok{ background:#f59f00; }
    .score-bad{ background:#c92a2a; }

    @media(max-width:767px){
        .seo-table{ table-layout:auto; }
        .col-page{ min-width:180px; }
        .col-issues{ min-width:240px; }
    }
</style>

{{-- Stats ------------------------------------------------------------- --}}
<div class="row">
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="seo-stat-card">
            <div class="seo-stat-value">{{ number_format($summary['total']) }}</div>
            <div class="seo-stat-label">Pages scanned</div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="seo-stat-card">
            <div class="seo-stat-value text-danger">{{ number_format($summary['with_errors']) }}</div>
            <div class="seo-stat-label">With errors</div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="seo-stat-card">
            <div class="seo-stat-value text-warning">{{ number_format($summary['with_issues'] - $summary['with_errors']) }}</div>
            <div class="seo-stat-label">Warnings only</div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="seo-stat-card">
            <div class="seo-stat-value text-success">{{ number_format($summary['clean']) }}</div>
            <div class="seo-stat-label">Clean pages</div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="seo-stat-card">
            <div class="seo-stat-value">
                {{ $summary['avg_score'] }}<span style="font-size:15px;color:#adb5bd">/100</span>
            </div>
            <div class="seo-stat-label">Average score</div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="seo-stat-card d-flex flex-column justify-content-center">
            <form action="{{ route('admin.seo.audit.rescan') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="las la-sync"></i> Re-scan
                </button>
            </form>

            @if($scannedAt)
                <small class="text-muted d-block mt-2" style="font-size:11px">
                    {{ \Carbon\Carbon::parse($scannedAt)->diffForHumans() }}
                </small>
            @endif
        </div>
    </div>
</div>

{{-- Issue filter chips ------------------------------------------------- --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong style="font-size:14px">Issues found</strong>

            @if(request('issue'))
                <a href="{{ request()->fullUrlWithQuery(['issue' => null, 'page' => null]) }}"
                   class="btn btn-light btn-sm">
                    Clear issue filter
                </a>
            @endif
        </div>

        <div class="issue-chips">
            @forelse($summary['issues'] as $code => $count)
                <a href="{{ request()->fullUrlWithQuery(['issue' => $code, 'page' => null]) }}"
                   class="issue-chip {{ request('issue') === $code ? 'active' : '' }}"
                   title="{{ $issueLabels[$code] ?? $code }}">
                    <span class="dot {{ \App\Services\SeoAuditor::severityOf($code) }}"></span>
                    {{ $shortLabels[$code] ?? $code }}
                    <span class="count">{{ number_format($count) }}</span>
                </a>
            @empty
                <span class="text-success">No SEO issues found. 🎉</span>
            @endforelse
        </div>
    </div>
</div>

{{-- Results ------------------------------------------------------------ --}}
<div class="card">
    <div class="card-body p-0">

        <div class="p-3 border-bottom">
            <form method="get" action="">
                {{-- Keep the chip filter active while searching --}}
                @if(request('issue'))
                    <input type="hidden" name="issue" value="{{ request('issue') }}">
                @endif

                <div class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="mb-1">Search</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control" placeholder="Page name">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="mb-1">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All types</option>
                            @foreach($summary['types'] as $type => $count)
                                <option value="{{ $type }}" @selected(request('type') === $type)>
                                    {{ $type }} ({{ number_format($count) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="mb-1">Severity</label>
                        <select name="severity" class="form-control">
                            <option value="">All</option>
                            <option value="error" @selected(request('severity') === 'error')>Has errors</option>
                            <option value="warning" @selected(request('severity') === 'warning')>Has warnings</option>
                            <option value="clean" @selected(request('severity') === 'clean')>Clean only</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-block mr-2">Filter</button>
                        <a href="{{ url()->current() }}" class="btn btn-light btn-block">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-3 pt-3 pb-1 text-muted" style="font-size:12.5px">
            Showing {{ number_format($results->firstItem() ?? 0) }}–{{ number_format($results->lastItem() ?? 0) }}
            of {{ number_format($results->total()) }} pages, worst first
        </div>

        <div class="table-responsive">
            <table class="table seo-table">
                <thead>
                    <tr>
                        <th class="col-page">Page</th>
                        <th class="col-score">Score</th>
                        <th class="col-issues">Issues</th>
                        <th class="col-action text-right">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td class="col-page">
                                <span class="type-badge">{{ $row['type'] }}</span>
                                <div class="page-name">{{ \Illuminate\Support\Str::limit($row['name'], 70) }}</div>
                                <div class="page-meta">
                                    #{{ $row['id'] }}
                                    @if($row['errors'])
                                        · <span class="text-danger">{{ $row['errors'] }} error{{ $row['errors'] > 1 ? 's' : '' }}</span>
                                    @endif
                                    @if($row['warnings'])
                                        · <span class="text-warning">{{ $row['warnings'] }} warning{{ $row['warnings'] > 1 ? 's' : '' }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="col-score">
                                @php
                                    $scoreClass = $row['score'] >= 80
                                        ? 'score-good'
                                        : ($row['score'] >= 50 ? 'score-ok' : 'score-bad');
                                @endphp

                                <span class="score-pill {{ $scoreClass }}">{{ $row['score'] }}</span>
                            </td>

                            <td class="col-issues">
                                @php
                                    // Cap the badges so one bad page can't stretch the row.
                                    $shown  = array_slice($row['issues'], 0, 5);
                                    $hidden = array_slice($row['issues'], 5);
                                @endphp

                                @forelse($shown as $issue)
                                    <span class="issue-badge {{ $issue['severity'] }}"
                                          title="{{ $issueLabels[$issue['code']] ?? $issue['code'] }}">
                                        {{ $shortLabels[$issue['code']] ?? $issue['code'] }}
                                        @if($issue['detail'])
                                            <span class="detail">· {{ $issue['detail'] }}</span>
                                        @endif
                                    </span>
                                @empty
                                    <span class="text-success" style="font-size:12.5px">
                                        <i class="las la-check-circle"></i> No issues
                                    </span>
                                @endforelse

                                @if($hidden)
                                    <span class="issue-more"
                                          title="{{ collect($hidden)->map(fn ($i) => $issueLabels[$i['code']] ?? $i['code'])->implode(', ') }}">
                                        +{{ count($hidden) }} more
                                    </span>
                                @endif
                            </td>

                            <td class="col-action text-right">
                                <a href="{{ $row['edit_url'] }}" class="btn btn-primary btn-sm">
                                    Fix
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-5 text-muted">
                                Nothing matches these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($results->hasPages())
        <div class="card-footer">
            {{ $results->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection
