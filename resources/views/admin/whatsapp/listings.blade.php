@extends('admin.layouts.master')
@section('title','WhatsApp Panel')
@section('content')

@include('admin.whatsapp.partials.nav')

<div class="card m-b-30">
    <div class="card-body">
        <form method="get">
            <div class="form-row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="mb-1">@lang('Search')</label>
                    <input type="text" name="q" class="form-control"
                           value="{{ request('q') }}"
                           placeholder="@lang('Listing name, username, mobile or email')">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">@lang('Category')</label>
                    <select name="category_id" class="form-control">
                        <option value="">@lang('All')</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                {{ __($category->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">@lang('Location')</label>
                    <select name="location_id" class="form-control">
                        <option value="">@lang('All')</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>
                                {{ __($location->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1">@lang('Listing status')</label>
                    <select name="status" class="form-control">
                        <option value="">@lang('Any')</option>
                        <option value="approved" @selected(request('status') === 'approved')>@lang('Approved')</option>
                        <option value="pending" @selected(request('status') === 'pending')>@lang('Pending')</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">@lang('WhatsApp status')</label>
                    <select name="sent" class="form-control">
                        <option value="">@lang('All')</option>
                        <option value="sent" @selected(request('sent') === 'sent')>@lang('Already sent')</option>
                        <option value="not_sent" @selected(request('sent') === 'not_sent')>@lang('Not sent yet')</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary btn-block mr-2">@lang('Filter')</button>
                    <a href="{{ url()->current() }}" class="btn btn-light btn-block mt-0">@lang('Reset')</a>
                </div>
            </div>
        </form>
    </div>
</div>

@adminCan('whatsapp.send')
    @include('admin.whatsapp.partials.send-bar')
@endadminCan

<div class="card m-b-30">
    <div class="card-header bg-color-primary d-flex justify-content-between align-items-center">
        <h6 class="color-white mb-0">@lang('Listings')</h6>
        <span class="color-white small">{{ $paginator->total() }} @lang('total')</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="waSelectAll" title="@lang('Select all')">
                    </th>
                    <th>@lang('Listing')</th>
                    <th>@lang('Category')</th>
                    <th>@lang('Location')</th>
                    <th>@lang('WhatsApp')</th>
                    <th>@lang('Last send')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($listings as $listing)
                    <tr>
                        <td>
                            <input type="checkbox" class="wa-target" value="{{ $listing->id }}"
                                   @if(!$listing->wa_phone) disabled title="@lang('No WhatsApp number')" @endif>
                        </td>
                        <td>
                            <strong>{{ $listing->name }}</strong>
                            @if($listing->status != 1)
                                <span class="badge badge-warning">@lang('Pending')</span>
                            @endif

                            @if($listing->wa_last)
                                @if($listing->wa_last->isFailed())
                                    <span class="badge badge-danger">@lang('Failed')</span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> @lang('Sent')
                                        @if($listing->wa_count > 1) ({{ $listing->wa_count }}x) @endif
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>{{ optional($listing->category)->name ?? '-' }}</td>
                        <td>{{ optional($listing->location)->name ?? '-' }}</td>
                        <td>
                            @if($listing->wa_phone)
                                <code>{{ $listing->wa_phone }}</code>
                            @else
                                <span class="text-danger small">@lang('Not available')</span>
                            @endif
                        </td>
                        <td>
                            @if($listing->wa_last)
                                <span class="badge badge-{{ $listing->wa_last->statusBadge() }}">
                                    {{ $listing->wa_last->status }}
                                </span>
                                <small class="text-muted d-block">
                                    {{ $listing->wa_last->created_at->diffForHumans() }}
                                </small>
                            @else
                                <span class="text-muted small">@lang('Never')</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="100%" class="text-center">@lang('No listings found')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $paginator->links() }}
    </div>
</div>

@adminCan('whatsapp.send')
    @include('admin.whatsapp.partials.send-script')
@endadminCan

@endsection
