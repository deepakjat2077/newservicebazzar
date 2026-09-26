<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Http\Libs\SendMail;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{

    public function all()
    {
    	$deposits = Payment::where('status','!=',0)->orderBy('id','desc')->with('user','method_currency','method_currency.paymentMethod','listing')->paginate(20);
    	$title = 'All Deposits';
    	$salesOrders = $this->paidSalesOrders();
    	return view('admin.deposit.index',compact('deposits','title','salesOrders'));
    }

    public function approved(){
        $deposits = Payment::where('status',1)->orderBy('id','desc')->with('user','method_currency','method_currency.paymentMethod','listing')->paginate(20);
        $title = 'Approved Deposits';
        $salesOrders = $this->paidSalesOrders();
        return view('admin.deposit.index',compact('deposits','title','salesOrders'));
    }

    /** Payments taken through Sales Genio order links live in sales_orders, not payments. */
    protected function paidSalesOrders()
    {
        return \App\Models\SalesOrder::where('status', \App\Models\SalesOrder::PAID)
            ->with(['listing:id,name,city', 'items:id,sales_order_id,name,plan', 'salesUser'])
            ->latest('paid_at')
            ->paginate(20, ['*'], 'orders_page');
    }

    public function pending(){
        $deposits = Payment::where('status',2)->orderBy('id','desc')->with('user','method_currency','method_currency.paymentMethod','listing')->paginate(20);
        $title = 'Pending Deposits';
        return view('admin.deposit.index',compact('deposits','title'));
    }

    public function rejected(){
        $deposits = Payment::where('status',3)->orderBy('id','desc')->with('user','method_currency','method_currency.paymentMethod','listing')->paginate(20);
        $title = 'Rejected Deposits';
        return view('admin.deposit.index',compact('deposits','title'));
    }

    public function details($id){
    	$deposit = Payment::with('listing','user','method_currency','method_currency.paymentMethod')->findOrFail($id);
    	return view('admin.deposit.details',compact('deposit'));
    }

    public function action(Request $request,$id,Booster $booster,SendMail $mail){
    	$request->validate([
    		'action'=>'required|integer|in:1,2',
    		'reason'=>'required'
    	]);

    	// Lock the row and re-check it is still pending (status 2) inside the
    	// transaction so a double-click or a concurrent second request can
    	// never credit the same deposit twice.
    	$result = DB::transaction(function () use ($request, $id, $booster) {
    		$payment = Payment::where('id', $id)->lockForUpdate()->first();

    		if (!$payment || (int) $payment->status !== 2) {
    			return null;
    		}

    		$user = $payment->user;
    		$payment->reason = $request->reason;

    		if ($request->action == 1) {
    			$payment->status = 1;
    			$booster->userBalance($user,$payment->amount,'+');
    			$booster->storeTrx([
					'user'=>$user,
					'trx_number'=>$payment->trx_number,
					'amount'=>$payment->amount,
					'charge'=>$payment->charge,
					'after_charge'=>$payment->after_charge,
					'type'=>'+',
					'remark'=>'deposit',
					'details'=>'Deposit via '.$payment->method_currency->paymentMethod->name,
		    	]);
    			$template = 'deposit_approve';
    		}else{
    			$payment->status = 3;
    			$template = 'deposit_reject';
    		}

    		$payment->save();

    		return ['payment' => $payment, 'user' => $user, 'template' => $template];
    	});

    	if (!$result) {
    		$alert = ['danger','This deposit has already been processed'];
    		return back()->withAlert($alert);
    	}

    	$payment = $result['payment'];
    	$user = $result['user'];
    	$setting = Setting::first();

        $data = [
            'name'=>$user->fullname(),
            'email'=>$user->email,
            'template'=>$result['template'],
            'value'=>[
                "amount"=>numberFormat($payment->amount),
                "site_currency"=>$setting->site_currency,
                "method_name"=>$payment->methodName(),
                "method_currency"=>$payment->method_currency->currency,
                "reason"=>$payment->reason,
                "trx"=>$payment->trx_number,
                "current_balance"=>numberFormat($user->balance),
            ]
        ];
        $mail->sendMail($data);

        $alert = $request->action == 1
            ? ['success','payment approved successfully']
            : ['success','payment rejected successfully'];

    	return back()->withAlert($alert);
    }
}
