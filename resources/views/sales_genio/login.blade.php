<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SB Sales – ServiceBazzar</title>
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

        .login-wrapper {
            display: flex;
            width: 900px;
            max-width: 95vw;
            min-height: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
        }

        /* Left panel */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 36px;
            color: #fff;
            text-align: center;
        }

        .left-panel .brand-logo {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 24px;
        }

        .left-panel .brand-logo span { color: #ffd54f; }

        .left-panel .tagline {
            font-size: 15px;
            font-weight: 300;
            line-height: 1.7;
            opacity: .9;
            max-width: 260px;
        }

        .left-panel .tagline strong {
            display: block;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .left-panel .divider {
            width: 50px;
            height: 2px;
            background: rgba(255,255,255,.4);
            border-radius: 2px;
            margin: 24px auto;
        }

        .left-panel .product-name {
            font-size: 13px;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: .7;
            margin-top: 28px;
        }

        /* Right panel */
        .right-panel {
            width: 380px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 28px 40px;
        }

        .right-panel h2 {
            font-size: 22px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 6px;
            text-align: center;
        }

        .right-panel h2 span { color: #1a73e8; }

        .right-panel .sub-text {
            font-size: 13px;
            color: #888;
            margin-bottom: 18px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 14px;
        }

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
            padding: 10px 14px;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #1a1a2e;
            background: #fafbff;
            transition: border-color .2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: #1a73e8;
            background: #fff;
        }

        .form-group input.is-invalid { border-color: #e53935; }

        .invalid-feedback {
            font-size: 12px;
            color: #e53935;
            margin-top: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 11px;
            background: #1a73e8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            letter-spacing: .5px;
            transition: background .2s, transform .1s;
            margin-top: 8px;
        }

        .btn-login:hover { background: #1557b0; }
        .btn-login:active { transform: scale(.98); }

        .alt-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
            font-size: 13px;
            white-space: nowrap;
        }

        .alt-links a { color: #1a73e8; text-decoration: none; font-weight: 500; }
        .alt-links a:hover { text-decoration: underline; }


        .status-msg {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #2e7d32;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .footer-text {
            text-align: center;
            font-size: 11px;
            color: #aaa;
            margin-top: 14px;
        }

        @media (max-width: 640px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; border-radius: 16px; }
            .login-wrapper { border-radius: 16px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Left -->
    <div class="left-panel">
        <div class="tagline">
            <strong>ONE LOGIN FOR ALL YOUR SALES TOOLS</strong>
            Use your Employee Code and Password to access the Sales Portal.
        </div>
        <div class="divider"></div>
        <div class="product-name">SB Sales</div>
    </div>

    <!-- Right -->
    <div class="right-panel">
        <div style="margin-bottom:14px;text-align:center;">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar" style="max-width:150px;">
        </div>
        <h2>Sign <span>In</span></h2>
        <p class="sub-text">Enter your credentials to continue</p>

        @if (session('status'))
            <div class="status-msg">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div style="background:#fde8e8;border:1px solid #f5c6cb;color:#c62828;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:12px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('sales_genio.login.post') }}">
            @csrf

            <div class="form-group">
                <label>Employee Code</label>
                <input
                    type="text"
                    name="empcode"
                    value="{{ strtoupper(old('empcode')) }}"
                    placeholder="e.g. SB1001"
                    class="{{ $errors->has('empcode') ? 'is-invalid' : '' }}"
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-login">LOGIN</button>
        </form>

        <div class="alt-links">
            <a href="{{ route('sales_genio.otp.login') }}">Login with OTP</a>
            <a href="{{ route('sales_genio.password.forgot') }}">Forgot password?</a>
        </div>

        <p class="footer-text">ServiceBazzar Sales Portal &copy; {{ date('Y') }}</p>
    </div>
</div>

</body>
</html>
