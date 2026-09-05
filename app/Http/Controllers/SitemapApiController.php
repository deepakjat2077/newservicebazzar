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
