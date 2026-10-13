@extends('admin.layouts.master')
@section('title', 'Sales Dispositions')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Sales Genio — Dispositions</h5>
                        <a href="{{ route('admin.sales_team.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-map-marked-alt"></i> Sales Team Report
                        </a>
                    </div>
                    <form method="get" class="form-row align-items-end">
                        <div class="col-md-2 mb-2">
                            <label class="mb-1 small">Disposition</label>
                            <select name="disposition" class="form-control form-control-sm">
                                <option value="">All dispositions</option>
                                @foreach($options as $opt)
                                    <option value="{{ $opt }}" @selected(request('disposition')==$opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1 small">Sales user</label>
                            <select name="sales_user_id" class="form-control form-control-sm">
                                <option value="">All sales users</option>
                                @foreach($salesUsers as $su)
                                    <option value="{{ $su->id }}" @selected(request('sales_user_id')==$su->id)>{{ $su->name }} ({{ $su->empcode }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1 small">From</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1 small">To</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1 small">Business city</label>
                            <input type="text" name="city" value="{{ request('city') }}" class="form-control form-control-sm" placeholder="e.g. Jaipur">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small d-block mb-1">
                                <input type="checkbox" name="call_back" value="1" @checked(request('call_back'))> Call Back only
                            </label>
                            <div class="d-flex">
                                <button class="btn btn-sm btn-primary mr-2">Filter</button>
                                <a href="{{ url()->current() }}" class="btn btn-sm btn-light">Reset</a>
                            </div>
                        </div>
                    </form>
                    @if($summary->isNotEmpty())
                        <div class="mt-1">
                            <span class="badge badge-dark">Total: {{ $summary->sum() }}</span>
                            @foreach($summary as $label => $n)
                                <span class="badge badge-light border">{{ $label }}: {{ $n }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Business</th>
                                <th>Mobile</th>
                                <th>Disposition</th>
                                <th>Call Back</th>
                                <th>Sales User</th>
                                <th>Rep's location</th>
                                <th>Date &amp; Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                            <tr>
                                <td>{{ $rows->firstItem() + $loop->index }}</td>
                                <td><strong>{{ optional($r->listing)->name ?? '—' }}</strong><br><small class="text-muted">{{ optional($r->listing)->city }}</small></td>
                                <td>{{ optional($r->listing)->mobile ?? '—' }}</td>
                                <td><span class="badge badge-info">{{ $r->disposition }}</span></td>
                                <td>@if($r->call_back)<span class="badge badge-warning">Yes</span>@else — @endif</td>
                                <td>
                                    @if($r->salesUser)
                                        <a href="{{ route('admin.sales_team.show', ['id' => $r->sales_user_id, 'date' => $r->created_at->toDateString()]) }}">{{ $r->salesUser->name }}</a>
                                        <br><small class="text-muted">{{ $r->salesUser->empcode }}</small>
                                    @else — @endif
                                </td>
                                <td style="max-width:240px;">
                                    @if($r->location && $r->location->hasPoint())
                                        <small>{{ $r->location->label() }}</small><br>
                                        <a href="{{ $r->location->mapUrl() }}" target="_blank" rel="noopener" class="small">
                                            <i class="fas fa-map-marker-alt"></i> View on map
                                        </a>
                                    @else
                                        <small class="text-muted">Not captured</small>
                                    @endif
                                </td>
                                <td>{{ $r->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <form action="{{ route('admin.sales_dispositions.destroy', $r->id) }}" method="POST" class="dispo-del-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger dispo-del-btn"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center">No dispositions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $rows->links() }}</div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.dispo-del-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
        var form = this.closest('.dispo-del-form');
        Swal.fire({
            title: 'Delete this disposition?',
            text: 'This record will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
        }).then(function(result){
            if(result.isConfirmed){ form.submit(); }
        });
    });
});
</script>
@endpush

@endsection
