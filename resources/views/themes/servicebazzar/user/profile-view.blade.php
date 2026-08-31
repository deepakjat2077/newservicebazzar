@extends($themeView.'layouts.site')

@section('content')

@php
    // Helper to pull an aggregate rating value + count off a listing using the
    // same stored-column fallbacks the search cards use (no extra queries).
    $bizRating = function ($listing) {
        $val = 0; $count = 0;
        foreach (['review', 'avg_rating', 'rating'] as $f) {
            if (!empty($listing->{$f}) && is_numeric($listing->{$f})) { $val = (float) $listing->{$f}; break; }
        }
        foreach (['total_review', 'review_count', 'ratings_count'] as $f) {
            if (!empty($listing->{$f}) && is_numeric($listing->{$f})) { $count = (int) $listing->{$f}; break; }
        }
        return ['value' => $val, 'count' => $count];
    };
    $bizImage = function ($listing) {
        return !empty($listing->thumb)
            ? loadImage('assets/images/listing/thumb/'.$listing->thumb)
            : asset('assets/images/no-image.png');
    };
    $bizPhone = function ($listing) {
        $m = is_array($listing->mobile_numbers) ? ($listing->mobile_numbers[0] ?? '') : $listing->mobile_numbers;
        return $m ?: '';
    };
    $bizUrl = function ($listing) {
        return route('listings.details', ['category' => optional($listing->category)->slug, 'slug' => $listing->slug]);
    };
    $bizLoc = function ($listing) {
        return trim(collect([optional($listing->location)->name ?: $listing->city, $listing->state])->filter()->implode(', '), ', ');
    };
    $isOwner = auth()->check() && (int) auth()->id() === (int) $user->id;
@endphp

