@extends('admin.layouts.master')
@section('title','Edit Listing')

@section('content')
@php
    $selectedUserJson = null;

    if (!empty($listing->user)) {
        $selectedUserJson = [
            'id' => old('user_id', $listing->user->id),
            'name' => trim(($listing->user->first_name ?? '') . ' ' . ($listing->user->last_name ?? '')),
            'username' => $listing->user->username ?? '',
            'email' => $listing->user->email ?? '',
            'mobile' => $listing->user->mobile ?? '',
        ];
    }
@endphp

@php

   

    $categoriesJson = $categories->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name ?? '',
        ];
    })->values();

    $subcategoriesJson = $subcategories->map(function ($s) {
        return [
            'id' => $s->id,
            'name' => $s->name ?? '',
            'category_id' => $s->category_id ?? '',
        ];
    })->values();

    $selectedCategoryJson = old('category_id')
        ? $categories->firstWhere('id', old('category_id'))
        : ($listing->category ? (object)[
            'id' => $listing->category->id,
            'name' => $listing->category->name
        ] : null);

    $selectedSubcategoriesJson = collect(old('subcategory_ids', []))->map(function ($id) use ($subcategories) {
        $sub = $subcategories->firstWhere('id', $id);
        return $sub ? [
            'id' => $sub->id,
            'name' => $sub->name,
            'category_id' => $sub->category_id,
        ] : null;
    })->filter()->values();

  if ($selectedSubcategoriesJson->isEmpty()) {
    $savedSubcategoryIds = is_array($listing->subcategory_ids)
        ? $listing->subcategory_ids
        : json_decode($listing->subcategory_ids, true);

    $selectedSubcategoriesJson = collect($savedSubcategoryIds ?? [])->map(function ($id) use ($subcategories) {
        $sub = $subcategories->firstWhere('id', $id);

        return $sub ? [
            'id' => $sub->id,
            'name' => $sub->name,
            'category_id' => $sub->category_id,
        ] : null;
    })->filter()->values();
}
@endphp
<style>
    .rrrr{
        color: red
    }

    
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Listing</h5>
                <a href="{{ route('admin.listing.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ url()->current() }}" method="POST" enctype="multipart/form-data" id="listingEditForm">
                @csrf

                <div class="card-body">
                    <div class="row">

                       <div class="col-md-4">

    <div class="form-group">
        <label>Business Image</label>
        <div class="border p-3 rounded text-center">
            <img id="mainPreview"
                 src="{{ $listing->thumb ? loadImage('assets/images/listing/thumb/'.$listing->thumb) : asset('assets/images/default.jpg') }}"
                 alt="Business Image"
                 style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

            <div class="custom-file mt-3">
                <input type="file" name="image" class="custom-file-input upload-image" id="upload">
                <label class="custom-file-label" for="upload">Choose Business Image</label>
            </div>

            <small class="text-primary d-block mt-2">
                Image will resize into 350x190 px
            </small>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <strong>Social Media Links</strong>
        </div>

        <div class="card-body">
<div class="form-group">
        <label>Website URL</label>
        <input type="url"
               name="website"
               class="form-control"
               value="{{ old('website', $listing->website ?? '') }}"
               placeholder="https://yourwebsite.com">
    </div>
            <div class="form-group">
                <label>Facebook URL</label>
                <input type="url"
                       name="facebook"
                       class="form-control"
                       value="{{ old('facebook', $listing->facebook ?? '') }}"
                       placeholder="https://facebook.com/yourpage">
            </div>

            <div class="form-group">
                <label>Instagram URL</label>
                <input type="url"
                       name="instagram"
                       class="form-control"
                       value="{{ old('instagram', $listing->instagram ?? '') }}"
                       placeholder="https://instagram.com/yourprofile">
            </div>

            <div class="form-group">
                <label>LinkedIn URL</label>
                <input type="url"
                       name="linkedin"
                       class="form-control"
                       value="{{ old('linkedin', $listing->linkedin ?? '') }}"
                       placeholder="https://linkedin.com/company/yourcompany">
            </div>

            <div class="form-group">
                <label>Twitter / X URL</label>
                <input type="url"
                       name="twitter"
                       class="form-control"
                       value="{{ old('twitter', $listing->twitter ?? '') }}"
                       placeholder="https://x.com/yourprofile">
            </div>

            <div class="form-group mb-0">
                <label>YouTube URL</label>
                <input type="url"
                       name="youtube"
                       class="form-control"
                       value="{{ old('youtube', $listing->youtube ?? '') }}"
                       placeholder="https://youtube.com/@yourchannel">
            </div>

        </div>
    </div>

</div>
                        <div class="col-md-8">

                            <h5 class="mb-3">Business Details</h5>

                            <div class="form-group">
                                <label>Business Name</label>
                                <input type="text" name="name" class="form-control seo-source"
                                       placeholder="Enter Business Name"
                                       value="{{ old('name', $listing->name) }}" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pincode</label>
                                        <input type="text" id="pincode" name="pincode" class="form-control"
                                               maxlength="6" placeholder="Enter Pincode"
                                               value="{{ old('pincode', $listing->pincode ?? '') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Since (Year)</label>
                                        <input type="text"
                                               name="established_year"
                                               class="form-control"
                                               maxlength="4"
                                               pattern="[0-9]{4}"
                                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                               placeholder="e.g. 2015"
                                               value="{{ old('established_year', $listing->established_year ?? '') }}">
                                    </div>
                                </div>
                            </div>
@php
    $hasOldAddress = !empty(trim($listing->address ?? ''));
@endphp

@if($hasOldAddress)
    <div class="form-group">
        <label>Address</label>
        <textarea name="address"
                  id="address"
                  class="form-control"
                  rows="3"
                  placeholder="Enter full address">{{ old('address', $listing->address ?? '') }}</textarea>
    </div>
@else
    <div class="form-group">
        <label>Plot No. / Shop No. / Floor <span class="rrrr">*</span></label>
        <input type="text"
               name="plot_no"
               class="form-control"
               placeholder="Enter Plot No. / Shop No. / Floor"
               value="{{ old('plot_no', $listing->plot_no ?? '') }}">
    </div>

    <div class="form-group">
        <label>Building Name / Market / Colony <span class="rrrr">*</span></label>
        <input type="text"
               name="building_name"
               class="form-control"
               placeholder="Enter Building Name / Market / Colony"
               value="{{ old('building_name', $listing->building_name ?? '') }}">
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Street / Road Name <span class="rrrr">*</span></label>
            <input type="text"
                   name="street_name"
                   class="form-control"
                   placeholder="Enter Street / Road Name"
                   value="{{ old('street_name', $listing->street_name ?? '') }}">
        </div>

        <div class="form-group col-md-6">
            <label>Landmark</label>
            <input type="text"
                   name="landmark"
                   class="form-control"
                   placeholder="Enter Landmark"
                   value="{{ old('landmark', $listing->landmark ?? '') }}">
        </div>
    </div>
@endif

<div class="form-group">
    <label>Area <span class="rrrr">*</span></label>
    <select class="form-control" id="area" name="area">
        <option value="">Select Area</option>

        @if(old('area', $listing->area ?? ''))
            <option value="{{ old('area', $listing->area ?? '') }}" selected>
                {{ old('area', $listing->area ?? '') }}
            </option>
        @endif
    </select>
</div>

<div class="form-group">
    <label>Location <span class="rrrr">*</span></label>
    <select name="location_id"
        id="location_id"
        class="form-control"
        required>
        <option value="">Select Location</option>

        @foreach($locations as $location)
            <option value="{{ $location->id }}"
                {{ old('location_id', $listing->location_id) == $location->id ? 'selected' : '' }}>
                {{ $location->name }}
            </option>
        @endforeach
    </select>
    <small id="locationAutoNote" class="form-text text-success d-none"></small>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>City</label>
        <input type="text"
               id="city"
               name="city"
               class="form-control seo-source"
               value="{{ old('city', $listing->city ?? '') }}"
               readonly>
    </div>

    <div class="form-group col-md-6">
        <label>State</label>
        <input type="text"
               id="state"
               name="state"
               class="form-control"
               value="{{ old('state', $listing->state ?? '') }}"
               readonly>
    </div>
</div>

<input type="hidden"
       name="latitude"
       id="latitude"
       value="{{ old('latitude', $listing->latitude ?? '') }}">

<input type="hidden"
       name="longitude"
       id="longitude"
       value="{{ old('longitude', $listing->longitude ?? '') }}">

<input type="hidden"
       name="full_address"
       id="full_address"
       value="{{ old('full_address', $listing->full_address ?? '') }}">

