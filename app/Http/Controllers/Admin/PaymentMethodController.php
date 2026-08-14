<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\MethodCurrency;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use HTMLPurifier;

class PaymentMethodController extends Controller
{
	use FileHandler;

    public function index(){
    	$methods = PaymentMethod::orderBy('name')->get();
    	return view('admin.gateway.methods',compact('methods'));
    }

    public function edit($id){
        $method = PaymentMethod::findOrFail($id);
        return view('admin.gateway.methodEdit',compact('method'));
    }

    public function offline(){
        return view('admin.gateway.methodOffline');
    }

    public function update(Request $request,$id){
    	$this->gatewayValidation($request);
    	$gateway = PaymentMethod::findOrFail($id);
    	$image = $gateway->image;
    	if ($request->hasFile('image')) {
            $image = $this->imageUpload($request->image,$gateway->image);
        }

        if ($gateway->is_manual) {
            $setting = $this->getSetting($request->setting);
        } else {
            // The edit form never re-displays stored API secrets, so a blank
            // field means "keep the current value", not "clear it".
            $existing = (array) $gateway->setting;
            $setting = [];
            foreach ((array) $request->setting as $key => $value) {
                $setting[$key] = ($value === null || $value === '') ? ($existing[$key] ?? null) : $value;
            }
        }

        $gateway->update([
        	'name'=>$request->name,
        	'image'=>$image,
            'setting'=>$setting,
        ]);

        if ($gateway->is_manual) {
            $this->createCurrency($request->currencies,$gateway);
        }
    	$alert = ['success','Payment Method Updated'];
    	return back()->withAlert($alert);
    }

    public function offlineStore(Request $request){
        $this->gatewayValidation($request);
        if ($request->hasFile('image')) {
            $image = $this->imageUpload($request->image,null);
        }

        $setting = $this->getSetting($request->setting);

        $gateway = PaymentMethod::create([
            'name'=>$request->name,
            'act'=>'manualGateway',
            'image'=>$image,
            'setting'=>$setting,
            'is_manual'=>1,
        ]);
        $this->createCurrency($request->currencies,$gateway);
        $alert = ['success','Payment Method Created'];
        return back()->withAlert($alert);
    }

    public function currencies(){
    	$methods = PaymentMethod::orderBy('name')->get();
    	return view('admin.gateway.currencies',compact('methods'));
    }

    public function currencyEdit($id){
    	$currency = MethodCurrency::findOrFail($id);
    	return view('admin.gateway.currencyUpdate',compact('currency'));
    }

    public function currencyUpdate(Request $request,$id){
      $request->validate([
    'minimum'=>'required|numeric|gte:0',
    'maximum'=>'required|numeric|gt:'.$request->minimum,
    'fixed_charge'=>'required|numeric|gte:0',
    'percent_charge'=>'required|numeric|gte:0',
    'conversion_rate'=>'required|numeric|gt:0',
    'instruction'=>'sometimes|required',
]);

        $purifier = new HTMLPurifier();
        $instruction = $purifier->purify($request->instruction);

        $currency = MethodCurrency::findOrFail($id);
        $currency->minimum = $request->minimum;
        $currency->maximum = $request->maximum;
        $currency->f_charge = $request->fixed_charge;
        $currency->p_charge = $request->percent_charge;
        $currency->cv_rate = $request->conversion_rate;
        $currency->instruction = $instruction;
        $currency->enable_status = $request->status ? 1 : 0;
        $currency->save();

        $alert = ['success','Method Currency Updated Successfully'];
        return back()->withAlert($alert);
    }

    protected function gatewayValidation($request){
        $request->validate([
            'name'=>'required',
            // Secret/requirement fields are nullable here — for automatic
            // gateways a blank field means "keep the existing stored value"
            // (see update()); offlineStore()'s own validation still requires
            // every manual-gateway requirement to be filled on creation.
            'setting.*'=>'nullable',
            'currencies.*'=>'sometimes|required',
            'image'=>'nullable|image|mimes:jpg,jpeg,png'
        ]);
    }

    protected function imageUpload($image,$old){
        $imageData = [
            'file'=>$image,
            'path'=>'assets/images/paymentMethod/',
            'size'=>'300x300',
            'prevFile'=>$old,
        ];
        $image = $this->uploadFile('image',$imageData);
        return $image;
    }

    protected function getSetting($requirment){
        $setting = [];
        foreach ($requirment as $data) {
            $setting[] = Str::strToKey($data);
        }
        return $setting;
    }

    protected function createCurrency($currencies,$gateway){
        foreach ($currencies as $key => $currency) {
            if(@$gateway->currencies[$key]){
                $gateway_currency = $gateway->currencies[$key];
                $gateway_currency->currency = $currency;
                $gateway_currency->currency_name = $currency;
                $gateway_currency->save();
            }else{
                MethodCurrency::create([
                    'payment_method_id'=>$gateway->id,
                    'currency'=>$currency,
                    'currency_name'=>$currency,
                    'is_manual'=>1,
                ]);
            }
        }
    }


}
