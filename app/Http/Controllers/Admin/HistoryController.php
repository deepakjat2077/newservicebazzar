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
}
