<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $meta['title'] }} — SB Sales</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input,select,textarea{font-family:'Poppins',sans-serif;} body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  .sg-wrap{max-width:1180px;margin:0 auto;padding:22px 20px 60px;}
  .sg-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
  .sg-head h1{font-size:22px;font-weight:800;margin:0;color:#12203f;}
  .sg-head p{margin:4px 0 0;color:#6b7285;font-size:13.5px;}
  .sg-search{display:flex;gap:8px;}
  .sg-search input{border:1px solid #dfe3ec;border-radius:9px;padding:9px 13px;font-size:14px;min-width:220px;}
  .sg-search button{background:#1a73e8;color:#fff;border:0;border-radius:9px;padding:9px 16px;font-weight:700;cursor:pointer;}
  .sg-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;overflow:hidden;}
  table{width:100%;border-collapse:collapse;}
  th{background:#fafbfc;color:#6b7280;font-size:11.5px;text-transform:uppercase;letter-spacing:.3px;text-align:left;padding:12px 16px;}
  td{padding:14px 16px;border-top:1px solid #eef0f6;font-size:14px;vertical-align:middle;}
  tr:hover td{background:#fbfcfe;}
  .sg-biz{font-weight:700;color:#12203f;text-decoration:none;}
  .sg-biz:hover{color:#1a73e8;}
  .sg-sub{display:block;color:#7a8296;font-size:12.5px;margin-top:2px;}
  .sg-chip{display:inline-block;background:#eef4fb;color:#1c6fb8;border-radius:20px;padding:3px 11px;font-size:12px;font-weight:600;}
  .sg-chip.is-green{background:#eafaf1;color:#1f7a45;}
  .sg-chip.is-amber{background:#fff7e8;color:#96660d;}
  .sg-call{background:#1f9d55;color:#fff;border-radius:8px;padding:7px 13px;font-size:13px;font-weight:700;text-decoration:none;}
  .sg-open{border:1px solid #1a73e8;color:#1a73e8;border-radius:8px;padding:6px 13px;font-size:13px;font-weight:700;text-decoration:none;}
  .sg-mail{border:1px solid #6741d9;background:#fff;color:#6741d9;border-radius:8px;padding:6px 13px;font-size:13px;font-weight:700;cursor:pointer;}
  .sg-mail:disabled{opacity:.6;cursor:wait;}
  .sg-mail-msg{font-size:12px;margin-top:6px;white-space:normal;max-width:260px;}
  .sg-mail-msg.ok{color:#1f9d55;} .sg-mail-msg.err{color:#e03131;}
  .sg-cityflag{background:#eef4fd;color:#1a73e8;border-radius:20px;padding:3px 11px;font-size:12px;font-weight:700;margin:0 6px;}
  .sg-cityflag a{color:#1a73e8;text-decoration:underline;margin-left:6px;}
  .sg-due{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
  .sg-due span{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #e6e9f2;border-radius:22px;
    padding:8px 15px;font-size:13px;font-weight:700;color:#5a6478;}
  .sg-due .is-over{background:#fdeceb;border-color:#f5c2c0;color:#b4261f;}
  .sg-due .is-today{background:#fff7e8;border-color:#f2dca6;color:#8a6412;}
  .sg-due .is-none{color:#8a92a6;}
  .sg-due-tag{display:inline-block;border-radius:20px;padding:4px 11px;font-size:12px;font-weight:700;
    background:#eef4fb;color:#1c6fb8;white-space:nowrap;}
  .sg-due-tag.is-over{background:#fdeceb;color:#b4261f;}
  .sg-due-tag.is-today{background:#fff7e8;color:#8a6412;}
  .sg-due-tag.is-none{background:#f4f6fb;color:#8a92a6;}
  .sg-note{color:#7a8296;font-size:13px;max-width:220px;}
  .sg-empty{padding:52px 20px;text-align:center;color:#7a8296;}
  .sg-empty i{font-size:40px;color:#cbd2e0;display:block;margin-bottom:10px;}
  .sg-foot{padding:14px 16px;}
  .sg-foot .pagination{display:flex;gap:6px;list-style:none;padding:0;margin:0;flex-wrap:wrap;}
  .sg-foot .page-item a,.sg-foot .page-item span{display:inline-block;padding:6px 11px;border:1px solid #e6e9f2;border-radius:7px;
    color:#37415a;text-decoration:none;font-size:13px;}
  .sg-foot .page-item.active span{background:#1a73e8;color:#fff;border-color:#1a73e8;}

  @media (max-width:760px){
    .sg-wrap{padding:16px 12px 50px;}
    .sg-head h1{font-size:19px;}
    .sg-search{width:100%;}
    .sg-search input{flex:1;min-width:0;}
    .sg-card{border:0;background:none;}
    table, tbody, tr, td{display:block;width:100%;}
    thead{display:none;}
    tr{background:#fff;border:1px solid #e6e9f2;border-radius:14px;margin-bottom:12px;padding:6px 0;}
    tr:hover td{background:none;}
    td{display:flex;justify-content:space-between;align-items:center;gap:14px;border:0;padding:9px 14px;
      font-size:13.5px;text-align:right;}
    td:before{content:attr(data-label);color:#8a92a6;font-size:12px;font-weight:600;text-align:left;flex-shrink:0;}
    td[data-label="Business"]{flex-direction:column;align-items:flex-start;text-align:left;border-bottom:1px solid #f1f3f8;
      padding-bottom:12px;margin-bottom:4px;}
    td[data-label="Business"]:before{display:none;}
    .sg-biz{font-size:15px;}
    .sg-note{max-width:none;}
    /* Buttons in an even two-column grid: in one right-aligned row the four
       deal buttons overflowed and Call was cut off at the left edge. */
    td.sg-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding-top:12px;
      border-top:1px solid #f1f3f8;margin-top:4px;white-space:normal !important;}
    .sg-actions:before{display:none;}
    .sg-actions .sg-call,.sg-actions .sg-open,.sg-actions .sg-mail{display:flex;align-items:center;justify-content:center;
      gap:6px;padding:10px 8px;font-size:13.5px;text-align:center;}
    .sg-actions .sg-call{border:1px solid #1f9d55;}
    .sg-actions .sg-mail-msg{grid-column:1 / -1;max-width:none;margin-top:0;text-align:center;}
    .sg-actions .sg-mail-msg:empty{display:none;}
    .sg-empty{background:#fff;border:1px solid #e6e9f2;border-radius:14px;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="sg-wrap">
  <div class="sg-head">
    <div>
      <h1>{{ $meta['title'] }}</h1>
      <p>
        @php
          $noun = in_array($tab, ['new-business', 'all-business'])
              ? ($rows->total() == 1 ? 'business' : 'businesses')
              : ($rows->total() == 1 ? 'record' : 'records');
        @endphp
        {{ $rows->total() }} {{ $noun }}
        @if($city)<span class="sg-cityflag">in {{ $city }} <a href="{{ route('sales_genio.city.clear') }}">show all</a></span>@endif
        · {{ $user->name }} ({{ $user->empcode }})
      </p>
    </div>
    @php $scheduled = (bool) array_intersect($meta['dispositions'] ?? [], \App\Models\ListingDisposition::SCHEDULED); @endphp
    @php
      $pageFields = \App\Http\Controllers\SalesGenioController::searchFields();
      $pageBy = array_key_exists($searchBy ?? '', $pageFields) ? $searchBy : 'name';
    @endphp
    <form class="sg-search" method="get">
      {{-- Carry the field picked in the top bar, or this box silently searches the name instead. --}}
      <input type="hidden" name="by" value="{{ $pageBy }}">
      <input type="text" name="q" value="{{ $search }}" placeholder="{{ $pageFields[$pageBy] }}">
      <button type="submit">Search</button>
    </form>
  </div>

  @if($scheduled && ($due['overdue'] || $due['today'] || $due['upcoming'] || $due['undated']))
    <div class="sg-due">
      @if($due['overdue'])<span class="is-over"><i class="fas fa-triangle-exclamation"></i> {{ $due['overdue'] }} overdue</span>@endif
      @if($due['today'])<span class="is-today"><i class="fas fa-bell"></i> {{ $due['today'] }} due today</span>@endif
      @if($due['upcoming'])<span><i class="fas fa-calendar-day"></i> {{ $due['upcoming'] }} upcoming</span>@endif
      @if($due['undated'])<span class="is-none"><i class="fas fa-circle-question"></i> {{ $due['undated'] }} with no date</span>@endif
    </div>
  @endif

  <div class="sg-card">
    <table>
      <thead>
        <tr>
          <th>Business</th>
          <th>Mobile</th>
          @if($tab === 'deal-closed')
            <th>Packages</th><th>Amount</th><th>Paid on</th>
          @elseif(in_array($tab, ['new-business', 'all-business']))
            <th>Category</th><th>Status</th><th>Created</th>
          @else
            @if($scheduled)<th>Due</th>@endif
            <th>Disposition</th><th>Note</th><th>Updated</th>
          @endif
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          @php $biz = in_array($tab, ['new-business', 'all-business']) ? $row : $row->listing; @endphp
          @continue(!$biz)
          <tr>
            <td data-label="Business">
              <a href="{{ route('sales_genio.business.profile', $biz->id) }}" class="sg-biz">{{ $biz->name }}</a>
              <span class="sg-sub">{{ $biz->area ? $biz->area.', ' : '' }}{{ $biz->city }}</span>
            </td>
            <td data-label="Mobile">{{ $biz->mobile ?: '—' }}</td>

            @if($tab === 'deal-closed')
              <td data-label="Packages">{{ $row->items->pluck('name')->implode(', ') }}</td>
              <td data-label="Amount"><b>₹{{ numberFormat($row->total) }}</b></td>
              <td data-label="Payment">
                @if($row->isPaid())
                  <span class="sg-chip is-green">Paid</span>
                  <span class="sg-sub">{{ $row->paid_at?->format('d M Y, h:i A') }}</span>
                @else
                  <span class="sg-chip is-amber">Payment pending</span>
                  <span class="sg-sub">Closed {{ $row->closed_at?->format('d M Y, h:i A') }}</span>
                @endif
              </td>
            @elseif(in_array($tab, ['new-business', 'all-business']))
              <td data-label="Category">{{ optional($biz->category)->name ?? '—' }}</td>
              <td data-label="Status">
                @if($biz->status == 1)<span class="sg-chip is-green">Live</span>
                @elseif($biz->status == 2)<span class="sg-chip is-amber">Rejected</span>
                @else<span class="sg-chip is-amber">Pending</span>@endif
              </td>
              <td data-label="Created">{{ $biz->created_at?->format('d M Y, h:i A') }}</td>
            @else
              @if($scheduled)
                <td data-label="Due">
                  @if($row->due_at)
                    <span class="sg-due-tag {{ $row->isOverdue() ? 'is-over' : ($row->isDueToday() ? 'is-today' : '') }}">{{ $row->dueLabel() }}</span>
                  @else
                    <span class="sg-due-tag is-none">No date</span>
                  @endif
                </td>
              @endif
              <td data-label="Disposition"><span class="sg-chip">{{ $row->disposition }}</span></td>
              <td data-label="Note" class="sg-note">{{ $row->note ?: '—' }}</td>
              <td data-label="Updated">{{ $row->updated_at?->format('d M Y, h:i A') }}</td>
            @endif

            <td data-label="Action" class="sg-actions" style="white-space:nowrap;">
              @if($biz->mobile)<a href="tel:{{ $biz->mobile }}" class="sg-call"><i class="fas fa-phone"></i> Call</a>@endif
              <a href="{{ route('sales_genio.business.profile', $biz->id) }}" class="sg-open">Open</a>
              @if($tab === 'deal-closed')
                <a href="{{ route('sales.order.invoice', $row->token) }}" target="_blank" rel="noopener" class="sg-open">Invoice</a>
                <button type="button" class="sg-mail" data-url="{{ route('sales_genio.business.order.invoice.mail', [$biz->id, $row->id]) }}" title="{{ $row->invoice_sent_at ? 'Last sent '.$row->invoice_sent_at->format('d M, h:i A').' to '.$row->invoice_email : 'Not sent yet' }}"><i class="fas fa-envelope"></i> Mail</button>
                <div class="sg-mail-msg"></div>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8">
            <div class="sg-empty">
              <i class="fas fa-inbox"></i>
              @if($tab === 'all-business' && !$search && !$city)
                Search any business by name, mobile, area, pincode or city — old listings included — then open it to edit.
              @else
                Nothing in {{ $meta['title'] }} yet.
              @endif
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="sg-foot">{{ $rows->links() }}</div>
  </div>
</div>

<script>
document.querySelectorAll('.sg-mail').forEach(function (btn) {
  var msg = btn.parentNode.querySelector('.sg-mail-msg');
  function send(email) {
    btn.disabled = true; msg.className = 'sg-mail-msg'; msg.textContent = 'Sending…';
    fetch(btn.dataset.url, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
      body: JSON.stringify({email: email || ''})
    }).then(function (r) { return r.json(); }).then(function (d) {
      btn.disabled = false;
      if (d.need_email) {
        var e = prompt('This business has no email. Send the invoice to:');
        if (e) { send(e.trim()); } else { msg.className = 'sg-mail-msg err'; msg.textContent = d.message; }
        return;
      }
      msg.className = 'sg-mail-msg ' + (d.status ? 'ok' : 'err');
      msg.textContent = d.message || (d.status ? 'Sent.' : 'Could not send.');
    }).catch(function () { btn.disabled = false; msg.className = 'sg-mail-msg err'; msg.textContent = 'Could not send. Check the connection.'; });
  }
  btn.addEventListener('click', function () { send(''); });
});
</script>
</body>
</html>
