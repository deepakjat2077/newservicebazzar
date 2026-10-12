@extends('admin.layouts.master')
@section('title', 'Featured Listings')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-1">Featured Listings on the home page</h5>
                <p class="text-muted mb-3" style="font-size:13px;">
                    Pick the listings the home page shows under "Featured Listings", in the order below. This has nothing
                    to do with plans or payments — only what you add here is shown. The first {{ $maxShown }} that are on are shown.
                    (If this list is empty, the home page falls back to trusted listings as before.)
                </p>

                <label class="font-weight-bold mb-1" for="hfSearch">Add a listing</label>
                <div style="position:relative;max-width:560px;">
                    <input type="text" id="hfSearch" class="form-control" placeholder="Type a business name, listing ID or phone number" autocomplete="off">
                    <div id="hfResults" class="hf-results" hidden></div>
                </div>
                <form method="post" action="{{ route('admin.home_featured.store') }}" id="hfAddForm">
                    @csrf
                    <input type="hidden" name="listing_id" id="hfListingId">
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">#</th>
                                <th>Listing</th>
                                <th>Show between <small class="text-muted">(optional)</small></th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $shown = 0; @endphp
                        @forelse($items as $i => $item)
                            @php
                                $l = $item->listing;
                                $live = $item->is_active
                                    && (!$item->starts_at || $item->starts_at->lte(now()))
                                    && (!$item->ends_at || $item->ends_at->gt(now()))
                                    && $l && (int) $l->status === 1;
                                if ($live) { $shown++; }
                                $state = !$l ? ['Listing deleted', 'danger']
                                    : ((int) $l->status !== 1 ? ['Listing not live', 'warning']
                                    : (!$item->is_active ? ['Hidden', 'secondary']
                                    : ($item->starts_at && $item->starts_at->isFuture() ? ['Scheduled', 'info']
                                    : ($item->ends_at && $item->ends_at->lte(now()) ? ['Ended', 'warning']
                                    : ($shown > $maxShown ? ['On (beyond first ' . $maxShown . ')', 'light'] : ['Showing', 'success'])))));
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <strong class="mr-1">{{ $i + 1 }}</strong>
                                        <form method="post" action="{{ route('admin.home_featured.move', [$item->id, 'up']) }}">@csrf
                                            <button class="btn btn-xs btn-outline-secondary" {{ $i === 0 ? 'disabled' : '' }} title="Move up"><i class="fas fa-arrow-up"></i></button>
                                        </form>
                                        <form method="post" action="{{ route('admin.home_featured.move', [$item->id, 'down']) }}">@csrf
                                            <button class="btn btn-xs btn-outline-secondary" {{ $loop->last ? 'disabled' : '' }} title="Move down"><i class="fas fa-arrow-down"></i></button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    @if($l)
                                        <div class="d-flex align-items-center" style="gap:10px;">
                                            <img src="{{ $l->thumb ? asset('assets/images/listing/thumb/' . $l->thumb) : asset('assets/images/no-image.png') }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                                            <div>
                                                <strong>{{ $l->name }}</strong> <small class="text-muted">#{{ $l->id }}</small><br>
                                                <small class="text-muted">{{ collect([optional($l->category)->name, $l->city])->filter()->implode(' · ') }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Listing #{{ $item->listing_id }} no longer exists</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="post" action="{{ route('admin.home_featured.update', $item->id) }}" class="d-flex align-items-center flex-wrap" style="gap:6px;">
                                        @csrf
                                        <input type="date" name="starts_at" class="form-control form-control-sm" style="width:150px;" value="{{ optional($item->starts_at)->format('Y-m-d') }}" title="From">
                                        <span class="text-muted">to</span>
                                        <input type="date" name="ends_at" class="form-control form-control-sm" style="width:150px;" value="{{ optional($item->ends_at)->format('Y-m-d') }}" title="Until">
                                        <button class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                </td>
                                <td><span class="badge badge-{{ $state[1] }}">{{ $state[0] }}</span></td>
                                <td class="text-right">
                                    <div class="d-inline-flex" style="gap:6px;">
                                        <form method="post" action="{{ route('admin.home_featured.toggle', $item->id) }}">@csrf
                                            <button class="btn btn-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $item->is_active ? 'Hide' : 'Show' }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.home_featured.destroy', $item->id) }}" onsubmit="return confirm('Remove this listing from Featured Listings?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No listings picked yet. Search above to add the first one.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hf-results{position:absolute;left:0;right:0;top:100%;z-index:50;background:#fff;border:1px solid #dfe3ec;border-radius:8px;box-shadow:0 10px 30px rgba(15,23,42,.12);max-height:340px;overflow-y:auto;margin-top:4px;}
    .hf-results[hidden]{display:none;}
    .hf-results button{display:block;width:100%;text-align:left;border:0;background:#fff;padding:9px 14px;border-bottom:1px solid #f1f3f8;cursor:pointer;}
    .hf-results button:hover{background:#f5f8ff;}
    .hf-results small{color:#7a8296;}
    .hf-results .none{padding:12px 14px;color:#7a8296;font-size:13px;}
</style>
<script>
(function () {
    var input = document.getElementById('hfSearch'), box = document.getElementById('hfResults');
    var url = @json(route('admin.home_featured.search')), timer;
    function esc(s) { return String(s || '').replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) { box.hidden = true; return; }
        timer = setTimeout(function () {
            fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    box.innerHTML = rows.length
                        ? rows.map(function (r) { return '<button type="button" data-id="' + r.id + '"><strong>' + esc(r.name) + '</strong> <small>#' + r.id + (r.meta ? ' · ' + esc(r.meta) : '') + '</small></button>'; }).join('')
                        : '<div class="none">No live listing found (or it is already in the list).</div>';
                    box.hidden = false;
                });
        }, 250);
    });
    box.addEventListener('click', function (e) {
        var b = e.target.closest('button[data-id]');
        if (!b) return;
        document.getElementById('hfListingId').value = b.getAttribute('data-id');
        document.getElementById('hfAddForm').submit();
    });
    document.addEventListener('click', function (e) { if (!e.target.closest('#hfResults') && e.target !== input) box.hidden = true; });
})();
</script>
@endsection
