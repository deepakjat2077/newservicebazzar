@php
    /*
     * Every form that sends a lead somewhere. The gate is keyed on the form's
     * action path, so a form added to any page — or injected later by the AJAX
     * listing partial — is covered the moment it posts to one of these.
     */
    $otpGatedPaths = collect([
        route('contact.author'),
        route('contact.author.email'),
        route('request-quote.store'),
    ])->map(fn ($url) => rtrim((string) parse_url($url, PHP_URL_PATH), '/'))->unique()->values();
@endphp

{{--
    The OTP step in front of the enquiry forms.

    Included once in the site layout rather than bolted onto each form: there
    are a dozen enquiry forms across the listing pages, the search results, the
    AJAX-injected cards and the quote page, and they all post to the same three
    endpoints. One delegated submit handler covers every one of them, including
    forms that did not exist when the page loaded.

    This is the visitor's side of the check only — SiteController rejects an
    unverified number regardless of what happens here (see
    requireVerifiedPhone()).
--}}
<div id="sbEnquiryOtpPopup" class="sb-auth-popup">
    <div class="sb-auth-box">
        <button type="button" class="sb-auth-close" id="sbEnqOtpClose">&times;</button>

        <div class="sb-auth-head">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
            <div>
                <h4>Verify your mobile</h4>
                <p>So the business can call you back</p>
            </div>
        </div>

        <div id="sbEnqOtpMsg"></div>

        <div class="sb-otp-text">
            Enter the code sent on WhatsApp to <strong id="sbEnqOtpNumber"></strong>
        </div>

        <form id="sbEnqOtpForm">
            <div class="sb-otp-boxes" id="sbEnqOtpBoxes">
                <input type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
            </div>

            <div class="sb-otp-bottom">
                <span>Didn&rsquo;t receive the OTP?</span>
                <button type="button" id="sbEnqOtpResend">Resend OTP</button>
            </div>

            <button type="submit" class="sb-btn" id="sbEnqOtpVerify" disabled>Verify &amp; Send Enquiry</button>
        </form>
    </div>
</div>

