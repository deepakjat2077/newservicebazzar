@php
    $isReset = ($mode ?? 'login') === 'reset';
    $needEmpcode = session('need_empcode') || old('empcode');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isReset ? 'Forgot Password' : 'Login with OTP' }} – SB Sales</title>
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
            padding: 16px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 32px 32px;
            width: 420px;
            max-width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
        }

        .logo { display: block; max-width: 150px; margin: 0 auto 18px; }

        h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; text-align: center; }
        h2 span { color: #1a73e8; }

        .sub { font-size: 13px; color: #888; margin-bottom: 20px; line-height: 1.6; text-align: center; }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 7px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #1a1a2e;
            background: #fafbff;
            outline: none;
        }

        .form-group input:focus { border-color: #1a73e8; background: #fff; }

        .phone-wrap { display: flex; }
        .phone-wrap .cc {
            padding: 12px 12px;
            border: 1.5px solid #dde2ec;
            border-right: 0;
            border-radius: 8px 0 0 8px;
            background: #f0f2f5;
            font-size: 14px;
            color: #555;
        }
        .phone-wrap input { border-radius: 0 8px 8px 0; }

        .error-msg {
            background: #fde8e8;
            border: 1px solid #f5c6cb;
            color: #c62828;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .btn-main {
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
            letter-spacing: .5px;
            margin-top: 6px;
        }

        .btn-main:hover { background: #1557b0; }
        .btn-main:disabled { opacity: .7; cursor: default; }

        .links { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; font-size: 13px; white-space: nowrap; }
        .links a { color: #1a73e8; text-decoration: none; font-weight: 500; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar" class="logo">

    @if($isReset)
        <h2>Forgot <span>Password</span></h2>
        <p class="sub">Enter your registered mobile number. We will send a 6-digit OTP to your WhatsApp to set a new password.</p>
    @else
        <h2>Login with <span>OTP</span></h2>
        <p class="sub">Enter your registered mobile number. We will send a 6-digit OTP to your WhatsApp.</p>
    @endif

    @if ($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form method="POST" id="mobileForm"
          action="{{ $isReset ? route('sales_genio.password.forgot.send') : route('sales_genio.otp.login.send') }}">
        @csrf

        <div class="form-group">
            <label for="mobile">Mobile Number</label>
            <div class="phone-wrap">
                <span class="cc">+91</span>
                <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}"
                       placeholder="10-digit mobile number" inputmode="numeric" maxlength="14"
                       autocomplete="tel-national" required autofocus>
            </div>
        </div>

        @if($needEmpcode)
            <div class="form-group">
                <label for="empcode">Employee Code</label>
                <input type="text" id="empcode" name="empcode" value="{{ strtoupper(old('empcode')) }}"
                       placeholder="e.g. SB1001" autocomplete="username" required>
            </div>
        @endif

        <button type="submit" class="btn-main">SEND OTP</button>
    </form>

    <div class="links">
        <a href="{{ route('sales_genio.login') }}">Back to Login</a>
        @if($isReset)
            <a href="{{ route('sales_genio.otp.login') }}">Login with OTP</a>
        @else
            <a href="{{ route('sales_genio.password.forgot') }}">Forgot password?</a>
        @endif
    </div>
</div>

<script>
    document.getElementById('mobileForm').addEventListener('submit', function () {
        var btn = this.querySelector('.btn-main');
        btn.disabled = true;
        btn.textContent = 'SENDING…';
    });
</script>
</body>
</html>
