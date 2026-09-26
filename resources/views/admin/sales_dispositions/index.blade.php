@extends('admin.layouts.master')
@section('title', 'Sales Dispositions')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Genio — Dispositions</h5>
                    <form method="get" class="form-inline">
                        <select name="disposition" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">All dispositions</option>
                            @foreach($options as $opt)
                                <option value="{{ $opt }}" @selected(request('disposition')==$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <label class="mr-2" style="font-size:13px;">
                            <input type="checkbox" name="call_back" value="1" onchange="this.form.submit()" @checked(request('call_back'))> Call Back only
                        </label>
                    </form>
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
                                <td>{{ optional($r->salesUser)->name ?? '—' }}<br><small class="text-muted">{{ optional($r->salesUser)->empcode }}</small></td>
                                <td>{{ $r->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <form action="{{ route('admin.sales_dispositions.destroy', $r->id) }}" method="POST" class="dispo-del-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger dispo-del-btn"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center">No dispositions recorded yet.</td></tr>
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
