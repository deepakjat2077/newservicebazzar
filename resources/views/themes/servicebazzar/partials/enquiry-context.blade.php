{{-- The hidden fields a "Send Enquiry" form carries when it is not about one
     business. Every form used to spell these out itself, from variables that
     only some of the routes define, so the category or the city quietly went
     out empty and the lead landed in the admin as N/A.

     The controller states the page's own category and city through
     App\Support\EnquiryContext; this only prints them. The build() fallback is
     for a page that hasn't been given one yet, so nothing renders blank.

     Pass ['ectxIds' => true] to get the ids the "Get Best Price" popup's
     script writes into. --}}
@php
    $ectx = $enquiryContext ?? \App\Support\EnquiryContext::build(
        $selectedCategory ?? null,
        $selectedSubcategory ?? null,
        $location ?? null,
        request('categorysearch')
    );
    $ectxIds = $ectxIds ?? false;
@endphp
<input type="hidden" name="category_id" value="{{ $ectx['category_id'] }}"
    @if($ectxIds) id="popupCategoryIdInput" data-page-value="{{ $ectx['category_id'] }}" @endif>
<input type="hidden" name="category_name" value="{{ $ectx['category_name'] }}"
    @if($ectxIds) id="popupCategoryInput" @endif>
<input type="hidden" name="location_id" value="{{ $ectx['location_id'] }}"
    @if($ectxIds) id="popupLocationIdInput" data-page-value="{{ $ectx['location_id'] }}" @endif>
<input type="hidden" name="location_name" value="{{ $ectx['location_name'] }}">
{{-- What the visitor typed, when the search matched no category of ours.
     Without it the lead arrives with nothing at all to call them about. --}}
@if($ectx['searched'] !== '' && !$ectx['category_id'])
    <input type="hidden" name="searched" value="{{ $ectx['searched'] }}">
@endif
