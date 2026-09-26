@extends('admin.layouts.master')
@section('title', isset($edit) ? 'Edit Sales User' : 'Add Sales User')
@section('content')

@php $u = $user ?? null; @endphp

<div class="row justify-content-center">
    <div class="col-12">
        <form method="POST" autocomplete="off" enctype="multipart/form-data"
            action="{{ isset($edit) ? route('admin.sales_users.update', $u->id) : route('admin.sales_users.store') }}">
            @csrf
            @if(isset($edit)) @method('PUT') @endif

            <div class="row">

                {{-- ============ left: photo + account ============ --}}
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">

                            <h6 class="mb-3 text-primary">Photo</h6>
                            <div class="text-center mb-3">
                                <img id="avatarPreview"
                                    src="{{ $u && $u->avatarUrl() ? $u->avatarUrl() : asset('assets/images/default.jpg') }}"
                                    style="width:140px;height:140px;object-fit:cover;border-radius:50%;border:1px solid #e6e9f2;">
                                <div class="mt-2">
                                    <label class="btn btn-sm btn-outline-primary mb-0">
                                        <i class="fas fa-camera"></i> Choose photo
                                        <input type="file" name="avatar" accept="image/*" hidden
                                            onchange="preview(this, 'avatarPreview')">
                                    </label>
                                </div>
                                @error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <hr>
                            <h6 class="mb-3 text-primary">Login</h6>

                            <div class="form-group">
                                <label>Employee Code <span class="text-danger">*</span></label>
                                <input type="text" name="empcode" class="form-control @error('empcode') is-invalid @enderror"
                                    value="{{ old('empcode', $u->empcode ?? ($nextCode ?? '')) }}"
                                    placeholder="e.g. SB1001" required style="text-transform:uppercase;" autocomplete="off">
                                @error('empcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @unless(isset($edit))
                                    <small class="form-text text-muted">Next free code, filled in for you.</small>
                                @endunless
                            </div>

                            <div class="form-group">
                                <label>
                                    Password {{ isset($edit) ? '(leave blank to keep)' : '' }}
                                    @unless(isset($edit))<span class="text-danger">*</span>@endunless
                                </label>
                                <div class="input-group">
                                    {{-- new-password stops Chrome dropping the admin's
                                         own saved login into a new employee's form. --}}
                                    <input type="password" name="password" id="pw1"
                                        class="form-control @error('password') is-invalid @enderror"
                                        autocomplete="new-password" {{ isset($edit) ? '' : 'required' }}>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePw('pw1', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    Confirm Password
                                    @unless(isset($edit))<span class="text-danger">*</span>@endunless
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="pw2"
                                        class="form-control" autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePw('pw2', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" name="status" class="custom-control-input" id="statusSwitch"
                                    {{ (old('status', $u->status ?? 1)) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="statusSwitch">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ right: the record ============ --}}
                <div class="col-lg-8">

                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">Personal &amp; Contact</h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $u->name ?? '') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Designation</label>
                                    <input type="text" name="designation" class="form-control"
                                        value="{{ old('designation', $u->designation ?? '') }}"
                                        placeholder="e.g. Sales Manager">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Mobile Number</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $u->phone ?? '') }}" maxlength="10"
                                        oninput="digits(this, 10)" placeholder="10 digits">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="form-text text-muted">The login OTP goes to this number on WhatsApp.</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Alternate Mobile</label>
                                    <input type="text" name="alt_phone" class="form-control @error('alt_phone') is-invalid @enderror"
                                        value="{{ old('alt_phone', $u->alt_phone ?? '') }}" maxlength="10"
                                        oninput="digits(this, 10)">
                                    @error('alt_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Date of Joining</label>
                                    <input type="date" name="joined_on" class="form-control"
                                        value="{{ old('joined_on', optional($u->joined_on ?? null)->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $u->email ?? '') }}" autocomplete="off">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">Address</h6>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>House / Street</label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ old('address', $u->address ?? '') }}"
                                        placeholder="House no, building, street">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Area</label>
                                    <input type="text" name="area" class="form-control"
                                        value="{{ old('area', $u->area ?? '') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control"
                                        value="{{ old('city', $u->city ?? 'Jaipur') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>State</label>
                                    <input type="text" name="state" class="form-control"
                                        value="{{ old('state', $u->state ?? 'Rajasthan') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Pincode</label>
                                    <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                                        value="{{ old('pincode', $u->pincode ?? '') }}" maxlength="6" oninput="digits(this, 6)">
                                    @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">ID Proof</h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Aadhaar Number</label>
                                    <input type="text" name="aadhaar_no" class="form-control @error('aadhaar_no') is-invalid @enderror"
                                        value="{{ old('aadhaar_no', $u->aadhaar_no ?? '') }}" maxlength="12"
                                        oninput="digits(this, 12)" placeholder="12 digits">
                                    @error('aadhaar_no')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                    <label class="btn btn-sm btn-outline-secondary mt-2 mb-0">
                                        <i class="fas fa-upload"></i> Aadhaar image
                                        <input type="file" name="aadhaar_image" accept="image/*" hidden
                                            onchange="preview(this, 'aadhaarPreview')">
                                    </label>
                                    @if($u && $u->docUrl('aadhaar_image'))
                                        <a href="{{ $u->docUrl('aadhaar_image') }}" target="_blank" class="btn btn-sm btn-link">View current</a>
                                    @endif
                                    <div><img id="aadhaarPreview" src="{{ $u ? ($u->docUrl('aadhaar_image') ?? '') : '' }}"
                                        class="mt-2 {{ $u && $u->docUrl('aadhaar_image') ? '' : 'd-none' }}"
                                        style="max-width:100%;max-height:130px;border-radius:6px;border:1px solid #e6e9f2;"></div>
                                    @error('aadhaar_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label>PAN Number</label>
                                    <input type="text" name="pan_no" class="form-control @error('pan_no') is-invalid @enderror"
                                        value="{{ old('pan_no', $u->pan_no ?? '') }}" maxlength="10"
                                        style="text-transform:uppercase;" placeholder="AAAAA0000A">
                                    @error('pan_no')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                    <label class="btn btn-sm btn-outline-secondary mt-2 mb-0">
                                        <i class="fas fa-upload"></i> PAN image
                                        <input type="file" name="pan_image" accept="image/*" hidden
                                            onchange="preview(this, 'panPreview')">
                                    </label>
                                    @if($u && $u->docUrl('pan_image'))
                                        <a href="{{ $u->docUrl('pan_image') }}" target="_blank" class="btn btn-sm btn-link">View current</a>
                                    @endif
                                    <div><img id="panPreview" src="{{ $u ? ($u->docUrl('pan_image') ?? '') : '' }}"
                                        class="mt-2 {{ $u && $u->docUrl('pan_image') ? '' : 'd-none' }}"
                                        style="max-width:100%;max-height:130px;border-radius:6px;border:1px solid #e6e9f2;"></div>
                                    @error('pan_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">Bank Details <small class="text-muted">(for salary)</small></h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Account Holder Name</label>
                                    <input type="text" name="account_holder" class="form-control"
                                        value="{{ old('account_holder', $u->account_holder ?? '') }}"
                                        placeholder="As printed in the passbook">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control"
                                        value="{{ old('bank_name', $u->bank_name ?? '') }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Account Number</label>
                                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                                        value="{{ old('bank_account', $u->bank_account ?? '') }}" maxlength="20"
                                        oninput="digits(this, 20)">
                                    @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>IFSC</label>
                                    <input type="text" name="bank_ifsc" class="form-control @error('bank_ifsc') is-invalid @enderror"
                                        value="{{ old('bank_ifsc', $u->bank_ifsc ?? '') }}" maxlength="11"
                                        style="text-transform:uppercase;" placeholder="HDFC0001234">
                                    @error('bank_ifsc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Branch</label>
                                    <input type="text" name="bank_branch" class="form-control"
                                        value="{{ old('bank_branch', $u->bank_branch ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($edit) ? 'Update User' : 'Create User' }}
                        </button>
                        <a href="{{ route('admin.sales_users.index') }}" class="btn btn-light ml-2">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePw(id, btn) {
        var input = document.getElementById(id);
        var showing = input.type === 'text';

        input.type = showing ? 'password' : 'text';
        btn.querySelector('i').className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    function digits(input, max) {
        input.value = input.value.replace(/\D/g, '').slice(0, max);
    }

    // Show the picked file straight away, so a wrong one is obvious.
    function preview(input, targetId) {
        if (!input.files || !input.files[0]) return;

        var img = document.getElementById(targetId);
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('d-none');
    }
</script>

@endsection
