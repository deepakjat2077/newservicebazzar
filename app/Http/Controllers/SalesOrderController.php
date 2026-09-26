<?php

namespace App\Http\Controllers;

use App\Models\CertificatePackage;
use App\Models\Listing;
use App\Models\PaymentMethod;
use App\Models\SalesOrder;
use App\Support\SalesPerks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The customer's side of a sales payment link: open it, see exactly what the
 * rep quoted, pay, and every package on the order switches on. No login — the
 * token in the URL is what identifies the order.
 */
class SalesOrderController extends Controller
{
    /** Same page, for templates that can only put the token in a query string. */
    public function showByQuery(Request $request)
    {
        return $this->show((string) $request->query('t', $request->query('token')));
    }

    public function show($token)
    {
        $order = SalesOrder::where('token', $token)->with(['items.package', 'listing', 'salesUser'])->firstOrFail();

        // Only gateways that are enabled and actually wired up.
        $enabled = \App\Models\MethodCurrency::where('enable_status', 1)
            ->whereHas('paymentMethod', fn ($q) => $q->whereIn('act', ['razorpay', 'cashfree']))
            ->with('paymentMethod')->get()
            ->pluck('paymentMethod')->unique('act');

        $gateways = $enabled->filter(fn ($m) => $m->act === 'razorpay' && !empty($m->setting->key_id))->values();
        $pending = $enabled->reject(fn ($m) => $m->act === 'razorpay')->values();

        return view('themes.servicebazzar.sales_order.pay', compact('order', 'gateways', 'pending'));
    }

    /** The invoice PDF, opened from the paid page or from an emailed link. */
    public function invoice($token)
    {
        $order = SalesOrder::where('token', $token)->with(['items', 'listing'])->firstOrFail();

        if (!$order->isPaid()) {
            abort(404);
        }

        return response(\App\Support\Invoice::pdf($order), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \App\Support\Invoice::filename($order) . '"',
        ]);
    }

    /** Sends the invoice again, to the business's own email. */
    public function resendInvoice($token)
    {
        $order = SalesOrder::where('token', $token)->with('listing')->firstOrFail();

        if (!$order->isPaid()) {
            abort(404);
        }

        $sent = \App\Support\Invoice::send($order, true);

        return back()->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Invoice emailed to ' . $order->fresh()->invoice_email
                : 'Could not send the invoice — check that the business has a valid email address.'
        );
    }

    /** Creates the gateway order right before the checkout modal opens. */
    public function createPayment(Request $request, $token)
    {
        $order = SalesOrder::where('token', $token)->firstOrFail();

        if ($order->isPaid()) {
            return response()->json(['success' => false, 'error' => 'This order is already paid.'], 409);
        }

        $method = PaymentMethod::where('act', 'razorpay')->first();

        if (!$method || empty($method->setting->key_id)) {
            return response()->json(['success' => false, 'error' => 'Razorpay is not configured.'], 500);
        }

        try {
            $api = new \Razorpay\Api\Api($method->setting->key_id, $method->setting->key_secret);

            $rzOrder = $api->order->create([
                'receipt'         => 'SO' . $order->id,
                'amount'          => (int) round($order->total * 100),
                'currency'        => 'INR',
                'payment_capture' => 1,
            ]);

            $order->update(['gateway' => 'razorpay', 'gateway_order_id' => $rzOrder->id]);

            return response()->json([
                'success'  => true,
                'key'      => $method->setting->key_id,
                'order_id' => $rzOrder->id,
                'amount'   => (int) round($order->total * 100),
                'name'     => optional($order->listing)->name,
                'contact'  => optional($order->listing)->mobile,
                'email'    => optional($order->listing)->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Sales order Razorpay create failed', ['order' => $order->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => 'Could not start the payment.'], 500);
        }
    }

    /** Verifies the signature, then switches every package on. */
    public function confirm(Request $request, $token)
    {
        $order = SalesOrder::where('token', $token)->with('items.package')->firstOrFail();

        $data = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        if ($order->isPaid()) {
            return response()->json(['success' => true, 'redirect' => route('sales.order.pay', $order->token)]);
        }

        $method = PaymentMethod::where('act', 'razorpay')->first();

        try {
            $api = new \Razorpay\Api\Api($method->setting->key_id, $method->setting->key_secret);
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature'  => $data['razorpay_signature'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Sales order signature failed', ['order' => $order->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => 'Payment could not be verified.'], 422);
        }

        $this->activate($order, $data['razorpay_payment_id']);

        // The invoice goes out once the customer already has their answer —
        // a slow mail server must never hold up a payment that has succeeded.
        app()->terminating(function () use ($order) {
            \App\Support\Invoice::send($order->fresh());
        });

        return response()->json(['success' => true, 'redirect' => route('sales.order.pay', $order->token)]);
    }

    /**
     * Marks the order paid once, and hands each package to the same activation
     * the normal checkout uses.
     */
    private function activate(SalesOrder $order, string $paymentId): void
    {
        DB::transaction(function () use ($order, $paymentId) {
            $locked = SalesOrder::where('id', $order->id)->lockForUpdate()->first();

            if (!$locked || $locked->isPaid()) {
                return;
            }

            $locked->update([
                'status'             => SalesOrder::PAID,
                'gateway_payment_id' => $paymentId,
                'paid_at'            => now(),
            ]);

            $listing = Listing::find($locked->listing_id);

            if (!$listing) {
                return;
            }

            // The rep's board should show this as a closed deal.
            if ($locked->sales_user_id) {
                \App\Models\ListingDisposition::updateOrCreate(
                    ['listing_id' => $listing->id, 'disposition' => 'Paid Client'],
                    [
                        'sales_user_id' => $locked->sales_user_id,
                        'note'          => 'Order #' . $locked->id . ' paid — ₹' . number_format((float) $locked->total),
                        'updated_at'    => now(),
                    ]
                );
            }

            foreach ($locked->items()->with('package')->get() as $item) {
                $package = $item->package ?? CertificatePackage::find($item->certificate_package_id);

                if (!$package) {
                    continue;
                }

                $meta = $item->meta ?? [];

                // A position campaign books one slot per area it was sold for.
                $positions = $meta['positions'] ?? [];

                SalesPerks::activate($listing, $package, [
                    'label'    => $item->plan ?: 'Plan',
                    'price'    => $item->price,
                    'days'     => $item->days,
                    'city'     => $meta['city'] ?? null,
                    'area'     => $meta['area'] ?? null,
                    'position' => null,
                ]);

                $bookedPosition = false;

                foreach ($positions as $pincode => $tier) {
                    $bookedPosition = true;
                    \App\Models\ListingPositionSlot::updateOrCreate([
                        'certificate_package_id' => $package->id,
                        'city'                   => $meta['city'] ?? $listing->city,
                        'pincode'                => $pincode,
                        'tier'                   => $tier,
                        'category_id'            => $listing->category_id,
                    ], [
                        'listing_id' => $listing->id,
                        'area'       => null,
                        'starts_at'  => now(),
                        'expires_at' => now()->addDays((int) $item->days),
                    ]);
                }

                // Lift the listing in search to the best position it now holds.
                if ($bookedPosition) {
                    SalesPerks::syncTopRank($listing->id);
                }
            }
        });
    }
}
