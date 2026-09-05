<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;

class SitemapApiController extends Controller
{
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
     * Get all subcategories for a category in a specific city
     */
    public function categorySubcategories($citySlug, $categorySlug)
    {
        $city = Location::where('slug', $citySlug)->where('status', 1)->first();
        $category = Category::where('slug', $categorySlug)->where('status', 1)->first();

        if (!$city || !$category) {
            return response()->json(['error' => 'City or Category not found'], 404);
        }

        // Get subcategories that have listings in this city for this category
        $subcategories = $category->children()
            ->where('status', 1)
            ->whereHas('listings', function ($query) use ($city) {
                $query->where('location_id', $city->id)
                    ->where('status', 1);
            })
            ->orderBy('name')
            ->select('id', 'name', 'slug', 'parent_id')
            ->get();

        return response()->json([
            'city' => $city->name,
            'category' => $category->name,
            'subcategories' => $subcategories->map(fn($subcat) => [
                'id' => $subcat->id,
                'name' => $subcat->name,
                'slug' => $subcat->slug,
            ]),
        ]);
    }
}
