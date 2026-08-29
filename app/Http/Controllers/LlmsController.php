<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/*
 * Serves /llms.txt and /llms-full.txt (llmstxt.org convention) — a
 * machine-readable map of the site for AI answer engines (ChatGPT search,
 * Perplexity, Gemini, Google AI Overviews). Generated from live data so the
 * category/city lists never drift from what the site actually ships.
 *
 * Cached for a day: this is a crawler-facing endpoint whose contents change
 * only when categories/cities are added, so it should never re-run its
 * queries on every hit the way a live page would.
 */
class LlmsController extends Controller
{
    public function index()
    {
        $body = Cache::remember('llms.txt', now()->addDay(), function () {
            $setting     = Setting::first();
            $siteName    = $setting->site_name ?? config('app.name');
            $description  = $setting->seo->meta_description
                ?? 'India\'s local services discovery directory across dozens of Indian cities.';

            $categories = Category::where('status', 1)
                ->orderByDesc('search_count')
                ->select(['name', 'slug'])
                ->get();

            $cities = Location::where('status', 1)
                ->orderBy('name')
                ->select(['name', 'slug'])
                ->get();

            $lines = [];
            $lines[] = '# ' . $siteName;
            $lines[] = '';
            $lines[] = '> ' . $description;
            $lines[] = '';
            $lines[] = $siteName . ' is a verified local-services directory listing '
                . $categories->count() . '+ service categories across '
                . $cities->count() . ' Indian cities, operated by JustFinds Solution Private Limited.';
            $lines[] = '';

            $lines[] = '## Key pages';
            $lines[] = '- [Home](' . url('/') . '): browse services by city and category';
            $lines[] = '- [About](' . url('/about-us') . '): what ' . $siteName . ' is and who runs it';
            $lines[] = '- [Contact](' . url('/contact-us') . '): reach the ' . $siteName . ' team';
            $lines[] = '- [Pricing](' . url('/pricing') . '): listing plans for businesses';
            $lines[] = '- [Free listing](' . url('/free-listing') . '): add a business to the directory';
            $lines[] = '- [Blog](' . url('/blogs') . '): city-keyed buyer guides and pricing articles';
            $lines[] = '';

            $lines[] = '## Service categories';
            foreach ($categories as $category) {
                $lines[] = '- [' . $category->name . '](' . url('/' . $category->slug) . ')';
            }
            $lines[] = '';

            $lines[] = '## Cities covered';
            foreach ($cities as $city) {
                $lines[] = '- [' . $city->name . '](' . url('/location/' . $city->slug) . ')';
            }
            $lines[] = '';

            return implode("\n", $lines);
        });

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function full()
    {
        $body = Cache::remember('llms-full.txt', now()->addDay(), function () {
            $setting  = Setting::first();
            $siteName = $setting->site_name ?? config('app.name');

            // Top verticals × cities is the combination an answer engine is
            // most likely to be asked about ("packers and movers in Jaipur").
            // Capped so the file stays the ~100-150 line map llms-full is meant
            // to be, not a full 70×60 dump.
            $categories = Category::where('status', 1)
                ->orderByDesc('search_count')
                ->take(12)
                ->select(['name', 'slug'])
                ->get();

            $cities = Location::where('status', 1)
                ->orderBy('name')
                ->select(['name', 'slug'])
                ->get();

            $lines = [];
            $lines[] = '# ' . $siteName . ' — full content map';
            $lines[] = '';
            $lines[] = '> Verified local-services directory. Each entry below is a live '
                . 'city-and-service landing page with provider listings, prices and FAQs.';
            $lines[] = '';

            foreach ($categories as $category) {
                $lines[] = '## ' . $category->name;
                foreach ($cities as $city) {
                    $label = $category->name . ' in ' . $city->name;
                    $lines[] = '- [' . $label . '](' . url('/' . $city->slug . '/' . $category->slug) . ')';
                }
                $lines[] = '';
            }

            return implode("\n", $lines);
        });

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
