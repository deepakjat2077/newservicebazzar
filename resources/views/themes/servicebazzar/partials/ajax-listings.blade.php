@php
    $allListings = collect(method_exists($listings, 'items') ? $listings->items() : $listings)
        ->filter(function ($item) {
            return is_object($item) && isset($item->id);
        })
        ->values();
@endphp

{{-- Empty state: no listings in this category/city combo --}}
@if($allListings->isEmpty())
    <div class="col-12">
        <div style="
            text-align: center;
            padding: 120px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        ">
            <!-- Decorative background circles -->
            <div style="
                position: absolute;
                top: -50px;
                right: -50px;
                width: 200px;
                height: 200px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            "></div>
            <div style="
                position: absolute;
                bottom: -30px;
                left: -30px;
                width: 150px;
                height: 150px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            "></div>

            <!-- Main content -->
            <div style="position: relative; z-index: 1;">
                <!-- Coming Soon Badge -->
                <div style="
                    display: inline-block;
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                    padding: 8px 24px;
                    border-radius: 50px;
                    margin-bottom: 24px;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                ">
                    <span style="
                        color: #fff;
                        font-size: 13px;
                        font-weight: 600;
                        letter-spacing: 1px;
                        text-transform: uppercase;
                    ">🚀 Coming Soon</span>
                </div>

                <!-- Main heading -->
                <h2 style="
                    color: #fff;
                    font-size: 36px;
                    font-weight: 800;
                    margin: 0 0 16px 0;
                    line-height: 1.2;
                ">
                    {{ $headingName ?? 'This Service' }}
                </h2>

                <!-- Location text -->
                <p style="
                    color: rgba(255, 255, 255, 0.95);
                    font-size: 20px;
                    margin: 0 0 32px 0;
                    font-weight: 500;
                ">
                    in <strong>{{ $locationText ?? 'your area' }}</strong>
                </p>

                <!-- Description -->
                <p style="
                    color: rgba(255, 255, 255, 0.85);
                    font-size: 15px;
                    margin: 0 auto 40px;
                    max-width: 500px;
                    line-height: 1.6;
                ">
                    We're working to bring the best {{ strtolower($headingName ?? 'service') }} providers to {{ $locationText ?? 'your area' }}.
                    Get ready for an amazing experience!
                </p>

                <!-- CTA Buttons -->
                <div style="
                    display: flex;
                    gap: 12px;
                    justify-content: center;
                    flex-wrap: wrap;
                    margin-bottom: 40px;
                ">
                    <a href="{{ route('free.listing') }}" style="
                        display: inline-block;
                        background: #fff;
                        color: #667eea;
                        padding: 14px 32px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-weight: 700;
                        font-size: 15px;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                    "
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0, 0, 0, 0.25)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)';"
                    >
                        List Your Business
                    </a>
                </div>

                <!-- Related categories section -->
                @if(!empty($relatedSubcategories) && $relatedSubcategories->count() > 0)
                    <div style="
                        margin-top: 50px;
                        padding-top: 40px;
                        border-top: 1px solid rgba(255, 255, 255, 0.2);
                    ">
                        <p style="
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 13px;
                            margin-bottom: 20px;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        ">Explore Other Services</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                            @foreach($relatedSubcategories->take(5) as $related)
                                <a href="{{ route('listings.subcategory', ['slug' => $related->slug]) }}"
                                   style="
                                       padding: 10px 18px;
                                       background: rgba(255, 255, 255, 0.15);
                                       color: #fff;
                                       text-decoration: none;
                                       border-radius: 25px;
                                       font-size: 14px;
                                       font-weight: 600;
                                       border: 1px solid rgba(255, 255, 255, 0.3);
                                       transition: all 0.3s ease;
                                   "
                                   onmouseover="this.style.background='rgba(255, 255, 255, 0.25)'; this.style.transform='translateY(-2px)';"
                                   onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(0)';"
                                >
                                    {{ $related->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    @php
        // 1. Fixed top-rank listings
    $fixedListings = $allListings->filter(function ($item) {
        return isset($item->top_rank) && in_array((int) $item->top_rank, [1, 2, 3, 4]);
    })->sortBy(function ($item) {
        return (int) ($item->top_rank ?? 999);
    })->values();

    // 2. Star paid listings (top-rank ke baad)
    $starListings = $allListings->filter(function ($item) {
        return ($item->is_star ?? 0) == 1
            && (!isset($item->top_rank) || !in_array((int) $item->top_rank, [1, 2, 3, 4]));
    })->values();

    // 3. Normal rotating listings
   $rotatingListings = $allListings->filter(function ($item) {
    return ($item->is_star ?? 0) != 1
        && (!isset($item->top_rank) || !in_array((int) $item->top_rank, [1, 2, 3, 4]));
})->values();

    function getTopBadge($rank) {
        if ($rank == 1) return 'P';
        if ($rank == 2) return 'D';
        if ($rank == 3) return 'G';
        if ($rank == 4) return 'S';
        return '';
    }


    function listingAddress($listing)
{
    if (!empty($listing->address)) {
        return $listing->address;
    }

    return collect([
        $listing->plot_no,
        $listing->building_name,
        $listing->street_name,
        $listing->landmark,
        $listing->area,
        $listing->city,
        $listing->state,
        $listing->pincode,
    ])->filter()->implode(', ');
}

@endphp

{{-- ================= FIXED TOP RANK LISTINGS ================= --}}
@forelse($fixedListings as $listing)
    @php
        $phone = '';

        if (is_array($listing->contact)) {
            $phone = $listing->contact['content'][0] ?? '';
        } else {
            $phone = $listing->contact;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $topBadge = getTopBadge($listing->top_rank);

        $whatsappNumber = is_array($listing->whatsapp_numbers)
            ? ($listing->whatsapp_numbers[0] ?? '')
            : $listing->whatsapp_numbers;

        $mobile_numbers = is_array($listing->mobile_numbers)
            ? ($listing->mobile_numbers[0] ?? '')
            : $listing->mobile_numbers;

        $popupCategoryName = $selectedCategory->name ?? request('categorysearch') ?? $listing->category->name ?? 'Listings';
    @endphp

    <div class="col-12 listing-card fixed-listing-card"
       data-location="{{ strtolower(listingAddress($listing)) }}"
         data-name="{{ strtolower($listing->name ?? '') }}"
         data-category="{{ strtolower($listing->category->name ?? '') }}">

     <div class="listing-area position-relative border p-4 rounded shadow-sm listing-box-clickable"
     data-url="{{ route('listings.details', ['category' => optional($listing->category)->slug, 'slug' => $listing->slug]) }}"
     data-track="listing_click" data-listing-id="{{ $listing->id }}" data-category-id="{{ optional($listing->category)->id }}" data-location-id="{{ optional($listing->location)->id ?? $listing->location_id }}">

            @if(!empty($topBadge))
                <div class="jd-top-badge">
                    {{ $topBadge }}
                </div>
            @endif

            <style>
              .listing-img-box {
    width: 100%;
    height: 192px;
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.listing-img-box::before {
    content: "";
    position: absolute;
    inset: 0;
    background: inherit;
    filter: blur(18px);
    transform: scale(1.15);
}

@media (max-width: 767px) {
    .listing-img-box {
        height: 190px;
    }

    .listing-img-box .listing-img {
        height: 190px;
    }
}
.listing-box-clickable {
    cursor: pointer;
}





.gallery-arrow{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    width:32px;
    height:50px;
    border:none;
    background:rgba(0,0,0,0.7);
    color:#fff;
    z-index:999;
    cursor:pointer;
    display:flex !important;
    align-items:center;
    justify-content:center;
    font-size:26px;
    line-height:1;
    opacity:1 !important;
}

.gallery-arrow.prev{
    left:5px;
}

.gallery-arrow.next{
    right:0px;
}

            </style>

            <div style="cursor: pointer;" class="row align-items-center ">
             <div class="col-4 col-md-3">
                    <div class="listing-img-box">
                       <div class="listing-gallery">
    
    <img src="{{ loadImage('assets/images/listing/thumb/' . $listing->thumb) }}"
         class="img-fluid rounded listing-img active"
         alt="{{ $listing->name }}"
           loading="lazy">

    @if(!empty($listing->images))
        @php
            $extraImages = is_array($listing->images)
                ? $listing->images
                : json_decode($listing->images, true);
        @endphp

        @if(is_array($extraImages))
            @foreach($extraImages as $img)
                <img src="{{ loadImage('assets/images/listing/' . $img) }}"
                     class="img-fluid rounded listing-img"
                     alt="{{ $listing->name }}"
                       loading="lazy">
            @endforeach
        @endif
    @endif
@if(!empty($extraImages) && count($extraImages) > 0)
    <button type="button" class="gallery-arrow prev">
        <i class="las la-angle-left"></i>
    </button>

    <button type="button" class="gallery-arrow next">
        <i class="las la-angle-right"></i>
    </button>
@endif
</div>
                    </div>
                </div>

               <div class="col-7 col-md-8">
                    <h4 class="title mb-2 listing-title d-flex align-items-center flex-wrap">
                        @if(($listing->is_star ?? 0) == 1)
                            <i class="fas fa-star ico"></i>
                        @endif
                     <a href="{{ route('listings.details', [
    'category' => optional($listing->category)->slug,
    'slug' => $listing->slug
]) }}">
    {{ __($listing->name) }}
