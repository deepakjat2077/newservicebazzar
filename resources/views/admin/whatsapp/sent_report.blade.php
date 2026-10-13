@extends('admin.layouts.master')
@section('title','WhatsApp Sent Report')
@section('content')
@include('admin.whatsapp.partials.nav')

<div class="card m-b-30">
    <div class="card-body">
        <form method="get" class="form-row">
            @if($phone)
                <input type="hidden" name="phone" value="{{ $phone }}">
            @endif
            <div class="form-group col-md-3">
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="@lang('Business number or name')">
            </div>
            <div class="form-group col-md-2">
                <input type="text" name="listing" class="form-control"
                       value="{{ request('listing') }}" placeholder="@lang('Listing name')">
            </div>
            <div class="form-group col-md-2">
                <select name="category_id" class="form-control wa-select2" data-placeholder="@lang('All categories')">
                    <option value="">@lang('All categories')</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <select name="location_id" class="form-control wa-select2" data-placeholder="@lang('All locations')">
                    <option value="">@lang('All locations')</option>
                    @foreach($locations as $l)
                        <option value="{{ $l->id }}" @selected(request('location_id') == $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-1">
                <button class="btn btn-primary btn-block">@lang('Filter')</button>
            </div>
            <div class="form-group col-md-2">
                <a href="{{ route('admin.whatsapp.sent.report') }}" class="btn btn-light btn-block">@lang('Reset')</a>
            </div>
        </form>
        <div class="small text-muted">
            <strong>{{ number_format($summary->numbers ?? 0) }}</strong> @lang('business numbers') ·
            <strong>{{ number_format($summary->enquiries ?? 0) }}</strong> @lang('enquiries sent')
        </div>
    </div>
</div>

@if($phone)
    {{-- One number: every enquiry it was sent. --}}
    <div class="card m-b-30">
        <div class="card-header bg-color-primary d-flex justify-content-between align-items-center">
            <h6 class="color-white mb-0">
                @lang('Enquiries sent to') {{ $phone }}
                <span class="badge badge-light ml-1">{{ $sent->total() }}</span>
            </h6>
            <a href="{{ route('admin.whatsapp.sent.report', request()->except(['phone', 'page'])) }}" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left"></i> @lang('All numbers')
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>@lang('Enquiry from')</th>
                        <th>@lang('Business / Listing')</th>
                        <th>@lang('Category')</th>
                        <th>@lang('Location')</th>
                        <th>@lang('Sent at')</th>
                        <th>@lang('Status')</th>
                        <th class="text-right">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sent as $row)
                        @php $lst = $listings->get($row->listing_id); @endphp
                        <tr>
                            <td>
                                <strong>{{ $row->enquirer ?? '-' }}</strong>
                                <small class="text-muted d-block">{{ $row->enquirer_phone }}</small>
                            </td>
                            <td>{{ $lst->name ?? $row->contact_name ?? '-' }}</td>
                            <td>{{ optional(optional($lst)->category)->name ?? '-' }}</td>
                            <td>{{ optional(optional($lst)->location)->name ?? '-' }}</td>
                            <td><small>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</small></td>
                            <td><span class="badge badge-secondary">{{ ucfirst($row->status ?? '-') }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.whatsapp.enquiry', $row->enquiry_id) }}" class="btn btn-sm btn-outline-primary">@lang('Open')</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="100%" class="text-center">@lang('Nothing found')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $sent->links() }}</div>
    </div>
@else
    {{-- All numbers, most enquiries first. --}}
    <div class="card m-b-30">
        <div class="card-header bg-color-primary">
            <h6 class="color-white mb-0">@lang('Enquiries sent per business number')</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>@lang('Business number')</th>
                        <th>@lang('Business / Listing')</th>
                        <th>@lang('Category')</th>
                        <th>@lang('Location')</th>
                        <th class="text-center">@lang('Enquiries sent')</th>
                        <th>@lang('Last sent')</th>
                        <th class="text-right">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $lst = $listings->get($row->listing_id); @endphp
                        <tr>
                            <td><strong>{{ $row->phone }}</strong></td>
                            <td>{{ $lst->name ?? $row->contact_name ?? '-' }}</td>
                            <td>{{ optional(optional($lst)->category)->name ?? '-' }}</td>
                            <td>{{ optional(optional($lst)->location)->name ?? '-' }}</td>
                            <td class="text-center"><span class="badge badge-primary" style="font-size:13px;">{{ $row->total }}</span></td>
                            <td><small>{{ \Carbon\Carbon::parse($row->last_sent)->format('d M Y, h:i A') }}</small></td>
                            <td class="text-right">
                                <a href="{{ route('admin.whatsapp.sent.report', array_merge(request()->except('page'), ['phone' => $row->phone])) }}"
                                   class="btn btn-sm btn-outline-primary">@lang('View enquiries')</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="100%" class="text-center">@lang('Nothing found')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $rows->links() }}</div>
    </div>
@endif
@endsection

@push('css-link')
<link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
@endpush
@push('js-link')
<script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endpush
@push('js')
<script>
$(function () {
    $('.wa-select2').each(function () {
        $(this).select2({ width: '100%', placeholder: $(this).data('placeholder'), allowClear: true });
    });
});
</script>
@endpush
