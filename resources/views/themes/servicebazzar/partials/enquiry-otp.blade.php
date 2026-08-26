@php
    /*
     * Every form that sends a lead somewhere. The gate is keyed on the form's
     * action path, so a form added to any page — or injected later by the AJAX
     * listing partial — is covered the moment it posts to one of these.
     */
    $otpGatedPaths = collect([
        route('contact.author')       => 'enquiry',
        route('contact.author.email') => 'enquiry',
        route('request-quote.store')  => 'quote request',
        route('contact.sumbit')       => 'message',
    ])->mapWithKeys(fn ($label, $url) => [
        rtrim((string) parse_url($url, PHP_URL_PATH), '/') => $label,
    ]);
@endphp

{{--
    The OTP step and the result popup that sit in front of every enquiry form.

    Included once in the site layout rather than bolted onto each form: there
    are a dozen enquiry forms across the listing pages, the search results, the
    AJAX-injected cards and the quote page, and they all post to the same three
    endpoints. One delegated submit handler covers every one of them, including
    forms that did not exist when the page loaded.

    The forms are submitted over AJAX from here on purpose. For a logged-out
    visitor the listing pages are served from the shared HTML cache
    (CachePublicPages), and a redirect back to a cached page cannot show a
    flash message — which is why a submitted enquiry, a wrong mobile number and
    a missing field all used to look identical: an unchanged page. Now every
    one of those gets a popup that says what happened.

    This is the visitor's side of the check only — SiteController rejects an
    unverified number regardless of what happens here (requireVerifiedPhone()).
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

<div id="sbEnqResultPopup" class="sb-auth-popup">
    <div class="sb-auth-box sb-enq-result">
        <button type="button" class="sb-auth-close" id="sbEnqResultClose">&times;</button>

        <div class="sb-enq-icon" id="sbEnqResultIcon"></div>

        <h4 id="sbEnqResultTitle"></h4>
        <p id="sbEnqResultText"></p>
        <ul id="sbEnqResultList"></ul>

        <button type="button" class="sb-btn" id="sbEnqResultOk">Done</button>
    </div>
</div>

<style>
    .sb-enq-result{ text-align:center; }
    .sb-enq-result h4{ margin:0 0 8px; font-size:20px; font-weight:700; }
    .sb-enq-result p{ margin:0 0 12px; color:#5b6b7f; font-size:14px; line-height:1.55; }
    .sb-enq-result ul{ list-style:none; padding:0; margin:0 0 14px; text-align:left; }
    .sb-enq-result ul:empty{ display:none; }
    .sb-enq-result ul li{
        background:#fdecec; color:#b02a2a; border-radius:8px;
        padding:9px 12px; margin-bottom:6px; font-size:13px; line-height:1.4;
    }
    .sb-enq-icon{
        width:64px; height:64px; border-radius:50%; margin:8px auto 16px;
        display:flex; align-items:center; justify-content:center;
        font-size:32px; font-weight:700; line-height:1;
    }
    .sb-enq-icon.is-success{ background:#e6f7ee; color:#22a45d; }
    .sb-enq-icon.is-error{ background:#fdecec; color:#d63939; }
    .sb-enq-icon.is-loading{ background:#eef2f7; }
    .sb-enq-icon.is-loading::after{
        content:''; width:26px; height:26px; border-radius:50%;
        border:3px solid #c9d4e2; border-top-color:#5b6b7f;
        animation:sbEnqSpin .7s linear infinite;
    }
    @keyframes sbEnqSpin{ to{ transform:rotate(360deg); } }
</style>

<script>
(function () {
    /* path => what the visitor just sent, so the popup can name it. */
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

    var resultPopup = document.getElementById('sbEnqResultPopup');
    var resultIcon  = document.getElementById('sbEnqResultIcon');
    var resultTitle = document.getElementById('sbEnqResultTitle');
    var resultText  = document.getElementById('sbEnqResultText');
    var resultList  = document.getElementById('sbEnqResultList');
    var resultOk    = document.getElementById('sbEnqResultOk');
    var resultClose = document.getElementById('sbEnqResultClose');

    if (!popup || !otpForm || !resultPopup) return;

    var activeForm  = null;
    var activePhone = '';
    var activeLabel = 'enquiry';
    var resendTimer = null;
    var busy        = false;

    // One OTP round per attempt. Without this, a server that keeps answering
    // "verify first" would bounce the visitor round the loop forever.
    var otpRounds   = 0;

    function capitalise(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

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

    function readJson(response) {
        return response.json().catch(function () { return {}; }).then(function (data) {
            return { ok: response.ok, status: response.status, data: data };
        });
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
        }).then(readJson);
    }

    /** Every validation message the server sent back, in order. */
    function errorList(data) {
        var messages = [];

        if (data && data.errors) {
            Object.keys(data.errors).forEach(function (key) {
                (data.errors[key] || []).forEach(function (text) { messages.push(text); });
            });
        }

        return messages;
    }

    function firstError(data) {
        var messages = errorList(data);

        if (messages.length) return messages[0];

        return (data && data.message) || 'Something went wrong. Please try again.';
    }

    function showMsg(type, text) {
        msgBox.innerHTML = '<div class="sb-' + type + '">' + text + '</div>';
    }

    function clearMsg() {
        msgBox.innerHTML = '';
    }

    function syncVerifyBtn() {
        verifyBtn.disabled = typedOtp().length !== 6;
    }

    function typedOtp() {
        return boxes.map(function (box) { return box.value.replace(/\D/g, ''); }).join('');
    }

    function lockScroll() { document.body.style.overflow = 'hidden'; }
    function unlockScroll() { document.body.style.overflow = ''; }

    function openPopup() {
        popup.classList.add('show');
        lockScroll();
        boxes.forEach(function (box) { box.value = ''; });
        syncVerifyBtn();
        if (boxes[0]) boxes[0].focus();
    }

    function closePopup() {
        popup.classList.remove('show');
        if (!resultPopup.classList.contains('show')) unlockScroll();
        clearMsg();
        stopResendCountdown();
    }

    /* ---- the result popup: the only thing the visitor is told by ------ */

    function showResult(kind, title, text, messages) {
        popup.classList.remove('show');

        resultIcon.className = 'sb-enq-icon is-' + kind;
        resultIcon.textContent = kind === 'success' ? '✓' : (kind === 'error' ? '!' : '');
        resultTitle.textContent = title;
        resultText.textContent = text || '';
        resultList.innerHTML = '';

        (messages || []).forEach(function (message) {
            var item = document.createElement('li');
            item.textContent = message;
            resultList.appendChild(item);
        });

        // Nothing to dismiss while the request is still in flight.
        resultOk.style.display  = kind === 'loading' ? 'none' : '';
        resultClose.style.display = kind === 'loading' ? 'none' : '';
        resultOk.textContent = kind === 'success' ? 'Done' : 'Close';

        resultPopup.classList.add('show');
        lockScroll();
    }

    function closeResult() {
        resultPopup.classList.remove('show');
        unlockScroll();
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

    /*
     * Several enquiry forms live inside something that opens over the page —
     * the "Get the list of best listings" popup, the SMS/email modal, the
     * sticky side form. A full page reload used to take that with it; now that
     * the form posts over AJAX, nothing does, and the visitor is left looking
     * at the form they have already sent.
     *
     * The page's own close button is clicked where there is one, so whatever
     * it resets (page scroll, the "popup already shown" flag) is reset the way
     * that page expects; the open-state class is dropped either way.
     */
    function closeHostOverlay(form) {
        var node = form.parentElement;

        while (node && node !== document.body) {
            var style = window.getComputedStyle(node);

            if (style.position === 'fixed' && style.display !== 'none') {
                var dismiss = node.querySelector(
                    '[data-bs-dismiss="modal"], [data-dismiss="modal"], button[id*="close" i], button[class*="close" i]'
                );

                if (dismiss) dismiss.click();

                node.classList.remove('show', 'active', 'open', 'is-open');

                return;
            }

            node = node.parentElement;
        }
    }

    /* ---- sending the enquiry itself ---------------------------------- */

    function submitEnquiry(form) {
        if (!form) return;

        showResult('loading', 'Sending your ' + activeLabel + '…', 'Please wait a moment.');

        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': tokenFor(form),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(readJson).then(function (result) {
            if (result.ok && result.data.status) {
                form.reset();
                closeHostOverlay(form);
                showResult('success', capitalise(activeLabel) + ' sent', result.data.message || 'The business will contact you shortly.');
                return;
            }

            // The number has not been proven yet (or the session lost it
            // between the OTP and the submit). This is the normal path for a
            // first-time visitor: the server asks, and the OTP step starts.
            if (result.data && result.data.otp_required && otpRounds < 1) {
                otpRounds++;
                closeResult();
                requestOtp(form, activePhone, false);
                return;
            }

            var messages = errorList(result.data);

            showResult(
                'error',
                capitalise(activeLabel) + ' not sent',
                messages.length ? 'Please fix the following and try again:' : firstError(result.data),
                messages
            );
        }).catch(function () {
            showResult('error', capitalise(activeLabel) + ' not sent', 'Network error. Please check your connection and try again.');
        });
    }

    function requestOtp(form, phone, isResend) {
        if (busy) return;

        busy = true;

        post(SEND_URL, tokenFor(form), { phone: phone }).then(function (result) {
            busy = false;

            if (!result.ok) {
                showResult('error', 'Could not send the OTP', firstError(result.data));
                return;
            }

            // Already proven in this session — nothing to ask, straight back
            // to the enquiry.
            if (result.data.verified) {
                closePopup();
                submitEnquiry(form);
                return;
            }

            numberEl.textContent = '+91 ' + phone;

            if (!popup.classList.contains('show')) {
                closeResult();
                openPopup();
            }

            clearMsg();

            if (isResend) showMsg('success', result.data.message || 'OTP sent again.');

            startResendCountdown();
        }).catch(function () {
            busy = false;
            showResult('error', 'Could not send the OTP', 'Network error. Please check your connection and try again.');
        });
    }

    /* ---- the gate itself --------------------------------------------- */

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || form.tagName !== 'FORM') return;

        var path;

        try {
            path = new URL(form.getAttribute('action') || window.location.href, window.location.href)
                .pathname.replace(/\/$/, '');
        } catch (e) {
            return;
        }

        if (!Object.prototype.hasOwnProperty.call(GATED_PATHS, path)) return;

        var field = form.querySelector('[name="phone"]');

        if (!field) return;

        // Nothing about an enquiry form submits the old way any more: even an
        // obviously wrong number goes through here, so the visitor gets a
        // popup that says what is wrong instead of a silent page reload.
        event.preventDefault();

        activeForm  = form;
        activePhone = digitsOf(field.value);
        activeLabel = GATED_PATHS[path] || 'enquiry';
        otpRounds   = 0;

        // Send the enquiry first and let the server say whether an OTP is
        // still owed. Asking the OTP endpoint up front spent a request (and a
        // slice of its much tighter rate limit) even for a visitor who had
        // already verified, and every form-level validation message — a name
        // that is not a name, a missing field — arrives from here in one go.
        submitEnquiry(form);
    }, true);

    /* ---- modal wiring ------------------------------------------------ */

    closeBtn.addEventListener('click', closePopup);
    resultOk.addEventListener('click', closeResult);
    resultClose.addEventListener('click', closeResult);

    popup.addEventListener('click', function (event) {
        if (event.target === popup) closePopup();
    });

    resultPopup.addEventListener('click', function (event) {
        if (event.target === resultPopup && resultOk.style.display !== 'none') closeResult();
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

            var form = activeForm;

            closePopup();
            submitEnquiry(form);
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
