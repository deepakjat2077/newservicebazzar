<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Categories – {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #1a1a2e; padding-bottom: 90px; }
        .page { max-width: 820px; margin: 0 auto; }

        .topbar {
            display: flex; align-items: center; padding: 16px 18px;
            border-bottom: 1px solid #eef0f4; position: sticky; top: 0; background: #fff; z-index: 100;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; margin-right: 16px; }
        .topbar .title { font-size: 17px; font-weight: 600; flex: 1; text-align: center; margin-right: 34px; }

        .content { padding: 18px; }
        .info-note {
            background: #fff8e6; border-radius: 8px; padding: 14px 16px; margin-bottom: 22px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .info-note i { color: #f5a623; margin-top: 2px; }
        .info-note .t { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
        .info-note .s { font-size: 12px; color: #666; line-height: 1.5; }

        .sec-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
        .sec-head .h { font-size: 15px; font-weight: 600; }
        .sec-head .del-all { font-size: 12px; font-weight: 600; color: #e53935; cursor: pointer; }

        .chips { display: flex; flex-wrap: wrap; gap: 10px; }
        .chip {
            display: inline-flex; align-items: center; gap: 8px;
            border: 1.5px solid #cfe0f5; border-radius: 22px; padding: 8px 14px;
            font-size: 13px; color: #1a1a2e; background: #f7fbff;
        }
        .chip .x { color: #888; cursor: pointer; font-size: 12px; }
        .chip .x:hover { color: #e53935; }
        .chip.add {
            border: 1.5px dashed #1a73e8; color: #1a73e8; background: #fff;
            cursor: pointer; font-weight: 600;
        }
        .empty-sel { font-size: 13px; color: #999; padding: 6px 0; }

        /* Add dropdown */
        .add-wrap { position: relative; display: inline-block; }
        .add-menu {
            position: absolute; left: 0; top: 44px; width: 300px; max-width: 80vw;
            background: #fff; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
            padding: 12px 0 6px; display: none; z-index: 50;
        }
        .add-menu.show { display: block; }
        .add-menu .search { padding: 0 14px 10px; }
        .add-menu .search input {
            width: 100%; padding: 9px 12px; border: 1.5px solid #dde2ec; border-radius: 8px;
            font-size: 13px; font-family: 'Poppins', sans-serif; outline: none;
        }
        .add-menu .list { max-height: 280px; overflow-y: auto; }
        .add-menu .opt { padding: 9px 14px; font-size: 14px; cursor: pointer; }
        .add-menu .opt:hover { background: #f5f7fb; }
        .add-menu .opt.disabled { color: #bbb; cursor: default; }
        .add-menu .none { padding: 12px 14px; font-size: 13px; color: #aaa; }

        /* Main category searchable */
        .main-cat-wrap { position: relative; margin-bottom: 24px; }
        .mc-label { font-size: 11px; color: #e65100; display: block; margin-bottom: 6px; }
        .mc-field {
            display: flex; align-items: center; border: 1.5px solid #d5dae5;
            border-radius: 8px; padding: 10px 14px; cursor: text;
        }
        .mc-field input {
            border: none; outline: none; flex: 1; font-size: 15px;
            font-family: 'Poppins', sans-serif; color: #1a1a2e; background: transparent;
        }
        .mc-chev { color: #888; font-size: 12px; }
        .mc-clear { color: #e53935; font-size: 14px; cursor: pointer; margin-right: 12px; }
        .mc-menu {
            position: absolute; left: 0; right: 0; top: calc(100% + 4px);
            background: #fff; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
            max-height: 300px; overflow-y: auto; padding: 6px 0; display: none; z-index: 60;
        }
        .mc-menu.show { display: block; }
        .mc-menu .mc-opt { padding: 10px 16px; font-size: 14px; cursor: pointer; }
        .mc-menu .mc-opt:hover { background: #f5f7fb; }
        .mc-menu .mc-opt.active { color: #1a73e8; font-weight: 600; }
        .mc-menu .mc-none { padding: 12px 16px; font-size: 13px; color: #aaa; }

        .add-new-btn {
            margin-top: 20px; background: #1a73e8; color: #fff; border: none;
            border-radius: 22px; padding: 10px 20px; font-size: 14px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
        }
        .add-new-btn:hover { background: #1557b0; }

        /* Full-screen Add Categories overlay */
        .add-overlay {
            position: fixed; inset: 0; background: #fff; z-index: 500;
            display: none; flex-direction: column;
        }
        .add-overlay.show { display: flex; }
        .ao-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 18px; border-bottom: 1px solid #eef0f4;
        }
        .ao-title { font-size: 16px; font-weight: 600; color: #1a73e8; }
        .ao-close { background: none; border: none; font-size: 20px; color: #444; cursor: pointer; }
        .ao-search {
            display: flex; align-items: center; gap: 10px; margin: 14px 18px;
            border: 1.5px solid #d5dae5; border-radius: 10px; padding: 12px 14px;
        }
        .ao-search i { color: #1a73e8; }
        .ao-search .ao-clear { color: #aaa; cursor: pointer; }
        .ao-search input {
            border: none; outline: none; flex: 1; font-size: 15px; font-family: 'Poppins', sans-serif;
        }
        .ao-list { flex: 1; overflow-y: auto; padding: 0 18px; }
        .ao-list .opt {
            padding: 15px 4px; font-size: 15px; cursor: pointer; border-bottom: 1px solid #f2f4f8;
            display: flex; align-items: center; justify-content: space-between;
        }
        .ao-list .opt:hover { background: #fafbff; }
        .ao-list .opt.added { color: #1a73e8; font-weight: 500; }
        .ao-list .opt .check { color: #1a73e8; font-size: 15px; }
        .ao-list .opt .plus { color: #bbb; font-size: 14px; }
        .ao-list .none { padding: 20px 4px; font-size: 14px; color: #aaa; }
        .ao-done { padding: 12px 18px; border-top: 1px solid #eef0f4; }
        .btn-done {
            width: 100%; background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 13px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer;
        }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1);
        }
        .btn-save {
            display: block; width: 100%; max-width: 820px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-save:hover { background: #1557b0; }
        .note-bottom { text-align: center; font-size: 12px; color: #999; margin-top: 8px; }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ sg_route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
        <div class="title">Add Business Categories</div>
    </div>

    <form method="POST" action="{{ sg_route('sales_genio.business.categories.update', $business->id) }}" id="catForm">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <div class="t">Add as many relevant categories as you can</div>
                    <div class="s">The more relevant categories you add, the more customer searches your business will appear in. Only add categories that truly describe your business.</div>
                </div>
            </div>

            {{-- Main category (searchable) --}}
            @php $curCat = $categories->firstWhere('id', $business->category_id); @endphp
            <div class="main-cat-wrap" id="mainCatWrap">
                <label class="mc-label">Main Business Category <span style="color:#999;font-weight:400;">(required to go live)</span></label>
                <div class="mc-field" onclick="openMainCat(event)">
                    <input type="text" id="mainCatInput" placeholder="Search &amp; select a category" autocomplete="off" value="{{ $curCat->name ?? '' }}" onkeyup="renderMainCats()" onclick="event.stopPropagation(); openMainCat(event)">
                    <i class="fas fa-xmark mc-clear" id="mcClear" onclick="clearMainCat(event)" style="{{ $curCat ? '' : 'display:none;' }}"></i>
                    <i class="fas fa-chevron-down mc-chev"></i>
                </div>
                <input type="hidden" name="category_id" id="mainCategory" value="{{ $business->category_id }}">
                <div class="mc-menu" id="mainCatMenu"></div>
            </div>

            <div class="sec-head">
                <div class="h">Selected Categories <span style="font-size:12px;color:#999;font-weight:400;">(keywords)</span></div>
                <div class="del-all" onclick="deleteAll()">DELETE ALL</div>
            </div>

            <div class="chips" id="chips">
                <div class="empty-sel" id="emptySel" style="display:none;">No categories selected yet. Tap "+ Add New Category".</div>
                <!-- selected chips injected here -->
            </div>

            <button type="button" class="add-new-btn" onclick="openAddOverlay()">
                <i class="fas fa-plus"></i> Add New Category
            </button>

            <div id="hiddenInputs"></div>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save and Proceed <i class="fas fa-arrow-right"></i></button>
            <div class="note-bottom">Updates may take a little time to go live.</div>
        </div>
    </form>
</div>

{{-- Full-screen Add Categories overlay (JustDial-style) --}}
<div class="add-overlay" id="addOverlay">
    <div class="ao-head">
        <div class="ao-title">Add Categories</div>
        <button type="button" class="ao-close" onclick="closeAddOverlay()"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="ao-search">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="catSearch" placeholder="Search category" onkeyup="renderOptions()">
        <i class="fas fa-xmark ao-clear" onclick="clearSearch()"></i>
    </div>
    <div class="ao-list" id="optList"></div>
    <div class="ao-done">
        <button type="button" class="btn-done" onclick="closeAddOverlay()">Done <span id="doneCount"></span></button>
    </div>
</div>

<script>
    let available = @json($available->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());
    const subcatUrl = '{{ sg_route("sales_genio.subcategories.by.category") }}';
    const allCategories = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());

    // ── Main category searchable dropdown ──
    function openMainCat(e) {
        e.stopPropagation();
        document.getElementById('mainCatMenu').classList.add('show');
        renderMainCats();
    }
    function renderMainCats() {
        const q = document.getElementById('mainCatInput').value.trim().toLowerCase();
        const cur = document.getElementById('mainCategory').value;
        const list = allCategories.filter(c => c.name.toLowerCase().includes(q));
        const menu = document.getElementById('mainCatMenu');
        if (!list.length) { menu.innerHTML = '<div class="mc-none">No category found</div>'; return; }
        menu.innerHTML = list.map(c =>
            '<div class="mc-opt ' + (String(c.id) === String(cur) ? 'active' : '') + '" data-id="' + c.id + '" data-name="' + c.name.replace(/"/g,'&quot;') + '">' + c.name + '</div>'
        ).join('');
        menu.querySelectorAll('.mc-opt').forEach(op => {
            op.addEventListener('click', () => pickMainCat(op.dataset.id, op.dataset.name));
        });
    }
    function pickMainCat(id, name) {
        document.getElementById('mainCatInput').value = name;
        document.getElementById('mainCategory').value = id;
        document.getElementById('mainCatMenu').classList.remove('show');
        document.getElementById('mcClear').style.display = '';
        loadKeywords(id);
    }

    function clearMainCat(e) {
        e.stopPropagation();
        document.getElementById('mainCatInput').value = '';
        document.getElementById('mainCategory').value = '';
        document.getElementById('mcClear').style.display = 'none';
        document.getElementById('mainCatMenu').classList.remove('show');
        selected = [];
        available = [];
        renderChips();
        renderOptions();
    }
    function loadKeywords(catId) {
        selected = []; // keywords belong to the category, reset on change
        renderChips();
        if (!catId) { available = []; renderOptions(); return; }
        fetch(subcatUrl + '?category_id=' + catId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { available = data; renderOptions(); });
    }
    const selectedIds = @json(array_map('intval', $selectedIds));
    let selected = available.filter(a => selectedIds.includes(a.id));
    // include any selected ids not in available (e.g. from another category), by name fallback
    @foreach($business->business_categories ?? [] as $bcName)
        if (!selected.some(s => s.name === @json($bcName))) {
            // orphan name without a matching available id — keep as name-only chip
            selected.push({ id: null, name: @json($bcName) });
        }
    @endforeach

    function renderChips() {
        const wrap = document.getElementById('chips');
        wrap.querySelectorAll('.chip.sel').forEach(c => c.remove());
        const addChipEl = wrap.querySelector('.chip.add');
        selected.forEach((s, i) => {
            const chip = document.createElement('div');
            chip.className = 'chip sel';
            chip.innerHTML = s.name + ' <span class="x" onclick="removeChip(' + i + ')">✕</span>';
            wrap.insertBefore(chip, addChipEl);
        });
        document.getElementById('emptySel').style.display = selected.length ? 'none' : 'block';
        renderHidden();
        const dc = document.getElementById('doneCount');
        if (dc) dc.textContent = selected.length ? '(' + selected.length + ')' : '';
    }

    function renderHidden() {
        const h = document.getElementById('hiddenInputs');
        h.innerHTML = selected.filter(s => s.id).map(s => '<input type="hidden" name="subcategory_ids[]" value="' + s.id + '">').join('');
    }

    function removeChip(i) { selected.splice(i, 1); renderChips(); }
    function deleteAll() { selected = []; renderChips(); }

    // ── Full-screen Add overlay ──
    function openAddOverlay() {
        if (!document.getElementById('mainCategory').value) {
            alert('Please select a Main Business Category first.');
            return;
        }
        document.getElementById('addOverlay').classList.add('show');
        document.getElementById('catSearch').value = '';
        renderOptions();
        setTimeout(() => document.getElementById('catSearch').focus(), 50);
    }
    function closeAddOverlay() { document.getElementById('addOverlay').classList.remove('show'); }
    function clearSearch() { document.getElementById('catSearch').value = ''; renderOptions(); document.getElementById('catSearch').focus(); }

    function renderOptions() {
        const q = document.getElementById('catSearch').value.trim().toLowerCase();
        const selIds = selected.map(s => s.id);
        const list = available.filter(a => a.name.toLowerCase().includes(q));
        const el = document.getElementById('optList');
        if (!list.length) { el.innerHTML = '<div class="none">No category found</div>'; return; }
        el.innerHTML = list.map(a => {
            const added = selIds.includes(a.id);
            return '<div class="opt ' + (added ? 'added' : '') + '" onclick="toggleOption(' + a.id + ')">'
                + '<span>' + a.name + '</span>'
                + (added ? '<i class="fas fa-circle-check check"></i>' : '<i class="fas fa-plus plus"></i>')
                + '</div>';
        }).join('');
    }

    // Tap toggles add/remove; overlay stays open for fast multi-select.
    function toggleOption(id) {
        const item = available.find(a => a.id === id);
        if (!item) return;
        const idx = selected.findIndex(s => s.id === id);
        if (idx === -1) selected.push(item);
        else selected.splice(idx, 1);
        renderChips();
        renderOptions();
    }

    document.addEventListener('click', function (e) {
        const mcWrap = document.getElementById('mainCatWrap');
        if (mcWrap && !mcWrap.contains(e.target)) document.getElementById('mainCatMenu').classList.remove('show');
    });

    // Typing a fresh query clears the prior selection until a real pick is made.
    document.getElementById('mainCatInput').addEventListener('input', function () {
        const typed = this.value.trim().toLowerCase();
        const match = allCategories.find(c => c.name.toLowerCase() === typed);
        document.getElementById('mainCategory').value = match ? match.id : '';
    });

    renderChips();
</script>
</body>
</html>
