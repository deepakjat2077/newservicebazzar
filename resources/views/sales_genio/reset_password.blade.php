<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password – SB Sales</title>
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
        .sub strong { color: #1a1a2e; }

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

        .otp-input { letter-spacing: 8px; font-size: 18px !important; font-weight: 600; text-align: center; }

        .pwd-wrap { position: relative; }
        .pwd-wrap input { padding-right: 64px; }
        .pwd-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: none;
            color: #1a73e8;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            padding: 6px;
        }

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

        .links { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; font-size: 13px; white-space: nowrap; }
        .links a { color: #1a73e8; text-decoration: none; font-weight: 500; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar" class="logo">

    <h2>Set New <span>Password</span></h2>
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

    <form method="POST" action="{{ route('sales_genio.password.reset.save') }}">
        @csrf

        <div class="form-group">
            <label for="otp">OTP</label>
            <input type="text" id="otp" name="otp" class="otp-input" maxlength="6"
                   inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code"
                   placeholder="------" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">New Password</label>
            <div class="pwd-wrap">
                <input type="password" id="password" name="password" minlength="6"
                       autocomplete="new-password" placeholder="At least 6 characters" required>
                <button type="button" class="pwd-toggle" data-target="password">Show</button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <div class="pwd-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="6"
                       autocomplete="new-password" placeholder="Type the password again" required>
                <button type="button" class="pwd-toggle" data-target="password_confirmation">Show</button>
            </div>
        </div>

        <button type="submit" class="btn-main">SAVE NEW PASSWORD</button>
    </form>

    <div class="links">
        <a href="{{ route('sales_genio.login') }}">Back to Login</a>
        <a href="{{ route('sales_genio.password.forgot') }}">Resend OTP</a>
    </div>
</div>

<script>
    document.getElementById('otp').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    document.querySelectorAll('.pwd-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(this.dataset.target);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.textContent = show ? 'Hide' : 'Show';
        });
    });
</script>
</body>
</html>
