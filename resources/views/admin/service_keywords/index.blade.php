@extends('admin.layouts.master')

@section('title', 'Service Keywords')

@section('content')

<div class="container-fluid">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Service Keywords List</h4>

            <a href="{{ route('admin.service-keywords.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Add Popular Services
            </a>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th width="80">Image</th>
                            <th>Keyword</th>
                            <th>Category</th>
                            <th>Slug</th>
                            <th width="100">Status</th>
                            <th width="170" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($keywords as $key => $keyword)
                        <tr>
                            <td>{{ $keywords->firstItem() + $key }}</td>

                            <td>
                                @if($keyword->image)
                                    <img src="{{ asset('assets/images/service-keywords/'.$keyword->image) }}"
                                         style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                @else
                                    <div style="width:60px;height:60px;background:#f1f1f1;border:1px solid #ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                        No Img
                                    </div>
                                @endif
                            </td>

                            <td><strong>{{ $keyword->keyword }}</strong></td>

                            <td>{{ optional($keyword->category)->name }}</td>

                            <td>{{ $keyword->slug }}</td>

                            <td>
                                @if($keyword->status == 1)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.service-keywords.edit', $keyword->id) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="{{ route('admin.service-keywords.status', $keyword->id) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-sync"></i>
                                </a>

                                <a href="{{ route('admin.service-keywords.delete', $keyword->id) }}"
                                   class="btn btn-danger btn-sm delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                No Service Keywords Found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($keywords->hasPages())
                <div class="mt-3">
                    {{ $keywords->links() }}
                </div>
            @endif

        </div>
    </div>

</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-btn').forEach(function(btn){

        btn.addEventListener('click', function(e){

            e.preventDefault();

            let deleteUrl = this.getAttribute('href');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This keyword will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = deleteUrl;
                }
            });

        });

    });

});
</script>
@endpush