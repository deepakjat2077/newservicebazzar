<?php

namespace App\Http\Traits;

use App\Http\Libs\SendMail;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Token;

trait GatewayIpn{

	public function flutterwaveIpn($trx,$type)
	{
		if ($type == 'error') {
			$alert = ['danger','Oops! Transaction Failed'];
			return redirect()->route($this->redirect())->withAlert($alert);
		}

		if (!isset($trx)) {
			$alert = ['danger','Oops! Transaction Failed'];
			return redirect()->route($this->redirect())->withAlert($alert);
		}


		$data = Payment::where('trx_number', $trx)->where('status',0)->orderBy('id', 'DESC')->first();
		if (!$data) {
			$alert = ['danger','Oops! Transaction Failed'];
			return redirect()->route($this->redirect())->withAlert($alert);
		}
        $method_currency = $data->method_currency;
        $method = $method_currency->paymentMethod;
		$credentials = $method->setting;

        $query = array(
            "SECKEY" =>  $credentials->secret_key,
            "txref" => $trx
        );

        $data_string = json_encode($query);
        $ch = curl_init('https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);

        if ($response->data->status == "successful") {
            $this->paymentSuccess($data);
            $alert = ['success', 'Transaction was successful, Ref: ' . $trx];
        } else {
            $alert = ['danger', 'Unable to Process'];
        }

        return redirect()->route($this->redirect())->withAlert($alert);

	}

	public function stripeIpn(Request $request){
        $this->validate($request, [
            'card_number' => 'required',
            'card_expiry' => 'required',
            'card_cvc' => 'required',
            'track' => 'required',
        ]);
        $data = Payment::where('trx_number', $request->track)->where('status',0)->orderBy('id', 'DESC')->first();
        if (!$data) {
			$alert = ['danger','Oops! Transaction Failed'];
			return redirect()->route($this->redirect())->withAlert($alert);
		}

        $cc = $request->card_number;
        $exp = $request->card_expiry;
        $cvc = $request->card_cvc;

        $exp = $pieces = explode("/", $request->card_expiry);
        $emo = trim($exp[0]);
        $eyr = trim($exp[1]);
        $cnts = round($data->after_convert, 2) * 100;

		$method_currency = $data->method_currency;
		$method = $method_currency->paymentMethod;
		$credential = $method->setting;


        Stripe::setApiKey($credential->secret_key);

        Stripe::setApiVersion("2020-03-02");

        try {
            $token = Token::create(array(
                "card" => array(
                    "number" => "$cc",
                    "exp_month" => $emo,
                    "exp_year" => $eyr,
                    "cvc" => "$cvc"
                )
            ));
            try {
                $charge = Charge::create(array(
                    'card' => $token['id'],
                    'currency' => $method_currency->currency,
                    'amount' => $cnts,
                    'description' => 'item',
                ));

                if ($charge['status'] == 'succeeded') {
                    $this->paymentSuccess($data);
                    $alert = ['success', 'Payment Success.'];
                }
            } catch (\Exception $e) {
                $alert = ['danger', $e->getMessage()];
            }
        } catch (\Exception $e) {
            $alert = ['danger', $e->getMessage()];
        }

        return redirect()->route($this->redirect())->withAlert($alert);
	}


    //PayTM
    public function paytmIpn(){
        $data = Payment::where('trx_number', $_POST['trx_number'])->where('status',0)->orderBy('id', 'DESC')->first();
        if (!$data) {
            $alert = ['danger','Oops! Transaction Failed'];
            return redirect()->route($this->redirect())->withAlert($alert);
        }
        $method_currency = $data->method_currency;
        $method = $method_currency->paymentMethod;
        $credentials = $method->setting;
        $ptm = new PayTM();
        if ($ptm->verifychecksum_e($_POST, $credentials->merchant_key, $_POST['CHECKSUMHASH']) != "TRUE") {
            $alert = ['danger','Security Problem'];
            return redirect()->route($this->redirect())->withAlert($alert);
        }
        if ($_POST['RESPCODE'] != "01") {
            $alert = ['danger',$_POST['RESPMSG']];
            return redirect()->route($this->redirect())->withAlert($alert);
        }
        $requestParamList = array("MID" => $credentials->merchant_id, "ORDERID" => $_POST['ORDERID']);
        $StatusCheckSum = $ptm->getChecksumFromArray($requestParamList, $credentials->merchant_key);
        $requestParamList['CHECKSUMHASH'] = $StatusCheckSum;
        $responseParamList = $ptm->callNewAPI($credentials->transaction_status_url, $requestParamList);
        if ($responseParamList['STATUS'] == 'TXN_SUCCESS' && $responseParamList['TXNAMOUNT'] == $_POST['TXNAMOUNT']) {
            $this->paymentSuccess($data);
            $alert = ['success', 'Payment successful'];
        } else {
            $alert = ['danger', 'It seems some issue in server to server communication. Kindly connect with administrator'];
        }
        return redirect()->route($this->redirect())->withAlert($alert);
    }


    //paypalIPN
    public function paypalIpn(){
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }
//         $paypalURL = "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr"; // use for sandbox text
        $paypalURL = "https://ipnpb.paypal.com/cgi-bin/webscr?";
        $url = $paypalURL . $req;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $verify = curl_exec($ch);
        curl_close($ch);

        if ($verify == "VERIFIED") {
            $receiver_email = $_POST['receiver_email'] ?? '';
            $mc_currency = $_POST['mc_currency'] ?? '';
            $mc_gross = $_POST['mc_gross'] ?? null;
            $track = $_POST['custom'] ?? null;
            $data = Payment::where('trx_number', $track)->where('status', 0)->orderBy('id', 'DESC')->first();

            if (!$data) {
                $alert = ['danger', 'Payment failed'];
                return redirect()->route($this->redirect())->withAlert($alert);
            }

            $method_currency = $data->method_currency;
            $method = $method_currency->paymentMethod;
            $credential = $method->setting;
            $amount = $data->after_convert;

            // Verify the payment was actually made to our own merchant account
            // and in the expected currency, not just that PayPal confirmed
            // *some* genuine IPN (which could be for a payment to any account).
            if ($mc_gross == $amount
                && hash_equals((string) ($credential->email ?? ''), (string) $receiver_email)
                && $mc_currency === $method_currency->currency
                && $data->status == '0') {
                $this->paymentSuccess($data);
                $alert = ['success', 'Payment successful'];
            }else{
                $alert = ['danger', 'Payment failed'];
            }
        }else{
                $alert = ['danger', 'Payment failed'];
        }
        return redirect()->route($this->redirect())->withAlert($alert);
    }

