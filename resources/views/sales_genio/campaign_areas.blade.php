<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Select Target Locations — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('sales_genio.partials.campaign_styles')
<style>
  .cmp-wrap{max-width:1560px;padding-left:26px;padding-right:26px;}
  .cmp-panel{padding:22px 26px;}
  .ar-list{max-height:none;}
  /* two columns of areas on a wide screen, so the list does not run down the page */
  @media (min-width:1100px){
    .ar-list{display:grid;grid-template-columns:1fr 1fr;max-height:420px;}
    .ar-row{border-right:1px solid #f1f3f8;}
  }

  .arm-add,.arm-find{display:none;}
  @media (max-width:760px){
    .cmp-actions,.ar-search,.ar-head h1{display:none !important;}
    .ar-head{display:flex;align-items:center;justify-content:space-between;margin:10px 0 8px;}
    #arCount{font-size:16px;font-weight:600;color:#37415a;margin:0;}
    .arm-find{display:block;background:none;border:0;font-size:19px;color:#6b7285;padding:6px;cursor:pointer;}
    .ar-list{border:0 !important;}
    .ar-row{border:0 !important;border-bottom:1px solid #eef0f4 !important;padding:14px 0 !important;background:none !important;}
    .ar-body strong{font-size:16px;}
    .ar-sub{font-size:14px;}

    /* summary: only what is chosen */
    body.arm-s2 .arm-foot-a{display:none !important;}
    body.arm-s1 .ar-all,body.arm-s1 .ar-item:not(.is-on),body.arm-s1 .arm-foot-b{display:none !important;}
    /* add more */
    body.arm-s2 .ar-head{display:none !important;}
    body.arm-s2 .arm-add{display:block;padding-top:14px;}
    body.arm-near .arm-city,body.arm-near .ar-list,body.arm-near .ar-all{display:none !important;}

    .arm-opt{display:flex;align-items:flex-start;gap:16px;padding:12px 0;font-size:15.5px;color:#1a1a2e;cursor:pointer;line-height:1.45;}
    .arm-opt input{position:absolute;opacity:0;pointer-events:none;}
    .arm-opt .rd{flex:0 0 26px;height:26px;border-radius:50%;border:1.5px solid #cfd5e1;display:flex;align-items:center;
      justify-content:center;color:transparent;font-size:12px;}
    .arm-opt input:checked + .rd{background:#1a73e8;border-color:#1a73e8;color:#fff;}
    .arm-city{padding-top:16px;}
    .arm-lbl{display:block;font-size:15px;color:#6b7285;margin:12px 0 10px;font-weight:400;}
    .arm-chips{display:flex;flex-wrap:wrap;gap:8px;}
    .arm-chip{display:inline-flex;align-items:center;gap:10px;background:#1a73e8;color:#fff;border:0;border-radius:24px;
      padding:9px 16px;font-size:14.5px;font-family:inherit;cursor:pointer;}
    .arm-search{width:100%;border:0;border-bottom:1px solid #dfe3ec;padding:8px 0 12px;font-size:17px;font-family:inherit;outline:none;}
    .arm-search:focus{border-bottom-color:#1a73e8;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')
@include('sales_genio.partials.campaign_mhead', [
  'back'  => route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]),
  'title' => 'Select Target Locations',
])

<div class="cmp-wrap">
  <div class="cmp-top">
    <a href="{{ route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]) }}" class="cmp-back"><i class="fas fa-arrow-left"></i> Back</a>
    @include('sales_genio.partials.campaign_dcart')
    <div class="cmp-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
    @include('sales_genio.partials.flow_avatar')
  </div>

  <div class="cmp-layout">
    @include('sales_genio.partials.campaign_sidebar', ['selected' => $package])

    <div class="cmp-panel">
      <form method="post" action="{{ route('sales_genio.business.campaign.areas.save', ['id' => $business->id, 'pkg' => $package->id]) }}" id="areaForm">
        @csrf
        <div class="ar-head">
          <div>
            <h1>Select Target Locations</h1>
            <p id="arCount">You have selected {{ count($selected) }} area{{ count($selected) == 1 ? '' : 's' }}</p>
          </div>
          <button type="button" class="arm-find" id="armFind" aria-label="Search and add areas"><i class="fas fa-magnifying-glass"></i></button>
          <div class="ar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="arSearch" placeholder="Search area or pincode">
          </div>
        </div>

        {{-- Phone, "Add More Areas" screen. --}}
        <div class="arm-add">
          @if($own)
            <label class="arm-opt">
              <input type="radio" name="arm_mode" value="near">
              <span class="rd"><i class="fas fa-check"></i></span>
              <span>Advertise near your pincode ({{ $own }})</span>
            </label>
          @endif
          <label class="arm-opt">
            <input type="radio" name="arm_mode" value="city">
            <span class="rd"><i class="fas fa-check"></i></span>
            <span>Advertise in specific pincodes, areas (Across City)</span>
          </label>

          <div class="arm-city">
            <p class="arm-lbl">Selected Areas</p>
            <div class="arm-chips" id="armChips"></div>
            <label class="arm-lbl" for="armSearch">Add pincodes, areas</label>
            <input type="text" id="armSearch" class="arm-search" autocomplete="off" placeholder="Type an area or pincode">
          </div>
        </div>

        @if(!$groups)
          <p class="ar-empty">No areas found for {{ $business->city }} yet.</p>
        @else
          <label class="ar-row ar-all">
            <input type="checkbox" id="arAll">
            <span class="ar-tick"><i class="fas fa-check"></i></span>
            <span class="ar-body"><strong id="arAllLabel">Select All</strong></span>
          </label>

          <div class="ar-list">
            @foreach($groups as $group)
              <label class="ar-row ar-item" data-text="{{ strtolower($group['label'].' '.$group['pincode'].' '.implode(' ', $group['areas'])) }}">
                <input type="checkbox" name="pincodes[]" value="{{ $group['pincode'] }}" @checked(in_array($group['pincode'], $selected))>
                <span class="ar-tick"><i class="fas fa-check"></i></span>
                <span class="ar-body">
                  <strong>{{ $group['label'] }}-{{ $group['pincode'] }}</strong>
                  @if($group['others'])
                    <span class="ar-sub">{{ implode(', ', array_slice($group['others'], 0, 3)) }}@if(count($group['others']) > 3)<b>…More</b>@endif</span>
                  @endif
                </span>
              </label>
            @endforeach
          </div>
        @endif

        <div class="cmp-actions">
          <a class="cmp-skip" href="{{ route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]) }}">Back</a>
          <button type="submit" class="cmp-add">Proceed</button>
        </div>

        {{-- Phone action bars: one for the summary, one for "Add More Areas". --}}
        <div class="cmp-mfoot arm-foot-a">
          <button type="button" class="sec" id="armMore">Add More Areas</button>
          <button type="submit" class="pri">Proceed</button>
        </div>
        <div class="cmp-mfoot arm-foot-b">
          <button type="button" class="pri" id="armApply">Apply</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var all = document.getElementById('arAll');
  var boxes = Array.prototype.slice.call(document.querySelectorAll('.ar-item input'));
  var count = document.getElementById('arCount');
  var allLabel = document.getElementById('arAllLabel');

  function sync() {
    var on = boxes.filter(function (b) { return b.checked; }).length;
    count.textContent = 'You have selected ' + on + ' area' + (on === 1 ? '' : 's');
    all.checked = on === boxes.length && on > 0;
    allLabel.textContent = all.checked ? 'Unselect All' : 'Select All';
    boxes.forEach(function (b) { b.closest('.ar-row').classList.toggle('is-on', b.checked); });
    all.closest('.ar-row').classList.toggle('is-on', all.checked);
  }

  all.addEventListener('change', function () {
    boxes.forEach(function (b) {
      if (b.closest('.ar-row').style.display !== 'none') b.checked = all.checked;
    });
    sync();
  });
  boxes.forEach(function (b) { b.addEventListener('change', sync); });

  document.getElementById('arSearch').addEventListener('input', function () {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.ar-item').forEach(function (row) {
      row.style.display = !q || row.dataset.text.indexOf(q) !== -1 ? '' : 'none';
    });
  });

  document.getElementById('areaForm').addEventListener('submit', function (e) {
    if (!boxes.some(function (b) { return b.checked; })) {
      e.preventDefault();
      alert('Please select at least one area.');
    }
  });

  sync();

  // ---- Phone: summary screen <-> "Add More Areas" screen ----
  if (!window.matchMedia('(max-width:760px)').matches) return;

  var body = document.body;
  var own = @json($own);
  var rows = Array.prototype.slice.call(document.querySelectorAll('.ar-item'));
  var search = document.getElementById('armSearch');
  var chips = document.getElementById('armChips');
  var allRow = all.closest('.ar-row');

  function boxFor(pin) { return boxes.filter(function (b) { return b.value === pin; })[0]; }
  function fire(b) { b.dispatchEvent(new Event('change', { bubbles: true })); }

  function drawChips() {
    chips.innerHTML = '';
    boxes.filter(function (b) { return b.checked; }).forEach(function (b) {
      var name = b.closest('.ar-row').querySelector('strong').textContent.trim();
      var chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'arm-chip';
      chip.innerHTML = '<span></span> <i class="fas fa-xmark"></i>';
      chip.firstChild.textContent = name.replace(/-(\d+)$/, ' ($1)');
      chip.addEventListener('click', function () { b.checked = false; fire(b); });
      chips.appendChild(chip);
    });
  }

  function filter() {
    var q = search.value.toLowerCase().trim();
    rows.forEach(function (row) {
      row.style.display = q && row.dataset.text.indexOf(q) !== -1 ? '' : 'none';
    });
    allRow.style.display = q ? '' : 'none';
    var shown = rows.filter(function (r) { return r.style.display !== 'none'; });
    all.checked = shown.length > 0 && shown.every(function (r) { return r.querySelector('input').checked; });
    allRow.classList.toggle('is-on', all.checked);
  }

  function setMode(mode) {
    body.classList.toggle('arm-near', mode === 'near');
    document.querySelectorAll('input[name="arm_mode"]').forEach(function (r) { r.checked = r.value === mode; });
    if (mode === 'near' && own) {
      boxes.forEach(function (b) { b.checked = b.value === own; });
      sync();
    }
    drawChips();
  }

  function showSummary() {
    body.classList.remove('arm-s2', 'arm-near');
    body.classList.add('arm-s1');
    rows.forEach(function (r) { r.style.display = ''; });
    allRow.style.display = '';
    window.scrollTo(0, 0);
  }

  function showAdd() {
    body.classList.remove('arm-s1');
    body.classList.add('arm-s2');
    var on = boxes.filter(function (b) { return b.checked; });
    setMode(own && on.length === 1 && on[0].value === own ? 'near' : 'city');
    search.value = '';
    filter();
    window.scrollTo(0, 0);
  }

  document.querySelectorAll('input[name="arm_mode"]').forEach(function (r) {
    r.addEventListener('change', function () { setMode(r.value); filter(); });
  });
  document.getElementById('areaForm').addEventListener('change', function (e) {
    if (e.target.name === 'arm_mode') return;
    drawChips();
    if (body.classList.contains('arm-s2')) filter();
  });
  search.addEventListener('input', filter);
  document.getElementById('armMore').addEventListener('click', showAdd);
  document.getElementById('armFind').addEventListener('click', showAdd);
  document.getElementById('armApply').addEventListener('click', function () {
    if (!boxes.some(function (b) { return b.checked; })) { alert('Please select at least one area.'); return; }
    showSummary();
  });

  showSummary();
})();
</script>

</body>
</html>
