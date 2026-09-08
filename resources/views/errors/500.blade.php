{{--
    Catch-all for any unhandled server error.

    Without this file Laravel falls back to its own bare "500 | SERVER ERROR"
    page, which is what visitors and admins were being shown. Same rule as
    errors/503.blade.php: standalone markup only — whatever broke the request
    may well be the database, and a layout that queries it would fail too.
--}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Service Bazzar — something went wrong</title>
<style>
    :root {
        --bg: #f6f7fb;
        --card: #ffffff;
        --ink: #1c1f2e;
        --muted: #6a7085;
        --brand: #5964ff;
        --line: #e6e8f0;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --bg: #12141d;
            --card: #1a1d29;
            --ink: #eef0f7;
            --muted: #9aa1b8;
            --brand: #7a83ff;
            --line: #2a2e3d;
        }
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: var(--bg);
        color: var(--ink);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                     "Helvetica Neue", Arial, sans-serif;
        line-height: 1.6;
    }

    .card {
        width: 100%;
        max-width: 460px;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 40px 32px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(16, 20, 40, .06);
    }

    .mark {
        width: 56px;
        height: 56px;
        margin: 0 auto 24px;
        border-radius: 14px;
        background: var(--brand);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mark svg { width: 28px; height: 28px; }

    h1 {
        margin: 0 0 12px;
        font-size: 22px;
        font-weight: 650;
        letter-spacing: -.01em;
    }

    p {
        margin: 0 0 8px;
        color: var(--muted);
        font-size: 15px;
    }

    .btn {
        display: inline-block;
        margin-top: 24px;
        padding: 11px 26px;
        border-radius: 9px;
        background: var(--brand);
        color: #fff;
        text-decoration: none;
        font-size: 15px;
        font-weight: 550;
    }

    .btn:hover { opacity: .9; }

    .foot {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--line);
        font-size: 13px;
        color: var(--muted);
    }

    .foot a { color: var(--brand); text-decoration: none; }
</style>
</head>
<body>
    <div class="card">
        <div class="mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 8v5"></path>
                <path d="M12 17h.01"></path>
                <circle cx="12" cy="12" r="9"></circle>
            </svg>
        </div>

        <h1>Something went wrong</h1>

        <p>This one is on us, and it has been logged.</p>
        <p>Thodi der baad dobara try karein.</p>

        <a class="btn" href="{{ url('/') }}">Back to home</a>

        <div class="foot">
            Need help? <a href="mailto:support@servicebazzar.com">support@servicebazzar.com</a>
        </div>
    </div>
</body>
</html>
