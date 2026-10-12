@extends('admin.layouts.master')
@section('title', 'Blog Comments')
@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-3">
      <div class="card-body">
        <form method="get" class="d-flex flex-wrap align-items-end" style="gap:10px;">
          <div style="flex:1;min-width:220px;">
            <label class="mb-1 small">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email or comment text">
          </div>
          <button class="btn btn--primary" type="submit">Search</button>
          @if(request('search'))<a href="{{ route('admin.blog.comments.index') }}" class="btn btn-outline-secondary">Clear</a>@endif
        </form>
        <p class="small text-muted mb-0 mt-2">Comments appear on the blog straight away. Comments with 2 or more links are saved but kept hidden (marked <b>Hidden</b>).</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead><tr><th>Date</th><th>Name / Email</th><th>Comment</th><th>Blog</th><th></th></tr></thead>
            <tbody>
            @forelse($comments as $c)
              @php $b = $blogs[$c->blog_id] ?? null; @endphp
              <tr>
                <td class="text-nowrap small">{{ $c->created_at->format('d M Y') }}<br><span class="text-muted">{{ $c->created_at->format('g:i a') }}</span></td>
                <td class="small">
                  <b>{{ $c->name }}</b>
                  @if($c->status == 0)<span class="badge badge-warning ml-1">Hidden</span>@endif
                  <br><a href="mailto:{{ $c->email }}">{{ $c->email }}</a>
                  @if($c->website)<br><a href="{{ $c->website }}" target="_blank" rel="nofollow noopener">{{ \Illuminate\Support\Str::limit($c->website, 40) }}</a>@endif
                </td>
                <td style="max-width:420px;white-space:normal;">{!! nl2br(e($c->comment)) !!}</td>
                <td class="small" style="max-width:220px;white-space:normal;">
                  @if($b && $b['slug'])
                    <a href="{{ route('blog.details', $b['slug']) }}#comment-{{ $c->id }}" target="_blank">{{ \Illuminate\Support\Str::limit($b['title'], 60) }}</a>
                  @else
                    <span class="text-muted">(deleted blog)</span>
                  @endif
                </td>
                <td class="text-nowrap">
                  <form method="POST" action="{{ route('admin.blog.comments.delete', $c->id) }}" onsubmit="return confirm('Delete this comment? It will be removed from the blog.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="las la-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No comments yet.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @if($comments->hasPages())<div class="card-footer">{{ $comments->links() }}</div>@endif
    </div>
  </div>
</div>
@endsection