</a>
                    </h4>

                    @php
                        $rating = 0;
                        $ratingCount = 0;

                        if (!empty($listing->review) && is_numeric($listing->review)) {
                            $rating = (float) $listing->review;
                        } elseif (!empty($listing->avg_rating) && is_numeric($listing->avg_rating)) {
                            $rating = (float) $listing->avg_rating;
                        } elseif (!empty($listing->rating) && is_numeric($listing->rating)) {
                            $rating = (float) $listing->rating;
                        }

                        if (!empty($listing->total_review) && is_numeric($listing->total_review)) {
                            $ratingCount = (int) $listing->total_review;
                        } elseif (!empty($listing->review_count) && is_numeric($listing->review_count)) {
                            $ratingCount = (int) $listing->review_count;
                        } elseif (!empty($listing->ratings_count) && is_numeric($listing->ratings_count)) {
                            $ratingCount = (int) $listing->ratings_count;
                        }
                    @endphp

                    @php
                        $hasRatingBlock = $rating > 0
                            || $ratingCount > 0
                            || ($listing->trust ?? 0) == 1
                            || ($listing->verified ?? 0) == 1;
                    @endphp

                    <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap"
                         style="{{ !$hasRatingBlock ? 'visibility:hidden;' : '' }}">

                        @if($rating > 0)
                            <span class="jd-rating-badge">
                                {{ number_format($rating, 1) }} ★
                            </span>
                        @endif

                        @if($ratingCount > 0)
                            <span class="jd-rating-count">
                                {{ $ratingCount }} Ratings
                            </span>
                        @endif

                        @if(($listing->trust ?? 0) == 1)
                            <a href="{{ route('badges.guide') }}" class="badge-link" title="Trusted Business: Verified identity plus additional certifications or high customer ratings">
                                <img src="{{ asset('assets/images/trust_2x.gif') }}" class="jd-badge-img" alt="Trusted Business badge" loading="lazy">
                            </a>
                        @endif

                        @if(($listing->verified ?? 0) == 1)
                            <a href="{{ route('badges.guide') }}" class="badge-link" title="Verified Business: Identity and credentials verified by ServiceBazzar">
                                <img src="{{ asset('assets/images/verified_2x.gif') }}" class="jd-badge-img" alt="Verified Business badge" loading="lazy">
                            </a>
                        @endif
                    </div>

                    @php
                        $locationName = optional($listing->location)->name;
                        $yearsInBusiness = (!empty($listing->established_year))
                            ? date('Y') - (int) $listing->established_year
                            : 0;
                    @endphp

                    <div class="listing-location text-muted d-flex align-items-center flex-wrap gap-2">
                        @if(!empty($locationName))
                            <span>
                                <i class="las la-map-marker"></i> {{ $locationName }}
                            </span>
                        @else
                            <span>&nbsp;</span>
                        @endif

                        @if($yearsInBusiness > 0)
                            <span class="dot-separator">•</span>
                            <span class="business-years">
                                <i class="las la-briefcase"></i> {{ $yearsInBusiness }} Years in Business
                            </span>
                        @else
                            <span>&nbsp;</span>
                        @endif

                        
                    </div>

                    @php
                        $subs = collect($listing->subcategory ?? [])->take(3);
                    @endphp

                    <div class="listing-subcategories mt-2">
                        @if($subs->count())
                            @foreach($subs as $sub)
                                <span class="subcat-chip">{{ $sub->name }}</span>
                            @endforeach
                        @else
                            <span>&nbsp;</span>
                        @endif
                    </div>

                    <div class="listing-contact mt-3 desktop-buttons">
                      @if(!empty($mobile_numbers))

   <a href="tel:{{ preg_replace('/[^0-9+]/', '', $mobile_numbers) }}"
   class="btn-call list-track-click"
   data-type="call"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id }}"
   data-location-id="{{ $listing->location_id }}"
   data-number="{{ $mobile_numbers }}">
    <i class="las la-phone"></i>
    {{ $mobile_numbers }}
</a>

@elseif(!empty($listing->landline_numbers[0]))

    <a href="tel:{{ $listing->landline_numbers[0] }}" class="btn-call">
        <i class="las la-headset"></i>
        {{ $listing->landline_country_code[0] ?? '+91' }}
        {{ $listing->landline_numbers[0] }}
    </a>

@endif

                        @if(!empty($whatsappNumber))
                          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}"
   target="_blank"
   class="btn-whatsapp list-track-click"
   data-type="whatsapp"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
   data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
   data-number="{{ $whatsappNumber }}">
                                <i class="lab la-whatsapp"></i>
                                WhatsApp
                            </a>
                        @endif

                       <button type="button"
        class="btn-best-price open-enquiry-modal list-track-click"
        data-type="enquiry"
        data-listing-id="{{ $listing->id }}"
        data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
        data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
        data-category="{{ $popupCategoryName }}">
                            <i class="las la-comment-alt-dots"></i>
                            Get Best Price
                        </button>
                    </div>
                </div>
            </div>

            <div class="listing-contact mobile-buttons mt-3">
               @if(!empty($mobile_numbers))

 <a href="tel:{{ preg_replace('/[^0-9+]/', '', $mobile_numbers) }}"
   class="btn-call list-track-click"
   data-type="call"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id }}"
   data-location-id="{{ $listing->location_id }}"
   data-number="{{ $mobile_numbers }}">
    <i class="las la-phone"></i>
    {{ $mobile_numbers }}
