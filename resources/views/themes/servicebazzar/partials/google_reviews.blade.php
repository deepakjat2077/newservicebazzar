{{-- Google reviews block for the listing details page. Expects $listing. --}}
@php
    $gReviews = $listing->gmb_reviews_json;
    if (is_string($gReviews)) {
        $gReviews = json_decode($gReviews, true);
    }
    $gReviews = is_array($gReviews) ? $gReviews : [];
    $gRating = (float) ($listing->gmb_rating ?? 0);
    $gCount = (int) ($listing->gmb_review_count ?? 0);
@endphp

@if($gRating > 0 && $gCount > 0)
<section class="listing-widget gmb-reviews" id="googleReviewsSection" aria-labelledby="gmb-reviews-title">
    <div class="gmb-reviews__head">
        <h4 class="media-title mb-0" id="gmb-reviews-title">More Reviews</h4>
        <div class="gmb-reviews__summary">
            <span class="gmb-reviews__score">{{ number_format($gRating, 1) }}</span>
            <span class="gmb-reviews__stars" aria-label="{{ number_format($gRating, 1) }} out of 5 stars">
                @for($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= round($gRating) ? 'is-on' : '' }}">★</span>
                @endfor
            </span>
            <span class="gmb-reviews__count">{{ number_format($gCount) }} reviews</span>
        </div>
    </div>

    @foreach($gReviews as $review)
        <article class="gmb-reviews__item">
            <header>
                <strong>
                    @if(!empty($review['author_url']) && str_starts_with($review['author_url'], 'https://'))
                        <a href="{{ $review['author_url'] }}" target="_blank" rel="nofollow noopener">{{ $review['author'] }}</a>
                    @else
                        {{ $review['author'] }}
                    @endif
                </strong>
                <span class="gmb-reviews__stars-small" aria-label="{{ (int) $review['rating'] }} out of 5">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= (int) $review['rating'] ? 'is-on' : '' }}">★</span>
                    @endfor
                </span>
                @if(!empty($review['when']))
                    <small>{{ $review['when'] }}</small>
                @endif
            </header>
            <p>{{ $review['text'] }}</p>
        </article>
    @endforeach

    <div class="gmb-reviews__footer">
        <span class="gmb-reviews__powered">Source: Google Maps</span>
    </div>
</section>

<style>
    .gmb-reviews { margin-top: 20px; }
    .gmb-reviews__item p { overflow-wrap: anywhere; }
    .gmb-reviews__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; }
    .gmb-reviews__summary { display: flex; align-items: center; gap: 8px; }
    .gmb-reviews__score { font-size: 1.5rem; font-weight: 700; }
    .gmb-reviews__stars span { color: #d0d5dd; font-size: 1.1rem; }
    .gmb-reviews__stars span.is-on, .gmb-reviews__stars-small span.is-on { color: #f5b301; }
    .gmb-reviews__stars-small span { color: #d0d5dd; font-size: .9rem; }
    .gmb-reviews__count { color: #667085; font-size: .9rem; }
    .gmb-reviews__item { border-top: 1px solid #eaecf0; padding: 14px 0; }
    .gmb-reviews__item header { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .gmb-reviews__item p { margin: 6px 0 0; color: #344054; line-height: 1.5; }
    .gmb-reviews__item small { color: #667085; }
    .gmb-reviews__footer { margin-top: 8px; color: #667085; font-size: .85rem; }
</style>
@endif
