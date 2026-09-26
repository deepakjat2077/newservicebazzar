@extends('admin.layouts.master')

@section('title', 'Success Stories')

@section('content')
<div class="row">
    {{-- Add --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add Success Story</h5>
                <small class="text-muted">Client testimonials — Get Listed screen par dikhengi.</small>
            </div>
            <form action="{{ route('admin.success-stories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label>Client Name</label>
                        <input type="text" name="client_name" class="form-control" required value="{{ old('client_name') }}">
                    </div>
                    <div class="mb-3">
                        <label>Business Name</label>
                        <input type="text" name="business_name" class="form-control" value="{{ old('business_name') }}">
                    </div>
                    <div class="mb-3">
                        <label>Rating (0-5)</label>
                        <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="{{ old('rating', '5') }}">
                    </div>
                    <div class="mb-3">
                        <label>Story</label>
                        <textarea name="story" rows="4" class="form-control" required>{{ old('story') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Client / Business Photo</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <button class="btn btn-primary w-100">Save Story</button>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Success Stories</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Client</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stories as $key => $story)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if($story->image)
                                        <img src="{{ asset('assets/images/success-stories/' . $story->image) }}"
                                             width="48" height="48" style="object-fit:cover;border-radius:50%;">
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $story->client_name }}</div>
                                    <small class="text-muted">{{ $story->business_name ?? '-' }}</small>
                                </td>
                                <td>{{ $story->rating !== null ? $story->rating . ' ★' : '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.success-stories.toggle', $story->id) }}"
                                       class="btn btn-sm {{ $story->status ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $story->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.success-stories.edit', $story->id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $story->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $story->id }}"
                                          action="{{ route('admin.success-stories.delete', $story->id) }}"
                                          method="POST" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Abhi koi success story nahi hai.</td></tr>
                        @endforelse
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
        text: 'Ye success story permanently delete ho jayegi!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(function(result){
        if (result.isConfirmed) { form.submit(); }
    });
});
</script>
@endpush
@endsection
