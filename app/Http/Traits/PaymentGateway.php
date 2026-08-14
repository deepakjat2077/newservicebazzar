<?php

namespace App\Http\Traits;

use App\Http\Libs\PayTM;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;

trait PaymentGateway{

	//Flutter Wave
	public function flutterwave($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$data = [
			'public_key'=>$credentials->public_key,
			'email'=>auth()->user()->email,
			'amount'=>round($payment->after_convert),
			'phone'=>auth()->user()->mobile,
			'currency'=>$methodCurrency->currency,
			'trx'=>$payment->trx_number,
			'redirect'=>false,
			'view'=>$this->rootPaymentPath('flutterwave'),
		];

		return $data;
	}

	//Stripe
	public function stripe($payment,$methodCurrency,$method){
		$data = [
			'trx'=>$payment->trx_number,
			'redirect'=>false,
			'ipn'=>route('ipn.stripe'),
			'view'=>$this->rootPaymentPath('stripe')
		];

		return $data;
	}

	//payTm
	public function paytm($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$data = [
			'redirect'=>false,
			'view'=>$this->rootPaymentPath('autosubmit'),
			'method'=>'post',
			'url'=>$credentials->transaction_url,
		];
		$data['value'] = [
			'MID'=>trim($credentials->merchant_id),
			'WEBSITE'=>trim($credentials->website),
			'CHANNEL_ID'=>trim($credentials->channel_id),
			'INDUSTRY_TYPE_ID'=>trim($credentials->business_type),
			'ORDER_ID'=>$payment->trx_number,
			'TXN_AMOUNT'=>round($payment->after_convert,2),
			'CUST_ID'=>$payment->user_id,
			'CALLBACK_URL'=>route('ipn.paytm'),
		];
		$data['value']['CHECKSUMHASH'] = (new PayTM())->getChecksumFromArray($data['value'], $credentials->merchant_key);
		return $data;
	}

	//paypal
	public function paypal($payment, $methodCurrency, $method){
		$setting = Setting::first();
		$credentials = $method->setting;
		$data = [
			'redirect'=>false,
			'view'=>$this->rootPaymentPath('autosubmit'),
			'method'=>'post',
			'url'=>'https://www.paypal.com/cgi-bin/webscr',
			// https://www.sandbox.paypal.com/ (SandBox Url)
		];
		$data['value'] = [
			'cmd' => '_xclick',
			'business' => $credentials->email,
			'cbt' => $setting->site_name,
			'currency_code' => $payment->method_currency->currency,
			'quantity' => 1,
			'item_name' => "Payment To $setting->site_name Account",
			'custom' => "$payment->trx_number",
	        'amount' => round($payment->after_convert,2),
	        'return' => route('user.deposit'),
	        'cancel_return' => route('user.deposit'),
	        'notify_url' => route('ipn.paypal'),
		];

		return $data;
	}

	//skrill
	public function skrill($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$setting = Setting::first();
		$data = [
			'redirect'=>false,
			'view'=>$this->rootPaymentPath('autosubmit'),
			'method'=>'post',
			'url'=>'https://www.moneybookers.com/app/payment.pl',
		];
		$data['value'] = [
			'pay_to_email' => trim($credentials->merchant_mail),
	        'transaction_id' => "$payment->trx_number",
	        'return_url' => route('user.deposit'),
	        'return_url_text' => "Return $setting->site_name",
	        'cancel_url' => route('user.deposit'),
	        'status_url' => route('ipn.skrill'),
	        'language' => 'EN',
	        'amount' => round($payment->after_convert,2),
	        'currency' => "$methodCurrency->currency",
	        'detail1_description' => "$setting->site_name",
	        'detail1_text' => "Pay To $setting->site_name",
	        'logo_url' => asset('assets/images/logo/logo.png'),
		];

		return $data;
	}

