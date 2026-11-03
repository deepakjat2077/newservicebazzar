
@php
    $gReviews = $listing->gmb_reviews_json;
    if (is_string($gReviews)) {
        $gReviews = json_decode($gReviews, true);
    }
    $gReviews = is_array($gReviews) ? $gReviews : [];
    $gRating  = (float) ($listing->gmb_rating ?? 0);
    $gCount   = (int)   ($listing->gmb_review_count ?? 0);
@endphp

@if($gRating > 0 && $gCount > 0 && count($gReviews))
@foreach($gReviews as $gIdx => $review)
<div class="reviewer mb-3{{ $gIdx >= 5 ? ' review-item-load' : '' }}"
     {{ $gIdx >= 5 ? 'style=display:none;' : '' }}>
    <div class="content w-100">
        <div class="review-item">

            <div class="review-header">
                <div class="review-left">
                    {{-- Initials avatar --}}
                    <span class="review-avatar gmb-avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr(trim($review['author'] ?? 'G'), 0, 1)) }}
                    </span>
                    <span class="review-name">{{ $review['author'] ?? '' }}</span>
                </div>
                <div class="review-right">
                    @if(!empty($review['when']))
                        <span class="review-date">{{ $review['when'] }}</span>
                    @endif
                    {{-- Google attribution (required by Google ToS) --}}
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($listing->name ?? '') }}"
                       target="_blank" rel="nofollow noopener"
                       class="gmb-source-badge" title="Review on Google Maps">
                        <svg width="14" height="14" viewBox="0 0 48 48" aria-label="Google Maps" role="img">
                            <path fill="#4285F4" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#34A853" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#EA4335" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="review-stars">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= (int) ($review['rating'] ?? 0))
                        <i class="fas fa-star"></i>
                    @else
                        <i class="far fa-star"></i>
                    @endif
                @endfor
            </div>

            @if(!empty($review['text']))
                <div class="review-text">
                    "{{ $review['text'] }}"
                </div>
            @endif

        </div>
    </div>
</div>
@endforeach

<style>
.gmb-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #1a73e8;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    flex-shrink: 0;
    /* reuse same sizing as .review-avatar img */
}
.gmb-source-badge {
    display: inline-flex;
    align-items: center;
    margin-left: 6px;
    opacity: .7;
    vertical-align: middle;
}
.gmb-source-badge:hover { opacity: 1; }
</style>
@endif
