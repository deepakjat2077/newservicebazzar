<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificatePackage;
use App\Models\Listing;
use App\Models\ListingSalesPackage;
use App\Models\Location;
use App\Models\MethodCurrency;
use App\Models\Payment;
use App\Support\SalesPerks;
use App\Http\Traits\PaymentGateway;
use App\Support\WebsiteTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The "More ways to grow" packages (SB Verified, SB Trusted, certificates,
 * web / mobile banners, business websites, Top positions) — the CertificatePackage
 * rows the website's Advertise page shows, exposed for the mobile app.
 *
 * These are a different system from the legacy premium plans (Package model,
 * /api/plans + /api/deposit). The buying flow mirrors the website's exactly,
 * and the price is always resolved from the DB (resolveChoice), never trusted
 * from the request. A paid plan returns a checkout_url the app opens in a
 * web-view (/pay/{trx}); a free plan activates straight away. After a paid
 * checkout completes, poll GET /api/deposit/{trx}/status for status = 1.
 */
class SalesPackageController extends Controller
{
    // Reused only for cashfree() — the same Cashfree order creation the
    // website's checkout runs (self-contained, uses no controller state).
    use PaymentGateway;

    /**
     * The only gateways the sales-package checkout runs through (the website's
     * flow for these packages uses exactly these). The methods list and the
     * deposit validation both read this, so the app is never shown a method
     * that deposit() would reject.
     */
    public const SALES_GATEWAYS = ['razorpay', 'cashfree'];

    /** The card list — every active sales package, lightest payload. */
    public function index()
    {
        $packages = CertificatePackage::where('status', 1)->orderBy('id')->get();

        return response()->json([
            'success'  => true,
            'packages' => $packages->map(fn ($p) => $this->card($p))->values(),
        ]);
    }

    /**
     * One package with everything the select screen needs: its plans, whether
     * it needs a city/area or a website design, the per-city/area price tree,
     * and the list of cities to choose from.
     */
    public function show(Request $request, $id)
    {
        $package = CertificatePackage::where('status', 1)->findOrFail($id);

        // When the app passes the business it is buying for, each website design
        // comes back with a live preview_url (that listing's own name, photos and
        // details), exactly like the website's "Choose a design" screen.
        $listingId = $this->previewListingId($request);

        return response()->json([
            'success' => true,
            'package' => $this->card($package) + [
                'tiers'           => $package->tierLabels(),
                'price_tree'      => $package->positionPriceTree(),
                'cities'          => $this->cities($package),
                'templates'       => $package->needsTemplate() ? $this->templateList($listingId) : [],
                'payment_methods' => $this->salesMethods(),
            ],
        ]);
    }

    /** Only the gateways the sales checkout accepts (Razorpay, Cashfree). */
    public function methods()
    {
        return response()->json(['success' => true, 'methods' => $this->salesMethods()]);
    }

    /** Areas of a city, for the city/area picker (same as the website). */
    public function areas(Request $request)
    {
        $areas = Listing::query()
            ->where('city', $request->query('city'))
            ->whereNotNull('area')->where('area', '!=', '')
            ->distinct()->orderBy('area')->limit(300)->pluck('area');

        return response()->json(['success' => true, 'areas' => $areas->values()]);
    }

    /**
     * The website designs offered by the business-website packages.
     * Pass ?listing_id= to get each design's live preview_url for that business.
     */
    public function templates(Request $request)
    {
        return response()->json([
            'success'   => true,
            'templates' => $this->templateList($this->previewListingId($request)),
        ]);
    }

