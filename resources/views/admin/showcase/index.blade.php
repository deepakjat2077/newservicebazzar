@extends('admin.layouts.master')

@section('title', 'Get Listed Showcase')

@section('content')
<div class="row">
    {{-- Search + add --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add Listing to Showcase</h5>
                <small class="text-muted">Ye listings app ke "Get Listed" screen par dikhengi.</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.showcase.index') }}" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search listing by name..."
                               value="{{ $q }}">
                        <button class="btn btn-primary">Search</button>
                    </div>
                </form>

                @if($q !== '' && $results->isEmpty())
                    <p class="text-muted mb-0">No matching active listings for "{{ $q }}".</p>
                @endif

                @foreach($results as $listing)
                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <div class="d-flex align-items-center">
                            @if($listing->thumb)
                                <img src="{{ asset('assets/images/listing/thumb/' . $listing->thumb) }}"
                                     width="40" height="40" style="object-fit:cover;border-radius:6px;" class="me-2">
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $listing->name }}</div>
                                <small class="text-muted">{{ $listing->city ?? '-' }}</small>
                            </div>
                        </div>
                        <form action="{{ route('admin.showcase.store') }}" method="POST" class="mb-0">
                            @csrf
                            <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                            <button class="btn btn-sm btn-success">+ Add</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Current showcase --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Showcase Listings ({{ $showcase->count() }})</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Listing</th>
                            <th>Category</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($showcase as $row)
                            <tr>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.showcase.move', [$row->id, 'up']) }}" class="btn btn-sm btn-light border">&uarr;</a>
                                    <a href="{{ route('admin.showcase.move', [$row->id, 'down']) }}" class="btn btn-sm btn-light border">&darr;</a>
                                </td>
                                <td>{{ optional($row->listing)->name ?? '(deleted listing)' }}</td>
                                <td>{{ optional(optional($row->listing)->category)->name ?? '-' }}</td>
                                <td>{{ optional($row->listing)->city ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.showcase.toggle', $row->id) }}"
                                       class="btn btn-sm {{ $row->status ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $row->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger deleteBtn"
                                            data-id="{{ $row->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $row->id }}"
                                          action="{{ route('admin.showcase.delete', $row->id) }}"
                                          method="POST" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Abhi koi listing showcase me nahi hai. Left se search karke add karo.</td></tr>
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
        title: 'Remove from showcase?',
        text: 'Ye listing showcase se hat jayegi (listing khud delete nahi hogi).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove'
    }).then(function(result){
        if (result.isConfirmed) { form.submit(); }
    });
});
</script>
@endpush
@endsection
