@extends('admin.layouts.master')
@section('title','Free Listing')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">

                <div class="p-3 border-bottom">
                    <form method="get" action="">
                        <div class="form-row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">@lang('Search')</label>
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="@lang('Business name / Mobile / Email')">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('Pincode')</label>
                                <input type="text" name="pincode" value="{{ request('pincode') }}" class="form-control" placeholder="@lang('Pincode')">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('City')</label>
                                <input type="text" name="city" value="{{ request('city') }}" class="form-control" placeholder="@lang('City')">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="mb-1">@lang('State')</label>
                                <input type="text" name="state" value="{{ request('state') }}" class="form-control" placeholder="@lang('State')">
                            </div>

                            <div class="col-md-3 mb-2 d-flex">
                                <button type="submit" class="btn btn-primary btn-block mr-2">@lang('Filter')</button>
                                <a href="{{ url()->current() }}" class="btn btn-light btn-block">@lang('Reset')</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col">@lang('ID')</th>
                                <th scope="col">@lang('User ID')</th>
                                <th scope="col">@lang('Mobile')</th>
                                <th scope="col">@lang('Email')</th>
                                <th scope="col">@lang('Business Name')</th>
                                <th scope="col">@lang('Pincode')</th>
                                <th scope="col">@lang('Plot No')</th>
                                <th scope="col">@lang('Building Name')</th>
                                <th scope="col">@lang('Street Name')</th>
                                <th scope="col">@lang('Landmark')</th>
                                <th scope="col">@lang('Area')</th>
                                <th scope="col">@lang('City')</th>
                                <th scope="col">@lang('State')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Created At')</th>
                                <th scope="col">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="cur-body">
                            @forelse($listings as $listing)
                                <tr>
                                    <td>{{ $listing->id }}</td>
                                    <td>{{ $listing->user_id }}</td>
                                    <td>{{ $listing->mobile ?? '' }}</td>
                                    <td>{{ $listing->email ?? '' }}</td>
                                    <td>{{ $listing->business_name ?? '' }}</td>
                                    <td>{{ $listing->pincode ?? '' }}</td>
                                    <td>{{ $listing->plot_no ?? '' }}</td>
                                    <td>{{ $listing->building_name ?? '' }}</td>
                                    <td>{{ $listing->street_name ?? '' }}</td>
                                    <td>{{ $listing->landmark ?? '' }}</td>
                                    <td>{{ $listing->area ?? '' }}</td>
                                    <td>{{ $listing->city ?? '' }}</td>
                                    <td>{{ $listing->state ?? '' }}</td>
                                    <td>
                                        @if($listing->status == 0)
                                            <span class="badge badge-warning">@lang('Pending')</span>
                                        @elseif($listing->status == 1)
                                            <span class="badge badge-success">@lang('Approved')</span>
                                        @else
                                            <span class="badge badge-danger">@lang('Rejected')</span>
                                        @endif
                                    </td>
                                    <td>{{ $listing->created_at ? $listing->created_at->format('d M Y h:i A') : '' }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('admin.listing.details', $listing->id) }}" class="btn btn-primary btn-sm mr-2">
                                                @lang('Details')
                                            </a>
                                            <a href="{{ route('admin.listing.edit', $listing->id) }}" class="btn btn-success btn-sm">
                                                @lang('Edit')
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="16">@lang('Data not found')</td>
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

@endsection