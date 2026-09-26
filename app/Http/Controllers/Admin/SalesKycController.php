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

    /** Read the number off a PAN/GST scan with AI and check it. */
    public function aiRead($id, $docId)
    {
        $doc = ListingKycDocument::where('listing_id', $id)->findOrFail($docId);
        $result = \App\Support\KycReader::run($doc);

        if (empty($result['ok'])) {
            return back()->withAlert(['error', $result['message'] ?? 'Could not read the document.']);
        }

        return back()->withAlert(['success', !empty($result['filled'])
            ? 'Number read and saved: ' . $result['number']
            : 'Document read. Check the result below.']);
    }

    /** Save (or correct) the number for every file of this document type. */
    public function saveNumber(Request $request, $id, $docId)
    {
        $doc = ListingKycDocument::where('listing_id', $id)->findOrFail($docId);
        $number = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('doc_number'))) ?: null;

        $ok = match ($doc->doc_type) {
            'pan' => !$number || \App\Support\KycReader::validPan($number),
            'gst' => !$number || \App\Support\KycReader::validGstin($number),
            default => true,
        };
        if (!$ok) {
            return back()->withAlert(['error', 'That ' . strtoupper($doc->doc_type) . ' number is not valid.']);
        }

        ListingKycDocument::where('listing_id', $id)->where('doc_type', $doc->doc_type)->update(['doc_number' => $number]);

        return back()->withAlert(['success', $number ? 'Number saved.' : 'Number cleared.']);
    }

    public function updateStatus(Request $request, $id, $docId)
    {
        $request->validate(['status' => 'required|in:approved,rejected,in_review']);
        $doc = ListingKycDocument::where('listing_id', $id)->findOrFail($docId);
        $doc->update(['status' => $request->status]);
        return back()->withAlert(['success', 'Document status updated.']);
    }
}
