<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesGenioController;
use App\Models\Listing;
use App\Models\ListingKycDocument;
use Illuminate\Http\Request;

class SalesKycController extends Controller
{
    public function index(Request $request)
    {
        // Listings that have at least one KYC document, newest submission first.
        $listingIds = ListingKycDocument::query()
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->distinct()
            ->pluck('listing_id');

        $listings = Listing::whereIn('id', $listingIds)
            ->withCount('kycDocuments')
            ->with('salesUser')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.sales_kyc.index', compact('listings'));
    }

    public function show($id)
    {
        $listing = Listing::with('salesUser')->findOrFail($id);
        $docs    = ListingKycDocument::where('listing_id', $id)->get()->groupBy('doc_type');
        $types   = SalesGenioController::kycTypes();
        return view('admin.sales_kyc.show', compact('listing', 'docs', 'types'));
    }

    public function updateStatus(Request $request, $id, $docId)
    {
        $request->validate(['status' => 'required|in:approved,rejected,in_review']);
        $doc = ListingKycDocument::where('listing_id', $id)->findOrFail($docId);
        $doc->update(['status' => $request->status]);
        return back()->withAlert(['success', 'Document status updated.']);
    }
}
