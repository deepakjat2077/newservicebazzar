@extends('admin.layouts.master')
@section('title', 'Admin Login History')
@section('content')
@php
    $eventBadge = ['login' => ['Login', 'success'], 'auto_login' => ['Auto sign-in', 'info'], 'failed' => ['Failed', 'danger'], 'logout' => ['Logout', 'secondary'], 'auto_logout' => ['Auto logout', 'warning']];
    $nameOf = fn ($a) => $a ? (trim($a->first_name . ' ' . $a->last_name) ?: $a->username) : null;
@endphp
<div class="row">
  <div class="col-12">
    <div class="card mb-3">
      <div class="card-body">
        <form method="get" class="d-flex flex-wrap align-items-end" style="gap:10px;">
          <div>
            <label class="mb-1 small">Admin</label>
            <select name="admin_id" class="form-control form-control-sm">
              <option value="">All admins</option>
              @foreach($admins as $a)
                <option value="{{ $a->id }}" @selected(request('admin_id') == $a->id)>{{ $nameOf($a) }} ({{ $a->username }})</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 small">Type</label>
            <select name="event" class="form-control form-control-sm">
              <option value="">All</option>
              @foreach($eventBadge as $k => $v)<option value="{{ $k }}" @selected(request('event') === $k)>{{ $v[0] }}</option>@endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 small">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
          </div>
          <button class="btn btn-sm btn-primary">Filter</button>
          @if(request()->hasAny(['admin_id','event','date']))<a href="{{ route('admin.history.admin_login') }}" class="btn btn-sm btn-light">Reset</a>@endif
        </form>
        <p class="text-muted small mb-0 mt-2">
          <i class="fas fa-map-marker-alt"></i> <b>GPS</b> = exact location shared by the browser at login (when allowed);
          <b>IP</b> = approximate city from the internet connection.
        </p>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr><th>Admin</th><th>Type</th><th>Date &amp; time</th><th>Location</th><th>IP</th><th>Device</th></tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
              @php $b = $eventBadge[$log->event] ?? [$log->event, 'light']; @endphp
              <tr>
                <td>
                  <strong>{{ $nameOf($log->admin) ?? ($log->username ?: 'Unknown') }}</strong>
                  @if($log->username)<br><small class="text-muted">{{ $log->username }}</small>@endif
                </td>
                <td><span class="badge badge-{{ $b[1] }}">{{ $b[0] }}</span></td>
                <td style="white-space:nowrap;">
                  {{ $log->created_at->timezone('Asia/Kolkata')->format('d M Y') }}<br>
                  <small class="text-muted">{{ $log->created_at->timezone('Asia/Kolkata')->format('h:i:s A') }} · {{ $log->created_at->diffForHumans() }}</small>
                </td>
                <td style="min-width:220px;">
                  @php $place = collect([$log->city, $log->state, $log->country])->filter()->implode(', '); @endphp
                  {{ $place ?: 'Looking up…' }}
                  @if($log->location_source)
                    <span class="badge badge-{{ $log->location_source === 'gps' ? 'success' : 'light' }}">{{ strtoupper($log->location_source) }}</span>
                  @endif
                  @if($log->address)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($log->address, 110) }}</small>@endif
                  @if($log->accuracy_m)<br><small class="text-muted">accurate to ~{{ $log->accuracy_m }} m</small>@endif
                  @if($log->latitude && $log->longitude)
                    <br><a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" rel="noopener" class="small"><i class="fas fa-map"></i> Open in Maps</a>
                  @endif
                </td>
                <td><small>{{ $log->ip }}</small></td>
                <td style="white-space:nowrap;"><small>{{ $log->browser }} · {{ $log->os }}<br>{{ $log->device }}</small></td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">No admin logins recorded yet. Every admin sign-in from now on will show here.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @if($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
    </div>
  </div>
</div>
@endsection
