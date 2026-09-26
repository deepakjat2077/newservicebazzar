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
    .cl-tag{ display:inline-block; padding:3px 10px; border-radius:20px; background:#e7f1fb; color:#1c7ed6; font-size:12px; font-weight:600; white-space:nowrap; }
    .cl-list{ display:grid; gap:14px; padding:16px; }
    .cl-card{ border:1px solid #e9ecef; border-radius:10px; padding:14px 16px; background:#fff; }
    .cl-top{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    .cl-name{ font-size:15px; font-weight:700; color:#212529; margin:0 0 4px; word-break:break-word; }
    .cl-meta{ display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:12px; color:#868e96; }
    .cl-linkrow{ display:flex; gap:8px; margin:12px 0; min-width:0; }
    .cl-linkrow input{ flex:1; min-width:0; height:36px; border:1px solid #e9ecef; border-radius:6px; background:#f8f9fa; padding:0 10px; font-family:monospace; font-size:12px; color:#495057; }
    .cl-copy{ flex:0 0 auto; height:36px; border:1px solid #dfe3e8; background:#fff; border-radius:6px; padding:0 14px; font-size:13px; font-weight:600; cursor:pointer; }
    .cl-copy:hover{ border-color:#1c7ed6; color:#1c7ed6; }
    .cl-stats{ display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:8px; }
    .cl-stat{ background:#f8f9fa; border-radius:8px; padding:8px 10px; }
    .cl-stat b{ display:block; font-size:18px; color:#212529; line-height:1.2; }
    .cl-stat span{ font-size:12px; color:#868e96; }
    .cl-del{ flex:0 0 auto; border:0; background:none; color:#adb5bd; cursor:pointer; padding:4px; }
    .cl-del:hover{ color:#e03131; }
    .cl-empty{ text-align:center; color:#868e96; padding:28px 16px; }
    @media (max-width:575px){ .cl-stats{ grid-template-columns:repeat(3, minmax(0,1fr)); } }
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
        @if($links->isEmpty())
            <div class="cl-empty">@lang('No campaign links yet. Create one above.')</div>
        @else
            <div class="cl-list">
                @foreach($links as $l)
                    @php $st = $stats[$l->id] ?? []; @endphp
                    <div class="cl-card">
                        <div class="cl-top">
                            <div>
                                <p class="cl-name">{{ $l->name }}</p>
                                <div class="cl-meta">
                                    <span class="cl-tag">{{ $l->channelLabel() }}</span>
                                    <span>{{ $l->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.analytics.campaigns.destroy', $l->id) }}" onsubmit="return confirm('Remove this link from the list? Visits already counted stay in Traffic Sources.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="cl-del" title="@lang('Remove')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        <div class="cl-linkrow">
                            <input type="text" value="{{ $l->url() }}" readonly aria-label="@lang('Campaign link')">
                            <button type="button" class="cl-copy cl-copy-btn" data-url="{{ $l->url() }}">@lang('Copy')</button>
                        </div>
                        <div class="cl-stats">
                            <div class="cl-stat"><b>{{ number_format($st['sessions'] ?? 0) }}</b><span>@lang('Visits')</span></div>
                            <div class="cl-stat"><b>{{ number_format($st['visitors'] ?? 0) }}</b><span>@lang('Visitors')</span></div>
                            <div class="cl-stat"><b>{{ number_format($st['calls'] ?? 0) }}</b><span>@lang('Calls')</span></div>
                            <div class="cl-stat"><b>{{ number_format($st['whatsapp'] ?? 0) }}</b><span>@lang('WhatsApp')</span></div>
                            <div class="cl-stat"><b>{{ number_format($st['enquiries'] ?? 0) }}</b><span>@lang('Enquiries')</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
