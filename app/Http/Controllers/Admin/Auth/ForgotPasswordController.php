<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showCodeRequestForm()
    {
        return view('admin.auth.passwords.email');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('admins');
    }

    public function sendResetCodeEmail(Request $request,SendMail $mail)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = Admin::where('email', $request->email)->first();

        // Respond identically whether or not the account exists, so this
        // endpoint can't be used to enumerate admin email addresses.
        if ($user) {
            // Invalidate any previously-issued codes for this admin before
            // issuing a new one, so stale codes can't be brute-forced later.
            AdminPasswordReset::where('email', $user->email)->delete();

            $code = verification_code(6);

            AdminPasswordReset::create([
                'email' => $user->email,
                'token' => $code,
                'status' => 0,
                'expires_at' => now()->addMinutes(15),
                'created_at' => date("Y-m-d h:i:s")
            ]);

            $data = [
                'name'=>$user->username,
                'email'=>$user->email,
                'template'=>'verification_code',
                'value'=>[
                    "code"=>$code,
                ]
            ];
            $mail->sendMail($data);
        }

        return redirect()->route('admin.password.verify');
    }

    public function verify(){
    	return view('admin.auth.passwords.code');
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required']);
        return redirect()->route('admin.password.reset.change', $request->code);
    }
}
