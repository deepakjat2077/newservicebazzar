<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification – Sales Genio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 48px 44px;
            width: 440px;
            max-width: 95vw;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            text-align: center;
        }

        .logo { max-width: 150px; margin-bottom: 28px; }

        .shield-icon {
            width: 64px;
            height: 64px;
            background: #e8f0fe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .shield-icon svg { width: 30px; height: 30px; fill: #1a73e8; }

        h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 8px; }

        .sub {
            font-size: 13px;
            color: #888;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .sub strong { color: #1a1a2e; }

        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .otp-inputs input {
            width: 48px;
            height: 56px;
            border: 2px solid #dde2ec;
            border-radius: 10px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            color: #1a1a2e;
            outline: none;
            transition: border-color .2s;
        }

        .otp-inputs input:focus { border-color: #1a73e8; }

        input[name="otp"] { display: none; }

        .error-msg {
            background: #fde8e8;
            border: 1px solid #f5c6cb;
            color: #c62828;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .btn-verify {
            width: 100%;
            padding: 13px;
            background: #1a73e8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-verify:hover { background: #1557b0; }

        .back-link {
            display: block;
            margin-top: 18px;
            font-size: 13px;
            color: #888;
            text-decoration: none;
        }

        .back-link:hover { color: #1a73e8; }

        .timer { font-size: 12px; color: #aaa; margin-top: 14px; }
        .timer span { color: #1a73e8; font-weight: 600; }
    </style>
</head>
<body>

<div class="card">
    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar" class="logo">

    <div class="shield-icon">
        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
    </div>

    <h2>OTP Verification</h2>
    <p class="sub">
        We have sent a 6-digit OTP to your WhatsApp number
        @if(session('phone_hint'))
            <strong>+91 {{ session('phone_hint') }}</strong>
        @endif
        . It is valid for 10 minutes.
    </p>

    @if ($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('sales_genio.otp.verify') }}" id="otpForm">
        @csrf
        <input type="hidden" name="otp" id="otpValue">

        <div class="otp-inputs" id="otpBoxes">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
        </div>

        <button type="submit" class="btn-verify">VERIFY OTP</button>
    </form>

    <div class="timer">OTP expires in <span id="countdown">10:00</span></div>

    <a href="{{ route('sales_genio.login') }}" class="back-link">Back to Login</a>
</div>

<script>
    const boxes = document.querySelectorAll('#otpBoxes input');

    boxes.forEach((box, i) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '');
            if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
            autoVerify();
        });
        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
        });
        box.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            [...text].slice(0, 6).forEach((ch, j) => { if (boxes[j]) boxes[j].value = ch; });
            boxes[Math.min(text.length, 5)].focus();
            autoVerify();
        });
    });

    const form = document.getElementById('otpForm');
    const verifyBtn = form.querySelector('.btn-verify');
    let sending = false;

    function syncOtp() {
        const otp = [...boxes].map(b => b.value).join('');
        document.getElementById('otpValue').value = otp;

        return otp;
    }

    /**
     * The sixth digit is the whole answer, so there is nothing left to press —
     * submit as soon as all six are in. The button stays for anyone who
     * pastes, corrects a digit, or is using a screen reader.
     */
    function autoVerify() {
        if (sending || syncOtp().length < 6) {
            return;
        }

        sending = true;
        boxes.forEach(b => b.blur());
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'VERIFYING…';
        form.submit();
    }

    form.addEventListener('submit', function (e) {
        if (syncOtp().length < 6) {
            e.preventDefault();
            boxes[0].focus();
            return;
        }

        sending = true;
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'VERIFYING…';
    });

    // Countdown timer (10 min)
    let seconds = 600;
    const el = document.getElementById('countdown');
    const timer = setInterval(() => {
        seconds--;
        if (seconds <= 0) { clearInterval(timer); el.textContent = 'Expired'; return; }
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        el.textContent = m + ':' + s;
    }, 1000);

    boxes[0].focus();
</script>
</body>
</html>