    //skrillIPN
    public function skrillIpn()
    {
        $data = Payment::where('trx_number', $_POST['transaction_id'] ?? null)->where('status', 0)->orderBy('id', 'DESC')->first();

        if (!$data) {
            $alert = ['danger', 'Payment failed'];
            return redirect()->route($this->redirect())->withAlert($alert);
        }

        $method_currency = $data->method_currency;
        $method = $method_currency->paymentMethod;
        $credential = $method->setting;
        $concatFields = ($_POST['merchant_id'] ?? '')
            . ($_POST['transaction_id'] ?? '')
            . strtoupper(md5($credential->secret_key))
            . ($_POST['mb_amount'] ?? '')
            . ($_POST['mb_currency'] ?? '')
            . ($_POST['status'] ?? '');

        $signatureValid = hash_equals(strtoupper(md5($concatFields)), (string) ($_POST['md5sig'] ?? ''));

        if ($signatureValid && ($_POST['status'] ?? null) == 2 && ($_POST['pay_to_email'] ?? null) === $credential->pay_to_email && $data->status == '0') {
            $this->paymentSuccess($data);
            $alert = ['success', 'Payment successful'];
        }else{
            $alert = ['danger', 'Payment failed'];
        }
        return redirect()->route($this->redirect())->withAlert($alert);
    }

    //payeerIPN
    public function payeerIpn(Request $request){
        if (isset($_POST["m_operation_id"]) && isset($_POST["m_sign"])) {

            $data = Payment::where('trx_number', $_POST['m_orderid'] ?? null)->where('status', 0)->orderBy('id', 'DESC')->first();

            if (!$data) {
                $alert = ['danger', 'Payment Failed.'];
                return redirect()->route($this->redirect())->withAlert($alert);
            }

            $method_currency = $data->method_currency;
            $method = $method_currency->paymentMethod;
            $credential = $method->setting;

            $sign_hash = strtoupper(hash('sha256', implode(":", array(
                $_POST['m_operation_id'],
                $_POST['m_operation_ps'],
                $_POST['m_operation_date'],
                $_POST['m_operation_pay_date'],
                $_POST['m_shop'],
                $_POST['m_orderid'],
                $_POST['m_amount'],
                $_POST['m_curr'],
                $_POST['m_desc'],
                $_POST['m_status'],
                $credential->secret_key
            ))));

            if (!hash_equals($sign_hash, (string) $_POST["m_sign"])) {
                $alert = ['danger', 'The digital signature did not matched.'];
            } else {
                if ($_POST['m_amount'] == round($data->after_convert,2) && $_POST['m_curr'] == $method_currency->currency && $_POST['m_status'] == 'success' && $data->status == '0') {
                    $this->paymentSuccess($data);
                    $alert = ['success', 'Transaction is successful'];
                } else {
                    $alert = ['danger', 'Payment Failed.'];
                }
            }
        } else {
            $alert = ['danger', 'Payment Failed.'];
        }
        return redirect()->route($this->redirect())->withAlert($alert);
    }


