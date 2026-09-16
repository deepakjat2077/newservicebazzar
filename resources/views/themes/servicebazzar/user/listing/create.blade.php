@extends($themeView . 'layouts.app')
@section('title', 'My Listings')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center align-items-start">
                <div class="col-lg-5 mb-4 mb-lg-0 d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/images/unnamed.jpg') }}" class="shadow rounded img-fluid" style="width:350px;"
                        alt="Business Preview">
                </div>

                <div class="col-lg-7">
                    <div class="card shadow border-0 listing-card">
                        <div class="card-body p-4 p-md-5">

                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <style>
                                .listing-card {
                                    border-radius: 18px;
                                    overflow: hidden;
                                }

                                .step-progress {
                                    display: flex;
                                    gap: 10px;
                                    margin-bottom: 28px;
                                    flex-wrap: wrap;
                                }

                                .step-progress .step-dot {
                                    flex: 1;
                                    min-width: 60px;
                                    height: 8px;
                                    border-radius: 30px;
                                    background: #e9ecef;
                                    transition: .3s;
                                }

                                .step-progress .step-dot.active {
                                    background: #0d6efd;
                                }

                                .step-title {
                                    font-size: 28px;
                                    font-weight: 700;
                                    margin-bottom: 6px;
                                    color: #1f1f1f;
                                }

                                .step-subtitle {
                                    font-size: 14px;
                                    color: #6c757d;
                                    margin-bottom: 24px;
                                    line-height: 1.6;
                                }

                                .wizard-step {
                                    display: none;
                                }

                                .wizard-step.active {
                                    display: block;
                                }

                                .form-control,
                                .form-select {
                                    height: 48px;
                                    border-radius: 10px;
                                    box-shadow: none !important;
                                }

                                textarea.form-control {
                                    height: auto;
                                }

                                .btn-main {
                                    background: #0d6efd;
                                    color: #fff;
                                    border: none;
                                    border-radius: 8px;
                                    height: 46px;
                                    font-weight: 600;
                                }

                                .btn-main:hover {
                                    background: #0b5ed7;
                                    color: #fff;
                                }

                                .btn-outline-light-custom {
                                    border: 1px solid #d9d9d9;
                                    background: #fff;
                                    color: #333;
                                    border-radius: 8px;
                                    height: 46px;
                                    font-weight: 600;
                                }

                                .btn-outline-light-custom:hover {
                                    background: #f8f9fa;
                                }

                                .add-link {
                                    display: inline-block;
                                    color: #0d6efd;
                                    font-size: 14px;
                                    font-weight: 600;
                                    text-decoration: none;
                                    margin-top: 8px;
                                    margin-bottom: 10px;
                                }

                                .add-link:hover {
                                    text-decoration: underline;
                                    color: #0d6efd;
                                }

                                .phone-group {
                                    display: flex;
                                    gap: 10px;
                                    margin-bottom: 12px;
                                }

                                .country-code {
                                    width: auto;
                                    min-width: 0;
                                    flex: 0 0 auto;
                                }

                                .input-remove {
                                    width: 42px;
                                    min-width: 42px;
                                    height: 48px;
                                    border: none;
                                    border-radius: 10px;
                                    background: #ffe5e5;
                                    color: #dc3545;
                                    font-weight: 700;
                                }

                                .weekday-list {
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: 10px;
                                    margin-bottom: 12px;
                                }

                                .weekday-item {
                                    position: relative;
                                }

                                .weekday-item input {
                                    display: none;
                                }

                                .weekday-item label {
                                    min-width: 48px;
                                    height: 38px;
                                    border-radius: 30px;
                                    border: 1px solid #d8d8d8;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    padding: 0 14px;
                                    font-size: 13px;
                                    cursor: pointer;
                                    color: #555;
                                    background: #fff;
                                    transition: .2s;
                                }

                                .weekday-item input:checked+label {
                                    background: #e7f1ff;
                                    color: #0d6efd;
                                    border-color: #0d6efd;
                                    font-weight: 600;
                                }

                                .time-slot-box {
                                    border: 1px solid #ececec;
                                    border-radius: 14px;
                                    padding: 14px;
                                    margin-bottom: 15px;
                                    background: #fafafa;
                                }

                                .section-label {
                                    font-size: 14px;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    color: #333;
                                }

                                .category-tags,
                                .suggested-tags {
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: 10px;
                                }

                                .selected-tag {
                                    background: #0d6efd;
                                    color: #fff;
                                    border-radius: 30px;
                                    padding: 8px 16px;
                                    font-size: 14px;
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 10px;
                                }

                                .selected-tag .remove-tag {
                                    cursor: pointer;
                                    font-weight: 700;
                                }

                                .suggested-tag {
                                    border: 1px solid #0d6efd;
                                    color: #0d6efd;
                                    background: #fff;
                                    border-radius: 30px;
                                    padding: 8px 16px;
                                    font-size: 14px;
                                    cursor: pointer;
                                    transition: .2s;
                                }

                                .suggested-tag:hover {
                                    background: #eef5ff;
                                }

                                .photo-grid {
                                    display: grid;
                                    grid-template-columns: repeat(2, 1fr);
                                    gap: 16px;
                                }

                                .photo-upload-card {
                                    height: 120px;
                                    border: 2px dashed #9ed0ff;
                                    border-radius: 12px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    flex-direction: column;
                                    text-align: center;
                                    cursor: pointer;
                                    background: #fafdff;
                                    color: #0d6efd;
                                    font-weight: 600;
                                    overflow: hidden;
                                    position: relative;
                                }

                                .photo-upload-card input {
                                    display: none;
                                }

                                .photo-upload-card img {
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                    position: absolute;
                                    inset: 0;
                                }

                                .photo-sample {
                                    position: relative;
                                    height: 120px;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    background: #f1f1f1;
                                }

                                .photo-sample img {
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                }

                                .photo-badge {
                                    position: absolute;
                                    left: 10px;
                                    bottom: 10px;
                                    background: #fff;
                                    padding: 3px 10px;
                                    border-radius: 4px;
                                    font-size: 12px;
                                    font-weight: 600;
                                }

                                .wizard-actions {
                                    display: flex;
                                    gap: 12px;
                                    margin-top: 28px;
                                }

                                .wizard-actions .btn {
                                    flex: 1;
                                }

                                .checkbox-text {
                                    font-size: 14px;
                                    color: #0d6efd;
                                    font-weight: 600;
                                    cursor: pointer;
                                }

                                .search-icon-input {
                                    position: relative;
                                }

                                .search-icon-input i {
                                    position: absolute;
                                    top: 50%;
                                    left: 14px;
                                    transform: translateY(-50%);
                                    color: #a0a0a0;
                                }

                                .search-icon-input input {
                                    padding-left: 40px;
                                }

                                @media(max-width:767px) {
                                    .step-title {
                                        font-size: 24px;
                                    }

                                    .phone-group {
                                        flex-wrap: wrap;
                                    }

                                    .wizard-actions {
                                        flex-direction: column;
                                    }
                                }
                            </style>

                            <form action="{{ route('user.listing.store') }}" method="POST" enctype="multipart/form-data"
                                id="multiStepForm">
                                @csrf

                                <div class="step-progress mb-4">
                                    <div class="step-dot active" data-step-dot="1"></div>
                                    <div class="step-dot" data-step-dot="2"></div>
                                    <div class="step-dot" data-step-dot="3"></div>
                                    <div class="step-dot" data-step-dot="4"></div>
                                    <div class="step-dot" data-step-dot="5"></div>
                                </div>

                                {{-- STEP 1 --}}
                                <div class="wizard-step active" data-step="1">
                                    <h3 class="step-title">Enter Your Business Details</h3>
                                    <p class="step-subtitle">Add your basic business information to get started.</p>

                                    <div class="mb-3">
                                        <label class="form-label">Business Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('business_name') }}" placeholder="Enter Business Name">
                                    </div>

                                   

                                    <div class="mb-3">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" id="pincode" class="form-control" placeholder="Pincode"
                                            name="pincode" maxlength="6" value="{{ old('pincode') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Plot No. / Shop No. / Floor</label>
                                        <input type="text" class="form-control" placeholder="Plot No. / Shop No. / Floor"
                                            name="plot_no" value="{{ old('plot_no') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Building Name / Market / Colony</label>
                                        <input type="text" class="form-control"
                                            placeholder="Building Name / Market / Colony" name="building_name"
                                            value="{{ old('building_name') }}">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Street / Road Name</label>
                                            <input type="text" class="form-control" placeholder="Street / Road Name"
                                                name="street_name" value="{{ old('street_name') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Landmark</label>
                                            <input type="text" class="form-control" placeholder="Landmark"
                                                name="landmark" value="{{ old('landmark') }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Area</label>
                                            <select class="form-control" id="area" name="area">
                                                <option value="">Select Area</option>
                                            </select>
                                        </div>

                                    </div>

