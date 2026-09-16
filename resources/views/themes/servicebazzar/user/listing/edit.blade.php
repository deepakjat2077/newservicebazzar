@extends($themeView . 'layouts.app')
@section('content')
@section('title','Edit Listing')

<section class="dashboard pt-80 pb-80">
    <div class="container">
        <div class="mb-3 text-right">
            <a href="{{ route('user.listing.index') }}" class="btn btn__base">
                <i class="las la-arrow-left"></i> @lang('My Listing')
            </a>
        </div>

        <div class="card shadow-sm border-0 listing-edit-card">
            <form action="{{ route('user.listing.update',$listing->id) }}" method="POST" enctype="multipart/form-data" id="listingEditForm">
                @csrf

                <div class="card-body">
                    <div class="row">

                        {{-- LEFT IMAGE --}}
                        <div class="col-lg-4 mb-4">
                            <div class="image-upload-area text-center">
                                <img id="preview"
                                     src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
                                     alt="Image"
                                     class="img-fluid rounded">

                                <div class="custom-file mt-3">
                                    <input type="file" name="image" class="custom-file-input upload-image" id="upload">
                                    <label class="pick-image" for="upload">@lang('Upload')</label>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT CONTENT --}}
                        <div class="col-lg-8">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('Business Name')</label>
                                        <input type="text" name="name" class="form-control"
                                               placeholder="@lang('Business Name')"
                                               value="{{ old('name', $listing->name) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('Select Category')</label>
                                        <select class="form-control" name="category_id" required>
                                            <option value="">@lang('Select One')</option>
                                            @foreach($categories as $categoryItem)
                                                <option value="{{ $categoryItem->id }}"
                                                    {{ old('category_id', $listing->category_id) == $categoryItem->id ? 'selected' : '' }}>
                                                    {{ $categoryItem->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('Select Location')</label>
                                        <select class="form-control" name="location_id" required>
                                            <option value="">@lang('Select One')</option>
                                            @foreach($locations as $locationItem)
                                                <option value="{{ $locationItem->id }}"
                                                    {{ old('location_id', $listing->location_id) == $locationItem->id ? 'selected' : '' }}>
                                                    {{ $locationItem->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('Pincode')</label>
                                        <input type="text" id="pincode" name="pincode" class="form-control"
                                               maxlength="6"
                                               placeholder="@lang('Pincode')"
                                               value="{{ old('pincode', $listing->pincode ?? '') }}">
                                    </div>
                                </div>


                              
                             
                             

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('Area')</label>
                                        <select class="form-control" id="area" name="area">
                                            <option value="">@lang('Select Area')</option>
                                            @if(old('area', $listing->area ?? ''))
                                                <option value="{{ old('area', $listing->area ?? '') }}" selected>
                                                    {{ old('area', $listing->area ?? '') }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('City')</label>
                                        <input type="text" id="city" name="city" class="form-control"
                                               value="{{ old('city', $listing->city ?? '') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('State')</label>
                                        <input type="text" id="state" name="state" class="form-control"
                                               value="{{ old('state', $listing->state ?? '') }}" readonly>
                                    </div>
                                </div>

                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $listing->latitude ?? '') }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $listing->longitude ?? '') }}">

                             @if(!empty($listing->address))
<div class="col-md-12">
    <div class="form-group">
        <label>@lang('Address')</label>
        <textarea name="address"
                  class="form-control"
                  rows="3">{{ old('address', $listing->address) }}</textarea>
    </div>
