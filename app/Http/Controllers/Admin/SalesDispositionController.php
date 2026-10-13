<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesGenioController;
use App\Models\ListingDisposition;
use App\Models\SalesUser;
use Illuminate\Http\Request;

class SalesDispositionController extends Controller
{
    public function index(Request $request)
    {
        $query = ListingDisposition::query()
            ->with(['listing:id,name,mobile,city', 'salesUser:id,name,empcode', 'location'])
            ->when($request->filled('disposition'), fn ($q) => $q->where('disposition', $request->disposition))
            ->when($request->filled('call_back'), fn ($q) => $q->where('call_back', 1))
            ->when($request->filled('sales_user_id'), fn ($q) => $q->where('sales_user_id', (int) $request->sales_user_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('city'), fn ($q) => $q->whereHas('listing', fn ($l) => $l->where('city', 'like', '%' . trim($request->city) . '%')));

        // Count per disposition for whatever the filters select (all pages).
        $summary = (clone $query)->reorder()
            ->selectRaw('disposition, COUNT(*) AS n')
            ->groupBy('disposition')
            ->orderByDesc('n')
            ->pluck('n', 'disposition');

        $rows = $query->latest()->paginate(30)->withQueryString();

        $options = SalesGenioController::dispositionOptions();
        $salesUsers = SalesUser::orderBy('name')->get(['id', 'name', 'empcode']);

        return view('admin.sales_dispositions.index', compact('rows', 'options', 'summary', 'salesUsers'));
    }

    public function destroy($id)
    {
        ListingDisposition::findOrFail($id)->delete();
        return back()->withAlert(['success', 'Disposition deleted.']);
    }
}
