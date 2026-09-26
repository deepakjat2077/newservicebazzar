<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice {{ $data['number'] }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f3f7;font-family:Arial,Helvetica,sans-serif;color:#1d2433;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f7;padding:26px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0"
             style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;">

        <tr>
          <td style="background:#0d1b2a;padding:20px 26px;color:#ffffff;">
            <div style="font-size:17px;font-weight:bold;">{{ $data['company']['trade_name'] }}</div>
            <div style="font-size:12px;color:#c6cedd;margin-top:3px;">Payment received — invoice attached</div>
          </td>
        </tr>

        <tr>
          <td style="padding:26px;">
            <p style="margin:0 0 14px;font-size:14.5px;">
              Dear {{ $data['buyer']['contact'] ?: $data['buyer']['name'] }},
            </p>

            <p style="margin:0 0 18px;font-size:14px;line-height:1.65;color:#3d4763;">
              Thank you for your payment. Your packages are now active on
              {{ $data['company']['website'] ?: 'Service Bazzar' }}. The tax invoice is attached
              to this email as a PDF.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f7f9fc;border-radius:8px;padding:4px 0;margin-bottom:18px;">
              <tr>
                <td style="padding:9px 16px;font-size:13px;color:#6b7285;">Invoice No</td>
                <td style="padding:9px 16px;font-size:13px;font-weight:bold;text-align:right;">{{ $data['number'] }}</td>
              </tr>
              <tr>
                <td style="padding:9px 16px;font-size:13px;color:#6b7285;">Date</td>
                <td style="padding:9px 16px;font-size:13px;font-weight:bold;text-align:right;">{{ $data['date']?->format('d M Y') }}</td>
              </tr>
              <tr>
                <td style="padding:9px 16px;font-size:13px;color:#6b7285;">Business</td>
                <td style="padding:9px 16px;font-size:13px;font-weight:bold;text-align:right;">{{ $data['buyer']['name'] }}</td>
              </tr>
              <tr>
                <td style="padding:9px 16px;font-size:13px;color:#6b7285;border-top:1px solid #e6eaf1;">Amount Paid</td>
                <td style="padding:9px 16px;font-size:15px;font-weight:bold;text-align:right;color:#1f8a4c;border-top:1px solid #e6eaf1;">
                  ₹{{ number_format($data['total'], 2) }}
                </td>
              </tr>
            </table>

            <p style="margin:0 0 8px;font-size:13.5px;font-weight:bold;color:#0d1b2a;">What you bought</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
              @foreach($data['order']->items as $item)
                <tr>
                  <td style="padding:8px 0;border-bottom:1px solid #eef1f6;font-size:13px;">
                    <b>{{ $item->name }}</b>
                    <div style="color:#7a8296;font-size:12px;margin-top:2px;">{{ $item->plan }} • {{ $item->days }} days</div>
                  </td>
                  <td style="padding:8px 0;border-bottom:1px solid #eef1f6;font-size:13px;text-align:right;white-space:nowrap;">
                    ₹{{ number_format((float) $item->price, 2) }}
                  </td>
                </tr>
              @endforeach
            </table>

            @if(!$data['buyer']['gstin'])
              <p style="margin:0 0 18px;padding:12px 14px;background:#fff8e8;border-radius:8px;
                        font-size:12.5px;color:#8a6412;line-height:1.6;">
                This invoice has been issued without a GSTIN. If your business is GST registered and you
                need input credit, reply to this email with your GST certificate and we will reissue it.
              </p>
            @endif

            <p style="margin:0;font-size:13px;color:#6b7285;line-height:1.7;">
              Any question about this invoice? Write to
              <a href="mailto:{{ $data['company']['email'] }}" style="color:#1a73e8;">{{ $data['company']['email'] }}</a>
              @if($data['company']['phone']) or call {{ $data['company']['phone'] }}@endif.
            </p>
          </td>
        </tr>

        <tr>
          <td style="background:#f7f9fc;padding:16px 26px;font-size:11.5px;color:#8a92a6;line-height:1.6;">
            {{ $data['company']['legal_name'] }}
            @if($data['company']['gstin']) • GSTIN {{ $data['company']['gstin'] }}@endif
            <br>{{ collect([$data['company']['address'], $data['company']['city'], $data['company']['state']])->filter()->implode(', ') }}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