    /**
     * Start a purchase for a sales package on one of the user's own listings.
     *
     * Body: listing_id, sales_package_id, method (MethodCurrency id),
     *       plan (index) OR tier+city+area (location packages),
     *       template (website packages).
     *
     * Free plan  -> { success, free: true }  (activated immediately).
     * Paid plan  -> { success, trx_number, checkout_url }  (open /pay/{trx}).
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'listing_id'       => 'required|integer',
            'sales_package_id' => 'required|integer',
            'method'           => 'required|integer',
            'plan'             => 'nullable',
            'tier'             => 'nullable|string',
            'city'             => 'nullable|string',
            'area'             => 'nullable|string',
            'template'         => 'nullable|string',
        ]);

        // The listing must belong to the buyer — nobody pays a package onto
        // someone else's business.
        $listing = Listing::where('id', $request->listing_id)
            ->where('user_id', $request->user()->id)->first();

        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => 'This business was not found on your account.',
            ], 403);
        }

        $package = CertificatePackage::where('status', 1)
            ->where('id', $request->sales_package_id)->firstOrFail();

        // Price always comes from the package's own plan / city-area row.
        $choice = $package->resolveChoice([
            'plan' => $request->input('plan'),
            'tier' => $request->input('tier'),
            'city' => $request->input('city', ''),
            'area' => $request->input('area', ''),
        ]);

        if (!$choice) {
            return response()->json([
                'success' => false,
                'message' => 'Please pick a plan for this package.',
            ], 422);
        }

        // Website packages must carry a chosen design, recorded in the plan
        // text the same "· Design: X" way SB Sales orders use.
        if ($package->needsTemplate()) {
            if (!WebsiteTemplates::exists($request->input('template'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please choose a website design first.',
                ], 422);
            }
            $choice['label'] .= ' · Design: ' . WebsiteTemplates::name($request->input('template'));
        }

        // Already running for this business (same city/area for location
        // packages): no second payment until it ends.
        $running = ListingSalesPackage::where('listing_id', $listing->id)
            ->where('certificate_package_id', $package->id)
            ->where('expires_at', '>', now())
            ->when($package->needsLocation(), fn ($q) => $q
                ->where('city', (string) ($choice['city'] ?? ''))
                ->where('area', (string) ($choice['area'] ?? '')))
            ->latest()->first();

        if ($running) {
            return response()->json([
                'success'    => false,
                'message'    => $package->name . ' is already active for this business till '
                    . $running->expires_at->format('d M Y, h:i A') . '. You can renew it once it ends.',
                'expires_at' => $running->expires_at->toIso8601String(),
            ], 422);
        }

        // A free plan has nothing to pay for — switch it on right away.
        if ($choice['price'] <= 0) {
            $bought = SalesPerks::activate($listing, $package, $choice);

            return response()->json([
                'success'    => true,
                'free'       => true,
                'message'    => $package->name . ' is now active for ' . $choice['days'] . ' day'
                    . ($choice['days'] > 1 ? 's' : '') . '.',
                'expires_at' => optional($bought->expires_at)->toIso8601String(),
            ], 201);
        }

        // Paid: only the gateways this checkout actually uses on the website.
        $method = MethodCurrency::where('enable_status', 1)->where('id', $request->method)
            ->whereHas('paymentMethod', fn ($q) => $q->whereIn('act', self::SALES_GATEWAYS))
            ->firstOrFail();

        $amount = $choice['price'];

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
            'package_id'         => null,
            'sales_package_id'   => $package->id,
            'sales_plan'         => $choice['label'],
            'sales_days'         => $choice['days'] ?? null,
            'sales_city'         => $choice['city'] ?? null,
            'sales_area'         => $choice['area'] ?? null,
            'listing_id'         => $listing->id,
            'method_currency_id' => $method->id,
            'trx_number'         => $trx,
            'amount'             => $amount,
            'charge'             => $charge,
            'after_charge'       => $afterCharge,
            'rate'               => $rate,
            'after_convert'      => $afterConvert,
        ]);

        // Create the gateway order server-side and hand the app the native
        // payment data (Razorpay order / Cashfree session) so it opens its own
        // SDK — no web page needed. checkout_url stays as a fallback.
        $native = $this->nativeGatewayData($payment, $method);

        return response()->json(array_merge([
            'success'      => true,
            'free'         => false,
            'trx_number'   => $trx,
            // Fallback only — the app pays natively from the gateway data below.
            // /pay/{trx} runs the site's Razorpay/Cashfree flow; on success the
            // package activates and GET /api/deposit/{trx}/status returns 1.
            'checkout_url' => url('/pay/' . $trx),
            'payment'      => [
                'label'        => $choice['label'],
                'amount'       => $amount,
                'charge'       => $charge,
                'after_charge' => $afterCharge,
            ],
        ], $native), 201);
    }

    /**
     * Create the gateway order on the server and return the native payment data
     * the app opens its Razorpay/Cashfree SDK with — the SAME order creation the
     * website checkout runs. On failure it logs the reason and returns [], so
     * the response still carries checkout_url and laravel.log shows why.
     */
    protected function nativeGatewayData(Payment $payment, MethodCurrency $method): array
    {
        $pm  = $method->paymentMethod;
        $act = $pm->act ?? '';

        try {
            if ($act === 'razorpay') {
                $s     = $pm->setting;
                $paise = (int) round($payment->after_convert * 100);
                $api   = new \Razorpay\Api\Api($s->key_id, $s->key_secret);
                $order = $api->order->create([
                    'receipt'         => $payment->trx_number,
                    'amount'          => $paise,
                    'currency'        => $method->currency,
                    'payment_capture' => '0',
                ]);

                $payment->extra_data = $order->id;
                $payment->save();

                return [
                    'gateway'  => 'razorpay',
                    'razorpay' => [
                        'key_id'      => $s->key_id,
                        'order_id'    => $order->id,
                        'amount'      => $paise,
                        'currency'    => $method->currency,
                        'name'        => 'Service Bazzar',
                        'description' => (string) $payment->sales_plan,
                    ],
                ];
            }

            if ($act === 'cashfree') {
                $result = $this->cashfree($payment, $method, $pm);
                if (!empty($result['error'])) {
                    throw new \RuntimeException($result['error']);
                }
                $v = $result['value'];

                return [
                    'gateway'  => 'cashfree',
                    'cashfree' => [
                        'payment_session_id' => $v['payment_session_id'],
                        'order_id'           => $payment->trx_number,
                        'environment'        => ($v['mode'] ?? 'sandbox') === 'production' ? 'PRODUCTION' : 'SANDBOX',
                    ],
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Sales deposit gateway order failed', [
                'trx'     => $payment->trx_number,
                'gateway' => $act,
                'error'   => $e->getMessage(),
            ]);
        }

        return [];
    }

