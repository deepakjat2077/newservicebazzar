@extends('admin.layouts.master')

@section('title', 'Listing Clicks')

@section('content')
<div class="card">

    <div class="card-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-2 mb-md-0">Listing Clicks</h5>

            {{--
                This admin theme ships Bootstrap 4.5 (see
                public/assets/admin/css/bootstrap.min.css), which has no
                `.gap-*` utility — that's a Bootstrap 5 class and silently
                does nothing here, which is why these buttons were touching
                with no space between them. `mr-2` (Bootstrap 4's margin
                utility) on each one instead.
            --}}
            <div class="d-flex flex-wrap">

                @php
                    // Every filter type-switch link (All/Call/WhatsApp/Enquiry)
                    // must carry through whichever date filter is active —
                    // previously this only preserved 'month', so switching type
                    // silently dropped a Date Range filter.
                    $activeDateParams = request()->only([
                        'month', 'date_from', 'date_to',
                    ]);
                @endphp

                <a href="{{ route('admin.enquiry.clicks', array_filter($activeDateParams)) }}"
                   class="btn btn-dark btn-sm mr-2 mb-2 {{ empty(request('type')) ? 'active' : '' }}">
                    All ({{ $totalClickCount ?? ($callCount + $whatsappCount + $enquiryCount) }})
                </a>

                <a href="{{ route('admin.enquiry.clicks', array_filter(array_merge($activeDateParams, ['type' => 'call']))) }}"
                   class="btn btn-success btn-sm mr-2 mb-2 {{ request('type') == 'call' ? 'active' : '' }}">
                    Call ({{ $callCount }})
                </a>

                <a href="{{ route('admin.enquiry.clicks', array_filter(array_merge($activeDateParams, ['type' => 'whatsapp']))) }}"
                   class="btn btn-primary btn-sm mr-2 mb-2 {{ request('type') == 'whatsapp' ? 'active' : '' }}">
                    WhatsApp ({{ $whatsappCount }})
                </a>

                <a href="{{ route('admin.enquiry.clicks', array_filter(array_merge($activeDateParams, ['type' => 'enquiry']))) }}"
                   class="btn btn-warning btn-sm mr-2 mb-2 {{ request('type') == 'enquiry' ? 'active' : '' }}">
                    Enquiry ({{ $enquiryCount }})
                </a>

                {{-- Moved up here, alongside All/Call/WhatsApp/Enquiry, out
                     of the filter form below — carries the same type + date
                     filter params as those buttons so the export always
                     matches whatever the admin is currently looking at. --}}
                <a href="{{ route('admin.enquiry.clicks.export', array_filter(array_merge($activeDateParams, ['type' => request('type')]))) }}"
                   class="btn btn-success btn-sm mr-2 mb-2">
                    <i class="fas fa-file-excel"></i>
                    Export Excel
                </a>

                {{--
                    Apply Filter / Reset moved up here too, same row as
                    everything else. "Apply Filter" is a submit button living
                    outside the <form> below (id="clicksFilterForm") — its
                    form="" attribute is what still submits that form; this
                    is standard HTML, not a hack, and works the same as if
                    the button were physically inside the <form> tags.
                --}}
                <button type="submit" form="clicksFilterForm" class="btn btn-primary btn-sm mr-2 mb-2">
                    <i class="fas fa-filter"></i>
                    Apply Filter
                </button>

                <a href="{{ route('admin.enquiry.clicks') }}"
                   class="btn btn-secondary btn-sm mb-2">
                    <i class="fas fa-sync-alt"></i>
                    Reset
                </a>

            </div>
        </div>

        <hr>

        {{--
            Two ways to filter by date — Select Month (original) and Date
            Range. Only one is meant to be filled at a time; if both are, the
            controller (resolveClickDateRange) picks Date Range first, then
            Month.

            (Year Range was here too but is removed for now — re-add by
            restoring the year_from/year_to branch in
            EnquiryController::resolveClickDateRange plus its two inputs
            here, see git history on this file.)
        --}}
        <form id="clicksFilterForm"
              action="{{ route('admin.enquiry.clicks') }}"
              method="GET"
              class="row align-items-end">

            @if(request('type'))
                <input type="hidden"
                       name="type"
                       value="{{ request('type') }}">
            @endif

            <div class="col-sm-6 col-md-4 col-lg-3 mb-2">
                <label for="month" class="form-label">
                    Select Month
                </label>

                {{--
                    Defaults to the current month so the box never sits
                    blank/dashed-out — this is display only: with no 'month'
                    in the URL the page still shows all-time totals, exactly
                    as before. Only submitting the form actually filters.
                --}}
                <input type="month"
                       name="month"
                       id="month"
                       class="form-control"
                       value="{{ request('month', now('Asia/Kolkata')->format('Y-m')) }}">
            </div>

            <div class="col-sm-12 col-md-8 col-lg-6 mb-2">
                <label class="form-label">Date Range</label>
                {{-- 'row' alone (no 'g-2', a Bootstrap 5-only gutter class)
                     already spaces these two via Bootstrap 4's normal
                     row/col padding. --}}
                <div class="row">
                    <div class="col-6">
                        <input type="date"
                               name="date_from"
                               class="form-control"
                               aria-label="From date"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6">
                        <input type="date"
                               name="date_to"
                               class="form-control"
                               aria-label="To date"
                               value="{{ request('date_to') }}">
                    </div>
                </div>
            </div>

        </form>

    </div>

    <div class="card-body">

        {{-- Totals Cards --}}
        <div class="row mb-3">

            <div class="col-md-6 col-lg-3 mb-2">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-1">Total Clicks</h6>

                    <h4 class="mb-0">
                        {{ $totalClickCount ?? ($callCount + $whatsappCount + $enquiryCount) }}
                    </h4>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-2">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-1">Total Calls</h6>

                    <h4 class="mb-0 text-success">
                        {{ $callCount }}
                    </h4>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-2">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-1">Total WhatsApp</h6>

                    <h4 class="mb-0 text-primary">
                        {{ $whatsappCount }}
                    </h4>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-2">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-1">Total Enquiries</h6>

                    <h4 class="mb-0 text-warning">
                        {{ $enquiryCount }}
                    </h4>
                </div>
            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Listing</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Owner</th>
                        <th>Owner Number</th>
                        <th>Clicked Number</th>
                        <th>Type</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clicks as $key => $click)
                        <tr>

                            <td>
                                {{ $clicks->firstItem() + $key }}
                            </td>

                            <td>
                                {{ $click->listing_name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $click->location_name ?? '-' }}
                            </td>

                            <td>
                                {{ $click->category_name ?? '-' }}
                            </td>

                            <td>
                                {{ $click->owner_name ?? '-' }}
                            </td>

                            <td>
                                {{ $click->owner_number ?? '-' }}
                            </td>

                            <td>
                                {{ $click->clicked_number ?? '-' }}
                            </td>

                            <td>
                                @if($click->type == 'call')
                                    <span class="badge badge-success">
                                        Call
                                    </span>

                                @elseif($click->type == 'whatsapp')
                                    <span class="badge badge-primary">
                                        WhatsApp
                                    </span>

                                @elseif($click->type == 'enquiry')
                                    <span class="badge badge-warning">
                                        Enquiry
                                    </span>

                                @else
                                    <span class="badge badge-secondary">
                                        {{ ucfirst($click->type ?? 'Unknown') }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $click->ip_address ?? '-' }}
                            </td>

                            <td>
                                {{ $click->created_at
                                    ? $click->created_at
                                        ->timezone('Asia/Kolkata')
                                        ->format('d M Y, h:i A')
                                    : 'N/A'
                                }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                No Data Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if($clicks->hasPages())
            <div class="mt-3">
                {{ $clicks
                    ->appends(request()->query())
                    ->links()
                }}
            </div>
        @endif

    </div>
</div>
@endsection