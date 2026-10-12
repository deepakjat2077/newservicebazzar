<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create New Business – SB Sales</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: #1a1a2e; }

        .navbar {
            background: #fff; padding: 0 24px; height: 56px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,.12); position: sticky; top: 0; z-index: 100;
        }
        .navbar img { height: 40px; }
        .navbar .back {
            font-size: 13px; color: #1a73e8; text-decoration: none; font-weight: 500;
            display: flex; align-items: center; gap: 6px;
        }

        .wrap { max-width: 900px; margin: 28px auto; padding: 0 20px; }
        .page-title { font-size: 22px; font-weight: 600; margin-bottom: 18px; }

        .form-card { background: #fff; border-radius: 14px; padding: 36px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }

        .alert-success {
            background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32;
            padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 22px;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-error {
            background: #fde8e8; border: 1px solid #f5c6cb; color: #c62828;
            padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 22px;
        }
        .alert-error ul { margin: 0; padding-left: 18px; }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px 28px; }

        .field { display: flex; flex-direction: column; position: relative; }
        .field label {
            font-size: 12px; font-weight: 500; color: #1a73e8; margin-bottom: 6px;
        }
        .field label .req { color: #e53935; }
        .field input {
            border: none; border-bottom: 1.5px solid #e0e4ec; padding: 8px 2px;
            font-size: 15px; font-family: 'Poppins', sans-serif; color: #1a1a2e;
            outline: none; background: transparent; transition: border-color .2s;
        }
        .field input:focus { border-bottom-color: #1a73e8; }

        /* Area autocomplete */
        .area-suggestions {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #fff; border: 1px solid #e0e4ec; border-radius: 0 0 8px 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12); max-height: 260px; overflow-y: auto;
            z-index: 50; display: none;
        }
        .area-suggestions.show { display: block; }
        .area-suggestions .item {
            padding: 10px 14px; font-size: 14px; cursor: pointer; border-bottom: 1px solid #f2f4f8;
        }
        .area-suggestions .item:hover, .area-suggestions .item.hl { background: #f5f7fb; }
        .area-suggestions .item .pin { font-size: 12px; color: #999; margin-left: 6px; }
        .area-suggestions .item .city { font-size: 12px; color: #1a73e8; font-weight: 600; margin-left: 8px; }
        #areaCityNote { display: none; font-size: 12px; color: #1a73e8; margin-top: 5px; font-weight: 600; }
        .area-suggestions .loading, .area-suggestions .empty {
            padding: 12px 14px; font-size: 13px; color: #aaa;
        }

        .actions { text-align: center; margin-top: 34px; }
        .btn-proceed {
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 13px 60px; font-size: 15px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; transition: background .2s;
        }
        .btn-proceed:hover { background: #1557b0; }

        .nav-title { display: none; }

        /* Phone: app-style form — back arrow + centred title, boxed fields,
           Area/Pincode side by side, Proceed pinned to the bottom. */
        @media (max-width: 640px) {
            body { background: #fff; }
            .navbar { padding: 0 16px; height: 60px; box-shadow: none; border-bottom: 1px solid #eef0f4; justify-content: flex-start; }
            .navbar img, .navbar .back span, .page-title { display: none; }
            .navbar .back { color: #1a1a2e; font-size: 22px; padding: 6px 8px 6px 0; }
            .nav-title { display: block; flex: 1; text-align: center; font-size: 18px; font-weight: 500; color: #1a1a2e; margin-right: 30px; }

            .wrap { margin: 0; padding: 0; }
            .form-card { border-radius: 0; box-shadow: none; padding: 20px 16px 110px; }
            .grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 12px 8px; }
            .grid > .field { grid-column: 1 / -1 !important; }
            .grid > .field.half { grid-column: auto !important; }

            .grid > .field.m-hide, .field .req { display: none; }
            .field { min-width: 0; border: 1px solid #dfe3ec; border-radius: 4px; padding: 12px 14px 6px; transition: border-color .2s; }
            .field input { width: 100%; min-width: 0; }
            .field:focus-within { border-color: #1a73e8; }
            .field label { font-size: 15px; color: #6b7285; font-weight: 400; margin-bottom: 2px; }
            .field.half label { font-size: 14px; }
            .field label span[style] { display: block; font-size: 12px; }
            .field input { border-bottom: 0; padding: 6px 0; font-size: 16px; }
            .field input[readonly] { background: transparent !important; }
            .area-suggestions { left: -1px; right: -1px; }

            .actions { position: fixed; left: 0; right: 0; bottom: 0; margin: 0; padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
                background: #fff; border-top: 1px solid #eef0f4; z-index: 60; }
            .btn-proceed { width: 100%; padding: 14px; font-size: 18px; font-weight: 500; border-radius: 6px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar">
    <a href="{{ route('sales_genio.dashboard') }}" class="back" aria-label="Back to Dashboard"><i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span></a>
    <span class="nav-title">Create New Business</span>
</nav>

<div class="wrap">
    <div class="page-title">Create New Business</div>

    <div class="form-card">
        @if(session('success'))
            <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('sales_genio.create.business.check') }}" id="bizForm">
            @csrf
            <div class="grid">
                <div class="field" style="grid-column:1 / -1;">
                    <label>Business Category <span style="color:#999;font-weight:400;">(optional — add later to go live)</span></label>
                    <input type="text" id="catInput" placeholder="Search &amp; select a category" autocomplete="off" value="{{ $categories->firstWhere('id', old('category_id'))->name ?? '' }}">
                    <input type="hidden" name="category_id" id="catId" value="{{ old('category_id') }}">
                    <div class="area-suggestions" id="catSuggestions"></div>
                </div>

                <div class="field">
                    <label>Company Name <span class="req">*</span></label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" required>
                </div>
                <div class="field">
                    <label>Contact Person Name</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}">
                </div>

                <div class="field">
                    <label>Mobile Number <span class="req">*</span></label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="10" inputmode="numeric" required>
                </div>
                <div class="field m-hide">
                    <label>Confirm Mobile Number <span class="req">*</span></label>
                    <input type="text" name="mobile_confirm" value="{{ old('mobile_confirm') }}" maxlength="10" inputmode="numeric" required onpaste="return false;">
                </div>

                <div class="field">
                    <label>Landline Number</label>
                    <input type="text" name="landline" value="{{ old('landline') }}" maxlength="15">
                </div>
                <div class="field m-hide">
                    <label>Confirm Landline Number</label>
                    <input type="text" name="landline_confirm" value="{{ old('landline_confirm') }}" maxlength="15" onpaste="return false;">
                </div>

                <div class="field">
                    <label>Email ID</label>
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="field m-hide">
                    <label>Confirm Email ID</label>
                    <input type="email" name="email_confirm" value="{{ old('email_confirm') }}" onpaste="return false;">
                </div>

                <div class="field">
                    <label>Website</label>
                    <input type="text" name="website" value="{{ old('website', 'www.') }}">
                </div>
                <div class="field m-hide">
                    <label>Confirm Website</label>
                    <input type="text" name="website_confirm" value="{{ old('website_confirm', 'www.') }}">
                </div>

                <div class="field half">
                    <label>Enter Area <span class="req">*</span></label>
                    <input type="text" name="area" id="areaInput" value="{{ old('area') }}" autocomplete="off" required>
                    <input type="hidden" name="city" id="cityInput" value="{{ old('city') }}">
                    <div id="areaCityNote">@if(old('city'))City: {{ old('city') }}@endif</div>
                    <div class="area-suggestions" id="areaSuggestions"></div>
                </div>
                <div class="field half">
                    <label>Enter Pincode <span class="req">*</span></label>
                    <input type="text" name="pincode" id="pincodeInput" value="{{ old('pincode') }}" maxlength="6" inputmode="numeric" required readonly placeholder="Auto-fills from Area" style="background:#f7f8fb;cursor:not-allowed;">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-proceed">Proceed</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ── Category searchable dropdown ───────────────────────────────
    const categories = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());
    const catInput = document.getElementById('catInput');
    const catId    = document.getElementById('catId');
    const catBox   = document.getElementById('catSuggestions');

    catInput.addEventListener('input', function () {
        catId.value = ''; // typing invalidates prior selection
        renderCats(catInput.value.trim().toLowerCase());
    });
    catInput.addEventListener('focus', function () {
        renderCats(catInput.value.trim().toLowerCase());
    });

    function renderCats(q) {
        const list = categories.filter(c => c.name.toLowerCase().includes(q)).slice(0, 30);
        if (!list.length) { catBox.innerHTML = '<div class="empty">No category found</div>'; catBox.classList.add('show'); return; }
        catBox.innerHTML = list.map(c => '<div class="item" data-id="' + c.id + '" data-name="' + c.name.replace(/"/g,'&quot;') + '">' + c.name + '</div>').join('');
        catBox.classList.add('show');
        catBox.querySelectorAll('.item').forEach(it => {
            it.addEventListener('click', () => {
                catInput.value = it.dataset.name;
                catId.value = it.dataset.id;
                catBox.classList.remove('show');
            });
        });
    }

    document.addEventListener('click', function (e) {
        if (!catInput.contains(e.target) && !catBox.contains(e.target)) catBox.classList.remove('show');
    });

    // Category is optional — but if the user typed something that isn't a
    // real selection, clear it so we don't send a stray value.
    document.getElementById('bizForm').addEventListener('submit', function () {
        if (!catId.value) catInput.value = '';
    });

    // Digits-only for mobile
    document.querySelectorAll('input[name="mobile"], input[name="mobile_confirm"]').forEach(function (el) {
        el.addEventListener('input', function () { el.value = el.value.replace(/\D/g, ''); });
    });

    // Website "www." prefix keeper
    document.querySelectorAll('input[name="website"], input[name="website_confirm"]').forEach(function (el) {
        el.addEventListener('focus', function () { if (!el.value) el.value = 'www.'; });
    });

    // ── Area autocomplete ──────────────────────────────────────────
    const areaInput = document.getElementById('areaInput');
    const pincodeInput = document.getElementById('pincodeInput');
    const box = document.getElementById('areaSuggestions');
    let areaSelected = false;
    let debounce, hlIndex = -1, currentItems = [];

    areaInput.addEventListener('input', function () {
        areaSelected = false;
        pincodeInput.value = '';
        document.getElementById('cityInput').value = '';
        const q = areaInput.value.trim();
        clearTimeout(debounce);
        if (q.length < 2) { hideBox(); return; }
        box.innerHTML = '<div class="loading">Searching...</div>';
        box.classList.add('show');
        debounce = setTimeout(() => fetchAreas(q), 250);
    });

    function fetchAreas(q) {
        fetch('{{ route("sales_genio.area.search") }}?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            currentItems = data; hlIndex = -1;
            if (!data.length) { box.innerHTML = '<div class="empty">No area found</div>'; return; }
            box.innerHTML = data.map((d, i) =>
                '<div class="item" data-i="' + i + '">' + d.area
                  + '<span class="city">' + (d.city || '') + '</span>'
                  + '<span class="pin">' + d.pincode + '</span></div>'
            ).join('');
            box.querySelectorAll('.item').forEach(it => {
                it.addEventListener('click', () => pick(currentItems[it.dataset.i]));
            });
        })
        .catch(() => { box.innerHTML = '<div class="empty">Search failed</div>'; });
    }

    function pick(item) {
        areaInput.value = item.area;
        pincodeInput.value = item.pincode;
        document.getElementById('cityInput').value = item.city || '';
        areaSelected = true;
        showCityNote(item.city);
        hideBox();
    }

    // The chosen area decides the city, so say plainly which city it landed in.
    function showCityNote(city) {
        const note = document.getElementById('areaCityNote');
        if (!note) return;
        if (city) {
            note.textContent = 'City: ' + city;
            note.style.display = 'block';
        } else {
            note.style.display = 'none';
        }
    }

    function hideBox() { box.classList.remove('show'); box.innerHTML = ''; }

    // Keyboard nav
    areaInput.addEventListener('keydown', function (e) {
        const items = box.querySelectorAll('.item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); hlIndex = Math.min(hlIndex + 1, items.length - 1); highlight(items); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); hlIndex = Math.max(hlIndex - 1, 0); highlight(items); }
        else if (e.key === 'Enter') { if (hlIndex >= 0) { e.preventDefault(); pick(currentItems[hlIndex]); } }
        else if (e.key === 'Escape') { hideBox(); }
    });
    function highlight(items) {
        items.forEach((it, i) => it.classList.toggle('hl', i === hlIndex));
    }

    // Rule: pincode manually typed but no area selected → clear pincode on blur
    pincodeInput.addEventListener('blur', function () {
        if (pincodeInput.value && areaInput.value.trim() === '') {
            pincodeInput.value = '';
        }
    });
    // If area is cleared, clear pincode + city too
    areaInput.addEventListener('blur', function () {
        setTimeout(() => {
            if (areaInput.value.trim() === '') {
                pincodeInput.value = '';
                document.getElementById('cityInput').value = '';
                showCityNote('');
            }
        }, 200);
    });

    // Close suggestions on outside click
    document.addEventListener('click', function (e) {
        if (!areaInput.contains(e.target) && !box.contains(e.target)) hideBox();
    });
</script>
<script>
    // Phones hide the "Confirm ..." boxes; fill them from the main field so
    // the server's match check still passes.
    (function () {
        if (!window.matchMedia('(max-width: 640px)').matches) return;
        var form = document.getElementById('bizForm');
        [['mobile', 'mobile_confirm'], ['landline', 'landline_confirm'],
         ['email', 'email_confirm'], ['website', 'website_confirm']].forEach(function (pair) {
            var src = form.querySelector('[name="' + pair[0] + '"]');
            var dst = form.querySelector('[name="' + pair[1] + '"]');
            if (!src || !dst) return;
            dst.removeAttribute('required');
            var sync = function () { dst.value = src.value; };
            sync();
            src.addEventListener('input', sync);
            form.addEventListener('submit', sync);
        });
    })();
</script>
</body>
</html>
