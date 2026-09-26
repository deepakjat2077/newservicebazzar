<?php

/*
|--------------------------------------------------------------------------
| Tax invoice
|--------------------------------------------------------------------------
|
| Who the invoice is issued BY. These are printed on every invoice that goes
| out, so they must match the registration exactly — a tax invoice with the
| wrong legal name or GSTIN is not a valid one.
|
| These are only the fallbacks. The live values are edited in the admin at
| Setting > Invoice and pushed in over the top by AppServiceProvider, so
| nothing here needs a deploy to change.
|
| Anything left blank is simply left off the invoice rather than printed
| empty, so the document still reads properly while details are pending.
|
*/

return [

    'company' => [
        'legal_name' => env('INVOICE_LEGAL_NAME', 'Service Bazzar'),
        'trade_name' => env('INVOICE_TRADE_NAME', 'Service Bazzar'),
        'address'    => env('INVOICE_ADDRESS', ''),
        'city'       => env('INVOICE_CITY', 'Jaipur'),
        'state'      => env('INVOICE_STATE', 'Rajasthan'),
        'state_code' => env('INVOICE_STATE_CODE', '08'),      // Rajasthan
        'pincode'    => env('INVOICE_PINCODE', ''),
        'gstin'      => env('INVOICE_GSTIN', ''),
        'cin'        => env('INVOICE_CIN', ''),
        'pan'        => env('INVOICE_PAN', ''),
        'phone'      => env('INVOICE_PHONE', ''),
        'email'      => env('INVOICE_EMAIL', 'info@servicebazzar.com'),
        'website'    => env('INVOICE_WEBSITE', 'www.servicebazzar.com'),
        // Downscaled copies — dompdf embeds the image as-is, and the full-size
        // logo alone made every attachment 1.5 MB. The light one sits on the
        // dark band at the top of the invoice.
        'logo'       => env('INVOICE_LOGO', 'assets/images/logo/logo-invoice.png'),
        'logo_light' => env('INVOICE_LOGO_LIGHT', 'assets/images/logo/logo-invoice-light.png'),
    ],

    // Left blank until the bank details are confirmed; the block is hidden.
    'bank' => [
        'name'     => env('INVOICE_BANK_NAME', ''),
        'account'  => env('INVOICE_BANK_ACCOUNT', ''),
        'ifsc'     => env('INVOICE_BANK_IFSC', ''),
        'branch'   => env('INVOICE_BANK_BRANCH', ''),
    ],

    // 998365 — "sale of advertising space / time". Change it if the
    // accountant classifies these packages differently.
    'sac'  => env('INVOICE_SAC', '998365'),

    // Prices quoted to the customer are exclusive of GST and 18% is added on
    // top, which is what the cart and the payment link already do.
    'gst_rate' => (float) env('INVOICE_GST_RATE', 18),

    // SB/26-27/0001
    'prefix' => env('INVOICE_PREFIX', 'SB'),

    'terms' => [
        'Packages start from the date the payment is received.',
        'Fees once paid are not refundable.',
        'This is a computer generated invoice and needs no signature.',
    ],
];
