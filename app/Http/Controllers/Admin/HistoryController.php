<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;

class HistoryController extends Controller
{
    public function loginHistory(){
    	$histories = LoginHistory::orderBy('id','desc')->with('user')->paginate(20);
    	return view('admin.histories.login_history',compact('histories'));
    }

    /** Admin panel sign-ins: who, when, where (GPS or IP city), browser and device. */
    public function adminLoginHistory(\Illuminate\Http\Request $request)
    {
        $q = \App\Models\AdminLoginLog::with('admin')->latest('id');

        if ($request->filled('admin_id')) {
            $q->where('admin_id', $request->admin_id);
        }
        if ($request->filled('event')) {
            $q->where('event', $request->event);
        }
        if ($request->filled('date')) {
            $q->whereDate('created_at', $request->date);
        }

        return view('admin.histories.admin_login_history', [
            'logs'   => $q->paginate(30)->withQueryString(),
            'admins' => \App\Models\Admin::orderBy('username')->get(['id', 'username', 'first_name', 'last_name']),
        ]);
    }
}