</a>

@elseif(!empty($listing->landline_numbers[0]))

    <a href="tel:{{ $listing->landline_numbers[0] }}" class="btn-call">
        <i class="las la-headset"></i>
        {{ $listing->landline_numbers[0] }}
    </a>

@endif

              <button type="button"
        class="btn-best-price open-enquiry-modal list-track-click"
        data-type="enquiry"
        data-listing-id="{{ $listing->id }}"
        data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
        data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
        data-category="{{ $popupCategoryName }}">
                    <i class="las la-comment-alt-dots"></i>
                    Get Best Price
                </button>
            </div>
        </div>
    </div>
@empty
@endforelse


{{-- ================= STAR PAID LISTINGS ================= --}}
@foreach($starListings as $listing)
    @php
        $phone = '';

        if (is_array($listing->contact)) {
            $phone = $listing->contact['content'][0] ?? '';
        } else {
            $phone = $listing->contact;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $whatsappNumber = is_array($listing->whatsapp_numbers)
            ? ($listing->whatsapp_numbers[0] ?? '')
            : $listing->whatsapp_numbers;

        $mobile_numbers = is_array($listing->mobile_numbers)
            ? ($listing->mobile_numbers[0] ?? '')
            : $listing->mobile_numbers;

        $popupCategoryName = $selectedCategory->name ?? request('categorysearch') ?? $listing->category->name ?? 'Listings';
    @endphp

    <div class="col-12 listing-card star-paid-card"
        data-location="{{ strtolower(listingAddress($listing)) }}"
         data-name="{{ strtolower($listing->name ?? '') }}"
         data-category="{{ strtolower($listing->category->name ?? '') }}">

      <div class="listing-area position-relative border p-4 rounded shadow-sm listing-box-clickable"
     data-url="{{ route('listings.details', ['category' => optional($listing->category)->slug, 'slug' => $listing->slug]) }}"
     data-track="listing_click" data-listing-id="{{ $listing->id }}" data-category-id="{{ optional($listing->category)->id }}" data-location-id="{{ optional($listing->location)->id ?? $listing->location_id }}">
            <div style="cursor: pointer;" class="row align-items-center ">
       <div class="col-4 col-md-3">
                    <div class="listing-gallery">
    
    <img src="{{ loadImage('assets/images/listing/thumb/' . $listing->thumb) }}"
         class="img-fluid rounded listing-img active"
         alt="{{ $listing->name }}"
         loading="lazy">

    @if(!empty($listing->images))
        @php
            $extraImages = is_array($listing->images)
                ? $listing->images
                : json_decode($listing->images, true);
        @endphp

        @if(is_array($extraImages))
            @foreach($extraImages as $img)
                <img src="{{ loadImage('assets/images/listing/' . $img) }}"
                     class="img-fluid rounded listing-img"
                     alt="{{ $listing->name }}"
                     loading="lazy">
            @endforeach
        @endif
    @endif

 @if(!empty($extraImages) && count($extraImages) > 0)
    <button type="button" class="gallery-arrow prev">
        <i class="las la-angle-left"></i>
    </button>

    <button type="button" class="gallery-arrow next">
        <i class="las la-angle-right"></i>
    </button>
@endif
</div>
                </div>

             <div class="col-7 col-md-8">
                    <h4 class="title mb-2 listing-title d-flex align-items-center flex-wrap">
                        @if(($listing->is_star ?? 0) == 1)
                            <i class="fas fa-star ico"></i>
                        @endif
                       <a href="{{ route('listings.details', [
    'category' => optional($listing->category)->slug,
    'slug' => $listing->slug
]) }}">
                            {{ __($listing->name) }}
                        </a>
                    </h4>

                    @php
                        $rating = 0;
                        $ratingCount = 0;

                        if (!empty($listing->review) && is_numeric($listing->review)) {
                            $rating = (float) $listing->review;
                        } elseif (!empty($listing->avg_rating) && is_numeric($listing->avg_rating)) {
                            $rating = (float) $listing->avg_rating;
                        } elseif (!empty($listing->rating) && is_numeric($listing->rating)) {
                            $rating = (float) $listing->rating;
                        }

                        if (!empty($listing->total_review) && is_numeric($listing->total_review)) {
                            $ratingCount = (int) $listing->total_review;
                        } elseif (!empty($listing->review_count) && is_numeric($listing->review_count)) {
                            $ratingCount = (int) $listing->review_count;
                        } elseif (!empty($listing->ratings_count) && is_numeric($listing->ratings_count)) {
                            $ratingCount = (int) $listing->ratings_count;
                        }
                    @endphp

                    @php
                        $hasRatingBlock = $rating > 0
                            || $ratingCount > 0
                            || ($listing->trust ?? 0) == 1
                            || ($listing->verified ?? 0) == 1;
                    @endphp

                    <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap"
                         style="{{ !$hasRatingBlock ? 'visibility:hidden;' : '' }}">
                        @if($rating > 0)
                            <span class="jd-rating-badge">
                                {{ number_format($rating, 1) }} ★
                            </span>
                        @endif

                        @if($ratingCount > 0)
                            <span class="jd-rating-count">
                                {{ $ratingCount }} Ratings
                            </span>
                        @endif

                        @if(($listing->trust ?? 0) == 1)
                            <a href="{{ route('badges.guide') }}" class="badge-link" title="Trusted Business: Verified identity plus additional certifications or high customer ratings">
                                <img src="{{ asset('assets/images/trust_2x.gif') }}" class="jd-badge-img" alt="Trusted Business badge" loading="lazy">
                            </a>
                        @endif

                        @if(($listing->verified ?? 0) == 1)
                            <a href="{{ route('badges.guide') }}" class="badge-link" title="Verified Business: Identity and credentials verified by ServiceBazzar">
                                <img src="{{ asset('assets/images/verified_2x.gif') }}" class="jd-badge-img" alt="Verified Business badge" loading="lazy">
                            </a>
                        @endif
                    </div>

                    <div class="listing-location text-muted d-flex align-items-center flex-wrap gap-2">
                        <span>
                            <i class="las la-map-marker"></i>
                            {{ optional($listing->location)->name }}
                        </span>

                        @if(!empty($listing->established_year))
                            @php
                                $yearsInBusiness = date('Y') - (int) $listing->established_year;
                            @endphp

                            @if($yearsInBusiness > 0)
                                <span class="dot-separator">•</span>
                                <span class="business-years">
                                    <i class="las la-briefcase"></i>
                                    {{ $yearsInBusiness }} Years in Business
                                </span>
                            @endif
                        @endif

                      


                    </div>

                    @if(isset($listing->subcategory) && $listing->subcategory->count())
                        <div class="listing-subcategories mt-2">
                            @foreach($listing->subcategory->take(3) as $sub)
                                <span class="subcat-chip">{{ $sub->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="listing-contact mt-3 desktop-buttons">
                    @if(!empty($mobile_numbers))

<a href="tel:{{ preg_replace('/[^0-9+]/', '', $mobile_numbers) }}"
   class="btn-call list-track-click"
   data-type="call"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id }}"
   data-location-id="{{ $listing->location_id }}"
   data-number="{{ $mobile_numbers }}">
    <i class="las la-phone"></i>
    {{ $mobile_numbers }}
