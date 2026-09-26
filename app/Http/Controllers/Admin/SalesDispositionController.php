<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesGenioController;
use App\Models\ListingDisposition;
use Illuminate\Http\Request;

class SalesDispositionController extends Controller
{
    public function index(Request $request)
    {
        $rows = ListingDisposition::query()
            ->with(['listing:id,name,mobile,city', 'salesUser:id,name,empcode'])
            ->when($request->filled('disposition'), fn ($q) => $q->where('disposition', $request->disposition))
            ->when($request->filled('call_back'), fn ($q) => $q->where('call_back', 1))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $options = SalesGenioController::dispositionOptions();

        return view('admin.sales_dispositions.index', compact('rows', 'options'));
    }

    public function destroy($id)
    {
        ListingDisposition::findOrFail($id)->delete();
        return back()->withAlert(['success', 'Disposition deleted.']);
    }
}
