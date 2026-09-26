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
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="cmp-wrap">
  <div class="cmp-top">
    <a href="{{ route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id]) }}" class="cmp-back"><i class="fas fa-arrow-left"></i> Back</a>
    <div class="cmp-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
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
          <div class="ar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="arSearch" placeholder="Search area or pincode">
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
    boxes.forEach(function (b) { b.checked = all.checked; });
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
})();
</script>

</body>
</html>