    /** The shared card payload for a package. */
    protected function card(CertificatePackage $p): array
    {
        return [
            'id'             => $p->id,
            'name'           => $p->name,
            'images'         => $p->imageUrls(),
            'benefits'       => $p->benefitList(),
            'highlights'     => $p->highlightList(),
            'plans'          => $p->planList(),
            'pricing_type'   => $p->pricing_type,
            'needs_location' => $p->needsLocation(),
            'needs_template' => $p->needsTemplate(),
            'validity_days'  => $p->validity_days,
            'starting_price' => $p->startingPrice(),
        ];
    }

    /** Every city the package can be bought in (same logic as the website). */
    protected function cities(CertificatePackage $package): array
    {
        if (!$package->needsLocation()) {
            return [];
        }

        $cities = Location::where('status', 1)->orderBy('name')->pluck('name');
        if ($cities->isEmpty()) {
            $cities = Listing::query()->whereNotNull('city')->where('city', '!=', '')
                ->distinct()->orderBy('city')->pluck('city');
        }

        return $cities->merge($package->positionPrices()->distinct()->pluck('city'))
            ->filter()->unique()->sort()->values()->all();
    }

    /**
     * Website designs as a flat list the app can render. When $listingId is a
     * live listing, each design carries a preview_url — a full home page drawn
     * from that business's own name, photos and details, the same render the
     * website shows. The app loads it in a web-view/iframe per design.
     */
    protected function templateList(?int $listingId = null): array
    {
        $out = [];
        foreach (WebsiteTemplates::all() as $key => $t) {
            $row = ['key' => $key] + $t;
            if ($listingId) {
                $row['preview_url'] = route('website.preview.public', ['id' => $listingId, 'template' => $key]);
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * The enabled Razorpay / Cashfree methods the sales checkout accepts,
     * in the same shape as the generic /api/payment-methods list so the app
     * renders them the same way.
     */
    protected function salesMethods(): array
    {
        return MethodCurrency::where('enable_status', 1)
            ->whereHas('paymentMethod', fn ($q) => $q->whereIn('act', self::SALES_GATEWAYS))
            ->with('paymentMethod')
            ->get()
            ->map(fn ($m) => [
                'id'             => $m->id,
                'name'           => optional($m->paymentMethod)->name,
                'currency'       => $m->currency,
                'symbol'         => $m->currency_name ?: $m->currency,
                'is_manual'      => (bool) $m->is_manual,
                'image'          => optional($m->paymentMethod)->image ? asset(optional($m->paymentMethod)->image) : null,
                'minimum'        => $m->minimum,
                'maximum'        => $m->maximum,
                'percent_charge' => $m->p_charge,
                'fixed_charge'   => $m->f_charge,
                'rate'           => $m->cv_rate,
            ])->values()->all();
    }

    /** A valid, live listing id from the request (for design previews), or null. */
    protected function previewListingId(Request $request): ?int
    {
        $id = (int) $request->query('listing_id');
        if ($id <= 0) {
            return null;
        }
        return Listing::where('id', $id)->where('status', 1)->exists() ? $id : null;
    }
}