</a>

@elseif(!empty($listing->landline_numbers[0]))

    <a href="tel:{{ $listing->landline_numbers[0] }}" class="btn-call">
        <i class="las la-headset"></i>
        {{ $listing->landline_country_code[0] ?? '+91' }}
        {{ $listing->landline_numbers[0] }}
    </a>

@endif

                        @if(!empty($whatsappNumber))
                          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}"
   target="_blank"
   class="btn-whatsapp list-track-click"
   data-type="whatsapp"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
   data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
   data-number="{{ $whatsappNumber }}">
                                <i class="lab la-whatsapp"></i>
                                WhatsApp
                            </a>
                        @endif

                     <button type="button"
        class="btn-best-price open-enquiry-modal list-track-click"
        data-type="enquiry"
        data-listing-id="{{ $listing->id }}"
        data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
        data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
        data-category="{{ $popupCategoryName }}">
                            <i class="las la-comment-alt-dots"></i>
                            Get Best Price
                        </button>
                    </div>
                </div>
            </div>

            <div class="listing-contact mobile-buttons mt-3">
              @if(!empty($mobile_numbers))

   <a href="tel:{{ preg_replace('/[^0-9+]/', '', $mobile_numbers) }}"
   class="btn-call list-track-click"
   data-type="call"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id }}"
   data-location-id="{{ $listing->location_id }}"
   data-number="{{ $mobile_numbers }}">
    <i class="las la-phone"></i>
    Call Now
</a>

@elseif(!empty($listing->landline_numbers[0]))

    <a href="tel:{{ $listing->landline_numbers[0] }}" class="btn-call">
        <i class="las la-headset"></i>
        {{ $listing->landline_numbers[0] }}
    </a>

@endif

               <button type="button"
        class="btn-best-price open-enquiry-modal list-track-click"
        data-type="enquiry"
        data-listing-id="{{ $listing->id }}"
        data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
        data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
        data-category="{{ $popupCategoryName }}">
                    <i class="las la-comment-alt-dots"></i>
                    Get Best Price
                </button>
            </div>
        </div>
    </div>
@endforeach
    <style>
        .listing-gallery {
    position: relative !important;
    width: 100% !important;
    height: 190px !important;
    overflow: hidden !important;
    border-radius: 8px !important;
}

.listing-gallery .listing-img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 190px !important;
    object-fit: cover !important;
    display: none !important;
}

.listing-gallery .listing-img.active {
    display: block !important;
}

.gallery-arrow {
    position: absolute !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    z-index: 5 !important;
}

.gallery-arrow.prev {
    border-radius: 0px 5px 5px 0px;
      background: #261713;
    color: white;
    left: 0px !important;
}

.gallery-arrow.next {
    border-radius: 5px 0px 0px 5px;
    background: #261713;
    color: white;
    right: 0px !important;
}
.gallery-arrow.hide-arrow{
    display:none !important;
}
.listing-gallery .listing-img.active{
    display: block !important;
    animation: jdSlide 0.45s ease;
}

@keyframes jdSlide{
    from{
        opacity: 0.7;
        transform: translateX(18px);
    }
    to{
        opacity: 1;
        transform: translateX(0);
    }
}
@media(max-width:767px){
    .listing-gallery,
    .listing-gallery .listing-img {
        height: 125px !important;
    }
}

    </style>

{{-- ================= ROTATING LISTINGS ================= --}}
@if($rotatingListings->count() > 0)
    <div class="col-12 p-0">
        <div id="rotatingListingsWrapper">
            @foreach($rotatingListings as $listing)
                @php
                    $phone = '';

                    if (is_array($listing->contact)) {
                        $phone = $listing->contact['content'][0] ?? '';
                    } else {
                        $phone = $listing->contact;
                    }

                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

                    $whatsappNumber = is_array($listing->whatsapp_numbers)
                        ? ($listing->whatsapp_numbers[0] ?? '')
                        : $listing->whatsapp_numbers;

                    $mobile_numbers = is_array($listing->mobile_numbers)
                        ? ($listing->mobile_numbers[0] ?? '')
                        : $listing->mobile_numbers;

                    $popupCategoryName = $selectedCategory->name ?? request('categorysearch') ?? $listing->category->name ?? 'Listings';
                @endphp

           <div class="col-12 listing-card rotating-card"
     data-id="{{ $listing->id }}"
     data-distance="{{ isset($listing->distance) ? (float) $listing->distance : 999999 }}"
    data-location="{{ strtolower(listingAddress($listing)) }}"
     data-name="{{ strtolower($listing->name ?? '') }}"
     data-category="{{ strtolower($listing->category->name ?? '') }}">

               @php
    $categorySlug = optional($listing->category)->slug;
    $listingSlug = $listing->slug ?? '';
    $detailUrl = (!empty($categorySlug) && !empty($listingSlug))
        ? route('listings.details', [
            'category' => $categorySlug,
            'slug' => $listingSlug
        ])
        : '';
@endphp

<div class="listing-area position-relative border p-4 rounded shadow-sm listing-box-clickable"
     data-url="{{ $detailUrl }}">  
                        <div style="cursor: pointer;" class="row align-items-center ">
                          <div class="col-4 col-md-3">
                              <div class="listing-gallery">

  
    <img src="{{ loadImage('assets/images/listing/thumb/' . $listing->thumb) }}"
         class="img-fluid rounded listing-img active"
         alt="{{ $listing->name }}"
         loading="lazy">

   
    @if(!empty($listing->photos))
        @php
            $extraImages = is_array($listing->photos)
                ? $listing->photos
                : json_decode($listing->photos, true);
        @endphp

        @if(is_array($extraImages))
            @foreach($extraImages as $img)
                <img src="{{ asset('assets/images/listing/photos/' . $img) }}"
                     class="img-fluid rounded listing-img"
                     alt="{{ $listing->name }}"
                      loading="lazy">
            @endforeach
        @endif
    @endif

    @if(!empty($listing->photos) && count($extraImages ?? []) > 0)
        <button type="button" class="gallery-arrow prev"><i class="las la-angle-left"></i></button>
        <button type="button" class="gallery-arrow next">   <i class="las la-angle-right"></i></button>
    @endif

</div>
                            </div>

                           <div class="col-7 col-md-8">
                                <h4 class="title mb-2 listing-title d-flex align-items-center flex-wrap">
                                    @if(($listing->is_star ?? 0) == 1)
                                         <i class="fas fa-star ico"></i>
                                    @endif
                               @php
    $categorySlug = optional($listing->category)->slug;
    $listingSlug = $listing->slug ?? '';
    $detailUrl = (!empty($categorySlug) && !empty($listingSlug))
        ? route('listings.details', [
            'category' => $categorySlug,
            'slug' => $listingSlug
        ])
        : '';
