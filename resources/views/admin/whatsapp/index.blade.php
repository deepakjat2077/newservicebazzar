@extends('admin.layouts.master')
@section('title','WhatsApp Panel')
@section('content')

@include('admin.whatsapp.partials.nav')

<div class="row">
    <div class="col-md-7">
        <div class="card m-b-30">
            <div class="card-header bg-color-primary">
                <h6 class="color-white mb-0">@lang('Recent sends')</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
                            <th>@lang('Number')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('When')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $message)
                            <tr>
                                <td>
                                    {{ $message->contact_name ?? optional($message->listing)->name ?? '-' }}
                                    @if($message->error)
                                        <small class="d-block text-danger">{{ $message->error }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $message->phone }}</code></td>
                                <td>
                                    <span class="badge badge-{{ $message->statusBadge() }}">{{ $message->status }}</span>
                                </td>
                                <td><small>{{ $message->created_at->diffForHumans() }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="100%" class="text-center">@lang('Nothing sent yet')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('admin.whatsapp.listings') }}" class="btn btn-sm btn-success">
                    <i class="fab fa-whatsapp"></i> @lang('Send to listings')
                </a>
                <a href="{{ route('admin.whatsapp.enquiries') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-inbox"></i> @lang('Forward an enquiry')
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card m-b-30">
            <div class="card-header bg-color-primary">
                <h6 class="color-white mb-0">@lang('Latest replies')</h6>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($replies as $reply)
                    <li class="list-group-item {{ $reply->read_at ? '' : 'bg-light' }}">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $reply->contact_name ?: $reply->phone }}</strong>
                            <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="small">{{ $reply->body }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">
                        @lang('No replies yet')
                    </li>
                @endforelse
            </ul>
            <div class="card-footer text-right">
                <a href="{{ route('admin.whatsapp.replies') }}" class="btn btn-sm btn-outline-primary">
                    @lang('All replies')
                </a>
            </div>
        </div>

        <div class="card m-b-30">
            <div class="card-header bg-color-primary">
                <h6 class="color-white mb-0">@lang('Webhook')</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    @lang('Paste this into the Gupshup dashboard so replies and delivery status reach this panel.')
                </p>
                <code class="d-block p-2 bg-light">{{ url('/gupshup/webhook') }}{{ config('services.gupshup.webhook_secret') ? '?token=' . config('services.gupshup.webhook_secret') : '' }}</code>
                @unless(config('services.gupshup.webhook_secret'))
                    <small class="text-muted d-block mt-2">
                        @lang('No webhook secret set — anyone who knows this URL could post to it. Add one in')
                        <a href="{{ route('admin.setting.gupshup') }}">@lang('WhatsApp Setting')</a>.
                    </small>
                @endunless
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
(function ($) {
    "use strict";

    // Poll for new replies so an admin sitting on the panel gets a nudge
    // without refreshing. Cheap query, once a minute.
    var known = {{ $stats['unread'] }};

    setInterval(function () {
        $.get("{{ route('admin.whatsapp.replies.count') }}", function (data) {
            if (data.count > known) {
                var toast = $('<div class="alert alert-info" style="position:fixed;top:70px;right:20px;z-index:9999;max-width:320px"></div>')
                    .text((data.count - known) + ' new WhatsApp reply(s) received.')
                    .appendTo('body');

                setTimeout(function () { toast.fadeOut(400, function () { $(this).remove(); }); }, 8000);
            }

            known = data.count;
        });
    }, 60000);
})(jQuery);
</script>
@endpush

@endsection