    //Paystack
    public function paystackIpn(Request $request){
        $request->validate([
            'reference' => 'required',
            'paystack-trxref' => 'required',
        ]);

        $track = $request->reference;
        $data = Payment::where('trx_number', $track)->where('status', 0)->orderBy('id', 'DESC')->first();

        if (!$data) {
            $alert = ['danger', 'Something went wrong while executing'];
            return redirect()->route($this->redirect())->withAlert($alert);
        }

        $method_currency = $data->method_currency;
        $method = $method_currency->paymentMethod;
        $credential = $method->setting;
        $secret_key = $credential->secret_key;

        $result = array();
        $url = 'https://api.paystack.co/transaction/verify/' . $track;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $secret_key]);
        $r = curl_exec($ch);
        curl_close($ch);

        if ($r) {
            $result = json_decode($r, true);
            if ($result) {
                if ($result['data']) {
                    if ($result['data']['status'] == 'success') {

                        $am = $result['data']['amount'];
                        $sam = round($data->after_convert, 2) * 100;

                        if ($am == $sam && $result['data']['currency'] == $method->currency  && $data->status == '0') {
                            $this->paymentSuccess($data);
                            $alert = ['success', 'Deposit Successful'];
                        } else {
                            $alert = ['danger', 'Less Amount Paid. Please Contact With Admin'];
                        }
                    } else {
                        $alert = ['danger', $result['data']['gateway_response']];
                    }
                } else {
                    $alert = ['danger', $result['message']];
                }
            } else {
                $alert = ['danger', 'Something went wrong while executing'];
            }
        } else {
            $alert = ['danger', 'Something went wrong while executing'];
        }
        return redirect()->route($this->redirect())->withAlert($alert);
    }

    //instamojoIPN
    public function instamojoIpn(Request $request){
        $data = Payment::where('wallet', $_POST['payment_request_id'] ?? null)->where('status', 0)->orderBy('id', 'DESC')->first();

        if (!$data) {
            $alert = ['danger', 'Oops! Transaction Failed'];
            return redirect()->route($this->redirect())->withAlert($alert);
        }

        $method_currency = $data->method_currency;
        $method = $method_currency->paymentMethod;
        $credential = $method->setting;

        $imData = $_POST;
        $macSent = (string) ($imData['mac'] ?? '');
        unset($imData['mac']);
        ksort($imData, SORT_STRING | SORT_FLAG_CASE);
        $mac = hash_hmac("sha1", implode("|", $imData), $credential->salt);

        if (hash_equals($mac, $macSent) && ($imData['status'] ?? null) == "Credit" && $data->status == '0') {
            $this->paymentSuccess($data);
            $alert = ['success', 'Payment successful'];
        } else {
            $alert = ['danger', 'Payment failed'];
        }

        return redirect()->route($this->redirect())->withAlert($alert);
    }

    //razorpay
    public function razorpayIpn(Request $request){
        $data = Payment::where('extra_data', $request->razorpay_order_id)->orderBy('id', 'DESC')->first();

        if (!$data) {
            $alert = ['danger', 'Invalid Request 1'];
            return redirect()->route($this->redirect())->withAlert($alert);
        }

        $method_currency = $data->method_currency;
        $method = $method_currency->paymentMethod;
        $credential = $method->setting;

        $sig = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, $credential->key_secret);

        if (hash_equals($sig, (string) $request->razorpay_signature)) {
            if ((int) $data->status === 0) {
                $this->paymentSuccess($data);
                $alert = ['success', 'Transaction successful'];
            } else {
                $alert = ['success', 'Payment already processed'];
            }
        } else {
            $alert = ['danger', "Invalid Request - Signature mismatch"];
        }

        return redirect()->route($this->redirect())->withAlert($alert);
    }

    //vougepay IPN
    public function vougepayIpn(Request $request){
        $request->validate([
            'transaction_id'=>'required'
        ]);

        $trx_number = $request->transaction_id;
        $url = "https://voguepay.com/?v_transaction_id=$trx_number&type=json";
        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        $result = json_decode($result);

        if (!$result || empty($result->merchant_ref)) {
            $alert = ['danger', "Invalid Request"];
            return redirect()->route($this->redirect())->withAlert($alert);
        }

        $track = $result->merchant_ref;

        $data = Payment::where('trx_number',$track)->where('status', 0)->orderBy('id', 'DESC')->first();

        if ($data && ($result->status ?? null) == "Approved") {
            $this->paymentSuccess($data);
            $alert = ['success', 'Payment Successful'];
        } else {
            $alert = ['danger', "Invalid Request"];
        }

        return redirect()->route($this->redirect())->withAlert($alert);

    }


    //Manual Gateway IPN
    public function manualIpn(Request $request,SendMail $mail){
        $validation['trx_number'] ='required';
        $validation['user_id']    ='required|integer';
        $validation['method_id']  ='required|integer';
        $method = PaymentMethod::findOrFail($request->method_id);
        $value = [];
        foreach ($method->setting as $requirment) {
            $validation[$requirment] = 'required';
            $value[$requirment] = $request->$requirment;
        }
        $request->validate($validation);
        $payment = Payment::where('user_id',$request->user_id)->where('trx_number',$request->trx_number)->where('status',0)->firstOrFail();
        $payment->status = 2;
        $payment->details = $value;
        $payment->save();
        $setting = Setting::first();
        $data = [
            'name'=>$payment->user->fullname(),
            'email'=>$payment->user->email,
            'template'=>'payment_request',
            'value'=>[
                "amount"=>numberFormat($payment->amount),
                "site_currency"=>$setting->site_currency,
                "method_name"=>$payment->methodName(),
                "method_currency"=>$payment->method_currency->currency,
                "trx"=>$payment->trx_number,
                "after_convert"=>numberFormat($payment->after_convert),
                "charge"=>numberFormat($payment->charge),
                "after_charge"=>numberFormat($payment->after_charge),
                "rate"=>numberFormat($payment->rate)
            ]
        ];
        $mail->sendMail($data);
        $alert = ['success','Payment request sent successfully. Please wait for admin approval'];
        return redirect()->route($this->redirect())->withAlert($alert);
    }

	/**
	 * Credits a pending payment exactly once. Wrapped in a transaction with a
	 * row lock and a re-check of `status` inside the lock so that a replayed
	 * or concurrently-duplicated IPN/webhook call can never credit the same
	 * payment twice, regardless of what the calling gateway handler already
	 * checked before calling this method.
	 */
	protected function paymentSuccess($payment){
		return DB::transaction(function () use ($payment) {
			$locked = Payment::where('id', $payment->id)->lockForUpdate()->first();

			if (!$locked || (int) $locked->status !== 0) {
				// Already processed (or no longer pending) - refuse to credit again.
				return false;
			}

			$locked->status = 1;
			$locked->save();

			$user = $locked->user;
			if ($user && $locked->package) {
				$expiry = now()->addDays($locked->package->validity);

				// A package bought for a specific business activates on that
				// listing (the level the owner advertises at). A plain account
				// purchase — no listing attached — keeps the legacy user-level
				// activation so existing pricing-page flows are unaffected.
				if ($locked->listing_id) {
					\App\Models\Listing::where('id', $locked->listing_id)
						->where('user_id', $user->id)
						->update([
							'package_id'  => $locked->package->id,
							'package_exp' => $expiry,
						]);
				} else {
					$user->package_id = $locked->package->id;
					$user->package_exp = $expiry;
					$user->save();
				}
			}

			$setting = Setting::first();
			if ($user && $setting) {
				$mailData = [
					'name'=>$user->fullname(),
					'email'=>$user->email,
					'template'=>'payment_success',
					'value'=>[
						"amount"=>numberFormat($locked->amount),
						"site_currency"=>$setting->site_currency,
						"method_name"=>$locked->methodName(),
						"method_currency"=>$locked->method_currency->currency,
						"trx"=>$locked->trx_number,
						"after_convert"=>numberFormat($locked->after_convert),
						"charge"=>numberFormat($locked->charge),
						"after_charge"=>numberFormat($locked->after_charge),
						"rate"=>numberFormat($locked->rate)
					]
				];
				$mail = new SendMail();
				$mail->sendMail($mailData);
			}

			\App\Support\Tracking\Tracker::serverConversion('payment_success', $user->id ?? null, [
				'meta' => ['payment_id' => $locked->id, 'amount' => $locked->amount],
			]);

			return true;
		});
	}

	protected function redirect(){
		return 'user.deposit';
	}
}
