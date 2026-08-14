@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Services List</h4>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Add Service
            </a>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th width="90">Image</th>
                            <th>Title</th>
                         
                            <th>Menu Title</th>
                            
                            <th width="100">Status</th>
                           
                            <th width="170" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $key => $service)
                            <tr>
                                <td>{{ $services->firstItem() + $key }}</td>

                                <td>
                                    @if(!empty($service->hero_image))
                                        <img src="{{ asset($service->hero_image) }}"
                                             alt="{{ $service->title }}"
                                             style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                    @else
                                        <div style="width:60px;height:60px;background:#f1f1f1;border:1px solid #ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:#777;">
                                            No Img
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $service->title }}</strong>
                                </td>

                             

                                <td>
                                    {{ $service->menu_title ?: '-' }}
                                </td>

                             

                                <td>
                                    @if($service->status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>

                                

                                <td class="text-center">
                                   

                                    <a href="{{ route('admin.services.edit', $service->id) }}"
                                       class="btn btn-primary btn-sm"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.admin.services.delete', $service->id) }}"
                                          method="POST"
                                          class="d-inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No services found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($services->hasPages())
                <div class="mt-3">
                    {{ $services->links() }}
                </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This service will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this service?')) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush