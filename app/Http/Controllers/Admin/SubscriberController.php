<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Libs\SendMail;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(){
    	$subscribers = Subscriber::orderBy('id','desc')->paginate(15);
    	return view('admin.subscriber.index',compact('subscribers'));
    }

    public function remove($id){
    	$subscriber = Subscriber::findOrFail($id);
    	$subscriber->delete();
    	$alert = ['success','Subscriber deleted successfully'];
    	return back()->withAlert($alert);
    }

    public function sendMail(){
        return view('admin.subscriber.mail');
    }

    public function submitMail(Request $request,SendMail $mail){
        $request->validate([
            'subject'=>'required',
            'message'=>'required',
        ]);
        $subscribers = Subscriber::all();
        foreach ($subscribers as $subscriber) {
            $data = [
                'email'=>$subscriber->email,
                'template'=>null,
                'subject'=>$request->subject,
                'value'=>[
                    'message'=>$request->message
                ]
            ];
            $mail->sendMail($data);
        }
        $alert = ['success','Mail sent successfully'];
        return back()->withAlert($alert);
    }
}
