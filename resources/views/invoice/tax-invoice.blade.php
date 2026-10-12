<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Tax Invoice {{ $number }}</title>
<style>
  @page { margin: 0; }
  * { box-sizing: border-box; }
  /* Helvetica is built into every PDF reader, so nothing is embedded.
     Amounts read "INR 1,180.00" so no rupee glyph is needed. */
  body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5px; color: #2b3445;
         margin: 0; line-height: 1.5; }
  table { width: 100%; border-collapse: collapse; }
  .r { text-align: right; }
  .c { text-align: center; }

  .page { padding: 0 0 46px; }           /* room for the pinned strip */
  .pad  { padding: 0 34px; }

  /* ---------------- brand band ---------------- */
  .band { background: #0d1b3e; padding: 22px 34px 20px; }
  .band td { vertical-align: middle; }
  .band .logo img { width: 168px; }
  .band .brand { color: #fff; font-size: 17px; font-weight: bold; }
  .band .site { color: #9fb0d4; font-size: 8.5px; margin-top: 5px; letter-spacing: .4px; }
  .band .right { text-align: right; }
  .band .kind { color: #fff; font-size: 19px; font-weight: bold; letter-spacing: 3.5px; }
  .band .orig { color: #8fa2c8; font-size: 7.5px; letter-spacing: 2px; margin-top: 3px; }
  .band .paid { display: inline-block; background: #17a45c; color: #fff; border-radius: 3px;
                padding: 4px 16px; font-size: 9px; font-weight: bold; letter-spacing: 2.5px; margin-top: 9px; }
  .accent { height: 3px; background: #e0242f; }

  /* ---------------- invoice meta ---------------- */
  .meta { background: #f4f6fa; }
  .meta td { width: 25%; padding: 11px 34px 11px 0; }
  .meta td:first-child { padding-left: 34px; }
  .meta .k { font-size: 7px; letter-spacing: 1.1px; color: #7e8ba3; text-transform: uppercase; font-weight: bold; }
  .meta .v { font-size: 10px; font-weight: bold; color: #0d1b3e; margin-top: 3px; word-break: break-all; }

  /* ---------------- parties ---------------- */
  .parties { margin-top: 20px; }
  .parties > tbody > tr > td { width: 50%; vertical-align: top; }
  .parties .pad-r { padding-right: 9px; }
  .parties .pad-l { padding-left: 9px; }
  .box { border: 1px solid #dfe4ee; border-radius: 4px; }
  .box .hd { background: #f4f6fa; padding: 7px 13px; font-size: 7.5px; letter-spacing: 1.3px;
             color: #5f6c85; text-transform: uppercase; font-weight: bold;
             border-bottom: 1px solid #dfe4ee; }
  .box .bd { padding: 12px 13px 13px; }
  .party-name { font-size: 11.5px; font-weight: bold; color: #0d1b3e; }
  .party-line { font-size: 8.8px; color: #5f6c85; margin-top: 4px; }

  .facts { margin-top: 9px; border-top: 1px solid #eef1f6; }
  .facts td { padding: 3px 0 !important; font-size: 8.8px; vertical-align: top; border: 0; }
  .facts tr:first-child td { padding-top: 8px !important; }
  .facts td.k { width: 62px; color: #8b97ad; }
  .facts td.v { color: #182338; font-weight: bold; word-break: break-all; }
  .muted { color: #a3abbb; font-style: italic; font-weight: normal !important; }

  /* ---------------- items ---------------- */
  .items { margin-top: 20px; border: 1px solid #dfe4ee; border-radius: 4px; }
  .items th { background: #0d1b3e; color: #fff; font-size: 7.5px; text-transform: uppercase;
              letter-spacing: .9px; padding: 9px 13px; text-align: left; font-weight: bold; }
  .items th.r { text-align: right; }
  .items th.c { text-align: center; }
  .items td { padding: 9px 13px; border-bottom: 1px solid #eef1f6; font-size: 9.5px; vertical-align: top; }
  .items tbody tr:last-child td { border-bottom: 0; }
  .it-name { font-weight: bold; color: #0d1b3e; font-size: 10px; }
  .it-sub { color: #7e8ba3; font-size: 8.3px; margin-top: 2px; }

  /* ---------------- summary ---------------- */
  .sum { margin-top: 16px; }
  .sum > tbody > tr > td { vertical-align: top; }
  .sum .left { width: 54%; padding-right: 14px; }
  .sum .right { width: 46%; }

  .totals { border: 1px solid #dfe4ee; border-radius: 4px; }
  .totals td { padding: 7px 13px; font-size: 9.5px; border-bottom: 1px solid #eef1f6; }
  .totals td.k { color: #5f6c85; }
  .totals td.v { text-align: right; font-weight: bold; color: #182338; width: 92px; }
  .totals tr.grand td { background: #0d1b3e; color: #fff; font-size: 11.5px; padding: 11px 13px; border: 0; }

  .words { border-left: 3px solid #e0242f; background: #fafbfd; padding: 9px 13px; }
  .words > .k { font-size: 7.5px; letter-spacing: 1.1px; color: #8b97ad; text-transform: uppercase; font-weight: bold; }
  .words b { display: block; color: #0d1b3e; font-size: 10px; margin-top: 3px; }
  .bank { margin-top: 10px; border: 1px solid #dfe4ee; border-radius: 4px; padding: 10px 13px; }
  .bank > .k { font-size: 7.5px; letter-spacing: 1.1px; color: #8b97ad; text-transform: uppercase; font-weight: bold; }

  /* ---------------- sign-off ---------------- */
  .foot-in { margin-top: 22px; border-top: 1px solid #dfe4ee; }
  .foot-in td { vertical-align: bottom; padding-top: 12px; }
  .foot-in .terms { width: 60%; padding-right: 22px; }
  .foot-in .cap { font-size: 7.5px; letter-spacing: 1.1px; color: #8b97ad;
                  text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
  .foot-in ol { margin: 0; padding-left: 13px; font-size: 8.3px; color: #7e8ba3; }
  .foot-in ol li { margin-bottom: 2px; }
  .foot-in .sign { text-align: right; }
  .sign-for { font-size: 8.3px; color: #8b97ad; }
  .sign-co { font-size: 9.5px; font-weight: bold; color: #0d1b3e; margin-top: 1px; }
  .sign-line { margin-top: 34px; border-top: 1px solid #b6c0d2; display: inline-block;
               padding-top: 4px; font-size: 8.3px; color: #7e8ba3; min-width: 158px; }
  .thanks { margin-top: 16px; text-align: center; font-size: 9px; color: #8b97ad; letter-spacing: .4px; }
  .strip { position: fixed; left: 0; right: 0; bottom: 0; background: #0d1b3e; color: #8fa2c8; font-size: 7.8px; text-align: center; padding: 8px 34px; }
  .strip b { color: #fff; font-weight: normal; }
</style>
</head>
<body>

{{-- ================= contact strip, pinned to every page ================= --}}
<div class="strip">
  Advertising services on <b>{{ $company['website'] ?: 'Service Bazzar' }}</b>
  &nbsp;&middot;&nbsp; {{ $company['email'] }}@if(!empty($company['phone'])) &nbsp;&middot;&nbsp; {{ $company['phone'] }}@endif
  &nbsp;&middot;&nbsp; Computer generated invoice
</div>

<div class="page">

  {{-- ================= brand band ================= --}}
  <table class="band">
    <tr>
      <td class="logo">
        @if(!empty($company['logo_light']) && is_file(public_path($company['logo_light'])))
          <img src="{{ public_path($company['logo_light']) }}" alt="">
        @else
          <div class="brand">{{ $company['trade_name'] ?: $company['legal_name'] }}</div>
        @endif
        <div class="site">{{ $company['website'] }}</div>
      </td>
      <td class="right">
        <div class="kind">{{ !empty($company['gstin']) ? 'TAX INVOICE' : 'INVOICE' }}</div>
        <div class="orig">ORIGINAL FOR RECIPIENT</div>
        <div class="paid">PAID</div>
      </td>
    </tr>
  </table>
  <div class="accent"></div>

  {{-- ================= meta strip ================= --}}
  <table class="meta">
    <tr>
      <td><div class="k">Invoice No</div><div class="v">{{ $number }}</div></td>
      <td><div class="k">Invoice Date</div><div class="v">{{ $date?->format('d M Y') }}</div></td>
      <td><div class="k">Place of Supply</div><div class="v">{{ $buyer['state'] ?: ($company['state'] ?? '-') }}</div></td>
      <td><div class="k">Payment Ref</div><div class="v">{{ $order->gateway_payment_id ?: ('Order #' . $order->id) }}</div></td>
    </tr>
  </table>

  <div class="pad">

    {{-- ================= supplier / buyer ================= --}}
    <table class="parties">
      <tr>
        <td class="pad-r">
          <div class="box">
            <div class="hd">Supplier</div>
            <div class="bd">
              <div class="party-name">{{ $company['legal_name'] }}</div>
              <div class="party-line">
                @if(!empty($company['address'])){{ $company['address'] }}<br>@endif
                {{ collect([$company['city'] ?? null, $company['state'] ?? null, $company['pincode'] ?? null])->filter()->implode(', ') }}
              </div>
              <table class="facts">
                @if(!empty($company['gstin']))<tr><td class="k">GSTIN</td><td class="v">{{ $company['gstin'] }}</td></tr>@endif
                @if(!empty($company['cin']))<tr><td class="k">CIN</td><td class="v">{{ $company['cin'] }}</td></tr>@endif
                @if(!empty($company['pan']))<tr><td class="k">PAN</td><td class="v">{{ $company['pan'] }}</td></tr>@endif
                @if(!empty($company['phone']))<tr><td class="k">Mobile</td><td class="v">{{ $company['phone'] }}</td></tr>@endif
                @if(!empty($company['email']))<tr><td class="k">Email</td><td class="v">{{ $company['email'] }}</td></tr>@endif
              </table>
            </div>
          </div>
        </td>

        <td class="pad-l">
          <div class="box">
            <div class="hd">Bill To / Recipient</div>
            <div class="bd">
              <div class="party-name">{{ $buyer['name'] }}</div>
              <div class="party-line">
                @if($buyer['contact']){{ $buyer['contact'] }}<br>@endif
                @if($buyer['address']){{ $buyer['address'] }}<br>@endif
                {{ collect([$buyer['city'], $buyer['state'], $buyer['pincode']])->filter()->implode(', ') }}
              </div>
              <table class="facts">
                <tr>
                  <td class="k">GSTIN</td>
                  <td class="v">@if($buyer['gstin']){{ $buyer['gstin'] }}@else<span class="muted">Not registered</span>@endif</td>
                </tr>
                <tr>
                  <td class="k">PAN</td>
                  <td class="v">@if(!empty($buyer['pan'])){{ $buyer['pan'] }}@else<span class="muted">Not provided</span>@endif</td>
                </tr>
                @if($buyer['phone'])<tr><td class="k">Mobile</td><td class="v">{{ $buyer['phone'] }}</td></tr>@endif
                @if($buyer['email'])<tr><td class="k">Email</td><td class="v">{{ $buyer['email'] }}</td></tr>@endif
              </table>
            </div>
          </div>
        </td>
      </tr>
    </table>

    {{-- ================= items ================= --}}
    <table class="items">
      <thead>
        <tr>
          <th style="width:24px;" class="c">#</th>
          <th>Description of Service</th>
          <th style="width:58px;" class="c">SAC</th>
          <th style="width:118px;" class="c">Validity</th>
          <th style="width:86px;" class="r">Amount (INR)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items->reject(fn ($it) => !empty($it->is_free))->values() as $i => $item)
          <tr>
            <td class="c">{{ $i + 1 }}</td>
            <td>
              <div class="it-name">{{ $item->name }}</div>
              <div class="it-sub">
                {{ $item->plan }}
                @php $m = $item->meta ?? []; @endphp
                @if(!empty($m['area'])) &middot; {{ $m['area'] }}@endif
                @if(!empty($m['city'])) &middot; {{ $m['city'] }}@endif
              </div>
            </td>
            <td class="c">{{ $sac }}</td>
            <td class="c">
              @php
                // The package runs from the day the payment landed.
                $from = $date?->copy();
                $till = $from?->copy()->addDays((int) $item->days);
              @endphp
              {{ $from?->format('d M y') }} &ndash; {{ $till?->format('d M y') }}
              <div class="it-sub" style="text-align:center;">{{ $item->days }} days</div>
            </td>
            <td class="r">{{ number_format((float) $item->price, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- ================= summary ================= --}}
    <table class="sum">
      <tr>
        <td class="left">
          <div class="words">
            <div class="k">Amount in words</div>
            <b>{{ $inWords }}</b>
          </div>

          @if(!empty($bank))
            <div class="bank">
              <div class="k">Bank Details</div>
              <table class="facts" style="margin-top:4px;border-top:0;">
                @if(!empty($bank['name']))<tr><td class="k">Bank</td><td class="v">{{ $bank['name'] }}</td></tr>@endif
                @if(!empty($bank['account']))<tr><td class="k">A/c No</td><td class="v">{{ $bank['account'] }}</td></tr>@endif
                @if(!empty($bank['ifsc']))<tr><td class="k">IFSC</td><td class="v">{{ $bank['ifsc'] }}</td></tr>@endif
                @if(!empty($bank['branch']))<tr><td class="k">Branch</td><td class="v">{{ $bank['branch'] }}</td></tr>@endif
              </table>
            </div>
          @endif
        </td>

        <td class="right">
          <table class="totals">
            <tr>
              <td class="k">Taxable Value</td>
              <td class="v">{{ number_format($taxable, 2) }}</td>
            </tr>
            @if($intraState)
              <tr>
                <td class="k">CGST @ {{ rtrim(rtrim(number_format($rate / 2, 2, '.', ''), '0'), '.') }}%</td>
                <td class="v">{{ number_format($cgst, 2) }}</td>
              </tr>
              <tr>
                <td class="k">SGST @ {{ rtrim(rtrim(number_format($rate / 2, 2, '.', ''), '0'), '.') }}%</td>
                <td class="v">{{ number_format($sgst, 2) }}</td>
              </tr>
            @else
              <tr>
                <td class="k">IGST @ {{ rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.') }}%</td>
                <td class="v">{{ number_format($igst, 2) }}</td>
              </tr>
            @endif
            @if(!empty($discount))
              <tr>
                <td class="k">Less: Discount{{ $discountName ? ' (' . $discountName . ')' : '' }}</td>
                <td class="v">−{{ number_format($discount, 2) }}</td>
              </tr>
            @endif
            <tr class="grand">
              <td class="k" style="color:#fff;">Total Paid (INR)</td>
              <td class="v" style="color:#fff;">{{ number_format($total, 2) }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    {{-- ================= sign-off ================= --}}
    <table class="foot-in">
      <tr>
        <td class="terms">
          <div class="cap">Terms &amp; Conditions</div>
          <ol>
            @foreach($terms as $t)<li>{{ $t }}</li>@endforeach
          </ol>
        </td>
        <td class="sign">
          <div class="sign-for">For</div>
          <div class="sign-co">{{ $company['legal_name'] }}</div>
          <div class="sign-line">Authorised Signatory</div>
        </td>
      </tr>
    </table>

    <div class="thanks">Thank you for your business.</div>
  </div>
</div>

</body>
</html>