<div class="sbsocial-wrap">
  <div class="container">
    <div class="sbsocial-grid">

      {{-- ================= LEFT: profile card + recent rail ================= --}}
      <aside class="sbsocial-side">

        <div class="sbsocial-card sbsocial-profile">
          <div class="sbsocial-cover"></div>
          <div class="sbsocial-identity">
            <img class="sbsocial-avatar"
                 src="{{ $user->image ? loadImage('assets/images/user/'.$user->image) : asset('assets/images/149071.png') }}"
                 alt="{{ $user->first_name }}">
            <div class="sbsocial-idtext">
              <h1 class="sbsocial-name">{{ trim($user->first_name.' '.$user->last_name) }}</h1>
              <span class="sbsocial-posts">{{ $postCount }} {{ Str::plural('post', $postCount) }}</span>
            </div>
          </div>

          @if($isOwner)
            <a href="{{ route('user.profile') }}" class="sbsocial-edit-btn">Edit Profile</a>
          @endif
        </div>

        @if($recentBusinesses->count())
        <div class="sbsocial-card sbsocial-recent">
          <h2 class="sbsocial-card-title">Recent Search</h2>

          @foreach($recentBusinesses as $biz)
          <div class="sbsocial-recent-item">
            <a href="{{ $bizUrl($biz) }}" class="sbsocial-recent-name">{{ $biz->name }}</a>
            <p class="sbsocial-recent-loc">{{ $bizLoc($biz) }}</p>
            <span class="sbsocial-recent-rate">Rate this Business</span>
            <a href="{{ $bizUrl($biz) }}#reviews" class="sbsocial-stars sbsocial-stars--empty" aria-label="Rate this business">
              @for($i=0;$i<5;$i++)<i class="far fa-star"></i>@endfor
            </a>
          </div>
          @endforeach
        </div>
        @endif

      </aside>

      {{-- ================= RIGHT: review feed ================= --}}
      <main class="sbsocial-feed">

        @forelse($reviews as $rating)
          @php
            $biz = $rating->listing;
            $agg = $bizRating($biz);
            $phone = $bizPhone($biz);
          @endphp

          <article class="sbsocial-card sbsocial-post" id="review-{{ $rating->id }}">

            <header class="sbsocial-post-head">
              <img class="sbsocial-post-avatar"
                   src="{{ $user->image ? loadImage('assets/images/user/'.$user->image) : asset('assets/images/149071.png') }}"
                   alt="{{ $user->first_name }}">
              <div class="sbsocial-post-meta">
                <p class="sbsocial-post-title">
                  <strong>{{ trim($user->first_name.' '.$user->last_name) }}</strong>
                  Rated <a href="{{ $bizUrl($biz) }}">{{ $biz->name }}</a>
                  <span class="sbsocial-inline-rating">{{ (int) $rating->ratings }} <i class="fas fa-star"></i></span>
                </p>
                <span class="sbsocial-post-time">{{ $rating->created_at->diffForHumans() }}</span>
              </div>
            </header>

            <a href="{{ $bizUrl($biz) }}" class="sbsocial-post-photo">
              <img src="{{ $bizImage($biz) }}" alt="{{ $biz->name }}" loading="lazy">
            </a>

            <div class="sbsocial-biz">
              <div class="sbsocial-biz-info">
                <a href="{{ $bizUrl($biz) }}" class="sbsocial-biz-name">{{ $biz->name }}</a>
                <div class="sbsocial-biz-rating">
                  @if($agg['value'] > 0)
                    <span class="sbsocial-biz-badge">{{ number_format($agg['value'], 1) }}</span>
                    <span class="sbsocial-stars">
                      @for($i=1;$i<=5;$i++)
                        <i class="fa{{ $i <= round($agg['value']) ? 's' : 'r' }} fa-star"></i>
                      @endfor
                    </span>
                  @endif
                  @if($agg['count'] > 0)
                    <span class="sbsocial-biz-count">{{ $agg['count'] }} Ratings</span>
                  @endif
                </div>
                @if($bizLoc($biz))
                  <p class="sbsocial-biz-loc"><i class="las la-map-marker"></i> {{ $bizLoc($biz) }}</p>
                @endif
              </div>

              @if($phone)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="sbsocial-call">
                  <i class="las la-phone"></i><span>CALL</span>
                </a>
              @endif
            </div>

            @if(!empty($rating->review))
              <p class="sbsocial-review-text">{{ $rating->review }}</p>
            @endif

            <footer class="sbsocial-actions">
              <a href="{{ $bizUrl($biz) }}#review-{{ $rating->id }}" class="sbsocial-act">
                <i class="las la-thumbs-up"></i> Helpful @if(($rating->likes_count ?? 0) > 0)({{ $rating->likes_count }})@endif
              </a>
              <a href="{{ $bizUrl($biz) }}#review-{{ $rating->id }}" class="sbsocial-act">
                <i class="las la-comment"></i> Comments @if(($rating->comments_count ?? 0) > 0)({{ $rating->comments_count }})@endif
              </a>
              <button type="button" class="sbsocial-act sbsocial-share"
                      data-url="{{ $bizUrl($biz) }}#review-{{ $rating->id }}"
                      data-text="{{ trim($user->first_name.' '.$user->last_name) }} rated {{ $biz->name }} {{ (int) $rating->ratings }}★">
                <i class="las la-share"></i> Share
              </button>
            </footer>

          </article>

        @empty
          <div class="sbsocial-card sbsocial-empty">
            <i class="las la-comment-dots"></i>
            <p>{{ $isOwner ? 'You haven\'t' : $user->first_name.' hasn\'t' }} reviewed any business yet.</p>
            @if($isOwner)
              <a href="{{ url('/') }}" class="sbsocial-edit-btn" style="max-width:220px;margin:12px auto 0;">Find a business to review</a>
            @endif
          </div>
        @endforelse

      </main>

    </div>
  </div>
</div>

