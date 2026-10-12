@extends('admin.layouts.master')
@section('title','WhatsApp Enquiries')
@section('content')

@include('admin.whatsapp.partials.nav')

<div class="card m-b-30">
    <div class="card-body">
        <form method="get" class="form-row">
            <div class="form-group col-md-4">
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="@lang('Enquiry name, phone or email')">
            </div>
            <div class="form-group col-md-3">
                <select name="type" class="form-control">
                    <option value="">@lang('All enquiries')</option>
                    <option value="listing" @if(request('type') === 'listing') selected @endif>
                        @lang('On a listing')
                    </option>
                    <option value="category" @if(request('type') === 'category') selected @endif>
                        @lang('On a category')
                    </option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <button class="btn btn-primary btn-block">@lang('Filter')</button>
            </div>
        </form>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white mb-0">@lang('Enquiries')</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>@lang('From')</th>
                    <th>@lang('Enquiry for')</th>
                    <th>@lang('Location')</th>
                    <th>@lang('Received')</th>
                    <th>@lang('Sent')</th>
                    <th class="text-right">@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enquiries as $enquiry)
                    <tr>
                        <td>
                            <strong>{{ $enquiry->name }}</strong>
                            <small class="text-muted d-block">{{ $enquiry->phone }}</small>
                        </td>
                        <td>
                            @if($enquiry->listing)
                                <span class="badge badge-info">@lang('Listing')</span>
                                {{ $enquiry->listing->name }}
                            @elseif($enquiry->category)
                                <span class="badge badge-secondary">@lang('Category')</span>
                                {{ $enquiry->category->name }}
                            @else
                                <span class="text-muted">@lang('Not specified')</span>
                            @endif
                        </td>
                        <td>{{ optional($enquiry->location)->name ?? '-' }}</td>
                        <td>
                            <small>{{ $enquiry->created_at->format('d M Y, h:i A') }}</small>
                        </td>
                        <td>
                            @if($sentCounts->get($enquiry->id))
                                <span class="badge badge-success">{{ $sentCounts->get($enquiry->id) }}</span>
                                @php $ls = $lastSends->get($enquiry->id); @endphp
                                @if($ls)
                                    <small class="d-block text-muted mt-1" style="white-space:nowrap;line-height:1.3;">
                                        by {{ $ls->admin_id ? ($senderNames[$ls->admin_id] ?? 'Admin #' . $ls->admin_id) : 'System (auto)' }}<br>
                                        {{ $ls->created_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}
                                    </small>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.whatsapp.enquiry', $enquiry->id) }}"
                               class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp"></i> @lang('Send')
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="100%" class="text-center">@lang('No enquiries found')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $enquiries->links() }}
    </div>
</div>

@endsection
