<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Setting;
use App\Support\MailProbe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    /**
     * The whole point of this button is to find out what is wrong with the
     * mail settings, so it must not answer with a stack trace: sending through
     * SendMail let PHPMailer's exception escape as a 500, which told the admin
     * nothing except that something broke.
     *
     * MailProbe runs the same send and keeps the server's side of the
     * conversation, so the reply that actually explains the refusal — a 535, a
     * 550, an SSL/TLS port mismatch — is what comes back.
     */
    public function testMail(Request $request){
        $request->validate([
            'email'=>'required|email'
        ]);

        $result = MailProbe::send(
            $request->email,
            'Test Mail',
            '<p>This is a test email from your ' . (Setting::first()->site_name ?? config('app.name')) . ' website.</p>'
        );

        if ($result['ok']) {
            return back()->withAlert(['success', 'Mail sent successfully to ' . $request->email]);
        }

        Log::error('Test mail failed', [
            'error'      => $result['error'],
            'reply'      => $result['reply'],
            'transcript' => array_slice($result['transcript'], -12),
        ]);

        $message = $result['error'];

        if ($result['reply']) {
            $message .= ' Server said: ' . $result['reply'] . '.';
        }

        if ($result['hint']) {
            $message .= ' ' . $result['hint'];
        }

        return back()->withAlert(['danger', $message]);
    }
}
