@extends($themeView.'layouts.app')
@section('content')
@section('title','Complete Your Profile')

@php
    // Which sidebar step to open first: land on the first one that isn't
    // done yet, so a returning user picks up where they left off instead of
    // always re-seeing Personal Details.
    $initialStep = !$progress['hasPersonal'] ? 1 : (!$progress['hasAddress'] ? 2 : (!$progress['hasFavorite'] ? 3 : 1));
@endphp

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-wizard-shell">

            {{-- Sidebar --}}
            <aside class="sb-wizard-side">
                <h5 class="sb-wizard-side-title">Fill Profile In Few Steps</h5>

                <div class="sb-wizard-progress-label">Overall Progress {{ $progress['percent'] }}%</div>
                <div class="sb-wizard-progress-track">
                    <div class="sb-wizard-progress-fill" style="width:{{ $progress['percent'] }}%"></div>
                </div>

                <ol class="sb-wizard-steps">
                    <li class="sb-wizard-step {{ $progress['hasPersonal'] ? 'is-done' : '' }}" data-step-trigger="1">
                        <span class="sb-wizard-step-dot">{!! $progress['hasPersonal'] ? '<i class="fa-solid fa-check"></i>' : '1' !!}</span>
                        <span class="sb-wizard-step-label">Personal Details</span>
                    </li>
                    <li class="sb-wizard-step {{ $progress['hasAddress'] ? 'is-done' : '' }}" data-step-trigger="2">
                        <span class="sb-wizard-step-dot">{!! $progress['hasAddress'] ? '<i class="fa-solid fa-check"></i>' : '2' !!}</span>
                        <span class="sb-wizard-step-label">Addresses</span>
                    </li>
                    <li class="sb-wizard-step {{ $progress['hasFavorite'] ? 'is-done' : '' }}" data-step-trigger="3">
                        <span class="sb-wizard-step-dot">{!! $progress['hasFavorite'] ? '<i class="fa-solid fa-check"></i>' : '3' !!}</span>
                        <span class="sb-wizard-step-label">Favorites</span>
                    </li>
                    <li class="sb-wizard-step">
                        <span class="sb-wizard-step-dot">4</span>
                        <span class="sb-wizard-step-label">Completed</span>
                    </li>
                </ol>
            </aside>

            {{-- Main panel --}}
            <div class="sb-wizard-main">

                {{-- Step 1: Personal Details --}}
                <div class="sb-wizard-panel" data-step-panel="1">
                    <h4 class="sb-wizard-heading">Please provide your personal details</h4>
                    <p class="sb-wizard-required-note">* Denotes mandatory fields</p>

                    <form action="{{ route('user.profile') }}" method="post" enctype="multipart/form-data" class="sb-wizard-form">
                        @csrf
                        <div class="sb-wizard-form-grid">
                            <div class="sb-wizard-form-fields">
                                <div class="sb-field-row">
                                    <label>*First Name :</label>
                                    <div class="sb-field-combo">
                                        <select name="title" class="sb-select sb-select-title">
                                            <option value="Mr" {{ $user->title === 'Mr' ? 'selected' : '' }}>Mr</option>
                                            <option value="Ms" {{ $user->title === 'Ms' ? 'selected' : '' }}>Ms</option>
                                            <option value="Mrs" {{ $user->title === 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                        </select>
                                        <input type="text" name="first_name" class="sb-input" value="{{ $user->first_name }}" placeholder="Enter First Name" required>
                                    </div>
                                </div>

                                <div class="sb-field-row">
                                    <label>Middle Name :</label>
                                    <input type="text" name="middle_name" class="sb-input" value="{{ $user->middle_name }}" placeholder="Enter Middle Name">
                                </div>

                                <div class="sb-field-row">
                                    <label>Last Name :</label>
                                    <input type="text" name="last_name" class="sb-input" value="{{ $user->last_name }}" placeholder="Enter Last Name" required>
                                </div>

                                <div class="sb-field-row">
                                    <label>*DOB :</label>
                                    <input type="date" name="dob" class="sb-input" value="{{ optional($user->dob)->format('Y-m-d') }}" required>
                                </div>

                                <div class="sb-field-row">
                                    <label>*Marital Status :</label>
                                    <select name="marital_status" class="sb-select sb-select-full" required>
                                        <option value="">Select</option>
                                        @foreach(['Single','Married','Divorced','Widowed'] as $status)
                                            <option value="{{ $status }}" {{ $user->marital_status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="sb-field-row">
                                    <label>City :</label>
                                    <input type="text" name="city" class="sb-input" value="{{ $user->city }}" placeholder="Enter City">
                                </div>

                                <div class="sb-field-row">
                                    <label>Area :</label>
                                    <input type="text" name="area" class="sb-input" value="{{ $user->area }}" placeholder="Enter Area">
                                </div>

                                <div class="sb-field-row">
                                    <label>Pincode :</label>
                                    <input type="text" name="pincode" class="sb-input" value="{{ $user->pincode }}" placeholder="Enter Pincode">
                                </div>

                                <div class="sb-field-row">
                                    <label>Occupation :</label>
                                    <select name="occupation" class="sb-select sb-select-full">
                                        <option value="">Select</option>
                                        @foreach(['Employed','Self-Employed','Student','Homemaker','Retired'] as $occ)
                                            <option value="{{ $occ }}" {{ $user->occupation === $occ ? 'selected' : '' }}>{{ $occ }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="sb-wizard-form-side">
                                <div class="sb-avatar-row">
                                    <img src="{{ loadImage('assets/images/user/'.$user->image) }}" class="sb-avatar-preview" id="sbAvatarPreview" alt="{{ $user->fullname() }}">
                                    <div class="sb-file-field">
                                        <input type="file" class="sb-file-input" name="image" id="sbProfileImageInput" accept="image/*">
                                        <label class="sb-file-label" for="sbProfileImageInput">Browse Image</label>
                                    </div>
                                </div>

                                <div class="sb-field-row">
                                    <label>Email ID :</label>
                                    <div class="sb-field-combo">
                                        <input type="email" class="sb-input" value="{{ $user->email }}" readonly>
                                        <i class="fa-solid fa-circle-check sb-verified-icon"></i>
                                    </div>
                                </div>

                                <div class="sb-field-row">
                                    <label>Mobile Number 1 :</label>
                                    <div class="sb-field-combo">
                                        <span class="sb-code-chip">+91</span>
                                        <input type="text" class="sb-input" value="{{ $user->mobile }}" readonly>
                                        <i class="fa-solid fa-lock sb-locked-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Preserved so profileUpdate() doesn't overwrite the free-text
                             address field this page no longer collects directly. --}}
                        <input type="hidden" name="address" value="{{ $user->address }}">
                        <input type="hidden" name="website" value="{{ $user->website }}">

                        <button type="submit" class="sb-wizard-primary-btn">Save &amp; Continue</button>
                    </form>
                </div>

                {{-- Step 2: Addresses --}}
                <div class="sb-wizard-panel" data-step-panel="2" hidden>
                    <h4 class="sb-wizard-heading">Please provide home and office address</h4>

                    @if($user->addresses->isNotEmpty())
                        <div class="sb-address-list">
                            @foreach($user->addresses as $savedAddress)
                                <div class="sb-address-card">
                                    <div>
                                        <strong>{{ $savedAddress->name ?: $user->fullname() }}</strong>
                                        @if($savedAddress->tag)
                                            <span class="sb-address-tag">{{ $savedAddress->tag }}</span>
                                        @endif
                                        <p>{{ $savedAddress->address }}{{ $savedAddress->city ? ', '.$savedAddress->city : '' }}{{ $savedAddress->pincode ? ' - '.$savedAddress->pincode : '' }}</p>
                                    </div>
                                    <form action="{{ route('user.profile.address.destroy', $savedAddress) }}" method="post" onsubmit="return confirm('Remove this address?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="sb-address-remove" title="Remove"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('user.profile.address.store') }}" method="post" class="sb-wizard-form sb-address-form">
                        @csrf
                        <div class="sb-address-grid">
                            <div class="sb-field-row">
                                <label>Name :</label>
                                <input type="text" name="name" class="sb-input" value="{{ $user->fullname() }}">
                            </div>
                            <div class="sb-field-row">
                                <label>Contact Number:</label>
                                <div class="sb-field-combo">
                                    <span class="sb-code-chip">+91</span>
                                    <input type="text" name="contact_number" class="sb-input" value="{{ $user->mobile }}">
                                </div>
                            </div>
                            <div class="sb-field-row">
                                <label>Email ID :</label>
                                <input type="email" name="email" class="sb-input" value="{{ $user->email }}">
                            </div>

                            <div class="sb-field-row sb-field-row-tall">
                                <label>Address:</label>
                                <textarea name="address" class="sb-input sb-textarea"></textarea>
                            </div>
                            <div class="sb-field-row">
                                <label>Pincode :</label>
                                <input type="text" name="pincode" class="sb-input" placeholder="Enter Pincode">
                            </div>
                            <div class="sb-field-row">
                                <label>City:</label>
                                <input type="text" name="city" class="sb-input" placeholder="Enter City">
                            </div>

                            <div class="sb-field-row">
                                <input type="text" name="landmark" class="sb-input" placeholder="Enter Landmark">
                            </div>
                            <div class="sb-field-row">
                                <label>Landline Number:</label>
                                <div class="sb-field-combo">
                                    <input type="text" name="landline_std" class="sb-input sb-input-std" placeholder="STD">
                                    <input type="text" name="landline_number" class="sb-input">
                                </div>
                            </div>
                            <div class="sb-field-row">
                                <label>Tag:</label>
                                <select name="tag" class="sb-select sb-select-full">
                                    <option value="">Select</option>
                                    <option value="Home">Home</option>
                                    <option value="Office">Office</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="sb-wizard-secondary-btn">Add Address</button>
                    </form>

                    <button type="button" class="sb-wizard-primary-btn sb-wizard-primary-btn--standalone" data-step-next="3">Save &amp; Continue</button>
                </div>

                {{-- Step 3: Favorites --}}
                <div class="sb-wizard-panel" data-step-panel="3" hidden>
                    <h4 class="sb-wizard-heading">Please select a category to enter details</h4>

                    <div class="sb-favorite-grid">
                        @foreach($favoriteCategories as $key => $cat)
                            <button type="button"
                                    class="sb-favorite-card {{ in_array($key, $selectedFavorites) ? 'is-selected' : '' }}"
                                    data-favorite-key="{{ $key }}"
                                    data-toggle-url="{{ route('user.profile.favorite.toggle') }}">
                                <i class="fa-solid {{ $cat['icon'] }}"></i>
                                <span>{{ $cat['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('css')
<style>
    .sb-wizard-shell{
        display:grid;
        grid-template-columns:280px 1fr;
        gap:24px;
        align-items:start;
    }
    @media (max-width:900px){
        .sb-wizard-shell{ grid-template-columns:1fr; }
    }

    .sb-wizard-side{
        background:#fff;
        border:1px solid #e6e8eb;
        border-radius:10px;
        padding:20px;
    }
    .sb-wizard-side-title{
        font-size:14px;
        letter-spacing:.03em;
        color:#333;
        font-weight:700;
        margin-bottom:14px;
    }
    .sb-wizard-progress-label{ font-size:13px; color:#555; margin-bottom:6px; }
    .sb-wizard-progress-track{
        height:6px;
        border-radius:4px;
        background:#eef0f2;
        overflow:hidden;
        margin-bottom:22px;
    }
    .sb-wizard-progress-fill{
        height:100%;
        background:#1e90ff;
        border-radius:4px;
    }

    .sb-wizard-steps{
        list-style:none;
        margin:0;
        padding:0;
        position:relative;
    }
    .sb-wizard-step{
        display:flex;
        align-items:center;
        gap:12px;
        padding-bottom:26px;
        position:relative;
        cursor:pointer;
    }
    .sb-wizard-step:not(:last-child)::before{
        content:"";
        position:absolute;
        left:13px;
        top:28px;
        bottom:0;
        width:2px;
        background:#e2e5e9;
    }
    .sb-wizard-step-dot{
        width:28px;
        height:28px;
        border-radius:50%;
        border:2px solid #c9ced4;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        color:#8a8f96;
        background:#fff;
        flex-shrink:0;
        z-index:1;
    }
    .sb-wizard-step.is-done .sb-wizard-step-dot{
        border-color:#22a45d;
        color:#22a45d;
    }
    .sb-wizard-step.is-active .sb-wizard-step-dot{
        border-color:#1e90ff;
        color:#1e90ff;
    }
    .sb-wizard-step-label{ font-size:14px; color:#444; font-weight:600; }
    .sb-wizard-step.is-active .sb-wizard-step-label{ color:#1e90ff; }

    .sb-wizard-main{
        background:#fff;
        border:1px solid #e6e8eb;
        border-radius:10px;
        padding:28px;
    }
    .sb-wizard-heading{ font-size:20px; font-weight:600; color:#222; margin-bottom:6px; }
    .sb-wizard-required-note{ font-size:12px; color:#888; margin-bottom:20px; }

    .sb-wizard-form-grid{
        display:grid;
        grid-template-columns:1fr 280px;
        gap:32px;
    }
    @media (max-width:700px){
        .sb-wizard-form-grid{ grid-template-columns:1fr; }
    }

    .sb-field-row{ margin-bottom:16px; }
    .sb-field-row label{
        display:block;
        font-size:13px;
        color:#555;
        margin-bottom:5px;
    }
    .sb-input, .sb-select{
        width:100%;
        border:1px solid #d5d9dd;
        border-radius:6px;
        padding:9px 12px;
        font-size:14px;
        color:#222;
        background:#fff;
    }
    .sb-textarea{ min-height:64px; resize:vertical; }
    .sb-field-combo{ display:flex; gap:8px; align-items:center; }
    .sb-field-combo .sb-input{ flex:1; }
    .sb-select-title{ width:78px; flex-shrink:0; }
    .sb-input-std{ width:70px; flex-shrink:0; }
    .sb-code-chip{
        border:1px solid #d5d9dd;
        border-radius:6px;
        padding:9px 10px;
        font-size:14px;
        color:#666;
        background:#f7f8f9;
        flex-shrink:0;
    }
    .sb-verified-icon{ color:#22a45d; }
    .sb-locked-icon{ color:#b0b5ba; }

    .sb-avatar-row{
        display:flex;
        align-items:center;
        gap:14px;
        margin-bottom:22px;
    }
    .sb-avatar-preview{
        width:64px;
        height:64px;
        border-radius:8px;
        object-fit:cover;
        border:1px solid #e6e8eb;
    }
    .sb-file-field{ position:relative; }
    .sb-file-input{
        position:absolute;
        inset:0;
        opacity:0;
        cursor:pointer;
    }
    .sb-file-label{
        display:inline-block;
        border:1px solid #d5d9dd;
        border-radius:6px;
        padding:9px 16px;
        font-size:13px;
        color:#444;
        background:#f7f8f9;
    }

    .sb-wizard-primary-btn{
        background:#1e90ff;
        color:#fff;
        border:0;
        border-radius:6px;
        padding:11px 26px;
        font-size:14px;
        font-weight:600;
        cursor:pointer;
    }
    .sb-wizard-primary-btn--standalone{ margin-top:24px; }
    .sb-wizard-secondary-btn{
        background:#fff;
        color:#1e90ff;
        border:1px solid #1e90ff;
        border-radius:6px;
        padding:9px 22px;
        font-size:13px;
        font-weight:600;
        cursor:pointer;
    }

    .sb-address-list{ margin-bottom:22px; }
    .sb-address-card{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        border:1px solid #e6e8eb;
        border-radius:8px;
        padding:14px 16px;
        margin-bottom:10px;
    }
    .sb-address-card strong{ font-size:14px; color:#222; }
    .sb-address-card p{ margin:4px 0 0; font-size:13px; color:#666; }
    .sb-address-tag{
        margin-left:8px;
        font-size:11px;
        color:#1e90ff;
        border:1px solid #1e90ff;
        border-radius:4px;
        padding:1px 7px;
    }
    .sb-address-remove{
        border:0;
        background:transparent;
        color:#d9364f;
        font-size:14px;
        cursor:pointer;
    }
    .sb-address-grid{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:16px 20px;
    }
    @media (max-width:700px){
        .sb-address-grid{ grid-template-columns:1fr; }
    }
    .sb-field-row-tall{ grid-row:span 2; }

    .sb-favorite-grid{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:16px;
    }
    @media (max-width:700px){
        .sb-favorite-grid{ grid-template-columns:repeat(2,1fr); }
    }
    .sb-favorite-card{
        display:flex;
        align-items:center;
        gap:12px;
        border:1px solid #e0e3e7;
        background:#f8f9fa;
        border-radius:8px;
        padding:16px;
        cursor:pointer;
        font-size:14px;
        color:#333;
        text-align:left;
    }
    .sb-favorite-card i{ font-size:18px; color:#666; width:20px; text-align:center; }
    .sb-favorite-card.is-selected{
        border-color:#1e90ff;
        background:#eaf4fe;
        color:#0e6fd6;
    }
    .sb-favorite-card.is-selected i{ color:#1e90ff; }
</style>
@endpush

@push('js')
<script>
(function(){
    "use strict";

    var steps = document.querySelectorAll('[data-step-trigger]');
    var panels = document.querySelectorAll('[data-step-panel]');

    function showStep(step){
        panels.forEach(function(panel){
            panel.hidden = panel.getAttribute('data-step-panel') !== String(step);
        });
        steps.forEach(function(item){
            item.classList.toggle('is-active', item.getAttribute('data-step-trigger') === String(step));
        });
    }

    steps.forEach(function(item){
        item.addEventListener('click', function(){
            showStep(item.getAttribute('data-step-trigger'));
        });
    });

    document.querySelectorAll('[data-step-next]').forEach(function(btn){
        btn.addEventListener('click', function(){
            showStep(btn.getAttribute('data-step-next'));
        });
    });

    showStep('{{ $initialStep }}');

    // Personal details avatar preview.
    var imageInput = document.getElementById('sbProfileImageInput');
    var preview = document.getElementById('sbAvatarPreview');
    if (imageInput && preview) {
        imageInput.addEventListener('change', function(){
            if (imageInput.files && imageInput.files[0]) {
                preview.src = URL.createObjectURL(imageInput.files[0]);
            }
        });
    }

    // Favorites — toggle without leaving the step.
    document.querySelectorAll('[data-favorite-key]').forEach(function(card){
        card.addEventListener('click', function(){
            var url = card.getAttribute('data-toggle-url');
            var key = card.getAttribute('data-favorite-key');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ category_key: key }),
            })
            .then(function(res){ return res.json(); })
            .then(function(data){
                card.classList.toggle('is-selected', !!data.selected);
            })
            .catch(function(){});
        });
    });
})();
</script>
@endpush
