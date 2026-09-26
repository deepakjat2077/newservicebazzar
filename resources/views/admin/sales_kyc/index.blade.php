@extends('admin.layouts.master')
@section('title', 'Sales KYC')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Genio — KYC Documents</h5>
                    <form method="get" class="d-flex">
                        <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">All statuses</option>
                            @foreach(['in_review'=>'In Review','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v)
                                <option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Business</th>
                                <th>Sales User</th>
                                <th>City</th>
                                <th>Documents</th>
                                <th>Listing Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listings as $l)
                            <tr>
                                <td>{{ $listings->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $l->name }}</strong></td>
                                <td>{{ optional($l->salesUser)->name ?? '—' }}</td>
                                <td>{{ $l->city ?? '—' }}</td>
                                <td><span class="badge badge-info">{{ $l->kyc_documents_count }} docs</span></td>
                                <td>
                                    @if($l->status == 1)<span class="badge badge-success">Active</span>
                                    @elseif($l->status == 3)<span class="badge badge-secondary">Inactive</span>
                                    @elseif($l->status == 0)<span class="badge badge-warning">Pending</span>
                                    @else<span class="badge badge-danger">Rejected</span>@endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.sales_kyc.show', $l->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center">No KYC submissions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $listings->links() }}</div>
        </div>
    </div>
</div>

@endsection
