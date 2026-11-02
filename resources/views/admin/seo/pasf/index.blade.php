@extends('admin.layouts.master')
@section('title','People Also Search')
@section('content')

<style>
.pasf-status { font-size:12px; padding:3px 8px; border-radius:10px; }
.pasf-status.approved { background:#dcfce7; color:#166534; }
.pasf-status.draft { background:#fef3c7; color:#92400e; }
.pasf-status.none { background:#f1f5f9; color:#64748b; }
.pasf-table td { vertical-align:middle; }
</style>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1">
            On each city page of a trial category, a <strong>"People in &lt;city&gt; also search for"</strong> block:
            the related searches Google shows for "&lt;category&gt; in &lt;city&gt;", each with a short answer written
            from that city's own listings.
        </p>
        <p class="mb-0 text-muted small">
            Nothing shows on the site until you <strong>Approve</strong> it. Read every answer first: the AI only gets
            the listings' names, areas, hours and what their descriptions mention, but it can still get something wrong.
            Trial categories are set in <code>SEO_PASF_FAQ_CATEGORIES</code>.
        </p>
    </div>
</div>

@if($categories->count() > 1)
    <ul class="nav nav-pills mb-3">
        @foreach($categories as $c)
            <li class="nav-item">
                <a class="nav-link {{ $category && $category->id === $c->id ? 'active' : '' }}" href="{{ route('admin.seo.pasf.index', ['category' => $c->id]) }}">{{ $c->name }}</a>
            </li>
        @endforeach
    </ul>
@endif

@if(!$category)
    <div class="alert alert-info">No category is switched on.</div>
@else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $category->name }}: city pages</h5>
            <span class="text-muted small">
                {{ $rows->where('status', 'approved')->count() }} live ·
                {{ $rows->where('status', 'draft')->count() }} waiting for approval ·
                {{ $cities->count() - $rows->count() }} not written
            </span>
        </div>
        <div class="table-responsive">
            <table class="table pasf-table mb-0">
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Listings</th>
                        <th>Status</th>
                        <th>Q&As</th>
                        <th>Written</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cities as $city)
                        @php $row = $rows->get($city->id); @endphp
                        <tr>
                            <td>
                                <a href="{{ url('/' . $city->slug . '/' . $category->slug) }}" target="_blank" rel="noopener">{{ $city->name }}</a>
                            </td>
                            <td>{{ $city->live_count }}</td>
                            <td>
                                @if(!$row)
                                    <span class="pasf-status none">Not written</span>
                                @elseif($row->status === 'approved')
                                    <span class="pasf-status approved">Live</span>
                                @else
                                    <span class="pasf-status draft">Draft</span>
                                @endif
                                @if($row && $row->error)
                                    <div class="small text-danger mt-1">{{ $row->error }}</div>
                                @endif
                            </td>
                            <td>{{ $row ? count($row->faqs ?? []) : '-' }}</td>
                            <td class="small text-muted">{{ $row && $row->generated_at ? $row->generated_at->format('d M, h:i A') : '-' }}</td>
                            <td class="text-right">
                                @if($row && !empty($row->faqs))
                                    <a href="{{ $row->status === 'approved' ? url('/' . $city->slug . '/' . $category->slug) . '#pasf-1' : $row->previewUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> {{ $row->status === 'approved' ? 'See' : 'Preview' }}
                                    </a>
                                    <a href="{{ route('admin.seo.pasf.edit', $row->id) }}" class="btn btn-sm btn-primary">
                                        {{ $row->status === 'approved' ? 'Edit' : 'Review & approve' }}
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('admin.seo.pasf.generate') }}" class="d-inline pasf-generate">
                                        @csrf
                                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                                        <input type="hidden" name="location_id" value="{{ $city->id }}">
                                        <button class="btn btn-sm btn-outline-primary">Write now</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<script>
    document.querySelectorAll('.pasf-generate').forEach(function (f) {
        f.addEventListener('submit', function () {
            var b = f.querySelector('button');
            b.disabled = true;
            b.textContent = 'Writing... (up to 30 sec)';
        });
    });
</script>
@endsection
