@extends('admin.layouts.master')
@section('title', 'KYC Review')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">{{ $listing->name }}</h5>
                        <p class="mb-1 text-muted" style="font-size:13px;">
                            Sales User: <strong>{{ optional($listing->salesUser)->name ?? '—' }}</strong>
                            &nbsp;|&nbsp; {{ $listing->area }}, {{ $listing->city }} - {{ $listing->pincode }}
                            &nbsp;|&nbsp; Mobile: {{ $listing->mobile }}
                        </p>
                    </div>
                    <a href="{{ route('admin.sales_kyc.index') }}" class="btn btn-sm btn-light">&larr; Back</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    @foreach($types as $key => $t)
                        @php $items = $docs[$key] ?? collect(); @endphp
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $t[0] }}</strong>
                                    @if($t[1])<span class="badge badge-warning">Mandatory</span>@endif
                                </div>

                                @if($items->count())
                                    @foreach($items as $doc)
                                        <div class="d-flex align-items-center justify-content-between border-top py-2">
                                            <a href="{{ asset($doc->path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file"></i> View Document
                                            </a>
                                            <span>
                                                @if($doc->status=='approved')<span class="badge badge-success">Approved</span>
                                                @elseif($doc->status=='rejected')<span class="badge badge-danger">Rejected</span>
                                                @else<span class="badge badge-warning">In Review</span>@endif
                                            </span>
                                            <span>
                                                <form action="{{ route('admin.sales_kyc.status', [$listing->id, $doc->id]) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <button class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form action="{{ route('admin.sales_kyc.status', [$listing->id, $doc->id]) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                                                </form>
                                            </span>
                                        </div>
                                        @if(in_array($key, \App\Support\KycReader::TYPES, true))
                                            @php $ai = $doc->ai_check; @endphp
                                            <div class="kyc-ai">
                                                <form action="{{ route('admin.sales_kyc.number', [$listing->id, $doc->id]) }}" method="POST" class="kyc-ai-num">
                                                    @csrf
                                                    <label>{{ $key === 'pan' ? 'PAN' : 'GSTIN' }}</label>
                                                    <input type="text" name="doc_number" value="{{ $doc->doc_number }}" maxlength="15" placeholder="{{ $key === 'pan' ? 'AAAAA0000A' : '08AAAAA0000A1Z5' }}">
                                                    <button class="btn btn-sm btn-outline-secondary">Save</button>
                                                </form>
                                                <form action="{{ route('admin.sales_kyc.ai_read', [$listing->id, $doc->id]) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-magic"></i> {{ $ai ? 'Read again with AI' : 'Read with AI' }}</button>
                                                </form>
                                                @if($ai)
                                                    <div class="kyc-ai-result">
                                                        @if(empty($ai['ok']))
                                                            <div class="kyc-bad">{{ $ai['message'] ?? 'Could not read.' }}</div>
                                                        @else
                                                            <div>AI read: <b>{{ $ai['number'] ?? '—' }}</b>@if(!empty($ai['name'])) &middot; {{ $ai['name'] }}@endif</div>
                                                            @foreach($ai['issues'] ?? [] as $msg)<div class="kyc-bad">&#9888; {{ $msg }}</div>@endforeach
                                                            @foreach($ai['notes'] ?? [] as $msg)<div class="kyc-good">&#10003; {{ $msg }}</div>@endforeach
                                                            @if(!empty($ai['valid']) && !empty($ai['number']) && $ai['number'] !== $doc->doc_number)
                                                                <form action="{{ route('admin.sales_kyc.number', [$listing->id, $doc->id]) }}" method="POST" class="mt-1">
                                                                    @csrf
                                                                    <input type="hidden" name="doc_number" value="{{ $ai['number'] }}">
                                                                    <button class="btn btn-sm btn-success">Use this number</button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                        <div class="kyc-when">Checked {{ optional($doc->ai_checked_at)->diffForHumans() }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="text-muted" style="font-size:13px;">Not uploaded</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .kyc-ai{ background:#f8f9fa; border-radius:6px; padding:10px 12px; margin:6px 0 10px; font-size:13px; }
    .kyc-ai-num{ display:flex; align-items:center; gap:6px; margin-bottom:8px; }
    .kyc-ai-num label{ margin:0; font-weight:600; min-width:48px; }
    .kyc-ai-num input{ flex:1; min-width:0; height:32px; border:1px solid #dee2e6; border-radius:4px; padding:0 8px; font-family:monospace; text-transform:uppercase; }
    .kyc-ai-result{ margin-top:8px; line-height:1.6; }
    .kyc-bad{ color:#c92a2a; }
    .kyc-good{ color:#2b8a3e; }
    .kyc-when{ color:#868e96; font-size:12px; }
</style>
@endsection