</div>
@endif

                                {{-- CONTACT PERSON --}}
                                <div class="col-md-12">
                                    <hr>
                                    <h5 class="mb-3">@lang('Contact Details')</h5>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('Contact Person')</label>
                                        <div class="phone-group">
                                            <select class="form-control country-code" name="contact_title">
                                                <option value="Mr" {{ old('contact_title', $listing->contact_title ?? '') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                                <option value="Mrs" {{ old('contact_title', $listing->contact_title ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                                <option value="Miss" {{ old('contact_title', $listing->contact_title ?? '') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                                <option value="Dr" {{ old('contact_title', $listing->contact_title ?? '') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                            </select>

                                            <input type="text" class="form-control"
                                                   name="contact_person"
                                                   placeholder="@lang('Contact Person')"
                                                   value="{{ old('contact_person', $listing->contact_person ?? '') }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- MOBILE NUMBERS --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('Mobile Number')</label>
                                        <div id="mobileNumbersWrapper">
                                            @php
                                                $mobileNumbers = old('mobile_numbers', $listing->mobile_numbers ?? []);
                                                $mobileCodes = old('mobile_country_code', $listing->mobile_country_code ?? []);
                                            @endphp

                                            @if(!empty($mobileNumbers) && count($mobileNumbers))
                                                @foreach($mobileNumbers as $i => $mobile)
                                                    <div class="phone-group mobile-number-item">
                                                        <select class="form-control country-code" name="mobile_country_code[]">
                                                            <option value="+91" {{ ($mobileCodes[$i] ?? '+91') == '+91' ? 'selected' : '' }}>+91</option>
                                                        </select>
                                                        <input type="text" class="form-control" name="mobile_numbers[]" value="{{ $mobile }}" placeholder="@lang('Mobile Number')">
                                                        @if($i > 0)
                                                            <button type="button" class="input-remove remove-row">×</button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="phone-group mobile-number-item">
                                                    <select class="form-control country-code" name="mobile_country_code[]">
                                                        <option value="+91">+91</option>
                                                    </select>
                                                    <input type="text" class="form-control" name="mobile_numbers[]" placeholder="@lang('Mobile Number')">
                                                </div>
                                            @endif
                                        </div>
                                        <a href="javascript:void(0)" class="add-link" id="addMobileNumber">+ Add Another Mobile Number</a>
                                    </div>
                                </div>

                                {{-- WHATSAPP NUMBERS --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('WhatsApp Number')</label>
                                        <div id="whatsappNumbersWrapper">
                                            @php
                                                $whatsappNumbers = old('whatsapp_numbers', $listing->whatsapp_numbers ?? []);
                                                $whatsappCodes = old('whatsapp_country_code', $listing->whatsapp_country_code ?? []);
                                            @endphp

                                            @if(!empty($whatsappNumbers) && count($whatsappNumbers))
                                                @foreach($whatsappNumbers as $i => $whatsapp)
                                                    <div class="phone-group whatsapp-number-item">
                                                        <select class="form-control country-code" name="whatsapp_country_code[]">
                                                            <option value="+91" {{ ($whatsappCodes[$i] ?? '+91') == '+91' ? 'selected' : '' }}>+91</option>
                                                        </select>
                                                        <input type="text" class="form-control whatsapp-input" name="whatsapp_numbers[]" value="{{ $whatsapp }}" placeholder="@lang('WhatsApp Number')">
                                                        @if($i > 0)
                                                            <button type="button" class="input-remove remove-row">×</button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="phone-group whatsapp-number-item">
                                                    <select class="form-control country-code" name="whatsapp_country_code[]">
                                                        <option value="+91">+91</option>
                                                    </select>
                                                    <input type="text" class="form-control whatsapp-input" name="whatsapp_numbers[]" placeholder="@lang('WhatsApp Number')">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="sameAsMobile">
                                            <label class="form-check-label checkbox-text" for="sameAsMobile">
                                                Same As Mobile Number
                                            </label>
                                        </div>

                                        <a href="javascript:void(0)" class="add-link" id="addWhatsappNumber">+ Add WhatsApp Number</a>
                                    </div>
                                </div>

                                {{-- LANDLINE --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('Landline Number')</label>
                                        <div id="landlineNumbersWrapper">
                                            @php
                                                $landlineNumbers = old('landline_numbers', $listing->landline_numbers ?? []);
                                                $landlineCodes = old('landline_country_code', $listing->landline_country_code ?? []);
                                            @endphp

                                            @if(!empty($landlineNumbers) && count($landlineNumbers))
                                                @foreach($landlineNumbers as $i => $landline)
                                                    <div class="phone-group landline-number-item">
                                                        <select class="form-control country-code" name="landline_country_code[]">
                                                            <option value="+91" {{ ($landlineCodes[$i] ?? '+91') == '+91' ? 'selected' : '' }}>+91</option>
                                                        </select>
                                                        <input type="text" class="form-control" name="landline_numbers[]" value="{{ $landline }}" placeholder="@lang('Landline Number')">
                                                        @if($i > 0)
                                                            <button type="button" class="input-remove remove-row">×</button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="phone-group landline-number-item">
                                                    <select class="form-control country-code" name="landline_country_code[]">
                                                        <option value="+91">+91</option>
                                                    </select>
                                                    <input type="text" class="form-control" name="landline_numbers[]" placeholder="@lang('Landline Number')">
                                                </div>
                                            @endif
                                        </div>
                                        <a href="javascript:void(0)" class="add-link" id="addLandlineNumber">+ Add Landline Number</a>
                                    </div>
                                </div>

                                {{-- EMAILS --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('Email')</label>
                                        <div id="emailsWrapper">
                                            @php
                                                $extraEmails = old('extra_emails', $listing->extra_emails ?? []);
                                            @endphp

                                            @if(!empty($extraEmails) && count($extraEmails))
                                                @foreach($extraEmails as $i => $email)
                                                    <div class="phone-group email-item">
                                                        <input type="email" class="form-control" name="extra_emails[]" value="{{ $email }}" placeholder="@lang('Email')">
                                                        @if($i > 0)
                                                            <button type="button" class="input-remove remove-row">×</button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="email-item mb-2">
                                                    <input type="email" class="form-control" name="extra_emails[]" placeholder="@lang('Email')">
                                                </div>
                                            @endif
                                        </div>
                                        <a href="javascript:void(0)" class="add-link" id="addAnotherEmail">+ Add Another Email</a>
                                    </div>
                                </div>

                            {{-- BUSINESS TIMINGS --}}
@php
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    $selectedDays = old('business_days', $listing->business_days ?? []);
    $openTimes    = old('open_time', $listing->open_time ?? []);
    $closeTimes   = old('close_time', $listing->close_time ?? []);

    /*
     * Agar model casts set nahi hain aur database me JSON string aa rahi hai,
     * to array me convert kar denge.
     */
    if (is_string($selectedDays)) {
        $selectedDays = json_decode($selectedDays, true) ?: [];
    }

    if (is_string($openTimes)) {
        $openTimes = json_decode($openTimes, true) ?: [];
    }

    if (is_string($closeTimes)) {
        $closeTimes = json_decode($closeTimes, true) ?: [];
    }

    $selectedDays = (array) $selectedDays;
    $openTimes    = (array) $openTimes;
    $closeTimes   = (array) $closeTimes;

    $isOpen24Hours = (bool) old(
        'open_24_hours',
        $listing->open_24_hours ?? false
    );

    /*
     * Kam se kam ek timing row show hogi.
     */
    if (empty($openTimes)) {
        $openTimes = [''];
    }
@endphp

<div class="col-md-12">
    <hr>
    <h5 class="mb-3">@lang('Business Timings')</h5>
</div>

<div class="col-md-12">
    <label class="section-label">
        @lang('Select Days Of The Week')
    </label>

    <div class="weekday-list">
        @foreach($days as $day)
            <div class="weekday-item">
                <input
                    type="checkbox"
                    id="day_{{ $day }}"
                    name="business_days[]"
                    value="{{ $day }}"
                    {{ in_array($day, $selectedDays, true) ? 'checked' : '' }}
                >

                <label for="day_{{ $day }}">
                    {{ $day }}
                </label>
            </div>
        @endforeach
    </div>

    <div id="businessDaysError" class="error-text d-none"></div>

    <div class="form-check mb-3">
        <input
            class="form-check-input"
            type="checkbox"
            id="selectAllDays"
            {{ count(array_intersect($days, $selectedDays)) === count($days) ? 'checked' : '' }}
        >

        <label
            class="form-check-label checkbox-text"
            for="selectAllDays"
        >
            @lang('Select All Days')
        </label>
    </div>

    <div class="form-check mb-3">
        <input
            class="form-check-input"
            type="checkbox"
            id="open24Hours"
            name="open_24_hours"
            value="1"
            {{ $isOpen24Hours ? 'checked' : '' }}
        >

        <label
            class="form-check-label checkbox-text"
            for="open24Hours"
        >
            @lang('Open 24 Hours')
        </label>
    </div>
</div>

<div class="col-md-12">
    <div id="timeSlotsWrapper">
        @foreach($openTimes as $index => $openTime)
            <div class="time-slot-box time-slot-item">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">
                            @lang('Open at')
                        </label>

                        <select
                            class="form-control business-open-time"
                            name="open_time[]"
                            data-selected="{{ $openTime }}"
                            {{ $isOpen24Hours ? 'disabled' : 'required' }}
                        >
                            <option value="">
                                @lang('Select Time')
                            </option>
                        </select>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">
                            @lang('Close at')
                        </label>

                        <select
                            class="form-control business-close-time"
                            name="close_time[]"
                            data-selected="{{ $closeTimes[$index] ?? '' }}"
                            {{ $isOpen24Hours ? 'disabled' : 'required' }}
                        >
                            <option value="">
                                @lang('Select Time')
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end mb-3">
                        @if($index > 0)
                            <button
                                type="button"
                                class="input-remove remove-time-slot w-100"
                            >
                                ×
                            </button>
                        @endif
                    </div>
                </div>

                <div class="time-slot-error error-text d-none"></div>
            </div>
        @endforeach
    </div>

    <a
        href="javascript:void(0)"
        class="add-link"
        id="addTimeSlot"
        style="{{ $isOpen24Hours ? 'display:none;' : '' }}"
    >
        + @lang('Add Another Time Slot')
    </a>
</div>

<div class="col-md-12">
    <div class="form-group">
        <label>@lang('Since (Year)')</label>

        <input
            type="number"
            name="established_year"
            class="form-control"
            min="1900"
            max="{{ date('Y') }}"
            maxlength="4"
            oninput="if(this.value.length > 4) this.value = this.value.slice(0, 4)"
            placeholder="e.g. 2015"
            value="{{ old('established_year', $listing->established_year ?? '') }}"
        >
    </div>
</div>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn__base btn-block">@lang('Update Listing')</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('css')
<style>
    .listing-edit-card{
        border-radius: 16px;
        overflow: hidden;
    }
    .image-upload-area{
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 16px;
        background: #fff;
    }
    .image-upload-area img{
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 12px;
        background: #f8f9fa;
    }
    .pick-image{
        display:inline-block;
        padding:10px 18px;
        background:#0d6efd;
        color:#fff;
        border-radius:8px;
        cursor:pointer;
        margin-bottom:0;
    }
    .pick-image:hover{
        background:#0b5ed7;
        color:#fff;
    }
    .custom-file-input{
        display:none;
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
    .weekday-item{
        position:relative;
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
    .imageRemove,.contactRemove,.removeImage,.faqRemove{
        top: 0;
        right: 0;
        padding: 1px 5px;
    }
    @media(max-width:767px){
        .phone-group{
            flex-wrap:wrap;
        }
    }
</style>
@endpush

@push('js')
<script>
(function($){
    "use strict";

    function readURL(input,thisElement) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var parentElement = thisElement.closest('.image-upload-area');
            var previewElement = parentElement.find('#preview');

            reader.onload = function(e) {
                previewElement.attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).on('change','.upload-image',function() {
        var thisElement = $(this);
        readURL(this,thisElement);
    });

    function createPhoneRow(type, wrapperClass){
        return `
            <div class="phone-group ${wrapperClass}">
                <select class="form-control country-code" name="${type}_country_code[]">
                    <option value="+91">+91</option>
                </select>
                <input type="text" class="form-control" name="${type}_numbers[]" placeholder="${type.replace('_',' ')} Number">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `;
    }

    $('#addMobileNumber').on('click', function(){
        $('#mobileNumbersWrapper').append(createPhoneRow('mobile','mobile-number-item'));
    });

    $('#addWhatsappNumber').on('click', function(){
        $('#whatsappNumbersWrapper').append(createPhoneRow('whatsapp','whatsapp-number-item'));
    });

    $('#addLandlineNumber').on('click', function(){
        $('#landlineNumbersWrapper').append(createPhoneRow('landline','landline-number-item'));
    });

    $('#addAnotherEmail').on('click', function(){
        $('#emailsWrapper').append(`
            <div class="phone-group email-item">
                <input type="email" class="form-control" name="extra_emails[]" placeholder="Email">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `);
    });

    $(document).on('click','.remove-row',function(){
        $(this).closest('.phone-group').remove();
    });

    $('#sameAsMobile').on('change', function(){
        const mobileInput = document.querySelector('input[name="mobile_numbers[]"]');
        const whatsappInput = document.querySelector('.whatsapp-input');

        if (this.checked && mobileInput && whatsappInput) {
            whatsappInput.value = mobileInput.value;
        } else if (whatsappInput) {
            whatsappInput.value = '';
        }
    });

    $('#selectAllDays').on('change', function(){
        $('input[name="business_days[]"]').prop('checked', this.checked);
    });

    $('#addTimeSlot').on('click', function(){
        $('#timeSlotsWrapper').append(`
            <div class="time-slot-box time-slot-item">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Open at</label>
                        <input type="text" class="form-control" name="open_time[]" placeholder="09:00 AM">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Close at</label>
                        <input type="text" class="form-control" name="close_time[]" placeholder="06:00 PM">
                    </div>
                    <div class="col-md-2 d-flex align-items-end mb-3">
                        <button type="button" class="input-remove remove-time-slot w-100">×</button>
                    </div>
                </div>
            </div>
        `);
    });

    $(document).on('click','.remove-time-slot', function(){
        $(this).closest('.time-slot-item').remove();
    });

    var featureI = {{ count($listing->features ?? []) }};
    $('.addFeature').on('click', function(){
        $('.featureElement').append(`
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" name="features[]" class="form-control" placeholder="@lang('Features')">
                        <div class="input-group-append">
                            <button type="button" class="btn__danger text-white input-group-text removeFeature">
                                <i class="las la-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        featureI++;
    });

    $(document).on('click','.removeFeature', function(){
        $(this).closest('.col-md-4').remove();
        featureI--;
    });

    var faqI = {{ count($listing->faq['question'] ?? []) }};
    $('.addFaq').on('click', function(){
        $('.faqElement').append(`
            <div class="col-md-4">
                <div class="card mt-3 position-relative">
                    <button type="button" class="btn btn__danger faqRemove position-absolute text-white">
                        <i class="las la-minus"></i>
                    </button>
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('Question')</label>
                            <input type="text" name="question[]" class="form-control" placeholder="@lang('Question')">
                        </div>
                        <div class="form-group">
                            <label>@lang('Answer')</label>
                            <input type="text" name="answer[]" class="form-control" placeholder="@lang('Answer')">
                        </div>
                    </div>
                </div>
            </div>
        `);
        faqI++;
    });

    $(document).on('click','.faqRemove', function(){
        $(this).closest('.col-md-4').remove();
        faqI--;
    });

    const pincodeInput = document.getElementById("pincode");

    if (pincodeInput) {
        pincodeInput.addEventListener("keyup", function () {
            let pin = this.value.trim();

            if (pin.length === 6 && /^\d{6}$/.test(pin)) {
                fetch("https://api.postalpincode.in/pincode/" + pin)
                    .then(response => response.json())
                    .then(data => {
                        let cityField = document.getElementById("city");
                        let stateField = document.getElementById("state");
                        let areaDropdown = document.getElementById("area");
                        let latitudeField = document.getElementById("latitude");
                        let longitudeField = document.getElementById("longitude");

                        cityField.value = "";
                        stateField.value = "";
                        areaDropdown.innerHTML = '<option value="">Select Area</option>';
                        latitudeField.value = "";
                        longitudeField.value = "";

                        if (data[0].Status === "Success" && data[0].PostOffice.length > 0) {
                            let postOffices = data[0].PostOffice;

                            cityField.value = postOffices[0].District || "";
                            stateField.value = postOffices[0].State || "";

                            postOffices.forEach(function (office) {
                                let option = document.createElement("option");
                                option.value = office.Name;
                                option.textContent = office.Name;
                                areaDropdown.appendChild(option);
                            });

                            let currentArea = @json(old('area', $listing->area ?? ''));
                            if(currentArea){
                                areaDropdown.value = currentArea;
                            }

                            let searchText = `${pin}, ${cityField.value}, ${stateField.value}, India`;

                            fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(searchText)}`)
                                .then(res => res.json())
                                .then(locationData => {
                                    if (locationData.length > 0) {
                                        latitudeField.value = locationData[0].lat || "";
                                        longitudeField.value = locationData[0].lon || "";
                                    }
                                });
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching pincode data:", error);
                    });
            }
        });
    }

})(jQuery);
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timeSlotsWrapper = document.getElementById('timeSlotsWrapper');
    const addTimeSlotButton = document.getElementById('addTimeSlot');
    const open24HoursCheckbox = document.getElementById('open24Hours');
    const selectAllDaysCheckbox = document.getElementById('selectAllDays');
    const businessDaysError = document.getElementById('businessDaysError');

    /*
     * 30-minute interval ke options banayega:
     * 12:00 AM, 12:30 AM ... 11:30 PM
     */
    function createBusinessTimeOptions() {
        let options = '<option value="">Select Time</option>';

        for (let hour = 0; hour < 24; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const period = hour >= 12 ? 'PM' : 'AM';

                let displayHour = hour % 12;

                if (displayHour === 0) {
                    displayHour = 12;
                }

                const formattedHour = String(displayHour).padStart(2, '0');
                const formattedMinute = String(minute).padStart(2, '0');

                const displayTime =
                    formattedHour + ':' + formattedMinute + ' ' + period;

                options +=
                    '<option value="' + displayTime + '">' +
                        displayTime +
                    '</option>';
            }
        }

        return options;
    }

    /*
     * Existing aur new dropdowns me options add karega.
     * data-selected ki saved value ko select bhi karega.
     */
    function initializeBusinessTimeDropdowns(container) {
        const target = container || document;

        target.querySelectorAll(
            '.business-open-time, .business-close-time'
        ).forEach(function (select) {
            if (select.dataset.timeInitialized === '1') {
                return;
            }

            const selectedValue =
                select.dataset.selected || select.value || '';

            select.innerHTML = createBusinessTimeOptions();
            select.dataset.timeInitialized = '1';

            if (selectedValue) {
                select.value = selectedValue;
            }
        });
    }

    function timeToMinutes(timeValue) {
        if (!timeValue) {
            return null;
        }

        const timeParts = timeValue.trim().split(' ');

        if (timeParts.length !== 2) {
            return null;
        }

        const clockParts = timeParts[0].split(':');
        const period = timeParts[1].toUpperCase();

        let hour = parseInt(clockParts[0], 10);
        const minute = parseInt(clockParts[1], 10);

        if (
            Number.isNaN(hour) ||
            Number.isNaN(minute) ||
            !['AM', 'PM'].includes(period)
        ) {
            return null;
        }

        if (period === 'AM' && hour === 12) {
            hour = 0;
        }

        if (period === 'PM' && hour !== 12) {
            hour += 12;
        }

        return (hour * 60) + minute;
    }

    function clearTimeSlotError(slot) {
        const errorBox = slot.querySelector('.time-slot-error');

        slot.querySelectorAll(
            '.business-open-time, .business-close-time'
        ).forEach(function (field) {
            field.classList.remove('field-error');
        });

        if (errorBox) {
            errorBox.textContent = '';
            errorBox.classList.add('d-none');
        }
    }

    function validateTimeSlot(slot) {
        const openSelect =
            slot.querySelector('.business-open-time');

        const closeSelect =
            slot.querySelector('.business-close-time');

        const errorBox =
            slot.querySelector('.time-slot-error');

        clearTimeSlotError(slot);

        if (!openSelect || !closeSelect) {
            return true;
        }

        if (!openSelect.value || !closeSelect.value) {
            if (!openSelect.value) {
                openSelect.classList.add('field-error');
            }

            if (!closeSelect.value) {
                closeSelect.classList.add('field-error');
            }

            if (errorBox) {
                errorBox.textContent =
                    'Please select opening and closing time.';

                errorBox.classList.remove('d-none');
            }

            return false;
        }

        const openMinutes = timeToMinutes(openSelect.value);
        let closeMinutes = timeToMinutes(closeSelect.value);

        if (openMinutes === null || closeMinutes === null) {
            if (errorBox) {
                errorBox.textContent =
                    'Please select a valid business timing.';

                errorBox.classList.remove('d-none');
            }

            return false;
        }

        /*
         * Same open aur close time invalid.
         */
        if (openMinutes === closeMinutes) {
            openSelect.classList.add('field-error');
            closeSelect.classList.add('field-error');

            if (errorBox) {
                errorBox.textContent =
                    'Opening and closing time cannot be the same. Use Open 24 Hours for an all-day business.';

                errorBox.classList.remove('d-none');
            }

            return false;
        }

        /*
         * Close time chhota hai to next day maana jayega.
         * Example: 07:00 PM se 12:00 AM.
         */
        if (closeMinutes < openMinutes) {
            closeMinutes += 24 * 60;
        }

        const duration = closeMinutes - openMinutes;

        if (duration <= 0 || duration >= (24 * 60)) {
            openSelect.classList.add('field-error');
            closeSelect.classList.add('field-error');

            if (errorBox) {
                errorBox.textContent =
                    'Please select a valid business timing.';

                errorBox.classList.remove('d-none');
            }

            return false;
        }

        return true;
    }

    function validateBusinessTimings() {
        const checkedDays = document.querySelectorAll(
            'input[name="business_days[]"]:checked'
        );

        let valid = true;

        if (checkedDays.length === 0) {
            if (businessDaysError) {
                businessDaysError.textContent =
                    'Please select at least one business day.';

                businessDaysError.classList.remove('d-none');
            }

            valid = false;
        } else if (businessDaysError) {
            businessDaysError.textContent = '';
            businessDaysError.classList.add('d-none');
        }

        if (open24HoursCheckbox && open24HoursCheckbox.checked) {
            return valid;
        }

        document.querySelectorAll('.time-slot-item')
            .forEach(function (slot) {
                if (!validateTimeSlot(slot)) {
                    valid = false;
                }
            });

        return valid;
    }

    function toggleOpen24Hours() {
        if (!open24HoursCheckbox || !timeSlotsWrapper) {
            return;
        }

        const isOpen24Hours = open24HoursCheckbox.checked;

        timeSlotsWrapper.style.display =
            isOpen24Hours ? 'none' : '';

        if (addTimeSlotButton) {
            addTimeSlotButton.style.display =
                isOpen24Hours ? 'none' : 'inline-block';
        }

        timeSlotsWrapper.querySelectorAll(
            '.business-open-time, .business-close-time'
        ).forEach(function (field) {
            field.disabled = isOpen24Hours;
            field.required = !isOpen24Hours;

            if (isOpen24Hours) {
                field.classList.remove('field-error');
            }
        });

        timeSlotsWrapper.querySelectorAll('.time-slot-error')
            .forEach(function (errorBox) {
                errorBox.textContent = '';
                errorBox.classList.add('d-none');
            });
    }

    /*
     * Select all days.
     */
    if (selectAllDaysCheckbox) {
        selectAllDaysCheckbox.addEventListener('change', function () {
            document.querySelectorAll(
                'input[name="business_days[]"]'
            ).forEach(function (dayCheckbox) {
                dayCheckbox.checked =
                    selectAllDaysCheckbox.checked;
            });

            if (businessDaysError) {
                businessDaysError.textContent = '';
                businessDaysError.classList.add('d-none');
            }
        });
    }

    /*
     * Individual day change hone par Select All sync hoga.
     */
    document.querySelectorAll(
        'input[name="business_days[]"]'
    ).forEach(function (dayCheckbox) {
        dayCheckbox.addEventListener('change', function () {
            const allDays = document.querySelectorAll(
                'input[name="business_days[]"]'
            );

            const checkedDays = document.querySelectorAll(
                'input[name="business_days[]"]:checked'
            );

            if (selectAllDaysCheckbox) {
                selectAllDaysCheckbox.checked =
                    allDays.length === checkedDays.length;
            }

            if (businessDaysError && checkedDays.length > 0) {
                businessDaysError.textContent = '';
                businessDaysError.classList.add('d-none');
            }
        });
    });

    if (open24HoursCheckbox) {
        open24HoursCheckbox.addEventListener(
            'change',
            toggleOpen24Hours
        );
    }

    /*
     * New timing slot.
     */
    if (addTimeSlotButton) {
        addTimeSlotButton.addEventListener('click', function () {
            if (!timeSlotsWrapper) {
                return;
            }

            const slotHtml = `
                <div class="time-slot-box time-slot-item">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Open at</label>

                            <select
                                class="form-control business-open-time"
                                name="open_time[]"
                                required
                            >
                                <option value="">Select Time</option>
                            </select>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label class="form-label">Close at</label>

                            <select
                                class="form-control business-close-time"
                                name="close_time[]"
                                required
                            >
                                <option value="">Select Time</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button
                                type="button"
                                class="input-remove remove-time-slot w-100"
                            >
                                ×
                            </button>
                        </div>
                    </div>

                    <div
                        class="time-slot-error error-text d-none"
                    ></div>
                </div>
            `;

            timeSlotsWrapper.insertAdjacentHTML(
                'beforeend',
                slotHtml
            );

            initializeBusinessTimeDropdowns(
                timeSlotsWrapper.lastElementChild
            );
        });
    }

    /*
     * Timing slot remove.
     */
    document.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-time-slot')) {
            return;
        }

        const slots =
            document.querySelectorAll('.time-slot-item');

        if (slots.length <= 1) {
            return;
        }

        const slot = event.target.closest('.time-slot-item');

        if (slot) {
            slot.remove();
        }
    });

    /*
     * Dropdown change par error clear.
     */
    document.addEventListener('change', function (event) {
        if (!event.target.matches(
            '.business-open-time, .business-close-time'
        )) {
            return;
        }

        const slot = event.target.closest('.time-slot-item');

        if (slot) {
            clearTimeSlotError(slot);
        }
    });

    /*
     * Edit form submit se pehle validation.
     */
    const editForm = document.getElementById('listingEditForm');

    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            if (!validateBusinessTimings()) {
                event.preventDefault();

                const heading =
                    document.querySelector('#timeSlotsWrapper');

                if (heading) {
                    heading.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
        });
    }

    initializeBusinessTimeDropdowns();
    toggleOpen24Hours();
});
</script>

@endpush