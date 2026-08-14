@extends('admin.layouts.master')
@section('title', $title)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }}</h4>
                <a href="{{ route('admin.agents.create') }}" class="btn btn-primary btn-sm">Add Agent</a>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($agent->image)
                                        <img src="{{ asset('assets/central/images/'.$agent->image) }}" width="45" height="45" style="border-radius:50%;object-fit:cover;">
                                    @else
                                        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" width="45" height="45" style="border-radius:50%;object-fit:cover;">
                                    @endif
                                </td>
                                <td>{{ $agent->first_name }} {{ $agent->last_name }}</td>
                                <td>{{ $agent->username }}</td>
                                <td>{{ $agent->email }}</td>
                                <td>{{ $agent->mobile }}</td>
                                <td>
                                    @if($agent->isSuperAdmin())
                                        <span class="badge bg-dark">
                                            {{ optional($agent->adminRole)->name ?? 'Super Admin' }}
                                        </span>
                                    @elseif($agent->adminRole)
                                        <span class="badge bg-info">{{ $agent->adminRole->name }}</span>
                                        <small class="d-block text-muted" style="font-size:11px">
                                            {{ count($agent->permissionKeys()) }} permissions
                                        </small>
                                    @else
                                        <span class="badge bg-warning">No role</span>
                                    @endif
                                </td>
                                <td>
                                    @if($agent->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Delete -->
    <button 
        class="btn btn-sm btn-danger deleteBtn"
        data-id="{{ $agent->id }}"
        data-url="{{ route('admin.agents.delete', $agent->id) }}">
        <i class="fas fa-trash"></i>
    </button>

    <!-- Hidden Form -->
    <form id="delete-form-{{ $agent->id }}" 
          action="{{ route('admin.agents.delete', $agent->id) }}" 
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No agents found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('js')
    
<script>
    $(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    let id = $(this).data('id');
    let form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: "Delete karne ke baad recover nahi hoga!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush
@endsection