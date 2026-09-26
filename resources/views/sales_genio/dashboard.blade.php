<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Genio Dashboard – ServiceBazzar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
        }

        /* Navbar */
        .navbar {
            background: #fff;
            padding: 0 24px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-left { display: flex; align-items: center; gap: 18px; }
        .navbar-left img { height: 40px; }

        /* City selector pill (like JustDial's Jaipur dropdown) */
        .city-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1.5px solid #d5dae5;
            border-radius: 24px;
            padding: 7px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #1a1a2e;
            cursor: pointer;
            user-select: none;
        }
        .city-selector i.fa-location-dot { color: #1a73e8; font-size: 14px; }
        .city-selector i.fa-chevron-down { font-size: 11px; color: #888; transition: transform .2s; }
        .city-wrap { position: relative; }
        .city-wrap.open .city-selector i.fa-chevron-down { transform: rotate(180deg); }

        /* City dropdown */
        .city-menu {
            position: absolute;
            left: 0;
            top: 48px;
            width: 260px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.15);
            padding: 14px 0 6px;
            display: none;
            z-index: 200;
        }
        .city-menu.open { display: block; }
        .city-menu .menu-title {
            font-size: 12px;
            font-weight: 600;
            color: #888;
            padding: 0 16px 10px;
        }
        .city-search {
            padding: 0 16px 12px;
        }
        .city-search input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }
        .city-search input:focus { border-color: #1a73e8; }
        .city-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .city-list .city-item {
            padding: 9px 16px;
            font-size: 14px;
            color: #1a1a2e;
            cursor: pointer;
            transition: background .15s;
        }
        .city-list .city-item:hover { background: #f5f7fb; }
        .city-list .city-item.active { color: #1a73e8; font-weight: 600; }
        .city-list .no-result { padding: 12px 16px; font-size: 13px; color: #aaa; }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        /* Create New Business button (like JustDial) */
        .btn-create {
            background: #1a73e8;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background .2s;
            white-space: nowrap;
        }
        .btn-create:hover { background: #1557b0; }

        /* Bell */
        .nav-bell {
            position: relative;
            font-size: 19px;
            color: #444;
            cursor: pointer;
        }
        .nav-bell .dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #e53935;
            border-radius: 50%;
            border: 1.5px solid #fff;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: #1a73e8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            flex-shrink: 0;
            cursor: pointer;
            position: relative;
        }

        /* Avatar dropdown */
        .avatar-wrap { position: relative; }
        .avatar-menu {
            position: absolute;
            right: 0;
            top: 52px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.15);
            min-width: 210px;
            padding: 14px 0 6px;
            display: none;
            z-index: 200;
        }
        .avatar-menu.open { display: block; }
        .avatar-menu .head {
            padding: 0 18px 12px;
            border-bottom: 1px solid #eef0f4;
            margin-bottom: 6px;
        }
        .avatar-menu .head .name { font-size: 14px; font-weight: 600; }
        .avatar-menu .head .role { font-size: 12px; color: #888; }
        .avatar-menu .head .code { font-size: 11px; color: #aaa; margin-top: 3px; }
        .avatar-menu button {
            width: 100%;
            text-align: left;
            padding: 10px 18px;
            background: none;
            border: none;
            font-size: 13px;
            font-weight: 500;
            color: #e53935;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .avatar-menu button:hover { background: #fef2f2; }

        /* Main */
        .main {
            padding: 28px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .greeting {
            margin-bottom: 24px;
        }

        .greeting h1 {
            font-size: 22px;
            font-weight: 600;
        }

        .greeting p {
            font-size: 13px;
            color: #888;
            margin-top: 4px;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            position: relative;
            overflow: hidden;
            /* Some cards are links through to their queue. */
            text-decoration: none;
            color: inherit;
            transition: box-shadow .15s, transform .15s;
        }

        a.stat-card:hover {
            box-shadow: 0 6px 18px rgba(16,24,40,.12);
            transform: translateY(-2px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent, #1a73e8);
        }

        .stat-card .label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #888;
        }

        .stat-card .value {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .stat-card .sub {
            font-size: 11px;
            color: #aaa;
        }

        .stat-card .icon {
            position: absolute;
            right: 18px;
            top: 18px;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--accent-light, #e8f0fe);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent, #1a73e8);
            font-size: 15px;
        }

    </style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="main">
    <div class="greeting">
        <h1>Welcome back, {{ explode(' ', $user->name)[0] }}! 👋</h1>
        <p>
            Employee Code: <strong>{{ $user->empcode }}</strong> &bull; {{ now()->format('l, d M Y') }}
            @if(session('sales_city'))
                {{-- The city picker narrows the queues, not the scoreboard. --}}
                &bull; <span style="color:#8a92a6;">showing all cities</span>
            @endif
        </p>
    </div>

    <div class="stats-grid">
        <a class="stat-card" href="{{ route('sales_genio.leads', 'deal-closed') }}" style="--accent:#1a73e8;--accent-light:#e8f0fe;">
            <div class="icon"><i class="fas fa-handshake"></i></div>
            <div class="label">Deal Closed</div>
            <div class="value">{{ $stats['deals'] }}</div>
            <div class="sub">This Month</div>
        </a>
        <div class="stat-card" style="--accent:#43a047;--accent-light:#e8f5e9;">
            <div class="icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="label">Revenue</div>
            <div class="value">₹{{ number_format($stats['revenue']) }}</div>
            <div class="sub">This Month</div>
        </div>
        <a class="stat-card" href="{{ route('sales_genio.leads', 'follow-up') }}" style="--accent:#fb8c00;--accent-light:#fff3e0;">
            <div class="icon"><i class="fas fa-phone-alt"></i></div>
            <div class="label">Follow Ups</div>
            <div class="value">{{ $stats['follow_ups'] }}</div>
            <div class="sub">Pending</div>
        </a>
        <a class="stat-card" href="{{ route('sales_genio.leads', 'new-business') }}" style="--accent:#8e24aa;--accent-light:#f3e5f5;">
            <div class="icon"><i class="fas fa-user-plus"></i></div>
            <div class="label">New Leads</div>
            <div class="value">{{ $stats['new_leads'] }}</div>
            <div class="sub">This Month</div>
        </a>
        <div class="stat-card" style="--accent:#e53935;--accent-light:#fde8e8;">
            <div class="icon"><i class="fas fa-percentage"></i></div>
            <div class="label">Conversion %</div>
            <div class="value">{{ $stats['conversion'] === null ? '—' : $stats['conversion'] . '%' }}</div>
            <div class="sub">
                @if($stats['pitched'])
                    {{ $stats['won'] }} of {{ $stats['pitched'] }} pitched
                @else
                    No business pitched yet
                @endif
            </div>
        </div>
        <div class="stat-card" style="--accent:#00897b;--accent-light:#e0f2f1;">
            <div class="icon"><i class="fas fa-bullseye"></i></div>
            <div class="label">Target</div>
            <div class="value">—</div>
            <div class="sub">No monthly target set</div>
        </div>
    </div>

</div>

<script>
    // Avatar menu
    document.addEventListener('click', function (e) {
        var menu = document.getElementById('avatarMenu');
        var wrap = document.querySelector('.avatar-wrap');
        if (menu && wrap && !wrap.contains(e.target)) {
            menu.classList.remove('open');
        }
        var cWrap = document.getElementById('cityWrap');
        if (cWrap && !cWrap.contains(e.target)) {
            document.getElementById('cityMenu').classList.remove('open');
            cWrap.classList.remove('open');
        }
    });

    // City dropdown
    function toggleCityMenu() {
        var menu = document.getElementById('cityMenu');
        var wrap = document.getElementById('cityWrap');
        var open = menu.classList.toggle('open');
        wrap.classList.toggle('open', open);
        if (open) {
            var s = document.getElementById('citySearch');
            s.value = '';
            filterCities();
            setTimeout(function () { s.focus(); }, 50);
        }
    }

    function filterCities() {
        var q = document.getElementById('citySearch').value.toLowerCase();
        var items = document.querySelectorAll('#cityList .city-item');
        var shown = 0;
        items.forEach(function (it) {
            var match = it.textContent.toLowerCase().indexOf(q) !== -1;
            it.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        var nr = document.getElementById('noResult');
        if (shown === 0) {
            if (!nr) {
                nr = document.createElement('div');
                nr.id = 'noResult';
                nr.className = 'no-result';
                nr.textContent = 'No city found';
                document.getElementById('cityList').appendChild(nr);
            }
        } else if (nr) {
            nr.remove();
        }
    }

    function selectCity(name) {
        document.getElementById('selectedCity').textContent = name;
        document.querySelectorAll('#cityList .city-item').forEach(function (it) {
            it.classList.toggle('active', it.textContent.trim() === name);
        });
        document.getElementById('cityMenu').classList.remove('open');
        document.getElementById('cityWrap').classList.remove('open');
    }
</script>

</body>
</html>
