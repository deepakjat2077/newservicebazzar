<?php

namespace App\Http\Controllers;

use App\Http\Traits\GatewayIpn;
use App\Http\Traits\PaymentGateway;
use App\Models\MethodCurrency;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;

class DepositController extends Controller
{
	use PaymentGateway, GatewayIpn;

    protected $setting;
    protected $theme;

    public function __construct(){
    	$this->setting = Setting::first();
		$this->theme = 'themes.'.$this->setting->theme.'.';
    }

    public function deposits(){
    	$payments = Payment::where('user_id',auth()->user()->id)->where('status','!=',0)->with('method_currency','package')->orderBy('id','desc')->paginate(20);
    	return view($this->theme.'user.payment.deposit',compact('payments'));
    }

    public function depositDetails($id)
    {
        $payment = Payment::where('user_id',auth()->id())->orderBy('id','desc')->where('status','!=',0)->findOrFail($id);
    	return view($this->theme.'user.payment.details',compact('payment'));
    }

    public function depositSubmit(Request $request){
    	$request->validate([
    		'method'=>'required',
    		'package_id'=>'required_without:sales_package_id|nullable|integer',
    		'sales_package_id'=>'nullable|integer',
    		'listing_id'=>'nullable|integer',
    	]);

    	// A sales package (SB Verified, banner, Top position…) instead of a
    	// legacy plan: the price comes from the package's own plan / city-area
    	// row, resolved here rather than trusted from the form.
    	$salesPackage = null;
    	$salesChoice = null;
    	if ($request->filled('sales_package_id')) {
    		$salesPackage = \App\Models\CertificatePackage::where('status',1)
    			->findOrFail($request->sales_package_id);
    		$salesChoice = $salesPackage->resolveChoice([
    			'plan' => $request->input('plan'),
    			'tier' => $request->input('tier'),
    			'city' => $request->input('city', ''),
    			'area' => $request->input('area', ''),
    		]);
    		if (!$salesChoice || $salesChoice['price'] <= 0) {
    			$alert = ['danger','Please pick a plan for this package.'];
    			return back()->withAlert($alert);
    		}
    	}

        $package = $salesPackage ? null : Package::where('status',1)->where('id',$request->package_id)->firstOrFail();
    	$method = MethodCurrency::where('enable_status',1)->where('id',$request->method)->firstOrFail();

    	// When the purchase is for a specific business (bought from the listing's
    	// Advertise page), the package activates on that listing — but only if it
    	// really belongs to the buyer, so nobody can pay a plan onto someone
    	// else's listing.
    	$listingId = null;
    	if ($request->filled('listing_id')) {
    		$listingId = \App\Models\Listing::where('id',$request->listing_id)
    			->where('user_id',auth()->id())->value('id');
    	}
    	$amount = $salesChoice ? $salesChoice['price'] : $package->price;
    	if ($amount < $method->minimum || $amount > $method->maximum) {
    		$alert = ['danger','Please follow the minimum and maximum limit'];
    		return back()->withAlert($alert);
    	}
    	$charge = ($amount * $method->p_charge) / 100 + $method->f_charge;
    	$after_charge = $amount + $charge;
    	$rate = $method->cv_rate;
    	$after_convert = $after_charge * $rate;
    	$trx_number = trxNumber();

    	$payment = Payment::create([
    		'user_id'=>auth()->user()->id,
            'package_id'=>$package?->id,
            'sales_package_id'=>$salesPackage?->id,
            'sales_plan'=>$salesChoice['label'] ?? null,
            'sales_days'=>$salesChoice['days'] ?? null,
            'sales_city'=>$salesChoice['city'] ?? null,
            'sales_area'=>$salesChoice['area'] ?? null,
            'listing_id'=>$listingId,
    		'method_currency_id'=>$method->id,
    		'trx_number'=>$trx_number,
    		'amount'=>$amount,
    		'charge'=>$charge,
    		'after_charge'=>$after_charge,
    		'rate'=>$rate,
    		'after_convert'=>$after_convert,
    	]);

    	\App\Support\Tracking\Tracker::conversion($request, 'payment_started', ['meta' => ['payment_id' => $payment->id, 'amount' => $amount]]);

    	session()->put('transaction',$trx_number);

    	// For AJAX requests from the checkout page, return the gateway's own
    	// order details so its JS SDK can open its modal right there on that
    	// page — the same reason the Razorpay branch below exists at all.
    	// Without this, a gateway just falls through to the plain redirect at
    	// the bottom of this method, which is fine on its own but takes the
    	// buyer to a separate /user/deposit/confirm page instead of staying
    	// on the checkout page they were already looking at.
    	if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
    		// Create Razorpay order for checkout modal
    		if ($method->paymentMethod->act === 'razorpay') {
    			try {
    				$razorpaySettings = $method->paymentMethod->setting;
    				$api = new \Razorpay\Api\Api($razorpaySettings->key_id, $razorpaySettings->key_secret);
    				$order = $api->order->create([
    					'receipt' => $payment->trx_number,
    					'amount' => $payment->after_convert * 100,
    					'currency' => $method->currency,
    					'payment_capture' => '0'
    				]);

    				// Store order ID in payment
    				$payment->extra_data = $order->id;
    				$payment->save();

    				return response()->json([
    					'success' => true,
    					'payment' => [
    						'id' => $payment->id,
    						'trx_number' => $payment->trx_number,
    						'order_id' => $order->id,
    						'amount' => $payment->after_convert * 100,
    						'currency' => $method->currency,
    						'key' => $razorpaySettings->key_id,
    						'user_name' => auth()->user()->username,
    						'user_email' => auth()->user()->email,
    						'user_phone' => auth()->user()->mobile,
    					]
    				]);
    			} catch (\Exception $e) {
    				return response()->json(['success' => false, 'error' => 'Failed to create order'], 500);
    			}
    		}

    		// Create Cashfree order for the checkout page's own modal — reuses
    		// PaymentGateway::cashfree() (the same one depositConfirm() calls
    		// for a non-AJAX submit) so the order-creation logic lives in one
    		// place. $method here is a MethodCurrency row, matching that
    		// trait method's ($payment, $methodCurrency, $paymentMethod) shape.
    		if ($method->paymentMethod->act === 'cashfree') {
    			$result = $this->cashfree($payment, $method, $method->paymentMethod);

    			if (!empty($result['error'])) {
    				return response()->json(['success' => false, 'error' => $result['error']], 500);
    			}

    			return response()->json([
    				'success' => true,
    				'cashfree' => $result['value'],
    			]);
    		}
    	}

