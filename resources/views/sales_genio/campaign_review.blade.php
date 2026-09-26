<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Review Budget — {{ $business->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('sales_genio.partials.campaign_styles')
<style>
  .cmp-wrap{max-width:1560px;padding-left:26px;padding-right:26px;}
  .rv-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px;}
  .rv-head h1{font-size:20px;font-weight:800;margin:0;color:#12203f;}
  .rv-steps{display:flex;align-items:center;gap:8px;font-size:12.5px;color:#8a92a6;font-weight:600;}
  .rv-steps b{color:#1a73e8;}
  .rv-grid{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;}
  .rv-main{flex:1;min-width:340px;}
  .rv-side{flex:0 0 330px;max-width:100%;}
  .rv-table{width:100%;border-collapse:collapse;border:1px solid #eef0f6;border-radius:12px;overflow:hidden;}
  .rv-table th{background:#fafbfc;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.3px;
    text-align:left;padding:11px 14px;}
  .rv-table th:last-child,.rv-table td:last-child{text-align:right;}
  .rv-table td{padding:13px 14px;border-top:1px solid #f1f3f8;font-size:13.5px;color:#37415a;}
  .rv-table .area{font-weight:700;color:#12203f;}
  .rv-table .pin{color:#8a92a6;font-weight:600;margin-left:6px;}
  .rv-pos{background:#eef4fb;color:#1c6fb8;border-radius:20px;padding:3px 11px;font-size:12px;font-weight:700;}
  .rv-amt{font-weight:700;color:#12203f;}
  .rv-free{margin-top:16px;display:flex;gap:14px;align-items:center;border:1px dashed #bde5cd;background:#f6fdf9;
    border-radius:12px;padding:14px 16px;}
  .rv-free img{width:92px;border:1px solid #e6e9f2;border-radius:7px;background:#fff;}
  .rv-free b{display:block;color:#12203f;font-size:14px;}
  .rv-free span{font-size:12.5px;color:#5a6478;}
  .rv-free .pill{background:#1f9d55;color:#fff;border-radius:20px;padding:3px 11px;font-size:11px;font-weight:700;margin-left:auto;}
  .rv-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;}
  .rv-card h3{margin:0;padding:15px 18px;border-bottom:1px solid #eef0f6;font-size:14.5px;font-weight:800;color:#12203f;}
  .rv-line{display:flex;justify-content:space-between;gap:10px;padding:12px 18px;font-size:13.5px;color:#5a6478;
    border-bottom:1px dashed #eef0f6;}
  .rv-line b{color:#12203f;}
  .rv-line.is-free b{color:#1f7a45;}
  .rv-grand{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;background:#12203f;color:#fff;}
  .rv-grand span{font-size:13.5px;opacity:.85;}
  .rv-grand strong{font-size:24px;}
  .rv-actions{display:flex;gap:10px;padding:16px 18px;}
  .rv-back{flex:0 0 120px;text-align:center;border:1px solid #d7dceb;color:#374151;border-radius:10px;padding:12px;
    font-weight:700;text-decoration:none;font-size:14px;}
  .rv-go{flex:1;background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:12px;font-weight:700;font-size:14px;
    cursor:pointer;font-family:'Poppins',sans-serif;}
  .rv-go:hover{background:#1662c4;}
  @media (max-width:900px){.rv-side{flex-basis:100%;}}
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="cmp-wrap">
  <div class="cmp-top">
    <a href="{{ route('sales_genio.business.campaign.position', ['id' => $business->id, 'pkg' => $package->id]) }}" class="cmp-back"><i class="fas fa-arrow-left"></i> Change positions</a>
    <div class="cmp-biz">
      <strong>{{ $business->name }}</strong>
      {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
  </div>

  @if(!empty($quote['clash']))
    <div class="cmp-flash warn"><i class="fas fa-circle-exclamation"></i> {{ $quote['clash'] }}</div>
  @endif

  <div class="cmp-layout">
    @include('sales_genio.partials.campaign_sidebar', ['selected' => $package])

    <div class="cmp-panel">
      <div class="rv-head">
        <h1>Review the budget</h1>
        <div class="rv-steps">Areas <i class="fas fa-chevron-right"></i> Positions <i class="fas fa-chevron-right"></i> <b>Review</b></div>
      </div>

      <div class="rv-grid">
        <div class="rv-main">
          <table class="rv-table">
            <thead>
              <tr><th>Area</th><th>Position</th><th>{{ $quote['tenure'] }}</th></tr>
            </thead>
            <tbody>
              @foreach($quote['lines'] as $line)
                <tr>
                  <td><span class="area">{{ $line['area'] }}</span><span class="pin">{{ $line['pincode'] }}</span></td>
                  <td><span class="rv-pos">{{ $line['tier'] }}</span></td>
                  <td class="rv-amt">₹{{ numberFormat($line['amount']) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($freebie)
            <div class="rv-free">
              @if($freebie->imageUrl())<img src="{{ $freebie->imageUrl() }}" alt="{{ $freebie->name }}">@endif
              <div>
                <b>{{ $freebie->name }}</b>
                <span>Comes free with this campaign · valid {{ $quote['days'] }} days</span>
              </div>
              <span class="pill">FREE</span>
            </div>
          @endif
        </div>

        <div class="rv-side">
          <form method="post" action="{{ route('sales_genio.business.campaign.confirm', ['id' => $business->id, 'pkg' => $package->id]) }}">
            @csrf
            <div class="rv-card">
              <h3>Budget summary</h3>
              <div class="rv-line"><span>{{ $package->name }}</span><b>₹{{ numberFormat($quote['total']) }}</b></div>
              <div class="rv-line"><span>Positions</span><b>{{ $quote['summary'] }}</b></div>
              <div class="rv-line"><span>Areas</span><b>{{ count($quote['lines']) }}</b></div>
              <div class="rv-line"><span>Tenure</span><b>{{ $quote['tenure'] }}</b></div>
              @if($freebie)
                <div class="rv-line is-free"><span>{{ $freebie->name }}</span><b>₹0</b></div>
              @endif
              <div class="rv-grand">
                <span>Total payable</span>
                <strong>₹{{ numberFormat($quote['total']) }}</strong>
              </div>
              <div class="rv-actions">
                <a class="rv-back" href="{{ route('sales_genio.business.campaign.position', ['id' => $business->id, 'pkg' => $package->id]) }}">Go back</a>
                <button type="submit" class="rv-go">Proceed</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
