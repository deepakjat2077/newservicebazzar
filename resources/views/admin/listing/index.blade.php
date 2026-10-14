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
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('Search')</label>
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="@lang('Listing name or username')">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('Category')</label>
                                <select name="category_id" class="form-control">
                                    <option value="">@lang('All')</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ __($category->name) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('Location')</label>
                                <select name="location_id" class="form-control">
                                    <option value="">@lang('All')</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ __($location->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('Area')</label>
                                {{-- Options are rebuilt in JS from #areaOptionsData so they follow the Location filter. --}}
                                <select name="area" id="areaFilter" class="form-control" data-placeholder="@lang('All areas')">
                                    <option value="">@lang('All')</option>
                                    @if(request('area'))
                                        <option value="{{ request('area') }}" selected>{{ request('area') }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('Added by')</label>
                                <select name="added_by" class="form-control">
                                    <option value="">@lang('All')</option>
                                    <option value="user" @selected(request('added_by') === 'user')>@lang('User (self-listed)')</option>
                                    <option value="admin" @selected(request('added_by') === 'admin')>@lang('Admin')</option>
                                    <option value="sales" @selected(request('added_by') === 'sales')>@lang('SB Sales team')</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 d-flex">
                                <button type="submit" class="btn btn-primary btn-block mr-2">@lang('Filter')</button>
                                <a href="{{ url()->current() }}" class="btn btn-light btn-block mr-2">@lang('Reset')</a>
                            </div>
                        </div>
                    </form>
                </div>

                @adminCan('listing.export')
                <div class="px-3 pt-2 pb-2 border-bottom d-flex flex-wrap align-items-center">
                    <form method="get" action="{{ route('admin.listing.small.images.export') }}" class="form-inline">
                        <span class="text-muted small mr-2">@lang('Listings with small photos'):</span>
                        <select name="min" class="form-control form-control-sm mr-2">
                            <option value="300">@lang('smaller than 300 px')</option>
                            <option value="500" selected>@lang('smaller than 500 px')</option>
                            <option value="700">@lang('smaller than 700 px')</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel"></i> @lang('Download Excel')
                        </button>
                    </form>
                </div>
                @endadminCan
                @if(request()->hasAny(['added_by','q','category_id','location_id','area']) && method_exists($listings, 'total'))
                    <div class="px-3 pb-2 text-muted">@lang('Found'): <strong>{{ number_format($listings->total()) }}</strong> @lang('listings')</div>
                @endif
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
                                    <td>
                                        @if($listing->sales_user_id)
                                            {{ optional($listing->salesUser)->name ?? __('Sales User') }}
                                            <span class="badge badge-info">Sales Genio</span>
                                        @else
                                            {{ optional($listing->user)->username ?? __('Admin') }}
                                        @endif
                                    </td>
                                    <td>{{ __($listing->category->name ?? 'N/A') }}</td>
                                    <td>
                                        @if($listing->status == 0)
                                            <span class="badge badge-warning">@lang('Pending')</span>
                                        @elseif($listing->status == 1)
                                            <span class="badge badge-success">@lang('Approve')</span>
                                        @elseif($listing->status == 3)
                                            <span class="badge badge-secondary">@lang('Inactive')</span>
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


@push('css-link')
<link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
@endpush
@push('js-link')
<script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script id="areaOptionsData" type="application/json">@json($areaOptions ?? [])</script>
<script>
$(function () {
    var all = JSON.parse(document.getElementById('areaOptionsData').textContent || '[]');
    var $area = $('#areaFilter');
    var $loc = $('select[name="location_id"]');
    var current = @json(request('area', ''));

    function build() {
        var locId = String($loc.val() || '');
        var seen = {};
        $area.empty().append(new Option('All', '', false, false));
        all.forEach(function (row) {
            if (locId && String(row.location_id) !== locId) return;
            // Without a city chosen the same area name can exist in two cities, so show the city.
            var hasCity = row.area.toLowerCase().indexOf(String(row.city).toLowerCase()) !== -1;
            var label = (locId || hasCity) ? row.area : row.area + ' (' + row.city + ')';
            var key = locId ? row.area : row.area + '|' + row.location_id;
            if (seen[key]) return;
            seen[key] = true;
            var opt = new Option(label, row.area, false, row.area === current);
            opt.dataset.loc = row.location_id;
            $area.append(opt);
        });
        $area.trigger('change.select2');
    }

    $area.select2({ width: '100%', placeholder: $area.data('placeholder'), allowClear: true });
    build();

    $loc.on('change', function () { current = ''; build(); });

    // Picking an area while "All" locations is selected also sets its city,
    // so "Jhotwara (Jaipur)" doesn't match a Jhotwara somewhere else.
    $area.on('select2:select', function (e) {
        var loc = e.params.data.element && e.params.data.element.dataset.loc;
        if (loc && !$loc.val()) {
            $loc.val(loc);
            current = e.params.data.id;
            build();
        }
    });
});
</script>

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