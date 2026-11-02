@extends('admin.layouts.master')
@section('title','Excluded IPs')
@section('content')

<style>
.xip-table td { vertical-align: middle; font-size: 13px; }
.xip-value { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px; }
.xip-mine { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
.xip-mine code { font-size: 13px; }
</style>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1">
            Traffic from these addresses is not counted. Use it for the office wifi, a staff phone, or anywhere
            the site is opened for testing — those visits were landing in the figures as strangers.
        </p>
        <p class="mb-0 text-muted small">
            Admins are already skipped while signed in. This is for everything else on the same connection.
            Adding an address also hides what it has already recorded, so the numbers correct themselves.
        </p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="xip-mine mb-3">
            You are on <code>{{ $yourIp }}</code>@if($yourRange), and your connection's range is
            <code>{{ $yourRange }}</code>@endif.
            @if($yourRange)
                <div class="text-muted small mt-1">
                    On this kind of connection every device gets its own address under one shared range, so add the
                    <strong>range</strong> to cover the whole wifi — the address alone covers only this one device.
                </div>
            @endif
        </div>

        <form action="{{ route('admin.analytics.excluded-ips.store') }}" method="POST" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-4">
                <label>IP address or range</label>
                <input type="text" name="value" class="form-control xip-value" id="xipValue"
                       value="{{ old('value') }}" placeholder="49.36.180.22" required>
            </div>
            <div class="form-group col-md-4">
                <label>What is it? <span class="text-muted">(optional)</span></label>
                <input type="text" name="label" class="form-control" value="{{ old('label') }}" placeholder="Office wifi">
            </div>
            <div class="form-group col-md-4">
                <button type="submit" class="btn btn-primary">Stop counting this</button>
                <button type="button" class="btn btn-outline-secondary" data-xip-fill="{{ $yourIp }}">Use my address</button>
                @if($yourRange)
                    <button type="button" class="btn btn-outline-secondary" data-xip-fill="{{ $yourRange }}">Use my range</button>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table xip-table mb-0">
            <thead>
                <tr>
                    <th>Address / range</th>
                    <th>What it is</th>
                    <th>Added</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr>
                        <td class="xip-value">{{ $rule->value }}</td>
                        <td>{{ $rule->label ?: '—' }}</td>
                        <td>{{ $rule->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                        <td class="text-right">
                            <form action="{{ route('admin.analytics.excluded-ips.destroy', $rule->id) }}" method="POST"
                                  onsubmit="return confirm('Count this address again from now on?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Nothing is excluded yet.</td>
                    </tr>
                @endforelse

                {{-- Set in .env, so it cannot be removed from here. --}}
                @foreach($fromEnv as $envIp)
                    <tr>
                        <td class="xip-value">{{ $envIp }}</td>
                        <td class="text-muted">From the server's .env file</td>
                        <td>—</td>
                        <td class="text-right text-muted small">Not editable here</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('[data-xip-fill]').forEach(function (b) {
    b.addEventListener('click', function () {
        document.getElementById('xipValue').value = b.getAttribute('data-xip-fill');
    });
});
</script>

@endsection
