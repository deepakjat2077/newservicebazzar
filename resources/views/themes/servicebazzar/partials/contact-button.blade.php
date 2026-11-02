@php
    /*
     * The call button on a listing card.
     *
     * Printing the number here gave the business its best click for free and
     * left us nothing: the visitor dialled from their own phone and no lead
     * was ever recorded. The button now says "Contact", and the popup behind
     * it takes a mobile number, verifies it and puts the call through.
     *
     * config('contact_call.enabled') is read on every render, so switching
     * CONTACT_CALL off in .env puts the plain number back on every card
     * without a deploy.
     *
     * @see partials/contact-call.blade.php — the popup and its JavaScript
     */
    $ccMobile   = $mobile_numbers ?? null;
    $ccLandline = $listing->landline_numbers[0] ?? null;
    $ccNumber   = !empty($ccMobile) ? $ccMobile : $ccLandline;

    // Same "Harmada, Jaipur" the card itself shows, so the popup names the
    // business the visitor was looking at rather than any business.
    $ccArea = collect([$listing->area ?? null, optional($listing->location)->name])
        ->filter()->implode(', ');
@endphp

@if(!empty($ccNumber))
    @if(config('contact_call.enabled'))
        {{-- No data-number and no tel: href on purpose — the number must not
             be in the page source either, or it is one "view source" away. --}}
        <button type="button"
            class="btn-call sb-contact-call list-track-click"
            data-type="call"
            data-listing-id="{{ $listing->id }}"
            data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
            data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
            data-listing-name="{{ $listing->name }}"
            data-listing-area="{{ $ccArea }}">
            <i class="las la-phone"></i>
            Contact
        </button>
    @elseif(!empty($ccMobile))
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $ccMobile) }}"
            class="btn-call list-track-click"
            data-type="call"
            data-listing-id="{{ $listing->id }}"
            data-category-id="{{ $listing->category_id }}"
            data-location-id="{{ $listing->location_id }}"
            data-number="{{ $ccMobile }}">
            <i class="las la-phone"></i>
            {{ $ccMobile }}
        </a>
    @else
        <a href="tel:{{ $ccLandline }}" class="btn-call">
            <i class="las la-headset"></i>
            {{ $listing->landline_country_code[0] ?? '+91' }}
            {{ $ccLandline }}
        </a>
    @endif
@endif
