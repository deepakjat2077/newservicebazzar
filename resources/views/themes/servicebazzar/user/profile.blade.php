@extends($themeView.'layouts.app')
@section('content')
@section('title','Complete Your Profile')

@php
    // Which sidebar step to open first: land on the first one that isn't
    // done yet, so a returning user picks up where they left off instead of
    // always re-seeing Personal Details.
    $initialStep = (int) old('profile_step', $initialStep);
    $initialStep = $initialStep ?: (!$progress['hasPersonal'] ? 1 : (!$progress['hasAddress'] ? 2 : (!$progress['hasFavorite'] ? 3 : ($progress['isComplete'] ? 4 : 1))));
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
                        <span class="sb-wizard-step-dot">{!! $progress['hasPersonal'] ? '<i class="fas fa-check"></i>' : '1' !!}</span>
                        <span class="sb-wizard-step-label">Personal Details</span>
                    </li>
                    <li class="sb-wizard-step {{ $progress['hasAddress'] ? 'is-done' : '' }}" data-step-trigger="2">
                        <span class="sb-wizard-step-dot">{!! $progress['hasAddress'] ? '<i class="fas fa-check"></i>' : '2' !!}</span>
                        <span class="sb-wizard-step-label">Addresses</span>
                    </li>
                    <li class="sb-wizard-step {{ $progress['hasFavorite'] ? 'is-done' : '' }}" data-step-trigger="3">
                        <span class="sb-wizard-step-dot">{!! $progress['hasFavorite'] ? '<i class="fas fa-check"></i>' : '3' !!}</span>
                        <span class="sb-wizard-step-label">Favorites</span>
                    </li>
                    <li class="sb-wizard-step {{ $progress['isComplete'] ? 'is-done' : '' }}" data-step-trigger="4">
                        <span class="sb-wizard-step-dot">{!! $progress['isComplete'] ? '<i class="fas fa-check"></i>' : '4' !!}</span>
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
                                    @error('dob')
                                        <small class="sb-field-error">{{ $message }}</small>
                                    @enderror
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
                                    <label>Pincode :</label>
                                    <input type="text" name="pincode" id="sbPincodeInput" class="sb-input" value="{{ $user->pincode }}" placeholder="Enter Pincode" maxlength="6" inputmode="numeric">
                                </div>

                                <div class="sb-field-row">
                                    <label>City :</label>
                                    <input type="text" name="city" id="sbCityInput" class="sb-input" value="{{ $user->city }}" placeholder="Enter City">
                                </div>

                                <div class="sb-field-row">
                                    <label>Area :</label>
                                    <select name="area" id="sbAreaSelect" class="sb-select sb-select-full">
                                        @if($user->area)
                                            <option value="{{ $user->area }}" selected>{{ $user->area }}</option>
                                        @else
                                            <option value="">Enter Pincode to pick an area</option>
                                        @endif
                                    </select>
                                    <small class="sb-field-hint" id="sbAreaHint"></small>
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
                                        {{-- An account created from a WhatsApp OTP has no email at all,
                                             so this is where its owner adds one. Only an address they
                                             have actually confirmed is locked with the verified tick. --}}
                                        @if($user->hasMailableEmail() && $user->email_verified)
                                            <input type="email" class="sb-input" value="{{ $user->email }}" readonly>
                                            <i class="fas fa-check-circle sb-verified-icon"></i>
                                        @else
                                            <input type="email" name="email" class="sb-input" value="{{ old('email', $user->hasMailableEmail() ? $user->email : '') }}" placeholder="Add your email ID" maxlength="191">
                                        @endif
                                    </div>
                                    @error('email')
                                        <small class="sb-field-error">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="sb-field-row">
                                    <label>Mobile Number 1 :</label>
                                    <div class="sb-field-combo">
                                        <span class="sb-code-chip">+91</span>
                                        <input type="text" class="sb-input" value="{{ $user->mobile }}" readonly>
                                        <i class="fas fa-lock sb-locked-icon"></i>
                                    </div>
                                </div>

                                <div class="sb-field-row">
                                    <label>Mobile Number 2 :</label>
                                    @php
                                        $mobile2IsVerified = $user->mobile_2 && ($user->mobile_2_verified ?? false);
                                    @endphp
                                    <div class="sb-field-combo">
                                        <span class="sb-code-chip">+91</span>
                                        <input type="text" name="mobile_2" id="sbMobile2Input" class="sb-input {{ $mobile2IsVerified ? 'sb-input-locked' : '' }}" value="{{ $user->mobile_2 }}" placeholder="Add another number" maxlength="10" inputmode="numeric" {{ $mobile2IsVerified ? 'readonly' : '' }}>
                                        <button type="button" class="sb-verify-btn {{ $mobile2IsVerified ? 'is-verified' : '' }}" id="sbMobile2VerifyBtn" data-mode="{{ $mobile2IsVerified ? 'edit' : 'verify' }}">
                                            @if($mobile2IsVerified)
                                                <i class="fas fa-pen"></i><span>Edit</span>
                                            @else
                                                Verify
                                            @endif
                                        </button>
                                    </div>
                                    <small class="sb-field-hint {{ $mobile2IsVerified ? 'is-success' : '' }}" id="sbMobile2Status">{{ $mobile2IsVerified ? 'Mobile Number 2 verified.' : '' }}</small>
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
                                        @php
                                            $addressLine = collect([
                                                $savedAddress->address,
                                                $savedAddress->landmark,
                                                $savedAddress->area ?: $user->area,
                                                $savedAddress->city ?: $user->city,
                                            ])->filter()->implode(', ');
                                            $addressPincode = $savedAddress->pincode ?: $user->pincode;
                                        @endphp
                                        <p>
                                            @if($addressLine)
                                                {{ $addressLine }}{{ $addressPincode ? ' - '.$addressPincode : '' }}
                                            @else
                                                Address details not added yet.
                                            @endif
                                        </p>
                                    </div>
                                    <form action="{{ route('user.profile.address.destroy', $savedAddress) }}" method="post" onsubmit="return confirm('Remove this address?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="sb-address-remove" title="Remove"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($user->addresses->isEmpty())
                    <form action="{{ route('user.profile.address.store') }}" method="post" class="sb-wizard-form sb-address-form">
                        @csrf
                        <input type="hidden" name="profile_step" value="2">
                        <div class="sb-address-grid">
                            <div class="sb-field-row">
                                <label>Name :</label>
                                <input type="text" name="name" class="sb-input sb-input-locked" value="{{ $user->fullname() }}" readonly>
                            </div>
                            <div class="sb-field-row">
                                <label>Contact Number:</label>
                                <div class="sb-field-combo">
                                    <span class="sb-code-chip">+91</span>
                                    <input type="text" name="contact_number" class="sb-input sb-input-locked" value="{{ $user->mobile }}" readonly>
                                </div>
                            </div>
                            <div class="sb-field-row">
                                <label>Email ID :</label>
                                <input type="email" name="email" class="sb-input sb-input-locked" value="{{ $user->hasMailableEmail() ? $user->email : '' }}" placeholder="No email added" readonly>
                            </div>

                            <div class="sb-field-row sb-field-row-tall">
                                <label>Address:</label>
                                <textarea name="address" class="sb-input sb-textarea">{{ old('address') }}</textarea>
                                @error('address')
                                    <small class="sb-field-error">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="sb-field-row">
                                <label>Pincode :</label>
                                <input type="text" name="pincode" class="sb-input sb-input-locked" value="{{ $user->pincode }}" placeholder="Add pincode in Personal Details" readonly>
                            </div>
                            <div class="sb-field-row">
                                <label>City:</label>
                                <input type="text" name="city" class="sb-input sb-input-locked" value="{{ $user->city }}" placeholder="Add city in Personal Details" readonly>
                            </div>
                            <div class="sb-field-row">
                                <label>Area:</label>
                                <input type="text" name="area" class="sb-input sb-input-locked" value="{{ $user->area }}" placeholder="Add area in Personal Details" readonly>
                            </div>

                            <div class="sb-field-row">
                                <label>Landmark:</label>
                                <input type="text" name="landmark" class="sb-input" value="{{ old('landmark') }}" placeholder="Enter Landmark">
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
                    @endif

                    <button type="button" class="sb-wizard-primary-btn sb-wizard-primary-btn--standalone" data-step-next="3">Save &amp; Continue</button>
                </div>

                {{-- Step 3: Favorites --}}
                <div class="sb-wizard-panel" data-step-panel="3" hidden>
                    <div id="sbFavoriteGridView">
                        <h4 class="sb-wizard-heading">Please select a category to enter details</h4>
                        @php
                            $selectedFavoriteKeys = collect($favoritedCategoryKeys ?? []);
                        @endphp

                        <div class="sb-favorite-grid">
                            @foreach($favoriteCategories as $key => $cat)
                                <button type="button"
                                        class="sb-favorite-card {{ $selectedFavoriteKeys->contains($key) ? 'is-selected' : '' }}"
                                        data-favorite-key="{{ $key }}"
                                        data-favorite-label="{{ $cat['label'] }}">
                                    <i class="fas {{ $cat['icon'] }}"></i>
                                    <span>{{ $cat['label'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <button type="button" class="sb-wizard-primary-btn sb-wizard-primary-btn--standalone" data-step-next="4">Continue</button>
                    </div>

                    {{-- Filled in by JS with the real businesses under whichever
                         category card was clicked, from this site's own listings. --}}
                    <div id="sbFavoriteListingsView" hidden>
                        <button type="button" class="sb-wizard-back-btn" id="sbFavoriteBackBtn">
                            <i class="fas fa-arrow-left"></i> Back to categories
                        </button>
                        <h4 class="sb-wizard-heading" id="sbFavoriteListingsHeading">Businesses</h4>

                        <div id="sbFavoriteListingsBody">
                            <p class="sb-wizard-loading">Loading businesses…</p>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Completed --}}
                <div class="sb-wizard-panel sb-wizard-panel--complete" data-step-panel="4" hidden>
                    <div class="sb-complete-icon"><i class="fas fa-check-circle"></i></div>
                    <h4 class="sb-wizard-heading">You're all set, {{ $user->first_name }}!</h4>
                    <p class="sb-complete-text">
                        Your profile is filled in — personal details, address and your favorite
                        businesses are all saved. You can come back to any step above any time
                        to update them.
                    </p>
                    <a href="{{ route('user.dashboard') }}" class="sb-wizard-primary-btn">Go to Dashboard</a>
                </div>

            </div>

            {{-- Right Sidebar: Quick Links --}}
            <aside class="sb-wizard-side-right">
                <div class="sb-sidebar-section">
                    <h6 style="margin-top: 0; color: #666; font-size: 12px; text-transform: uppercase; font-weight: 600;">Looking for work?</h6>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><a href="#full-time" style="display: block; padding: 8px 0; color: #333; text-decoration: none; font-size: 14px;">Full-Time Jobs</a></li>
                        <li><a href="#part-time" style="display: block; padding: 8px 0; color: #333; text-decoration: none; font-size: 14px;">Part-Time Jobs</a></li>
                        <li><a href="#freelance" style="display: block; padding: 8px 0; color: #333; text-decoration: none; font-size: 14px;">Freelance Jobs</a></li>
                    </ul>
                    <a href="#more-jobs" style="display: block; padding: 12px 0; color: var(--primary-color, #2563eb); text-decoration: none; font-size: 13px; font-weight: 600;">KNOW MORE →</a>
                </div>

                <div class="sb-sidebar-section" style="border-top: 1px solid #eee; padding-top: 16px; margin-top: 16px;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><a href="#saved" style="display: flex; align-items: center; gap: 10px; padding: 8px 0; color: #333; text-decoration: none; font-size: 14px;"><i class="las la-bookmark" style="font-size: 18px;"></i>Saved</a></li>
                        <li><a href="#favorites" style="display: flex; align-items: center; gap: 10px; padding: 8px 0; color: #333; text-decoration: none; font-size: 14px;"><i class="las la-heart" style="font-size: 18px;"></i>Favorites</a></li>
                        <li><a href="#transactions" style="display: flex; align-items: center; gap: 10px; padding: 8px 0; color: #333; text-decoration: none; font-size: 14px;"><i class="las la-exchange-alt" style="font-size: 18px;"></i>My Transactions</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<div class="sb-mobile2-otp-modal" id="sbMobile2OtpModal" hidden>
    <div class="sb-mobile2-otp-box">
        <button type="button" class="sb-mobile2-otp-close" id="sbMobile2OtpClose">&times;</button>
        <h4>Verify Mobile Number 2</h4>
        <p>Enter the OTP sent on WhatsApp to <strong id="sbMobile2OtpNumber"></strong>.</p>
        <input type="text" class="sb-input" id="sbMobile2OtpInput" maxlength="6" inputmode="numeric" placeholder="Enter 6 digit OTP">
        <small class="sb-field-hint" id="sbMobile2OtpStatus"></small>
        <button type="button" class="sb-wizard-primary-btn" id="sbMobile2OtpSubmit">Verify OTP</button>
    </div>
</div>
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
    .sb-input-locked,
    .sb-input[readonly]{
        background:#f7f8f9;
        color:#555;
        cursor:not-allowed;
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
    .sb-field-error{ display:block; margin-top:4px; color:#d63939; font-size:12px; }
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

    .sb-field-hint{ display:block; font-size:11px; color:#999; margin-top:4px; }
    .sb-field-hint.is-error{ color:#d9364f; }
    .sb-field-hint.is-success{ color:#22a45d; }
    .sb-verify-btn{
        border:1px solid #d5d9dd;
        background:#f7f8f9;
        border-radius:6px;
        padding:9px 14px;
        font-size:12px;
        font-weight:600;
        color:#444;
        cursor:pointer;
        flex-shrink:0;
    }
    .sb-verify-btn.is-verified{
        border-color:#22a45d;
        color:#22a45d;
        background:#eefaf3;
    }
    .sb-verify-btn i{
        margin-right:5px;
        font-size:11px;
    }
    .sb-verify-btn:disabled{
        opacity:.7;
        cursor:not-allowed;
    }

    .sb-mobile2-otp-modal{
        position:fixed;
        inset:0;
        z-index:999999;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:18px;
        background:rgba(0,0,0,.5);
    }
    .sb-mobile2-otp-modal[hidden]{ display:none; }
    .sb-mobile2-otp-box{
        position:relative;
        width:min(360px, 100%);
        padding:24px;
        border-radius:10px;
        background:#fff;
        box-shadow:0 18px 45px rgba(0,0,0,.22);
    }
    .sb-mobile2-otp-box h4{
        margin:0 28px 8px 0;
        font-size:18px;
        font-weight:700;
        color:#222;
    }
    .sb-mobile2-otp-box p{
        margin:0 0 16px;
        font-size:13px;
        color:#666;
        line-height:1.5;
    }
    .sb-mobile2-otp-box .sb-wizard-primary-btn{
        width:100%;
        margin-top:14px;
    }
    .sb-mobile2-otp-close{
        position:absolute;
        top:12px;
        right:12px;
        width:28px;
        height:28px;
        border:0;
        background:transparent;
        color:#111;
        font-size:24px;
        line-height:1;
        cursor:pointer;
    }

    .sb-wizard-back-btn{
        border:0;
        background:transparent;
        color:#1e90ff;
        font-size:13px;
        font-weight:600;
        cursor:pointer;
        margin-bottom:14px;
        padding:0;
    }
    .sb-wizard-loading{ font-size:13px; color:#888; }
    .sb-listing-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        border:1px solid #eceef0;
        border-radius:8px;
        padding:12px 14px;
        margin-bottom:10px;
    }
    .sb-listing-row strong{ font-size:14px; color:#222; display:block; }
    .sb-listing-row small{ font-size:12px; color:#888; }
    .sb-listing-fav-btn{
        border:1px solid #1e90ff;
        background:#fff;
        color:#1e90ff;
        border-radius:6px;
        padding:7px 14px;
        font-size:12px;
        font-weight:600;
        cursor:pointer;
        flex-shrink:0;
        white-space:nowrap;
    }
    .sb-listing-fav-btn.is-selected{
        background:#1e90ff;
        color:#fff;
    }
    .sb-wizard-empty{ font-size:13px; color:#888; }

    .sb-wizard-panel--complete{
        text-align:center;
        padding:30px 10px;
    }
    .sb-complete-icon{ font-size:44px; color:#22a45d; margin-bottom:14px; }
    .sb-complete-text{
        font-size:14px;
        color:#666;
        max-width:440px;
        margin:0 auto 22px;
        line-height:1.6;
    }
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

    var mobile2Btn = document.getElementById('sbMobile2VerifyBtn');
    var mobile2Input = document.getElementById('sbMobile2Input');
    var mobile2Status = document.getElementById('sbMobile2Status');
    var mobile2OtpModal = document.getElementById('sbMobile2OtpModal');
    var mobile2OtpClose = document.getElementById('sbMobile2OtpClose');
    var mobile2OtpNumber = document.getElementById('sbMobile2OtpNumber');
    var mobile2OtpInput = document.getElementById('sbMobile2OtpInput');
    var mobile2OtpSubmit = document.getElementById('sbMobile2OtpSubmit');
    var mobile2OtpStatus = document.getElementById('sbMobile2OtpStatus');
    var verifiedMobile2 = mobile2Btn && mobile2Btn.dataset.mode === 'edit'
        ? mobile2Input.value.replace(/\D/g, '')
        : '';

    function setMobile2Message(el, text, type){
        if (!el) return;
        el.textContent = text || '';
        el.classList.remove('is-error', 'is-success');
        if (type) el.classList.add(type === 'success' ? 'is-success' : 'is-error');
    }

    function setMobile2Button(mode){
        if (!mobile2Btn) return;
        mobile2Btn.dataset.mode = mode;
        mobile2Btn.classList.toggle('is-verified', mode === 'edit');

        if (mode === 'edit') {
            mobile2Btn.innerHTML = '<i class="fas fa-pen"></i><span>Edit</span>';
        } else if (mode === 'save') {
            mobile2Btn.innerHTML = '<i class="fas fa-save"></i><span>Save</span>';
        } else if (mode === 'sending') {
            mobile2Btn.textContent = 'Sending...';
        } else {
            mobile2Btn.textContent = 'Verify';
        }
    }

    function lockMobile2Input(shouldLock){
        if (!mobile2Input) return;
        mobile2Input.readOnly = shouldLock;
        mobile2Input.classList.toggle('sb-input-locked', shouldLock);
    }

    function setMobile2Verified(number, message){
        verifiedMobile2 = number;
        mobile2Input.value = number;
        lockMobile2Input(true);
        setMobile2Button('edit');
        setMobile2Message(mobile2Status, message || 'Mobile Number 2 verified successfully.', 'success');
    }

    function startMobile2Edit(){
        lockMobile2Input(false);
        setMobile2Button('save');
        setMobile2Message(mobile2Status, '', null);
        mobile2Input.focus();
        mobile2Input.setSelectionRange(mobile2Input.value.length, mobile2Input.value.length);
    }

    function resetMobile2Edit(){
        mobile2Input.value = verifiedMobile2;
        lockMobile2Input(true);
        setMobile2Button('edit');
        setMobile2Message(mobile2Status, 'Mobile Number 2 verified.', 'success');
    }

    function sendMobile2Otp(number, nextMode){
        mobile2Btn.disabled = true;
        setMobile2Button('sending');
        setMobile2Message(mobile2Status, '', null);

        fetch("{{ route('user.profile.mobile2.send') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ mobile_2: number })
        })
        .then(function(response){
            return response.json().then(function(data){
                if (!response.ok || data.status === false) throw data;
                return data;
            });
        })
        .then(function(data){
            setMobile2Button(nextMode || 'verify');
            setMobile2Message(mobile2Status, data.message || 'OTP sent on WhatsApp.', 'success');
            openMobile2OtpModal(number);
        })
        .catch(function(error){
            setMobile2Button(nextMode || 'verify');
            setMobile2Message(mobile2Status, error.message || 'Could not send OTP. Please try again.', 'error');
        })
        .finally(function(){
            mobile2Btn.disabled = false;
        });
    }

    function openMobile2OtpModal(number){
        mobile2OtpNumber.textContent = '+91 ' + number;
        mobile2OtpInput.value = '';
        setMobile2Message(mobile2OtpStatus, '', null);
        mobile2OtpModal.hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(function(){ mobile2OtpInput.focus(); }, 50);
    }

    function closeMobile2OtpModal(){
        mobile2OtpModal.hidden = true;
        document.body.style.overflow = '';
    }

    function csrfToken(){
        return document.querySelector('input[name="_token"]').value;
    }

    if (mobile2Btn && mobile2Input) {
        if (verifiedMobile2) {
            lockMobile2Input(true);
            setMobile2Button('edit');
        }

        mobile2Input.addEventListener('input', function(){
            mobile2Input.value = mobile2Input.value.replace(/\D/g, '').slice(0, 10);
            if (!verifiedMobile2) {
                setMobile2Button('verify');
                setMobile2Message(mobile2Status, '', null);
            } else if (mobile2Input.value !== verifiedMobile2) {
                setMobile2Button('save');
                setMobile2Message(mobile2Status, 'Save to verify this new number.', null);
            } else if (verifiedMobile2) {
                setMobile2Button('save');
                setMobile2Message(mobile2Status, 'Same verified number. Save will not send OTP.', 'success');
            }
        });

        mobile2Btn.addEventListener('click', function(){
            var mode = mobile2Btn.dataset.mode || 'verify';
            var number = mobile2Input.value.replace(/\D/g, '');

            if (mode === 'edit') {
                startMobile2Edit();
                return;
            }

            if (!/^\d{10}$/.test(number)) {
                setMobile2Message(mobile2Status, 'Please enter a valid 10 digit mobile number.', 'error');
                return;
            }

            if (number === verifiedMobile2) {
                resetMobile2Edit();
                return;
            }

            sendMobile2Otp(number, verifiedMobile2 ? 'save' : 'verify');
        });
    }

    if (mobile2OtpClose) {
        mobile2OtpClose.addEventListener('click', closeMobile2OtpModal);
    }

    if (mobile2OtpModal) {
        mobile2OtpModal.addEventListener('click', function(e){
            if (e.target === mobile2OtpModal) {
                closeMobile2OtpModal();
            }
        });
    }

    if (mobile2OtpInput) {
        mobile2OtpInput.addEventListener('input', function(){
            mobile2OtpInput.value = mobile2OtpInput.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    if (mobile2OtpSubmit) {
        mobile2OtpSubmit.addEventListener('click', function(){
            var number = mobile2Input.value.replace(/\D/g, '');
            var otp = mobile2OtpInput.value.replace(/\D/g, '');

            if (!/^\d{6}$/.test(otp)) {
                setMobile2Message(mobile2OtpStatus, 'Please enter the 6 digit OTP.', 'error');
                return;
            }

            mobile2OtpSubmit.disabled = true;
            mobile2OtpSubmit.textContent = 'Verifying...';
            setMobile2Message(mobile2OtpStatus, '', null);

            fetch("{{ route('user.profile.mobile2.verify') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ mobile_2: number, otp: otp })
            })
            .then(function(response){
                return response.json().then(function(data){
                    if (!response.ok || data.status === false) throw data;
                    return data;
                });
            })
            .then(function(data){
                setMobile2Verified(data.mobile || number);
                closeMobile2OtpModal();
            })
            .catch(function(error){
                setMobile2Message(mobile2OtpStatus, error.message || 'Invalid OTP. Please try again.', 'error');
            })
            .finally(function(){
                mobile2OtpSubmit.disabled = false;
                mobile2OtpSubmit.textContent = 'Verify OTP';
            });
        });
    }

    // Pincode -> City/Area — looked up from India Post's public pincode API
    // so typing a pincode fills the area dropdown instead of the user
    // having to type a locality name freehand.
    var pincodeInput = document.getElementById('sbPincodeInput');
    var citySelectTarget = document.getElementById('sbCityInput');
    var areaSelect = document.getElementById('sbAreaSelect');
    var areaHint = document.getElementById('sbAreaHint');

    function lookupPincode(pin){
        areaHint.textContent = 'Looking up areas for ' + pin + '…';

        fetch('https://api.postalpincode.in/pincode/' + pin)
            .then(function(res){ return res.json(); })
            .then(function(data){
                var result = data && data[0];

                if (!result || result.Status !== 'Success' || !result.PostOffice || !result.PostOffice.length) {
                    areaHint.textContent = 'No areas found for this pincode — type it in manually.';
                    return;
                }

                areaSelect.innerHTML = '';
                result.PostOffice.forEach(function(po){
                    var opt = document.createElement('option');
                    opt.value = po.Name;
                    opt.textContent = po.Name;
                    areaSelect.appendChild(opt);
                });

                if (citySelectTarget && !citySelectTarget.value.trim()) {
                    citySelectTarget.value = result.PostOffice[0].District;
                }

                areaHint.textContent = result.PostOffice.length + ' area(s) found — pick one.';
            })
            .catch(function(){
                areaHint.textContent = 'Could not look up this pincode right now.';
            });
    }

    if (pincodeInput) {
        pincodeInput.addEventListener('blur', function(){
            var pin = pincodeInput.value.trim();
            if (/^\d{6}$/.test(pin)) {
                lookupPincode(pin);
            }
        });
    }

    // Favorites — clicking a category card fetches this site's own real
    // listings under a matching category, instead of a fake sample list.
    var gridView = document.getElementById('sbFavoriteGridView');
    var listingsView = document.getElementById('sbFavoriteListingsView');
    var listingsHeading = document.getElementById('sbFavoriteListingsHeading');
    var listingsBody = document.getElementById('sbFavoriteListingsBody');
    var backBtn = document.getElementById('sbFavoriteBackBtn');
    var listingsUrl = '{{ route('user.profile.favorite.listings') }}';
    var listingToggleUrl = '{{ route('user.profile.favorite.listing.toggle') }}';

    function renderListingRow(listing, categoryKey){
        var row = document.createElement('div');
        row.className = 'sb-listing-row';

        var info = document.createElement('div');
        info.innerHTML = '<strong></strong><small></small>';
        info.querySelector('strong').textContent = listing.name;
        info.querySelector('small').textContent = listing.area || '';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sb-listing-fav-btn' + (listing.is_favorited ? ' is-selected' : '');
        btn.textContent = listing.is_favorited ? 'Added to Favorite' : 'Add to Favorite';

        btn.addEventListener('click', function(){
            fetch(listingToggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ listing_id: listing.id, category_key: categoryKey }),
            })
            .then(function(res){ return res.json(); })
            .then(function(data){
                btn.classList.toggle('is-selected', !!data.selected);
                btn.textContent = data.selected ? 'Added to Favorite' : 'Add to Favorite';
                var categoryCard = document.querySelector('[data-favorite-key="' + categoryKey + '"]');
                if (categoryCard) {
                    categoryCard.classList.toggle('is-selected', !!data.category_selected);
                }
            })
            .catch(function(){});
        });

        row.appendChild(info);
        row.appendChild(btn);
        return row;
    }

    document.querySelectorAll('[data-favorite-key]').forEach(function(card){
        card.addEventListener('click', function(){
            var key = card.getAttribute('data-favorite-key');
            var label = card.getAttribute('data-favorite-label');

            gridView.hidden = true;
            listingsView.hidden = false;
            listingsHeading.textContent = label;
            listingsBody.innerHTML = '<p class="sb-wizard-loading">Loading businesses…</p>';

            fetch(listingsUrl, {
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
                listingsBody.innerHTML = '';

                if (!data.listings || !data.listings.length) {
                    var empty = document.createElement('p');
                    empty.className = 'sb-wizard-empty';
                    empty.textContent = data.matched
                        ? 'No ' + label + ' businesses are listed yet — check back soon.'
                        : 'This category isn\'t on Service Bazzar yet.';
                    listingsBody.appendChild(empty);
                    return;
                }

                data.listings.forEach(function(listing){
                    listingsBody.appendChild(renderListingRow(listing, key));
                });
            })
            .catch(function(){
                listingsBody.innerHTML = '<p class="sb-wizard-empty">Something went wrong loading businesses.</p>';
            });
        });
    });

    if (backBtn) {
        backBtn.addEventListener('click', function(){
            listingsView.hidden = true;
            gridView.hidden = false;
        });
    }
})();
</script>
@endpush