<style>
.sbsocial-wrap{background:#f4f5f7;padding:24px 0 64px;}
.sbsocial-grid{display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start;}
.sbsocial-side{position:sticky;top:20px;display:flex;flex-direction:column;gap:16px;}
.sbsocial-card{background:#fff;border:1px solid #e7e9ee;border-radius:12px;box-shadow:0 1px 3px rgba(16,24,40,.04);overflow:hidden;}

/* profile card */
.sbsocial-cover{height:110px;background:linear-gradient(135deg,#7c93f0 0%,#667eea 40%,#764ba2 100%);}
.sbsocial-identity{padding:0 18px 12px;display:flex;align-items:flex-end;gap:12px;margin-top:-42px;}
.sbsocial-avatar{width:92px;height:92px;border-radius:50%;border:4px solid #fff;object-fit:cover;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.12);}
.sbsocial-idtext{transform:translateY(14px);}
.sbsocial-name{font-size:19px;font-weight:700;color:#111;margin:0;line-height:1.2;}
.sbsocial-posts{font-size:13px;color:#6b7280;}
.sbsocial-edit-btn{display:block;margin:14px 18px 18px;padding:9px;text-align:center;border:1px solid #d7dae0;
  border-radius:8px;color:#374151;font-weight:600;font-size:14px;text-decoration:none;transition:.15s;}
.sbsocial-edit-btn:hover{background:#f7f8fa;color:#111;}

/* recent rail */
.sbsocial-recent{padding:18px;}
.sbsocial-card-title{font-size:15px;font-weight:700;color:#111;margin:0 0 12px;}
.sbsocial-recent-item{padding:12px 0;border-bottom:1px solid #eef0f3;}
.sbsocial-recent-item:last-child{border-bottom:0;padding-bottom:0;}
.sbsocial-recent-name{font-size:14px;font-weight:600;color:#1f2937;text-decoration:none;display:block;}
.sbsocial-recent-name:hover{color:#e23744;}
.sbsocial-recent-loc{font-size:12px;color:#9096a2;margin:2px 0 8px;}
.sbsocial-recent-rate{font-size:12px;color:#6b7280;display:block;margin-bottom:4px;}
.sbsocial-stars{color:#f5a623;font-size:15px;display:inline-flex;gap:3px;text-decoration:none;}
.sbsocial-stars--empty{color:#c9ced6;}
.sbsocial-stars--empty:hover{color:#f5a623;}

/* feed */
.sbsocial-feed{display:flex;flex-direction:column;gap:16px;min-width:0;}
.sbsocial-post{padding:16px;}
.sbsocial-post-head{display:flex;gap:12px;align-items:center;margin-bottom:12px;}
.sbsocial-post-avatar{width:44px;height:44px;border-radius:50%;object-fit:cover;}
.sbsocial-post-meta{min-width:0;}
.sbsocial-post-title{margin:0;font-size:14px;color:#374151;line-height:1.45;}
.sbsocial-post-title strong{color:#111;}
.sbsocial-post-title a{color:#e23744;text-decoration:none;font-weight:600;}
.sbsocial-inline-rating{background:#eafaf1;color:#1f9d55;font-weight:700;padding:1px 7px;border-radius:5px;
  font-size:12px;white-space:nowrap;margin-left:2px;}
.sbsocial-inline-rating .fa-star{font-size:10px;}
.sbsocial-post-time{font-size:12px;color:#9096a2;}
.sbsocial-post-photo{display:block;border-radius:10px;overflow:hidden;margin-bottom:14px;background:#f2f3f5;}
.sbsocial-post-photo img{width:100%;max-height:320px;object-fit:cover;display:block;}

.sbsocial-biz{display:flex;align-items:center;justify-content:space-between;gap:12px;
  border:1px solid #eef0f3;border-radius:10px;padding:12px 14px;}
.sbsocial-biz-info{min-width:0;}
.sbsocial-biz-name{font-size:16px;font-weight:700;color:#111;text-decoration:none;display:block;}
.sbsocial-biz-name:hover{color:#e23744;}
.sbsocial-biz-rating{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:5px 0;}
.sbsocial-biz-badge{background:#1f9d55;color:#fff;font-weight:700;font-size:13px;padding:1px 8px;border-radius:5px;}
.sbsocial-biz-count{font-size:12px;color:#6b7280;}
.sbsocial-biz-loc{font-size:13px;color:#6b7280;margin:0;}
.sbsocial-call{flex-shrink:0;display:inline-flex;flex-direction:column;align-items:center;gap:2px;
  color:#1a73e8;text-decoration:none;font-weight:700;font-size:12px;}
.sbsocial-call i{font-size:20px;}

.sbsocial-review-text{margin:14px 0 4px;color:#374151;font-size:14px;line-height:1.55;}

.sbsocial-actions{display:flex;gap:6px;border-top:1px solid #eef0f3;margin-top:14px;padding-top:6px;}
.sbsocial-act{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;
  background:none;border:0;padding:10px;border-radius:8px;color:#5f6673;font-size:13px;font-weight:600;
  cursor:pointer;text-decoration:none;transition:.15s;}
.sbsocial-act:hover{background:#f5f6f8;color:#111;}
.sbsocial-act i{font-size:17px;}

/* empty */
.sbsocial-empty{padding:48px 20px;text-align:center;color:#9096a2;}
.sbsocial-empty i{font-size:40px;display:block;margin-bottom:10px;}
.sbsocial-empty p{margin:0;font-size:15px;}

@media (max-width:900px){
  .sbsocial-grid{grid-template-columns:1fr;}
  .sbsocial-side{position:static;}
}
</style>

<script>
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.sbsocial-share');
  if (!btn) return;
  var url = btn.getAttribute('data-url');
  var text = btn.getAttribute('data-text') || '';
  if (navigator.share) {
    navigator.share({ title: 'ServiceBazzar', text: text, url: url }).catch(function(){});
  } else if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(function () {
      var old = btn.innerHTML;
      btn.innerHTML = '<i class="las la-check"></i> Copied';
      setTimeout(function(){ btn.innerHTML = old; }, 1500);
    });
  }
});
</script>

@endsection
