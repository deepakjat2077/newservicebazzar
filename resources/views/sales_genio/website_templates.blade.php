<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Choose a Design — SB Sales</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Poppins',sans-serif;background:#f3f5fa;color:#1a2235}
  button{font-family:inherit}

  .wt-wrap{max-width:1180px;margin:0 auto;padding:12px 20px 130px}
  .wt-head h1{font-size:26px;font-weight:700;color:#12203f;line-height:1.25}
  .wt-head p{margin-top:6px;font-size:14.5px;color:#5a6478;max-width:640px}
  .wt-plan{display:inline-flex;align-items:center;gap:8px;margin-top:14px;background:#eaf2fe;color:#1a5fc4;border-radius:20px;padding:7px 16px;font-size:13px;font-weight:600}
  .wt-error{margin-top:14px;background:#fde8e8;border:1px solid #f5c6cb;color:#c62828;padding:11px 14px;border-radius:10px;font-size:13.5px}

  .wt-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px;margin-top:26px}
  .wt-card{position:relative;background:#fff;border:2px solid #e3e7ef;border-radius:16px;padding:14px;transition:border-color .15s, box-shadow .15s}
  .wt-card.is-on{border-color:#1a73e8;box-shadow:0 10px 30px rgba(26,115,232,.18)}
  .wt-badge{position:absolute;top:-12px;right:16px;z-index:3;display:none;align-items:center;gap:6px;background:#1a73e8;color:#fff;font-size:12px;font-weight:600;padding:5px 12px;border-radius:20px}
  .wt-card.is-on .wt-badge{display:inline-flex}

  /* The page is drawn 1200px wide and shrunk to the card; the card scrolls through the whole page. */
  .wt-frame{height:540px;overflow-y:auto;overflow-x:hidden;border:1px solid #e3e7ef;border-radius:10px;background:#fff;scrollbar-width:thin}
  .wt-scale{position:relative;width:100%}
  .wt-scale iframe{position:absolute;left:0;top:0;width:1200px;border:0;transform-origin:0 0;pointer-events:none;background:#fff}
  .wt-hint{margin-top:8px;font-size:11.5px;color:#8a92a6;text-align:center}

  .wt-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px}
  .wt-meta b{display:block;font-size:17px;color:#12203f}
  .wt-meta span{font-size:12.5px;color:#6b7285;line-height:1.35}
  .wt-btns{display:flex;gap:8px;flex-shrink:0}
  .wt-btn{border-radius:9px;padding:9px 14px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #1a73e8;background:#fff;color:#1a73e8}
  .wt-btn:hover{background:#f2f7fe}
  .wt-btn.pri{background:#1a73e8;color:#fff}
  .wt-btn.pri:hover{background:#1557b0}
  .wt-card.is-on .wt-btn.pri{background:#1f9d55;border-color:#1f9d55}

  .wt-bar{position:fixed;left:0;right:0;bottom:0;z-index:60;background:#fff;border-top:1px solid #e3e7ef;box-shadow:0 -4px 16px rgba(16,24,40,.08)}
  .wt-bar-in{max-width:1180px;margin:0 auto;padding:14px 20px calc(14px + env(safe-area-inset-bottom));display:flex;align-items:center;justify-content:space-between;gap:16px}
  .wt-sel{font-size:14.5px;color:#5a6478}
  .wt-sel b{color:#12203f}
  .wt-go{background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:13px 36px;font-size:15px;font-weight:600;cursor:pointer}
  .wt-go:disabled{background:#c5cde0;cursor:not-allowed}

  /* full-size preview */
  .wt-modal{position:fixed;inset:0;z-index:200;background:rgba(15,23,42,.62);display:none;align-items:center;justify-content:center;padding:18px}
  .wt-modal.open{display:flex}
  .wt-panel{background:#fff;border-radius:14px;width:100%;max-width:1240px;height:100%;max-height:920px;display:flex;flex-direction:column;overflow:hidden}
  .wt-ph{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border-bottom:1px solid #eef0f6;flex-wrap:wrap}
  .wt-ph b{font-size:16px;color:#12203f}
  .wt-ph .grp{display:flex;gap:8px;align-items:center}
  .wt-dev{display:inline-flex;border:1px solid #d5dae5;border-radius:9px;overflow:hidden}
  .wt-dev button{border:0;background:#fff;padding:8px 13px;font-size:13px;color:#5a6478;cursor:pointer}
  .wt-dev button.on{background:#eaf2fe;color:#1a5fc4;font-weight:600}
  .wt-x{border:0;background:#f1f3f8;width:34px;height:34px;border-radius:50%;font-size:19px;color:#62708c;cursor:pointer}
  .wt-stage{flex:1;background:#e9edf5;display:flex;justify-content:center;overflow:auto}
  .wt-stage iframe{border:0;background:#fff;width:100%;height:100%;transition:width .2s}
  .wt-stage.phone iframe{width:390px;max-width:100%;box-shadow:0 0 0 1px #d5dae5}

  @media (max-width:1000px){ .wt-grid{grid-template-columns:repeat(2,minmax(0,1fr))} }
  @media (max-width:760px){
    body{background:#fff}
    .wt-wrap{padding:6px 16px 120px}
    .wt-head h1{font-size:20px}
    /* the designs sit side by side and swipe */
    .wt-grid{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;gap:14px;margin:20px -16px 0;padding:14px 16px 6px}
    .wt-card{flex:0 0 84%;scroll-snap-align:center}
    .wt-frame{height:460px}
    .wt-meta{flex-direction:column;align-items:stretch;gap:12px}
    .wt-btns{width:100%}
    .wt-btn{flex:1;padding:11px 14px;font-size:14px}
    .wt-bar-in{padding:12px 16px calc(12px + env(safe-area-inset-bottom))}
    .wt-sel{font-size:13px;line-height:1.3}
    .wt-go{padding:13px 22px;font-size:15px}
    .wt-modal{padding:0}
    .wt-panel{border-radius:0;max-height:none}
    .wt-dev{display:none}
  }
</style>
</head>
<body>

@include('sales_genio.partials.campaign_mhead', [
  'back'  => route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]),
  'title' => 'Choose Your Design',
])
@include('sales_genio.partials.flow_toprow', [
  'back' => route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]),
  'backLabel' => 'Back to plan', 'maxWidth' => 1180, 'withCart' => true,
])

<div class="wt-wrap">
    <div class="wt-head">
        <h1>Choose a website design for {{ $business->name }}</h1>
        <p>Each design shows the full home page, filled in with this business's own name, photos and details. Scroll inside a design to see all of it.</p>
        <div class="wt-plan"><i class="fas fa-globe"></i> {{ $package->name }} · {{ $choice['label'] }} · ₹{{ numberFormat($choice['price']) }}</div>
        @if(session('error'))<div class="wt-error">{{ session('error') }}</div>@endif
    </div>

    <form method="post" action="{{ route('sales_genio.business.campaign.cart', $business->id) }}" id="wtForm">
        @csrf
        <input type="hidden" name="package_id" value="{{ $package->id }}">
        <input type="hidden" name="plan" value="{{ $plan }}">
        <input type="hidden" name="template" id="wtValue" value="{{ $selected }}">

        <div class="wt-grid">
            @foreach($templates as $key => $tpl)
                <article class="wt-card {{ $selected === $key ? 'is-on' : '' }}" data-key="{{ $key }}" data-name="{{ $tpl['name'] }}"
                         data-url="{{ route('sales_genio.business.website.preview', ['id' => $business->id, 'template' => $key]) }}">
                    <span class="wt-badge"><i class="fas fa-check"></i> Selected</span>
                    <div class="wt-frame"><div class="wt-scale"><iframe tabindex="-1" loading="lazy" scrolling="no" title="{{ $tpl['name'] }} design"></iframe></div></div>
                    <div class="wt-hint">Scroll to see the full home page</div>
                    <div class="wt-meta">
                        <div><b>{{ $tpl['name'] }}</b><span>{{ $tpl['tagline'] }}</span></div>
                        <div class="wt-btns">
                            <button type="button" class="wt-btn" data-act="preview">Preview</button>
                            <button type="button" class="wt-btn pri" data-act="select">Select</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="wt-bar">
            <div class="wt-bar-in">
                <div class="wt-sel" id="wtSel">Pick a design to continue</div>
                <button type="submit" class="wt-go" id="wtGo" disabled>Add to Cart</button>
            </div>
        </div>
    </form>
</div>

<div class="wt-modal" id="wtModal" role="dialog" aria-modal="true" aria-label="Design preview">
    <div class="wt-panel">
        <div class="wt-ph">
            <b id="wtModalName"></b>
            <div class="grp">
                <div class="wt-dev"><button type="button" class="on" data-dev="desktop"><i class="fas fa-desktop"></i> Desktop</button><button type="button" data-dev="phone"><i class="fas fa-mobile-screen"></i> Phone</button></div>
                <button type="button" class="wt-btn pri" id="wtModalPick">Select this design</button>
                <button type="button" class="wt-x" id="wtModalClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="wt-stage" id="wtStage"><iframe id="wtModalFrame" title="Full design preview"></iframe></div>
    </div>
</div>

<script>
(function () {
    // A phone shows each design's own phone layout; a larger screen shows the desktop page.
    var DESIGN_W = window.matchMedia('(max-width: 760px)').matches ? 400 : 1200;
    var cards = Array.prototype.slice.call(document.querySelectorAll('.wt-card'));
    var value = document.getElementById('wtValue'), sel = document.getElementById('wtSel'), go = document.getElementById('wtGo');
    var modal = document.getElementById('wtModal'), stage = document.getElementById('wtStage'), mframe = document.getElementById('wtModalFrame');
    var current = null;

    // ---- shrink each full-width page into its card ----
    function fit(card) {
        var scale = card.querySelector('.wt-scale'), frame = card.querySelector('iframe');
        var k = scale.clientWidth / DESIGN_W;
        var h = Number(frame.dataset.h || 4800);
        frame.style.width = DESIGN_W + 'px';
        frame.style.height = h + 'px';
        frame.style.transform = 'scale(' + k + ')';
        scale.style.height = Math.round(h * k) + 'px';
    }
    cards.forEach(function (card) {
        var frame = card.querySelector('iframe');
        frame.addEventListener('load', function () {
            try { frame.dataset.h = frame.contentDocument.documentElement.scrollHeight; } catch (e) {}
            fit(card);
        });
        frame.style.width = DESIGN_W + 'px';
        frame.src = card.dataset.url;
        fit(card);
    });
    window.addEventListener('resize', function () { cards.forEach(fit); });

    // ---- choosing ----
    function choose(key) {
        value.value = key || '';
        var name = '';
        cards.forEach(function (c) {
            var on = c.dataset.key === key;
            c.classList.toggle('is-on', on);
            c.querySelector('[data-act="select"]').textContent = on ? 'Selected ✓' : 'Select';
            if (on) name = c.dataset.name;
        });
        sel.innerHTML = name ? 'Selected design: <b>' + name + '</b>' : 'Pick a design to continue';
        go.disabled = !name;
    }
    cards.forEach(function (c) {
        c.querySelector('[data-act="select"]').addEventListener('click', function () { choose(c.dataset.key); });
        c.querySelector('[data-act="preview"]').addEventListener('click', function () { openModal(c); });
    });
    choose(value.value || null);

    // ---- full-size preview ----
    function openModal(card) {
        current = card;
        document.getElementById('wtModalName').textContent = card.dataset.name + ' design';
        mframe.src = card.dataset.url;
        setDevice('desktop');
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.remove('open');
        mframe.removeAttribute('src');
        document.body.style.overflow = '';
    }
    function setDevice(d) {
        stage.classList.toggle('phone', d === 'phone');
        Array.prototype.forEach.call(modal.querySelectorAll('[data-dev]'), function (b) { b.classList.toggle('on', b.dataset.dev === d); });
    }
    Array.prototype.forEach.call(modal.querySelectorAll('[data-dev]'), function (b) { b.addEventListener('click', function () { setDevice(b.dataset.dev); }); });
    document.getElementById('wtModalPick').addEventListener('click', function () { if (current) choose(current.dataset.key); closeModal(); });
    document.getElementById('wtModalClose').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('open')) closeModal(); });

    document.getElementById('wtForm').addEventListener('submit', function (e) { if (!value.value) e.preventDefault(); });
})();
</script>
</body>
</html>
