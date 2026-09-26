<?php

namespace App\Support;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The tax invoice for a paid sales order: allocates the number, works out the
 * GST split, renders the PDF and emails it to the business.
 *
 * Nothing here ever changes an invoice that has already been issued — the
 * number and the buyer's GSTIN are frozen onto the order the first time.
 */
class Invoice
{
    /**
     * The invoice number, allocated once. Series runs per financial year:
     * SB/26-27/0001. Taken under a lock so two payments landing together
     * cannot be given the same number.
     */
    public static function numberFor(SalesOrder $order): string
    {
        if ($order->invoice_no) {
            return $order->invoice_no;
        }

        $paid = $order->paid_at ?: now();
        // The Indian financial year starts in April.
        $startYear = $paid->month >= 4 ? $paid->year : $paid->year - 1;
        $series = sprintf('%02d-%02d', $startYear % 100, ($startYear + 1) % 100);
        $prefix = config('invoice.prefix', 'SB') . '/' . $series . '/';

        return DB::transaction(function () use ($order, $prefix) {
            $locked = SalesOrder::where('id', $order->id)->lockForUpdate()->first();

            if ($locked->invoice_no) {
                return $locked->invoice_no;
            }

            $last = SalesOrder::where('invoice_no', 'like', $prefix . '%')
                ->orderByDesc('invoice_no')
                ->value('invoice_no');

            $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            $number = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);

            $locked->update(['invoice_no' => $number]);
            $order->invoice_no = $number;

            return $number;
        });
    }

    /** The buyer's GSTIN, from their approved GST certificate on file. */
    public static function buyerGstin(SalesOrder $order): ?string
    {
        if ($order->buyer_gstin) {
            return $order->buyer_gstin;
        }

        return self::kycNumber($order->listing_id, 'gst');
    }

    /** A number off one of the business's KYC documents, approved ones first. */
    private static function kycNumber(int $listingId, string $type): ?string
    {
        $number = \App\Models\ListingKycDocument::where('listing_id', $listingId)
            ->where('doc_type', $type)
            ->whereNotNull('doc_number')
            ->where('doc_number', '!=', '')
            ->orderByRaw("CASE WHEN LOWER(status) = 'approved' THEN 0 ELSE 1 END")
            ->value('doc_number');

        return $number ? strtoupper(trim($number)) : null;
    }

    /** Everything the invoice template prints. */
    public static function data(SalesOrder $order): array
    {
        $order->loadMissing(['items', 'listing', 'salesUser']);

        $company = config('invoice.company');
        $listing = $order->listing;
        $rate    = (float) config('invoice.gst_rate', 18);

        $buyerState = trim((string) ($listing->state ?? ''));

        // Same state as the seller means the tax is split CGST + SGST;
        // anywhere else it is a single IGST line.
        $intraState = $buyerState !== ''
            && strcasecmp($buyerState, (string) ($company['state'] ?? '')) === 0;

        $taxable = (float) $order->subtotal;
        $tax     = (float) $order->gst;

        $address = collect([
            $listing->plot_no ?? null,
            $listing->building_name ?? null,
            $listing->street_name ?? null,
            $listing->landmark ?? null,
            $listing->area ?? null,
        ])->filter()->implode(', ');

        return [
            'order'      => $order,
            'number'     => self::numberFor($order),
            'date'       => $order->paid_at ?: $order->created_at,
            'company'    => $company,
            'bank'       => array_filter((array) config('invoice.bank')),
            'sac'        => config('invoice.sac'),
            'terms'      => config('invoice.terms', []),
            'buyer'      => [
                'name'    => $listing->name ?? '',
                'contact' => trim(($listing->contact_title ? $listing->contact_title . ' ' : '') . ($listing->contact_person ?? '')),
                'address' => $address,
                'city'    => $listing->city ?? '',
                'state'   => $buyerState,
                'pincode' => $listing->pincode ?? '',
                'phone'   => $listing->mobile ?? '',
                'email'   => $listing->email ?? '',
                'gstin'   => self::buyerGstin($order),
                'pan'     => self::kycNumber($order->listing_id, 'pan'),
            ],
            'intraState' => $intraState,
            'rate'       => $rate,
            'taxable'    => $taxable,
            'cgst'       => $intraState ? round($tax / 2, 2) : 0.0,
            'sgst'       => $intraState ? round($tax / 2, 2) : 0.0,
            'igst'       => $intraState ? 0.0 : $tax,
            'tax'        => $tax,
            'total'      => (float) $order->total,
            'inWords'    => self::words((float) $order->total),
        ];
    }

    /** Renders the invoice PDF and returns the raw bytes. */
    public static function pdf(SalesOrder $order): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.tax-invoice', self::data($order))
            ->setPaper('a4');

        return $pdf->output();
    }

    /** SB-26-27-0001.pdf */
    public static function filename(SalesOrder $order): string
    {
        return 'Invoice-' . str_replace('/', '-', self::numberFor($order)) . '.pdf';
    }

    /**
     * Emails the invoice to the business. Safe to call more than once — it
     * stops if the invoice has already gone out, and never throws, because a
     * mail problem must not disturb a payment that has already succeeded.
     */
    public static function send(SalesOrder $order, bool $force = false): bool
    {
        if (!$order->isPaid()) {
            return false;
        }

        if ($order->invoice_sent_at && !$force) {
            return false;
        }

        $order->loadMissing('listing');

        $to = collect([$order->listing->email ?? null])
            ->merge((array) ($order->listing->extra_emails ?? []))
            ->map(fn ($e) => trim((string) $e))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($to->isEmpty()) {
            Log::warning('Invoice not sent: the business has no email address', ['order' => $order->id]);

            return false;
        }

        try {
            // Freeze what was true at the moment of issue.
            $order->forceFill([
                'buyer_gstin'   => $order->buyer_gstin ?: self::buyerGstin($order),
                'invoice_email' => $to->first(),
            ])->save();

            Mail::to($to->first())
                ->cc($to->slice(1)->all())
                ->send(new \App\Mail\InvoiceMail($order));

            $order->forceFill(['invoice_sent_at' => now()])->save();

            return true;
        } catch (\Throwable $e) {
            Log::error('Invoice email failed', ['order' => $order->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /** ₹1,180 → "One Thousand One Hundred Eighty Rupees Only" (Indian units). */
    public static function words(float $amount): string
    {
        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);

        $text = self::numberToWords($rupees) . ' Rupees';

        if ($paise > 0) {
            $text .= ' and ' . self::numberToWords($paise) . ' Paise';
        }

        return $text . ' Only';
    }

    private static function numberToWords(int $n): string
    {
        if ($n === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
                 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $under100 = function (int $x) use ($ones, $tens) {
            if ($x < 20) {
                return $ones[$x];
            }

            return trim($tens[intdiv($x, 10)] . ' ' . $ones[$x % 10]);
        };

        $parts = [];

        // Crore, lakh, thousand, hundred — the Indian grouping.
        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $value => $label) {
            if ($n >= $value) {
                $count = intdiv($n, $value);
                $n %= $value;
                $parts[] = ($value >= 10000000 ? self::numberToWords($count) : $under100($count)) . ' ' . $label;
            }
        }

        if ($n > 0) {
            $parts[] = $under100($n);
        }

        return implode(' ', $parts);
    }
}
