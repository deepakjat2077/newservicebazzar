@extends('admin.layouts.master')
@section('title','Forward Enquiry')
@section('content')

@include('admin.whatsapp.partials.nav')

<div class="card m-b-30">
    <div class="card-header bg-color-primary d-flex justify-content-between align-items-center">
        <h6 class="color-white mb-0">@lang('Enquiry details')</h6>
        <a href="{{ route('admin.whatsapp.enquiries') }}" class="btn btn-sm btn-light">
            @lang('Back to enquiries')
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <small class="text-muted d-block">@lang('From')</small>
                <strong>{{ $enquiry->name }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">@lang('Phone')</small>
                <strong>{{ $enquiry->phone }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">@lang('Email')</small>
                <strong>{{ $enquiry->email ?: '-' }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">@lang('Received')</small>
                <strong>{{ $enquiry->created_at->format('d M Y, h:i A') }}</strong>
            </div>
        </div>
        <hr>
        @if($enquiry->listing)
            <p class="mb-0">
                @lang('Form was submitted on the listing')
                <strong>{{ $enquiry->listing->name }}</strong>
                @if($enquiry->location) — {{ $enquiry->location->name }} @endif.
                @lang('Send it straight to that listing below.')
            </p>
        @elseif($enquiry->category)
            <p class="mb-0">
                @lang('Enquiry came in on the category')
                <strong>{{ $enquiry->category->name }}</strong>
                @if($enquiry->location)
                    @lang('in') <strong>{{ $enquiry->location->name }}</strong>
                @endif.
                @lang('Every matching listing is shown below — pick the ones to forward it to, or use Select all.')
            </p>
        @else
            <p class="mb-0 text-muted">@lang('This enquiry has no listing or category attached, so there is nothing to forward it to.')</p>
        @endif
    </div>
</div>

@if(!empty($preview))
<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white mb-0">@lang('Template variables')</h6>
    </div>
    <div class="card-body">
        <p class="small text-muted">
            @lang('Sample below uses')
            <strong>{{ $targets->first()->name }}</strong>.
            @lang('Variables 1 and 2 change per listing; the rest come from this enquiry.')
        </p>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <tbody>
                    @php
                        $labels = [
                            'Contact person', 'Listing name', 'Customer name',
                            'Customer phone', 'Location', 'Category', 'Enquiry date',
                        ];

                        // Built here rather than inline: Blade's parser chokes on a
                        // literal {{n}} written inside an echo expression.
                        $open = '{' . '{';
                        $close = '}' . '}';
                    @endphp
                    @foreach($preview as $index => $value)
                        <tr>
                            <td style="width:90px"><code>{{ $open . ($index + 1) . $close }}</code></td>
                            <td style="width:160px" class="text-muted">{{ $labels[$index] ?? '' }}</td>
                            <td><strong>{{ $value }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@adminCan('whatsapp.send')
    @if($targets->isNotEmpty())
        @include('admin.whatsapp.partials.send-bar')
    @endif
@endadminCan

<div class="card m-b-30">
    <div class="card-header bg-color-primary d-flex justify-content-between align-items-center">
        <h6 class="color-white mb-0">@lang('Matching listings')</h6>
        <span class="color-white small">{{ $targets->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="waSelectAll" title="@lang('Select all')">
                    </th>
                    <th>@lang('Listing')</th>
                    <th>@lang('Location')</th>
                    <th>@lang('WhatsApp')</th>
                    <th>@lang('Already sent')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($targets as $listing)
                    <tr>
                        <td>
                            <input type="checkbox" class="wa-target" value="{{ $listing->id }}"
                                   @if(!$listing->wa_phone) disabled title="@lang('No WhatsApp number')" @endif>
                        </td>
                        <td>
                            <strong>{{ $listing->name }}</strong>
                            @if($listing->is_featured)
                                <span class="badge badge-primary">@lang('Featured')</span>
                            @endif

                            @if($listing->wa_sent)
                                <span class="badge badge-{{ $listing->wa_sent === 'failed' ? 'danger' : 'success' }}">
                                    @if($listing->wa_sent === 'failed')
                                        @lang('Failed')
                                    @else
                                        <i class="fas fa-check"></i> @lang('Sent')
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td>{{ optional($listing->location)->name ?? '-' }}</td>
                        <td>
                            @if($listing->wa_phone)
                                <code>{{ $listing->wa_phone }}</code>
                            @else
                                <span class="text-danger small">@lang('Not available')</span>
                            @endif
                        </td>
                        <td>
                            @if($listing->wa_sent)
                                <span class="badge badge-{{ $listing->wa_sent === 'failed' ? 'danger' : 'success' }}">
                                    {{ $listing->wa_sent }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="100%" class="text-center">@lang('No matching listings')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($sent->isNotEmpty())
<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white mb-0">@lang('Send history for this enquiry')</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>@lang('Listing')</th>
                    <th>@lang('Number')</th>
                    <th>@lang('Template')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('When')</th>
                    <th>@lang('Note')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sent as $message)
                    <tr>
                        <td>{{ $message->contact_name ?? optional($message->listing)->name ?? '-' }}</td>
                        <td><code>{{ $message->phone }}</code></td>
                        <td><small>{{ $message->template_id }}</small></td>
                        <td>
                            <span class="badge badge-{{ $message->statusBadge() }}">{{ $message->status }}</span>
                        </td>
                        <td><small>{{ $message->created_at->diffForHumans() }}</small></td>
                        <td><small class="text-danger">{{ $message->error }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@adminCan('whatsapp.send')
    @if($targets->isNotEmpty())
        @include('admin.whatsapp.partials.send-script', ['enquiryId' => $enquiry->id])
    @endif
@endadminCan

@endsection
