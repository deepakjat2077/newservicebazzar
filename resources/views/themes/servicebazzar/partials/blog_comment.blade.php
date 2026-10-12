{{-- One blog comment. Email is never printed. --}}
<div class="sb-comment" id="comment-{{ $c->id }}">
    <div class="sb-comment-avatar" aria-hidden="true">
        <svg viewBox="0 0 48 48" width="48" height="48"><rect width="48" height="48" fill="#c4c4c4"/><circle cx="24" cy="18" r="9" fill="#fff"/><path d="M6 48c1.5-11 9-17 18-17s16.5 6 18 17z" fill="#fff"/></svg>
    </div>
    <div class="sb-comment-body">
        <div class="sb-comment-head">
            @if($c->website)
                <a class="sb-comment-name" href="{{ $c->website }}" target="_blank" rel="nofollow ugc noopener">{{ $c->name }}</a>
            @else
                <span class="sb-comment-name">{{ $c->name }}</span>
            @endif
            <time class="sb-comment-date" datetime="{{ $c->created_at->toIso8601String() }}">{{ $c->created_at->format('F j, Y') }} at {{ $c->created_at->format('g:i a') }}</time>
        </div>
        <div class="sb-comment-text">{!! nl2br(e($c->comment)) !!}</div>
    </div>
</div>