<div class="mb-3">
    <label class="form-label">Location</label>
    <select class="form-control" name="location_id">
        <option value="">Select Location</option>

        @foreach($locations as $location)
            <option value="{{ $location->id }}">
                {{ $location->name }}
            </option>
        @endforeach

    </select>
</div>


                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City</label>
                                            <input type="text" id="city" class="form-control" placeholder="City"
                                                name="city" value="{{ old('city') }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">State</label>
                                            <input type="text" id="state" class="form-control"
                                                placeholder="State" name="state" value="{{ old('state') }}" readonly>
                                        </div>
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" name="longitude" id="longitude"
                                        value="{{ old('longitude') }}">

                                    <div class="wizard-actions">
                                        <button type="button" class="btn btn-main next-step">Save and Continue</button>
                                    </div>
                                </div>

                                {{-- STEP 2 --}}
                                <div class="wizard-step" data-step="2">
                                    <h3 class="step-title">Add Contact Details</h3>
                                    <p class="step-subtitle">Add contact person, phone, WhatsApp, landline and emails.</p>



                                    <div id="contactPersonWrapper">
                                        <label class="form-label">Contact Person</label>

                                        <div class="phone-group">
                                            <select class="form-select country-code" name="contact_title">
                                                <option value="Mr">Mr</option>
                                                <option value="Mrs">Mrs</option>
                                                <option value="Miss">Miss</option>
                                                <option value="Dr">Dr</option>
                                            </select>

                                            <input type="text" class="form-control" name="contact_person"
                                                placeholder="Contact Person">
                                        </div>
                                    </div>
                                    <div id="mobileNumbersWrapper">
                                        <label class="form-label">Mobile Number</label>
                                        <div class="phone-group mobile-number-item">
                                            <select class="form-select country-code" name="mobile_country_code[]">
                                                <option value="+91">+91</option>
                                            </select>
                                            <input type="text" class="form-control" name="mobile_numbers[]"
                                                placeholder="Mobile Number">
                                        </div>
                                    </div>
                                    <a href="javascript:void(0)" class="add-link" id="addMobileNumber">+ Add Another
                                        Mobile Number</a>

                                    <div id="whatsappNumbersWrapper">
                                        <label class="form-label">WhatsApp Number</label>
                                        <div class="phone-group whatsapp-number-item">
                                            <select class="form-select country-code" name="whatsapp_country_code[]">
                                                <option value="+91">+91</option>
                                            </select>
                                            <input type="text" class="form-control whatsapp-input"
                                                name="whatsapp_numbers[]" placeholder="WhatsApp Number">
                                        </div>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="sameAsMobile">
                                        <label class="form-check-label checkbox-text" for="sameAsMobile">
                                            Same As Mobile Number
                                        </label>
                                    </div>

                                    <a href="javascript:void(0)" class="add-link" id="addWhatsappNumber">+ Add WhatsApp
                                        Number</a>

                                    <div id="landlineNumbersWrapper">
                                        <label class="form-label">Landline Number</label>
                                        <div class="phone-group landline-number-item">
                                            <select class="form-select country-code" name="landline_country_code[]">
                                                <option value="+91">+91</option>
                                            </select>
                                            <input type="text" class="form-control" name="landline_numbers[]"
                                                placeholder="Landline Number">
                                        </div>
                                    </div>

                                    <a href="javascript:void(0)" class="add-link" id="addLandlineNumber">+ Add Landline
                                        Number</a>

                                   <div class="phone-group email-item">
                <input id="addAnotherEmail" type="email" class="form-control" name="extra_emails[]" placeholder="Email">
               
            </div>
                                  

                                    <div class="wizard-actions">
                                        <button type="button"
                                            class="btn btn-outline-light-custom prev-step">Back</button>
                                        <button type="button" class="btn btn-main next-step">Save and Continue</button>
                                    </div>
                                </div>

                                {{-- STEP 3 --}}
                                <div class="wizard-step" data-step="3">
                                    <h3 class="step-title">Add Business Timings</h3>
                                    <p class="step-subtitle">Let your customers know when you are open for business.</p>

                                    <label class="section-label">Select Days Of The Week</label>
                                    <div class="weekday-list">
                                        @php
                                            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                        @endphp
                                        @foreach ($days as $day)
                                            <div class="weekday-item">
                                                <input type="checkbox" id="day_{{ $day }}"
                                                    name="business_days[]" value="{{ $day }}">
                                                <label for="day_{{ $day }}">{{ $day }}</label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="selectAllDays">
                                        <label class="form-check-label checkbox-text" for="selectAllDays">Select All
                                            Days</label>
                                    </div>

                                    <div id="timeSlotsWrapper">
                                        <div class="time-slot-box time-slot-item">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Open at</label>
                                                    <select class="form-select" name="open_time[]">
                                                        <option value="">Select</option>
                                                        <option>06:00 AM</option>
                                                        <option>07:00 AM</option>
                                                        <option>08:00 AM</option>
                                                        <option>09:00 AM</option>
                                                        <option>10:00 AM</option>
                                                        <option>11:00 AM</option>
                                                        <option>12:00 PM</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Close at</label>
                                                    <select class="form-select" name="close_time[]">
                                                        <option value="">Select</option>
                                                        <option>01:00 PM</option>
                                                        <option>02:00 PM</option>
                                                        <option>03:00 PM</option>
                                                        <option>04:00 PM</option>
                                                        <option>05:00 PM</option>
                                                        <option>06:00 PM</option>
                                                        <option>07:00 PM</option>
                                                        <option>08:00 PM</option>
                                                        <option>09:00 PM</option>
                                                        <option>10:00 PM</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="javascript:void(0)" class="add-link" id="addTimeSlot">+ Add Another Time
                                        Slot</a>

                                           <div >
                                        <label class="form-label">Since (Year)</label>
                                        <div class="email-item mb-2">
                                           <input type="number" name="established_year" class="form-control"
       min="1900" max="{{ date('Y') }}"
       oninput="if(this.value.length>4)this.value=this.value.slice(0,4)"
       placeholder="e.g. 2015">
                                        </div>
                                    </div>


                                    <div class="wizard-actions">
                                        <button type="button"
                                            class="btn btn-outline-light-custom prev-step">Back</button>
                                        <button type="button" class="btn btn-main next-step">Save and Continue</button>
                                    </div>
                                </div>

                                {{-- STEP 4 --}}
                                <div class="wizard-step" data-step="4">
                                    <h3 class="step-title">Add Business Category</h3>
                                    <p class="step-subtitle">Choose the right business categories so your customer can
                                        easily find you.</p>

                                    <div class="search-icon-input mb-4 position-relative">
                                        <i class="fas fa-search"></i>
                                        <input type="text" class="form-control" id="categorySearch"
                                            placeholder="Type Business Category">

                                        <div id="categorySearchResults" class="category-search-results"></div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="section-label">Selected Category</div>
                                        <div class="category-tags" id="selectedMainCategory"></div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="section-label">Selected Categories</div>
                                        <div class="category-tags" id="selectedCategories"></div>
                                    </div>

                                    <div>
                                        <div class="section-label">Suggested Categories</div>
                                        <div class="suggested-tags" id="suggestedCategories"></div>
                                    </div>

                                    <div id="hiddenCategoryInputs"></div>

                                    <div class="wizard-actions">
                                        <button type="button"
                                            class="btn btn-outline-light-custom prev-step">Back</button>
                                        <button type="button" class="btn btn-main next-step">Save and Continue</button>
                                    </div>
                                </div>

                                {{-- STEP 5 --}}
                                <div class="wizard-step" data-step="5">
                                    <h3 class="step-title">Add Photos</h3>
                                    <p class="step-subtitle">Showcase photos of your business to look authentic.</p>

                                    <div class="photo-grid">
                                        <label class="photo-upload-card">
                                            <input type="file" name="thumb" class="photo-input" accept="image/*">
                                            <div class="upload-text">
                                                <div style="font-size:28px;line-height:1;">📷</div>
                                                <div>Add Photo</div>
                                            </div>
                                            <img src="" alt="Business listing photo preview" class="preview-image d-none">
                                        </label>

                                        <div class="photo-sample">
                                            <img src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?q=80&w=800&auto=format&fit=crop"
                                                alt="Example business photo 1">
                                            <span class="photo-badge">Example</span>
                                        </div>

                                        <div class="photo-sample">
                                            <img src="https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?q=80&w=800&auto=format&fit=crop"
                                                alt="Example business photo 2">
                                            <span class="photo-badge">Example</span>
                                        </div>

                                        <div class="photo-sample">
                                            <img src="https://images.unsplash.com/photo-1497366754035-f200968a6e72?q=80&w=800&auto=format&fit=crop"
                                                alt="Example business photo 3">
                                            <span class="photo-badge">Example</span>
                                        </div>
                                    </div>

                                    <div class="wizard-actions">
                                        <button type="button"
                                            class="btn btn-outline-light-custom prev-step">Back</button>
                                        <button type="button" class="btn btn-outline-light-custom"
                                            id="skipPhotos">Skip</button>
                                        <button type="submit" class="btn btn-main">Save and Continue</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    let currentStep = 1;
    const totalSteps = 5;

    function showStep(step) {
        document.querySelectorAll(".wizard-step").forEach(item => item.classList.remove("active"));
        document.querySelector('.wizard-step[data-step="' + step + '"]').classList.add("active");

        document.querySelectorAll(".step-dot").forEach(dot => dot.classList.remove("active"));
        for (let i = 1; i <= step; i++) {
            const activeDot = document.querySelector('.step-dot[data-step-dot="' + i + '"]');
            if (activeDot) activeDot.classList.add("active");
        }

        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    document.querySelectorAll(".next-step").forEach(btn => {
        btn.addEventListener("click", function () {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    document.querySelectorAll(".prev-step").forEach(btn => {
        btn.addEventListener("click", function () {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
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

                            let searchText = `${pin}, ${cityField.value}, ${stateField.value}, India`;

                            fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(searchText)}`)
                                .then(res => res.json())
                                .then(locationData => {
                                    if (locationData.length > 0) {
                                        latitudeField.value = locationData[0].lat || "";
                                        longitudeField.value = locationData[0].lon || "";
                                    }
                                })
                                .catch(err => {
                                    console.error("Geocoding error:", err);
                                });
                        } else {
                            alert("Invalid Pincode");
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching pincode data:", error);
                    });
            }
        });
    }

    function createPhoneRow(name, wrapperClass) {
        return `
            <div class="phone-group ${wrapperClass}">
                <select class="form-select country-code" name="${name}_country_code[]">
                    <option value="+91">+91</option>
                </select>
                <input type="text" class="form-control" name="${name}_numbers[]" placeholder="${name.replace('_', ' ')}">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `;
    }

    document.getElementById("addMobileNumber").addEventListener("click", function () {
        document.getElementById("mobileNumbersWrapper").insertAdjacentHTML("beforeend", createPhoneRow('mobile', 'mobile-number-item'));
    });

    document.getElementById("addWhatsappNumber").addEventListener("click", function () {
        document.getElementById("whatsappNumbersWrapper").insertAdjacentHTML("beforeend", createPhoneRow('whatsapp', 'whatsapp-number-item'));
    });

    document.getElementById("addLandlineNumber").addEventListener("click", function () {
        document.getElementById("landlineNumbersWrapper").insertAdjacentHTML("beforeend", createPhoneRow('landline', 'landline-number-item'));
    });

    document.getElementById("addAnotherEmail").addEventListener("click", function () {
        document.getElementById("emailsWrapper").insertAdjacentHTML("beforeend", `
            <div class="phone-group email-item">
                <input type="email" class="form-control" name="extra_emails[]" placeholder="Email">
                <button type="button" class="input-remove remove-row">×</button>
            </div>
        `);
    });

    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-row")) {
            e.target.closest(".phone-group").remove();
        }
    });

    document.getElementById("sameAsMobile").addEventListener("change", function () {
        const mobileInput = document.querySelector('input[name="mobile_numbers[]"]');
        const whatsappInput = document.querySelector('.whatsapp-input');

        if (this.checked && mobileInput && whatsappInput) {
            whatsappInput.value = mobileInput.value;
        } else if (whatsappInput) {
            whatsappInput.value = '';
        }
    });

    document.getElementById("selectAllDays").addEventListener("change", function () {
        document.querySelectorAll('input[name="business_days[]"]').forEach(day => {
            day.checked = this.checked;
        });
    });

    document.getElementById("addTimeSlot").addEventListener("click", function () {
        document.getElementById("timeSlotsWrapper").insertAdjacentHTML("beforeend", `
            <div class="time-slot-box time-slot-item">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Open at</label>
                        <select class="form-select" name="open_time[]">
                            <option value="">Select</option>
                            <option>06:00 AM</option>
                            <option>07:00 AM</option>
                            <option>08:00 AM</option>
                            <option>09:00 AM</option>
                            <option>10:00 AM</option>
                            <option>11:00 AM</option>
                            <option>12:00 PM</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Close at</label>
                        <select class="form-select" name="close_time[]">
                            <option value="">Select</option>
                            <option>01:00 PM</option>
                            <option>02:00 PM</option>
                            <option>03:00 PM</option>
                            <option>04:00 PM</option>
                            <option>05:00 PM</option>
                            <option>06:00 PM</option>
                            <option>07:00 PM</option>
                            <option>08:00 PM</option>
                            <option>09:00 PM</option>
                            <option>10:00 PM</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end mb-3">
                        <button type="button" class="input-remove remove-time-slot w-100">×</button>
                    </div>
                </div>
            </div>
        `);
    });

    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-time-slot")) {
            e.target.closest(".time-slot-item").remove();
        }
    });

    document.querySelectorAll(".photo-input").forEach(input => {
        input.addEventListener("change", function () {
            const file = this.files[0];
            const parent = this.closest(".photo-upload-card");
            const preview = parent.querySelector(".preview-image");
            const text = parent.querySelector(".upload-text");

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove("d-none");
                    text.classList.add("d-none");
                };
                reader.readAsDataURL(file);
            }
        });
    });

    document.getElementById("skipPhotos").addEventListener("click", function () {
        document.getElementById("multiStepForm").submit();
    });
});
</script>
    <style>
        .search-icon-input {
            position: relative;
        }

        .category-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-top: 6px;
            z-index: 999;
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }

        .category-result-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
        }

        .category-result-item:hover {
            background: #f8f9fa;
        }

        .category-tags,
        .suggested-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .selected-tag,
        .selected-main-tag,
        .suggested-tag {
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .selected-main-tag {
            background: #0d6efd;
            color: #fff;
        }

        .selected-tag {
            background: #198754;
            color: #fff;
        }

        .suggested-tag {
            background: #f1f3f5;
            border: 1px solid #ddd;
            color: #222;
        }

        .remove-tag {
            cursor: pointer;
            font-weight: bold;
        }
    </style>
    <script>
        const categories = @json($categories);
        const subcategories = @json($subcategories);

        let selectedCategory = null;
        let selectedSubcategories = [];

        const categorySearch = document.getElementById('categorySearch');
        const categorySearchResults = document.getElementById('categorySearchResults');
        const selectedMainCategory = document.getElementById('selectedMainCategory');
        const selectedCategories = document.getElementById('selectedCategories');
        const suggestedCategories = document.getElementById('suggestedCategories');
        const hiddenCategoryInputs = document.getElementById('hiddenCategoryInputs');

        categorySearch.addEventListener('keyup', function() {
            let keyword = this.value.toLowerCase().trim();

            if (keyword === '') {
                categorySearchResults.style.display = 'none';
                categorySearchResults.innerHTML = '';
                return;
            }

            let filtered = categories.filter(category =>
                category.name.toLowerCase().includes(keyword)
            );

            let html = '';

            if (filtered.length > 0) {
                filtered.forEach(category => {
                    html += `
                    <div class="category-result-item"
                         data-id="${category.id}"
                         data-name="${category.name}">
                        ${category.name}
                    </div>
                `;
                });
            } else {
                html = `<div class="category-result-item">No category found</div>`;
            }

            categorySearchResults.innerHTML = html;
            categorySearchResults.style.display = 'block';
        });

        document.addEventListener('click', function(e) {

            if (e.target.classList.contains('category-result-item') && e.target.dataset.id) {
                const id = e.target.dataset.id;
                const name = e.target.dataset.name;

                selectedCategory = {
                    id,
                    name
                };
                selectedSubcategories = [];

                categorySearch.value = name;
                categorySearchResults.style.display = 'none';

                renderSelectedCategory();
                renderSuggestedSubcategories();
                renderSelectedSubcategories();
                renderHiddenInputs();
            }

            if (e.target.classList.contains('suggested-tag')) {
                const id = e.target.dataset.id;
                const name = e.target.dataset.name;

                let exists = selectedSubcategories.some(item => item.id == id);

                if (!exists) {
                    selectedSubcategories.push({
                        id,
                        name
                    });
                    renderSuggestedSubcategories();
                    renderSelectedSubcategories();
                    renderHiddenInputs();
                }
            }

            if (e.target.classList.contains('remove-tag')) {
                const id = e.target.dataset.id;

                selectedSubcategories = selectedSubcategories.filter(item => item.id != id);

                renderSuggestedSubcategories();
                renderSelectedSubcategories();
                renderHiddenInputs();
            }

            if (!e.target.closest('.search-icon-input')) {
                categorySearchResults.style.display = 'none';
            }
        });

        function renderSelectedCategory() {
            if (!selectedCategory) {
                selectedMainCategory.innerHTML = '';
                return;
            }

            selectedMainCategory.innerHTML = `
            <span class="selected-main-tag">${selectedCategory.name}</span>
        `;
        }

        function renderSuggestedSubcategories() {
            if (!selectedCategory) {
                suggestedCategories.innerHTML = '';
                return;
            }

            let filteredSubs = subcategories.filter(sub => sub.category_id == selectedCategory.id);

            let html = '';

            filteredSubs.forEach(sub => {
                let alreadySelected = selectedSubcategories.some(item => item.id == sub.id);

                if (!alreadySelected) {
                    html += `
                    <span class="suggested-tag"
                          data-id="${sub.id}"
                          data-name="${sub.name}">
                        ${sub.name} +
                    </span>
                `;
                }
            });

            suggestedCategories.innerHTML = html ? html : '<span>No subcategory found</span>';
        }

        function renderSelectedSubcategories() {
            let html = '';

            selectedSubcategories.forEach(sub => {
                html += `
                <span class="selected-tag">
                    ${sub.name}
                    <span class="remove-tag" data-id="${sub.id}">&times;</span>
                </span>
            `;
            });

            selectedCategories.innerHTML = html;
        }

        function renderHiddenInputs() {
            let html = '';

            if (selectedCategory) {
                html += `<input type="hidden" name="category_id" value="${selectedCategory.id}">`;
            }

            selectedSubcategories.forEach(sub => {
                html += `<input type="hidden" name="subcategory_ids[]" value="${sub.id}">`;
            });

            hiddenCategoryInputs.innerHTML = html;
        }
    </script>
@endsection
