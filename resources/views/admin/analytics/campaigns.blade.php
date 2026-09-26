@extends('admin.layouts.master')
@section('content')
@section('title','Campaign Links')

<style>
    .cl-help{ font-size:13px; color:#6b7280; margin:0 0 14px; }
    .cl-form label{ font-size:12.5px; font-weight:600; color:#495057; margin-bottom:4px; }
    .cl-form .form-control{ height:40px; }
    .cl-err{ color:#e03131; font-size:12.5px; margin-top:4px; }
    .cl-new{ background:#ebfbee; border:1px solid #b2f2bb; border-radius:8px; padding:12px 14px; margin-bottom:16px; }
    .cl-copyrow{ display:flex; gap:8px; }
    .cl-copyrow input{ flex:1; font-family:monospace; font-size:12.5px; background:#fff; }
    .cl-url{ font-family:monospace; font-size:12px; color:#495057; word-break:break-all; max-width:360px; }
    .cl-num{ text-align:right; white-space:nowrap; }
    .cl-tag{ display:inline-block; padding:2px 9px; border-radius:20px; background:#e7f1fb; color:#1c7ed6; font-size:12px; font-weight:600; white-space:nowrap; }
    .cl-copy{ border:1px solid #dfe3e8; background:#fff; border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer; }
    .cl-copy:hover{ border-color:#1c7ed6; color:#1c7ed6; }
    .cl-del{ border:0; background:none; color:#adb5bd; cursor:pointer; }
    .cl-del:hover{ color:#e03131; }
</style>

<div class="card m-b-30">
    <div class="card-header"><h6>@lang('Create a campaign link')</h6></div>
    <div class="card-body">
        <p class="cl-help">
            @lang('Use this link in the ad, post, WhatsApp message or QR code instead of the plain site address. Every visit that comes through it is counted against this campaign below, and in Traffic Sources → By Campaign.')
        </p>

        @if(session('campaign_created') && ($new = $links->firstWhere('id', session('campaign_created'))))
            <div class="cl-new">
                <b>@lang('Link ready.') </b>@lang('Copy it and use it in') {{ $new->channelLabel() }}:
                <div class="cl-copyrow m-t-10">
                    <input type="text" class="form-control" value="{{ $new->url() }}" readonly id="cl-new-url">
                    <button type="button" class="btn btn-primary cl-copy-btn" data-url="{{ $new->url() }}">@lang('Copy')</button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.analytics.campaigns.store') }}" class="cl-form">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>@lang('Campaign name')</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Diwali offer" required maxlength="120">
                    @error('name')<div class="cl-err">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 form-group">
                    <label>@lang('Where will you post it?')</label>
                    <select name="channel" class="form-control" required>
                        @foreach($channels as $key => [$label])
                            <option value="{{ $key }}" @selected(old('channel') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('channel')<div class="cl-err">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 form-group">
                    <label>@lang('Which page should it open?')</label>
                    <input type="url" name="landing_url" class="form-control" value="{{ old('landing_url', url('/')) }}" required maxlength="1000">
                    @error('landing_url')<div class="cl-err">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">@lang('Create link')</button>
        </form>
    </div>
</div>

<div class="card m-b-30">
    <div class="card-header"><h6>@lang('Your campaign links')</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>@lang('Campaign')</th>
                        <th>@lang('Channel')</th>
                        <th>@lang('Link')</th>
                        <th class="cl-num">@lang('Visits')</th>
                        <th class="cl-num">@lang('Visitors')</th>
                        <th class="cl-num">@lang('Calls')</th>
                        <th class="cl-num">@lang('WhatsApp')</th>
                        <th class="cl-num">@lang('Enquiries')</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $l)
                        @php $st = $stats[$l->id] ?? []; @endphp
                        <tr>
                            <td>
                                <b>{{ $l->name }}</b>
                                <div class="text-muted" style="font-size:12px">{{ $l->created_at->format('d M Y') }}</div>
                            </td>
                            <td><span class="cl-tag">{{ $l->channelLabel() }}</span></td>
                            <td>
                                <div class="cl-url">{{ $l->url() }}</div>
                                <button type="button" class="cl-copy cl-copy-btn m-t-5" data-url="{{ $l->url() }}">@lang('Copy')</button>
                            </td>
                            <td class="cl-num">{{ number_format($st['sessions'] ?? 0) }}</td>
                            <td class="cl-num">{{ number_format($st['visitors'] ?? 0) }}</td>
                            <td class="cl-num">{{ number_format($st['calls'] ?? 0) }}</td>
                            <td class="cl-num">{{ number_format($st['whatsapp'] ?? 0) }}</td>
                            <td class="cl-num">{{ number_format($st['enquiries'] ?? 0) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.analytics.campaigns.destroy', $l->id) }}" onsubmit="return confirm('Remove this link from the list? Visits already counted stay in Traffic Sources.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="cl-del" title="@lang('Remove')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted p-4">@lang('No campaign links yet. Create one above.')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.cl-copy-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var url = btn.dataset.url;
        var done = function () { var t = btn.textContent; btn.textContent = 'Copied'; setTimeout(function () { btn.textContent = t; }, 1500); };
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(done);
        } else {
            var i = document.createElement('input'); i.value = url; document.body.appendChild(i); i.select(); document.execCommand('copy'); i.remove(); done();
        }
    });
});
</script>
@endsection