<input type="hidden"
       name="place_id"
       id="place_id"
       value="{{ old('place_id', $listing->place_id ?? '') }}">
                          
                            <hr>

                            <h5 class="mb-3">Contact Details</h5>

                            <div class="form-group">
                                <label>Contact Person</label>
                                <div class="phone-group">
                                    <select class="form-control country-code" name="contact_title">
                                        <option value="Mr" {{ old('contact_title', $listing->contact_title ?? '') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                        <option value="Mrs" {{ old('contact_title', $listing->contact_title ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                        <option value="Miss" {{ old('contact_title', $listing->contact_title ?? '') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                        <option value="Dr" {{ old('contact_title', $listing->contact_title ?? '') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                    </select>

                                    <input type="text" class="form-control "
                                           name="contact_person"
                                           placeholder="Contact Person"
                                           value="{{ old('contact_person', $listing->contact_person ?? '') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Mobile Number</label>
                                <div id="mobileNumbersWrapper">
                                    @php
                                        $mobileNumbers = old('mobile_numbers', $listing->mobile_numbers ?? ['']);
                                        $mobileCodes = old('mobile_country_code', $listing->mobile_country_code ?? ['+91']);
                                    @endphp

                                    @foreach($mobileNumbers as $i => $mobile)
                                        <div class="phone-group mobile-number-item">
                                            <select class="form-control country-code" name="mobile_country_code[]">
                                                <option value="+91" {{ ($mobileCodes[$i] ?? '+91') == '+91' ? 'selected' : '' }}>+91</option>
                                            </select>
                                            <input type="text" class="form-control " name="mobile_numbers[]" value="{{ $mobile }}" placeholder="Mobile Number">
                                            @if($i > 0)
                                                <button type="button" class="input-remove remove-row">×</button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <a href="javascript:void(0)" class="add-link" id="addMobileNumber">+ Add Another Mobile Number</a>
                            </div>

                            <div class="form-group">
                                <label>WhatsApp Number</label>
                                <div id="whatsappNumbersWrapper">
                                    @php
                                        $whatsappNumbers = old('whatsapp_numbers', $listing->whatsapp_numbers ?? ['']);
                                        $whatsappCodes = old('whatsapp_country_code', $listing->whatsapp_country_code ?? ['+91']);
                                    @endphp

                                    @foreach($whatsappNumbers as $i => $whatsapp)
                                        <div class="phone-group whatsapp-number-item">
                                            <select class="form-control country-code" name="whatsapp_country_code[]">
                                                <option value="+91" {{ ($whatsappCodes[$i] ?? '+91') == '+91' ? 'selected' : '' }}>+91</option>
                                            </select>
                                            <input type="text" class="form-control whatsapp-input " name="whatsapp_numbers[]" value="{{ $whatsapp }}" placeholder="WhatsApp Number">
                                            @if($i > 0)
                                                <button type="button" class="input-remove remove-row">×</button>
                                            @endif
                                        </div>  
                                    @endforeach
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="sameAsMobile">
                                    <label class="form-check-label checkbox-text" for="sameAsMobile">
                                        Same As Mobile Number
                                    </label>
                                </div>

                                <a href="javascript:void(0)" class="add-link" id="addWhatsappNumber">+ Add WhatsApp Number</a>
                            </div>

                            <div class="form-group">
    <label>Landline Number</label>

    <div id="landlineNumbersWrapper">
        @php
            $landlineNumbers = old('landline_numbers', $listing->landline_numbers ?? ['']);
            $landlineCodes = old('landline_country_code', $listing->landline_country_code ?? ['+91']);
        @endphp

        @foreach($landlineNumbers as $i => $landline)
            <div class="phone-group landline-number-item">
                <select class="form-control country-code" name="landline_country_code[]">
                    <option value="+91" {{ ($landlineCodes[$i] ?? '+91') == '+91' ? 'selected' : '' }}>
                        +91
                    </option>
                </select>

                <input type="text"
                       class="form-control"
                       name="landline_numbers[]"
                       value="{{ $landline }}"
                       placeholder="Landline Number">

                @if($i > 0)
                    <button type="button" class="input-remove remove-row">×</button>
                @endif
            </div>
        @endforeach
    </div>

    <a href="javascript:void(0)"
       class="add-link"
       id="addLandlineNumber">
       + Add Another Landline Number
    </a>
</div>
                          

                            <div class="form-group">
                                <label>Email</label>
                                <div id="emailsWrapper">
                                    @php
                                        $extraEmails = old('extra_emails', $listing->extra_emails ?? ['']);
                                    @endphp

                                    @foreach($extraEmails as $i => $email)
                                        <div class="phone-group email-item">
                                            <input type="email" class="form-control" name="extra_emails[]" value="{{ $email }}" placeholder="Email">
                                            @if($i > 0)
                                                <button type="button" class="input-remove remove-row">×</button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <a href="javascript:void(0)" class="add-link" id="addAnotherEmail">+ Add Another Email</a>
                            </div>

                            <hr>

                            <h5 class="mb-3">User Details</h5>

                            <div class="form-group smart-picker" id="userPicker">
                                <label>Add User</label>
                                <input type="text" id="userSearchInput" class="form-control smart-input"
                                       placeholder="Search by username, name, email or mobile"
                                       value="{{ old('user_name', $selectedUserJson['name'] ?? '') }}">
                                <div class="suggestion-dropdown d-none" id="userSuggestionBox"></div>
                                <div id="selectedUserWrap" class="mt-2"></div>
                            </div>

                            <div id="userHiddenInputs">
    <input type="hidden" name="user_id" id="selected_user_id" value="{{ old('user_id', $listing->user_id ?? '') }}">
</div>

                            <hr>

                            <h5 class="mb-3">Category Details</h5>

                            <div class="form-group smart-picker" id="categoryPicker">
                                <label>Main Category</label>
                                <input type="text" id="categorySearchInput" class="form-control smart-input"
                                       placeholder="Type category name"
                                    value="{{ old('category_name', optional($selectedCategoryJson)->name ?? '') }}">
                                <div class="suggestion-dropdown d-none" id="categorySuggestionBox"></div>
                                <div id="selectedCategoryWrap" class="mt-2"></div>
                            </div>

                            <div class="form-group smart-picker mt-4" id="subcategoryPicker">
                                <label>Sub Categories</label>
                                <input type="text" id="subcategorySearchInput" class="form-control smart-input"
                                       placeholder="Type sub category name"
                                       {{ old('category_id', $listing->category_id) ? '' : 'disabled' }}>
                                <div class="suggestion-dropdown d-none" id="subcategorySuggestionBox"></div>
                               <div id="selectedSubcategoryWrap" class="mt-2">
    @foreach($selectedSubcategoriesJson as $sub)
        <span class="tag-pill selected">
            {{ $sub['name'] }}
            <span class="remove-tag remove-selected-subcategory" data-id="{{ $sub['id'] }}">&times;</span>
        </span>
    @endforeach
</div>
                            </div>

                            <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $listing->category_id) }}">
                           <div id="subcategoryHiddenInputs">
    @foreach($selectedSubcategoriesJson as $sub)
        <input type="hidden" name="subcategory_ids[]" value="{{ $sub['id'] }}">
    @endforeach
</div>

                        </div>

                        <div class="col-md-12 mt-2 mb-4">
                            <div class="bg__dark p-2 text-white d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">Extra Images</h5>
                                <button type="button" class="btn btn-success text-white addImage">
                                    <i class="las la-plus"></i> Add New
                                </button>
                            </div>

                         @php
    $listingPhotos = is_array($listing->photos)
        ? $listing->photos
        : json_decode($listing->photos, true);
@endphp

<div class="row imageElement">
    @if(!empty($listingPhotos))
        @foreach($listingPhotos as $photo)
            <div class="col-md-3 existing-extra-image">
                <div class="image-upload-area mt-3 position-relative">
                    <button type="button"
                            class="btn btn-sm btn-danger text-white position-absolute imageRemoveBtn"
                            data-image="{{ $photo }}">
                        <i class="las la-minus"></i>
                    </button>

                    <img src="{{ asset('assets/images/listing/photos/' . $photo) }}" alt="Image">
                </div>
            </div>
        @endforeach
    @endif

    <div class="col-md-3 extra-upload-box">
        <div class="image-upload-area mt-3 position-relative openExtraImages">
            <img src="{{ asset('assets/images/default.jpg') }}" alt="Image"/>
            <div class="pick-image">Upload Images</div>
        </div>
    </div>
</div>

<input type="file"
       name="images[]"
       id="extraImagesInput"
       class="d-none"
       multiple
       accept="image/*">
                        </div>

                        {{-- <div class="col-md-12 mt-2 mb-4">
                            <div class="bg__dark p-2 text-white d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">Services</h5>
                                <button type="button" class="btn btn-success text-white addFeature">
                                    <i class="las la-plus"></i> Add New
                                </button>
                            </div>

                            <div class="row mt-3 featureElement">
                                @php
                                    $features = old('features', $listing->features ?? ['']);
                                    if (empty($features)) { $features = ['']; }
                                @endphp

                                @foreach($features as $i => $feature)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" name="features[]" class="form-control  seo-source feature-input" value="{{ $feature }}" placeholder="Features">
                                                @if($i > 0)
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger text-white input-group-text removeFeature">
                                                            <i class="las la-minus"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div> --}}

                        <div class="col-md-12">
                            <hr>
                            <h5 class="mb-3">Business Timings</h5>
                        </div>

                      <div class="col-md-12">
    <label class="section-label">Select Days Of The Week</label>

    <div class="weekday-list">
    @php
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    $savedBusinessDays = $listing->business_days ?? [];

    if (is_string($savedBusinessDays)) {
        $decodedBusinessDays = json_decode($savedBusinessDays, true);

        $savedBusinessDays = is_array($decodedBusinessDays)
            ? $decodedBusinessDays
            : array_filter(array_map('trim', explode(',', $savedBusinessDays)));
    }

    $selectedDays = old('business_days', $savedBusinessDays);

    if (!is_array($selectedDays)) {
        $selectedDays = [];
    }
@endphp

        @foreach($days as $day)
            <div class="weekday-item">
                <input
                    type="checkbox"
                    id="day_{{ $day }}"
                    name="business_days[]"
                    value="{{ $day }}"
                    {{ in_array($day, $selectedDays) ? 'checked' : '' }}
                >

                <label for="day_{{ $day }}">
                    {{ $day }}
                </label>
            </div>
        @endforeach
    </div>

    <div class="form-check mb-3">
        <input
            class="form-check-input"
            type="checkbox"
            id="selectAllDays"
        >

        <label
            class="form-check-label checkbox-text"
            for="selectAllDays"
        >
            Select All Days
        </label>
    </div>

   @php
    $savedOpen24OpenTimes = $listing->open_time ?? [];
    $savedOpen24CloseTimes = $listing->close_time ?? [];

    if (is_string($savedOpen24OpenTimes)) {
        $savedOpen24OpenTimes =
            json_decode($savedOpen24OpenTimes, true) ?: [];
    }

    if (is_string($savedOpen24CloseTimes)) {
        $savedOpen24CloseTimes =
            json_decode($savedOpen24CloseTimes, true) ?: [];
    }

    $detectedOpen24Hours =
        count($savedOpen24OpenTimes) === 1
        &&
        count($savedOpen24CloseTimes) === 1
        &&
        ($savedOpen24OpenTimes[0] ?? '') === '12:00 AM'
        &&
        ($savedOpen24CloseTimes[0] ?? '') === '11:59 PM';

    $isOpen24Hours = old(
        'open_24_hours',
        $detectedOpen24Hours ? 1 : 0
    );
@endphp

<div class="form-check mb-3">
    <input
        class="form-check-input"
        type="checkbox"
        id="open24Hours"
        name="open_24_hours"
        value="1"
        {{ (int) $isOpen24Hours === 1 ? 'checked' : '' }}
    >

    <label
        class="form-check-label checkbox-text"
        for="open24Hours"
    >
        Open 24 Hours
    </label>
</div>
</div>

                        <div class="col-md-12">
                   @php
    $savedOpenTimes = $listing->open_time ?? [];
    $savedCloseTimes = $listing->close_time ?? [];

    if (is_string($savedOpenTimes)) {
        $decodedOpenTimes = json_decode($savedOpenTimes, true);

        $savedOpenTimes = is_array($decodedOpenTimes)
            ? $decodedOpenTimes
            : array_filter(array_map('trim', explode(',', $savedOpenTimes)));
    }

    if (is_string($savedCloseTimes)) {
        $decodedCloseTimes = json_decode($savedCloseTimes, true);

        $savedCloseTimes = is_array($decodedCloseTimes)
            ? $decodedCloseTimes
            : array_filter(array_map('trim', explode(',', $savedCloseTimes)));
    }

    $openTimes = old('open_time', $savedOpenTimes);
    $closeTimes = old('close_time', $savedCloseTimes);

    $openTimes = is_array($openTimes) && !empty($openTimes)
        ? array_values($openTimes)
        : [''];

    $closeTimes = is_array($closeTimes)
        ? array_values($closeTimes)
        : [''];

    $timeOptions = [];

    for ($h = 0; $h < 24; $h++) {
        foreach (['00', '30'] as $m) {
            $timeOptions[] = date(
                'h:i A',
                strtotime(sprintf('%02d:%s', $h, $m))
            );
        }
    }
@endphp
                            <div id="timeSlotsWrapper">
                                @foreach($openTimes as $i => $open)
                                    <div class="time-slot-box time-slot-item border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-5 mb-3">
                                                <label class="form-label">Open at</label>
                                                <select class="form-control" name="open_time[]">
                                                    <option value="">Select Opening Time</option>
                                                    @foreach($timeOptions as $time)
                                                        <option value="{{ $time }}" {{ $open == $time ? 'selected' : '' }}>
                                                            {{ $time }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-5 mb-3">
                                                <label class="form-label">Close at</label>
                                                <select class="form-control" name="close_time[]">
                                                    <option value="">Select Closing Time</option>
                                                    @foreach($timeOptions as $time)
                                                        <option value="{{ $time }}" {{ ($closeTimes[$i] ?? '') == $time ? 'selected' : '' }}>
                                                            {{ $time }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2 d-flex align-items-end mb-3">
                                                @if($i > 0)
                                                    <button type="button" class="btn btn-danger remove-time-slot w-100">×</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <a href="javascript:void(0)" class="add-link" id="addTimeSlot">+ Add Another Time Slot</a>
                        </div>

                        <div class="col-md-12 mt-2 mb-4">
                            <div class="bg__dark p-2 text-white d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">Frequently Asked Question (FAQ)</h5>
                                <button type="button" class="btn btn-success text-white addFaq">
                                    <i class="las la-plus"></i> Add New
                                </button>
                            </div>

                            <div class="row faqElement">
                                @php
                                    $faqQuestions = old('question', $listing->faq['question'] ?? ['']);
                                    $faqAnswers = old('answer', $listing->faq['answer'] ?? ['']);
                                    if (empty($faqQuestions)) { $faqQuestions = ['']; }
                                @endphp

                                @foreach($faqQuestions as $i => $question)
                                    <div class="col-md-4">
                                        <div class="card mt-3 position-relative">
                                            @if($i > 0)
                                                <button type="button" class="btn btn-danger faqRemove position-absolute text-white">
                                                    <i class="las la-minus"></i>
                                                </button>
                                            @endif
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Question</label>
                                                    <input type="text" name="question[]" class="form-control  faq-question" value="{{ $question }}" placeholder="Question">
                                                </div>
                                                <div class="form-group">
                                                    <label>Answer</label>
                                                    <input type="text" name="answer[]" class="form-control  faq-answer" value="{{ $faqAnswers[$i] ?? '' }}" placeholder="Answer">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                                <h5 class="mb-2">SEO Settings</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="generateSeoKeywords">
                                    Auto Generate Keywords
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="seo-score-card">
                                <div class="seo-score-title">Google SEO Score</div>
                                <div class="seo-score-value" id="seoScoreValue">0</div>
                                <div class="seo-score-bar">
                                    <div class="seo-score-fill" id="seoScoreFill"></div>
                                </div>
                                <small class="text-muted d-block mt-2" id="seoScoreText">Start filling SEO fields</small>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="spam-score-card">
                                <div class="seo-score-title">Spam Detection</div>
                                <div class="spam-status" id="spamStatus">Safe</div>
                                <small class="text-muted d-block mt-2" id="spamReason">No spam signals detected</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control  seo-field"
                                       placeholder="Enter Meta Title"
                                       value="{{ old('meta_title', $listing->meta_title ?? '') }}">
                                <small class="text-muted" id="metaTitleCount">0 / 60 characters</small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control  seo-field"
                                       placeholder="e.g. plumber, electrician, jaipur"
                                       value="{{ old('meta_keywords', is_array($listing->meta_keywords ?? null) ? implode(', ', $listing->meta_keywords) : ($listing->meta_keywords ?? '')) }}">
                                <small class="text-danger d-block">
                                    Adult, illegal or link-based content is not allowed.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea name="meta_description" id="meta_description" class="form-control  seo-field" rows="3"
                                          placeholder="Enter Meta Description">{{ old('meta_description', $listing->meta_description ?? '') }}</textarea>
                                <small class="text-muted" id="metaDescriptionCount">0 / 160 characters</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-2">
                            <div class="form-group">
                                <label>Listing Details</label>
                                {{-- data-editor="manual": see the matching note in add.blade.php --}}
                                <textarea class="richEditor" data-editor="manual" rows="6" name="details" id="details">{{ old('details', $listing->details) }}</textarea>
                                <small class="text-danger d-block mt-2">
                                    Adult, illegal or link-based content is not allowed.
                                </small>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.listing.index') }}" class="btn btn-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Listing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.listing.partials.session-keepalive', ['keepAliveForm' => '#listingEditForm'])
@include('admin.listing.partials.location-autoselect')
@include('admin.listing.partials.firewall-shield')
@endsection

@push('css')
<style>
    .image-upload-area{
        border:1px dashed #d8d8d8;
        padding:10px;
        border-radius:10px;
        text-align:center;
        background:#fff;
        overflow:hidden;
    }
    .image-upload-area img{
        width:100%;
        height:180px;
        object-fit:cover;
        border-radius:8px;
        display:block;
    }
    .pick-image{
        display:block;
        margin-top:10px;
        cursor:pointer;
        color:#0d6efd;
        font-weight:600;
    }
    .imageRemove,.removeImage{
        top:6px;
        right:6px;
        padding:1px 6px;
        z-index:10;
    }
    .phone-group{
        display:flex;
        gap:10px;
        margin-bottom:12px;
    }
    .country-code{
        width:100px;
        min-width:100px;
    }
    .input-remove{
        width:42px;
        min-width:42px;
        height:46px;
        border:none;
        border-radius:8px;
        background:#ffe5e5;
        color:#dc3545;
        font-weight:700;
    }
    .add-link{
        display:inline-block;
        color:#0d6efd;
        font-size:14px;
        font-weight:600;
        text-decoration:none;
        margin-top:4px;
    }
    .add-link:hover{
        text-decoration:underline;
    }
    .weekday-list{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-bottom:12px;
    }
    .weekday-item input{
        display:none;
    }
    .weekday-item label{
        min-width:48px;
        height:38px;
        border-radius:30px;
        border:1px solid #d8d8d8;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:0 14px;
        font-size:13px;
        cursor:pointer;
        color:#555;
        background:#fff;
        transition:.2s;
        margin:0;
    }
    .weekday-item input:checked + label{
        background:#e7f1ff;
        color:#0d6efd;
        border-color:#0d6efd;
        font-weight:600;
    }
    .time-slot-box{
        border:1px solid #ececec;
        border-radius:14px;
        padding:14px;
        margin-bottom:15px;
        background:#fafafa;
    }
    .checkbox-text{
        font-size:14px;
        color:#0d6efd;
        font-weight:600;
        cursor:pointer;
    }
    .faqRemove{
        position:absolute;
        top:5px;
        right:5px;
        padding:2px 6px;
        font-size:12px;
        z-index:10;
    }
    .custom-file-label::after{
        content:"Browse";
    }
    .smart-picker{
        position:relative;
    }
    .tag-pill{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:10px 18px;
        border-radius:999px;
        border:1px solid #d5d8dd;
        background:#f1f3f5;
        color:#1f2937;
        font-size:15px;
        font-weight:500;
        line-height:1;
        transition:all .2s ease;
        margin:6px 8px 0 0;
    }
    .tag-pill.selected{
        background:#198754;
        border-color:#198754;
        color:#fff;
    }
    .remove-tag{
        cursor:pointer;
        font-size:16px;
        font-weight:700;
        line-height:1;
    }
    .suggestion-dropdown{
        position:absolute;
        top:100%;
        left:0;
        right:0;
        background:#fff;
        border:1px solid #ddd;
        border-radius:10px;
        max-height:220px;
        overflow-y:auto;
        z-index:999;
        margin-top:5px;
        box-shadow:0 10px 25px rgba(0,0,0,0.08);
    }
    .suggestion-item{
        padding:12px 15px;
        cursor:pointer;
        border-bottom:1px solid #f1f1f1;
    }
    .suggestion-item:hover{
        background:#f8f9fa;
    }
    .suggestion-name{
        font-weight:600;
    }
    .suggestion-meta{
        font-size:12px;
        color:#6c757d;
        margin-top:4px;
    }
    .suggestion-empty{
        padding:12px 15px;
        color:#888;
    }
    .seo-score-card,
    .spam-score-card{
        border:1px solid #e9ecef;
        border-radius:14px;
        padding:16px;
        background:#fff;
        min-height:130px;
    }
    .seo-score-title{
        font-weight:700;
        font-size:15px;
        margin-bottom:8px;
    }
    .seo-score-value{
        font-size:34px;
        font-weight:800;
        line-height:1;
        margin-bottom:10px;
    }
    .seo-score-bar{
        width:100%;
        height:10px;
        background:#edf0f2;
        border-radius:30px;
        overflow:hidden;
    }
    .seo-score-fill{
        width:0%;
        height:100%;
        background:#28a745;
        transition:width .3s ease;
    }
    .spam-status{
        font-size:26px;
        font-weight:800;
        line-height:1.2;
    }

    @media(max-width:425px){
        .bg__dark .btn-success{
            padding:7px 5px;
            font-size:12px;
        }
        .phone-group{
            flex-direction:column;
        }
        .country-code{
            width:100%;
            min-width:100%;
        }
    }

    .image-upload-area{
    border:1px dashed #d8d8d8;
    padding:10px;
    border-radius:10px;
    text-align:center;
    background:#fff;
    overflow:hidden;
    position:relative;
}

.image-upload-area img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:8px;
    display:block;
}

.imageRemoveBtn{
    position:absolute;
    top:6px;
    right:6px;
    width:26px;
    height:26px;
    border:none;
    border-radius:4px;
    background:#f14a16;
    color:#fff;
    font-size:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:20;
    cursor:pointer;
    box-shadow:0 2px 6px rgba(0,0,0,0.15);
    padding:0;
}

.imageRemoveBtn i{
    color:#fff;
    font-size:14px;
    line-height:1;
}

.imageRemoveBtn:hover{
    background:#d93f10;
}
</style>
@endpush

@push('js')
<script>
(function ($) {
    "use strict";

    if (typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
    }

    /*
     * Deleted from the text outright. Only unambiguous terms belong here —
     * this is a directory that lists hospitals, clinics and colleges, so a
     * word with a legitimate business use must not be silently erased.
     */
    const blockedWords = [
        'xxx','porn','porn video','pornvideo','call girl','callgirl','escort','escorts',
        'hot video','blue film','bluefilm','sex','sex video','sexvideo','bhabhi sex',
        'desi sex','massage sex','boobs','oral sex','handjob','blowjob','fuck','fucking',
        'condom sex','randi','veshya','prostitute','dating sex','cam sex',
        'illegal drugs','cocaine','mdma','heroin'
    ];

    /*
     * Flagged in the spam panel for a human to look at, never deleted. These
     * all have real uses: "Breast Cancer Screening Centre", "Adult Education
     * Institute", a proctology clinic writing "anal fistula", a urologist
     * writing "penis", a restaurant called "Opium".
     *
     * Move a word between the two lists to change how strict this is.
     */
    const sensitiveWords = [
        'adult','breast','nipple','vagina','penis','anal','sexy','nude',
        'night service','ganja','opium','charas'
    ];

    const spamWords = [
        'free free','100% guaranteed','click here','buy now','limited offer','instant deal',
        'cheap price','best price','urgent sale','cash bonus','earn money fast','win now',
        'promo code','offer valid','subscribe now'
    ];

    const stopWords = [
        'the','and','for','with','your','from','that','this','into','near','best','service',
        'services','business','shop','store','company','agency','center','centre','in','on',
        'at','of','to','a','an','is','are','by'
    ];

    let selectedUser = @json($selectedUserJson);
    let selectedCategory = @json($selectedCategoryJson ? ['id' => $selectedCategoryJson->id, 'name' => $selectedCategoryJson->name] : null);
    let selectedSubcategories = @json($selectedSubcategoriesJson);

    let initialCategories = @json($categoriesJson);
    let initialSubcategories = @json($subcategoriesJson);
let locationFilteredSubcategories = [];
let subcategoryAjaxRequest = null;
    function debounce(func, delay = 300) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function escapeHtml(text) {
        return $('<div>').text(text ?? '').html();
    }

    function containsLink(text) {
        if (!text) return false;
        return /(https?:\/\/|ftp:\/\/|www\.|mailto:|tel:|<a\s[^>]*href=|href=)/i.test(text);
    }

    function stripHtml(text) {
        let div = document.createElement('div');
        div.innerHTML = text;
        return (div.textContent || div.innerText || '').trim();
    }

    /*
     * Blocked words must only match whole words. A plain substring match ate
     * the middle of ordinary words — "analysis" became "ysis", "canal"
     * became "c", "Sussex" became "Sus" — which is what made the editor look
     * like it was corrupting text as you typed.
     */
    function blockedWordRegex(word) {
        const escaped = word
            .replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
            .replace(/\s+/g, '\\s+');

        return new RegExp('\\b' + escaped + '\\b', 'gi');
    }

    function containsBlockedWords(text) {
        if (!text) return false;

        const value = String(text);
        return blockedWords.some(word => blockedWordRegex(word).test(value));
    }

    function cleanText(text) {
        if (!text) return '';

        let cleaned = String(text);
        cleaned = cleaned.replace(/https?:\/\/\S+/gi, '');
        cleaned = cleaned.replace(/www\.\S+/gi, '');
        cleaned = cleaned.replace(/<a\b[^>]*>(.*?)<\/a>/gi, '$1');
        cleaned = cleaned.replace(/href\s*=\s*(['"]).*?\1/gi, '');

        blockedWords.forEach(word => {
            cleaned = cleaned.replace(blockedWordRegex(word), '');
        });

        // Deliberately no trim()/whitespace collapsing here: this also runs
        // over editor HTML, where eating a trailing space means the typist
        // cannot put a space after a word.
        return cleaned;
    }

    /*
     * Paste handling. Keeps simple formatting, drops links, scripts, Word
     * style soup and blocked words. Returns markup ready for pasteHTML.
     */
    function sanitizePastedHtml(html) {
        const holder = document.createElement('div');
        holder.innerHTML = cleanText(html);

        holder.querySelectorAll('script, style, meta, link, iframe, object, embed, img, a')
            .forEach(function (node) {
                if (node.tagName === 'A') {
                    // Keep what the link said, lose the link itself.
                    while (node.firstChild) {
                        node.parentNode.insertBefore(node.firstChild, node);
                    }
                }

                node.remove();
            });

        holder.querySelectorAll('*').forEach(function (node) {
            Array.from(node.attributes).forEach(function (attr) {
                const keep = attr.name.toLowerCase();

                if (keep !== 'colspan' && keep !== 'rowspan') {
                    node.removeAttribute(attr.name);
                }
            });
        });

        return holder.innerHTML;
    }

    /** Small, non-blocking note when a paste had something stripped out. */
    function showPasteNotice() {
        let $note = $('#pasteCleanNotice');

        if (!$note.length) {
            $note = $('<div id="pasteCleanNotice" class="alert alert-warning" ' +
                'style="position:fixed;right:18px;bottom:18px;z-index:20000;max-width:320px;font-size:13px"></div>')
                .appendTo('body');
        }

        $note.text('Pasted — links and blocked words were removed.').stop(true, true).show();

        clearTimeout(showPasteNotice._t);
        showPasteNotice._t = setTimeout(function () {
            $note.fadeOut(300);
        }, 5000);
    }

    function validateTextContent(text) {
        if (!text) return null;

        let plain = stripHtml(text);

        if (containsLink(text) || containsLink(plain)) {
            return 'Links are not allowed.';
        }

        if (containsBlockedWords(text) || containsBlockedWords(plain)) {
            return 'Adult or illegal keywords are not allowed.';
        }

        return null;
    }

    function sanitizeFieldValue($field) {
        let oldVal = $field.val();
        let cleaned = cleanText(oldVal);
        if (oldVal !== cleaned) {
            $field.val(cleaned);
        }
    }

    /*
     * Only safe to call when the caret does not matter — i.e. on submit.
     * summernote('code', ...) re-renders the whole editable area and drops
     * the cursor back to the start, so calling it while someone is typing
     * makes the box impossible to write in.
     */
    function sanitizeSummernoteContent() {
        if (!$.fn.summernote || !$('.richEditor').length) return;

        let content = $('.richEditor').summernote('code');
        let cleaned = cleanText(content);

        if (content !== cleaned) {
            $('.richEditor').summernote('code', cleaned);
        }
    }

    function extractPlainDetails() {
        if ($.fn.summernote && $('.richEditor').length) {
            return stripHtml($('.richEditor').summernote('code'));
        }
        return $('#details').val() || '';
    }

    function uniqueWords(arr) {
        return [...new Set(arr.filter(Boolean))];
    }

    function getCategoryName() {
        return selectedCategory ? selectedCategory.name : '';
    }

    function autoGenerateKeywords() {
        const sources = [];

        sources.push($('input[name="name"]').val() || '');
        sources.push($('#city').val() || '');
        sources.push(getCategoryName());
        sources.push(...$('.feature-input').map(function () { return $(this).val(); }).get());
        sources.push(...$('.faq-question').map(function () { return $(this).val(); }).get());
        sources.push(extractPlainDetails());

        let combined = sources.join(' ').toLowerCase();
        combined = cleanText(combined);
        combined = combined.replace(/[^a-z0-9\s,-]/gi, ' ');

        let words = combined.split(/\s+/).filter(word =>
            word.length > 2 &&
            !stopWords.includes(word) &&
            !/^\d+$/.test(word)
        );

        let topWords = uniqueWords(words).slice(0, 12);

        let phraseParts = [];
        let businessName = $('input[name="name"]').val() || '';
        let city = $('#city').val() || '';
        let category = getCategoryName();

        if (businessName) phraseParts.push(businessName);
        if (category) phraseParts.push(category);
        if (city) phraseParts.push(city);

        let finalKeywords = uniqueWords([
            ...phraseParts,
            ...topWords
        ]).slice(0, 12);

        $('#meta_keywords').val(finalKeywords.join(', '));
        updateSeoScore();
        updateSpamDetection();
    }

    function updateSeoScore() {
        let score = 0;

        const title = ($('#meta_title').val() || '').trim();
        const keywords = ($('#meta_keywords').val() || '').trim();
        const description = ($('#meta_description').val() || '').trim();
        const details = extractPlainDetails().trim();
        const businessName = ($('input[name="name"]').val() || '').trim();
        const category = getCategoryName();

        if (businessName.length >= 3) score += 10;
        if (category.length >= 3) score += 10;

        if (title.length >= 30 && title.length <= 60) {
            score += 25;
        } else if (title.length > 0) {
            score += 10;
        }

        if (description.length >= 80 && description.length <= 160) {
            score += 25;
        } else if (description.length > 0) {
            score += 10;
        }

        if (keywords.length > 0) {
            let keywordCount = keywords.split(',').map(k => k.trim()).filter(Boolean).length;
            if (keywordCount >= 5 && keywordCount <= 12) {
                score += 20;
            } else {
                score += 8;
            }
        }

        if (details.length >= 150) score += 10;

        score = Math.min(100, score);

        $('#seoScoreValue').text(score);
        $('#seoScoreFill').css('width', score + '%');

        let text = 'Needs improvement';
        if (score >= 80) text = 'Excellent SEO structure';
        else if (score >= 60) text = 'Good SEO structure';
        else if (score >= 40) text = 'Average SEO structure';

        $('#seoScoreText').text(text);
        $('#metaTitleCount').text(title.length + ' / 60 characters');
        $('#metaDescriptionCount').text(description.length + ' / 160 characters');
    }

    function updateSpamDetection() {
        let spamScore = 0;
        let reasons = [];

        const sources = [
            $('input[name="name"]').val() || '',
            $('#meta_title').val() || '',
            $('#meta_keywords').val() || '',
            $('#meta_description').val() || '',
            extractPlainDetails()
        ];

        const text = sources.join(' ').toLowerCase();

        spamWords.forEach(word => {
            if (text.includes(word)) {
                spamScore += 20;
                reasons.push(word);
            }
        });

        // Not removed from the listing — surfaced so a human can judge it.
        sensitiveWords.forEach(word => {
            if (blockedWordRegex(word).test(text)) {
                spamScore += 10;
                reasons.push('needs review: ' + word);
            }
        });

        if ((text.match(/!/g) || []).length > 5) {
            spamScore += 10;
            reasons.push('too many exclamation marks');
        }

        if ((text.match(/\bfree\b/g) || []).length > 3) {
            spamScore += 15;
            reasons.push('repeated "free"');
        }

        if ((text.match(/\b(best|cheap|guaranteed|instant)\b/g) || []).length > 5) {
            spamScore += 15;
            reasons.push('too many promotional words');
        }

        if (containsLink(text)) {
            spamScore += 40;
            reasons.push('link detected');
        }

        let status = 'Safe';
        let reasonText = 'No spam signals detected';

        if (spamScore >= 50) {
            status = 'High Risk';
            reasonText = reasons.length ? reasons.join(', ') : 'Strong spam pattern detected';
        } else if (spamScore >= 20) {
            status = 'Medium Risk';
            reasonText = reasons.length ? reasons.join(', ') : 'Some spam pattern detected';
        }

        $('#spamStatus').text(status);
        $('#spamReason').text(reasonText);
    }

    function readURL(input, thisElement) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var parentElement = thisElement.closest('.image-upload-area, .border');
            var previewElement = parentElement.find('#preview, #mainPreview').first();

            reader.onload = function (e) {
                previewElement.attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

   function renderSelectedUser() {
    let html = '';

    if (selectedUser) {
        html = `
            <span class="tag-pill selected">
                ${escapeHtml(selectedUser.name)}
                <span class="remove-tag remove-selected-user" data-id="${selectedUser.id}">&times;</span>
            </span>
        `;
        $('#selected_user_id').val(selectedUser.id);
    } else {
        $('#selected_user_id').val('');
    }

    $('#selectedUserWrap').html(html);
}

    function renderSelectedCategory() {
        let html = '';

        if (selectedCategory) {
            html = `
                <span class="tag-pill selected">
                    ${escapeHtml(selectedCategory.name)}
                    <span class="remove-tag remove-selected-category" data-id="${selectedCategory.id}">&times;</span>
                </span>
            `;
            $('#categorySearchInput').val(selectedCategory.name);
        } else {
            $('#categorySearchInput').val('');
        }

        $('#selectedCategoryWrap').html(html);
        $('#category_id').val(selectedCategory ? selectedCategory.id : '');

        if (selectedCategory) {
            $('#subcategorySearchInput').prop('disabled', false);
        } else {
            $('#subcategorySearchInput').prop('disabled', true).val('');
            $('#subcategorySuggestionBox').addClass('d-none').html('');
        }

        updateSeoScore();
    }

    function renderSelectedSubcategories() {
        let html = '';
        let hidden = '';

        selectedSubcategories.forEach(sub => {
            html += `
                <span class="tag-pill selected">
                    ${escapeHtml(sub.name)}
                    <span class="remove-tag remove-selected-subcategory" data-id="${sub.id}">&times;</span>
                </span>
            `;
            hidden += `<input type="hidden" name="subcategory_ids[]" value="${sub.id}">`;
        });

        $('#selectedSubcategoryWrap').html(html);
        $('#subcategoryHiddenInputs').html(hidden);
    }

    function getPrimaryEmail() {
        return ($('input[name="extra_emails[]"]').first().val() || '').trim();
    }

    function getPrimaryMobile() {
        return ($('input[name="mobile_numbers[]"]').first().val() || '').trim();
    }

    function splitContactPerson() {
        let fullName = ($('input[name="contact_person"]').val() || '').trim();

        if (!fullName) {
            return { first_name: '', last_name: '' };
        }

        let parts = fullName.split(/\s+/);
        let first_name = parts.shift() || '';
        let last_name = parts.join(' ');

        return { first_name, last_name };
    }

    function buildUserAddress() {
        let addressParts = [
            ($('input[name="plot_no"]').val() || '').trim(),
            ($('input[name="building_name"]').val() || '').trim(),
            ($('input[name="street_name"]').val() || '').trim(),
            ($('input[name="landmark"]').val() || '').trim(),
            ($('#area').val() || '').trim(),
            ($('#city').val() || '').trim(),
            ($('#state').val() || '').trim(),
            ($('#pincode').val() || '').trim()
        ];

        return addressParts.filter(Boolean).join(', ');
    }

    function appendQuickUserModal() {
        if ($('#quickUserModal').length) return;

        $('body').append(`
            <div class="modal fade" id="quickUserModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New User</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" id="quick_first_name" class="form-control" placeholder="First Name">
                            </div>

                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" id="quick_last_name" class="form-control" placeholder="Last Name">
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" id="quick_email" class="form-control" placeholder="Email">
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input type="text" id="quick_password" class="form-control" placeholder="Password">
                            </div>

                            <small class="text-muted d-block">
                                Username auto generate hoga. Address listing form se auto fill hoga.
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" id="saveQuickUser">Create User</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    function openQuickUserModal() {
        appendQuickUserModal();

        let nameParts = splitContactPerson();

        $('#quick_first_name').val(nameParts.first_name);
        $('#quick_last_name').val(nameParts.last_name);
        $('#quick_email').val(getPrimaryEmail());
        $('#quick_password').val('');

        $('#quickUserModal').modal('show');
    }

    function renderUserDropdown(list) {
        let html = '';

        list.forEach(user => {
            if (!selectedUser || String(selectedUser.id) !== String(user.id)) {
                html += `
                    <div class="suggestion-item add-user-item"
                         data-id="${user.id}"
                         data-name="${escapeHtml(user.name)}"
                         data-username="${escapeHtml(user.username || '')}"
                         data-email="${escapeHtml(user.email || '')}"
                         data-mobile="${escapeHtml(user.mobile || '')}">
                        <div class="suggestion-name">${escapeHtml(user.name)}</div>
                        <div class="suggestion-meta">
                            ${escapeHtml(user.username || '')}
                            ${user.email ? ' | ' + escapeHtml(user.email) : ''}
                            ${user.mobile ? ' | ' + escapeHtml(user.mobile) : ''}
                        </div>
                    </div>
                `;
            }
        });

        html += `
            <div class="suggestion-item text-primary open-quick-user-modal">
                <div class="suggestion-name">
                    <i class="fas fa-plus-circle mr-1"></i> Add New User
                </div>
                <div class="suggestion-meta">
                    Click karke naya user banao
                </div>
            </div>
        `;

        $('#userSuggestionBox')
            .html(html)
            .removeClass('d-none');
    }

    function renderCategoryDropdown(list) {
        let html = '';

        list.forEach(cat => {
            if (!selectedCategory || String(selectedCategory.id) !== String(cat.id)) {
                html += `
                    <div class="suggestion-item add-category-item"
                         data-id="${cat.id}"
                         data-name="${escapeHtml(cat.name)}">
                        <div class="suggestion-name">${escapeHtml(cat.name)}</div>
                    </div>
                `;
            }
        });

        $('#categorySuggestionBox')
            .html(html || '<div class="suggestion-empty">No category found</div>')
            .removeClass('d-none');
    }

    function renderSubcategoryDropdown(list) {
        if (!selectedCategory) {
            $('#subcategorySuggestionBox')
                .html('<div class="suggestion-empty">Please select category first</div>')
                .removeClass('d-none');
            return;
        }

        let q = $('#subcategorySearchInput').val().trim();
        let filteredList = list.filter(item => String(item.category_id) === String(selectedCategory.id));
        let html = '';

        filteredList.forEach(sub => {
            if (!selectedSubcategories.some(x => String(x.id) === String(sub.id))) {
                html += `
                    <div class="suggestion-item add-subcategory-item"
                         data-id="${sub.id}"
                         data-name="${escapeHtml(sub.name)}">
                        <div class="suggestion-name">${escapeHtml(sub.name)}</div>
                    </div>
                `;
            }
        });

        if (q.length > 0) {
            let exactMatch = filteredList.some(item =>
                (item.name || '').toLowerCase() === q.toLowerCase()
            );

            if (!exactMatch) {
                html += `
                    <div class="suggestion-item add-new-subcategory-item text-primary"
                         data-name="${escapeHtml(q)}">
                        <div class="suggestion-name">
                            <i class="fas fa-plus-circle mr-1"></i> Add New "${escapeHtml(q)}"
                        </div>
                        <div class="suggestion-meta">
                            In category: ${escapeHtml(selectedCategory.name)}
                        </div>
                    </div>
                `;
            }
        }

        $('#subcategorySuggestionBox')
            .html(html || '<div class="suggestion-empty">No sub category found</div>')
            .removeClass('d-none');
    }

    $(document).on('change', '.upload-image', function () {
        readURL(this, $(this));
    });

    $('#categorySearchInput').on('focus keyup', debounce(function () {
        let q = $(this).val().trim().toLowerCase();
        let filtered = initialCategories || [];

        if (q.length > 0) {
            filtered = filtered.filter(item =>
                item.name && item.name.toLowerCase().includes(q)
            );
        }

        renderCategoryDropdown(filtered);
    }, 200));

   $('#subcategorySearchInput').on('focus keyup', debounce(function () {
    if (!selectedCategory) {
        $('#subcategorySuggestionBox')
            .html('<div class="suggestion-empty">Please select category first</div>')
            .removeClass('d-none');

        return;
    }

    let q = $(this).val().trim().toLowerCase();

    let filtered = locationFilteredSubcategories.filter(function (item) {
        return !q ||
            String(item.name || '')
                .toLowerCase()
                .includes(q);
    });

    renderSubcategoryDropdown(filtered);
}, 200));

    let searchTimer;
    $('#userSearchInput').on('keyup focus', function () {
        let q = $(this).val().trim();

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            $.ajax({
                url: "{{ route('users.search') }}",
                type: "GET",
                data: { q: q },
                success: function (res) {
                    renderUserDropdown(res);
                },
                error: function () {
                    $('#userSuggestionBox')
                        .html(`
                            <div class="suggestion-empty">Search failed</div>
                            <div class="suggestion-item text-primary open-quick-user-modal">
                                <div class="suggestion-name">
                                    <i class="fas fa-plus-circle mr-1"></i> Add New User
                                </div>
                            </div>
                        `)
                        .removeClass('d-none');
                }
            });
        }, 300);
    });

    $(document).on('click', '.open-quick-user-modal', function () {
        $('#userSuggestionBox').addClass('d-none').html('');
        openQuickUserModal();
    });

    $(document).on('click', '#saveQuickUser', function () {
        let first_name = ($('#quick_first_name').val() || '').trim();
        let last_name  = ($('#quick_last_name').val() || '').trim();
        let email      = ($('#quick_email').val() || '').trim();
        let password   = ($('#quick_password').val() || '').trim();
        let mobile     = getPrimaryMobile();
        let address    = buildUserAddress();

        if (!first_name) {
            alert('First Name required hai');
            $('#quick_first_name').focus();
            return;
        }

        if (!email) {
            alert('Email required hai');
            $('#quick_email').focus();
            return;
        }

        if (!password) {
            alert('Password required hai');
            $('#quick_password').focus();
            return;
        }

        $.ajax({
            url: "{{ route('admin.users.quick.store') }}",
            type: "POST",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                first_name: first_name,
                last_name: last_name,
                email: email,
                password: password,
                mobile: mobile,
                address: address
            },
            beforeSend: function () {
                $('#saveQuickUser').prop('disabled', true).text('Creating...');
            },
            success: function (res) {
                $('#saveQuickUser').prop('disabled', false).text('Create User');

                if (res.status) {
                    selectedUser = {
                        id: res.data.id,
                        name: res.data.name,
                        username: res.data.username || '',
                        email: res.data.email || '',
                        mobile: res.data.mobile || ''
                    };

                    $('#userSearchInput').val(selectedUser.name);
                    renderSelectedUser();
                    $('#quickUserModal').modal('hide');
                } else {
                    alert(res.message || 'User create nahi hua');
                }
            },
            error: function (xhr) {
                $('#saveQuickUser').prop('disabled', false).text('Create User');

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert(xhr.responseJSON.message);
                } else {
                    alert('Error creating user');
                }

                console.log(xhr.responseText);
            }
        });
    });

    $(document).on('click', '.add-user-item', function () {
        selectedUser = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            username: $(this).data('username') || '',
            email: $(this).data('email') || '',
            mobile: $(this).data('mobile') || ''
        };

        $('#userSearchInput').val(selectedUser.name);
        $('#userSuggestionBox').addClass('d-none').html('');
        renderSelectedUser();
    });

  $(document).on('click', '.add-category-item', function () {
    selectedCategory = {
        id: $(this).data('id'),
        name: $(this).data('name')
    };

    selectedSubcategories = [];
    locationFilteredSubcategories = [];

    $('#categorySearchInput').val(selectedCategory.name);
    $('#categorySuggestionBox').addClass('d-none').html('');
    $('#subcategorySearchInput').val('');

    renderSelectedCategory();
    renderSelectedSubcategories();

    loadEditLocationSubcategories(true);
});

    $(document).on('click', '.add-subcategory-item', function () {
        let sub = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            category_id: selectedCategory ? selectedCategory.id : null
        };

        if (!selectedSubcategories.some(x => String(x.id) === String(sub.id))) {
            selectedSubcategories.push(sub);
        }

        $('#subcategorySearchInput').val('');
        renderSelectedSubcategories();
      renderSubcategoryDropdown(locationFilteredSubcategories);
    });

    $(document).on('mousedown', '.add-new-subcategory-item', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let name = ($(this).attr('data-name') || '').trim();
        let categoryId = selectedCategory ? selectedCategory.id : '';

        if (!name || !categoryId) {
            alert('Name ya category missing hai');
            return;
        }

        /*
         * Quick-add now also generates the subcategory's SEO title, keywords
         * and description through the AI, which takes a few seconds — so the
         * row has to show it is working, and must not accept a second click
         * while the first is still in flight.
         */
        let $item = $(this);

        if ($item.data('busy')) {
            return;
        }

        let originalHtml = $item.html();

        $.ajax({
            url: "{{ route('admin.subcategories.quick.store') }}",
            type: "POST",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                category_id: categoryId
            },
            beforeSend: function () {
                $item.data('busy', true)
                    .css('pointer-events', 'none')
                    .html('<i class="fas fa-spinner fa-spin"></i> Adding "' + name + '" — writing SEO…');
            },
            complete: function () {
                $item.data('busy', false)
                    .css('pointer-events', '')
                    .html(originalHtml);
            },
            success: function (res) {
                if (res.status) {
                    let newSub = {
                        id: res.data.id,
                        name: res.data.name,
                        category_id: res.data.category_id
                    };

                    let alreadyExists = initialSubcategories.some(x => String(x.id) === String(newSub.id));
                    if (!alreadyExists) {
                        initialSubcategories.push(newSub);
                    }

                    let alreadySelected = selectedSubcategories.some(x => String(x.id) === String(newSub.id));
                    if (!alreadySelected) {
                        selectedSubcategories.push(newSub);
                    }

                    renderSelectedSubcategories();
                    $('#subcategorySearchInput').val('');
                    $('#subcategorySuggestionBox').addClass('d-none').html('');
                } else {
                    alert(res.message || 'Sub category add nahi hui');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Error adding subcategory');
            }
        });
    });

    $(document).on('click', '.remove-selected-user', function () {
        selectedUser = null;
        $('#userSearchInput').val('');
        renderSelectedUser();
    });

    $(document).on('click', '.remove-selected-category', function () {
        selectedCategory = null;
        selectedSubcategories = [];
        renderSelectedCategory();
        renderSelectedSubcategories();
    });

    $(document).on('click', '.remove-selected-subcategory', function () {
        let id = $(this).data('id');
        selectedSubcategories = selectedSubcategories.filter(item => String(item.id) !== String(id));
        renderSelectedSubcategories();
       renderSubcategoryDropdown(locationFilteredSubcategories);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#userPicker').length && !$(e.target).closest('#quickUserModal').length) {
            $('#userSuggestionBox').addClass('d-none');
        }
        if (!$(e.target).closest('#categoryPicker').length) {
            $('#categorySuggestionBox').addClass('d-none');
        }
        if (!$(e.target).closest('#subcategoryPicker').length) {
            $('#subcategorySuggestionBox').addClass('d-none');
        }
    });

   
    let featureI = $('.featureElement .col-md-4').length;
    $(document).on('click', '.addFeature', function () {
        var featureElement = `
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" name="features[]" class="form-control  seo-source feature-input" placeholder="Features">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger text-white input-group-text removeFeature"><i class="las la-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('.featureElement').append(featureElement);
        featureI++;
    });

    $(document).on('click', '.removeFeature', function () {
        $(this).closest('.col-md-4').remove();
        featureI--;
        updateSeoScore();
    });

    let faqI = $('.faqElement .col-md-4').length || 1;
    $(document).on('click', '.addFaq', function () {
        var faqElement = `
            <div class="col-md-4">
                <div class="card mt-3 position-relative">
                    <button type="button" class="btn btn-danger faqRemove position-absolute text-white">
                        <i class="las la-minus"></i>
                    </button>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Question</label>
                            <input type="text" name="question[]" class="form-control  faq-question" placeholder="Question">
                        </div>
                        <div class="form-group">
                            <label>Answer</label>
                            <input type="text" name="answer[]" class="form-control  faq-answer" placeholder="Answer">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('.faqElement').append(faqElement);
        faqI++;
    });

    $(document).on('click', '.faqRemove', function () {
        $(this).closest('.col-md-4').remove();
        faqI--;
    });




    function loadEditLocationSubcategories(showDropdown = false) {
    let locationId = $('#location_id').val();
    let categoryId = selectedCategory ? selectedCategory.id : '';

    if (!locationId || !categoryId) {
        locationFilteredSubcategories = [];

        $('#subcategorySearchInput')
            .prop('disabled', true);

        return;
    }

    if (subcategoryAjaxRequest) {
        subcategoryAjaxRequest.abort();
    }

    $('#subcategorySearchInput')
        .prop('disabled', true)
        .attr('placeholder', 'Loading sub categories...');

    subcategoryAjaxRequest = $.ajax({
        url: "{{ route('admin.listing.used.subcategories') }}",
        type: "GET",
        dataType: "json",

        data: {
            location_id: locationId,
            category_id: categoryId
        },

        success: function (res) {
    locationFilteredSubcategories = Array.isArray(res.data)
        ? res.data
        : [];

    // Pehle se selected subcategories ko list me preserve rakho
    selectedSubcategories.forEach(function (selectedSub) {
        let exists = locationFilteredSubcategories.some(function (item) {
            return String(item.id) === String(selectedSub.id);
        });

        if (!exists) {
            locationFilteredSubcategories.push(selectedSub);
        }
    });

    $('#subcategorySearchInput')
        .prop('disabled', false)
        .attr('placeholder', 'Type sub category name');

    if (showDropdown) {
        renderSubcategoryDropdown(locationFilteredSubcategories);
    }
},

        error: function (xhr, status) {
            if (status === 'abort') {
                return;
            }

            locationFilteredSubcategories = [];

            $('#subcategorySearchInput')
                .prop('disabled', false)
                .attr('placeholder', 'Type sub category name');

            $('#subcategorySuggestionBox')
                .html(
                    '<div class="suggestion-empty">' +
                    'Sub categories load nahi hui' +
                    '</div>'
                )
                .removeClass('d-none');

            console.log(xhr.responseText);
        },

        complete: function () {
            subcategoryAjaxRequest = null;
        }
    });
}

$('#location_id').on('change', function () {
    selectedSubcategories = [];
    locationFilteredSubcategories = [];

    $('#subcategorySearchInput').val('');
    $('#subcategorySuggestionBox').addClass('d-none').html('');

    renderSelectedSubcategories();

    if ($(this).val() && selectedCategory) {
        loadEditLocationSubcategories(true);
    } else {
        $('#subcategorySearchInput').prop('disabled', true);
    }
});

$(document).ready(function () {
    renderSelectedUser();
    renderSelectedCategory();
    renderSelectedSubcategories();

    let locationId = $('#location_id').val();

    if (locationId && selectedCategory) {
        loadEditLocationSubcategories(false);
    }
});

    function createPhoneRow(type, wrapperClass) {
        return `
            <div class="phone-group ${wrapperClass}">
                <select class="form-control country-code" name="${type}_country_code[]">
                    <option value="+91">+91</option>
                </select>
                <input type="text" class="form-control " name="${type}_numbers[]" placeholder="${type.replace('_',' ')} Number">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `;
    }

    $('#addMobileNumber').on('click', function () {
        $('#mobileNumbersWrapper').append(createPhoneRow('mobile', 'mobile-number-item'));
    });

    $('#addWhatsappNumber').on('click', function () {
        $('#whatsappNumbersWrapper').append(createPhoneRow('whatsapp', 'whatsapp-number-item'));
    });

    $('#addLandlineNumber').on('click', function () {
        $('#landlineNumbersWrapper').append(createPhoneRow('landline', 'landline-number-item'));
    });

    $('#addAnotherEmail').on('click', function () {
        $('#emailsWrapper').append(`
            <div class="phone-group email-item">
                <input type="email" class="form-control" name="extra_emails[]" placeholder="Email">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `);
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('.phone-group').remove();
    });

    $('#sameAsMobile').on('change', function () {
        const mobileInput = document.querySelector('input[name="mobile_numbers[]"]');
        const whatsappInput = document.querySelector('.whatsapp-input');

        if (this.checked && mobileInput && whatsappInput) {
            whatsappInput.value = mobileInput.value;
        } else if (whatsappInput) {
            whatsappInput.value = '';
        }
    });

  
let timeOptionsHtml = `
    <option value="">Select Time</option>
    <option>12:00 AM</option>
    <option>12:30 AM</option>
    <option>01:00 AM</option>
    <option>01:30 AM</option>
    <option>02:00 AM</option>
    <option>02:30 AM</option>
    <option>03:00 AM</option>
    <option>03:30 AM</option>
    <option>04:00 AM</option>
    <option>04:30 AM</option>
    <option>05:00 AM</option>
    <option>05:30 AM</option>
    <option>06:00 AM</option>
    <option>06:30 AM</option>
    <option>07:00 AM</option>
    <option>07:30 AM</option>
    <option>08:00 AM</option>
    <option>08:30 AM</option>
    <option>09:00 AM</option>
    <option>09:30 AM</option>
    <option>10:00 AM</option>
    <option>10:30 AM</option>
    <option>11:00 AM</option>
    <option>11:30 AM</option>
    <option>12:00 PM</option>
    <option>12:30 PM</option>
    <option>01:00 PM</option>
    <option>01:30 PM</option>
    <option>02:00 PM</option>
    <option>02:30 PM</option>
    <option>03:00 PM</option>
    <option>03:30 PM</option>
    <option>04:00 PM</option>
    <option>04:30 PM</option>
    <option>05:00 PM</option>
    <option>05:30 PM</option>
    <option>06:00 PM</option>
    <option>06:30 PM</option>
    <option>07:00 PM</option>
    <option>07:30 PM</option>
    <option>08:00 PM</option>
    <option>08:30 PM</option>
    <option>09:00 PM</option>
    <option>09:30 PM</option>
    <option>10:00 PM</option>
    <option>10:30 PM</option>
    <option>11:00 PM</option>
    <option>11:30 PM</option>
`;

let firstOpenApplied = false;
let firstCloseApplied = false;

function makeTimeSlot(day) {
    return `
        <div class="time-slot-box time-slot-item" data-day="${day}">
            <input type="hidden" name="timing_days[]" value="${day}">

            <div class="mb-2">
                <strong>${day}</strong>
            </div>

            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">Open at</label>
                  <select class="form-control sync-open-time" name="open_time[]">
    <option value="">Select Opening Time</option>

    @foreach($timeOptions as $time)
        <option
            value="{{ $time }}"
            {{ trim((string) $open) === trim($time) ? 'selected' : '' }}
        >
            {{ $time }}
        </option>
    @endforeach
</select>
                </div>

                <div class="col-md-5 mb-3">
                    <label class="form-label">Close at</label>
                  <select class="form-control sync-close-time" name="close_time[]">
    <option value="">Select Closing Time</option>

    @foreach($timeOptions as $time)
        <option
            value="{{ $time }}"
            {{ trim((string) ($closeTimes[$i] ?? '')) === trim($time) ? 'selected' : '' }}
        >
            {{ $time }}
        </option>
    @endforeach
</select>
                </div>

                <div class="col-md-2 d-flex align-items-end mb-3">
                    <button type="button" class="input-remove remove-time-slot w-100">×</button>
                </div>
            </div>
        </div>
    `;
}



function rebuildTimeSlots() {
    let oldOpen = $('.sync-open-time').first().val() || '';
    let oldClose = $('.sync-close-time').first().val() || '';

    $('#timeSlotsWrapper').html('');

    $('input[name="business_days[]"]:checked').each(function () {
        $('#timeSlotsWrapper').append(makeTimeSlot($(this).val()));
    });

    if (oldOpen) {
        $('.sync-open-time').val(oldOpen);
        firstOpenApplied = true;
    }

    if (oldClose) {
        $('.sync-close-time').val(oldClose);
        firstCloseApplied = true;
    }
}

$('#selectAllDays').on('change', function () {
    $('input[name="business_days[]"]').prop('checked', this.checked);
    rebuildTimeSlots();
});

$(document).on('change', 'input[name="business_days[]"]', function () {
    $('#selectAllDays').prop(
        'checked',
        $('input[name="business_days[]"]').length ===
        $('input[name="business_days[]"]:checked').length
    );

    rebuildTimeSlots();
});

$(document).on('change', '.sync-open-time', function () {
    let val = $(this).val();

    if (!firstOpenApplied && val) {
        $('.sync-open-time').each(function () {
            if (!$(this).val()) {
                $(this).val(val);
            }
        });

        firstOpenApplied = true;
    }
});

$(document).on('change', '.sync-close-time', function () {
    let val = $(this).val();

    if (!firstCloseApplied && val) {
        $('.sync-close-time').each(function () {
            if (!$(this).val()) {
                $(this).val(val);
            }
        });

        firstCloseApplied = true;
    }
});

$(document).on('click', '.remove-time-slot', function () {
    let day = $(this).closest('.time-slot-item').data('day');

    $('input[name="business_days[]"][value="' + day + '"]')
        .prop('checked', false);

    $(this).closest('.time-slot-item').remove();

    $('#selectAllDays').prop(
        'checked',
        $('input[name="business_days[]"]').length ===
        $('input[name="business_days[]"]:checked').length
    );
});

$('#addTimeSlot').on('click', function () {
    if ($('#open24Hours').is(':checked')) {
        return;
    }

    rebuildTimeSlots();
});

/*
|--------------------------------------------------------------------------
| Open 24 Hours
|--------------------------------------------------------------------------
*/
function toggleOpen24Hours() {
    let isOpen24Hours = $('#open24Hours').is(':checked');

    if (isOpen24Hours) {
        $('#timeSlotsWrapper').hide();
        $('#addTimeSlot').hide();

        $('#timeSlotsWrapper')
            .find('select, input')
            .prop('disabled', true);
    } else {
        $('#timeSlotsWrapper').show();
        $('#addTimeSlot').show();

        $('#timeSlotsWrapper')
            .find('select, input')
            .prop('disabled', false);
    }
}

$(document)
    .off('change', '#open24Hours')
    .on('change', '#open24Hours', function () {
        toggleOpen24Hours();
    });

toggleOpen24Hours();



function syncSelectAllDaysOnLoad() {
    let totalDays =
        $('input[name="business_days[]"]').length;

    let checkedDays =
        $('input[name="business_days[]"]:checked').length;

    $('#selectAllDays').prop(
        'checked',
        totalDays > 0 && totalDays === checkedDays
    );
}

syncSelectAllDaysOnLoad();
$('#pincode').on('keyup', function () {
        let pin = $(this).val().trim();

        if (pin.length === 6 && /^\d{6}$/.test(pin)) {
            fetch("https://api.postalpincode.in/pincode/" + pin)
                .then(response => response.json())
                .then(data => {
                    $('#city').val('');
                    $('#state').val('');
                    $('#area').html('<option value="">Select Area</option>');
                    $('#latitude').val('');
                    $('#longitude').val('');

                    if (data[0].Status === "Success" && data[0].PostOffice.length > 0) {
                        let postOffices = data[0].PostOffice;

                        $('#city').val(postOffices[0].District || '');
                        $('#state').val(postOffices[0].State || '');

                        // Location is the same city under another name — keep
                        // it in step instead of making it a second manual
                        // choice.
                        if (typeof window.autoSelectListingLocation === 'function') {
                            window.autoSelectListingLocation(postOffices[0].District || '');
                        }

                        postOffices.forEach(function (office) {
                            $('#area').append(`<option value="${office.Name}">${office.Name}</option>`);
                        });

                        let currentArea = @json(old('area', $listing->area ?? ''));
                        if (currentArea) {
                            $('#area').val(currentArea);
                        }

                        let searchText = `${pin}, ${postOffices[0].District}, ${postOffices[0].State}, India`;

                        fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(searchText)}`)
                            .then(res => res.json())
                            .then(locationData => {
                                if (locationData.length > 0) {
                                    $('#latitude').val(locationData[0].lat || '');
                                    $('#longitude').val(locationData[0].lon || '');
                                }
                            });

                        updateSeoScore();
                    }
                });
        }
    });

    /*
     * The selector here used to be a bare "." — not a valid jQuery selector,
     * so every input/blur/paste that bubbled to document threw
     * "Syntax error, unrecognized expression: ." and killed the rest of the
     * handlers for that event. Matches add.blade.php's binding now.
     *
     * Sanitising is deferred on paste: the handler runs before the clipboard
     * content is in the field, so reading it immediately would clean the old
     * value and leave the pasted one untouched.
     */
    $(document).on('blur paste', '.seo-source, .seo-field', function () {
        const $field = $(this);

        setTimeout(function () {
            sanitizeFieldValue($field);
            updateSeoScore();
            updateSpamDetection();
        }, 0);
    });

    $('#meta_title, #meta_keywords, #meta_description, input[name="name"], #city').on('input', function () {
        updateSeoScore();
        updateSpamDetection();
    });

    $('#generateSeoKeywords').on('click', function () {
        autoGenerateKeywords();
    });

    if ($.fn.summernote && $('.richEditor').length) {
        $('.richEditor').summernote({
            height: 220,
            placeholder: 'Write listing details',
            toolbar: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['table']],
                ['view', ['codeview']]
            ],
            callbacks: {
                /*
                 * Paste used to be blocked outright whenever the clipboard
                 * held a link or a blocked word — silently, with nothing
                 * pasted and no explanation. Now the content is cleaned and
                 * inserted, so paste always does something.
                 */
                onPaste: function (e) {
                    const clipboard = (e.originalEvent || e).clipboardData || window.clipboardData;

                    if (!clipboard) {
                        return;
                    }

                    e.preventDefault();

                    const rawHtml = clipboard.getData('text/html');
                    const rawText = clipboard.getData('text/plain') || '';

                    let markup;

                    if (rawHtml) {
                        markup = sanitizePastedHtml(rawHtml);
                    } else {
                        markup = $('<div>').text(cleanText(rawText)).html().replace(/\r?\n/g, '<br>');
                    }

                    $('.richEditor').summernote('pasteHTML', markup);

                    const original = stripHtml(rawHtml || rawText);
                    const kept = stripHtml(markup);

                    if (original && kept.length < original.length) {
                        showPasteNotice();
                    }

                    setTimeout(function () {
                        updateSeoScore();
                        updateSpamDetection();
                    }, 20);
                },

                /*
                 * No sanitising here. Rewriting the editor's HTML on every
                 * keystroke reset the caret to the top of the box and ate the
                 * space you had just typed, which made the editor unusable.
                 * Content is cleaned once, on submit.
                 */
                onChange: function () {
                    updateSeoScore();
                    updateSpamDetection();
                }
            }
        });

        $(document).on('keydown', '.note-editable', function (e) {
            if ((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'k') {
                e.preventDefault();
                return false;
            }
        });
    }

    $('#listingEditForm').on('submit', function (e) {
        let hasError = false;
        let errorMessage = '';

        $('.').each(function () {
            sanitizeFieldValue($(this));
            let val = $(this).val();
            let error = validateTextContent(val);

            if (error) {
                hasError = true;
                errorMessage = error;
                $(this).focus();
                return false;
            }
        });

        sanitizeSummernoteContent();

        if (!hasError) {
            let detailsValue = '';

            if ($.fn.summernote && $('.richEditor').length) {
                detailsValue = $('.richEditor').summernote('code');
            } else {
                detailsValue = $('#details').val();
            }

            let editorError = validateTextContent(detailsValue);
            if (editorError) {
                hasError = true;
                errorMessage = 'Listing Details: ' + editorError;
            }
        }

        if (hasError) {
            e.preventDefault();
            alert(errorMessage);
            return false;
        }

        // Pack the fields and the photos so the hosting firewall has no upload
        // to inspect, then submit. Reading the images is asynchronous, so this
        // submit is cancelled and the shield re-submits the form when it is
        // done. See the firewall-shield partial.
        e.preventDefault();
        shieldAndSubmit(this);
        return false;
    });

    appendQuickUserModal();
    renderSelectedCategory();
    renderSelectedSubcategories();
    renderSelectedUser();
    updateSeoScore();
    updateSpamDetection();
renderSelectedUser();
renderSelectedCategory();
renderSelectedSubcategories();
updateSeoScore();
updateSpamDetection();
if ($('#location_id').val() && selectedCategory) {
    loadEditLocationSubcategories(false);
}


$(document).ready(function () {

    renderSelectedUser();
    renderSelectedCategory();
    renderSelectedSubcategories();

    if ($('#location_id').val() && selectedCategory) {
        loadEditLocationSubcategories(false);
    }

});
$(document).ready(function () {

    renderSelectedUser();
    renderSelectedCategory();
    renderSelectedSubcategories();

    if ($('#location_id').val() && selectedCategory) {
        loadEditLocationSubcategories(true);
    }

});
})(jQuery);
</script>
<script>
    $(document).on('click', '.imageRemoveBtn', function () {
    let imageName = $(this).data('image');

    $(this).closest('.existing-extra-image').remove();

    $('<input>').attr({
        type: 'hidden',
        name: 'remove_images[]',
        value: imageName
    }).appendTo('#listingEditForm');
});


let selectedExtraFiles = [];
let extraImageDT = new DataTransfer();

$(document).on('click', '.addImage, .openExtraImages', function () {
    $('#extraImagesInput').click();
});

$(document).on('change', '#extraImagesInput', function () {
    let files = Array.from(this.files);

    files.forEach(function (file) {
        selectedExtraFiles.push(file);
    });

    renderExtraImages();
});

function renderExtraImages() {
    $('.new-extra-image').remove();

    extraImageDT = new DataTransfer();

    selectedExtraFiles.forEach(function (file, index) {
        extraImageDT.items.add(file);

        let reader = new FileReader();

        reader.onload = function (e) {
            $('.extra-upload-box').before(`
                <div class="col-md-3 new-extra-image">
                    <div class="image-upload-area mt-3 position-relative">
                        <button type="button"
                                class="btn btn-sm btn-danger text-white position-absolute imageRemove"
                                data-index="${index}">
                            <i class="las la-minus"></i>
                        </button>

                        <img src="${e.target.result}" alt="Image"/>
                    </div>
                </div>
            `);
        };

        reader.readAsDataURL(file);
    });

    document.getElementById('extraImagesInput').files = extraImageDT.files;
}

$(document).on('click', '.imageRemove', function () {
    let index = $(this).data('index');

    selectedExtraFiles.splice(index, 1);

    renderExtraImages();
});

</script>


@endpush

{{-- Auto spelling/grammar correction is switched off: it rewrote business
     names, colony names and details behind the typist's back. The partial and
     its route are left in place, so re-enabling it is just uncommenting this.

@push("js")
@include("admin.listing.partials.auto-spell")
@endpush
--}}