@endphp

@if(!empty($detailUrl))
    <a href="{{ $detailUrl }}">
        {{ __($listing->name) }}
    </a>
@else
    <span>{{ __($listing->name) }}</span>
@endif
                                </h4>

                                @php
                                    $rating = 0;
                                    $ratingCount = 0;

                                    if (!empty($listing->review) && is_numeric($listing->review)) {
                                        $rating = (float) $listing->review;
                                    } elseif (!empty($listing->avg_rating) && is_numeric($listing->avg_rating)) {
                                        $rating = (float) $listing->avg_rating;
                                    } elseif (!empty($listing->rating) && is_numeric($listing->rating)) {
                                        $rating = (float) $listing->rating;
                                    }

                                    if (!empty($listing->total_review) && is_numeric($listing->total_review)) {
                                        $ratingCount = (int) $listing->total_review;
                                    } elseif (!empty($listing->review_count) && is_numeric($listing->review_count)) {
                                        $ratingCount = (int) $listing->review_count;
                                    } elseif (!empty($listing->ratings_count) && is_numeric($listing->ratings_count)) {
                                        $ratingCount = (int) $listing->ratings_count;
                                    }
                                @endphp

                                @php
                                    $hasRatingBlock = $rating > 0
                                        || $ratingCount > 0
                                        || ($listing->trust ?? 0) == 1
                                        || ($listing->verified ?? 0) == 1;
                                @endphp

                                <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap">
                                    @if($hasRatingBlock)
                                        @if($rating > 0)
                                            <span class="jd-rating-badge">
                                                {{ number_format($rating, 1) }} ★
                                            </span>
                                        @endif

                                        @if($ratingCount > 0)
                                            <span class="jd-rating-count">
                                                {{ $ratingCount }} Ratings
                                            </span>
                                        @endif

                                        @if(($listing->trust ?? 0) == 1)
                                            <img src="{{ asset('assets/images/trust_2x.gif') }}" class="jd-badge-img" alt="Trusted Business badge" loading="lazy">
                                        @endif

                                        @if(($listing->verified ?? 0) == 1)
                                            <img src="{{ asset('assets/images/verified_2x.gif') }}" class="jd-badge-img" alt="Verified Business badge" loading="lazy">
                                        @endif
                                    @endif
                                </div>

                                <div class="listing-location text-muted d-flex align-items-center flex-wrap gap-2">
                             {{-- @if(isset($listing->distance) && $listing->distance !== null)
    <span class="dot-separator">•</span>
    <span class="text-muted">
        @if((float) $listing->distance < 0.5)
            Very close
        @elseif((float) $listing->distance < 2)
            Nearby
        @else
            {{ number_format((float) $listing->distance, 1) }} km away
        @endif
    </span>
@endif --}}
                                  <span>
    <i class="las la-map-marker"></i>

    {{ collect([
       
      
        $listing->area,
        optional($listing->location)->name
    ])->filter()->implode(', ') }}

</span>
                                    @if(!empty($listing->established_year))
                                        @php
                                            $yearsInBusiness = date('Y') - (int) $listing->established_year;
                                        @endphp

                                        @if($yearsInBusiness > 0)
                                            <span class="dot-separator">•</span>
                                            <span class="business-years">
                                                <i class="las la-briefcase"></i>
                                                {{ $yearsInBusiness }} Years in Business
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                @if(isset($listing->subcategory) && $listing->subcategory->count())
                                    <div class="listing-subcategories mt-2">
                                        @foreach($listing->subcategory->take(3) as $sub)
                                            <span class="subcat-chip">{{ $sub->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="listing-contact mt-3 desktop-buttons">
                                  @if(!empty($mobile_numbers))

  <a href="tel:{{ preg_replace('/[^0-9+]/', '', $mobile_numbers) }}"
   class="btn-call list-track-click"
   data-type="call"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id }}"
   data-location-id="{{ $listing->location_id }}"
   data-number="{{ $mobile_numbers }}">
    <i class="las la-phone"></i>
    {{ $mobile_numbers }}
</a>

@elseif(!empty($listing->landline_numbers[0]))

    <a href="tel:{{ $listing->landline_numbers[0] }}" class="btn-call">
        <i class="las la-headset"></i>
        {{ $listing->landline_country_code[0] ?? '+91' }}
        {{ $listing->landline_numbers[0] }}
    </a>

@endif

                                    @if(!empty($whatsappNumber))
                                      <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}"
   target="_blank"
   class="btn-whatsapp list-track-click"
   data-type="whatsapp"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
   data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
   data-number="{{ $whatsappNumber }}">
                                            <i class="lab la-whatsapp"></i>
                                            WhatsApp
                                        </a>
                                    @endif
<button type="button"
        class="btn-best-price open-enquiry-modal list-track-click"
        data-type="enquiry"
        data-listing-id="{{ $listing->id }}"
        data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
        data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
        data-category="{{ $popupCategoryName }}">
                                        <i class="las la-comment-alt-dots"></i>
                                        Get Best Price
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="listing-contact mobile-buttons mt-3">
                           @if(!empty($mobile_numbers))

   <a href="tel:{{ preg_replace('/[^0-9+]/', '', $mobile_numbers) }}"
   class="btn-call list-track-click"
   data-type="call"
   data-listing-id="{{ $listing->id }}"
   data-category-id="{{ $listing->category_id }}"
   data-location-id="{{ $listing->location_id }}"
   data-number="{{ $mobile_numbers }}">
    <i class="las la-phone"></i>
    Call Now
</a>

@elseif(!empty($listing->landline_numbers[0]))

    <a href="tel:{{ $listing->landline_numbers[0] }}" class="btn-call">
        <i class="las la-headset"></i>
        {{ $listing->landline_numbers[0] }}
    </a>

@endif

                        <button type="button"
        class="btn-best-price open-enquiry-modal list-track-click"
        data-type="enquiry"
        data-listing-id="{{ $listing->id }}"
        data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
        data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
        data-category="{{ $popupCategoryName }}">
                                <i class="las la-comment-alt-dots"></i>
                                Get Best Price
                            </button>
                        </div>
                    </div>
                </div>

                @if(($loop->iteration % 4 == 0) && !$loop->last)
                    <div class="col-12 pb-4 rotating-card enquiry-rotating-card">
                        <section class="py-4 enquiry-box" style="background:#eaf3fb;margin-right: 10px;">
                            <div class="container">
                                <div class="rounded-3 overflow-hidden">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-lg-4 p-4">
                                            <h4 class="fw-bold mb-1">
                                                Get the List of Top
                                                <span class="text-primary">
                                                    {{ $selectedCategory->name ?? request('categorysearch') ?? 'Listings' }}
                                                </span>
                                            </h4>
                                            <p class="mb-0 small text-dark">
                                                We'll send you contact details in seconds for free
                                            </p>
                                        </div>

                                        <div class="col-lg-8 p-5 bg-white border-start">
                                            <form action="{{ route('contact.author') }}" method="POST">
                                                @csrf
                                                @include('themes.servicebazzar.partials.form-guard')

                                                <input type="hidden" name="category_id" value="{{ $selectedCategory->id ?? '' }}">
                                                <input type="hidden" name="category_name" value="{{ $selectedCategory->name ?? request('categorysearch') }}">
                                                <input type="hidden" name="location_name" value="{{ request('location_name') }}">
                                                 <input type="hidden" name="location_id"
       value="{{ request('location_id') ?? ($location->id ?? '') }}">

                                                <div style="cursor: pointer;" class="row align-items-center ">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control form-control-sm" name="name" placeholder="Name">
                                                    </div>

                                                    <div class="col-md-5 mobile-input-fix" style="margin-left: -25px;">
                                                        <input name="phone" type="text" class="form-control form-control-sm" placeholder="Mobile Number">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <button type="submit" class="btn btn-primary btn-sm send-enquiry-btn">
                                                            Send Enquiry
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" checked>
                                                <label class="form-check-label small">
                                                    I Agree to
                                                    <a href="{{ route('single.slug',38) }}" class="text-decoration-none">T&amp;C's Privacy Policy</a>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Close the empty-state if/else block --}}
