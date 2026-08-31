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
        $deposit = Payment::where('user_id',auth()->id())->orderBy('id','desc')->where('status','!=',0)->findOrFail($id);
    	return view($this->theme.'user.payment.details',compact('deposit'));
    }

    public function depositSubmit(Request $request){
    	$request->validate([
    		'method'=>'required',
    		'package_id'=>'required|integer',
    		'listing_id'=>'nullable|integer',
    	]);
        $package = Package::where('status',1)->where('id',$request->package_id)->firstOrFail();
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
    	$amount = $package->price;
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
            'package_id'=>$package->id,
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
