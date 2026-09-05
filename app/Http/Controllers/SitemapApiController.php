<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Listing;
use Illuminate\Http\Request;

class SitemapApiController extends Controller
{
    /**
     * Get ALL city-category combinations sorted alphabetically
     */
    public function popularCombinations()
    {
        // Get ALL city-category combinations with listings, sorted alphabetically
        $combinations = \DB::table('listings')
            ->join('locations', 'listings.location_id', '=', 'locations.id')
            ->join('categories', 'listings.category_id', '=', 'categories.id')
            ->where('listings.status', 1)
            ->where('locations.status', 1)
            ->where('categories.status', 1)
            ->select('locations.slug as city_slug', 'locations.name as city_name',
                     'categories.slug as category_slug', 'categories.name as category_name',
                     \DB::raw('COUNT(*) as count'))
            ->groupBy('locations.id', 'categories.id')
            ->orderBy('locations.name')  // Sort by city name alphabetically
            ->orderBy('categories.name')  // Then by category name alphabetically
            ->get();

        return response()->json([
            'combinations' => $combinations->map(fn($combo) => [
                'city_name' => $combo->city_name,
                'city_slug' => $combo->city_slug,
                'category_name' => $combo->category_name,
                'category_slug' => $combo->category_slug,
                'count' => $combo->count,
                'url' => url($combo->city_slug . '/' . $combo->category_slug),
            ]),
        ]);
    }

    /**
     * Get ALL city-subcategory combinations from the subcategories table.
     *
     * Subcategories live in their own `subcategories` table (each has a
     * parent category_id). A listing links to them through its JSON
     * `subcategory_ids` column, and each subcategory has its own page at
     * /listings/subcategory/{slug}. We walk every live listing, expand its
     * subcategory_ids, and pair each real subcategory with the listing's
     * city — so the directory shows genuine "Parent Category > Subcategory
     * in City" rows, not sibling categories mislabelled as subcategories.
     */
    public function subcategoryCombinations()
    {
        // Preload every live subcategory keyed by id, with its parent
        // category name — one query instead of one per listing.
        $subcategories = \DB::table('subcategories')
            ->join('categories', 'subcategories.category_id', '=', 'categories.id')
            ->where('subcategories.status', 1)
            ->where('categories.status', 1)
            ->select('subcategories.id', 'subcategories.name', 'subcategories.slug',
                     'categories.name as category_name', 'categories.slug as category_slug')
            ->get()
            ->keyBy('id');

        // Fetch all live listings that carry subcategory_ids, with their city.
        $listings = \DB::table('listings')
            ->join('locations', 'listings.location_id', '=', 'locations.id')
            ->where('listings.status', 1)
            ->where('locations.status', 1)
            ->whereNotNull('listings.subcategory_ids')
            ->select('locations.slug as city_slug', 'locations.name as city_name',
                     'listings.subcategory_ids')
            ->get();

        // Build city + subcategory combinations, deduped by a composite key.
        $seen = [];
        $combinations = [];
        foreach ($listings as $listing) {
            $subcategoryIds = json_decode($listing->subcategory_ids, true);
            if (!is_array($subcategoryIds)) continue;

            foreach ($subcategoryIds as $subId) {
                // subcategory_ids stores ids as strings; normalise to int.
                $sub = $subcategories->get((int) $subId);
                if (!$sub) continue;

                $key = $listing->city_slug . '|' . $sub->slug;
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $combinations[] = [
                    'city_name' => $listing->city_name,
                    'city_slug' => $listing->city_slug,
                    'category_name' => $sub->category_name,
                    'category_slug' => $sub->category_slug,
                    'subcategory_name' => $sub->name,
                    'subcategory_slug' => $sub->slug,
                    'url' => url('/listings/subcategory/' . $sub->slug),
                ];
            }
        }

        // Sort alphabetically: City -> Parent Category -> Subcategory.
        usort($combinations, function ($a, $b) {
            return [$a['city_name'], $a['category_name'], $a['subcategory_name']]
                <=> [$b['city_name'], $b['category_name'], $b['subcategory_name']];
        });

        return response()->json(['combinations' => $combinations]);
    }

    /**
     * Get all categories available in a specific city
     */
    public function cityCategories($slug)
    {
        $city = Location::where('slug', $slug)->where('status', 1)->first();

        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        // Get categories that have listings in this city
        $categories = Category::where('status', 1)
            ->whereHas('listings', function ($query) use ($city) {
                $query->where('location_id', $city->id)
                    ->where('status', 1);
            })
            ->orderBy('name')
            ->select('id', 'name', 'slug')
            ->get();

        return response()->json([
            'city' => $city->name,
            'categories' => $categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ]),
        ]);
    }

    /**
     * Get listings for a city and category (simple list format like JustDial)
     */
    public function cityListings($citySlug, $categorySlug)
    {
        $city = Location::where('slug', $citySlug)->where('status', 1)->first();
        $category = Category::where('slug', $categorySlug)->where('status', 1)->first();

        if (!$city || !$category) {
            return response()->json(['error' => 'City or Category not found'], 404);
        }

        // Get listings for this city and category
        $listings = Listing::where('location_id', $city->id)
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->orderBy('name')
            ->select('id', 'name', 'slug')
            ->limit(100)
            ->get();

        return response()->json([
            'city' => $city->name,
            'category' => $category->name,
            'total' => count($listings),
            'listings' => $listings->map(fn($listing) => [
                'id' => $listing->id,
                'name' => $listing->name,
                'slug' => $listing->slug,
                'url' => url('/listings/' . $category->slug . '/' . $listing->slug),
            ]),
        ]);
    }
}
