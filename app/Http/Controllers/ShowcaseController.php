<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ShowcaseListing;
use Illuminate\Http\Request;

/**
 * Admin pick-list for the "Get Listed / Grow Your Business" screen. The admin
 * searches real listings, adds the ones to feature, orders them and toggles
 * them on/off. The app reads them through Api\ContentController@getListed.
 */
class ShowcaseController extends Controller
{
    public function index(Request $request)
    {
        $showcase = ShowcaseListing::with('listing.category')
            ->orderBy('sort_order')->orderByDesc('id')->get();

        // Search box to find listings to add (skip ones already showcased).
        $q = trim((string) $request->query('q'));
        $results = $q === '' ? collect() : Listing::where('status', 1)
            ->where('name', 'like', "%{$q}%")
            ->whereNotIn('id', $showcase->pluck('listing_id')->all())
            ->orderBy('name')->limit(20)->get(['id', 'name', 'city', 'thumb']);

        return view('admin.showcase.index', compact('showcase', 'results', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
        ]);

        // Idempotent — listing_id is unique, so a repeat add just no-ops.
        ShowcaseListing::firstOrCreate(
            ['listing_id' => $request->listing_id],
            ['sort_order' => (int) ShowcaseListing::max('sort_order') + 1, 'status' => 1]
        );

        return back()->with('success', 'Listing added to the Get Listed showcase');
    }

    public function toggle($id)
    {
        $row = ShowcaseListing::findOrFail($id);
        $row->status = ! $row->status;
        $row->save();

        return back()->with('success', 'Showcase listing status updated');
    }

    public function move($id, $dir)
    {
        $row = ShowcaseListing::findOrFail($id);

        // Swap sort_order with the neighbour in the requested direction.
        $neighbour = ShowcaseListing::when($dir === 'up',
                fn ($q) => $q->where('sort_order', '<', $row->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $row->sort_order)->orderBy('sort_order'))
            ->first();

        if ($neighbour) {
            [$row->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $row->sort_order];
            $row->save();
            $neighbour->save();
        }

        return back();
    }

    public function delete($id)
    {
        ShowcaseListing::findOrFail($id)->delete();

        return back()->with('success', 'Listing removed from the showcase');
    }
}
