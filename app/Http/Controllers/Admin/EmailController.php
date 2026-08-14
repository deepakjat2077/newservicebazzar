<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use HTMLPurifier;

class EmailController extends Controller
{
    
    public function index(){
    	return view('admin.setting.email.index');
    }

    public function emailUpdate(Request $request){
    	$request->validate([
    		'host'=>'required',
    		'port'=>'required|integer',
    		'encryption'=>'required|in:ssl,tls',
    		'username'=>'required',
    		// Password is optional on update — the edit form never re-displays
    		// the stored value, so a blank submission means "keep the current
    		// password" rather than "clear it".
    		'password'=>'nullable',
            'email_from'=>'required',
    	]);
    	$setting = Setting::first();
    	$setting->mail = [
	    	'host' => $request->host,
	    	'port' => $request->port,
	    	'encryption' => $request->encryption,
	    	'username' => $request->username,
	    	'password' => $request->filled('password') ? $request->password : (@$setting->mail->password),
            'email_from' => $request->email_from,
    	];
    	$setting->save();

    	$alert = ['success','Mail configuration updated successfully'];
        return back()->withAlert($alert);
    }

    public function emailUpdateTemplate(Request $request){
        $request->validate([
            'template'=>'required'
        ]);

        $purifier = new HTMLPurifier();
        $template = $purifier->purify($request->template);

        $setting = Setting::first();
        $setting->email_template = $template;
        $setting->save();
        $alert = ['success','Mail template updated successfully'];
        return back()->withAlert($alert);
    }

    public function testMail(Request $request,SendMail $mail){
        $request->validate([
            'email'=>'required|email'
        ]);
        $data = [
            'name'=>'Admin',
            'email'=>$request->email,
            'subject'=>'Test Mail',
            'template'=>'test_mail',
            'value'=>[
                'message'=>'This is a test email from your Directory360 system.'
            ]
        ];
        $mail->sendMail($data);
        $alert = ['success','Mail sent successfully'];
        return back()->withAlert($alert);
    }
}
