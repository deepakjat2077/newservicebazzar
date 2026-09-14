@extends('admin.layouts.master')

@section('title', 'Add Listing')

@section('content')
<style>
    .rrrr{
        color: red
    }

    .area-manual-toggle{
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Listing</h5>
                <a href="{{ route('admin.listing.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.listing.store') }}" method="POST" enctype="multipart/form-data" id="listingCreateForm">
                @csrf

                <div class="card-body">
                    <div class="row">

                       <div class="col-md-4">

    <div class="form-group">
        <label>Business Image <span class="rrrr">*</span></label>
        <div class="border p-3 rounded text-center">
            <img id="mainPreview"
                 src="{{ asset('assets/images/default.jpg') }}"
                 alt="Business Image"
                 style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

            <div class="custom-file mt-3">
                <input type="file" name="thumb" class="custom-file-input upload-image" id="upload">
                <label class="custom-file-label" for="upload">Choose Business Image</label>
            </div>

            <small class="text-primary d-block mt-2">
                Image will resize into 350x190 px
            </small>
        </div>
    </div>

    {{-- Social Media Links --}}
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
               value="{{ old('website') }}"
               placeholder="https://yourwebsite.com">
    </div>

            <div class="form-group">
                <label>Facebook URL</label>
                <input type="url"
                       name="facebook"
                       class="form-control"
                       value="{{ old('facebook') }}"
                       placeholder="https://facebook.com/yourpage">
            </div>

            <div class="form-group">
                <label>Instagram URL</label>
                <input type="url"
                       name="instagram"
                       class="form-control"
                       value="{{ old('instagram') }}"
                       placeholder="https://instagram.com/yourprofile">
            </div>

            <div class="form-group">
                <label>LinkedIn URL</label>
                <input type="url"
                       name="linkedin"
                       class="form-control"
                       value="{{ old('linkedin') }}"
                       placeholder="https://linkedin.com/company/yourcompany">
            </div>

            <div class="form-group">
                <label>Twitter / X URL</label>
                <input type="url"
                       name="twitter"
                       class="form-control"
                       value="{{ old('twitter') }}"
                       placeholder="https://x.com/yourprofile">
            </div>

            <div class="form-group mb-0">
                <label>YouTube URL</label>
                <input type="url"
                       name="youtube"
                       class="form-control"
                       value="{{ old('youtube') }}"
                       placeholder="https://youtube.com/@yourchannel">
            </div>

        </div>
    </div>
</div>

                        <div class="col-md-8">

                            <h5 class="mb-3">Business Details</h5>

                            <div class="form-group">
                                <label>Business Name <span class="rrrr">*</span></label>
                               <input type="text" name="name" class="form-control seo-source"
       placeholder="Enter Business Name" value="{{ old('name') }}" required>
       
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pincode <span class="rrrr">*</span> </label>
                                        <input type="text" id="pincode" name="pincode" class="form-control"
                                               maxlength="6" placeholder="Enter Pincode"
                                               value="{{ old('pincode') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Since (Year) <span class="rrrr">*</span></label>
                                        <input type="text"
                                               name="established_year"
                                               class="form-control"
                                               maxlength="4"
                                               pattern="[0-9]{4}"
                                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                               placeholder="e.g. 2015"
                                               value="{{ old('established_year') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Plot No. / Shop No. / Floor</label>
                                <input type="text" name="plot_no" class="form-control "
                                       placeholder="Enter Plot No. / Shop No. / Floor" value="{{ old('plot_no') }}">
                            </div>

                            <div class="form-group">
                                <label>Building Name / Market / Colony</label>
                                <input type="text" name="building_name" class="form-control "
                                       placeholder="Enter Building Name / Market / Colony" value="{{ old('building_name') }}">
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Street / Road Name</label>
                                    <input type="text" name="street_name" class="form-control "
                                           placeholder="Enter Street / Road Name" value="{{ old('street_name') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Landmark</label>
                                    <input type="text" name="landmark" class="form-control "
                                           placeholder="Enter Landmark" value="{{ old('landmark') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Area <span class="rrrr">*</span></label>
                                    <a href="javascript:void(0)" id="areaManualToggle" class="area-manual-toggle">+ Add manually</a>
                                </div>
                                <select class="form-control" id="area" name="area">
                                    <option value="">Select Area </option>
                                    @if(old('area'))
                                        <option value="{{ old('area') }}" selected>{{ old('area') }}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="form-group" style="position:relative;">
                                <label>Location <span class="rrrr">*</span></label>
                               <select name="location_id"
        id="location_id"
        class="form-control select2-search"
        data-placeholder="Select Location"
        required>
                                    <option value="">Select Location </option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small id="locationAutoNote" class="form-text text-success d-none"></small>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>City</label>
                                    <input type="text" id="city" name="city" class="form-control seo-source"
                                           placeholder="City" value="{{ old('city') }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>State</label>
                                    <input type="text" id="state" name="state" class="form-control"
                                           placeholder="State" value="{{ old('state') }}" readonly>
                                </div>
                            </div>

                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                          <input type="hidden" name="full_address" id="full_address" value="">
<input type="hidden" name="place_id" id="place_id" value="">
                            <hr>

                            <h5 class="mb-3">Contact Details</h5>

                            <div class="form-group">
                                <label>Contact Person <span class="rrrr">*</span></label>
                                <div class="phone-group">
                                    <select class="form-control country-code" name="contact_title">
                                        <option value="Mr" {{ old('contact_title') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                        <option value="Mrs" {{ old('contact_title') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                        <option value="Miss" {{ old('contact_title') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                        <option value="Dr" {{ old('contact_title') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                    </select>

                                    <input type="text" class="form-control "
                                           name="contact_person"
                                           placeholder="Contact Person"
                                           value="{{ old('contact_person') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Mobile Number</label>
                                <small class="text-muted d-block mb-1">At least one of Mobile / WhatsApp / Landline is required. A Mobile Number blocks Landline Number.</small>
                                <div id="mobileNumbersWrapper">
                                    @php
                                        $mobileNumbers = old('mobile_numbers', ['']);
                                        $mobileCodes = old('mobile_country_code', ['+91']);
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
                                        $whatsappNumbers = old('whatsapp_numbers', ['']);
                                        $whatsappCodes = old('whatsapp_country_code', ['+91']);
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

                          <div class="form-group" id="landlineGroup">
    <label>Landline Number</label>

    <div id="landlineNumbersWrapper">
        <div class="phone-group landline-number-item">
            <select class="form-control country-code" name="landline_country_code[]">
                <option value="+91">+91</option>
            </select>

            <input type="text"
                   class="form-control"
                   name="landline_numbers[]"
                   id="landlineNumberInput"
                   placeholder="Landline Number">
        </div>
    </div>
    <small class="text-danger d-none" id="landlineBlockedHint">Remove the Mobile Number to add a Landline Number.</small>

    <a href="javascript:void(0)"
       class="add-link"
       id="addLandlineNumber">
       + Add Another Landline Number
    </a>
</div>

                            <div class="form-group">
                                <label>Email <span class="rrrr">*</span></label>
                                <div id="emailsWrapper">
                                    @php
                                        $extraEmails = old('extra_emails', ['']);
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
                                <label>Add User <span class="rrrr">*</span></label>
                                <input type="text" id="userSearchInput" class="form-control smart-input"
                                       placeholder="Search by username, name, email or mobile">
                                <div class="suggestion-dropdown d-none" id="userSuggestionBox"></div>
                                <div id="selectedUserWrap" class="mt-2"></div>
                            </div>

                            <hr>

                            <h5 class="mb-3">Category Details</h5>

                            <div class="form-group smart-picker" id="categoryPicker">
                                <label>Main Category <span class="rrrr">*</span></label>
                                <input type="text" id="categorySearchInput" class="form-control smart-input"
                                       placeholder="Type category name"
                                       value="{{ old('category_name') }}">
                                <div class="suggestion-dropdown d-none" id="categorySuggestionBox"></div>
                                <div id="selectedCategoryWrap" class="mt-2"></div>
                            </div>

                            <div class="form-group smart-picker mt-4" id="subcategoryPicker">
                                <label>Sub Categories <span class="rrrr">*</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="selectAllSubcategoriesBtn" style="display:none;">
                                        <i class="fas fa-check-double"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="unselectAllSubcategoriesBtn" style="display:none;">
                                        <i class="fas fa-times-circle"></i> Unselect All
                                    </button>
                                </label>
                                <input type="text" id="subcategorySearchInput" class="form-control smart-input"
                                       placeholder="Type sub category name"
                                       {{ old('category_id') ? '' : 'disabled' }}>
                                <div class="suggestion-dropdown d-none" id="subcategorySuggestionBox"></div>
                                <div id="selectedSubcategoryWrap" class="mt-2"></div>
                            </div>

                            <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id') }}">
                            <div id="userHiddenInputs"></div>
                            <div id="subcategoryHiddenInputs"></div>

                        </div>

                       <div class="col-md-12 mt-2 mb-4">
    <div class="bg__dark p-2 text-white d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Extra Images <span class="rrrr">*</span> <small class="fw-normal">(min. 2)</small></h5>

        <button type="button" class="btn btn-success text-white addImage">
            <i class="las la-plus"></i> Add New
        </button>
    </div>

    <div class="row imageElement">
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
                                    $features = old('features', ['']);
                                @endphp

                                @foreach($features as $i => $feature)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" name="features[]" class="form-control  seo-source feature-input" value="{{ $feature }}" placeholder="Features" required>
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
    <h5 class="mb-3">Business Timings <span class="rrrr">*</span></h5>
</div>

<div class="col-md-12">
    <label class="section-label">Select Days Of The Week</label>
    <div class="weekday-list">
        @php
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $selectedDays = old('business_days', []);
        @endphp

        @foreach($days as $day)
            <div class="weekday-item">
                <input type="checkbox"
                       id="day_{{ $day }}"
                       name="business_days[]"
                       value="{{ $day }}"
                       {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                <label for="day_{{ $day }}">{{ $day }}</label>
            </div>
        @endforeach
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="selectAllDays">
        <label class="form-check-label checkbox-text" for="selectAllDays">
            Select All Days
        </label>
    </div>
    <div class="form-check mb-3">
    <input
        class="form-check-input"
        type="checkbox"
        id="open24Hours"
        name="open_24_hours"
        value="1"
        {{ old('open_24_hours') ? 'checked' : '' }}
    >

    <label class="form-check-label checkbox-text" for="open24Hours">
        Open 24 Hours
    </label>
</div>
</div>

<div class="col-md-12">
    @php
        $openTimes = [''];
        $closeTimes = old('close_time', ['']);

        $timeOptions = [];
        for ($h = 0; $h < 24; $h++) {
            foreach (['00', '30'] as $m) {
                $timeOptions[] = date('h:i A', strtotime(sprintf('%02d:%s', $h, $m)));
            }
        }
    @endphp

    {{--
        Deliberately empty — see the matching comment in listing/edit.blade.php.
        generateSlotsByDays() builds every row itself on page load (from
        editTimingSeed if this were an edit, or from scratch here), and only
        its rows carry the data-day/timing_days[] the per-day JS depends on.
        A single dead row used to sit here instead ($openTimes = ['']), so
        the very first generateSlotsByDays() call saw the wrapper already
        non-empty and skipped its own seeding — same bug as the edit page,
        just with nothing saved yet to lose here.
    --}}
    <div id="timeSlotsWrapper"></div>

    {{-- "+ Add Shift" now sits on each day's own row (2 shifts per day —
         e.g. 9-1, lunch break, 5-9) instead of one global link here; kept
         as an empty, hidden anchor only so toggleOpen24Hours()'s existing
         $('#addTimeSlot') lookup keeps working without touching that
         function. --}}
    <a href="javascript:void(0)" class="d-none" id="addTimeSlot"></a>
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
                                    $faqQuestions = old('question', ['']);
                                    $faqAnswers = old('answer', ['']);
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
                                       value="{{ old('meta_title') }}">
                                <small class="text-muted" id="metaTitleCount">0 / 60 characters</small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control  seo-field"
                                       placeholder="e.g. plumber, electrician, jaipur"
                                       value="{{ old('meta_keywords') }}">
                                <small class="text-danger d-block">
                                    Adult, illegal or link-based content is not allowed.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea name="meta_description" id="meta_description" class="form-control  seo-field" rows="3"
                                          placeholder="Enter Meta Description">{{ old('meta_description') }}</textarea>
                                <small class="text-muted" id="metaDescriptionCount">0 / 160 characters</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-2">
                            <div class="form-group">
                                <label>Listing Details</label>
                                {{-- data-editor="manual": this form builds its own Summernote
                                     instance further down, with paste sanitising and the SEO
                                     score hooks. app.js skips anything marked manual. --}}
                                <textarea class="richEditor" data-editor="manual" rows="6" name="details" id="details">{{ old('details') }}</textarea>
                                <small class="text-danger d-block mt-2">
                                    Adult, illegal or link-based content is not allowed.
                                </small>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.listing.index') }}" class="btn btn-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveListingBtn">
    <i class="fas fa-save"></i> Save Listing
</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.listing.partials.session-keepalive', ['keepAliveForm' => '#listingCreateForm'])
@include('admin.listing.partials.csrf-live', ['tokenForm' => '#listingCreateForm'])
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
</style>
@endpush

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
        : null;

    $selectedSubcategoriesJson = collect(old('subcategory_ids', []))->map(function ($id) use ($subcategories) {
        $sub = $subcategories->firstWhere('id', $id);
        return $sub ? [
            'id' => $sub->id,
            'name' => $sub->name,
            'category_id' => $sub->category_id,
        ] : null;
    })->filter()->values();
@endphp

@push('js')
<script>
function setRichEditorContent(selector, content) {
    let $textarea = $(selector);
    content = content || '';

    $textarea.val(content);

    // Summernote keeps its own copy of the markup, so writing the textarea
    // alone would be discarded the moment the editor syncs back.
    if ($.fn.summernote && $textarea.next('.note-editor').length) {
        $textarea.summernote('code', content);
    }
}
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

    let selectedUser = null;
    let selectedCategory = @json($selectedCategoryJson ? ['id' => $selectedCategoryJson->id, 'name' => $selectedCategoryJson->name] : null);
    let selectedSubcategories = @json($selectedSubcategoriesJson);

    let initialCategories = @json($categoriesJson);
    let initialSubcategories = @json($subcategoriesJson);
let locationFilteredSubcategories = [];
let subcategoryAjaxRequest = null;
    let aiTimer = null;
    let aiRequestInProgress = false;
    let lastAiPayloadHash = '';

    let userEdited = {
        meta_title: false,
        meta_keywords: false,
        meta_description: false,
        details: false,
        faq: false
    };

   function debounce(func, delay = 300) {
    let timer;

    return function (...args) {
        clearTimeout(timer);

        timer = setTimeout(() => {
            func.apply(this, args);
        }, delay);
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
   * summernote('code', ...) re-renders the whole editable area and drops the
   * cursor back to the start, so calling it while someone is typing makes
   * the box impossible to write in.
   */
  function sanitizeSummernoteContent() {
    if (!$.fn.summernote || !$('.richEditor').length) return;

    let content = $('.richEditor').summernote('code');
    let cleaned = cleanText(content);

    if (content !== cleaned) {
        $('.richEditor').summernote('code', cleaned);
    }
}

/** Small, non-blocking note when a paste had something stripped out of it. */
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

    function getSelectedSubcategoryNames() {
        return selectedSubcategories.map(item => item.name || '').filter(Boolean);
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
            let reader = new FileReader();
            let parentElement = thisElement.closest('.image-upload-area, .border');
            let previewElement = parentElement.find('#preview, #mainPreview').first();

            reader.onload = function (e) {
                previewElement.attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function renderSelectedUser() {
        let html = '';
        let hidden = '';

        if (selectedUser) {
            html = `
                <span class="tag-pill selected">
                    ${escapeHtml(selectedUser.name)}
                    <span class="remove-tag remove-selected-user" data-id="${selectedUser.id}">&times;</span>
                </span>
            `;
            hidden = `<input type="hidden" name="user_id" value="${selectedUser.id}">`;
        }

        $('#selectedUserWrap').html(html);
        $('#userHiddenInputs').html(hidden);
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

       let locationId = $('#location_id').val();

if (selectedCategory && locationId) {
    $('#subcategorySearchInput').prop('disabled', false);
} else {
    $('#subcategorySearchInput')
        .prop('disabled', true)
        .val('');

    $('#subcategorySuggestionBox')
        .addClass('d-none')
        .html('');
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

    function updateSubcategoryButtonVisibility() {
        // Show "Unselect All" only if there are selected subcategories
        if (selectedSubcategories.length > 0) {
            $('#unselectAllSubcategoriesBtn').show();
        } else {
            $('#unselectAllSubcategoriesBtn').hide();
        }

        // Show "Select All" only if there are available subcategories AND not all are selected
        if (locationFilteredSubcategories.length > 0 &&
            selectedSubcategories.length < locationFilteredSubcategories.length) {
            $('#selectAllSubcategoriesBtn').show();
        } else if (locationFilteredSubcategories.length > 0 && selectedSubcategories.length === 0) {
            $('#selectAllSubcategoriesBtn').show();
        } else if (locationFilteredSubcategories.length === 0) {
            $('#selectAllSubcategoriesBtn').hide();
        }
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
                <div class="suggestion-meta">Click karke naya user banao</div>
            </div>
        `;

        $('#userSuggestionBox').html(html).removeClass('d-none');
    }

    function renderCategoryDropdown(list) {
        let html = '';

        list.forEach(cat => {
            html += `
                <div class="suggestion-item add-category-item"
                     data-id="${cat.id}"
                     data-name="${escapeHtml(cat.name)}">
                    <div class="suggestion-name">${escapeHtml(cat.name)}</div>
                </div>
            `;
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

    function hasMinimumAiData() {
        let name = $('input[name="name"]').val().trim();
        let categoryId = $('#category_id').val();
        let city = $('#city').val().trim();
        let state = $('#state').val().trim();
        return !!(name && categoryId && city && state);
    }

    function getAiPayload() {
        return {
            _token: listingCsrfToken(),
            name: $('input[name="name"]').val(),
            pincode: $('#pincode').val(),
            established_year: $('input[name="established_year"]').val(),
            plot_no: $('input[name="plot_no"]').val(),
            building_name: $('input[name="building_name"]').val(),
            street_name: $('input[name="street_name"]').val(),
            landmark: $('input[name="landmark"]').val(),
            area: $('#area').val(),
            location_id: $('select[name="location_id"]').val(),
            city: $('#city').val(),
            state: $('#state').val(),
            category_id: $('#category_id').val(),
            category_name: $('#categorySearchInput').val(),
            subcategories: getSelectedSubcategoryNames(),
            business_days: $('input[name="business_days[]"]:checked').map(function () {
                return $(this).val();
            }).get(),
            open_time: $('select[name="open_time[]"]').map(function () {
                return $(this).val();
            }).get(),
            close_time: $('select[name="close_time[]"]').map(function () {
                return $(this).val();
            }).get()
        };
    }

    function hashPayload(payload) {
        return JSON.stringify(payload);
    }

    function fillFaqFields(faqs) {
        if (userEdited.faq) return;
        if (!Array.isArray(faqs)) return;

        let html = '';

        if (!faqs.length) return;

        faqs.forEach(function (faq, i) {
            html += `
                <div class="col-md-4">
                    <div class="card mt-3 position-relative">
                        ${i > 0 ? '<button type="button" class="btn btn-danger faqRemove position-absolute text-white"><i class="las la-minus"></i></button>' : ''}
                        <div class="card-body">
                            <div class="form-group">
                                <label>Question</label>
                                <input type="text" name="question[]" class="form-control faq-question" value="${escapeHtml(faq.question || '')}" placeholder="Question">
                            </div>
                            <div class="form-group">
                                <label>Answer</label>
                                <input type="text" name="answer[]" class="form-control faq-answer" value="${escapeHtml(faq.answer || '')}" placeholder="Answer">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('.faqElement').html(html);
    }

    function applyAiResponse(data) {
        if (!userEdited.meta_title) {
            $('#meta_title').val(data.meta_title || '');
        }

        if (!userEdited.meta_keywords) {
            $('#meta_keywords').val(data.meta_keywords || '');
        }

        if (!userEdited.meta_description) {
            $('#meta_description').val(data.meta_description || '');
        }

       if (!userEdited.details) {
    setRichEditorContent('#details', data.details || '');
}
        fillFaqFields(data.faqs || []);
        updateSeoScore();
        updateSpamDetection();
    }

    function autoGenerateAiContent() {
        if (!hasMinimumAiData()) return;
        if (aiRequestInProgress) return;

        let payload = getAiPayload();
        let payloadHash = hashPayload(payload);

        if (payloadHash === lastAiPayloadHash) return;

        aiRequestInProgress = true;

        $.ajax({
            url: "{{ route('admin.listing.ai.generate') }}",
            type: "POST",
            dataType: "json",
            data: payload,
            // Give up before the server in front of PHP does. Without this the
            // request hung until LiteSpeed dropped it, and the admin was shown
            // a raw 408 for something the form recovers from silently.
            timeout: 60000,
            success: function (res) {
                if (res && res.status && res.data) {
                    lastAiPayloadHash = payloadHash;
                    applyAiResponse(res.data);
                }
            },
            error: function (xhr) {
                console.log('AI generate error:', xhr.responseText);
            },
            complete: function () {
                aiRequestInProgress = false;
            }
        });
    }

    function triggerAutoAiGenerate() {
        clearTimeout(aiTimer);
        aiTimer = setTimeout(function () {
            autoGenerateAiContent();
        }, 1200);
    }

  function resetTimeSlots() {
    $('#timeSlotsWrapper').html('');
}


/*
|--------------------------------------------------------------------------
| Time dropdown options
|--------------------------------------------------------------------------
*/
function getTimeOptionsHtml(selectedValue = '', type = 'open') {

    let placeholder = type === 'open'
        ? 'Select Opening Time'
        : 'Select Closing Time';

    let html = `<option value="">${placeholder}</option>`;

    let timeOptions = @json($timeOptions);

    timeOptions.forEach(function (time) {

        let selected = String(selectedValue) === String(time)
            ? 'selected'
            : '';

        html += `
            <option value="${time}" ${selected}>
                ${time}
            </option>
        `;
    });

    return html;
}


/*
|--------------------------------------------------------------------------
| Create one day timing row
|--------------------------------------------------------------------------
*/
function appendDayTimeSlot(
    day,
    openValue = '',
    closeValue = '',
    isManual = false,
    shiftIndex = 0,
    insertAfter = null
) {

    /*
     * Shift 2+ ("2nd shift", "3rd shift"...) is how a day with a lunch
     * break — open 9-1, closed, open 5-9 — gets represented: one row per
     * shift, all sharing the same day and all going into open_time[] /
     * close_time[] / timing_days[] at the same array index, so the day
     * repeats there on purpose. See the matching comment on $businessDays
     * in Admin\ListingController.
     */
    let dayLabel = isManual
        ? 'Extra Time Slot'
        : (shiftIndex > 0 ? `${day} (Shift ${shiftIndex + 1})` : day);

    let $row = $(`
        <div class="time-slot-box time-slot-item border rounded p-3 mb-3"
             data-day="${day || ''}"
             data-manual="${isManual ? '1' : '0'}"
             data-shift-index="${shiftIndex}">

            <input type="hidden"
                   name="timing_days[]"
                   value="${day || ''}">

            <div class="row">

                <div class="col-md-2 mb-3">
                    <label>Day</label>

                    <input type="text"
                           class="form-control"
                           value="${dayLabel}"
                           readonly>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Open at</label>

                    <select class="form-control day-open-time"
                            name="open_time[]">

                        ${getTimeOptionsHtml(openValue, 'open')}
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Close at</label>

                    <select class="form-control day-close-time"
                            name="close_time[]">

                        ${getTimeOptionsHtml(closeValue, 'close')}
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end mb-3 time-slot-actions">

                    ${
                        !isManual
                            ? `
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm add-day-shift mr-2"
                                        data-day="${day || ''}"
                                        title="Add a second shift for ${day} — e.g. lunch-break hours">
                                    + Add Shift
                                </button>
                            `
                            : ''
                    }

                    ${
                        (isManual || shiftIndex > 0)
                            ? `
                                <button type="button"
                                        class="btn btn-danger btn-sm remove-time-slot">
                                    ×
                                </button>
                            `
                            : ''
                    }

                </div>
            </div>
        </div>
    `);

    if (insertAfter && insertAfter.length) {
        insertAfter.after($row);
    } else {
        $('#timeSlotsWrapper').append($row);
    }
}


/*
|--------------------------------------------------------------------------
| Generate one timing row for each selected day
|--------------------------------------------------------------------------
*/
function generateSlotsByDays() {

    /*
     * Open 24 Hours checked hai to rows generate nahi hongi.
     */
    if ($('#open24Hours').is(':checked')) {
        return;
    }

    /*
     * Existing day-wise values save kar lo (sabhi shifts, ek din ke 2 ho
     * sakte hain), taaki checkbox change hone par entered timing delete na
     * ho.
     */
    let existingValues = {};

    $('#timeSlotsWrapper .time-slot-item').each(function () {

        let day = String($(this).data('day') || '');

        if (!day) {
            return;
        }

        if (!existingValues[day]) {
            existingValues[day] = [];
        }

        existingValues[day].push({
            open: $(this).find('.day-open-time').val() || '',
            close: $(this).find('.day-close-time').val() || ''
        });
    });

    let selectedDays = $('input[name="business_days[]"]:checked')
        .map(function () {
            return $(this).val();
        })
        .get();

    resetTimeSlots();

    if (selectedDays.length === 0) {
        return;
    }

    selectedDays.forEach(function (day) {

        let shifts = existingValues[day] && existingValues[day].length
            ? existingValues[day]
            : [{ open: '', close: '' }];

        shifts.forEach(function (shift, shiftIndex) {
            appendDayTimeSlot(
                day,
                shift.open,
                shift.close,
                false,
                shiftIndex
            );
        });
    });
}

    function appendFaq(question = '', answer = '') {
        $('.faqElement').append(`
            <div class="col-md-4">
                <div class="card mt-3 position-relative">
                    <button type="button" class="btn btn-danger faqRemove position-absolute text-white">
                        <i class="las la-minus"></i>
                    </button>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Question</label>
                            <input type="text" name="question[]" class="form-control faq-question" value="${escapeHtml(question)}" placeholder="Question">
                        </div>
                        <div class="form-group">
                            <label>Answer</label>
                            <input type="text" name="answer[]" class="form-control faq-answer" value="${escapeHtml(answer)}" placeholder="Answer">
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    function makePhoneRow(type) {
        return `
            <div class="phone-group ${type}-number-item">
                <select class="form-control country-code" name="${type}_country_code[]">
                    <option value="+91" selected>+91</option>
                </select>
                <input type="text" class="form-control ${type === 'whatsapp' ? 'whatsapp-input' : ''}" name="${type}_numbers[]" placeholder="${type.charAt(0).toUpperCase() + type.slice(1)} Number">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `;
    }

    function makeEmailRow() {
        return `
            <div class="phone-group email-item">
                <input type="email" class="form-control" name="extra_emails[]" placeholder="Email">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `;
    }

    // image preview
    $(document).on('change', '.upload-image', function () {
        readURL(this, $(this));
    });

    // category search
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

    // subcategory search
$('#subcategorySearchInput').on('focus keyup', debounce(function () {
    if (!selectedCategory) {
        $('#subcategorySuggestionBox')
            .html(
                '<div class="suggestion-empty">' +
                'Please select category first' +
                '</div>'
            )
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

    // Select All Subcategories button
    $('#selectAllSubcategoriesBtn').on('click', function (e) {
        e.preventDefault();
        if (locationFilteredSubcategories.length === 0) return;

        // Add all available subcategories that aren't already selected
        locationFilteredSubcategories.forEach(sub => {
            if (!selectedSubcategories.some(s => s.id === sub.id)) {
                selectedSubcategories.push(sub);
            }
        });

        renderSelectedSubcategories();
        updateSubcategoryButtonVisibility();
        $('#subcategorySearchInput').val('').trigger('keyup');
    });

    // Unselect All Subcategories button
    $('#unselectAllSubcategoriesBtn').on('click', function (e) {
        e.preventDefault();
        selectedSubcategories = [];
        renderSelectedSubcategories();
        updateSubcategoryButtonVisibility();
        $('#subcategorySearchInput').val('').trigger('keyup');
    });

    // user search
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
        let last_name = ($('#quick_last_name').val() || '').trim();
        let email = ($('#quick_email').val() || '').trim();
        let password = ($('#quick_password').val() || '').trim();
        let mobile = getPrimaryMobile();
        let address = buildUserAddress();

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
                _token: listingCsrfToken(),
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
                console.log(xhr.responseText);
                alert('Error creating user');
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

    let locationId = $('#location_id').val();

    if (!locationId) {
        $('#subcategorySearchInput').prop('disabled', true);
        return;
    }

    $('#subcategorySearchInput')
        .prop('disabled', true)
        .attr('placeholder', 'Loading sub categories...');

    $.ajax({
        url: "{{ route('admin.listing.used.subcategories') }}",
        type: "GET",
        dataType: "json",
        data: {
            location_id: locationId,
            category_id: selectedCategory.id
        },

        success: function (res) {
            locationFilteredSubcategories = Array.isArray(res.data)
                ? res.data
                : [];

            $('#subcategorySearchInput')
                .prop('disabled', false)
                .attr('placeholder', 'Type sub category name');

            renderSubcategoryDropdown(
                locationFilteredSubcategories
            );

            // Update button visibility based on subcategory state
            updateSubcategoryButtonVisibility();
        },

        error: function (xhr) {
            console.log(xhr.responseText);

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
        }
    });

    updateSeoScore();
    triggerAutoAiGenerate();
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
        updateSubcategoryButtonVisibility();
        renderSubcategoryDropdown(locationFilteredSubcategories);
        triggerAutoAiGenerate();
    });

    /**
     * Turn a failed quick-add into something the admin can act on: the
     * validation message where there is one, the session/permission problem
     * where that is what happened, and a plain note otherwise.
     */
    function subcategoryErrorMessage(xhr) {
        if (xhr.status === 0) {
            return 'Sub category add nahi hui — network connection टूट गया. Dobara try karein.';
        }

        if (xhr.status === 419 || xhr.status === 401) {
            return 'Aapka session expire ho gaya hai. Page reload karke dobara login karein.';
        }

        if (xhr.status === 403) {
            return 'Sub category add karne ki permission nahi hai.';
        }

        var res = xhr.responseJSON;

        if (res && res.errors) {
            var first = Object.keys(res.errors)[0];
            if (first && res.errors[first] && res.errors[first][0]) {
                return res.errors[first][0];
            }
        }

        if (res && res.message) {
            return res.message;
        }

        return 'Sub category add nahi hui (error ' + xhr.status + '). Thodi der baad try karein.';
    }

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
                _token: listingCsrfToken(),
                name: name,
                category_id: categoryId
            },
            beforeSend: function () {
                $item.data('busy', true)
                    .css('pointer-events', 'none')
                    .html('<i class="fas fa-spinner fa-spin"></i> Adding "' + name + '"…');
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

let alreadyInLocationList = locationFilteredSubcategories.some(
    x => String(x.id) === String(newSub.id)
);

if (!alreadyInLocationList) {
    locationFilteredSubcategories.push(newSub);
}

                    let alreadySelected = selectedSubcategories.some(x => String(x.id) === String(newSub.id));
                    if (!alreadySelected) {
                        selectedSubcategories.push(newSub);
                    }

                    renderSelectedSubcategories();
                    updateSubcategoryButtonVisibility();
                    $('#subcategorySearchInput').val('');
                    $('#subcategorySuggestionBox').addClass('d-none').html('');
                    triggerAutoAiGenerate();
                } else {
                    alert(res.message || 'Sub category add nahi hui');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                // A bare "Error adding subcategory" gave the admin nothing to
                // act on and nothing to report, so say what actually came back.
                alert(subcategoryErrorMessage(xhr));
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
        $('#categorySearchInput').val('');
        $('#subcategorySearchInput').val('').prop('disabled', true);
        $('#selectAllSubcategoriesBtn').hide();
        $('#unselectAllSubcategoriesBtn').hide();
        renderSelectedCategory();
        renderSelectedSubcategories();
        updateSeoScore();
        triggerAutoAiGenerate();
    });

    $(document).on('click', '.remove-selected-subcategory', function () {
        let id = $(this).data('id');
        selectedSubcategories = selectedSubcategories.filter(item => String(item.id) !== String(id));
        renderSelectedSubcategories();
        updateSubcategoryButtonVisibility();
        renderSubcategoryDropdown(locationFilteredSubcategories);
        triggerAutoAiGenerate();
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

    $('.imageElement').html('');
    $('.imageElement').append(`
    <div class="col-md-3 extra-upload-box">
        <div class="image-upload-area mt-3 position-relative openExtraImages">
            <img src="{{ asset('assets/images/default.jpg') }}" alt="Image"/>
            <div class="pick-image">Upload Images</div>
        </div>
    </div>
`);
    extraImageDT = new DataTransfer();

    selectedExtraFiles.forEach(function (file, index) {

        extraImageDT.items.add(file);

        let reader = new FileReader();

        reader.onload = function (e) {
            $('.imageElement').append(`
                <div class="col-md-3">
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

    // feature add/remove
    $(document).on('click', '.addFeature', function () {
        let featureElement = `
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" name="features[]" class="form-control seo-source feature-input" placeholder="Features">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger text-white input-group-text removeFeature">
                                <i class="las la-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('.featureElement').append(featureElement);
    });

    $(document).on('click', '.removeFeature', function () {
        $(this).closest('.col-md-4').remove();
        triggerAutoAiGenerate();
    });

    // phone/email rows
    $('#addMobileNumber').on('click', function () {
        $('#mobileNumbersWrapper').append(makePhoneRow('mobile'));
    });

    $('#addWhatsappNumber').on('click', function () {
        $('#whatsappNumbersWrapper').append(makePhoneRow('whatsapp'));
    });

    $('#addLandlineNumber').on('click', function () {
        $('#landlineNumbersWrapper').append(makePhoneRow('landline'));
    });

    /*
     * A Mobile Number blocks Landline Number — enforced authoritatively on
     * the server (store() rejects both being filled), this is just the
     * live UX: the moment a Mobile Number is typed, the Landline group is
     * disabled and cleared so the admin never fills in a combination the
     * server will reject anyway. Delegated because mobile rows are added
     * dynamically (see makePhoneRow/addMobileNumber above).
     */
    function toggleLandlineForMobile() {
        const hasMobile = $('input[name="mobile_numbers[]"]').toArray()
            .some(function (el) { return $(el).val().trim() !== ''; });

        $('#landlineNumbersWrapper input[name="landline_numbers[]"]').prop('disabled', hasMobile);
        $('#landlineNumbersWrapper select[name="landline_country_code[]"]').prop('disabled', hasMobile);
        $('#addLandlineNumber').toggleClass('disabled', hasMobile).css('pointer-events', hasMobile ? 'none' : '');
        $('#landlineBlockedHint').toggleClass('d-none', !hasMobile);

        if (hasMobile) {
            $('#landlineNumbersWrapper input[name="landline_numbers[]"]').val('');
        }
    }

    $(document).on('input', 'input[name="mobile_numbers[]"]', toggleLandlineForMobile);
    toggleLandlineForMobile();

    $('#addAnotherEmail').on('click', function () {
        $('#emailsWrapper').append(makeEmailRow());
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('.phone-group').remove();
    });

    $('#sameAsMobile').on('change', function () {
        if ($(this).is(':checked')) {
            let mobileVals = $('input[name="mobile_numbers[]"]').map(function () {
                return $(this).val();
            }).get();

            let mobileCodes = $('select[name="mobile_country_code[]"]').map(function () {
                return $(this).val();
            }).get();

            $('#whatsappNumbersWrapper').html('');

            if (mobileVals.length) {
                mobileVals.forEach(function (num, index) {
                    $('#whatsappNumbersWrapper').append(`
                        <div class="phone-group whatsapp-number-item">
                            <select class="form-control country-code" name="whatsapp_country_code[]">
                                <option value="+91" ${(mobileCodes[index] || '+91') === '+91' ? 'selected' : ''}>+91</option>
                            </select>
                            <input type="text" class="form-control whatsapp-input" name="whatsapp_numbers[]" value="${escapeHtml(num || '')}" placeholder="WhatsApp Number">
                            ${index > 0 ? '<button type="button" class="input-remove remove-row">×</button>' : ''}
                        </div>
                    `);
                });
            } else {
                $('#whatsappNumbersWrapper').append(makePhoneRow('whatsapp'));
            }
        }
    });

    // faq add/remove
    $('.addFaq').on('click', function () {
        appendFaq();
    });

    $(document).on('click', '.faqRemove', function () {
        $(this).closest('.col-md-4').remove();
        userEdited.faq = true;
        triggerAutoAiGenerate();
    });

    // time slot add/remove
    //
    // "+ Add Shift" lives on each day's own row now (appendDayTimeSlot) —
    // that is what makes a 2-shift day (e.g. 9-1, closed for lunch, 5-9)
    // possible, so the old single global "+ Add Another Time Slot" link
    // (which added a dayless row nothing downstream could attribute to a
    // day) is gone.
    $(document)
        .off('click', '.add-day-shift')
        .on('click', '.add-day-shift', function () {

            let day = String($(this).data('day') || '');

            if (!day || $('#open24Hours').is(':checked')) {
                return;
            }

            let $dayRows = $('#timeSlotsWrapper .time-slot-item')
                .filter(function () {
                    return String($(this).data('day') || '') === day;
                });

            appendDayTimeSlot(
                day,
                '',
                '',
                false,
                $dayRows.length,
                $dayRows.last()
            );

            triggerAutoAiGenerate();
        });

   $(document)
    .off('click', '.remove-time-slot')
    .on('click', '.remove-time-slot', function () {

        /*
         * Sirf 2nd+ shift (ya legacy manual) row remove hogi — pehla
         * shift us din ka checkbox uncheck karke hi hataya jaata hai.
         */
        $(this).closest('.time-slot-item').remove();

        triggerAutoAiGenerate();
    });
    // select all days
 /*
|--------------------------------------------------------------------------
| Select All Business Days
|--------------------------------------------------------------------------
*/
$('#selectAllDays').off('change').on('change', function () {

    const checked = $(this).is(':checked');

    /*
     * Sirf days select/unselect honge.
     * Open 24 Hours par koi effect nahi hoga.
     */
    $('input[name="business_days[]"]').prop('checked', checked);

    generateSlotsByDays();

    triggerAutoAiGenerate();
});

/*
|--------------------------------------------------------------------------
| Individual day change par Select All sync
|--------------------------------------------------------------------------
*/
$(document).on(
    'change',
    'input[name="business_days[]"]',
    function () {

        const totalDays =
            $('input[name="business_days[]"]').length;

        const selectedDays =
            $('input[name="business_days[]"]:checked').length;

        const allDaysSelected =
            totalDays > 0 && totalDays === selectedDays;

        $('#selectAllDays').prop('checked', allDaysSelected);

        /*
         * Ticking/unticking one day on its own used to leave the timing
         * rows untouched — only "Select All" and the Open 24 Hours toggle
         * rebuilt them. So checking a single day (the normal case) never
         * grew a row for it. generateSlotsByDays() re-reads whatever rows
         * already exist first, so this is safe to call on every toggle.
         */
        generateSlotsByDays();

        triggerAutoAiGenerate();
    }
);
/*
|--------------------------------------------------------------------------
| Open 24 Hours
|--------------------------------------------------------------------------
*/
function toggleOpen24Hours() {

    const isOpen24Hours =
        $('#open24Hours').is(':checked');

    const $wrapper =
        $('#timeSlotsWrapper');

    const $addButton =
        $('#addTimeSlot');

    if (isOpen24Hours) {

        
        $wrapper.hide();
        $addButton.hide();

     
        $wrapper
            .find('select, input[name="timing_days[]"]')
            .prop('disabled', true);

    } else {

        /*
         * Normal timing fields show.
         */
        $wrapper.show();
        $addButton.show();

        $wrapper
            .find('select, input[name="timing_days[]"]')
            .prop('disabled', false);

        /*
         * Selected days ke according rows create karo.
         */
        generateSlotsByDays();
    }

    triggerAutoAiGenerate();
}


$(document)
    .off('change', '#open24Hours')
    .on('change', '#open24Hours', function () {
        toggleOpen24Hours();
    });
/*
 * Page load par bhi state apply hogi.
 */
toggleOpen24Hours();

    /*
     * The Area field switches between a dropdown and a plain text box.
     *
     * Wherever the postal directory knows the pincode, picking from its list
     * keeps the spelling consistent across listings. Where it does not — and
     * it is missing a lot of genuine pincodes — the admin has to be able to
     * just type the area, otherwise the whole listing is unsaveable over one
     * field. Both states write to the same `area` name, so the form and the
     * controller behind it do not care which one is on screen.
     */
    function areaField() {
        return $('#area');
    }

    // Areas most recently fetched for the current pincode — kept so the
    // "+ Add manually" toggle can restore the dropdown after switching to
    // free text, without re-hitting the pincode API.
    var lastFetchedAreas = [];

    function replaceAreaField($replacement) {
        var $current = areaField();
        var value = ($current.val() || '').trim();

        $replacement.attr('id', 'area').attr('name', 'area').addClass('form-control');
        $current.replaceWith($replacement);

        return value;
    }

    function setAreaAsDropdown(areas) {
        var $select = $('<select>');
        var previous = replaceAreaField($select);

        $select.append('<option value="">Select Area</option>');

        var matched = false;

        (areas || []).forEach(function (area) {
            var name = String(area || '').trim();

            if (!name) {
                return;
            }

            var selected = name.toLowerCase() === previous.toLowerCase();
            matched = matched || selected;

            $select.append(
                $('<option>').attr('value', name).prop('selected', selected).text(name)
            );
        });

        // Something already typed that is not on the list is still the
        // admin's answer — keep it rather than silently dropping it.
        if (previous && !matched) {
            $select.append($('<option>').attr('value', previous).prop('selected', true).text(previous));
        }

        $('#areaHelp').remove();
    }

    function setAreaAsFreeText(message) {
        var $input = $('<input type="text" placeholder="Enter Area">');
        var previous = replaceAreaField($input);

        $input.val(previous);

        $('#areaHelp').remove();

        if (message) {
            $input.after(
                $('<small class="form-text text-muted" id="areaHelp">').text(message)
            );
        }
    }

    // "+ Add manually" toggle — the dropdown only ever offers what the
    // postal directory returned for the pincode, and plenty of real
    // localities (e.g. "Noida Sector 9") never appear there. Lets the admin
    // switch to a free-text box even when the dropdown did load options.
    $('#areaManualToggle').on('click', function () {
        var $toggle = $(this);

        if (areaField().is('select')) {
            setAreaAsFreeText();
            $toggle.text('Choose from list');
        } else {
            setAreaAsDropdown(lastFetchedAreas);
            $toggle.text('+ Add manually');
        }
    });

    // pincode -> city/state/area
    $('#pincode').on('keyup change', debounce(function () {
        let pin = $(this).val().trim();

        if (pin.length !== 6 || !/^\d{6}$/.test(pin)) {
            $('#city').val('');
            $('#state').val('');
            setAreaAsDropdown([]);
            $('#latitude').val('');
            $('#longitude').val('');
            return;
        }

        setAreaAsDropdown([]);
        areaField().is('select') && areaField().html('<option value="">Loading...</option>');

       fetch("/admin/pincode/" + pin)
            .then(response => response.json())
            .then(data => {
                $('#latitude').val('');
                $('#longitude').val('');

                $('#city').val(data.city || '');
                $('#state').val(data.state || '');

                /*
                 * City/State are readonly by default — trusted, so the admin
                 * can't accidentally fat-finger a mismatch against the
                 * pincode. But readonly with nothing to fill it stays empty
                 * forever with no way in: the postal directory is missing
                 * plenty of real pincodes (see PincodeController's own
                 * comment), and our own listings won't have every pincode
                 * either. Same fix as the Area field just below — a miss
                 * unlocks the field for manual entry instead of being a
                 * dead end.
                 */
                $('#city').prop('readonly', !!data.city);
                $('#state').prop('readonly', !!data.state);

                if (data.city && typeof window.autoSelectListingLocation === 'function') {
                    // Location is the same city under another name — keep it
                    // in step instead of making it a second manual choice.
                    window.autoSelectListingLocation(data.city);
                }

                if (data && data.status && Array.isArray(data.areas) && data.areas.length > 0) {
                    lastFetchedAreas = data.areas;
                    setAreaAsDropdown(data.areas);

                    let searchText = `${pin}, ${data.city || ''}, ${data.state || ''}, India`;

                    // Geocoding goes through our own server (see
                    // SiteController@geocodeAddress). Calling Nominatim
                    // directly from the browser gets a 403 — their usage
                    // policy blocks browser-side callers and rate-limits
                    // to ~1 request/sec.
                    fetch("{{ route('geocode.address') }}?address=" + encodeURIComponent(searchText))
                        .then(res => res.json())
                        .then(locationData => {
                            if (locationData && locationData.status) {
                                $('#latitude').val(locationData.lat || '');
                                $('#longitude').val(locationData.lng || '');
                            }
                        })
                        .catch(() => {});

                    updateSeoScore();
                    triggerAutoAiGenerate();
                } else {
                    // The postal directory is missing plenty of real pincodes
                    // (160043, Chandigarh Sector 44, among them). A dead
                    // dropdown left the admin with no way to save a perfectly
                    // good address, so the field becomes a text box instead.
                    setAreaAsFreeText(data.source === 'unreachable'
                        ? 'Pincode service abhi reachable nahi — area type kar dijiye'
                        : 'Is pincode ka area list me nahi hai — type kar dijiye');
                }
            })
            .catch(() => {
                $('#latitude').val('');
                $('#longitude').val('');
                $('#city').prop('readonly', false);
                $('#state').prop('readonly', false);
                setAreaAsFreeText('Area load nahi ho paya — type kar dijiye');
            });
    }, 500));

    // manual edit tracking
    $('#meta_title').on('input', function () {
        userEdited.meta_title = true;
        updateSeoScore();
        updateSpamDetection();
    });

    $('#meta_keywords').on('input', function () {
        userEdited.meta_keywords = true;
        updateSeoScore();
        updateSpamDetection();
    });

    $('#meta_description').on('input', function () {
        userEdited.meta_description = true;
        updateSeoScore();
        updateSpamDetection();
    });

    $(document).on('input', '.faq-question, .faq-answer', function () {
        userEdited.faq = true;
        updateSeoScore();
        updateSpamDetection();
    });

    $('#details').on('input', function () {
        userEdited.details = true;
        updateSeoScore();
        updateSpamDetection();
    });

    /*
     * This form's own Summernote instance — app.js skips it because the
     * textarea carries data-editor="manual". Kept in step with the matching
     * block in edit.blade.php: same toolbar, same paste sanitising, so a
     * listing behaves identically whether it is being created or edited.
     */
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
                    userEdited.details = true;
                    updateSeoScore();
                    updateSpamDetection();
                }
            }
        });

        // Ctrl+K opens Summernote's link dialog; listing details must stay link-free.
        $(document).on('keydown', '.note-editable', function (e) {
            if ((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'k') {
                e.preventDefault();
                return false;
            }
        });
    }

    // seo sanitize
  $(document).on('input', '.seo-source, .seo-field, .faq-question, .faq-answer', function () {
    updateSeoScore();
    updateSpamDetection();
});

// Deferred: on paste this handler runs before the clipboard content is in
// the field, so reading it immediately would clean the old value and leave
// the pasted one untouched.
$(document).on('blur paste', '.seo-source, .seo-field', function () {
    const $field = $(this);

    setTimeout(function () {
        sanitizeFieldValue($field);
        updateSeoScore();
        updateSpamDetection();
    }, 0);
});
    $('#generateSeoKeywords').on('click', function () {
        autoGenerateKeywords();
    });

    // auto AI trigger listeners
    $(document).on('input', 'input[name="name"], input[name="plot_no"], input[name="building_name"], input[name="street_name"], input[name="landmark"], input[name="established_year"], input[name="contact_person"]', function () {
        triggerAutoAiGenerate();
    });

    $(document).on('change', '#area, select[name="location_id"], #category_id, input[name="subcategory_ids[]"], select[name="open_time[]"], select[name="close_time[]"]', function () {
        triggerAutoAiGenerate();
    });

    let listingFormSubmitting = false;

    $('#listingCreateForm').on('submit', async function (e) {
        e.preventDefault();

        if (listingFormSubmitting) {
            return false;
        }

        // Push Summernote HTML into the real textarea before validation/POST.
        if ($.fn.summernote && $('.richEditor').length) {
            $('#details').val($('.richEditor').summernote('code'));
        }

        let errorFound = false;
        let firstError = null;

        $('.seo-source, .seo-field, .faq-question, .faq-answer').each(function () {
            const message = validateTextContent($(this).val());

            if (message) {
                errorFound = true;
                firstError = firstError || this;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        const detailsMessage = validateTextContent(extractPlainDetails());

        if (detailsMessage) {
            errorFound = true;
            firstError = firstError || $('#details')[0];
            $('#details').addClass('is-invalid');
        } else {
            $('#details').removeClass('is-invalid');
        }

        if (errorFound) {
            alert('Please remove blocked words or links from the content fields.');

            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus?.();
            }

            return false;
        }

        listingFormSubmitting = true;

        const releaseSaveButton = function () {
            listingFormSubmitting = false;

            $('#saveListingBtn')
                .prop('disabled', false)
                .html('<i class="fas fa-save"></i> Save Listing');
        };

        $('#saveListingBtn')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        try {
            // Geocoding is optional. A 403/timeout must not stop listing creation.
            if ((!$('#latitude').val() || !$('#longitude').val())
                && typeof updateLatLngFromAddress === 'function') {
                await updateLatLngFromAddress();
            }
        } catch (error) {
            console.warn('Geocoding failed; submitting listing without coordinates.', error);
        }

        /*
         * Last-moment token refresh. Everything typed here is only in the DOM
         * — posting with a stale token means a "Page Expired" screen and the
         * whole form gone. One cheap call decides between saving and losing
         * it, so it is worth blocking the submit for.
         *
         * A refused ping is not a reason to refuse the save. This used to
         * abort on any failed ping, so a 403 from the CDN — which challenges
         * requests it does not recognise and has nothing to do with the
         * session — put "Your login has expired" over a perfectly good login
         * and sent the agent off to log in again, repeatedly, while the real
         * token in the form was still valid the whole time. Only a server
         * that positively says the session is gone stops the save now; a
         * blocked ping falls through and lets the POST itself report what
         * actually happens.
         */
        const form = this;
        const tokenState = await refreshListingToken();

        if (tokenState === 'expired') {
            releaseSaveButton();

            Swal.fire({
                title: 'Your login has expired',
                html: 'Your listing is still on this screen — nothing is lost. ' +
                      'Open <b>/admin</b> in a <b>new tab</b>, log in, then come ' +
                      'back here and press Save again.',
                icon: 'warning',
                confirmButtonText: 'Got it'
            });

            return false;
        }

        /*
        |----------------------------------------------------------------------
        | The POST
        |----------------------------------------------------------------------
        |
        | Sent with fetch rather than a native submit. The hosting firewall in
        | front of this site answers 403 to some saves — always on this URL,
        | never on any other, and only for certain content. A native submit
        | replaces this page with the server's bare "Forbidden" screen and the
        | filled-in listing goes with it. Through fetch the rejection is just a
        | value: the page stays exactly as it is and the agent is told what
        | happened and that their work is still there.
        |
        | redirect: 'manual' is deliberate. If fetch followed the success
        | redirect it would load the listing index here, consuming the flashed
        | "Listing added" notify and the draft-clear signal before the browser
        | ever displayed that page. An opaque redirect leaves the session flash
        | untouched for the real navigation below.
        |
        | X-Requested-With turns a validation failure into 422 JSON instead of
        | a redirect, which is what keeps it distinguishable from success once
        | the redirect itself is opaque.
        |
        */
        let response;
        let payload;

        // Building the envelope used to happen inside the try below, so a
        // failure while sealing the photos reported "the connection dropped"
        // even though nothing had been sent yet — and nothing reached the
        // server to contradict it. Two steps, two messages.
        try {
            payload = await shieldedFormData(form);
        } catch (error) {
            releaseSaveButton();

            Swal.fire({
                title: 'Photos could not be prepared',
                html: 'The listing was not sent — the photos could not be ' +
                      'packed for upload. <b>Nothing on this screen is lost</b>. ' +
                      'Check your connection and press Save again; if it keeps ' +
                      'happening, remove the last photo you added and try once more.',
                icon: 'error',
                confirmButtonText: 'OK'
            });

            return false;
        }

        try {
            response = await fetch(form.action, {
                method: 'POST',
                // Fields and photos sealed into opaque values so the hosting
                // firewall has no upload to inspect. See the firewall-shield
                // partial.
                body: payload,
                credentials: 'same-origin',
                redirect: 'manual',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    // Also in the header, not only in the body. The body now
                    // carries a multi-megabyte encoded photo; if anything in
                    // front of PHP truncates or refuses to parse it, the
                    // header still proves the request is ours, and the save
                    // fails with a real error instead of "Page Expired".
                    'X-CSRF-TOKEN': (form.querySelector('[name="_token"]') || {}).value || ''
                }
            });
        } catch (error) {
            releaseSaveButton();

            Swal.fire({
                title: 'Save could not be sent',
                html: 'The connection dropped before the listing reached the ' +
                      'server. <b>Nothing on this screen is lost</b> — press ' +
                      'Save again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });

            return false;
        }

        // Saved. The redirect was not followed, so its flashed message and its
        // draft-clear signal are still waiting for this navigation.
        if (response.type === 'opaqueredirect' || response.redirected) {
            window.location.href = "{{ route('admin.listing.index') }}";

            return false;
        }

        releaseSaveButton();

        if (response.status === 422) {
            let messages = [];

            try {
                const payload = await response.json();

                $.each(payload.errors || {}, function (field, list) {
                    messages = messages.concat(list);
                });
            } catch (error) {
                messages = [];
            }

            if (!messages.length) {
                messages = ['Please check the highlighted fields and try again.'];
            }

            Swal.fire({
                title: 'Listing not saved',
                html: '<div style="text-align:left">&bull; ' +
                      messages.join('<br>&bull; ') + '</div>',
                icon: 'warning',
                confirmButtonText: 'OK'
            });

            return false;
        }

        if (response.status === 419) {
            Swal.fire({
                title: 'Your login has expired',
                html: 'Your listing is still on this screen — nothing is lost. ' +
                      'Open <b>/admin</b> in a <b>new tab</b>, log in, then come ' +
                      'back here and press Save again.',
                icon: 'warning',
                confirmButtonText: 'Got it'
            });

            return false;
        }

        if (response.status === 403) {
            Swal.fire({
                title: 'The server blocked this save',
                html: 'The hosting firewall rejected this listing before it ' +
                      'reached the site. This is a server setting, not a ' +
                      'mistake in the form.<br><br>' +
                      '<b>Everything you typed is still on this screen.</b> ' +
                      'Try rewording the <b>Listing Details</b> text and press ' +
                      'Save again. If it keeps happening, report it so the ' +
                      'firewall rule can be lifted.',
                icon: 'error',
                confirmButtonText: 'OK'
            });

            return false;
        }

        Swal.fire({
            title: 'Save failed (' + response.status + ')',
            html: 'The server refused the listing. <b>Nothing on this screen ' +
                  'is lost</b> — press Save again, and report this code if it ' +
                  'keeps happening.',
            icon: 'error',
            confirmButtonText: 'OK'
        });

        return false;
    });

    appendQuickUserModal();
    renderSelectedCategory();
    renderSelectedSubcategories();
    renderSelectedUser();
    updateSeoScore();
    updateSpamDetection();

    /*
    |--------------------------------------------------------------------------
    | Draft bridge
    |--------------------------------------------------------------------------
    |
    | The picked user, category and subcategories live in these variables, not
    | in the markup — the chips and hidden inputs are only what they render to.
    | Putting the markup back on its own would leave all three null here, and
    | the next render (a location change, a chip removed) would wipe the lot.
    | So the draft partial reads and writes the state itself and lets the
    | existing render functions redraw from it.
    |
    | Has to live in this block: these variables are scoped to it.
    |
    */
    window.listingDraftPickers = {
        get: function () {
            return {
                user:          selectedUser || null,
                category:      selectedCategory || null,
                subcategories: selectedSubcategories || []
            };
        },
        set: function (state) {
            state = state || {};

            selectedUser          = state.user || null;
            selectedCategory      = state.category || null;
            selectedSubcategories = Array.isArray(state.subcategories) ? state.subcategories : [];

            renderSelectedUser();
            renderSelectedCategory();
            renderSelectedSubcategories();
        }
    };

    /*
     * The next block used to live in its own <script>(function($){...})(jQuery)
     * — a separate closure with no access to selectedCategory,
     * selectedSubcategories, locationFilteredSubcategories or the
     * renderSelected*() functions above. Reading any of them threw
     * "ReferenceError: selectedSubcategories is not defined" the moment
     * #location_id fired a change event — silently on a plain <select>
     * (the browser logs it and moves on), but loudly once Location became a
     * select2: select2 relies on that same change event to redraw its own
     * closed-box text, so the exception aborted its listener before it
     * could run — the dropdown value was set, but the box kept showing the
     * old one until the page reloaded. Folded into this closure so both
     * halves share the one set of variables.
     */
    function loadLocationSubcategories(showDropdown = false) {
    let locationId = $('#location_id').val();
    let categoryId = selectedCategory ? selectedCategory.id : '';

    locationFilteredSubcategories = [];

    if (!locationId || !categoryId) {
        $('#subcategorySearchInput')
            .prop('disabled', true)
            .val('');

        $('#subcategorySuggestionBox')
            .addClass('d-none')
            .html('');

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
    console.log('USED SUBCATEGORY RESPONSE:', res);

    locationFilteredSubcategories =
        Array.isArray(res.data) ? res.data : [];

    $('#subcategorySearchInput')
        .prop('disabled', false)
        .attr('placeholder', 'Type sub category name');

    renderSubcategoryDropdown(locationFilteredSubcategories);
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
                .html('<div class="suggestion-empty">Sub categories load nahi hui</div>')
                .removeClass('d-none');

            console.log(xhr.responseText);
        },

        complete: function () {
            subcategoryAjaxRequest = null;
        }
    });
}

$('#location_id').on('change', function () {
    /*
     * The clear below is deliberate — the subcategory list is filtered by
     * location, so the old picks may not even exist under the new one. It just
     * has to be visible: an agent who picked six subcategories and then fixed
     * the location would otherwise watch them disappear with no explanation,
     * or not notice at all and save a listing with none.
     */
    var hadPicks = selectedSubcategories.length > 0 || !!selectedCategory;

    if (hadPicks) {
        $('#locationAutoNote')
            .removeClass('d-none')
            .text('Location badalne par category aur subcategory dobara select karni hongi.');
    }

    selectedSubcategories = [];
    locationFilteredSubcategories = [];

    $('#subcategorySearchInput')
        .val('')
        .prop('disabled', true);

    $('#selectAllSubcategoriesBtn').hide();
    $('#unselectAllSubcategoriesBtn').hide();

    $('#subcategorySuggestionBox')
        .addClass('d-none')
        .html('');

    renderSelectedSubcategories();

    /*
     * Location badalne par selected category ko dobara click/select karna hoga.
     */
    if (selectedCategory) {
        $('#categorySearchInput').val('');
        selectedCategory = null;
        renderSelectedCategory();
    }
});

})(jQuery);
</script>   
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fields = [
        'input[name="plot_no"]',
        'input[name="building_name"]',
        'input[name="street_name"]',
        'input[name="landmark"]',
        '#area',
        '#city',
        '#state',
        '#pincode'
    ];

    function updateFullAddress() {
        const values = fields.map(selector => {
            const el = document.querySelector(selector);
            return el ? el.value.trim() : '';
        }).filter(Boolean);

        document.getElementById('full_address').value = values.join(', ') + ', India';
        console.log('FULL ADDRESS:', document.getElementById('full_address').value);
    }

    fields.forEach(selector => {
        const el = document.querySelector(selector);
        if (el) {
            el.addEventListener('input', updateFullAddress);
            el.addEventListener('change', updateFullAddress);
            el.addEventListener('blur', updateFullAddress);
        }
    });

    updateFullAddress();
});
</script>
<script>
let geoController = null;

function getAreaText() {
    const areaEl = document.getElementById('area');
    if (!areaEl) return '';

    if (areaEl.tagName === 'SELECT') {
        return areaEl.options[areaEl.selectedIndex]?.text?.trim() || areaEl.value.trim() || '';
    }

    return areaEl.value.trim() || '';
}

function normalizeAreaName(area) {
    const map = {
        'niwaroo': 'Niwaru',
        'niwaru': 'Niwaru',
        'nivaroo': 'Niwaru',
        'nivaru': 'Niwaru'
    };

    const key = area.toLowerCase().trim();
    return map[key] || area;
}

async function geocodeAddress(address) {
    if (geoController) geoController.abort();
    geoController = new AbortController();

    // Server-side proxy, not Nominatim directly (direct browser calls
    // answer 403 — see the note in the pincode handler above).
    const url = "{{ route('geocode.address') }}?address=" + encodeURIComponent(address);

    const response = await fetch(url, {
        signal: geoController.signal,
        headers: { 'Accept': 'application/json' }
    });

    const data = await response.json();

    // Callers below expect Nominatim's [{lat, lon}] shape.
    return (data && data.status)
        ? [{ lat: data.lat, lon: data.lng }]
        : [];
}

async function updateLatLngFromArea() {
    const rawArea = getAreaText();
    const area = normalizeAreaName(rawArea);
    const city = document.getElementById('city')?.value?.trim() || '';
    const state = document.getElementById('state')?.value?.trim() || '';
    const pincode = document.getElementById('pincode')?.value?.trim() || '';

    if (!area || !city || !state) {
        console.log('Area/City/State missing');
        return false;
    }

    const addresses = [
        `${area}, ${city}, ${state}, ${pincode}, India`,
        `${area}, ${city}, ${state}, India`,
        `${area}, ${city}, India`,
        `${city}, ${state}, ${pincode}, India`
    ];

    for (const address of addresses) {
        try {
            console.log('Trying:', address);
            const data = await geocodeAddress(address);

            if (data && data.length > 0) {
                document.getElementById('latitude').value = data[0].lat || '';
                document.getElementById('longitude').value = data[0].lon || '';
                console.log('Lat/Lng updated:', data[0].lat, data[0].lon, 'FROM:', address);
                return true;
            }
        } catch (error) {
            if (error.name === 'AbortError') return false;
            console.log('Geo error:', error);
        }
    }

    console.log('No result found for any area fallback');
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    const areaEl = document.getElementById('area');
    const pincodeEl = document.getElementById('pincode');

    if (areaEl) {
        areaEl.addEventListener('change', updateLatLngFromArea);
        areaEl.addEventListener('blur', updateLatLngFromArea);
    }

    if (pincodeEl) {
        pincodeEl.addEventListener('change', updateLatLngFromArea);
        pincodeEl.addEventListener('blur', updateLatLngFromArea);
    }
});
</script>

<script>

$(document).ready(function () {

    function applyTimeToAllSlots() {
        let open = $('select[name="open_time[]"]').first().val();
        let close = $('select[name="close_time[]"]').first().val();

        // sab slots me same value apply
        $('select[name="open_time[]"]').each(function () {
            $(this).val(open);
        });

        $('select[name="close_time[]"]').each(function () {
            $(this).val(close);
        });
    }

  
$(document)
    .off('change.copyOpenTime', '.day-open-time')
    .on('change.copyOpenTime', '.day-open-time', function () {

        let $currentRow = $(this).closest('.time-slot-item');
        let $firstDayRow = $('#timeSlotsWrapper .time-slot-item[data-manual="0"]')
            .first();

        /*
         * Sirf first day row change hone par sab mein copy hoga.
         * Other rows manually edit ki ja sakti hain.
         */
        if (!$currentRow.is($firstDayRow)) {
            triggerAutoAiGenerate();
            return;
        }

        let selectedOpenTime = $(this).val();

        $('#timeSlotsWrapper .time-slot-item[data-manual="0"]')
            .not($firstDayRow)
            .find('.day-open-time')
            .val(selectedOpenTime);

        triggerAutoAiGenerate();
    });


/*
|--------------------------------------------------------------------------
| First row Close Time → copy to remaining day rows
|--------------------------------------------------------------------------
*/
$(document)
    .off('change.copyCloseTime', '.day-close-time')
    .on('change.copyCloseTime', '.day-close-time', function () {

        let $currentRow = $(this).closest('.time-slot-item');
        let $firstDayRow = $('#timeSlotsWrapper .time-slot-item[data-manual="0"]')
            .first();

        if (!$currentRow.is($firstDayRow)) {
            triggerAutoAiGenerate();
            return;
        }

        let selectedCloseTime = $(this).val();

        $('#timeSlotsWrapper .time-slot-item[data-manual="0"]')
            .not($firstDayRow)
            .find('.day-close-time')
            .val(selectedCloseTime);

        triggerAutoAiGenerate();
    });
   $('#selectAllDays').on('change', function () {

    let checked = $(this).is(':checked');

    $('input[name="business_days[]"]').prop('checked', checked);

    generateSlotsByDays(); // 🔥 ADD
});

    $(document).on('change', 'input[name="business_days[]"]', function () {
        applyTimeToAllSlots();
    });

});

$(document).on('input', 'input[name="mobile_numbers[]"], input[name="whatsapp_numbers[]"]', function () {

    let value = $(this).val();

    // space remove + sirf number allow
    value = value.replace(/\s+/g, '');   // space remove
    value = value.replace(/[^0-9]/g, ''); // sirf digits

    // max 10 digit
    value = value.substring(0, 10);

    $(this).val(value);
});


</script>

<script>
async function updateLatLngFromAddress() {
    const address = [
        $('input[name="plot_no"]').val(),
        $('input[name="building_name"]').val(),
        $('input[name="street_name"]').val(),
        $('input[name="landmark"]').val(),
        $('#area').val(),
        $('#city').val(),
        $('#state').val(),
        $('#pincode').val(),
        'India'
    ]
        .map(value => String(value || '').trim())
        .filter(Boolean)
        .join(', ');

    $('#full_address').val(address);

    if (address.length < 10) {
        return false;
    }

    try {
        const response = await fetch(
            "{{ route('geocode.address') }}?address=" + encodeURIComponent(address),
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        if (!response.ok) {
            console.warn('Geocode HTTP error:', response.status);
            return false;
        }

        const data = await response.json();

        if (data && data.status && data.lat && data.lng) {
            $('#latitude').val(data.lat);
            $('#longitude').val(data.lng);
            return true;
        }
    } catch (error) {
        console.warn('Geocode request failed:', error);
    }

    return false;
}
</script>
@endpush

{{-- Auto spelling/grammar correction is switched off: it rewrote business
     names, colony names and details behind the typist's back. The partial and
     its route are left in place, so re-enabling it is just uncommenting this.

@push("js")
@include("admin.listing.partials.auto-spell")
@endpush
--}}

{{-- select2 is vendored under public/assets/admin (same copy
     subcategories/index.blade.php and setting/seo.blade.php use) — no CDN. --}}
@push('css-link')
	<link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
@endpush
@push('js-link')
	<script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endpush
@push('js')
<script>
$(document).ready(function () {
    "use strict";

    // Location dropdown gets a search box — the list runs into the hundreds
    // and scrolling through a plain <select> to find one city is slow.
    //
    // dropdownParent is pinned to the field's own .form-group instead of the
    // default <body>: appended-to-body dropdowns measure their position off
    // whatever ancestor happens to be the offset parent, and on this page
    // that put the panel's real click area somewhere other than where it
    // was drawn — options highlighted on hover but a click landed on
    // whatever was actually underneath, so nothing ever got selected.
    // Anchoring to the field itself keeps the drawn position and the click
    // target the same element.
    $('.select2-search').each(function () {
        $(this).select2({
            width: '100%',
            placeholder: $(this).data('placeholder'),
            dropdownParent: $(this).closest('.form-group')
        });
    });
});
</script>
@endpush

{{-- Last on purpose: the draft has to be put back after every other script
     on this page has finished building its rows, editors and pickers. --}}
@include('admin.listing.partials.form-draft', [
    'draftKey' => $draftKey,
    'hasOld'   => count(old()) > 0,
])