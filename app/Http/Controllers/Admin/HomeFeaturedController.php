<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFeaturedListing;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin > Featured Listings: the admin picks which listings the home page's
 * "Featured Listings" section shows, and in what order. Nothing to do with
 * payments or plans.
 */
class HomeFeaturedController extends Controller
{
    public const MAX_SHOWN = 12;

    public function index()
    {
        $items = HomeFeaturedListing::with(['listing.category', 'listing.location'])
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.home_featured.index', ['items' => $items, 'maxShown' => self::MAX_SHOWN]);
    }

    /** Live search for the "Add a listing" box: name, id or phone. */
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $taken = HomeFeaturedListing::pluck('listing_id');

        $rows = Listing::query()
            ->where('status', 1)
            ->whereNotIn('id', $taken)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')->orWhere('mobile', 'like', '%' . $term . '%');
                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                }
            })
            ->with('category:id,name')
            ->orderBy('name')->limit(15)
            ->get(['id', 'name', 'city', 'area', 'category_id']);

        return response()->json($rows->map(fn ($l) => [
            'id'   => $l->id,
            'name' => $l->name,
            'meta' => collect([optional($l->category)->name, trim(($l->area ? $l->area . ', ' : '') . $l->city, ', ')])->filter()->implode(' · '),
        ]));
    }

    public function store(Request $request)
    {
        $request->validate(['listing_id' => 'required|integer|exists:listings,id']);

        HomeFeaturedListing::firstOrCreate(
            ['listing_id' => (int) $request->listing_id],
            ['sort_order' => (int) HomeFeaturedListing::max('sort_order') + 1, 'is_active' => true]
        );

        return back()->withAlert(['success', 'Listing added to Featured Listings.']);
    }

    public function update(Request $request, $id)
    {
        $item = HomeFeaturedListing::findOrFail($id);

        $data = $request->validate([
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $item->update([
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at'   => $data['ends_at'] ?? null,
        ]);

        return back()->withAlert(['success', 'Dates saved.']);
    }

    public function toggle($id)
    {
        $item = HomeFeaturedListing::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        return back()->withAlert(['success', $item->is_active ? 'Shown on the home page.' : 'Hidden from the home page.']);
    }

    /** Moves one row up or down by swapping it with its neighbour. */
    public function move($id, $dir)
    {
        $items = HomeFeaturedListing::orderBy('sort_order')->orderBy('id')->get()->values();
        $i = $items->search(fn ($row) => $row->id === (int) $id);
        $j = $dir === 'up' ? $i - 1 : $i + 1;

        if ($i !== false && isset($items[$j])) {
            $ids = $items->pluck('id')->all();
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];

            DB::transaction(function () use ($ids) {
                foreach ($ids as $pos => $rowId) {
                    HomeFeaturedListing::whereKey($rowId)->update(['sort_order' => $pos + 1]);
                }
            });
            HomeFeaturedListing::refreshHome();
        }

        return back();
    }

    public function destroy($id)
    {
        HomeFeaturedListing::findOrFail($id)->delete();

        return back()->withAlert(['success', 'Removed from Featured Listings.']);
    }
}
