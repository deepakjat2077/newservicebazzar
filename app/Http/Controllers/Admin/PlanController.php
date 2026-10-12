<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingSalesPackage;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'active'); // active | expired | all

        $query = ListingSalesPackage::with(['listing.user', 'listing', 'package'])
            ->orderByDesc('created_at');

        if ($status === 'active') {
            $query->where('expires_at', '>', now());
        } elseif ($status === 'expired') {
            $query->where('expires_at', '<=', now());
        }

        $plans = $query->paginate(25)->withQueryString();

        $totalActive  = ListingSalesPackage::where('expires_at', '>', now())->count();
        $totalExpired = ListingSalesPackage::where('expires_at', '<=', now())->count();
        $totalAll     = ListingSalesPackage::count();

        return view('admin.plans.index', compact('plans', 'status', 'totalActive', 'totalExpired', 'totalAll'));
    }
}