    	return redirect()->route('user.deposit.confirm');
    }

    public function depositConfirm(){
    	$trx_number = session()->get('transaction');
    	if (!$trx_number) {
    		$alert = ['danger','Oops! Session expired'];
    		return redirect()->route('user.deposit')->withAlert($alert);
    	}
    	$payment = Payment::where('trx_number',$trx_number)->orderBy('id','desc')->first();
    	$methodCurrency = $payment->method_currency;
    	$method = $methodCurrency->paymentMethod;
    	$methodName = $method->act;
    	$data = $this->$methodName($payment,$methodCurrency,$method);
        if (@$data['error']) {
            $alert = ['danger',$data['error']];
            return redirect()->route('user.deposit')->withAlert($alert);
        }
    	if ($data['redirect'] == true) {
    		return redirect($data['payment_url']);
    	}else{
    		return view($this->theme.$data['view'],compact('data'));
    	}
    }

    public function details($id){
        $payment = Payment::where('user_id',auth()->user()->id)->where('status','!=',0)->with('method_currency','package')->where('id',$id)->firstOrFail();
        return view($this->theme.'user.payment.details',compact('payment'));
    }

    /**
     * Mobile-app checkout entry, opened in the app's web-view as /pay/{trx}.
     *
     * Same as depositConfirm() but keyed on the transaction number in the URL
     * instead of the web session — the app is token-authenticated and holds no
     * session cookie. The trx is a random, unguessable string that only starts
     * a payment the user themselves just created (still status 0), so it needs
     * no login wall of its own; every gateway (redirect or auto-submit view)
     * runs through the one PaymentGateway path the website already uses.
     */
    public function appCheckout($trx){
        $payment = Payment::where('trx_number',$trx)->where('status',0)->firstOrFail();
        $methodCurrency = $payment->method_currency;
        $method = $methodCurrency->paymentMethod;
        $methodName = $method->act;

        $data = $this->$methodName($payment,$methodCurrency,$method);

        if (@$data['error']) {
            abort(400, $data['error']);
        }

        if ($data['redirect'] == true) {
            return redirect($data['payment_url']);
        }

        return view($this->theme.$data['view'],compact('data'));
    }
}
