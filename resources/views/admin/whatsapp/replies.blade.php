@extends('admin.layouts.master')
@section('title','WhatsApp Replies')
@section('content')

@include('admin.whatsapp.partials.nav')

<div class="card m-b-30">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-end">
            <form method="get" class="form-row flex-grow-1">
                <div class="form-group col-md-4 mb-0">
                    <input type="text" name="search" class="form-control"
                           value="{{ request('search') }}" placeholder="@lang('Number, name or message text')">
                </div>
                <div class="form-group col-md-3 mb-0">
                    <select name="filter" class="form-control">
                        <option value="">@lang('All replies')</option>
                        <option value="unread" @if(request('filter') === 'unread') selected @endif>
                            @lang('Unread only')
                        </option>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-0">
                    <button class="btn btn-primary btn-block">@lang('Filter')</button>
                </div>
            </form>

            @if($stats['unread'])
                <form method="post" action="{{ route('admin.whatsapp.replies.readall') }}" class="ml-2">
                    @csrf
                    <button class="btn btn-outline-primary">@lang('Mark all read')</button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white mb-0">@lang('Replies from WhatsApp')</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>@lang('From')</th>
                    <th>@lang('Message')</th>
                    <th>@lang('Reply to')</th>
                    <th>@lang('Received')</th>
                    <th class="text-right">@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($replies as $reply)
                    <tr class="{{ $reply->read_at ? '' : 'font-weight-bold' }}">
                        <td>
                            {{ $reply->contact_name ?: '-' }}
                            <small class="text-muted d-block"><code>{{ $reply->phone }}</code></small>
                        </td>
                        <td>{{ $reply->body }}</td>
                        <td>
                            @if($reply->listing)
                                {{ $reply->listing->name }}
                            @else
                                <span class="text-muted small">@lang('Unknown')</span>
                            @endif
                        </td>
                        <td><small>{{ $reply->created_at->format('d M Y, h:i A') }}</small></td>
                        <td class="text-right">
                            @if($reply->read_at)
                                <span class="badge badge-light">@lang('Read')</span>
                            @else
                                <form method="post" action="{{ route('admin.whatsapp.replies.read', $reply->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">@lang('Mark read')</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center">
                            @lang('No replies yet.')
                            <small class="d-block text-muted">
                                @lang('Replies show up here once Gupshup posts them to your webhook URL.')
                            </small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $replies->links() }}
    </div>
</div>

@endsection