	//Payeer
	public function payeer($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$setting = Setting::first();
		$arHash = [trim($credentials->merchant_id), $payment->trx_number, round($payment->after_convert,2), $methodCurrency->currency, base64_encode("Pay To $setting->site_name")];
        $arHash[] = $credentials->secret_key;
		$data = [
			'redirect'=>false,
			'view'=>$this->rootPaymentPath('autosubmit'),
			'method'=>'get',
			'url'=>'https://payeer.com/merchant',
		];

		$data['value'] = [
			'm_shop'    => trim($credentials->merchant_id),
	        'm_orderid' => $payment->trx_number,
	        'm_amount'  => round($payment->after_convert,2),
	        'm_curr'    => $methodCurrency->currency,
	        'm_desc'    => base64_encode("Pay To $setting->site_name"),
	        'm_sign'    => strtoupper(hash('sha256', implode(":", $arHash))),
		];

		return $data;
	}

	//paystack
	public function paystack($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$data = [
			'key'=>$credentials->public_key,
			'email'=>auth()->user()->email,
			'amount'=>$payment->after_convert * 100,
			'currency'=>$methodCurrency->currency,
			'ref'=>$payment->trx_number,
			'view'=>$this->rootPaymentPath('paystack'),
			'redirect'=>false,
		];
		return $data;
	}

	//Instamojo
	public function instamijo($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$setting = Setting::first();
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://instamojo.com/api/1.1/payment-requests/');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "X-Api-Key:$credentials->api_key",
                "X-Auth-Token:$credentials->auth_token"
            )
        );

        $payload = [
            'purpose' => 'Payment to ' . $setting->site_name,
            'amount' => round($payment->after_convert,2),
            'buyer_name' => $payment->user->username,
            'redirect_url' => route('user.deposit'),
            'webhook' => route('ipn.instamojo'),
            'email' => $payment->user->email,
            'send_email' => true,
            'allow_repeated_payments' => false
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($response);
        if (!$res->success) {
        	$data = ['error'=>$res->message];
        }else{
	        $payment->wallet = $res->payment_request->id;
	        $payment->save();
	        $data = [
	        	'redirect'=>true,
	        	'payment_url'=>$res->payment_request->longurl,
	        ];
        }
        return $data;
	}


	//razorpay
	public function razorpay($payment,$methodCurrency,$method){
		$credentials = $method->setting;
		$api = new Api($credentials->key_id, $credentials->key_secret);
		$order = $api->order->create(
            array(
                'receipt' => $payment->trx_number,
                'amount' => $payment->after_convert * 100,
                'currency' => $methodCurrency->currency,
                'payment_capture' => '0'
            )
        );
        $payment->extra_data = $order->id;
        $payment->save();
        $data = [
	        'method' => 'POST',
	        'url' => route('ipn.razorpay'),
	        'custom' => $payment->trx_number,
	        'checkout_js' => "https://checkout.razorpay.com/v1/checkout.js",
	        'redirect' => false,
	        'view' => 'user.payment.razorpay',
        ];
		$data['value'] = [
			'key' => $credentials->key_id,
	        'amount' => $payment->after_convert * 100,
	        'currency' => $methodCurrency->currency,
	        'order_id' => $order['id'],
	        'buttontext' => "Pay with Razorpay",
	        'name' => Auth::user()->username,
	        'description' => $payment->package->name,
	        'image' => asset( 'assets/images/logo/logo.png'),
	        'prefill.name' => Auth::user()->fullname(),
	        'prefill.email' => Auth::user()->email,
	        'prefill.contact' => Auth::user()->mobile,
	        'theme.color' => "#2ecc71",

		];

		return $data;
	}


	//Manual gateway
	public function manualGateway($payment, $methodCurrency, $method){
		$data = [
			'view'=>$this->rootPaymentPath('manual'),
			'redirect'=>false,
			'payment'=>$payment,
			'method'=>$method,
			'currency'=>$methodCurrency,
		];

		return $data;
	}

	protected function rootPaymentPath($view){
		return 'user.payment.'.$view;
	}
}
