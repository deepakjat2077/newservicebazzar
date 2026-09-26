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

@endsection
