@extends('admin.layouts.master')

@section('title', 'Manage Banners')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5>Add Banner</h5>
            </div>
        
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" placeholder="Jaipur">
                    </div>

                    <div class="mb-3">
                        <label>Desktop Banner</label>
                        <input type="file" name="desktop_banner" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Mobile Banner</label>
                        <input type="file" name="mobile_banner" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Link</label>
                        <input type="text" name="link" class="form-control" placeholder="https://example.com">
                    </div>

                    <div class="mb-3">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>

                    <button class="btn btn-primary w-100">Save Banner</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5>All Banners</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>City</th>
                            <th>Desktop</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($banners as $key => $banner)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $banner->title }}</td>
                                <td>{{ $banner->category->name ?? '-' }}</td>
                                <td>{{ $banner->city ?? '-' }}</td>
                                <td>
                                    @if($banner->desktop_banner)
                                        <img src="{{ asset('assets/images/category/banner/' . $banner->desktop_banner) }}" width="80">
                                    @endif
                                </td>
                                <td>
                                    @if($banner->mobile_banner)
                                        <img src="{{ asset('assets/images/category/banner/' . $banner->mobile_banner) }}" width="80">
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.banners.toggle', $banner->id) }}"
                                       class="btn btn-sm {{ $banner->status ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $banner->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-info">Edit</a>
                                

                                       
 <!-- Delete -->
    <button 
        class="btn btn-sm btn-danger deleteBtn"
        data-id="{{ $banner->id }}"
        data-url="{{ route('admin.banners.delete', $banner->id) }}">
        <i class="fas fa-trash"></i>
    </button>

    <!-- Hidden Form -->
    <form id="delete-form-{{ $banner->id }}" 
          action="{{ route('admin.banners.delete', $banner->id) }}" 
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    var id = $(this).data('id');
    var form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: 'Ye listing permanently delete ho jayegi!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(function(result){
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endpush
@endsection