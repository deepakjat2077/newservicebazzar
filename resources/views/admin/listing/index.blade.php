@extends('admin.layouts.master')
@section('content')
@section('title','Listing')
<div class="row">

    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <form method="get" action="">
                        <div class="form-row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">@lang('Search')</label>
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="@lang('Listing name or username')">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">@lang('Category')</label>
                                <select name="category_id" class="form-control">
                                    <option value="">@lang('All')</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ __($category->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">@lang('Location')</label>
                                <select name="location_id" class="form-control">
                                    <option value="">@lang('All')</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ __($location->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 d-flex">
                                <button type="submit" class="btn btn-primary btn-block mr-2">@lang('Filter')</button>
                                <a href="{{ url()->current() }}" class="btn btn-light btn-block mr-2">@lang('Reset')</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('Name')</th>
                                <th scope="col">@lang('Username')</th>
                                <th scope="col">@lang('Category')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="cur-body">
                            @forelse($listings as $listing)
                                <tr>
                                    <td>{{ __($listing->name) }}</td>
                                    {{-- A listing added from the admin panel
                                         need not belong to a registered user —
                                         the user picker on that form is
                                         optional — so this relation is null
                                         for those rows and reading through it
                                         took the whole page down. --}}
                                    <td>{{ optional($listing->user)->username ?? __('Admin') }}</td>
                                    <td>{{ __($listing->category->name ?? 'N/A') }}</td>
                                    <td>
                                        @if($listing->status == 0)
                                            <span class="badge badge-warning">@lang('Pending')</span>
                                        @elseif($listing->status == 1)
                                            <span class="badge badge-success">@lang('Approve')</span>
                                        @else
                                            <span class="badge badge-danger">@lang('Rejected')</span>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('admin.listing.details',$listing->id) }}" class="btn btn-primary btn-sm mr-2">
                                                @lang('Details')
                                            </a>

                                            {{-- WhatsApp sending lives in the WhatsApp panel
                                                 (Admin > WhatsApp) so every send is logged and
                                                 shows up in the delivery/reply stats. --}}

                                            <a href="{{ route('admin.listing.edit',$listing->id) }}" class="btn btn-warning btn-sm mr-2">
                                                @lang('Edit')
                                            </a>

                                            @if(auth()->guard('admin')->user()->role == 'super_admin')
                                                <button type="button" class="btn btn-danger btn-sm deleteBtn" data-id="{{ $listing->id }}">
                                                    @lang('Delete')
                                                </button>

                                                <form id="delete-form-{{ $listing->id }}"
                                                    action="{{ route('admin.listing.delete',$listing->id) }}"
                                                    method="POST" style="display:none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="100%">@lang('Data not found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listings->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@if(session('notify'))
<script>
    document.addEventListener("DOMContentLoaded", function () {
        @foreach(session('notify') as $msg)
            Swal.fire({
                icon: "{{ $msg[0] }}",
                title: "{{ $msg[1] }}",
                timer: 2000,
                showConfirmButton: false
            });
        @endforeach
    });
</script>
@endif


@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>


    $(document).on('click', '.deleteBtn', function () {
    var id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#delete-form-' + id).submit();
        }
    });
});

</script>
@endpush

@endsection