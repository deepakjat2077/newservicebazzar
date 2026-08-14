@extends('admin.layouts.master')

@section('title', 'Enquiry List')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-2 mb-md-0">
                        Enquiry List
                    </h5>

                    <div class="d-flex flex-wrap gap-2">

                        {{-- All --}}
                        <a href="{{ route('admin.enquiry.index', array_filter([
                                'month' => request('month')
                            ])) }}"
                           class="btn btn-sm btn-outline-dark
                           {{ empty(request('status')) ? 'active' : '' }}">
                            All
                        </a>

                        {{-- Read --}}
                        <a href="{{ route('admin.enquiry.index', array_filter([
                                'status' => 'read',
                                'month' => request('month')
                            ])) }}"
                           class="btn btn-sm btn-outline-success
                           {{ request('status') == 'read' ? 'active' : '' }}">
                            Read ({{ $readCount }})
                        </a>

                        {{-- Unread --}}
                        <a href="{{ route('admin.enquiry.index', array_filter([
                                'status' => 'unread',
                                'month' => request('month')
                            ])) }}"
                           class="btn btn-sm btn-outline-danger
                           {{ request('status') == 'unread' ? 'active' : '' }}">
                            Unread ({{ $unreadCount }})
                        </a>

                    </div>
                </div>

                <hr>

                {{-- Month Filter and Excel Export --}}
                <form action="{{ route('admin.enquiry.index') }}"
                      method="GET"
                      class="row align-items-end">

                    {{-- Current read/unread filter preserve hoga --}}
                    @if(request('status'))
                        <input type="hidden"
                               name="status"
                               value="{{ request('status') }}">
                    @endif

                    <div class="col-md-4 col-lg-3 mb-2">
                        <label for="month" class="form-label">
                            Select Month
                        </label>

                        <input type="month"
                               name="month"
                               id="month"
                               class="form-control"
                               value="{{ request('month') }}">
                    </div>

                    <div class="col-md-8 col-lg-9 mb-2">

                        <button type="submit"
                                class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            Apply Filter
                        </button>

                        <a href="{{ route('admin.enquiry.index') }}"
                           class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i>
                            Reset
                        </a>

                        {{--
                            Selected month ka complete enquiry report export hoga.

                            Status export mein pass nahi kiya hai, isliye selected
                            month ke read aur unread dono records Excel mein aayenge.

                            Excel mein read/unread totals sheet nahi banegi.
                        --}}
                        <a href="{{ route('admin.enquiry.export', array_filter([
        'month' => request('month')
    ])) }}"
   class="btn btn-success">

    <i class="fas fa-file-excel"></i>
    Export Excel
</a>

                    </div>
                </form>

            </div>

            <div class="card-body">

                {{-- Read / Unread Cards --}}
                <div class="row mb-3">

                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-1">
                                Total Read
                            </h6>

                            <h4 class="mb-0 text-success">
                                {{ $readCount }}
                            </h4>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-1">
                                Total Unread
                            </h6>

                            <h4 class="mb-0 text-danger">
                                {{ $unreadCount }}
                            </h4>
                        </div>
                    </div>

                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Listing</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($enquiries as $key => $enquiry)
                                <tr>

                                    <td>
                                        {{ $enquiries->firstItem() + $key }}
                                    </td>

                                    <td>
                                        {{ $enquiry->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $enquiry->phone ?? '-' }}
                                    </td>

                                    <td>
                                        {{ optional($enquiry->listing)->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ optional($enquiry->location)->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ optional($enquiry->category)->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        @if($enquiry->is_read == 1)
                                            <span class="badge badge-success">
                                                Read
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                Unread
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $enquiry->created_at
                                            ? $enquiry->created_at
                                                ->timezone('Asia/Kolkata')
                                                ->format('d M Y, h:i A')
                                            : 'N/A'
                                        }}
                                    </td>

                                    <td>

                                        {{-- Mark as read.

                                             One direction only. There used to
                                             be a second button here that put
                                             an enquiry back to unread, sitting
                                             in the same spot and looking the
                                             same — so a second click on what
                                             had just been marked read quietly
                                             undid it, and the row looked
                                             untouched. Read means dealt with;
                                             there is no reason to undo it. --}}
                                        @if($enquiry->is_read == 0)

                                            <a href="{{ route(
                                                    'admin.enquiry.read',
                                                    $enquiry->id
                                                ) }}"
                                               class="btn btn-sm btn-success"
                                               title="Mark as read">
                                                <i class="fas fa-check-circle"></i>
                                            </a>

                                        @endif

                                        {{-- Delete only for super admin --}}
                                        @if(
                                            auth()->guard('admin')->check() &&
                                            auth()->guard('admin')->user()->role == 'super_admin'
                                        )

                                            <button type="button"
                                                    class="btn btn-sm btn-danger deleteBtn"
                                                    data-id="{{ $enquiry->id }}"
                                                    title="Delete enquiry">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                            <form id="delete-form-{{ $enquiry->id }}"
                                                  action="{{ route(
                                                      'admin.enquiry.delete',
                                                      $enquiry->id
                                                  ) }}"
                                                  method="POST"
                                                  style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                        @endif

                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    {{-- Table mein 9 columns hain --}}
                                    <td colspan="9"
                                        class="text-center">
                                        No enquiries found
                                    </td>
                                </tr>

                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Pagination --}}
                @if($enquiries->hasPages())
                    <div class="mt-3">
                        {{ $enquiries
                            ->appends(request()->query())
                            ->links()
                        }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).on('click', '.deleteBtn', function (event) {
    event.preventDefault();

    const id = $(this).data('id');
    const form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: 'Ye enquiry permanently delete ho jayegi!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endpush