{{--
    The "Contact" popup behind every listing card's call button.

    Three steps in one box: take a mobile number, check the code that goes to
    it on WhatsApp, then put the call through. The number of the business is
    never in the page — it only ever comes back from the server, and only after
    the code is answered, which is what turns a call into a lead we can see.

    Included once in the site layout rather than per card: a results page
    carries twenty of these buttons and the AJAX "load more" adds twenty more,
    so the handler is delegated and the markup exists once.

    @see \App\Http\Controllers\ContactCallController
    @see partials/contact-button.blade.php — the button that opens this
--}}
<div id="sbCcPopup" class="sbcc-overlay" aria-hidden="true">
    <div class="sbcc-box" role="dialog" aria-modal="true" aria-labelledby="sbCcBiz">
        <button type="button" class="sbcc-close" id="sbCcClose" aria-label="Close">&times;</button>

        {{-- The business, not the form, is what the visitor came for: its
             initial sits in the middle of a ringing signal. --}}
        <div class="sbcc-hero">
            <div class="sbcc-signal">
                <span></span><span></span><span></span>
                <div class="sbcc-avatar" id="sbCcAvatar">S</div>
            </div>

            <span class="sbcc-kicker"><i class="las la-phone-volume"></i> Connect by call</span>
            <h4 id="sbCcBiz"></h4>
            <p id="sbCcArea"></p>
        </div>

        <div class="sbcc-sheet">
            <div class="sbcc-steps" id="sbCcSteps">
                <span class="sbcc-step-dot is-on"><b>1</b>Number</span>
                <span class="sbcc-step-dot"><b>2</b>OTP</span>
                <span class="sbcc-step-dot"><b>3</b>Call</span>
            </div>

            <div class="sbcc-msg" id="sbCcMsg"></div>

            <form class="sbcc-pane" id="sbCcStepPhone" novalidate>
                <label class="sbcc-label" for="sbCcPhone">Your mobile number</label>

                <div class="sbcc-input">
                    <span class="sbcc-flag">+91</span>
                    <input type="tel" id="sbCcPhone" maxlength="10" inputmode="numeric"
                        autocomplete="tel-national" placeholder="Enter your number">
                </div>

                <button type="submit" class="sbcc-btn" id="sbCcSend">
                    <span>Get OTP &amp; Connect</span>
                    <i class="las la-arrow-right"></i>
                </button>

                <ul class="sbcc-trust">
                    <li><i class="lab la-whatsapp"></i> Code on WhatsApp</li>
                    <li><i class="las la-bolt"></i> 20 seconds</li>
                    <li><i class="las la-tag"></i> Free</li>
                </ul>
            </form>

            <form class="sbcc-pane" id="sbCcStepOtp" novalidate hidden>
                <p class="sbcc-otp-text">
                    Code sent on WhatsApp to <strong id="sbCcOtpNum"></strong>
                    <button type="button" class="sbcc-link" id="sbCcChange">change</button>
                </p>

                <div class="sbcc-otp" id="sbCcBoxes">
                    <input type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                </div>

                <button type="submit" class="sbcc-btn" id="sbCcVerify" disabled>
                    <span>Verify &amp; Connect</span>
                    <i class="las la-arrow-right"></i>
                </button>

                <div class="sbcc-resend">
                    <span>Didn&rsquo;t get the code?</span>
                    <button type="button" class="sbcc-link" id="sbCcResend">Resend OTP</button>
                </div>
            </form>

            <div class="sbcc-pane sbcc-done" id="sbCcStepDone" hidden>
                <div class="sbcc-done-icon" id="sbCcDoneIcon"><i class="las la-phone-volume"></i></div>

                <h5 id="sbCcDoneTitle"></h5>
                <p id="sbCcDoneText"></p>

                <a href="#" class="sbcc-btn sbcc-btn-dial" id="sbCcDial" hidden></a>
                <button type="button" class="sbcc-btn sbcc-btn-ghost" id="sbCcDoneBtn">Done</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* The card's call button is a <button> now, not an <a>, and a button
       brings its own font and cursor with it. Everything else about it still
       comes from .btn-call on the listing card. */
    button.sb-contact-call{
        cursor:pointer;
        font:inherit;
        font-weight:600;
        line-height:1.4;
    }
    /* The listing page's sidebar link is a <button> here too, and that one
       has no background of its own to fall back on — left alone it renders
       as a grey browser button. Deliberately not in the rule above:
       .btn-call-main's green must still win there. */
    button.sb-contact-call.contact-link{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:9px 18px;
        border:1.5px solid #cfe2ff;
        border-radius:10px;
        background:#f1f7ff;
        color:#0d6efd;
        font-size:15px;
        font-weight:700;
        transition:background .15s, border-color .15s;
    }
    button.sb-contact-call.contact-link:hover{ background:#e3efff; border-color:#a9cdfd; }

    .sbcc-overlay{
        position:fixed; inset:0; z-index:999999; display:none;
        align-items:center; justify-content:center; padding:18px;
        background:rgba(4,18,11,.62); backdrop-filter:blur(5px);
    }
    .sbcc-overlay.show{ display:flex; }

    .sbcc-box{
        width:384px; max-width:100%; border-radius:26px;
        position:relative; overflow:hidden;
        background:radial-gradient(120% 70% at 50% 0%,#0f7a42 0%,#07381f 58%,#04240f 100%);
        box-shadow:0 34px 80px rgba(2,22,11,.5), 0 0 0 1px rgba(255,255,255,.06);
        animation:sbccRise .3s cubic-bezier(.2,1,.4,1);
    }
    @keyframes sbccRise{ from{ opacity:0; transform:translateY(26px) scale(.95); } }

    .sbcc-close{
        position:absolute; right:14px; top:14px; z-index:4;
        border:0; background:rgba(255,255,255,.12); color:#fff;
        width:32px; height:32px; border-radius:50%;
        font-size:21px; line-height:1; cursor:pointer;
        transition:background .15s, transform .2s;
    }
    .sbcc-close:hover{ background:rgba(255,255,255,.26); transform:rotate(90deg); }

    .sbcc-hero{ position:relative; padding:26px 24px 22px; text-align:center; color:#fff; }

    /* Three rings leaving the business's initial, a beat apart. */
    .sbcc-signal{
        position:relative; width:72px; height:72px; margin:0 auto 16px;
        display:flex; align-items:center; justify-content:center;
    }
    .sbcc-signal > span{
        position:absolute; inset:0; border-radius:50%;
        border:1.5px solid rgba(255,255,255,.4);
        animation:sbccWave 2.6s cubic-bezier(.2,.6,.3,1) infinite;
    }
    .sbcc-signal > span:nth-child(2){ animation-delay:.85s; }
    .sbcc-signal > span:nth-child(3){ animation-delay:1.7s; }
    @keyframes sbccWave{
        0%{ transform:scale(.88); opacity:.75; }
        100%{ transform:scale(2.05); opacity:0; }
    }
    .sbcc-avatar{
        position:relative; width:72px; height:72px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        background:linear-gradient(145deg,#ffffff 0%,#dbf5e6 100%);
        color:#0a6b38; font-size:30px; font-weight:800; letter-spacing:.5px;
        box-shadow:0 10px 26px rgba(0,0,0,.3);
    }

    .sbcc-kicker{
        display:inline-flex; align-items:center; gap:6px;
        padding:5px 12px; border-radius:999px; margin-bottom:10px;
        background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.16);
        font-size:11.5px; font-weight:700; letter-spacing:.9px; text-transform:uppercase;
        color:#c8f5dc;
    }
    .sbcc-kicker i{ font-size:14px; }
    .sbcc-hero h4{
        margin:0; font-size:20px; line-height:1.3; font-weight:700; color:#fff;
        /* A business name can run long; two lines, then an ellipsis. */
        display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
    }
    .sbcc-hero p{
        margin:5px 0 0; font-size:13px; color:rgba(255,255,255,.62);
    }
    .sbcc-hero p:empty{ display:none; }

    /* The white sheet rides up over the hero. */
    .sbcc-sheet{
        position:relative; background:#fff;
        border-radius:26px 26px 0 0;
        padding:18px 22px 24px;
        box-shadow:0 -10px 30px rgba(0,0,0,.18);
    }

    .sbcc-steps{
        display:flex; align-items:center; justify-content:center; gap:0;
        margin:0 0 20px;
    }
    .sbcc-step-dot{
        display:flex; align-items:center; gap:7px;
        font-size:11.5px; font-weight:700; letter-spacing:.3px; color:#b3bdc7;
        transition:color .2s;
    }
    .sbcc-step-dot b{
        width:23px; height:23px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        background:#edf1f5; color:#9aa6b2; font-size:11.5px; font-weight:700;
        transition:background .2s, color .2s, box-shadow .2s;
    }
    /* The rule between two steps, drawn by the one on the right. */
    .sbcc-step-dot + .sbcc-step-dot::before{
        content:''; width:26px; height:2px; border-radius:2px;
        background:#e6ebf0; margin:0 10px;
    }
    .sbcc-step-dot.is-on{ color:#0c7a41; }
    .sbcc-step-dot.is-on b{
        background:#0c7a41; color:#fff; box-shadow:0 0 0 4px rgba(12,122,65,.14);
    }
    .sbcc-step-dot.is-done{ color:#5f6d7a; }
    .sbcc-step-dot.is-done b{ background:#d6f2e2; color:#0c7a41; }

    .sbcc-msg{ display:none; font-size:13px; line-height:1.5; border-radius:11px; padding:10px 13px; margin-bottom:15px; }
    .sbcc-msg.is-error{ display:block; background:#fdecec; color:#b02a2a; }
    .sbcc-msg.is-info{ display:block; background:#eaf6ef; color:#15703f; }

    .sbcc-label{ display:block; font-size:12.5px; font-weight:700; color:#55636f; letter-spacing:.3px; margin-bottom:9px; }

    .sbcc-input{
        display:flex; align-items:center; height:56px;
        background:#f5f8f9; border:1.5px solid #e2e8ec; border-radius:16px;
        overflow:hidden;
        transition:border-color .18s, box-shadow .18s, background .18s;
    }
    .sbcc-input:focus-within{
        background:#fff; border-color:#0c7a41;
        box-shadow:0 0 0 4px rgba(12,122,65,.13);
    }
    .sbcc-flag{
        align-self:stretch; display:flex; align-items:center;
        padding:0 15px; font-size:15px; font-weight:800; color:#39474f;
        border-right:1.5px solid #e6ecef;
    }
    .sbcc-input input{
        flex:1; border:0; outline:none; height:100%; background:transparent;
        padding:0 15px; font-size:19px; font-weight:700; letter-spacing:1.4px;
        color:#182028; min-width:0;
    }
    .sbcc-input input::placeholder{ font-size:16px; font-weight:500; letter-spacing:normal; color:#b8c2cb; }

    .sbcc-btn{
        position:relative; overflow:hidden;
        display:flex; align-items:center; justify-content:center; gap:10px;
        width:100%; height:54px; margin-top:18px;
        border:0; border-radius:16px; cursor:pointer;
        background:linear-gradient(135deg,#14a84f 0%,#0a6b38 100%);
        box-shadow:0 12px 26px rgba(10,107,56,.32);
        color:#fff; font-size:15.5px; font-weight:700; letter-spacing:.3px;
        text-decoration:none;
        transition:filter .15s, box-shadow .15s, transform .1s;
    }
    .sbcc-btn i{ font-size:19px; }
    /* A slow sheen across the primary button, so it reads as the way out. */
    .sbcc-btn::after{
        content:''; position:absolute; top:0; left:-60%; width:45%; height:100%;
        background:linear-gradient(100deg,rgba(255,255,255,0) 0%,rgba(255,255,255,.3) 50%,rgba(255,255,255,0) 100%);
        transform:skewX(-18deg);
        animation:sbccSheen 3.4s ease-in-out infinite;
    }
    @keyframes sbccSheen{ 0%,65%{ left:-60%; } 100%{ left:120%; } }
    .sbcc-btn:hover{ filter:brightness(1.08); color:#fff; text-decoration:none; }
    .sbcc-btn:active{ transform:translateY(1px); box-shadow:0 7px 16px rgba(10,107,56,.3); }
    .sbcc-btn:disabled{
        background:#c3ccd4; box-shadow:none; cursor:not-allowed; filter:none;
    }
    .sbcc-btn:disabled::after,
    .sbcc-btn-ghost::after,
    .sbcc-btn.is-busy::after{ display:none; }
    .sbcc-btn.is-busy{ opacity:.85; pointer-events:none; }
    .sbcc-btn-ghost{ background:#f0f3f6; color:#49586a; box-shadow:none; }
    .sbcc-btn-ghost:hover{ background:#e6ebf1; color:#49586a; filter:none; }
    .sbcc-btn-dial{ font-size:19px; letter-spacing:1px; }

    .sbcc-trust{
        display:flex; flex-wrap:wrap; justify-content:center; gap:6px 16px;
        list-style:none; margin:16px 0 0; padding:0;
    }
    .sbcc-trust li{
        display:flex; align-items:center; gap:5px;
        font-size:11.5px; font-weight:600; color:#8794a1;
    }
    .sbcc-trust i{ font-size:14px; color:#14a84f; }

    .sbcc-otp-text{ margin:0 0 16px; font-size:13.5px; line-height:1.6; color:#55636f; text-align:center; }
    .sbcc-otp-text strong{ color:#182028; }
    .sbcc-link{
        border:0; background:none; padding:0; cursor:pointer;
        color:#0c7a41; font-size:13px; font-weight:700; text-decoration:underline;
    }

    .sbcc-otp{ display:flex; gap:8px; }
    .sbcc-otp input{
        width:100%; min-width:0; height:58px;
        background:#f5f8f9; border:1.5px solid #e2e8ec; border-radius:14px;
        text-align:center; font-size:22px; font-weight:800; color:#182028; outline:none;
        transition:border-color .18s, box-shadow .18s, background .18s;
    }
    .sbcc-otp input:focus{
        background:#fff; border-color:#0c7a41;
        box-shadow:0 0 0 4px rgba(12,122,65,.13);
    }

    .sbcc-resend{
        display:flex; justify-content:center; align-items:center; gap:7px;
        font-size:12.5px; color:#8794a1; margin-top:16px;
    }
    .sbcc-resend button:disabled{ color:#b3bdc7; text-decoration:none; cursor:default; }

    .sbcc-done{ text-align:center; }
    .sbcc-done h5{ margin:0 0 9px; font-size:19px; font-weight:700; color:#182028; }
    .sbcc-done p{ margin:0; font-size:14px; line-height:1.6; color:#64727f; }
    .sbcc-done-icon{
        width:80px; height:80px; border-radius:50%; margin:2px auto 18px;
        display:flex; align-items:center; justify-content:center;
        font-size:37px; color:#0a6b38; position:relative;
        background:linear-gradient(145deg,#e4f8ec 0%,#d5f0e6 100%);
    }
    .sbcc-done-icon.is-ringing::after{
        content:''; position:absolute; inset:0; border-radius:50%;
        border:2px solid #14a84f; animation:sbccWave 1.9s ease-out infinite;
    }
    .sbcc-done-icon.is-error{ background:#fdecec; color:#d63939; }

    .sbcc-spin{
        width:17px; height:17px; border-radius:50%; flex:none;
        border:2px solid rgba(255,255,255,.45); border-top-color:#fff;
        animation:sbccSpin .7s linear infinite;
    }
    @keyframes sbccSpin{ to{ transform:rotate(360deg); } }

    @media(max-width:420px){
        .sbcc-box{ border-radius:22px; }
        .sbcc-hero{ padding:22px 18px 20px; }
        .sbcc-signal, .sbcc-avatar{ width:64px; height:64px; }
        .sbcc-avatar{ font-size:27px; }
        .sbcc-hero h4{ font-size:18px; }
        .sbcc-sheet{ padding:16px 16px 20px; border-radius:22px 22px 0 0; }
        .sbcc-step-dot + .sbcc-step-dot::before{ width:18px; margin:0 7px; }
        .sbcc-otp{ gap:6px; }
        .sbcc-otp input{ height:52px; font-size:20px; border-radius:12px; }
    }

    @media (prefers-reduced-motion:reduce){
        .sbcc-signal > span,
        .sbcc-btn::after,
        .sbcc-done-icon.is-ringing::after{ animation:none; }
    }
</style>

<script>
(function () {
    var POPUP = document.getElementById('sbCcPopup');

    if (!POPUP) return;

    var SEND_URL    = @json(route('enquiry.otp.send'));
    var VERIFY_URL  = @json(route('enquiry.otp.verify'));
    var CONNECT_URL = @json(route('contact.call.connect'));
    var TOKEN       = @json(csrf_token());
    var RESEND_WAIT = 60;

    var el = {
        avatar:    document.getElementById('sbCcAvatar'),
        biz:       document.getElementById('sbCcBiz'),
        area:      document.getElementById('sbCcArea'),
        dots:      Array.prototype.slice.call(document.querySelectorAll('#sbCcSteps .sbcc-step-dot')),
        msg:       document.getElementById('sbCcMsg'),
        stepPhone: document.getElementById('sbCcStepPhone'),
        stepOtp:   document.getElementById('sbCcStepOtp'),
        stepDone:  document.getElementById('sbCcStepDone'),
        phone:     document.getElementById('sbCcPhone'),
        send:      document.getElementById('sbCcSend'),
        otpNum:    document.getElementById('sbCcOtpNum'),
        boxes:     Array.prototype.slice.call(document.querySelectorAll('#sbCcBoxes input')),
        verify:    document.getElementById('sbCcVerify'),
        resend:    document.getElementById('sbCcResend'),
        change:    document.getElementById('sbCcChange'),
        doneIcon:  document.getElementById('sbCcDoneIcon'),
        doneTitle: document.getElementById('sbCcDoneTitle'),
        doneText:  document.getElementById('sbCcDoneText'),
        dial:      document.getElementById('sbCcDial'),
        doneBtn:   document.getElementById('sbCcDoneBtn')
    };

    var listingId = null;
    var phone = '';
    var timer = null;

    /* The token baked into a page served from the HTML cache is swapped back
       to a live one by CachePublicPages, but a form on the page is still the
       better source when there is one. */
    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) return meta.getAttribute('content');

        var field = document.querySelector('input[name="_token"]');
        if (field && field.value) return field.value;

        return TOKEN;
    }

    function post(url, payload) {
        var body = new FormData();
        var tok = token();

        body.append('_token', tok);
        Object.keys(payload).forEach(function (key) { body.append(key, payload[key]); });

        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': tok,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body,
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                return { ok: res.ok, status: res.status, data: data };
            });
        });
    }

    /** The server's own words where there are any — they say what to fix. */
    function reason(result, fallback) {
        var data = result.data || {};

        if (data.errors) {
            var first = Object.keys(data.errors)[0];
            if (first && data.errors[first] && data.errors[first][0]) return data.errors[first][0];
        }

        if (result.status === 419) return 'Your session expired. Please refresh the page and try again.';
        if (result.status === 429) return 'Too many attempts. Please wait a minute and try again.';

        return data.message || fallback;
    }

    function note(text, kind) {
        el.msg.textContent = text || '';
        el.msg.className = 'sbcc-msg' + (text ? ' is-' + (kind || 'error') : '');
    }

    function busy(button, on, label) {
        if (!button) return;

        if (on) {
            // innerHTML, not textContent: these buttons carry an icon.
            button.dataset.label = button.innerHTML;
            button.classList.add('is-busy');
            button.innerHTML = '<span class="sbcc-spin"></span>' + (label || 'Please wait');
        } else {
            button.classList.remove('is-busy');
            button.innerHTML = button.dataset.label || button.innerHTML;
        }
    }

    /** Swap the visible pane and move the 1-2-3 marker with it. */
    function show(pane, index) {
        [el.stepPhone, el.stepOtp, el.stepDone].forEach(function (node) {
            node.hidden = node !== pane;
        });

        el.dots.forEach(function (dot, i) {
            dot.classList.toggle('is-on', i === index - 1);
            dot.classList.toggle('is-done', i < index - 1);
        });
    }

    function countdown() {
        var left = RESEND_WAIT;

        clearInterval(timer);
        el.resend.disabled = true;
        el.resend.textContent = 'Resend in ' + left + 's';

        timer = setInterval(function () {
            left -= 1;

            if (left <= 0) {
                clearInterval(timer);
                el.resend.disabled = false;
                el.resend.textContent = 'Resend OTP';
                return;
            }

            el.resend.textContent = 'Resend in ' + left + 's';
        }, 1000);
    }

    function open(button) {
        var name = button.dataset.listingName || '';

        listingId = button.dataset.listingId || null;
        phone = '';

        el.biz.textContent = name;
        el.area.textContent = button.dataset.listingArea || '';
        el.avatar.textContent = (name.trim()[0] || 'S').toUpperCase();

        note('');
        el.boxes.forEach(function (box) { box.value = ''; });
        el.verify.disabled = true;
        el.dial.hidden = true;
        show(el.stepPhone, 1);

        /* Same visitor, same browser, same number as last time — there is no
           reason to make them type it again. Nothing else is remembered. */
        try {
            el.phone.value = localStorage.getItem('sb_cc_phone') || '';
        } catch (e) { /* private window */ }

        POPUP.classList.add('show');
        POPUP.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        setTimeout(function () { el.phone.focus(); }, 60);
    }

    function close() {
        clearInterval(timer);
        POPUP.classList.remove('show');
        POPUP.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function sendOtp(button, resent) {
        var digits = (el.phone.value || '').replace(/\D+/g, '').slice(-10);

        if (digits.length !== 10) {
            note('Please enter your 10-digit mobile number.');
            return;
        }

        phone = digits;
        note('');
        busy(button, true, resent ? 'Sending' : 'Sending OTP');

        post(SEND_URL, { phone: phone }).then(function (result) {
            busy(button, false);

            if (!result.ok) {
                note(reason(result, 'Could not send the OTP. Please try again in a moment.'));
                return;
            }

            try {
                localStorage.setItem('sb_cc_phone', phone);
            } catch (e) { /* private window */ }

            /* Already proven in this session — no second code, straight
               through to the call. */
            if (result.data.verified) {
                connect();
                return;
            }

            el.otpNum.textContent = '+91 ' + phone;
            show(el.stepOtp, 2);
            note(result.data.message || '', 'info');
            countdown();

            setTimeout(function () { el.boxes[0].focus(); }, 60);
        }).catch(function () {
            busy(button, false);
            note('Network problem. Please check your connection and try again.');
        });
    }

    function code() {
        return el.boxes.map(function (box) { return box.value; }).join('');
    }

    function connect() {
        show(el.stepDone, 3);
        el.dial.hidden = true;
        el.doneIcon.className = 'sbcc-done-icon is-ringing';
        // Reset: a failed attempt left the warning glyph in here.
        el.doneIcon.innerHTML = '<i class="las la-phone-volume"></i>';
        el.doneTitle.textContent = 'Setting up your call';
        el.doneText.textContent = 'One moment, please.';
        note('');

        post(CONNECT_URL, { listing_id: listingId, phone: phone }).then(function (result) {
            var data = result.data || {};

            if (!result.ok || !data.status) {
                el.doneIcon.className = 'sbcc-done-icon is-error';
                el.doneIcon.innerHTML = '<i class="las la-exclamation"></i>';
                el.doneTitle.textContent = 'Could not connect';
                el.doneText.textContent = reason(result, 'Please try again in a moment.');
                return;
            }

            if (data.connected) {
                el.doneIcon.className = 'sbcc-done-icon is-ringing';
                el.doneTitle.textContent = 'Your phone is ringing';
                el.doneText.textContent = data.message;
                return;
            }

            el.doneIcon.className = 'sbcc-done-icon';
            el.doneTitle.textContent = 'Mobile verified';
            el.doneText.textContent = data.message || 'Here is the number — tap to call.';
            el.dial.innerHTML = '<i class="las la-phone"></i><span>' + (data.number || '') + '</span>';
            el.dial.setAttribute('href', data.tel || '#');
            el.dial.hidden = !data.number;
        }).catch(function () {
            el.doneIcon.className = 'sbcc-done-icon is-error';
            el.doneIcon.innerHTML = '<i class="las la-exclamation"></i>';
            el.doneTitle.textContent = 'Could not connect';
            el.doneText.textContent = 'Network problem. Please check your connection and try again.';
        });
    }

    document.addEventListener('click', function (e) {
        var button = e.target.closest('.sb-contact-call');

        if (button) {
            e.preventDefault();
            open(button);
        }
    });

    POPUP.addEventListener('click', function (e) {
        if (e.target === POPUP) close();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && POPUP.classList.contains('show')) close();
    });

    document.getElementById('sbCcClose').addEventListener('click', close);
    el.doneBtn.addEventListener('click', close);

    el.phone.addEventListener('input', function () {
        this.value = this.value.replace(/\D+/g, '').slice(0, 10);
    });

    el.stepPhone.addEventListener('submit', function (e) {
        e.preventDefault();
        sendOtp(el.send, false);
    });

    el.resend.addEventListener('click', function () {
        if (!el.resend.disabled) sendOtp(el.resend, true);
    });

    el.change.addEventListener('click', function () {
        clearInterval(timer);
        note('');
        show(el.stepPhone, 1);
        el.phone.focus();
    });

    el.boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            this.value = this.value.replace(/\D+/g, '').slice(0, 1);

            if (this.value && el.boxes[index + 1]) el.boxes[index + 1].focus();

            el.verify.disabled = code().length !== 6;
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && el.boxes[index - 1]) {
                el.boxes[index - 1].focus();
            }
        });

        /* WhatsApp hands the code over as one block when it is pasted or
           picked from the keyboard's suggestion strip. */
        box.addEventListener('paste', function (e) {
            var text = (e.clipboardData || window.clipboardData).getData('text') || '';
            var digits = text.replace(/\D+/g, '').slice(0, 6);

            if (!digits) return;

            e.preventDefault();
            el.boxes.forEach(function (target, i) { target.value = digits[i] || ''; });
            el.verify.disabled = digits.length !== 6;
            el.boxes[Math.min(digits.length, 5)].focus();
        });
    });

    el.stepOtp.addEventListener('submit', function (e) {
        e.preventDefault();

        var otp = code();

        if (otp.length !== 6) return;

        note('');
        busy(el.verify, true, 'Verifying');

        post(VERIFY_URL, { phone: phone, otp: otp }).then(function (result) {
            busy(el.verify, false);

            if (!result.ok || !result.data.verified) {
                note(reason(result, 'Incorrect OTP. Please try again.'));
                el.boxes.forEach(function (box) { box.value = ''; });
                el.verify.disabled = true;
                el.boxes[0].focus();
                return;
            }

            clearInterval(timer);
            connect();
        }).catch(function () {
            busy(el.verify, false);
            note('Network problem. Please check your connection and try again.');
        });
    });
})();
</script>
