@extends('admin.layouts.master')
@section('content')
@section('title','Listing Details Req')

<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('ID')</th>
                                <th>@lang('Business Name')</th>
                                <th>@lang('Pending For')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($listings as $key => $listing)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <strong>{{ $listing->name ?? '-' }}</strong>
                                    </td>

                                    <td>
                                        @if($listing->name_approval_status == 'pending')
                                            <span class="badge badge--warning">@lang('Name')</span>
                                        @endif

                                        @if($listing->address_approval_status == 'pending')
                                            <span class="badge badge--warning">@lang('Address')</span>
                                        @endif

                                        @if($listing->website_approval_status == 'pending')
                                            <span class="badge badge--warning">@lang('Website')</span>
                                        @endif
                                    </td>

                                    <td>
                                        <button type="button"
                                                class="btn btn--primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#detailsModal{{ $listing->id }}">
                                            <i class="las la-eye"></i> @lang('View Details')
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        @lang('No update requests found')
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($listings as $listing)
    <div class="modal fade" id="detailsModal{{ $listing->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        @lang('Listing Update Details')
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label><strong>@lang('Current Business Name')</strong></label>
                            <div class="border rounded p-2">
                                {{ $listing->name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label><strong>@lang('Requested New Name')</strong></label>
                            <div class="border rounded p-2">
                                {{ $listing->pending_name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label><strong>@lang('Current Address')</strong></label>
                            <div class="border rounded p-2" style="min-height: 70px;">
                                {{ $listing->address ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label><strong>@lang('Requested New Address')</strong></label>
                            <div class="border rounded p-2" style="min-height: 70px;">
                                {{ $listing->pending_address ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label><strong>@lang('Current Website')</strong></label>
                            <div class="border rounded p-2">
                                @if(!empty($listing->website))
                                    <a href="{{ Str::startsWith($listing->website, ['http://','https://']) ? $listing->website : 'https://'.$listing->website }}"
                                       target="_blank">
                                        {{ $listing->website }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label><strong>@lang('Requested New Website')</strong></label>
                            <div class="border rounded p-2">
                                @if(!empty($listing->pending_website))
                                    <a href="{{ Str::startsWith($listing->pending_website, ['http://','https://']) ? $listing->pending_website : 'https://'.$listing->pending_website }}"
                                       target="_blank">
                                        {{ $listing->pending_website }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label><strong>@lang('Status')</strong></label>
                            <div>
                                @if(
                                    $listing->name_approval_status == 'pending' ||
                                    $listing->address_approval_status == 'pending' ||
                                    $listing->website_approval_status == 'pending'
                                )
                                    <span class="badge badge--warning">@lang('Pending')</span>
                                @elseif(
                                    $listing->name_approval_status == 'rejected' ||
                                    $listing->address_approval_status == 'rejected' ||
                                    $listing->website_approval_status == 'rejected'
                                )
                                    <span class="badge badge--danger">@lang('Rejected')</span>
                                @else
                                    <span class="badge badge--success">@lang('Approved')</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <a href="{{ route('admin.listing.approve.name.address', $listing->id) }}"
                       class="btn btn--success btn-sm">
                        <i class="las la-check"></i> @lang('Approve')
                    </a>

                    <a href="{{ route('admin.listing.reject.name.address', $listing->id) }}"
                       class="btn btn--danger btn-sm">
                        <i class="las la-times"></i> @lang('Reject')
                    </a>

                    <button type="button" class="btn btn--dark btn-sm" data-dismiss="modal">
                        @lang('Close')
                    </button>
                </div>

            </div>
        </div>
    </div>
@endforeach

@endsection