<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MethodCurrency;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Buying a plan from the mobile app, through whichever gateways the admin has
 * turned on — no gateway is hardcoded here. The admin enables payment methods
 * exactly as they do for the website (PaymentMethod > MethodCurrency,
 * enable_status = 1), and this simply lists whatever is enabled and starts a
 * payment against it.
 *
 * The actual gateway handshake is NOT reimplemented: POST /deposit creates the
 * Payment row (same charge maths as DepositController@depositSubmit) and hands
 * back a `checkout_url` the app opens in a web-view. That URL runs the site's
 * existing gateway flow (DepositController@appCheckout -> the PaymentGateway
 * trait), so Razorpay / Paytm / Instamojo / Stripe / manual — every method the
 * admin can configure — works through the one code path the website already
 * uses, and the gateways' existing IPN callbacks update the payment status.
 *
 * Behind `user.api`.
 */
class PaymentController extends Controller
{
    /** The payment methods the admin currently has enabled. */
    public function methods()
    {
        $methods = MethodCurrency::where('enable_status', 1)
            ->with('paymentMethod')->get();

        return response()->json([
            'success' => true,
            'methods' => $methods->map(fn ($m) => [
                'id'         => $m->id,
                'name'       => optional($m->paymentMethod)->name,
                'currency'   => $m->currency,
                'symbol'     => $m->currency_name ?: $m->currency,
                'is_manual'  => (bool) $m->is_manual,
                'image'      => optional($m->paymentMethod)->image
                    ? asset(optional($m->paymentMethod)->image) : null,
                'minimum'    => $m->minimum,
                'maximum'    => $m->maximum,
                'percent_charge' => $m->p_charge,
                'fixed_charge'   => $m->f_charge,
                'rate'       => $m->cv_rate,
            ])->values(),
        ]);
    }

    /**
     * Start a payment for a plan. Returns a checkout_url the app opens in a
     * web-view; the gateway takes it from there.
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'package_id' => 'required|integer',
            'method'     => 'required|integer', // MethodCurrency id
        ]);

        $package = Package::where('status', 1)->where('id', $request->package_id)->firstOrFail();
        $method  = MethodCurrency::where('enable_status', 1)->where('id', $request->method)->firstOrFail();

        $amount = $package->price;

        if ($amount < $method->minimum || $amount > $method->maximum) {
            return response()->json([
                'success' => false,
                'message' => 'Amount is outside this method\'s minimum/maximum limit.',
            ], 422);
        }

        // Same charge maths the website uses.
        $charge       = ($amount * $method->p_charge) / 100 + $method->f_charge;
        $afterCharge  = $amount + $charge;
        $rate         = $method->cv_rate;
        $afterConvert = $afterCharge * $rate;
        $trx          = trxNumber();

        $payment = Payment::create([
            'user_id'            => $request->user()->id,
            'package_id'         => $package->id,
            'method_currency_id' => $method->id,
            'trx_number'         => $trx,
            'amount'             => $amount,
            'charge'             => $charge,
            'after_charge'       => $afterCharge,
            'rate'               => $rate,
            'after_convert'      => $afterConvert,
        ]);

        return response()->json([
            'success'      => true,
            'trx_number'   => $trx,
            // Opened in an in-app web-view. Runs the site's existing gateway
            // flow for this exact transaction, whatever method it is.
            'checkout_url' => url('/pay/' . $trx),
            'payment'      => [
                'amount'       => $amount,
                'charge'       => $charge,
                'after_charge' => $afterCharge,
            ],
        ], 201);
    }

    /** Poll a payment's status (0 pending, 1 success, 2 rejected). */
    public function status(Request $request, string $trx)
    {
        $payment = Payment::where('user_id', $request->user()->id)
            ->where('trx_number', $trx)->firstOrFail();

        return response()->json([
            'success' => true,
            'status'  => (int) $payment->status,
        ]);
    }
}
