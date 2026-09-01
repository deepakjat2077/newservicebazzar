@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Contact Details — '.$listing->name)

@php
    // Normalised values already on the listing — these are "grandfathered"
    // (already verified as far as this form is concerned) so the owner isn't
    // forced to re-OTP numbers/emails they saved before this rule existed.
    $verifiedMobiles = collect($listing->mobile_numbers ?? [])
        ->map(fn ($v) => \App\Support\MobileNumber::national($v))->filter()->values();
    $verifiedEmails = collect($listing->extra_emails ?? [])
        ->map(fn ($v) => strtolower(trim((string) $v)))->filter()->values();
@endphp

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Contact Details</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('danger'))
            <div class="alert alert-danger">{{ session('danger') }}</div>
        @endif

        <div class="sb-biz-panel-info-banner">
            <i class="fas fa-circle-info"></i>
            Update your contact details to stay in touch with your customers. A new mobile
            number or email must be verified with an OTP before it can be saved.
        </div>

        <form action="{{ route('user.listing.business-contact.update', $listing->id) }}" method="post" id="sbContactForm"
              data-send-otp-url="{{ route('user.listing.contact.send-otp', $listing->id) }}"
              data-verify-otp-url="{{ route('user.listing.contact.verify-otp', $listing->id) }}">
            @csrf

            <div class="sb-contact-block">
                <div class="sb-contact-row">
                    <div class="sb-biz-panel-field sb-contact-title">
                        <label>Title</label>
                        <select name="contact_title" class="sb-input">
                            @foreach(['Mr','Mrs','Ms'] as $t)
                                <option value="{{ $t }}" {{ $listing->contact_title === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sb-biz-panel-field sb-contact-grow">
                        <label>Contact Person Name</label>
                        <input type="text" name="contact_person" class="sb-input" value="{{ $listing->contact_person }}">
                    </div>
                </div>
            </div>

            <p class="sb-contact-section-label">Mobile Numbers</p>
            <div id="sbMobileList">
                @forelse(collect($listing->mobile_numbers ?? [])->values() as $i => $number)
                    <div class="sb-otp-field" data-channel="mobile">
                        <div class="sb-contact-row sb-contact-repeatable">
                            <div class="sb-biz-panel-field sb-contact-code">
                                <label>Code</label>
                                <select name="mobile_country_code[]" class="sb-input">
                                    <option value="+91" {{ (($listing->mobile_country_code[$i] ?? '+91') === '+91') ? 'selected' : '' }}>+91</option>
                                </select>
                            </div>
                            <div class="sb-biz-panel-field sb-contact-grow">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_numbers[]" class="sb-input sb-otp-value" value="{{ $number }}" data-original="{{ \App\Support\MobileNumber::national($number) }}">
                            </div>
                            <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddMobile"><i class="fas fa-plus"></i> Add Another Mobile Number</button>

            <p class="sb-contact-section-label">WhatsApp Numbers</p>
            <div id="sbWhatsappList">
                @forelse(collect($listing->whatsapp_numbers ?? [])->values() as $i => $number)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-code">
                            <label>Code</label>
                            <select name="whatsapp_country_code[]" class="sb-input">
                                <option value="+91" {{ (($listing->whatsapp_country_code[$i] ?? '+91') === '+91') ? 'selected' : '' }}>+91</option>
                            </select>
                        </div>
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>WhatsApp Number</label>
                            <input type="text" name="whatsapp_numbers[]" class="sb-input" value="{{ $number }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddWhatsapp"><i class="fas fa-plus"></i> Add Another WhatsApp Number</button>

            <p class="sb-contact-section-label">Email</p>
            <div id="sbEmailList">
                @forelse(collect($listing->extra_emails ?? [])->values() as $email)
                    <div class="sb-otp-field" data-channel="email">
                        <div class="sb-contact-row sb-contact-repeatable">
                            <div class="sb-biz-panel-field sb-contact-grow">
                                <label>Email</label>
                                <input type="email" name="extra_emails[]" class="sb-input sb-otp-value" value="{{ $email }}" data-original="{{ strtolower(trim($email)) }}">
                            </div>
                            <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddEmail"><i class="fas fa-plus"></i> Add Another Email</button>

            <p class="sb-contact-section-label">Landline Number</p>
            <div id="sbLandlineList">
                @forelse(collect($listing->landline_numbers ?? [])->values() as $i => $number)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-code">
                            <label>Code</label>
                            <select name="landline_country_code[]" class="sb-input">
                                <option value="+91" {{ (($listing->landline_country_code[$i] ?? '+91') === '+91') ? 'selected' : '' }}>+91</option>
                            </select>
                        </div>
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Landline Number</label>
                            <input type="text" name="landline_numbers[]" class="sb-input" value="{{ $number }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddLandline"><i class="fas fa-plus"></i> Add Landline Number</button>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-contact-section-label{ font-size:13px; font-weight:700; color:#222; margin:22px 0 10px; }
    .sb-contact-row{ display:flex; gap:10px; align-items:flex-end; margin-bottom:6px; }
    .sb-contact-code{ width:88px; flex-shrink:0; }
    .sb-contact-grow{ flex:1; min-width:0; }
    .sb-contact-title{ width:90px; flex-shrink:0; }
    .sb-biz-panel-field label{ display:block; font-size:11px; color:#888; margin-bottom:6px; height:14px; line-height:14px; }
    /* A native <select> and a text <input> render at slightly different
       heights; with the row bottom-aligned that pushed the "Code" column (and
       its label) below the number column. Lock every control to one height so
       both labels sit on the same line and the boxes line up. */
    .sb-contact-row .sb-input{ height:42px; box-sizing:border-box; }
    .sb-contact-remove{ border:0; background:transparent; color:#d9364f; padding:9px; flex-shrink:0; cursor:pointer; height:42px; }
    .sb-contact-add-btn{
        border:0;
        background:transparent;
        color:#1e90ff;
        font-size:13px;
        font-weight:600;
        padding:0;
        margin-bottom:6px;
        cursor:pointer;
    }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:26px; }

    /* OTP verify UI */
    .sb-otp-field{ margin-bottom:12px; }
    .sb-otp-controls{ margin:2px 0 0 98px; display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
    .sb-otp-field[data-channel="email"] .sb-otp-controls{ margin-left:0; }
    .sb-otp-verify-btn{
        border:1px solid #1e90ff; background:#eaf4fe; color:#1e90ff;
        border-radius:6px; padding:5px 14px; font-size:12px; font-weight:700; cursor:pointer;
    }
    .sb-otp-badge{ font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px; }
    .sb-otp-badge--ok{ color:#188f4b; }
    .sb-otp-badge--todo{ color:#d9800a; }
    .sb-otp-box{ display:flex; flex-wrap:wrap; align-items:center; gap:8px; width:100%; margin-top:6px; }
    .sb-otp-code{ width:150px; border:1px solid #d5d9dd; border-radius:6px; padding:7px 10px; font-size:14px; letter-spacing:2px; }
    .sb-otp-confirm{ border:0; background:#188f4b; color:#fff; border-radius:6px; padding:7px 16px; font-size:12px; font-weight:700; cursor:pointer; }
    .sb-otp-resend{ border:0; background:transparent; color:#1e90ff; font-size:12px; font-weight:600; cursor:pointer; }
    .sb-otp-msg{ font-size:12px; width:100%; }
    .sb-otp-msg.is-ok{ color:#188f4b; }
    .sb-otp-msg.is-err{ color:#d9364f; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var form = document.getElementById('sbContactForm');
    var sendUrl = form.getAttribute('data-send-otp-url');
    var verifyUrl = form.getAttribute('data-verify-otp-url');
    var token = form.querySelector('input[name=_token]').value;

    // Values considered verified this page-load: everything already saved on
    // the listing (grandfathered) plus anything OTP-verified during this visit.
    var verified = { mobile: new Set(), email: new Set() };
    @foreach($verifiedMobiles as $m) verified.mobile.add(@json((string) $m)); @endforeach
    @foreach($verifiedEmails as $e) verified.email.add(@json((string) $e)); @endforeach

    function normalize(channel, val){
        val = (val || '').trim();
        if (channel === 'email') return val.toLowerCase();
        var d = val.replace(/\D+/g, '');
        if (d.length === 12 && d.indexOf('91') === 0) d = d.slice(2);
        return d;
    }

    function post(url, body){
        return fetch(url, {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
            body: new URLSearchParams(body).toString()
        }).then(function(r){ return r.json().then(function(j){ return {ok:r.ok, body:j}; }); });
    }

    function setupField(field){
        if (field.dataset.wired) return;
        field.dataset.wired = '1';
        var channel = field.getAttribute('data-channel');
        var input = field.querySelector('.sb-otp-value');

        var controls = document.createElement('div');
        controls.className = 'sb-otp-controls';
        controls.innerHTML =
            '<button type="button" class="sb-otp-verify-btn">Verify</button>' +
            '<span class="sb-otp-badge sb-otp-badge--ok" hidden><i class="fas fa-circle-check"></i> Verified</span>' +
            '<span class="sb-otp-badge sb-otp-badge--todo" hidden>Not verified</span>' +
            '<div class="sb-otp-box" hidden>' +
                '<input type="text" class="sb-otp-code" inputmode="numeric" maxlength="6" placeholder="6-digit OTP">' +
                '<button type="button" class="sb-otp-confirm">Confirm</button>' +
                '<button type="button" class="sb-otp-resend">Resend</button>' +
                '<span class="sb-otp-msg"></span>' +
            '</div>';
        field.appendChild(controls);

        var verifyBtn = controls.querySelector('.sb-otp-verify-btn');
        var okBadge = controls.querySelector('.sb-otp-badge--ok');
        var todoBadge = controls.querySelector('.sb-otp-badge--todo');
        var box = controls.querySelector('.sb-otp-box');
        var codeInput = controls.querySelector('.sb-otp-code');
        var confirmBtn = controls.querySelector('.sb-otp-confirm');
        var resendBtn = controls.querySelector('.sb-otp-resend');
        var msg = controls.querySelector('.sb-otp-msg');

        function render(){
            var val = input.value.trim();
            var norm = normalize(channel, val);
            var isVerified = norm !== '' && verified[channel].has(norm);
            var empty = val === '';
            okBadge.hidden = !isVerified;
            todoBadge.hidden = isVerified || empty;
            verifyBtn.hidden = isVerified || empty;
            if (isVerified || empty) { box.hidden = true; }
        }

        input.addEventListener('input', function(){ box.hidden = true; msg.textContent = ''; render(); });

        verifyBtn.addEventListener('click', function(){
            var val = input.value.trim();
            verifyBtn.disabled = true; verifyBtn.textContent = 'Sending...';
            post(sendUrl, {channel:channel, value:val}).then(function(res){
                verifyBtn.disabled = false; verifyBtn.textContent = 'Verify';
                box.hidden = false;
                if (res.body.status){
                    msg.className = 'sb-otp-msg is-ok'; msg.textContent = res.body.message || 'OTP sent.';
                    codeInput.focus();
                } else {
                    msg.className = 'sb-otp-msg is-err'; msg.textContent = res.body.message || 'Could not send OTP.';
                }
            }).catch(function(){ verifyBtn.disabled = false; verifyBtn.textContent = 'Verify'; box.hidden = false; msg.className = 'sb-otp-msg is-err'; msg.textContent = 'Network error. Try again.'; });
        });

        resendBtn.addEventListener('click', function(){ verifyBtn.click(); });

        confirmBtn.addEventListener('click', function(){
            var val = input.value.trim();
            var code = codeInput.value.trim();
            if (code.length !== 6){ msg.className = 'sb-otp-msg is-err'; msg.textContent = 'Enter the 6-digit OTP.'; return; }
            confirmBtn.disabled = true; confirmBtn.textContent = 'Checking...';
            post(verifyUrl, {channel:channel, value:val, otp:code}).then(function(res){
                confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm';
                if (res.body.status){
                    verified[channel].add(normalize(channel, val));
                    msg.textContent = ''; render();
                } else {
                    msg.className = 'sb-otp-msg is-err'; msg.textContent = res.body.message || 'Invalid OTP.';
                }
            }).catch(function(){ confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm'; msg.className = 'sb-otp-msg is-err'; msg.textContent = 'Network error. Try again.'; });
        });

        render();
    }

    document.querySelectorAll('.sb-otp-field').forEach(setupField);

    function addRow(listId, template, wire){
        var list = document.getElementById(listId);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = template;
        var row = wrapper.firstElementChild;
        var rm = row.querySelector('.sb-contact-remove');
        if (rm) rm.addEventListener('click', function(){ row.remove(); });
        list.appendChild(row);
        if (wire) setupField(row);
    }

    document.querySelectorAll('.sb-contact-remove').forEach(function(btn){
        btn.addEventListener('click', function(){
            var host = btn.closest('.sb-otp-field') || btn.closest('.sb-contact-repeatable');
            if (host) host.remove();
        });
    });

    document.getElementById('sbAddMobile').addEventListener('click', function(){
        addRow('sbMobileList', '<div class="sb-otp-field" data-channel="mobile"><div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-code"><label>Code</label><select name="mobile_country_code[]" class="sb-input"><option value="+91" selected>+91</option></select></div><div class="sb-biz-panel-field sb-contact-grow"><label>Mobile Number</label><input type="text" name="mobile_numbers[]" class="sb-input sb-otp-value" data-original=""></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div></div>', true);
    });

    document.getElementById('sbAddWhatsapp').addEventListener('click', function(){
        addRow('sbWhatsappList', '<div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-code"><label>Code</label><select name="whatsapp_country_code[]" class="sb-input"><option value="+91" selected>+91</option></select></div><div class="sb-biz-panel-field sb-contact-grow"><label>WhatsApp Number</label><input type="text" name="whatsapp_numbers[]" class="sb-input"></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div>', false);
    });

    document.getElementById('sbAddEmail').addEventListener('click', function(){
        addRow('sbEmailList', '<div class="sb-otp-field" data-channel="email"><div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-grow"><label>Email</label><input type="email" name="extra_emails[]" class="sb-input sb-otp-value" data-original=""></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div></div>', true);
    });

    document.getElementById('sbAddLandline').addEventListener('click', function(){
        addRow('sbLandlineList', '<div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-code"><label>Code</label><select name="landline_country_code[]" class="sb-input"><option value="+91" selected>+91</option></select></div><div class="sb-biz-panel-field sb-contact-grow"><label>Landline Number</label><input type="text" name="landline_numbers[]" class="sb-input"></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div>', false);
    });

    // Block submit if any mobile/email value isn't verified.
    form.addEventListener('submit', function(e){
        var problem = null;
        form.querySelectorAll('.sb-otp-field').forEach(function(field){
            if (problem) return;
            var channel = field.getAttribute('data-channel');
            var input = field.querySelector('.sb-otp-value');
            var val = input.value.trim();
            if (val === '') return;
            if (!verified[channel].has(normalize(channel, val))) {
                problem = { field: field, channel: channel, val: val };
            }
        });
        if (problem){
            e.preventDefault();
            problem.field.scrollIntoView({behavior:'smooth', block:'center'});
            var vb = problem.field.querySelector('.sb-otp-verify-btn');
            if (vb) vb.focus();
            alert('Please verify the ' + (problem.channel === 'mobile' ? 'mobile number' : 'email') + ' "' + problem.val + '" with an OTP before saving.');
        }
    });
});
</script>
@endpush