@endif



<div id="bestPriceModal" class="best-price-modal">
    <div class="best-price-modal-dialog">
        <button type="button" class="best-price-close" id="closeBestPriceModal">&times;</button>

        <div class="best-price-modal-content">
            <div class="best-price-left">
                <h2 class="best-price-title">
                    Get the list of best <span id="popupCategoryName">Listings</span>
                </h2>

                <form action="{{ route('contact.author') }}" method="POST">
                    @csrf
                    @include('themes.servicebazzar.partials.form-guard')

                    {{-- Filled by the opener below from the card whose "Get Best
                         Price" was clicked. Without it every enquiry from this
                         shared popup reached the admin with no business against
                         it, so nobody could tell which listing it was for. --}}
                    <input type="hidden" name="listing_id" id="popupListingInput" value="">

                    <input type="hidden" name="category_id" id="popupCategoryIdInput" value="{{ $selectedCategory->id ?? '' }}" data-page-value="{{ $selectedCategory->id ?? '' }}">
                    <input type="hidden" name="category_name" id="popupCategoryInput" value="{{ $selectedCategory->name ?? request('categorysearch') ?? 'Listings' }}">
                    <input type="hidden" name="location_name" value="{{ request('location_name') }}">
                     <input type="hidden" name="location_id" id="popupLocationIdInput"
       value="{{ request('location_id') ?? ($location->id ?? '') }}"
       data-page-value="{{ request('location_id') ?? ($location->id ?? '') }}">

                    <div class="best-price-field">
                        <label>Name*</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="best-price-field">
                        <label>Mobile Number*</label>
                        <input type="text" name="phone" required>
                    </div>

                    <div class="best-price-check">
                        <input type="checkbox" id="agreePolicy" checked>
                        <label for="agreePolicy">
                            I Agree to Terms and Conditions
                            <a href="{{ route('single.slug',38) }}">T&amp;C's Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="best-price-submit">
                        GET BEST PRICE
                    </button>
                </form>

                {{-- One line, not three bullets. This sits below the button,
                     where it is reassurance for someone already deciding —
                     three separate claims there only made the box taller than
                     the screen. --}}
                <p class="best-price-points">
                    Businesses compete for your order — details come by SMS/Email.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 767px) {
    .listing-subcategories {
        display: none !important;
    }
}
.dot-separator {
    margin: 0 6px;
    font-weight: bold;
    color: #555;
}

.business-years {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    color: #000000;
    font-weight: 400;
}

.jd-rating-wrap {
    gap: 10px;
    /* Reserve the badge row's height even when a listing has no rating/
       trust/verified badge to show, so cards without one don't shrink and
       misalign with cards that have one. Used to be a hidden "dummy" text
       node for this — an SEO audit flagged that as hidden-keyword-stuffing
       risk (Google can see it in source even though visibility:hidden). An
       empty div with min-height does the same job without any text. */
    min-height: 10px;
}

.jd-rating-badge {
    background: #1bbf73;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
}

.jd-rating-count {
    font-size: 13px;
    color: #555;
}

.jd-badge-img {
    height: 20px;
    object-fit: contain;
}

.listing-subcategories {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.subcat-chip {
    display: inline-block;
    padding: 4px 10px;
    font-size: 13px;
    line-height: 1.2;
    color: #333;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-weight: 500;
}

.listing-contact {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.listing-contact a,
.listing-contact button {
    flex: 1;
    justify-content: center;
    white-space: nowrap;
}

.btn-call {
    background: #138808;
    color: #fff;
    padding: 8px 18px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s ease;
    border: none;
}

.btn-whatsapp {
    background: #f8f9fa;
    border: 1px solid #cfcfcf;
    color: #333;
    padding: 8px 18px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s ease;
}

.btn-whatsapp i {
    color: #25D366;
    font-size: 18px;
}

.btn-best-price {
    background: #0d6efd;
    color: #fff;
    padding: 8px 18px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-call:hover {
    background: #138808 !important;
    color: #fff !important;
    text-decoration: none !important;
}

.btn-whatsapp:hover {
    background: #f8f9fa !important;
    color: #333 !important;
    text-decoration: none !important;
}

.btn-best-price:hover {
    background: #0b5ed7;
    color: #fff;
}

.listing-img {
    width: 100%;
    height: 190px;
    object-fit: cover;
    display: block;
    border-radius: 8px;
}

.mobile-buttons {
    display: none;
}

.jd-top-badge {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 26px;
    height: 26px;
    padding: 0 8px;
    background: #000;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 8px 0 8px;
    z-index: 5;
}

#rotatingListingsWrapper {
    display: block;
}

#rotatingListingsWrapper .rotating-card {
    transition: transform 0.5s ease, opacity 0.5s ease;
}

/*
 * Popup — sized to the two fields it actually asks for. It used to open a
 * 1000px, half-screen-tall panel with a promo image taking up almost half of
 * it, which reads as an advertisement rather than a quick question. Kept
 * identical to the copy on the listing details page.
 */
.best-price-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
    padding: 20px;
    overflow-y: auto;
}

.best-price-modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.best-price-modal-dialog {
    width: 100%;
    max-width: 380px;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 12px 40px rgba(0,0,0,0.22);
}

.best-price-close {
    position: absolute;
    top: 6px;
    right: 12px;
    border: none;
    background: transparent;
    font-size: 26px;
    line-height: 1;
    cursor: pointer;
    z-index: 99;
    color: #888;
}

.best-price-modal-content {
    display: block;
}

.best-price-left {
    width: 100%;
    padding: 24px 22px 20px;
}

.best-price-title {
    font-size: 18px;
    font-weight: 700;
    color: #111;
    margin-bottom: 16px;
    padding-right: 22px;
    line-height: 1.35;
}

.best-price-title span {
    color: #0d6efd;
}

.best-price-field {
    margin-bottom: 12px;
}

.best-price-field label {
    display: block;
    font-size: 13px;
    margin-bottom: 5px;
    color: #666;
    font-weight: 500;
}

.best-price-field input {
    width: 100%;
    height: 42px;
    border: 1px solid #cfcfcf;
    border-radius: 6px;
    padding: 0 12px;
    font-size: 15px;
    outline: none;
}

.best-price-field input:focus {
    border-color: #0d6efd;
}

