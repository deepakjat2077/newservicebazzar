<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $meta['title'] }} — Sales Genio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input,select,textarea{font-family:'Poppins',sans-serif;}
  body{margin:0;background:#f1f3f7;font-family:'Poppins',sans-serif;color:#1a2235;}

  .fu-wrap{display:grid;grid-template-columns:392px 1fr;gap:16px;padding:16px 18px 40px;align-items:start;}

  /* ---------- left: the queue ---------- */
  .fu-list{background:transparent;}
  .fu-bar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;}
  .fu-bar .found{font-size:14px;font-weight:600;color:#3d4763;}
  .fu-bar .tools{display:flex;gap:8px;position:relative;}
  .fu-pill{background:#fff;border:1px solid #dfe3ec;border-radius:8px;padding:7px 13px;font-size:13px;
    color:#3d4763;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
  .fu-pill:hover{border-color:#b9c2d6;}
  .fu-pill.is-on{border-color:#1a73e8;color:#1a73e8;background:#f5f9ff;}

  .fu-drop{display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #e6e9f2;
    border-radius:10px;box-shadow:0 12px 30px rgba(16,24,40,.14);padding:6px;min-width:190px;z-index:60;}
  .fu-drop.open{display:block;}
  .fu-drop a,.fu-drop button{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;
    padding:9px 12px;border-radius:7px;font-size:13.5px;color:#37415a;text-decoration:none;background:none;border:0;cursor:pointer;}
  .fu-drop a:hover,.fu-drop button:hover{background:#f4f7fc;}
  .fu-drop a.is-on{background:#eef4fb;color:#1a73e8;font-weight:600;}
  .fu-drop b{font-size:12px;color:#8a92a6;font-weight:600;}

  /* ---------- Filters drawer ---------- */
  /* The left rail shifts every direct child of body; a full-screen drawer
     must not move with it. */
  body > .fu-scrim{margin-left:0 !important;}
  .fu-scrim{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:900;display:none;opacity:0;transition:opacity .18s;}
  .fu-scrim.show{display:block;}
  .fu-scrim.open{opacity:1;}
  .fu-panel{position:absolute;top:0;right:0;bottom:0;width:420px;max-width:100%;background:#fff;
    display:flex;flex-direction:column;box-shadow:-8px 0 32px rgba(16,24,40,.18);
    transform:translateX(100%);transition:transform .24s cubic-bezier(.22,.61,.36,1);}
  .fu-scrim.open .fu-panel{transform:translateX(0);}
  .fu-phead{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #eef0f6;}
  .fu-phead h3{margin:0;font-size:17px;font-weight:700;color:#12203f;}
  .fu-pclose{background:none;border:0;font-size:22px;line-height:1;color:#62708c;cursor:pointer;}
  .fu-pbody{flex:1;overflow-y:auto;padding:20px 24px;}
  .fu-pfoot{display:flex;gap:12px;padding:16px 24px;border-top:1px solid #eef0f6;}
  .fu-pfoot button{flex:1;border-radius:9px;padding:12px;font-size:14px;font-weight:600;cursor:pointer;}
  .fu-pfoot .reset{background:#fff;border:1px solid #d5dae5;color:#3d4763;}
  .fu-pfoot .apply{background:#1a73e8;border:0;color:#fff;}

  .fu-plabel{font-size:14px;font-weight:600;color:#12203f;margin:0 0 12px;}
  .fu-chips{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
  .fu-chip{border:1px solid #d5dae5;background:#fff;border-radius:8px;padding:8px 18px;font-size:13.5px;
    color:#3d4763;cursor:pointer;}
  .fu-chip.is-on{border-color:#1a73e8;color:#1a73e8;background:#f5f9ff;}
  .fu-range{font-size:13px;color:#5b6379;margin-bottom:16px;}

  /* calendar */
  .cal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
  .cal-head b{font-size:14px;color:#12203f;}
  .cal-nav{display:flex;gap:4px;}
  .cal-nav button{background:none;border:0;width:28px;height:28px;border-radius:6px;color:#5b6379;cursor:pointer;font-size:13px;}
  .cal-nav button:hover{background:#f1f3f8;}
  table.cal{width:100%;border-collapse:collapse;}
  table.cal th{font-size:12px;color:#8a92a6;font-weight:600;padding:8px 0;background:#f7f9fc;}
  table.cal td{text-align:center;padding:0;}
  table.cal button{width:100%;border:0;background:none;padding:9px 0;font-size:13px;color:#1d2433;cursor:pointer;}
  table.cal button:hover:not(:disabled){background:#eef4fb;border-radius:6px;}
  table.cal button.mute{color:#c3c9d6;}
  table.cal td.in button{background:#eef4fb;}
  table.cal button.pick{background:#1a73e8;color:#fff;border-radius:50%;width:30px;height:30px;padding:0;margin:0 auto;display:block;}

  .fu-sub{font-size:12px;color:#6b7285;margin:0 0 6px;font-weight:600;}
  .fu-panel input[type=text]{width:100%;border:1px solid #dfe3ec;border-radius:8px;padding:9px 12px;
    font-size:13.5px;margin-bottom:14px;}

  .fu-items{background:#fff;border:1px solid #e6e9f2;border-radius:12px;overflow:hidden;
    max-height:calc(100vh - 170px);overflow-y:auto;}
  .fu-item{display:block;padding:14px 16px;border-bottom:1px solid #eef0f6;text-decoration:none;color:inherit;}
  .fu-item:last-child{border-bottom:0;}
  .fu-item:hover{background:#fafbfe;}
  .fu-item.is-on{background:#eef4fb;box-shadow:inset 3px 0 0 #1a73e8;}
  .fu-top{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:9px;}
  .fu-when{display:inline-flex;align-items:center;gap:6px;background:#f3f5fa;color:#5b6379;border-radius:6px;
    padding:3px 9px;font-size:11.5px;font-weight:600;}
  .fu-when.is-over{background:#fdeaea;color:#c62828;}
  .fu-when.is-today{background:#fff4e5;color:#b25e02;}
  .fu-when.is-none{background:#f3f5fa;color:#8a92a6;}
  .fu-kind{font-size:11.5px;color:#1a73e8;font-weight:600;}
  .fu-name{display:flex;gap:8px;font-size:14.5px;font-weight:700;color:#12203f;line-height:1.35;}
  .fu-name .n{color:#8a92a6;font-weight:600;}
  .fu-meta{font-size:12.5px;color:#7a8296;margin-top:5px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
  .fu-note{font-size:12.5px;color:#5b6379;margin-top:7px;background:#f8f9fc;border-radius:7px;padding:7px 10px;}
  .fu-call{margin-left:auto;color:#1a73e8;font-size:15px;}
  .fu-row{display:flex;align-items:flex-start;gap:10px;}
  .fu-row > div{flex:1;min-width:0;}
  .fu-empty{padding:44px 20px;text-align:center;color:#8a92a6;font-size:13.5px;}
  .fu-empty i{display:block;font-size:26px;margin-bottom:10px;color:#c7cddb;}
  .fu-pages{padding:12px;}

  /* ---------- right: the business ---------- */
  .fu-detail{background:#fff;border:1px solid #e6e9f2;border-radius:12px;overflow:hidden;}
  .fu-dhead{display:flex;gap:16px;padding:18px 20px;}
  .fu-dhead img{width:118px;height:88px;object-fit:cover;border-radius:8px;background:#f1f3f7;flex-shrink:0;}
  .fu-dhead .who{flex:1;min-width:0;}
  .fu-dhead h2{margin:0;font-size:19px;font-weight:700;color:#12203f;display:flex;align-items:center;gap:8px;}
  .fu-code{font-size:12.5px;color:#8a92a6;margin-top:3px;}
  .fu-tags{display:flex;gap:8px;flex-wrap:wrap;margin-top:9px;align-items:center;}
  .fu-tag{font-size:11.5px;font-weight:600;border-radius:6px;padding:3px 10px;background:#f3f5fa;color:#5b6379;}
  .fu-tag.hot{background:#fdeaea;color:#d33;}
  .fu-tag.live{background:#eafaf1;color:#1f7a45;}
  .fu-rate{display:inline-flex;align-items:center;gap:4px;background:#1f8a4c;color:#fff;border-radius:5px;
    padding:2px 8px;font-size:12.5px;font-weight:700;}
  .fu-edit{border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:8px;padding:8px 16px;
    font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;height:fit-content;display:inline-flex;gap:7px;align-items:center;}
  .fu-edit:hover{background:#f5f9ff;}

  .fu-tabs{display:flex;gap:26px;padding:0 20px;border-bottom:1px solid #eef0f6;overflow-x:auto;}
  .fu-tabs button{background:none;border:0;padding:12px 0;font-size:13.5px;color:#5b6379;cursor:pointer;
    border-bottom:2px solid transparent;white-space:nowrap;}
  .fu-tabs button.is-on{color:#1a73e8;font-weight:600;border-bottom-color:#1a73e8;}

  .fu-pane{padding:20px;display:none;}
  .fu-pane.is-on{display:block;}
  .fu-sec{padding:16px 0;border-bottom:1px solid #f2f4f9;}
  .fu-sec:last-child{border-bottom:0;}
  .fu-sec h4{margin:0 0 8px;font-size:13.5px;font-weight:700;color:#12203f;}
  .fu-sec p{margin:0;font-size:13.5px;color:#4a5570;line-height:1.6;}
  .fu-link{color:#1a73e8;text-decoration:none;font-size:13px;}
  .fu-owner{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
  .fu-owner .num{flex:1;min-width:190px;font-size:13.5px;color:#4a5570;}
  .fu-btn{border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;text-decoration:none;
    display:inline-flex;align-items:center;gap:8px;border:1px solid transparent;}
  .fu-btn.wa{border-color:#25d366;color:#128c3e;background:#fff;}
  .fu-btn.call{background:#1a73e8;color:#fff;}
  .fu-btn.ghost{border-color:#dfe3ec;color:#3d4763;background:#fff;}

  .fu-cards{display:flex;gap:12px;overflow-x:auto;padding-bottom:4px;}
  .fu-pkg{border:1px solid #e6e9f2;border-radius:10px;padding:13px 15px;min-width:230px;}
  .fu-pkg .st{font-size:11px;font-weight:700;border-radius:5px;padding:2px 8px;display:inline-block;margin-bottom:8px;}
  .fu-pkg .st.on{background:#eafaf1;color:#1f7a45;}
  .fu-pkg .st.off{background:#fdeaea;color:#c62828;}
  .fu-pkg b{display:block;font-size:15px;color:#12203f;}
  .fu-pkg small{display:block;font-size:12px;color:#7a8296;margin-top:4px;}

  .fu-strength{display:flex;align-items:center;gap:12px;background:#fafbfe;border:1px solid #eef0f6;
    border-radius:10px;padding:13px 16px;}
  .fu-strength .track{flex:1;height:7px;background:#e9edf4;border-radius:4px;overflow:hidden;}
  .fu-strength .fill{height:100%;background:#2fa360;border-radius:4px;}
  .fu-strength b{font-size:13px;color:#1f7a45;white-space:nowrap;}

  table.fu-tbl{width:100%;border-collapse:collapse;}
  table.fu-tbl th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.3px;color:#8a92a6;
    padding:10px 12px;background:#fafbfc;}
  table.fu-tbl td{padding:12px;border-top:1px solid #eef0f6;font-size:13.5px;}

  .fu-none{padding:60px 20px;text-align:center;color:#8a92a6;}
  .fu-none i{display:block;font-size:30px;margin-bottom:12px;color:#c7cddb;}

  @media (max-width:1050px){
    .fu-wrap{grid-template-columns:1fr;}
    .fu-items{max-height:none;}
    .fu-detail{display:none;}
    .fu-detail.mobile-open{display:block;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

@php
  // New Business rows are listings; the scheduled queues are dispositions.
  $isListing = in_array($tab, ['new-business', 'all-business']);
  $dueLinks = ['all' => 'All', 'today' => 'Todays'];
  $biz = $detail['listing'] ?? null;
  $hasFilter = request()->filled('from') || request()->filled('to')
            || request()->filled('area') || request()->filled('pincode');
@endphp

<div class="fu-wrap">

  {{-- ================= left: the queue ================= --}}
  <div class="fu-list">
    <div class="fu-bar">
      <span class="found">{{ $rows->total() }} result{{ $rows->total() == 1 ? '' : 's' }} found</span>
      <div class="tools">
        <button type="button" class="fu-pill {{ $dueFilter !== 'all' ? 'is-on' : '' }}" onclick="toggleBox(event,'dueDrop')">
          {{ $dueLinks[$dueFilter] ?? 'All' }} <i class="fas fa-chevron-down" style="font-size:10px;"></i>
        </button>
        <button type="button" class="fu-pill {{ $hasFilter ? 'is-on' : '' }}" onclick="openFilters()">Filter</button>

        <div class="fu-drop" id="dueDrop">
          @foreach($dueLinks as $key => $label)
            <a class="{{ $dueFilter === $key ? 'is-on' : '' }}"
               href="{{ request()->fullUrlWithQuery(['due' => $key, 'page' => null, 'open' => null]) }}">{{ $label }}</a>
          @endforeach
        </div>
      </div>
    </div>

    <div class="fu-items">
      @forelse($rows as $i => $row)
        {{-- New Business lists businesses; the scheduled queues list
             dispositions that point at one. --}}
        @php $b = $isListing ? $row : $row->listing; @endphp
        @continue(!$b)
        <a class="fu-item {{ (int) $open === (int) $b->id ? 'is-on' : '' }}"
           href="{{ request()->fullUrlWithQuery(['open' => $b->id]) }}#detail">
          <div class="fu-top">
            @if($isListing)
              <span class="fu-when {{ $b->created_at?->isToday() ? 'is-today' : '' }}">
                <i class="far fa-clock"></i> Added {{ $b->created_at?->format('d M Y | h:i A') }}
              </span>
              <span class="fu-kind">
                @if($b->status == 1) Live
                @elseif($b->status == 2) Rejected
                @else Pending @endif
              </span>
            @else
              @if($row->due_at)
                <span class="fu-when {{ $row->isOverdue() ? 'is-over' : ($row->isDueToday() ? 'is-today' : '') }}">
                  <i class="far fa-clock"></i> {{ $row->due_at->format('d M Y | h:i A') }}
                </span>
              @else
                <span class="fu-when is-none"><i class="far fa-clock"></i> No date</span>
              @endif
              <span class="fu-kind">{{ $row->disposition }}</span>
            @endif
          </div>

          <div class="fu-row">
            <div>
              <div class="fu-name">
                <span class="n">{{ $rows->firstItem() + $i }}.</span>
                <span>{{ $b->name }}</span>
              </div>
              <div class="fu-meta"><i class="fas fa-location-dot"></i>
                {{ $b->area ?: $b->city }}@if($b->pincode) &bull; {{ $b->pincode }}@endif
              </div>
              @if($b->category)
                <div class="fu-meta"><i class="fas fa-tag"></i> {{ $b->category->name }}</div>
              @endif
              @if(!$isListing && $row->note)
                <div class="fu-note">{{ \Illuminate\Support\Str::limit($row->note, 90) }}</div>
              @endif
            </div>
            @if($b->mobile)
              <span class="fu-call" onclick="event.preventDefault();event.stopPropagation();location.href='tel:{{ $b->mobile }}'">
                <i class="fas fa-phone"></i>
              </span>
            @endif
          </div>
        </a>
      @empty
        <div class="fu-empty">
          <i class="fas fa-inbox"></i>
          @if($search)
            No {{ strtolower($meta['title']) }} matches “{{ $search }}”.
          @else
            Nothing in {{ $meta['title'] }} yet.
          @endif
        </div>
      @endforelse

      @if($rows->hasPages())
        <div class="fu-pages">{{ $rows->links() }}</div>
      @endif
    </div>
  </div>

  {{-- ================= right: the business ================= --}}
  <div class="fu-detail" id="detail">
    @if(!$biz)
      <div class="fu-none">
        <i class="fas fa-building-circle-check"></i>
        Pick a business on the left to see its details here.
      </div>
    @else
      <div class="fu-dhead">
        @if($biz->thumb)
          <img src="{{ asset('assets/images/listing/thumb/'.$biz->thumb) }}" alt="">
        @else
          <img src="{{ asset('assets/images/default.jpg') }}" alt="">
        @endif
        <div class="who">
          <h2>
            {{ $biz->name }}
            @if($biz->is_star)<i class="fas fa-star" style="color:#f5a623;font-size:15px;"></i>@endif
          </h2>
          <div class="fu-code">SB{{ str_pad($biz->id, 6, '0', STR_PAD_LEFT) }}@if($biz->pincode) &bull; {{ $biz->pincode }}@endif</div>
          <div class="fu-tags">
            @if($detail['revenue'] > 0)
              <span class="fu-tag live"><i class="fas fa-circle-check"></i> Paid client</span>
            @elseif($detail['strength'] >= 80)
              <span class="fu-tag hot">Hot lead 🔥</span>
            @endif
            @if($biz->verified)<span class="fu-tag live">SB Verified</span>@endif
            @if($biz->trust)<span class="fu-tag live">SB Trusted</span>@endif
            <span class="fu-rate"><i class="fas fa-star" style="font-size:10px;"></i>
              {{ number_format($detail['rating'], 1) }}</span>
            <span style="font-size:12.5px;color:#7a8296;">{{ $detail['rating_cnt'] }} Ratings</span>
          </div>
        </div>
        <a class="fu-edit" href="{{ route('sales_genio.business.profile', $biz->id) }}">
          <i class="fas fa-pen-to-square"></i> Open
        </a>
      </div>

      <div class="fu-tabs">
        <button class="is-on" onclick="showPane(this,'pSummary')">Summary</button>
        <button onclick="showPane(this,'pPayments')">Payment History</button>
        <button onclick="showPane(this,'pPackages')">Packages</button>
        <button onclick="showPane(this,'pActivity')">Activity</button>
      </div>

      {{-- ---- Summary ---- --}}
      <div class="fu-pane is-on" id="pSummary">
        <div class="fu-sec">
          <h4>Address</h4>
          @php
            $addr = collect([$biz->plot_no, $biz->building_name, $biz->street_name, $biz->landmark, $biz->area, $biz->city])
                      ->filter()->implode(', ');
          @endphp
          <p>{{ $addr ?: '—' }}{{ $biz->pincode ? '-'.$biz->pincode : '' }}</p>
          @if($addr)
            <a class="fu-link" target="_blank"
               href="https://www.google.com/maps/search/?api=1&query={{ urlencode($addr) }}">Get Direction</a>
          @endif
        </div>

        <div class="fu-sec">
          <h4>Owner Details</h4>
          <div class="fu-owner">
            <span class="num">
              {{-- contact_title is a salutation (Mr/Mrs/Dr), so it goes in
                   front of the name — the role in brackets is always Owner. --}}
              {{ $biz->contact_person ? trim(($biz->contact_title ? $biz->contact_title.' ' : '').$biz->contact_person).' (Owner) • ' : '' }}
              {{ collect(array_filter((array) ($biz->mobile_numbers ?: [$biz->mobile])))->implode(', ') ?: '—' }}
            </span>
            @if($biz->mobile)
              <a class="fu-btn wa" target="_blank"
                 href="https://wa.me/91{{ preg_replace('/\D/', '', $biz->mobile) }}"><i class="fab fa-whatsapp"></i> WhatsApp</a>
              <a class="fu-btn call" href="tel:{{ $biz->mobile }}"><i class="fas fa-phone"></i> Call</a>
            @endif
          </div>
        </div>

        <div class="fu-sec">
          <h4>Business Category</h4>
          <p>{{ optional($biz->category)->name ?: '—' }}</p>
        </div>

        <div class="fu-sec">
          <h4>Profile Strength</h4>
          <div class="fu-strength">
            <div class="track"><div class="fill" style="width:{{ $detail['strength'] }}%;
              {{ $detail['strength'] < 60 ? 'background:#e0a11b;' : '' }}"></div></div>
            <b style="{{ $detail['strength'] < 60 ? 'color:#b25e02;' : '' }}">{{ $detail['strength'] }}%</b>
          </div>
          @if($detail['missing'])
            <p style="margin-top:9px;font-size:12.5px;color:#b25e02;">
              Still needed: {{ implode(', ', $detail['missing']) }}
            </p>
          @endif
        </div>

        @if($detail['last'])
          <div class="fu-sec">
            <h4>Last Disposed By</h4>
            <p>
              {{ optional($detail['last']->salesUser)->name ?: 'Unknown' }}
              @if(optional($detail['last']->salesUser)->empcode) • {{ $detail['last']->salesUser->empcode }} @endif
              <br>
              <span style="color:#8a92a6;font-size:12.5px;">
                {{ $detail['last']->disposition }} • {{ $detail['last']->updated_at?->format('d M Y | h:i A') }}
              </span>
            </p>
          </div>
        @endif

        <div class="fu-sec" style="display:flex;gap:10px;flex-wrap:wrap;">
          <a class="fu-btn ghost" href="{{ route('sales_genio.business.profile', $biz->id) }}">
            <i class="fas fa-id-card"></i> Full profile</a>
          <a class="fu-btn ghost" href="{{ route('sales_genio.business.photos', $biz->id) }}">
            <i class="fas fa-images"></i> Photos</a>
          <a class="fu-btn ghost" href="{{ route('sales_genio.business.campaign', $biz->id) }}">
            <i class="fas fa-bullhorn"></i> Manage Campaign</a>
        </div>
      </div>

      {{-- ---- Payment History ---- --}}
      <div class="fu-pane" id="pPayments">
        @if($detail['orders']->isEmpty())
          <div class="fu-none"><i class="fas fa-receipt"></i> No payment received from this business yet.</div>
        @else
          <p style="margin:0 0 14px;font-size:13.5px;color:#4a5570;">
            Total received: <b style="color:#1f7a45;">₹{{ number_format($detail['revenue']) }}</b>
            across {{ $detail['orders']->count() }} payment{{ $detail['orders']->count() == 1 ? '' : 's' }}.
          </p>
          <table class="fu-tbl">
            <thead><tr><th>Paid on</th><th>Packages</th><th>Amount</th><th>Invoice</th></tr></thead>
            <tbody>
              @foreach($detail['orders'] as $o)
                <tr>
                  <td>{{ $o->paid_at?->format('d M Y, h:i A') ?: '—' }}</td>
                  <td>{{ $o->items->pluck('name')->implode(', ') ?: '—' }}</td>
                  <td><b>₹{{ number_format((float) $o->total) }}</b></td>
                  <td style="white-space:nowrap;">
                    <a class="fu-link" target="_blank" href="{{ route('sales.order.invoice', $o->token) }}">
                      <i class="fas fa-file-invoice"></i> {{ $o->invoice_no ?: 'View' }}</a>
                    <a class="fu-link" style="margin-left:10px;"
                       href="{{ route('sales.order.invoice.resend', $o->token) }}">Email</a>
                    <div style="font-size:11.5px;color:#8a92a6;margin-top:3px;">
                      {{ $o->invoice_sent_at ? 'Sent '.$o->invoice_sent_at->format('d M Y') : 'Not emailed yet' }}
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>

      {{-- ---- Packages ---- --}}
      <div class="fu-pane" id="pPackages">
        @if($detail['packages']->isEmpty())
          <div class="fu-none"><i class="fas fa-box-open"></i> This business has not bought any package yet.</div>
        @else
          <div class="fu-cards">
            @foreach($detail['packages'] as $p)
              @php $live = $p->expires_at && $p->expires_at->isFuture(); @endphp
              <div class="fu-pkg">
                <span class="st {{ $live ? 'on' : 'off' }}">{{ $live ? 'Active' : 'Expired' }}</span>
                <b>₹{{ number_format((float) $p->price) }}</b>
                <small>{{ optional($p->package)->name ?: $p->plan }}</small>
                <small>{{ $p->plan }}@if($p->area) • {{ $p->area }}@endif</small>
                <small>
                  {{ $p->starts_at?->format('d M y') }} – {{ $p->expires_at?->format('d M y') }}
                  @if($live) ({{ (int) now()->diffInDays($p->expires_at) }} days left) @endif
                </small>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- ---- Activity ---- --}}
      <div class="fu-pane" id="pActivity">
        @php $history = $detail['history']; @endphp
        @if($history->isEmpty())
          <div class="fu-none"><i class="fas fa-clock-rotate-left"></i> No activity recorded yet.</div>
        @else
          <table class="fu-tbl">
            <thead><tr><th>When</th><th>Disposition</th><th>Due</th><th>By</th><th>Note</th></tr></thead>
            <tbody>
              @foreach($history as $h)
                <tr>
                  <td>{{ $h->updated_at?->format('d M Y, h:i A') }}</td>
                  <td>{{ $h->disposition }}</td>
                  <td>{{ $h->due_at?->format('d M Y, h:i A') ?: '—' }}</td>
                  <td>{{ optional($h->salesUser)->name ?: '—' }}</td>
                  <td>{{ $h->note ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    @endif
  </div>
</div>

{{-- ================= Filters drawer ================= --}}
<div class="fu-scrim" id="filterScrim" onclick="if(event.target.id==='filterScrim')closeFilters()">
  <div class="fu-panel">
    <form method="get" id="filterForm">
      <input type="hidden" name="q" value="{{ $search }}">
      <input type="hidden" name="by" value="{{ $searchBy }}">
      <input type="hidden" name="due" value="{{ $dueFilter }}">
      <input type="hidden" name="from" id="fFrom" value="{{ request('from') }}">
      <input type="hidden" name="to" id="fTo" value="{{ request('to') }}">
    </form>

    <div class="fu-phead">
      <h3>Filters</h3>
      <button type="button" class="fu-pclose" onclick="closeFilters()">&times;</button>
    </div>

    <div class="fu-pbody">
      <p class="fu-plabel">Select Duration</p>
      <p style="margin:-6px 0 12px;font-size:12px;color:#8a92a6;">
        {{ $isListing ? 'Filters by the date the business was added.' : 'Filters by the follow-up due date.' }}
      </p>
      <div class="fu-chips">
        <button type="button" class="fu-chip is-on" id="chipCustom" onclick="pickSpan(this,'custom')">Custom</button>
        <button type="button" class="fu-chip" onclick="pickSpan(this,1)">1 Month</button>
        <button type="button" class="fu-chip" onclick="pickSpan(this,3)">3 Months</button>
      </div>

      <div class="fu-range" id="rangeText">Pick a start and end date below</div>

      <div class="cal-head">
        <b id="calMonth"></b>
        <span class="cal-nav">
          <button type="button" onclick="shiftMonth(-1)"><i class="fas fa-chevron-left"></i></button>
          <button type="button" onclick="shiftMonth(1)"><i class="fas fa-chevron-right"></i></button>
        </span>
      </div>
      <table class="cal">
        <thead><tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr></thead>
        <tbody id="calBody"></tbody>
      </table>

      <div style="height:22px;"></div>
      <p class="fu-plabel">Location</p>
      <p class="fu-sub">Area</p>
      <input type="text" id="fArea" value="{{ request('area') }}" placeholder="e.g. Jhotwara">
      <p class="fu-sub">Pincode</p>
      <input type="text" id="fPin" value="{{ request('pincode') }}" placeholder="e.g. 302012">
    </div>

    <div class="fu-pfoot">
      <button type="button" class="reset" onclick="resetFilters()">Reset Filters</button>
      <button type="button" class="apply" onclick="applyFilters()">Apply</button>
    </div>
  </div>
</div>

<script>
  function toggleBox(ev, id) {
    ev.stopPropagation();
    var box = document.getElementById(id);
    var wasOpen = box.classList.contains('open');
    closeBoxes();
    if (!wasOpen) box.classList.add('open');
  }
  function closeBoxes() {
    var el = document.getElementById('dueDrop');
    if (el) el.classList.remove('open');
  }
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.fu-drop')) closeBoxes();
  });

  /* ---------------- Filters drawer ---------------- */

  var fFrom = @json(request('from')), fTo = @json(request('to'));
  var calCursor = new Date();
  var MONTHS = ['January','February','March','April','May','June',
                'July','August','September','October','November','December'];

  function openFilters() {
    var s = document.getElementById('filterScrim');
    s.classList.add('show');
    requestAnimationFrame(function () { s.classList.add('open'); });
    if (fFrom) calCursor = new Date(fFrom);
    drawCal();
  }
  function closeFilters() {
    var s = document.getElementById('filterScrim');
    s.classList.remove('open');
    setTimeout(function () { s.classList.remove('show'); }, 240);
  }

  function ymd(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  function pretty(s) {
    if (!s) return '';
    var d = new Date(s);
    return d.getDate() + ' ' + MONTHS[d.getMonth()].slice(0, 3) + ' ' + d.getFullYear();
  }

  // A ready-made window, counted forward from today.
  function pickSpan(btn, months) {
    document.querySelectorAll('.fu-chips .fu-chip').forEach(function (c) { c.classList.remove('is-on'); });
    btn.classList.add('is-on');

    if (months === 'custom') { fFrom = null; fTo = null; drawCal(); return; }

    // Follow-ups are due ahead of today; businesses were added before it.
    var back = @json($isListing);
    var start = new Date();
    var end = new Date();

    if (back) { start.setMonth(start.getMonth() - months); }
    else      { end.setMonth(end.getMonth() + months); }

    fFrom = ymd(start);
    fTo = ymd(end);
    calCursor = new Date(start);
    drawCal();
  }

  function shiftMonth(step) {
    calCursor.setMonth(calCursor.getMonth() + step);
    drawCal();
  }

  function drawCal() {
    document.getElementById('calMonth').textContent =
      MONTHS[calCursor.getMonth()] + ' ' + calCursor.getFullYear();

    document.getElementById('rangeText').textContent =
      (fFrom || fTo) ? (pretty(fFrom) || '…') + ' – ' + (pretty(fTo) || '…')
                     : 'Pick a start and end date below';

    var first = new Date(calCursor.getFullYear(), calCursor.getMonth(), 1);
    var start = new Date(first);
    start.setDate(1 - first.getDay());          // back to the Sunday before

    var body = document.getElementById('calBody');
    body.innerHTML = '';

    for (var w = 0; w < 6; w++) {
      var tr = document.createElement('tr');

      for (var d = 0; d < 7; d++) {
        var day = new Date(start);
        day.setDate(start.getDate() + w * 7 + d);

        var key = ymd(day);
        var td = document.createElement('td');
        var btn = document.createElement('button');

        btn.type = 'button';
        btn.textContent = day.getDate();
        if (day.getMonth() !== calCursor.getMonth()) btn.className = 'mute';
        if (key === fFrom || key === fTo) btn.className = 'pick';
        else if (fFrom && fTo && key > fFrom && key < fTo) td.className = 'in';

        btn.onclick = (function (k) { return function () { chooseDay(k); }; })(key);

        td.appendChild(btn);
        tr.appendChild(td);
      }
      body.appendChild(tr);
    }
  }

  // First click sets the start, second sets the end; a third starts over.
  function chooseDay(key) {
    if (!fFrom || (fFrom && fTo)) { fFrom = key; fTo = null; }
    else if (key < fFrom) { fTo = fFrom; fFrom = key; }
    else { fTo = key; }

    document.querySelectorAll('.fu-chips .fu-chip').forEach(function (c) { c.classList.remove('is-on'); });
    document.getElementById('chipCustom').classList.add('is-on');
    drawCal();
  }

  function applyFilters() {
    var f = document.getElementById('filterForm');
    document.getElementById('fFrom').value = fFrom || '';
    document.getElementById('fTo').value = fTo || '';

    ['area', 'pincode'].forEach(function (name, i) {
      var value = document.getElementById(i === 0 ? 'fArea' : 'fPin').value.trim();
      var input = f.querySelector('[name="' + name + '"]');
      if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        f.appendChild(input);
      }
      input.value = value;
    });

    f.submit();
  }

  function resetFilters() {
    window.location = '{{ route('sales_genio.leads', $tab) }}';
  }

  function showPane(btn, id) {
    document.querySelectorAll('.fu-tabs button').forEach(function (b) { b.classList.remove('is-on'); });
    document.querySelectorAll('.fu-pane').forEach(function (p) { p.classList.remove('is-on'); });
    btn.classList.add('is-on');
    document.getElementById(id).classList.add('is-on');
  }

  // On a phone the two panes do not fit side by side, so opening one from the
  // list shows the detail and the back link returns to the list.
  if (window.matchMedia('(max-width:1050px)').matches && location.hash === '#detail') {
    var d = document.getElementById('detail');
    if (d) { d.classList.add('mobile-open'); d.scrollIntoView(); }
  }
</script>

</body>
</html>
