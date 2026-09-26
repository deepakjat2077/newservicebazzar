<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports — Sales Genio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input,select,textarea{font-family:'Poppins',sans-serif;} body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .sg-wrap{max-width:1180px;margin:0 auto;padding:22px 20px 60px;}
  h1{font-size:22px;font-weight:800;margin:0 0 4px;color:#12203f;}
  .sg-sub{color:#6b7285;font-size:13.5px;margin:0 0 18px;}
  .sg-range{display:flex;gap:10px;align-items:flex-end;background:#fff;border:1px solid #e6e9f2;border-radius:14px;
    padding:16px 18px;margin-bottom:18px;flex-wrap:wrap;}
  .sg-range label{display:block;font-size:12px;color:#6b7285;margin-bottom:5px;font-weight:600;}
  .sg-range input{border:1px solid #dfe3ec;border-radius:9px;padding:8px 12px;font-size:14px;}
  .sg-range button{background:#1a73e8;color:#fff;border:0;border-radius:9px;padding:9px 18px;font-weight:700;cursor:pointer;}
  .sg-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:20px;}
  .sg-tile{background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:16px 18px;}
  .sg-tile span{display:block;font-size:12px;color:#7a8296;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
  .sg-tile strong{display:block;font-size:26px;color:#12203f;margin-top:6px;}
  .sg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:16px;}
  .sg-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;overflow:hidden;}
  .sg-card h2{font-size:15px;font-weight:800;margin:0;padding:15px 18px;border-bottom:1px solid #eef0f6;color:#12203f;}
  table{width:100%;border-collapse:collapse;}
  td{padding:11px 18px;border-top:1px solid #f1f3f8;font-size:14px;}
  td:last-child{text-align:right;font-weight:700;}
  .sg-empty{padding:32px;text-align:center;color:#7a8296;}
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="sg-wrap">
  <h1>My Performance</h1>
  <p class="sg-sub">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }} · {{ $user->name }} ({{ $user->empcode }})</p>

  <form class="sg-range" method="get">
    <div><label>From</label><input type="date" name="from" value="{{ $from->toDateString() }}"></div>
    <div><label>To</label><input type="date" name="to" value="{{ $to->toDateString() }}"></div>
    <button type="submit">Apply</button>
  </form>

  <div class="sg-tiles">
    <div class="sg-tile"><span>New Business</span><strong>{{ $daily->sum() }}</strong></div>
    <div class="sg-tile"><span>Dispositions</span><strong>{{ $dispositions->sum() }}</strong></div>
    <div class="sg-tile"><span>Deal Closed</span><strong>{{ $deals }}</strong></div>
    <div class="sg-tile"><span>Revenue</span><strong>₹{{ numberFormat($revenue) }}</strong></div>
    <div class="sg-tile"><span>Follow Ups</span><strong>{{ $dispositions['Follow Up'] ?? 0 }}</strong></div>
    <div class="sg-tile"><span>Call Backs</span><strong>{{ $dispositions['Call Back'] ?? 0 }}</strong></div>
  </div>

  <div class="sg-grid">
    <div class="sg-card">
      <h2>By disposition</h2>
      <table>
        @forelse($dispositions as $name => $count)
          <tr><td>{{ $name }}</td><td>{{ $count }}</td></tr>
        @empty
          <tr><td colspan="2"><div class="sg-empty">No dispositions in this period.</div></td></tr>
        @endforelse
      </table>
    </div>

    <div class="sg-card">
      <h2>Businesses created per day</h2>
      <table>
        @forelse($daily as $day => $count)
          <tr><td>{{ \Carbon\Carbon::parse($day)->format('d M Y') }}</td><td>{{ $count }}</td></tr>
        @empty
          <tr><td colspan="2"><div class="sg-empty">No businesses created in this period.</div></td></tr>
        @endforelse
      </table>
    </div>
  </div>
</div>

</body>
</html>
