<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Listing;
use App\Models\Service;
use App\Models\Theme;

class SitemapController extends Controller
{
    public function __construct()
    {
        try {
            $this->setting = \App\Models\Setting::first();
            $this->theme = 'themes.'.$this->setting->theme.'.';
        } catch (\Exception $e) {
            $this->setting = null;
        }
    }
    
    public function index()
    {
        // The view only ever reads a slug and an updated_at, so only those are
        // selected. Hydrating whole rows cost ~106 MB for the listings alone —
        // on an endpoint crawlers hit far more often than any human does.
        $categories = Category::where('status', 1)
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        $subcategories = Subcategory::where('status', 1)
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        // Category comes along so the sitemap can build the real canonical
        // `listings/{category}/{slug}` URL instead of the redirecting
        // `/listings/{slug}` shortcut.
        $listings = Listing::where('status', 1)
            ->select(['id', 'slug', 'category_id', 'updated_at'])
            ->with(['category' => function ($query) {
                $query->select(['id', 'slug']);
            }])
            ->get();

        $services = Service::where('status', 1)
            ->select(['id', 'slug', 'updated_at'])
            ->get();
       $blogs = Theme::where('theme_name', $this->setting->theme)
        ->where('name', 'blog')
        ->where('is_multiple', 1)
        ->get();

        return response()
            ->view('sitemap', compact('categories', 'subcategories', 'listings', 'services', 'blogs'))
            ->header('Content-Type', 'application/xml');
    }
}