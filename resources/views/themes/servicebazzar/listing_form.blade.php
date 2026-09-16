@extends($themeView.'layouts.site')
@section('title','Free - Listing')
@section('content')


   <section class="py-5 free-listing-page">
        <div class="container">
              <div class="contact-with-author mb-4">
              <button type="button" id="openAdminChat" class="btn btn__base w-100">
    <i class="las la-comments"></i> Chat with admin
</button>
                </div>

            <div class="row justify-content-center align-items-start">
              <div class="col-lg-5 mb-4 mb-lg-0 d-flex justify-content-center align-items-center free-listing-left {{ $existingListings->isNotEmpty() ? 'd-none' : '' }}" id="sbFreeListingLeftCol">
                    <img src="{{ asset('assets/images/image.png') }}" class="shadow rounded img-fluid" style="width:350px;"
                        alt="Business Preview">
                </div>

                @if($existingListings->isNotEmpty())
                    {{-- Already run a business (or more than one) — pick which
                         one to manage, or start a fresh listing, instead of
                         landing straight back in the "add a business" form
                         every time this page is opened. --}}
                    <div class="col-lg-7" id="sbSelectBusinessCol">
                        <div class="card shadow border-0 listing-card">
                            <div class="card-body p-4 p-md-5">
                                <h3 class="mb-4">Select Your Business</h3>

                                <div class="sb-select-business-list">
                                    @foreach($existingListings as $existingListing)
                                        <a href="{{ route('user.listing.dashboard', $existingListing->id) }}" class="sb-select-business-row">
                                            <span class="sb-select-business-avatar">
                                                @if($existingListing->thumb)
                                                    <img src="{{ loadImage('assets/images/listing/thumb/'.$existingListing->thumb) }}" alt="{{ $existingListing->name }}">
                                                @else
                                                    {{ strtoupper(substr($existingListing->name, 0, 1)) }}
                                                @endif
                                            </span>
                                            <span class="sb-select-business-body">
                                                <strong>
                                                    {{ $existingListing->name }}
                                                    @if($existingListing->verified)
                                                        <i class="fas fa-circle-check sb-select-business-verified"></i>
                                                    @endif
                                                </strong>
                                                <small>{{ optional($existingListing->location)->name ?? $existingListing->area }}</small>
                                            </span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @endforeach
                                </div>

                                <div class="sb-select-business-or">OR</div>

                                <button type="button" class="btn btn__base w-100" id="sbAddNewBusinessBtn">Add New Business</button>
                            </div>
                        </div>
                    </div>
                @endif

             <div class="col-lg-7 free-listing-form-scroll {{ $existingListings->isNotEmpty() ? 'd-none' : '' }}" id="sbNewBusinessFormCol">
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
@media (max-width: 767px){
    .free-listing-left{
        display: none !important;
    }
}
.contact-with-author{
    display:none;
}
.free-listing-page {
    padding-top: 0;
   
}
@media (min-width: 992px) {
    .free-listing-form-scroll {
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .free-listing-form-scroll::-webkit-scrollbar {
        display: none;
    }

    .free-listing-form-scroll .wizard-step {
        padding-bottom: 100px;
    }
}                 


                                #areaManual,
.sb-picker-input{
    height: 48px;
    line-height: 48px;
    padding-top: 0;
    padding-bottom: 0;
}

/* Submitted field — kept in the DOM for the pincode auto-fill and the
   wizard's payload, but the search combobox is what the user sees. */
.sb-picker-native{
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.sb-picker{
    position: relative;
}

.sb-picker-options{
    position: absolute;
    z-index: 1060;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    max-height: 260px;
    overflow-y: auto;
    margin: 0;
    padding: 6px;
    list-style: none;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
}

.sb-picker-options li{
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
}

.sb-picker-options li.is-active,
.sb-picker-options li[aria-selected="true"]{
    background: #e7f1ff;
}

.sb-picker-empty{
    color: #6c757d;
    cursor: default;
}

.area-manual-toggle{
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}

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

                                /* Theme Bootstrap 4 par hai, jisme .form-label aur .form-select
                                   dono hain hi nahi. Isliye label select ke saath ek hi line me
                                   chipak jata tha aur select browser ka apna native box dikhta
                                   tha — paas wale input se alag. Dono yahan khud define kiye. */
                                .form-label {
                                    display: block;
                                    margin-bottom: 6px;
                                }

                                .form-select {
                                    width: 100%;
                                    padding: 0 34px 0 12px;
                                    border: 1px solid #ced4da;
                                    color: #212529;
                                    background-color: #fff;
                                    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
                                    background-repeat: no-repeat;
                                    background-position: right 12px center;
                                    background-size: 14px 10px;
                                    -webkit-appearance: none;
                                    -moz-appearance: none;
                                    appearance: none;
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

                                /* Native select hai — koi .form-select styling theme me nahi.
                                   Fixed width dene par "Mr" baayen aur browser ka arrow dayen
                                   kinare chipak jata tha, beech me khaali patti. Content jitni
                                   chaudai me dono paas rehte hain. */
                                .country-code {
                                    width: auto;
                                    min-width: 84px;
                                    flex: 0 0 auto;
                                    padding-left: 12px;
                                    padding-right: 28px;
                                    background-position: right 9px center;
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

                                .field-error {
    border-color: #dc3545;
}

.error-text {
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
}

                            </style>

                            <form action="{{ route('user.listing.store') }}" method="POST" enctype="multipart/form-data"
                                id="multiStepForm">
                                @csrf


                            

</div>

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
                                            value="{{ old('business_name') }}" placeholder="Enter Business Name" required>
                                    </div>

                                   

                                    <div class="mb-3">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" id="pincode" class="form-control" placeholder="Pincode"
                                            name="pincode" value="{{ old('pincode') }}" required>
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
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label mb-0">Area</label>
                                                <a href="javascript:void(0)" id="areaManualToggle" class="area-manual-toggle">+ Add manually</a>
                                            </div>

                                            <select class="form-control" id="area" name="area" required>
                                                <option value="">Select Area</option>
                                            </select>

                                            {{--
                                                Pincode API (India Post) sirf uske apne registered post-office
                                                names deta hai — kai localities (e.g. "Noida Sector 9") usme
                                                list nahi hoti. Ye text input tab dikhta hai jab user "Add
                                                manually" par click kare; select ki jagah lekar wahi
                                                name="area" bhejta hai, taaki backend me koi change na karna
                                                pade.
                                            --}}
                                            <input type="text" class="form-control d-none" id="areaManual"
                                                name="area" placeholder="Enter your area / sector name" disabled>
                                        </div>

                                    </div>

<div class="mb-3">
    <label class="form-label" for="locationSearch">Location</label>

    <select class="form-control" name="location_id" id="location_id" required>
        <option value="">Select Location</option>

        @foreach($locations as $location)
            <option value="{{ $location->id }}">
                {{ $location->name }}
            </option>
        @endforeach

    </select>
</div>

{{--
    Area (~post-office list) aur Location (~100 cities) dono lambi lists hain
    jinme type karke dhoondhne ka koi tareeka nahi tha. Ye script har aise
    select ke aage ek search box laga deti hai; select khud DOM me rehta hai
    aur wahi submit hota hai, isliye pincode auto-fill, "Add manually" toggle
    aur wizard ka payload — sab pehle jaisa chalta hai.
--}}
<script>
window.sbSearchableSelect = (function () {
    const norm = v => String(v).toLowerCase().trim();

    function enhance(select, config) {
        if (!select || select.sbPicker) {
            return null;
        }

        const settings = config || {};

        const picker = document.createElement('div');
        picker.className = 'sb-picker';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control sb-picker-input';
        input.placeholder = settings.placeholder || 'Search';
        input.autocomplete = 'off';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-autocomplete', 'list');

        if (settings.inputId) {
            input.id = settings.inputId;
        }

        // required visible input par chahiye: chhupe hue select par hota to
        // browser "not focusable" keh kar submit hi rok deta.
        if (select.required) {
            input.required = true;
            select.required = false;
        }

        const list = document.createElement('ul');
        list.className = 'sb-picker-options d-none';
        list.setAttribute('role', 'listbox');

        picker.appendChild(input);
        picker.appendChild(list);
        select.parentNode.insertBefore(picker, select);

        select.classList.add('sb-picker-native');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');

        let active = -1;

        const items = () => Array.from(list.querySelectorAll('li[data-value]'));

        function open() {
            list.classList.remove('d-none');
            input.setAttribute('aria-expanded', 'true');
        }

        function close() {
            list.classList.add('d-none');
            input.setAttribute('aria-expanded', 'false');
            active = -1;
        }

        function render(query) {
            const q = norm(query);

            // Options select se har baar padhe jaate hain — area ki list
            // pincode ke jawab par dobara bhari jaati hai.
            const matches = Array.from(select.options)
                .filter(o => o.value && norm(o.textContent).includes(q));

            list.innerHTML = '';
            active = -1;

            if (!matches.length) {
                const empty = document.createElement('li');
                empty.className = 'sb-picker-empty';
                empty.textContent = settings.emptyText || 'No match found';
                list.appendChild(empty);
            } else {
                matches.forEach(function (o) {
                    const li = document.createElement('li');
                    li.textContent = o.textContent.trim();
                    li.dataset.value = o.value;
                    li.setAttribute('role', 'option');
                    li.setAttribute('aria-selected', String(o.value === select.value));
                    list.appendChild(li);
                });
            }

            open();
        }

        function highlight(index) {
            const all = items();
            if (!all.length) {
                return;
            }

            active = (index + all.length) % all.length;
            all.forEach((li, i) => li.classList.toggle('is-active', i === active));
            all[active].scrollIntoView({ block: 'nearest' });
        }

        // Input sirf wahi dikhata hai jo select me set hai, taaki type karke
        // chhoda hua text galti se chuni hui value na lage.
        function syncFromSelect() {
            const chosen = select.selectedOptions[0];
            input.value = chosen && chosen.value ? chosen.textContent.trim() : '';
        }

        function choose(li) {
            if (!li || !li.dataset.value) {
                return;
            }

            select.value = li.dataset.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncFromSelect();
            close();
        }

        input.addEventListener('focus', () => render(''));

        input.addEventListener('input', function () {
            select.value = '';
            render(input.value);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                list.classList.contains('d-none') ? render(input.value) : highlight(active + 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlight(active - 1);
            } else if (e.key === 'Enter' && !list.classList.contains('d-none')) {
                const all = items();
                if (all.length) {
                    e.preventDefault();
                    choose(all[active > -1 ? active : 0]);
                }
            } else if (e.key === 'Escape') {
                close();
                syncFromSelect();
            }
        });

        // mousedown, click nahi: input ka blur pehle chal kar list band kar
        // deta aur click kahin nahi girta.
        list.addEventListener('mousedown', function (e) {
            const li = e.target.closest('li[data-value]');
            if (li) {
                e.preventDefault();
                choose(li);
            }
        });

        input.addEventListener('blur', () => setTimeout(syncFromSelect, 0));

        document.addEventListener('click', function (e) {
            if (!picker.contains(e.target)) {
                close();
                syncFromSelect();
            }
        });

        select.addEventListener('change', syncFromSelect);
        syncFromSelect();

        select.sbPicker = {
            element: picker,
            input: input,
            sync: syncFromSelect,
            setActive: function (on) {
                picker.classList.toggle('d-none', !on);
                input.disabled = !on;
                if (!on) {
                    close();
                }
            }
        };

        return select.sbPicker;
    }

    return enhance;
})();

sbSearchableSelect(document.getElementById('area'), {
    inputId: 'areaSearch',
    placeholder: 'Search your area',
    emptyText: 'No area found'
});

sbSearchableSelect(document.getElementById('location_id'), {
    inputId: 'locationSearch',
    placeholder: 'Search your city',
    emptyText: 'No city found'
});
</script>


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
                                                placeholder="Mobile Number" required >
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
                <input id="addAnotherEmail" type="email" class="form-control" name="extra_emails[]" placeholder="Email" required>
               
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

                                  <!-- 24 Hours checkbox -->
<div class="form-check mb-3">
    <input
        class="form-check-input"
        type="checkbox"
        id="open24Hours"
        name="open_24_hours"
        value="1"
    >

    <label class="form-check-label checkbox-text" for="open24Hours">
        Open 24 Hours
    </label>
</div>

<!-- Normal business timings -->
<div id="timeSlotsWrapper">
    <div class="time-slot-box time-slot-item">
        <div class="row">

            <!-- Open time -->
          <div class="col-md-6 mb-3">
    <label class="form-label">Open at</label>

    <select
        class="form-select business-open-time"
        name="open_time[]"
        required
    >
        <option value="">Select Time</option>
    </select>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label">Close at</label>

    <select
        class="form-select business-close-time"
        name="close_time[]"
        required
    >
        <option value="">Select Time</option>
    </select>
</div>


        </div>

        <div class="time-slot-error error-text d-none"></div>
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

    /* =====================================================
       WIZARD STEPS
    ===================================================== */

    function showStep(step) {
        document.querySelectorAll(".wizard-step").forEach(function (item) {
            item.classList.remove("active");
        });

        const selectedStep = document.querySelector(
            '.wizard-step[data-step="' + step + '"]'
        );

        if (selectedStep) {
            selectedStep.classList.add("active");
        }

        document.querySelectorAll(".step-dot").forEach(function (dot) {
            dot.classList.remove("active");
        });

        for (let i = 1; i <= step; i++) {
            const activeDot = document.querySelector(
                '.step-dot[data-step-dot="' + i + '"]'
            );

            if (activeDot) {
                activeDot.classList.add("active");
            }
        }

        rememberStep(step);

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    /* =====================================================
       REFRESH PAR WAHIN RAHO
       Page reload hone par wizard hamesha step 1 se shuru ho jata tha, chahe
       user chauthe step par ho. Step number tab tak yaad rehta hai jab tak
       tab khula hai; listing submit hote hi bhula diya jata hai.
    ===================================================== */

    const STEP_KEY = "sbListingWizardStep";

    function rememberStep(step) {
        try {
            sessionStorage.setItem(STEP_KEY, String(step));
        } catch (e) {
            /* private mode me storage band ho sakta hai */
        }
    }

    function forgetStep() {
        try {
            sessionStorage.removeItem(STEP_KEY);
        } catch (e) {}
    }

    function restoreStep() {
        // Jinke paas pehle se business hai unhe "Select Your Business" milta
        // hai; form chhupa ho to usme kood kar jaana galat hoga.
        const formCol = document.getElementById("sbNewBusinessFormCol");
        if (formCol && formCol.classList.contains("d-none")) {
            return;
        }

        let saved = 0;
        try {
            saved = parseInt(sessionStorage.getItem(STEP_KEY) || "0", 10);
        } catch (e) {}

        if (saved > 1 && saved <= totalSteps) {
            currentStep = saved;
            showStep(currentStep);
        }
    }


    /* =====================================================
       FORM ERRORS
    ===================================================== */

    function clearStepErrors(stepEl) {
        if (!stepEl) {
            return;
        }

        stepEl.querySelectorAll(".field-error").forEach(function (element) {
            element.classList.remove("field-error");
        });

        stepEl.querySelectorAll(".error-text:not(.time-slot-error)")
            .forEach(function (element) {
                element.remove();
            });

        stepEl.querySelectorAll(".time-slot-error").forEach(function (element) {
            element.textContent = "";
            element.classList.add("d-none");
        });
    }


    function showFieldError(field, message) {
        if (!field) {
            return;
        }

        field.classList.add("field-error");

        const parent = field.parentElement;

        if (!parent.querySelector(".error-text")) {
            const error = document.createElement("div");

            error.className = "error-text";
            error.innerText = message;

            parent.appendChild(error);
        }
    }


    /* =====================================================
       BUSINESS TIME OPTIONS
    ===================================================== */

    /*
     * 30 minute interval par timing options:
     *
     * 12:00 AM
     * 12:30 AM
     * 01:00 AM
     * ...
     * 11:30 PM
     */
    function createBusinessTimeOptions() {
        let options = '<option value="">Select Time</option>';

        for (let hour = 0; hour < 24; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {

                const period = hour >= 12 ? "PM" : "AM";

                let displayHour = hour % 12;

                if (displayHour === 0) {
                    displayHour = 12;
                }

                const formattedHour = String(displayHour).padStart(2, "0");
                const formattedMinute = String(minute).padStart(2, "0");

                const displayTime =
                    formattedHour + ":" + formattedMinute + " " + period;

                options +=
                    '<option value="' + displayTime + '">' +
                    displayTime +
                    '</option>';
            }
        }

        return options;
    }


    /*
     * Existing aur newly-added timing dropdowns me options load karega.
     */
    function initializeBusinessTimeDropdowns(container) {
        const target = container || document;

        target.querySelectorAll(
            ".business-open-time, .business-close-time"
        ).forEach(function (select) {

            if (select.dataset.timeInitialized === "1") {
                return;
            }

            const previousValue = select.value;

            select.innerHTML = createBusinessTimeOptions();
            select.dataset.timeInitialized = "1";

            if (previousValue) {
                select.value = previousValue;
            }
        });
    }


    /*
     * Time ko minutes me convert karta hai.
     *
     * 07:00 PM = 1140
     * 12:00 AM = 0
     * 12:00 PM = 720
     */
    function timeToMinutes(timeValue) {
        if (!timeValue) {
            return null;
        }

        const timeParts = timeValue.trim().split(" ");

        if (timeParts.length !== 2) {
            return null;
        }

        const time = timeParts[0];
        const period = timeParts[1].toUpperCase();

        const parts = time.split(":");

        let hour = parseInt(parts[0], 10);
        const minute = parseInt(parts[1], 10);

        if (
            Number.isNaN(hour) ||
            Number.isNaN(minute) ||
            !["AM", "PM"].includes(period)
        ) {
            return null;
        }

        if (period === "AM") {
            /*
             * 12:00 AM midnight hota hai.
             */
            if (hour === 12) {
                hour = 0;
            }
        } else {
            /*
             * 12:00 PM noon hota hai.
             */
            if (hour !== 12) {
                hour += 12;
            }
        }

        return (hour * 60) + minute;
    }


    /*
     * Single timing slot validate karega.
     *
     * 07:00 PM - 12:00 AM valid hoga.
     * Close time ko next day maana jayega.
     */
    function validateTimeSlot(slot) {
        const openSelect = slot.querySelector(
            'select[name="open_time[]"]'
        );

        const closeSelect = slot.querySelector(
            'select[name="close_time[]"]'
        );

        const errorBox = slot.querySelector(".time-slot-error");

        if (!openSelect || !closeSelect) {
            return true;
        }

        openSelect.classList.remove("field-error");
        closeSelect.classList.remove("field-error");

        if (errorBox) {
            errorBox.textContent = "";
            errorBox.classList.add("d-none");
        }

        const openTime = openSelect.value;
        const closeTime = closeSelect.value;

        if (!openTime || !closeTime) {
            if (!openTime) {
                openSelect.classList.add("field-error");
            }

            if (!closeTime) {
                closeSelect.classList.add("field-error");
            }

            if (errorBox) {
                errorBox.textContent =
                    "Please select opening and closing time.";

                errorBox.classList.remove("d-none");
            }

            return false;
        }

        const openMinutes = timeToMinutes(openTime);
        let closeMinutes = timeToMinutes(closeTime);

        if (openMinutes === null || closeMinutes === null) {
            if (errorBox) {
                errorBox.textContent = "Please select a valid timing.";
                errorBox.classList.remove("d-none");
            }

            return false;
        }

        /*
         * Same opening aur closing time allowed nahi hai.
         *
         * Example:
         * 07:00 PM - 07:00 PM invalid.
         *
         * 24 hours ke liye checkbox use hoga.
         */
        if (openMinutes === closeMinutes) {
            openSelect.classList.add("field-error");
            closeSelect.classList.add("field-error");

            if (errorBox) {
                errorBox.textContent =
                    "Opening and closing time cannot be the same. Select Open 24 Hours if the business stays open all day.";

                errorBox.classList.remove("d-none");
            }

            return false;
        }

        /*
         * Close time open time se chhota ho to closing next day hogi.
         *
         * Example:
         *
         * Open:  07:00 PM = 1140
         * Close: 12:00 AM = 0
         *
         * 0 + 1440 = next day 12:00 AM
         */
        if (closeMinutes < openMinutes) {
            closeMinutes += 24 * 60;
        }

        const duration = closeMinutes - openMinutes;

        /*
         * Duration zero ya 24 hours se zyada nahi honi chahiye.
         */
        if (duration <= 0 || duration >= (24 * 60)) {
            openSelect.classList.add("field-error");
            closeSelect.classList.add("field-error");

            if (errorBox) {
                errorBox.textContent =
                    "Please select a valid business timing.";

                errorBox.classList.remove("d-none");
            }

            return false;
        }

        return true;
    }


    /*
     * Saare timing slots validate karega.
     */
    function validateAllTimeSlots() {
        const open24Hours = document.getElementById("open24Hours");

        /*
         * 24 Hours checked hai to Open/Close validation nahi hogi.
         */
        if (open24Hours && open24Hours.checked) {
            return true;
        }

        const slots = document.querySelectorAll(".time-slot-item");

        if (!slots.length) {
            return false;
        }

        let valid = true;

        slots.forEach(function (slot) {
            if (!validateTimeSlot(slot)) {
                valid = false;
            }
        });

        return valid;
    }


    /* =====================================================
       OPEN 24 HOURS
    ===================================================== */

    function toggleOpen24Hours() {
        const checkbox = document.getElementById("open24Hours");
        const wrapper = document.getElementById("timeSlotsWrapper");
        const addTimeSlotButton = document.getElementById("addTimeSlot");

        if (!checkbox || !wrapper) {
            return;
        }

        const isOpen24Hours = checkbox.checked;

        /*
         * Checked hone par timing section hide hoga.
         */
        wrapper.style.display = isOpen24Hours ? "none" : "";

        if (addTimeSlotButton) {
            addTimeSlotButton.style.display =
                isOpen24Hours ? "none" : "inline-block";
        }

        wrapper.querySelectorAll(
            'select[name="open_time[]"], select[name="close_time[]"]'
        ).forEach(function (field) {
            field.disabled = isOpen24Hours;

            if (isOpen24Hours) {
                field.classList.remove("field-error");
            }
        });

        wrapper.querySelectorAll(".time-slot-error")
            .forEach(function (errorBox) {
                errorBox.textContent = "";
                errorBox.classList.add("d-none");
            });
    }


    const open24HoursCheckbox =
        document.getElementById("open24Hours");

    if (open24HoursCheckbox) {
        open24HoursCheckbox.addEventListener(
            "change",
            toggleOpen24Hours
        );
    }


    /* =====================================================
       STEP VALIDATION
    ===================================================== */

    function validateCurrentStep() {
        const stepEl = document.querySelector(
            '.wizard-step[data-step="' + currentStep + '"]'
        );

        if (!stepEl) {
            return false;
        }

        clearStepErrors(stepEl);

        let valid = true;

        /*
         * Disabled fields validate nahi honge.
         */
        stepEl.querySelectorAll(
            "input[required]:not(:disabled), " +
            "select[required]:not(:disabled), " +
            "textarea[required]:not(:disabled)"
        ).forEach(function (field) {

            if (!String(field.value).trim()) {
                showFieldError(field, "This field is required");
                valid = false;
            }
        });


        /*
         * Contact details validation.
         */
        if (currentStep === 2) {
            const mobile = stepEl.querySelector(
                'input[name="mobile_numbers[]"]'
            );

            const email = stepEl.querySelector(
                'input[name="extra_emails[]"]'
            );

            if (
                mobile &&
                !/^[6-9][0-9]{9}$/.test(mobile.value.trim())
            ) {
                showFieldError(
                    mobile,
                    "Enter valid 10 digit mobile number"
                );

                valid = false;
            }

            if (
                email &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
                    email.value.trim()
                )
            ) {
                showFieldError(
                    email,
                    "Enter valid email address"
                );

                valid = false;
            }
        }


        /*
         * Business days aur timing validation.
         */
        if (currentStep === 3) {
            const selectedDays = stepEl.querySelectorAll(
                'input[name="business_days[]"]:checked'
            );

            if (selectedDays.length === 0) {
                const daysWrapper =
                    stepEl.querySelector(".weekday-list");

                if (daysWrapper) {
                    const error = document.createElement("div");

                    error.className = "error-text";
                    error.innerText =
                        "Please select at least one business day.";

                    daysWrapper.insertAdjacentElement(
                        "afterend",
                        error
                    );
                }

                valid = false;
            }

            if (!validateAllTimeSlots()) {
                valid = false;
            }
        }


        /*
         * Category validation.
         */
        if (
            currentStep === 4 &&
            typeof selectedCategory !== "undefined" &&
            !selectedCategory
        ) {
            const categoryField =
                document.getElementById("categorySearch");

            showFieldError(
                categoryField,
                "Please select category from list"
            );

            valid = false;
        }

        return valid;
    }


    document.querySelectorAll(".next-step").forEach(function (button) {
        button.addEventListener("click", function () {

            if (!validateCurrentStep()) {
                return;
            }

            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });


    document.querySelectorAll(".prev-step").forEach(function (button) {
        button.addEventListener("click", function () {

            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
    });

    const wizardForm = document.getElementById("multiStepForm");
    if (wizardForm) {
        wizardForm.addEventListener("submit", forgetStep);
    }

    restoreStep();


    /* =====================================================
       AREA - ADD MANUALLY
       ===================================================== */

    (function () {
        const toggleLink = document.getElementById("areaManualToggle");
        const areaSelect = document.getElementById("area");
        const areaManual = document.getElementById("areaManual");

        if (!toggleLink || !areaSelect || !areaManual) {
            return;
        }

        let manualMode = false;

        toggleLink.addEventListener("click", function () {
            manualMode = !manualMode;

            if (manualMode) {
                // Manual input le raha hai area ka charge — select ko disable
                // karna zaroori hai, warna dono name="area" ek saath submit
                // ho jayenge aur select ki khaali value manual value ko
                // overwrite kar degi.
                areaSelect.classList.add("d-none");
                areaSelect.disabled = true;

                // Select ke aage lagi search box bhi hatni chahiye, warna
                // manual input ke upar ek adhoora area field khada rehta hai.
                if (areaSelect.sbPicker) {
                    areaSelect.sbPicker.setActive(false);
                }

                areaManual.classList.remove("d-none");
                areaManual.disabled = false;
                areaManual.required = true;
                areaManual.focus();

                toggleLink.textContent = "Choose from list";
            } else {
                areaManual.classList.add("d-none");
                areaManual.disabled = true;
                areaManual.required = false;
                areaManual.value = "";

                areaSelect.classList.remove("d-none");
                areaSelect.disabled = false;

                if (areaSelect.sbPicker) {
                    areaSelect.sbPicker.setActive(true);
                }

                toggleLink.textContent = "+ Add manually";
            }
        });
    })();

    /* =====================================================
       PINCODE
    ===================================================== */

    const pincodeInput = document.getElementById("pincode");

    if (pincodeInput) {
        pincodeInput.addEventListener("keyup", async function () {

           const match = this.value.match(/^\s*(\d{6})\s*$/);

if (!match) {
    return;
}

const pin = match[1];

            try {
                const response = await fetch(
                    "{{ route('pincode.fetch', ['pin' => '__PIN__']) }}".replace("__PIN__", pin)
                );

                if (!response.ok) {
                    throw new Error('HTTP error, status: ' + response.status);
                }

                const data = await response.json();

                const cityField =
                    document.getElementById("city");

                const stateField =
                    document.getElementById("state");

                const areaDropdown =
                    document.getElementById("area");

                const latitudeField =
                    document.getElementById("latitude");

                const longitudeField =
                    document.getElementById("longitude");

                if (cityField) {
                    cityField.value = "";
                }

                if (stateField) {
                    stateField.value = "";
                }

                if (areaDropdown) {
                    areaDropdown.innerHTML =
                        '<option value="">Select Area</option>';
                }

                if (latitudeField) {
                    latitudeField.value = "";
                }

                if (longitudeField) {
                    longitudeField.value = "";
                }

                if (data && data.status && Array.isArray(data.areas) && data.areas.length > 0) {

                    if (cityField) {
                        cityField.value =
                            data.city || "";
                    }

                    if (stateField) {
                        stateField.value =
                            data.state || "";
                    }

                    if (areaDropdown) {
                        data.areas.forEach(function (areaName) {
                            const option =
                                document.createElement("option");

                            option.value = areaName;
                            option.textContent = areaName;

                            areaDropdown.appendChild(option);
                        });

                        // Nayi list aayi hai, purana chuna hua area ab valid
                        // nahi — search box ko bata do.
                        areaDropdown.dispatchEvent(
                            new Event('change', { bubbles: true })
                        );
                    }

                    const locationSelect = document.getElementById("location_id");
                    if (locationSelect && data.city) {
                        const city = data.city.toLowerCase().trim();
                        Array.from(locationSelect.options).some(function (option) {
                            if (option.textContent.toLowerCase().trim() === city) {
                                locationSelect.value = option.value;
                                // The search box mirrors this field, and it
                                // only hears about a value set in code if the
                                // event is raised by hand.
                                locationSelect.dispatchEvent(
                                    new Event('change', { bubbles: true })
                                );
                                return true;
                            }

                            return false;
                        });
                    }

                    const searchText =
                        pin + ", " +
                        (cityField ? cityField.value : "") + ", " +
                        (stateField ? stateField.value : "") +
                        ", India";

                    try {
                        const geoResponse = await fetch(
                            "{{ route('geocode.address') }}" +
                            "?address=" +
                            encodeURIComponent(searchText)
                        );

                        const locationData =
                            await geoResponse.json();

                        if (
                            locationData.latitude &&
                            locationData.longitude
                        ) {
                            if (latitudeField) {
                                latitudeField.value =
                                    locationData.latitude;
                            }

                            if (longitudeField) {
                                longitudeField.value =
                                    locationData.longitude;
                            }
                        }

                    } catch (error) {
                        console.error(
                            "Geocoding error:",
                            error
                        );
                    }

                } else if (data) {
                    console.warn("Area not found for pincode " + pin, data);
                } else {
                    console.warn("Invalid response for pincode " + pin);
                    alert("Area not found for this pincode. Please use Add manually.");
                }

            } catch (error) {
                console.error(
                    "Error fetching pincode data:",
                    error
                );
                alert("Unable to fetch area data. Please use Add manually.");
            }
        });
    }


    /* =====================================================
       PHONE ROWS
    ===================================================== */

    function createPhoneRow(name, wrapperClass) {
        return `
            <div class="phone-group ${wrapperClass}">
                <select
                    class="form-select country-code"
                    name="${name}_country_code[]"
                >
                    <option value="+91">+91</option>
                </select>

                <input
                    type="text"
                    class="form-control"
                    name="${name}_numbers[]"
                    placeholder="${name.replace("_", " ")}"
                >

                <button
                    type="button"
                    class="input-remove remove-row"
                >
                    ×
                </button>
            </div>
        `;
    }


    const addMobileButton =
        document.getElementById("addMobileNumber");

    if (addMobileButton) {
        addMobileButton.addEventListener("click", function () {
            const wrapper =
                document.getElementById("mobileNumbersWrapper");

            if (wrapper) {
                wrapper.insertAdjacentHTML(
                    "beforeend",
                    createPhoneRow(
                        "mobile",
                        "mobile-number-item"
                    )
                );
            }
        });
    }


    const addWhatsappButton =
        document.getElementById("addWhatsappNumber");

    if (addWhatsappButton) {
        addWhatsappButton.addEventListener("click", function () {
            const wrapper =
                document.getElementById("whatsappNumbersWrapper");

            if (wrapper) {
                wrapper.insertAdjacentHTML(
                    "beforeend",
                    createPhoneRow(
                        "whatsapp",
                        "whatsapp-number-item"
                    )
                );
            }
        });
    }


    const addLandlineButton =
        document.getElementById("addLandlineNumber");

    if (addLandlineButton) {
        addLandlineButton.addEventListener("click", function () {
            const wrapper =
                document.getElementById("landlineNumbersWrapper");

            if (wrapper) {
                wrapper.insertAdjacentHTML(
                    "beforeend",
                    createPhoneRow(
                        "landline",
                        "landline-number-item"
                    )
                );
            }
        });
    }


    const addAnotherEmail =
        document.getElementById("addAnotherEmail");

    /*
     * Ye tab chalega jab addAnotherEmail koi button/link ho.
     */
    if (
        addAnotherEmail &&
        (
            addAnotherEmail.tagName === "BUTTON" ||
            addAnotherEmail.tagName === "A"
        )
    ) {
        addAnotherEmail.addEventListener("click", function () {
            const wrapper =
                document.getElementById("emailsWrapper");

            if (wrapper) {
                wrapper.insertAdjacentHTML(
                    "beforeend",
                    `
                    <div class="phone-group email-item">
                        <input
                            type="email"
                            class="form-control"
                            name="extra_emails[]"
                            placeholder="Email"
                        >

                        <button
                            type="button"
                            class="input-remove remove-row"
                        >
                            ×
                        </button>
                    </div>
                    `
                );
            }
        });
    }


    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("remove-row")) {
            const row = event.target.closest(".phone-group");

            if (row) {
                row.remove();
            }
        }
    });


    /* =====================================================
       SAME AS MOBILE
    ===================================================== */

    const sameAsMobile =
        document.getElementById("sameAsMobile");

    if (sameAsMobile) {
        sameAsMobile.addEventListener("change", function () {
            const mobileInput = document.querySelector(
                'input[name="mobile_numbers[]"]'
            );

            const whatsappInput =
                document.querySelector(".whatsapp-input");

            if (
                this.checked &&
                mobileInput &&
                whatsappInput
            ) {
                whatsappInput.value = mobileInput.value;
            } else if (whatsappInput) {
                whatsappInput.value = "";
            }
        });
    }


    /* =====================================================
       SELECT ALL DAYS
    ===================================================== */

    const selectAllDays =
        document.getElementById("selectAllDays");

    if (selectAllDays) {
        selectAllDays.addEventListener("change", function () {
            document.querySelectorAll(
                'input[name="business_days[]"]'
            ).forEach(function (day) {
                day.checked = selectAllDays.checked;
            });
        });
    }


    /* =====================================================
       ADD ANOTHER TIME SLOT
    ===================================================== */

    const addTimeSlotButton =
        document.getElementById("addTimeSlot");

    if (addTimeSlotButton) {
        addTimeSlotButton.addEventListener("click", function () {
            const wrapper =
                document.getElementById("timeSlotsWrapper");

            if (!wrapper) {
                return;
            }

            wrapper.insertAdjacentHTML(
                "beforeend",
                `
                <div class="time-slot-box time-slot-item">
                    <div class="row">

                        <div class="col-md-5 mb-3">
                            <label class="form-label">
                                Open at
                            </label>

                            <select
                                class="form-select business-open-time"
                                name="open_time[]"
                            >
                                <option value="">
                                    Select Time
                                </option>
                            </select>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label class="form-label">
                                Close at
                            </label>

                            <select
                                class="form-select business-close-time"
                                name="close_time[]"
                            >
                                <option value="">
                                    Select Time
                                </option>
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
                `
            );

            const newlyAddedSlot =
                wrapper.lastElementChild;

            initializeBusinessTimeDropdowns(
                newlyAddedSlot
            );
        });
    }


    /*
     * Time slot remove.
     */
    document.addEventListener("click", function (event) {
        if (
            !event.target.classList.contains(
                "remove-time-slot"
            )
        ) {
            return;
        }

        const allSlots =
            document.querySelectorAll(".time-slot-item");

        /*
         * Kam se kam ek timing slot rahega.
         */
        if (allSlots.length <= 1) {
            return;
        }

        const slot =
            event.target.closest(".time-slot-item");

        if (slot) {
            slot.remove();
        }
    });


    /*
     * User time change kare to us slot ka error clear ho.
     */
    document.addEventListener("change", function (event) {
        if (
            !event.target.matches(
                ".business-open-time, .business-close-time"
            )
        ) {
            return;
        }

        const slot =
            event.target.closest(".time-slot-item");

        if (!slot) {
            return;
        }

        event.target.classList.remove("field-error");

        const errorBox =
            slot.querySelector(".time-slot-error");

        if (errorBox) {
            errorBox.textContent = "";
            errorBox.classList.add("d-none");
        }
    });


    /* =====================================================
       PHOTO PREVIEW
    ===================================================== */

    document.querySelectorAll(".photo-input")
        .forEach(function (input) {

            input.addEventListener("change", function () {
                const file = this.files[0];

                const parent =
                    this.closest(".photo-upload-card");

                if (!parent) {
                    return;
                }

                const preview =
                    parent.querySelector(".preview-image");

                const text =
                    parent.querySelector(".upload-text");

                if (!file || !preview || !text) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {
                    preview.src = event.target.result;
                    preview.classList.remove("d-none");
                    text.classList.add("d-none");
                };

                reader.readAsDataURL(file);
            });
        });


    /* =====================================================
       SKIP PHOTOS
    ===================================================== */

    const skipPhotos =
        document.getElementById("skipPhotos");

    if (skipPhotos) {
        skipPhotos.addEventListener("click", function () {
            const form =
                document.getElementById("multiStepForm");

            if (form) {
                form.submit();
            }
        });
    }


    /* =====================================================
       INITIALIZATION
    ===================================================== */

   initializeBusinessTimeDropdowns();
toggleOpen24Hours();




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
        let subcategories = [];  // Will be populated via AJAX when category is selected

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
                renderHiddenInputs();

                // Fetch subcategories for this category via AJAX
                // (instead of loading all 700+ subcategories inline on page load)
                fetch(`/admin/subcategories/by-category/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        subcategories = data;
                        // Re-render any subcategory suggestions if they exist
                        if (typeof renderSuggestedSubcategories === 'function') {
                            renderSuggestedSubcategories();
                        }
                    })
                    .catch(err => console.error('Failed to load subcategories:', err));
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
                  
                    renderHiddenInputs();
                }
            }

            if (e.target.classList.contains('remove-tag')) {
                const id = e.target.dataset.id;

                selectedSubcategories = selectedSubcategories.filter(item => item.id != id);

              
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



<div class="modal fade guest-chat-modal" id="guestChatModal" tabindex="-1" role="dialog" aria-labelledby="guestChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="guestChatModalLabel">@lang('Chat with owner')</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
</button>
            </div>
            <div class="modal-body">
                <div id="guestChatLoading" class="guest-chat-loading d-flex align-items-center justify-content-center is-hidden">
                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                    <span class="ml-2">@lang('Checking your session...')</span>
                </div>

                <div class="guest-chat-step d-none" data-step="1">
                    <div class="guest-chat-alert alert alert-danger d-none"></div>
                   <form id="guestChatStartForm">
    @csrf

    <input type="hidden" name="listing_id" value="0">

   <div class="form-group">
    <label>@lang('Your name')</label>
    <input type="text"
           name="name"
           class="form-control form-control-sm"
           value="{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) }}"
           readonly>
</div>

    <div class="form-group">
        <label>@lang('Your email')</label>
        <input type="email" name="email" class="form-control form-control-sm"
               value="{{ auth()->user()->email ?? '' }}" readonly>
    </div>

    <div class="form-group">
        <label>@lang('Phone (optional)')</label>
        <input type="text" name="phone" class="form-control form-control-sm">
    </div>

    <div class="form-group">
        <label>@lang('Message')</label>
        <textarea name="first_message" class="form-control form-control-sm" rows="3" maxlength="1000"></textarea>
    </div>

    <button type="submit" class="btn btn__base w-100">@lang('Start Chat')</button>
</form>
                </div>

                <div class="guest-chat-step d-none" data-step="2">
                    <div class="guest-chat-alert alert alert-danger d-none"></div>
                    <p class="text-muted mb-3">@lang('We sent a 6-digit code to') <strong id="guestChatEmailPreview"></strong></p>
                    <form id="guestChatVerifyForm">
                        @csrf
                      
                        <div class="form-group">
                            <label>@lang('Verification code')</label>
                            <input type="text" name="otp" class="form-control form-control-sm guest-chat-otp" maxlength="6" required>
                        </div>
                        <button type="submit" class="btn btn__base w-100">@lang('Verify & start chat')</button>
                        <button type="button" id="guestChatResend" class="btn btn-link btn-sm mt-2">@lang('Resend code')</button>
                    </form>
                </div>

                <div class="guest-chat-step d-none" data-step="3">
                    <div class="guest-chat-head d-flex justify-content-between align-items-center mb-2">
                        <div>
                        
                            <div class="guest-chat-status" id="guestChatOwnerStatus"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light guest-chat-sound" id="guestChatSoundToggle">
                            <i class="las la-volume-up"></i>
                            <span>@lang('Sound on')</span>
                        </button>
                    </div>
                    <div class="guest-chat-note text-muted mb-2" id="guestChatNote">@lang('Replies may take a few minutes.')</div>
                    <div id="guestChatMessages" class="guest-chat-messages mb-3">
                        <div class="text-center text-muted" id="guestChatEmpty">@lang('No messages yet')</div>
                    </div>
                    <form id="guestChatSendForm">
                        @csrf
                        <div class="input-group">
                            <textarea name="body" id="guestChatMessage" class="form-control form-control-sm" rows="2" placeholder="@lang('Type your message')"></textarea>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn__base">@lang('Send')</button>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">@lang('Press Enter to send, Shift + Enter for new line')</small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<style>
    	
    .guest-chat-messages{
        max-height: 280px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        background: #fafafa;
    }
    .guest-chat-message{
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
    }
    .guest-chat-message.is-guest{
        align-items: flex-end;
    }
    .guest-chat-message .bubble{
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .guest-chat-message.is-guest .bubble{
        background: #e8f7ff;
        border-color: #cfe9f6;
    }
    .guest-chat-message .meta{
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }
    .guest-chat-modal .modal-dialog{
        max-width: 520px;
    }
    .guest-chat-modal .modal-body{
        padding: 20px;
    }
    .guest-chat-modal .form-group label{
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
    }
    .guest-chat-loading{
        min-height: 220px;
        font-weight: 500;
        color: #495057;
    }
    .guest-chat-loading.is-hidden{
        display: none !important;
    }
    .guest-chat-head{
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 8px;
    }
    .guest-chat-owner{
        font-weight: 600;
    }
    .guest-chat-status{
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .guest-chat-status .dot{
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        background: #adb5bd;
    }
    .guest-chat-status .dot.online{
        background: #2ecc71;
    }
    .guest-chat-status .dot.offline{
        background: #ff6b6b;
    }
    .guest-chat-note{
        font-size: 12px;
    }
    .guest-chat-sound{
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .guest-chat-otp{
        letter-spacing: 6px;
        text-align: center;
        font-weight: 600;
    }
    .guest-chat-modal .input-group .form-control{
        border-right: 0;
    }
    .guest-chat-modal .input-group-append .btn{
        border-radius: 0 .25rem .25rem 0;
    }
    .guest-chat-modal .input-group{
        border: 1px solid #e3e7ed;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .guest-chat-modal .input-group .form-control{
        border: 0;
        box-shadow: none;
    }
    .guest-chat-modal .input-group-append .btn{
        border-radius: 0;
    }
</style>

@push('js')
    
<script>

    
(function($){
    "use strict";

    var listingId = 0;
    var ownerName = "Admin";

    var $modal = $('#guestChatModal');
    var $steps = $modal.find('.guest-chat-step');
    var $messages = $('#guestChatMessages');

    var conversationId = null;
    var afterId = 0;
    var polling = null;
    var guestEmail = "{{ auth()->user()->email ?? '' }}";
    var csrfToken = '{{ csrf_token() }}';

    var emptyText = @json(__('No messages yet'));
    var genericError = @json(__('Something went wrong. Please try again.'));
    var textYou = @json(__('You'));
    var soundUrl = @json(asset($themeAsset.'sound/message.wav'));
    var soundAudio = null;
    var soundEnabled = true;

    function showStep(step){
        $steps.addClass('d-none');
        $steps.filter('[data-step="' + step + '"]').removeClass('d-none');
        $modal.find('.guest-chat-alert').addClass('d-none').text('');
        $('#guestChatLoading').addClass('is-hidden');
    }

    function showError(step, message){
        var $alert = $steps.filter('[data-step="' + step + '"]').find('.guest-chat-alert');
        $alert.removeClass('d-none').text(message || genericError);
    }

    function resetChat(){
        $('#guestChatEmailPreview').text('');
        $messages.html('<div class="text-center text-muted" id="guestChatEmpty">' + emptyText + '</div>');
        conversationId = null;
        afterId = 0;
        stopPolling();
    }

    function scrollToBottom(){
        $messages.scrollTop($messages.prop('scrollHeight'));
    }

    function appendMessage(message){
        $('#guestChatEmpty').remove();

        var isGuest = message.sender_type === 'guest' || message.sender_type === 'user';
        var label = isGuest ? textYou : ownerName;

        var html =
            '<div class="guest-chat-message ' + (isGuest ? 'is-guest' : 'is-owner') + '" data-id="' + message.id + '">' +
                '<div class="bubble"></div>' +
                '<div class="meta"><span class="mr-2">' + label + '</span>' + (message.created_at || '') + '</div>' +
            '</div>';

        var $node = $(html);
        $node.find('.bubble').text(message.body);
        $messages.append($node);

        afterId = message.id;
        scrollToBottom();
    }

    function fetchMessages(){
        if (!conversationId) return;

        $.get('{{ route('guest-chat.messages') }}', {
            conversation_id: conversationId,
            after_id: afterId
        }).done(function(resp){
            if (!resp || !resp.ok) return;

            if (resp.messages && resp.messages.length) {
                resp.messages.forEach(function(message){
                    appendMessage(message);
                });
            }
        });
    }

    function startPolling(){
        if (polling) return;
        polling = setInterval(fetchMessages, 2000);
    }

    function stopPolling(){
        if (polling) {
            clearInterval(polling);
            polling = null;
        }
    }

    function parseError(xhr){
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) return xhr.responseJSON.message;
            if (xhr.responseJSON.errors) {
                return Object.values(xhr.responseJSON.errors).flat().join(' ');
            }
        }
        return genericError;
    }

    function getSound(){
        if (!soundAudio) {
            soundAudio = new Audio(soundUrl);
            soundAudio.preload = 'auto';
        }
        return soundAudio;
    }

    function syncSoundToggle(){
        var $btn = $('#guestChatSoundToggle');
        if (!$btn.length) return;

        if (soundEnabled) {
            $btn.find('span').text(@json(__('Sound on')));
            $btn.find('i').removeClass('la-volume-mute').addClass('la-volume-up');
        } else {
            $btn.find('span').text(@json(__('Sound off')));
            $btn.find('i').removeClass('la-volume-up').addClass('la-volume-mute');
        }
    }

    function createAdminChat(){
      var name = "{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) }}";
        var email = "{{ auth()->user()->email ?? '' }}";

        $('#guestChatStartForm input[name=name]').val(name);
        $('#guestChatStartForm input[name=email]').val(email);

        $.ajax({
            method: 'POST',
            url: '{{ route('guest-chat.start') }}',
            data: {
               
                admin_chat: 1,
                name: name,
                email: email,
                phone: '',
                first_message: 'Free listing user chat',
                _token: csrfToken
            }
        }).done(function(resp){
            if (resp && resp.ok) {
                guestEmail = email;
                conversationId = resp.conversation_id || null;

                if (conversationId) {
                    showStep(3);
                    fetchMessages();
                    startPolling();
                } else {
                    $('#guestChatEmailPreview').text(guestEmail);
                    showStep(2);
                }
            } else {
                showStep(1);
            }
        }).fail(function(xhr){
            showStep(1);
            showError(1, parseError(xhr));
        });
    }

    $('#guestChatStartForm').on('submit', function(e){
        e.preventDefault();

        var $form = $(this);
        var data = {
            listing_id: 0,
            admin_chat: 1,
            name: $form.find('input[name=name]').val(),
            email: $form.find('input[name=email]').val(),
            phone: $form.find('input[name=phone]').val(),
            first_message: $form.find('textarea[name=first_message]').val(),
            _token: csrfToken
        };

        $.ajax({
            method: 'POST',
            url: '{{ route('guest-chat.start') }}',
            data: data
        }).done(function(resp){
            if (resp && resp.ok) {
                guestEmail = data.email;
                conversationId = resp.conversation_id || null;

                if (conversationId) {
                    showStep(3);
                    fetchMessages();
                    startPolling();
                } else {
                    $('#guestChatEmailPreview').text(guestEmail);
                    showStep(2);
                }
            }
        }).fail(function(xhr){
            showError(1, parseError(xhr));
        });
    });

    $('#guestChatVerifyForm').on('submit', function(e){
        e.preventDefault();

        $.ajax({
            method: 'POST',
            url: '{{ route('guest-chat.verify') }}',
            data: {
                listing_id: 0,
                admin_chat: 1,
                email: guestEmail,
                otp: $(this).find('input[name=otp]').val(),
                _token: csrfToken
            }
        }).done(function(resp){
            if (resp && resp.ok) {
                conversationId = resp.conversation_id;
                showStep(3);
                fetchMessages();
                startPolling();
            }
        }).fail(function(xhr){
            showError(2, parseError(xhr));
        });
    });

    $('#guestChatSendForm').on('submit', function(e){
        e.preventDefault();

        var body = $('#guestChatMessage').val().trim();
        if (!body || !conversationId) return;

        $.ajax({
            method: 'POST',
            url: '{{ route('guest-chat.send') }}',
            data: {
                conversation_id: conversationId,
                body: body,
                _token: csrfToken
            }
        }).done(function(resp){
            if (resp && resp.ok) {
                $('#guestChatMessage').val('');
                appendMessage(resp.message);
            }
        });
    });

    $('#guestChatMessage').on('keydown', function(e){
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#guestChatSendForm').submit();
        }
    });

    $('#guestChatSoundToggle').on('click', function(){
        soundEnabled = !soundEnabled;
        syncSoundToggle();
    });

    $modal.on('hidden.bs.modal', function(){
        window.guestChatActive = false;
        stopPolling();
    });

   $modal.on('shown.bs.modal', function(){
    window.guestChatActive = true;
});
$(document).on('click', '#openAdminChat', function(e){
    e.preventDefault();

    resetChat();
    syncSoundToggle();

    $steps.addClass('d-none');
    $('#guestChatLoading').removeClass('is-hidden');

    if ($.fn.modal) {
        $('#guestChatModal').modal('show');
    } else {
        $('#guestChatModal').addClass('show').show();
        $('body').addClass('modal-open');
        $('<div class="modal-backdrop fade show"></div>').appendTo(document.body);
    }

    setTimeout(function(){
        createAdminChat();
    }, 300);
});
})(jQuery);
</script>


@endpush

<style>
    body:has(.free-listing-page) a[href*="wa.me"],
body:has(.free-listing-page) a[href*="whatsapp"],
body:has(.free-listing-page) .whatsapp-btn,
body:has(.free-listing-page) .whatsapp-float,
body:has(.free-listing-page) .floating-whatsapp,
body:has(.free-listing-page) .floating-contact,
body:has(.free-listing-page) .float-whatsapp {
    display: none !important;
}

/* only free listing page */
.free-listing-page ~ .whatsapp-btn,
.free-listing-page ~ .whatsapp-float,
.free-listing-page ~ .floating-whatsapp,
.free-listing-page ~ a[href*="wa.me"],
.free-listing-page ~ a[href*="whatsapp"]{
    display: none;
}

.free-listing-chat-float {
    position: fixed;
    right: 15px;
    bottom: 150px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: #25d366;
    color: #fff;
    border: 0;
    z-index: 9999;
}

.sb-select-business-list{ margin-bottom: 20px; }
.sb-select-business-row{
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 4px;
    border-bottom: 1px solid #eee;
    color: #222;
}
.sb-select-business-row:last-child{ border-bottom: 0; }
.sb-select-business-avatar{
    width: 42px;
    height: 42px;
    border-radius: 8px;
    background: #7c6fe8;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
    overflow: hidden;
}
.sb-select-business-avatar img{ width: 100%; height: 100%; object-fit: cover; }
.sb-select-business-body{ flex: 1; min-width: 0; }
.sb-select-business-body strong{ display: block; font-size: 15px; font-weight: 700; color: #222; }
.sb-select-business-verified{ color: #1e90ff; font-size: 13px; margin-left: 4px; }
.sb-select-business-body small{ display: block; font-size: 12.5px; color: #888; }
.sb-select-business-row > i:last-child{ color: #bbb; font-size: 13px; }
.sb-select-business-or{ text-align: center; color: #999; font-size: 13px; margin: 6px 0 22px; }

</style>

<button type="button" id="freeListingChatFloat" class="free-listing-chat-float">
    <i class="las la-comments"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var addNewBtn = document.getElementById('sbAddNewBusinessBtn');
    var selectCol = document.getElementById('sbSelectBusinessCol');
    var formCol = document.getElementById('sbNewBusinessFormCol');
    var leftCol = document.getElementById('sbFreeListingLeftCol');

    if (addNewBtn) {
        addNewBtn.addEventListener('click', function () {
            selectCol.classList.add('d-none');
            formCol.classList.remove('d-none');
            if (leftCol) leftCol.classList.remove('d-none');
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('.free-listing-page')) return;

    document.querySelectorAll(
        'a[href*="wa.me"], a[href*="whatsapp"], .whatsapp-btn, .whatsapp-float, .floating-whatsapp, .float-whatsapp'
    ).forEach(function(el){
        el.remove();
    });

    const chatBtn = document.getElementById('freeListingChatFloat');
    if (chatBtn) {
        chatBtn.addEventListener('click', function(e){
            e.preventDefault();
            document.getElementById('openAdminChat')?.click();
        });
    }
});
</script> 


<script>
document.addEventListener('DOMContentLoaded', function () {
    const listingForm = document.getElementById('multiStepForm');

    if (!listingForm) {
        return;
    }


    /*
     * Pehle ye submit ko rok kar AI ka jawab aane tak intezaar karta tha —
     * aur jab pehla jawab validation me fail hota to doosri baar bhi. Itni
     * der user spinner dekhta rehta tha aur listing abhi save bhi nahi hui
     * hoti thi. Ab submit seedha jaata hai; SEO aur FAQ save ke baad
     * GenerateListingAiContent job likhti hai.
     */
    listingForm.addEventListener('submit', function () {
        const submitButton = listingForm.querySelector(
            'button[type="submit"]'
        );

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm mr-1"></span> Saving...';
        }
    });
});
</script>


@endsection