.best-price-check {
    display: flex;
    gap: 7px;
    align-items: flex-start;
    margin: 4px 0 14px;
    font-size: 12px;
    line-height: 1.4;
    color: #777;
}

.best-price-check a {
    color: #777;
    text-decoration: underline;
}

.best-price-submit {
    background: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 6px;
    width: 100%;
    height: 44px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.best-price-points {
    margin: 12px 0 0;
    font-size: 12px;
    line-height: 1.5;
    color: #888;
    text-align: center;
}

@media (max-width: 767.98px) {
    .enquiry-box {
        margin-right: 0 !important;
    }

    .enquiry-box .col-lg-4 {
        padding: 20px !important;
    }

    .enquiry-box .col-lg-8 {
        padding: 20px !important;
        border-left: none !important;
        border-top: 1px solid #dee2e6;
    }

    .enquiry-box .row.align-items-center {
        row-gap: 10px;
    }

    .enquiry-box .row.align-items-center > div {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
    }

    .enquiry-box .mobile-input-fix {
        margin-left: 0 !important;
    }

    .enquiry-box .form-control.form-control-sm {
        width: 100%;
        height: 40px;
        font-size: 14px;
    }

    .enquiry-box .send-enquiry-btn {
        width: 140px;
        height: 36px;
        font-size: 12px;
        padding: 4px 10px;
    }

    .enquiry-box .form-check {
        margin-top: 10px !important;
    }
}

@media (max-width: 767px) {
    .jd-rating-badge {
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .jd-rating-count {
        font-size: 11px;
        color: #666;
    }

    .jd-badge-img {
        height: 14px;
    }

    .subcat-chip {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 5px;
        background: #f5f5f5;
        border: 1px solid #e2e2e2;
        color: #444;
        font-weight: 500;
    }

    .listing-area {
        padding: 12px !important;
        border-radius: 10px;
        background: #fff;
    }

    .listing-card {
        margin-bottom: 12px;
    }

    /* 🔥 ROW FIX */
    .listing-card .listing-area .row {
        display: flex;
        flex-direction: row !important;
        align-items: flex-start !important;
        flex-wrap: nowrap !important;
        margin: 0 !important;
    }

    /* 🔥 REMOVE BOOTSTRAP GAP */
    .listing-card .listing-area .row > div {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
    }

   /* MOBILE IMAGE COL-4 */
/* 🔥 MOBILE IMAGE BIGGER */
.listing-card .listing-area .row > .col-3,
.listing-card .listing-area .row > .col-4,
.listing-card .listing-area .row > .col-md-3 {
    flex: 0 0 38% !important;   /* 👈 image bada */
    width: 38% !important;
    max-width: 38% !important;
    padding-right: 8px !important;
}

/* 🔥 DETAILS AUTO ADJUST */
.listing-card .listing-area .row > .col-8,
.listing-card .listing-area .row > .col-7,
.listing-card .listing-area .row > .col-md-8 {
    flex: 0 0 62% !important;
    width: 62% !important;
    max-width: 62% !important;
}
/* MOBILE IMAGE SIZE */
.listing-img-box,
.listing-img {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    height: 125px !important;
    min-height: 125px !important;
    max-height: 125px !important;
    border-radius: 6px !important;
    overflow: hidden;
    object-fit: cover !important;
}

    .listing-img-box::before {
        display: none !important;
    }

    .listing-img {
        object-fit: cover;
        object-position: center;
        display: block;
    }

    .listing-title {
        font-size: 15px !important;
        line-height: 1.3;
        font-weight: 700;
        margin: 0 0 5px 0 !important;
    }

    .listing-title a {
        color: #222 !important;
        text-decoration: none;
    }

    .jd-rating-wrap {
        gap: 5px;
        margin-bottom: 5px !important;
    }

    .listing-location {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 3px;
        font-size: 12px;
        line-height: 1.4;
        color: #666 !important;
        margin-bottom: 5px !important;
    }

    .business-years {
        font-size: 12px;
        color: #111;
    }

    .dot-separator {
        margin: 0 2px;
    }

    .listing-subcategories {
        gap: 5px;
        margin-top: 3px;
        margin-bottom: 0;
    }

    .desktop-buttons {
        display: none !important;
    }

    .mobile-buttons {
        display: flex !important;
        gap: 8px;
        flex-wrap: nowrap;
        margin-top: 8px !important;
    }

    .mobile-buttons .btn-whatsapp {
        display: none !important;
    }

    .mobile-buttons .btn-call,
    .mobile-buttons .btn-best-price {
        flex: 1;
        min-width: 0;
        height: 38px;
        padding: 0 10px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 13px !important;
        font-weight: 600;
        border-radius: 6px;
        white-space: nowrap;
    }

    .mobile-buttons .btn-call i,
    .mobile-buttons .btn-best-price i {
        font-size: 13px;
        margin-right: 5px;
    }

    .best-price-modal {
        padding: 10px;
    }

    .best-price-modal-dialog {
        max-width: 340px;
    }

    .best-price-left {
        padding: 20px 16px 16px;
    }

    .best-price-title {
        font-size: 17px;
    }
}
.listing-star-icon {
    width: 18px;
    height: 18px;
    object-fit: contain;
    margin-left: 6px;
    vertical-align: middle;
}

</style>

<script>
/*
 * Opening the "Get best price" popup.
 *
 * Delegated on document rather than bound to each button: cards loaded later
 * by infinite scroll are never individually re-bound. Registered here at parse
 * time instead of on DOMContentLoaded, and every element looked up inside the
 * handler, so a visitor who clicks while the rest of the page is still loading
 * still gets a popup that knows which listing they clicked.
 */
document.addEventListener('click', function (e) {
    const button = e.target.closest('.open-enquiry-modal');

    if (!button) {
        return;
    }

    const modal = document.getElementById('bestPriceModal');

    if (!modal) {
        return;
    }

    const categoryText = document.getElementById('popupCategoryName');
    const categoryInput = document.getElementById('popupCategoryInput');
    const listingInput = document.getElementById('popupListingInput');
    const categoryIdInput = document.getElementById('popupCategoryIdInput');
    const locationIdInput = document.getElementById('popupLocationIdInput');

    const category = button.getAttribute('data-category') || 'Listings';

    if (categoryText) categoryText.textContent = category;
    if (categoryInput) categoryInput.value = category;

    // Which business the visitor was actually looking at when they asked.
    // Every one of these buttons carries its card's ids (data-listing-id and
    // friends); the popup used to read only the category off them, so the lead
    // reached the admin with no listing against it.
    if (listingInput) {
        listingInput.value = button.getAttribute('data-listing-id') || '';
    }

    // `data-page-value` and not the input's current value: a previous click
    // has already overwritten that.
    if (categoryIdInput) {
        categoryIdInput.value = button.getAttribute('data-category-id')
            || categoryIdInput.getAttribute('data-page-value') || '';
    }

    if (locationIdInput) {
        locationIdInput.value = button.getAttribute('data-location-id')
            || locationIdInput.getAttribute('data-page-value') || '';
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
});

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('bestPriceModal');
    const closeBtn = document.getElementById('closeBestPriceModal');

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('rotatingListingsWrapper');
    if (!wrapper) return;

    let cards = Array.from(wrapper.querySelectorAll('.rotating-card'))
        .filter(card => !card.classList.contains('enquiry-rotating-card'));

    if (cards.length <= 1) return;

    function seededShuffle(array, seed) {
        let arr = [...array];

        function random() {
            seed = (seed * 9301 + 49297) % 233280;
            return seed / 233280;
        }

        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }

        return arr;
    }

    function getDistanceGroup(card) {
        let distance = parseFloat(card.dataset.distance || '999999');

        if (isNaN(distance)) {
            distance = 999999;
        }

        // Same KM group ke andar hi rotation
        // 1.2, 1.6 = 1 KM group
        // 3.1, 3.8 = 3 KM group
        return Math.floor(distance);
    }

    const seed = Date.now() + Math.floor(Math.random() * 10000);

    let groupedCards = {};

    cards.forEach(card => {
        const group = getDistanceGroup(card);

        if (!groupedCards[group]) {
            groupedCards[group] = [];
        }

        groupedCards[group].push(card);
    });

    let finalCards = [];

    Object.keys(groupedCards)
        .sort((a, b) => Number(a) - Number(b))
        .forEach(group => {
            const shuffledGroup = seededShuffle(groupedCards[group], seed + Number(group));
            finalCards = finalCards.concat(shuffledGroup);
        });

    wrapper.innerHTML = '';

    finalCards.forEach((card, index) => {
        wrapper.appendChild(card);

        if ((index + 1) % 4 === 0 && index !== finalCards.length - 1) {
            const enquiryBox = document.createElement('div');
            enquiryBox.className = 'col-12 pb-4 rotating-card enquiry-rotating-card';
            enquiryBox.innerHTML = `
                <section class="py-4 enquiry-box" style="background:#eaf3fb;margin-right:10px;">
                    <div class="container">
                        <div class="rounded-3 overflow-hidden">
                            <div class="row g-0 align-items-center">
                                <div class="col-lg-4 p-4">
                                    <h4 class="fw-bold mb-1">
                                        Get the List of Top
                                        <span class="text-primary">
                                            {{ $selectedCategory->name ?? request('categorysearch') ?? 'Listings' }}
                                        </span>
                                    </h4>
                                    <p class="mb-0 small text-dark">
                                        We'll send you contact details in seconds for free
                                    </p>
                                </div>
                                <div class="col-lg-8 p-5 bg-white border-start">
                                    <form action="{{ route('contact.author') }}" method="POST">
                                        @csrf
                                        @include('themes.servicebazzar.partials.form-guard')
                                        <input type="hidden" name="category_id" value="{{ $selectedCategory->id ?? '' }}">
                                        <input type="hidden" name="category_name" value="{{ $selectedCategory->name ?? request('categorysearch') }}">
                                        <input type="hidden" name="location_name" value="{{ request('location_name') }}">
                                         <input type="hidden" name="location_id"
       value="{{ request('location_id') ?? ($location->id ?? '') }}">
                                        <div style="cursor: pointer;" class="row align-items-center ">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control form-control-sm" name="name" placeholder="Name">
                                            </div>
                                            <div class="col-md-5 mobile-input-fix" style="margin-left:-25px;">
                                                <input name="phone" type="text" class="form-control form-control-sm" placeholder="Mobile Number">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary btn-sm send-enquiry-btn">
                                                    Send Enquiry
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" checked>
                                        <label class="form-check-label small">
                                            I Agree to
                                            <a href="{{ route('single.slug',38) }}" class="text-decoration-none">T&amp;C's Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            `;
            wrapper.appendChild(enquiryBox);
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delegated for the same reason as the enquiry-modal binding above —
    // cards appended by infinite scroll need this without being re-bound.
    document.addEventListener('click', function (e) {
        const card = e.target.closest('.listing-box-clickable');
        if (!card) {
            return;
        }

        if (e.target.closest('a, button, input, textarea, select, label, form')) {
            return;
        }

        const url = card.getAttribute('data-url');
        if (url) {
            window.location.href = url;
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Arrows hidden up front for every gallery already on the page —
    // galleries appended later by infinite scroll get this from
    // initGalleryArrowVisibility(), called on each freshly-inserted batch
    // (see the loader script further down).
    function initGalleryArrowVisibility(root) {
        (root || document).querySelectorAll('.listing-gallery').forEach(function (gallery) {
            let images = gallery.querySelectorAll('.listing-img');
            let prev = gallery.querySelector('.prev');
            let next = gallery.querySelector('.next');

            if (!prev || !next) return;

            if (images.length <= 1) {
                prev.style.display = 'none';
                next.style.display = 'none';
                return;
            }

            prev.classList.toggle('hide-arrow', images[0].classList.contains('active'));
        });
    }

    window.initGalleryArrowVisibility = initGalleryArrowVisibility;
    initGalleryArrowVisibility();

    /*
     * Delegated (not bound per-gallery) so cards appended by infinite
     * scroll work without being individually re-bound. Slide state (which
     * image is active, whether a transition is mid-flight) is read off the
     * DOM each click instead of a per-gallery closure variable, since a
     * delegated handler has no closure of its own to keep it in.
     */
    document.addEventListener('click', function (e) {
        const arrow = e.target.closest('.gallery-arrow');
        if (!arrow) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        if (arrow.dataset.busy === '1') {
            return;
        }

        const gallery = arrow.closest('.listing-gallery');
        if (!gallery) {
            return;
        }

        const images = Array.from(gallery.querySelectorAll('.listing-img'));
        const prev = gallery.querySelector('.prev');
        const next = gallery.querySelector('.next');
        const index = images.findIndex(function (img) { return img.classList.contains('active'); });

        if (index === -1) {
            return;
        }

        const newIndex = arrow.classList.contains('next') ? index + 1 : index - 1;
        if (newIndex < 0 || newIndex >= images.length) {
            return;
        }

        if (prev) prev.dataset.busy = '1';
        if (next) next.dataset.busy = '1';

        images[index].classList.remove('active');
        images[newIndex].classList.add('active');

        if (prev) prev.classList.toggle('hide-arrow', newIndex === 0);
        if (next) next.classList.toggle('hide-arrow', newIndex === images.length - 1);

        setTimeout(function () {
            if (prev) prev.dataset.busy = '0';
            if (next) next.dataset.busy = '0';
        }, 450);
    });

});
</script>
<script>
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".list-track-click");
    if (!btn) return;

    e.stopPropagation();

    const href = btn.getAttribute("href");
    const target = btn.getAttribute("target");

    fetch("{{ route('listing.click.track') }}", {
        method: "POST",
        keepalive: true,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            listing_id: btn.dataset.listingId || '',
            category_id: btn.dataset.categoryId || '',
            location_id: btn.dataset.locationId || '',
            type: btn.dataset.type || '',
            clicked_number: btn.dataset.number || ''
        })
    }).catch(function(error) {
        console.log('Click track error:', error);
    });

    if (href && href.startsWith("tel:")) {
        return true;
    }

    if (href) {
        e.preventDefault();

        if (target === "_blank") {
            window.open(href, "_blank");
        } else {
            window.location.href = href;
        }
    }
});
</script>