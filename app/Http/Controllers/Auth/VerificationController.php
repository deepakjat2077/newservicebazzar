<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{

	protected $setting;
	protected $theme;

	public function __construct(){
		$this->setting = Setting::first();
		$this->theme = 'themes.'.$this->setting->theme.'.';
	}

    public function index(){
    	$user = auth()->user();
    	if ($user->email_verified == 1) {
    		return redirect()->route('user.dashboard');
    	}
    	if (!session('verifyTime') || session('verifyTime') < now()) {
        	$this->sendVerifyMail($user);
        	$verifyTime = now()->addMinutes(1);
        	session()->put('verifyTime',$verifyTime);
    	}
    	return view($this->theme.'user.auth.verify');
    }

    public function verifyResend(){
    	session()->flash('resent',1);
    	$user = auth()->user();
    	$this->sendVerifyMail($user);
        $verifyTime = now()->addMinutes(1);
        session()->put('verifyTime',$verifyTime);
    	$alert = ['success','Verification mail sent successfully'];
    	return redirect()->route('user.verification')->withAlert($alert);
    }

    protected function sendVerifyMail($user){
        $mail = new SendMail();
    	$user = auth()->user();
        $link = route('user.verify',encrypt($user->id));
        $data = [
            'name'=>$user->fullname(),
            'email'=>$user->email,
            'template'=>'verify_account',
            'value'=>[
                'link'=>$link
            ]
        ];
        $mail->sendMail($data);
    }

    public function verify($hash){
    	$user = auth()->user();
    	$id = decrypt($hash);
    	if ($user->email_verified == 0) {
	    	$user->email_verified = 1;
	    	$user->save();
	    	$alert = ['success','Email verified successfull'];
    	}else{
	    	$alert = ['success','Your email is already verified'];
    	}
    	return redirect()->route('user.dashboard')->withAlert($alert);
    }

    public function banned(){
    	if (auth()->user()->status) {
    		return redirect()->route('user.dashboard');
    	}
    	return view($this->theme.'user.auth.banned');
    }
}
