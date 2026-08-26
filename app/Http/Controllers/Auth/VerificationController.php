<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    	// Signed in over WhatsApp: the address on the row is a mobile-derived
    	// placeholder (RegisterController::quickAuthSend), so there is nothing
    	// to verify and no mailbox to verify it from. Sending them here at all
    	// was what produced the SMTP 500 — take them to their listings instead.
    	if ($user->isMobileVerified()) {
    		return redirect()->route('user.listing.index');
    	}
    	if (!session('verifyTime') || session('verifyTime') < now()) {
        	if ($this->sendVerifyMail($user)) {
	        	$verifyTime = now()->addMinutes(1);
	        	session()->put('verifyTime',$verifyTime);
        	}else{
        		session()->flash('alert',['danger','We could not send the verification mail right now. Please try again in a moment.']);
        	}
    	}
    	return view($this->theme.'user.auth.verify');
    }

    public function verifyResend(){
    	session()->flash('resent',1);
    	$user = auth()->user();
    	if ($user->isMobileVerified()) {
    		return redirect()->route('user.listing.index');
    	}
    	if (!$this->sendVerifyMail($user)) {
    		return redirect()->route('user.verification')->withAlert(['danger','We could not send the verification mail right now. Please try again in a moment.']);
    	}
        $verifyTime = now()->addMinutes(1);
        session()->put('verifyTime',$verifyTime);
    	$alert = ['success','Verification mail sent successfully'];
    	return redirect()->route('user.verification')->withAlert($alert);
    }

    /**
     * A mail server refusing the address (PHPMailer throws for that) is not a
     * reason to hand the account holder a 500 page — the account is fine, only
     * the delivery failed. Report it and let the view render.
     *
     * @return bool whether the mail actually went out
     */
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

        try {
            $mail->sendMail($data);
        } catch (\Throwable $exception) {
            Log::error('Verification mail failed', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        return true;
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