<script>
(function () {
    var GATED_PATHS = @json($otpGatedPaths);
    var SEND_URL    = "{{ route('enquiry.otp.send') }}";
    var VERIFY_URL  = "{{ route('enquiry.otp.verify') }}";
    var RESEND_WAIT = 60;

    var popup     = document.getElementById('sbEnquiryOtpPopup');
    var closeBtn  = document.getElementById('sbEnqOtpClose');
    var msgBox    = document.getElementById('sbEnqOtpMsg');
    var numberEl  = document.getElementById('sbEnqOtpNumber');
    var otpForm   = document.getElementById('sbEnqOtpForm');
    var boxes     = Array.prototype.slice.call(document.querySelectorAll('#sbEnqOtpBoxes input'));
    var verifyBtn = document.getElementById('sbEnqOtpVerify');
    var resendBtn = document.getElementById('sbEnqOtpResend');

    if (!popup || !otpForm) return;

    // Numbers this visitor has already proven, mirroring the session so a
    // second enquiry does not ask again. The server keeps the real record.
    var verified    = Object.create(null);
    var activeForm  = null;
    var activePhone = '';
    var resendTimer = null;
    var busy        = false;

    function digitsOf(value) {
        var digits = String(value || '').replace(/\D+/g, '');

        if (digits.length === 12 && digits.indexOf('91') === 0) return digits.slice(2);
        if (digits.length === 11 && digits.charAt(0) === '0') return digits.slice(1);

        return digits;
    }

    function tokenFor(form) {
        var field = form && form.querySelector('input[name="_token"]');

        if (field && field.value) return field.value;

        var meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    function post(url, token, payload) {
        var body = new FormData();

        body.append('_token', token);
        Object.keys(payload).forEach(function (key) { body.append(key, payload[key]); });

        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                return { ok: response.ok, data: data };
            });
        });
    }

    function firstError(data) {
        if (data && data.errors) {
            for (var key in data.errors) {
                if (data.errors[key] && data.errors[key].length) return data.errors[key][0];
            }
        }

        return (data && data.message) || 'Something went wrong. Please try again.';
    }

    function showMsg(type, text) {
        msgBox.innerHTML = '<div class="sb-' + type + '">' + text + '</div>';
    }

    function clearMsg() {
        msgBox.innerHTML = '';
    }

    function announce(type, text) {
        if (popup.classList.contains('show')) {
            showMsg(type, text);
        } else if (window.toastr) {
            type === 'success' ? toastr.success(text) : toastr.error(text);
        } else {
            alert(text);
        }
    }

    function syncVerifyBtn() {
        verifyBtn.disabled = typedOtp().length !== 6;
    }

    function typedOtp() {
        return boxes.map(function (box) { return box.value.replace(/\D/g, ''); }).join('');
    }

    function openPopup() {
        popup.classList.add('show');
        document.body.style.overflow = 'hidden';
        boxes.forEach(function (box) { box.value = ''; });
        syncVerifyBtn();
        if (boxes[0]) boxes[0].focus();
    }

    function closePopup() {
        popup.classList.remove('show');
        document.body.style.overflow = '';
        clearMsg();
        stopResendCountdown();
        activeForm = null;
    }

    function stopResendCountdown() {
        if (resendTimer) {
            clearInterval(resendTimer);
            resendTimer = null;
        }

        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend OTP';
    }

    function startResendCountdown() {
        var left = RESEND_WAIT;

        resendBtn.disabled = true;
        resendBtn.textContent = 'Resend in ' + left + 's';

        resendTimer = setInterval(function () {
            left -= 1;

            if (left <= 0) {
                stopResendCountdown();
                return;
            }

            resendBtn.textContent = 'Resend in ' + left + 's';
        }, 1000);
    }

    /* The number is proven — let the form go through untouched. */
    function release(form) {
        if (!form) return;

        form.dataset.sbOtpDone = '1';

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    function requestOtp(form, phone, isResend) {
        if (busy) return;

        busy = true;

        post(SEND_URL, tokenFor(form), { phone: phone }).then(function (result) {
            busy = false;

            if (!result.ok) {
                announce('error', firstError(result.data));
                return;
            }

            if (result.data.verified) {
                verified[phone] = true;
                closePopup();
                release(form);
                return;
            }

            numberEl.textContent = '+91 ' + phone;

            if (!popup.classList.contains('show')) openPopup();

            clearMsg();

            if (isResend) showMsg('success', result.data.message || 'OTP sent again.');

            startResendCountdown();
        }).catch(function () {
            busy = false;
            announce('error', 'Network error. Please try again.');
        });
    }

    /* ---- the gate itself --------------------------------------------- */

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || form.tagName !== 'FORM') return;
        if (form.dataset.sbOtpDone === '1') return;

        var path;

        try {
            path = new URL(form.getAttribute('action') || window.location.href, window.location.href)
                .pathname.replace(/\/$/, '');
        } catch (e) {
            return;
        }

        if (GATED_PATHS.indexOf(path) === -1) return;

        var field = form.querySelector('[name="phone"]');

        if (!field) return;

        var phone = digitsOf(field.value);

        // Not a number worth spending an OTP on — let the server's own
        // validation give the visitor the usual message.
        if (phone.length !== 10) return;
        if (verified[phone]) return;

        event.preventDefault();

        activeForm  = form;
        activePhone = phone;

        requestOtp(form, phone, false);
    }, true);

    /* ---- modal wiring ------------------------------------------------ */

    closeBtn.addEventListener('click', closePopup);

    popup.addEventListener('click', function (event) {
        if (event.target === popup) closePopup();
    });

    boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);

            if (box.value && boxes[index + 1]) boxes[index + 1].focus();

            syncVerifyBtn();
        });

        box.addEventListener('keydown', function (event) {
            if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
                boxes[index - 1].focus();
            }
        });

        box.addEventListener('paste', function (event) {
            var pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');

            if (!pasted) return;

            event.preventDefault();

            boxes.forEach(function (target, i) { target.value = pasted.charAt(i) || ''; });

            syncVerifyBtn();
            (boxes[Math.min(pasted.length, boxes.length - 1)] || boxes[0]).focus();
        });
    });

    otpForm.addEventListener('submit', function (event) {
        event.preventDefault();

        if (busy || !activeForm) return;

        var otp = typedOtp();

        if (otp.length !== 6) return;

        busy = true;
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Verifying...';

        post(VERIFY_URL, tokenFor(activeForm), { phone: activePhone, otp: otp }).then(function (result) {
            busy = false;
            verifyBtn.textContent = 'Verify & Send Enquiry';

            if (!result.ok || !result.data.verified) {
                showMsg('error', firstError(result.data));
                boxes.forEach(function (box) { box.value = ''; });
                syncVerifyBtn();
                if (boxes[0]) boxes[0].focus();
                return;
            }

            verified[activePhone] = true;

            var form = activeForm;

            closePopup();
            release(form);
        }).catch(function () {
            busy = false;
            verifyBtn.textContent = 'Verify & Send Enquiry';
            syncVerifyBtn();
            showMsg('error', 'Network error. Please try again.');
        });
    });

    resendBtn.addEventListener('click', function () {
        if (!activeForm || resendBtn.disabled) return;

        clearMsg();
        requestOtp(activeForm, activePhone, true);
    });
})();
</script>
