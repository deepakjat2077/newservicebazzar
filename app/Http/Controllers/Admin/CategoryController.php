<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\CategoryFilter;
use App\Support\HttpRetry;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
	use FileHandler;

 public function index(){
    $categories = Category::with('filters')->paginate(20);
    return view('admin.category.index', compact('categories'));
}

   

public function createPage()
{
    return view('admin.category.create');
}

public function create(Request $request)
{
    $request->validate([
        'name'                  => 'required|string|max:255|unique:categories,name',
        'image'                 => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',

        'desktop_banner'        => 'nullable|array',
        'desktop_banner.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',

        'mobile_banner'         => 'nullable|array',
        'mobile_banner.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',

        'desktop_banner_link'   => 'nullable|array',
        'desktop_banner_link.*' => 'nullable|string|max:500',

        'mobile_banner_link'    => 'nullable|array',
        'mobile_banner_link.*'  => 'nullable|string|max:500',

        'filters'               => 'nullable|array',
        'filters.*'             => 'nullable|string|max:255',

        'seo_title'             => 'nullable|string|max:255',
        'seo_keywords'          => 'nullable|string|max:500',
        'seo_description'       => 'nullable|string',
        'description'           => 'nullable|string',
        'icon'                  => 'nullable|string|max:255',
    ]);

    try {
        $image = null;
        $desktopBanner = null;
        $mobileBanner = null;

        // Category Image Upload
        if ($request->hasFile('image')) {
            $imageData = [
                'file'     => $request->file('image'),
                'path'     => 'assets/images/category/',
                'prevFile' => null,
            ];
            $image = $this->uploadFile('image', $imageData);
        }

        // First Desktop Banner Upload from array
        if ($request->hasFile('desktop_banner')) {
            foreach ($request->file('desktop_banner') as $file) {
                if ($file) {
                    $desktopBannerData = [
                        'file'     => $file,
                        'path'     => 'assets/images/category/banner/',
                        'size'     => '1920x600',
                        'prevFile' => null,
                    ];
                    $desktopBanner = $this->uploadFile('image', $desktopBannerData);
                    break;
                }
            }
        }

        // First Mobile Banner Upload from array
        if ($request->hasFile('mobile_banner')) {
            foreach ($request->file('mobile_banner') as $file) {
                if ($file) {
                    $mobileBannerData = [
                        'file'     => $file,
                        'path'     => 'assets/images/category/banner/',
                        'size'     => '768x500',
                        'prevFile' => null,
                    ];
                    $mobileBanner = $this->uploadFile('image', $mobileBannerData);
                    break;
                }
            }
        }

        // Unique Slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        // Save Category
        $category = new Category();
        $category->name = $request->name;
        $category->slug = $slug;
        $category->image = $image;
        $category->desktop_banner = $desktopBanner;
        $category->mobile_banner = $mobileBanner;
        $category->icon = $request->icon;
        $category->seo_title = $request->seo_title;
        $category->seo_keywords = $request->seo_keywords;
        $category->seo_description = $request->seo_description;
        // Normalised on the way in, so what is stored is already the same
        // small set of tags the category page is styled for — see ContentHtml.
        $category->description = \App\Support\ContentHtml::clean($request->description);
        $category->status = $request->has('status') ? 1 : 0;
        $category->save();
        \App\Support\ContentHtml::flushPublicPageCache();

        // Save Filters
        if ($request->has('filters') && is_array($request->filters)) {
            foreach ($request->filters as $filter) {
                if (!empty(trim($filter))) {
                    CategoryFilter::create([
                        'category_id' => $category->id,
                        'filter_name' => trim($filter),
                        'status'      => 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.category.index')
            ->withAlert(['success', 'Category created successfully']);

    } catch (Exception $e) {
        return back()->withInput()
            ->withAlert(['danger', $e->getMessage()]);
    }
}

public function update($id)
{
    $category = Category::with('filters')->findOrFail($id);
    return view('admin.category.edit', compact('category'));
}

public function edit(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'icon' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'filters.*' => 'nullable|string|max:255',
        'seo_title' => 'nullable|string|max:255',
        'seo_keywords' => 'nullable|string|max:255',
        'seo_description' => 'nullable|string',
        'description' => 'nullable|string',
    ]);

    $category = Category::findOrFail($id);

    $image = $category->image;
    $desktopBanner = $category->desktop_banner;
    $mobileBanner = $category->mobile_banner;

    if ($request->hasFile('image')) {
        try {
            $imageData = [
                'file' => $request->image,
                'path' => 'assets/images/category/',
                'prevFile' => $category->image,
            ];
            $image = $this->uploadFile('image', $imageData);
        } catch (\Exception $e) {
            $alert = ['danger', 'Something wrong in category image upload'];
            return back()->withAlert($alert);
        }
    }

    if ($request->hasFile('desktop_banner')) {
        try {
            $desktopBannerData = [
                'file' => $request->desktop_banner,
                'path' => 'assets/images/category/banner/',
                'size' => '1920x600',
                'prevFile' => $category->desktop_banner,
            ];
            $desktopBanner = $this->uploadFile('image', $desktopBannerData);
        } catch (\Exception $e) {
            $alert = ['danger', 'Something wrong in desktop banner upload'];
            return back()->withAlert($alert);
        }
    }

    if ($request->hasFile('mobile_banner')) {
        try {
            $mobileBannerData = [
                'file' => $request->mobile_banner,
                'path' => 'assets/images/category/banner/',
                'size' => '768x500',
                'prevFile' => $category->mobile_banner,
            ];
            $mobileBanner = $this->uploadFile('image', $mobileBannerData);
        } catch (\Exception $e) {
            $alert = ['danger', 'Something wrong in mobile banner upload'];
            return back()->withAlert($alert);
        }
    }

    $category->name = $request->name;
    $category->slug = \Str::slug($request->name);
    $category->icon = $request->icon; // ye line missing thi
    $category->status = $request->status ? 1 : 0;
    $category->image = $image;
    $category->desktop_banner = $desktopBanner;
    $category->mobile_banner = $mobileBanner;
    $category->seo_title = $request->seo_title;
    $category->seo_keywords = $request->seo_keywords;
    $category->seo_description = $request->seo_description;
    // Same normalising as create — see ContentHtml.
    $category->description = \App\Support\ContentHtml::clean($request->description);
    $category->save();
    \App\Support\ContentHtml::flushPublicPageCache();

    CategoryFilter::where('category_id', $category->id)->delete();

    if ($request->filled('filters')) {
        foreach ($request->filters as $filter) {
            if (!empty($filter)) {
                CategoryFilter::create([
                    'category_id' => $category->id,
                    'filter_name' => $filter,
                    'status' => 1,
                ]);
            }
        }
    }

   $alert = ['success', 'Category updated successfully'];
return redirect()->route('admin.category.index')->withAlert($alert);
}

 public function delete($id)
{
    try {
        $Category = Category::findOrFail($id);

        // old image delete
        if (!empty($Category->image)) {
            $imagePath = public_path('assets/images/category' . $Category->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $Category->delete();

        $alert = ['success', 'Category deleted successfully'];
        return redirect()->route('admin.category.index')->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }
}

public function searchIcons(Request $request)
{
    $query = trim($request->get('q', ''));

    if ($query === '') {
        return response()->json([
            'status' => false,
            'message' => 'Search keyword required',
            'items' => [],
        ], 422);
    }

    try {
        $attempt = HttpRetry::send(function () use ($query) {
            return Http::timeout(20)
                ->connectTimeout(10)
                ->withHeaders([
                    'Client-ID' => trim((string) config('services.iconscout.client_id')),
                    'Accept'    => 'application/json',
                ])
                ->get('https://api.iconscout.com/v3/search', [
                    'asset'    => 'icon',
                    'query'    => $query,
                    'per_page' => 12,
                ]);
        }, 3, 800, 'iconscout.search', 20);

        $response = $attempt['response'];

        if ($response === null) {
            return response()->json([
                'status'  => false,
                'message' => 'Icon search is unavailable right now. Please try again.',
                'items'   => [],
            ], 502);
        }

        if (!$response->successful()) {
            // IconScout's status code stays out of our response: an icon picker
            // failing must not look like the admin panel itself being down.
            return response()->json([
                'status'  => false,
                'message' => HttpRetry::retryable($response)
                    ? 'Icon search is busy right now. Please try again.'
                    : 'Icon search failed',
                'items'   => [],
                'raw'     => $response->body(),
            ], HttpRetry::clientStatus($response->status()));
        }

        $json = $response->json();

        $rawItems = $json['response']['items']['data']
            ?? $json['response']['items']
            ?? $json['items']['data']
            ?? $json['items']
            ?? $json['data']
            ?? [];

        $items = collect($rawItems)->map(function ($item) {
            $uuid = $item['uuid']
                ?? $item['item_uuid']
                ?? null;

            $name = $item['name']
                ?? $item['title']
                ?? 'Icon';

            $preview = $item['urls']['png_64']
                ?? $item['urls']['png_128']
                ?? $item['urls']['png_256']
                ?? $item['preview_url']
                ?? $item['thumbnail_url']
                ?? null;

            return [
                'uuid'    => $uuid,
                'name'    => $name,
                'preview' => $preview,
            ];
        })->filter(function ($item) {
            return !empty($item['uuid']) && !empty($item['preview']);
        })->values();

        return response()->json([
            'status' => true,
            'items'  => $items,
        ]);
    } catch (\Throwable $e) {
        report($e);
        return response()->json([
            'status'  => false,
            'message' => 'Icon search failed',
            'items'   => [],
        ], 500);
    }
}

/**
 * Hostnames the icon preview_url is allowed to resolve to. Restricting to
 * IconScout's own CDN prevents this endpoint being used as an SSRF proxy
 * (e.g. to reach internal hosts or cloud metadata endpoints).
 */
private const ALLOWED_ICON_HOSTS = [
    'api.iconscout.com',
    'cdn.iconscout.com',
    'cdnl.iconscout.com',
    'cdni.iconscout.com',
    'static.iconscout.com',
];

public function downloadIcon(Request $request)
{
    $request->validate([
        'preview_url' => 'required|url',
    ]);

    try {
        $url = $request->preview_url;
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host || !in_array(strtolower($host), self::ALLOWED_ICON_HOSTS, true)) {
            return response()->json([
                'status' => false,
                'message' => 'Icon source host is not allowed',
            ], 422);
        }

        $response = Http::timeout(20)->get($url);

        if (!$response->successful()) {
            return response()->json([
                'status' => false,
                'message' => 'Image download failed'
            ]);
        }

        $contentType = (string) $response->header('Content-Type');
        if (!str_starts_with($contentType, 'image/')) {
            return response()->json([
                'status' => false,
                'message' => 'Downloaded content is not an image',
            ], 422);
        }

        $body = $response->body();
        if (strlen($body) > 5 * 1024 * 1024) {
            return response()->json([
                'status' => false,
                'message' => 'Downloaded image exceeds the size limit',
            ], 422);
        }

        $directory = public_path('assets/images/category/icons');

        if (!\File::exists($directory)) {
            \File::makeDirectory($directory, 0755, true);
        }

        $fileName = 'icon-' . \Str::random(10) . '.png';
        \File::put($directory . '/' . $fileName, $body);

        return response()->json([
            'status' => true,
            'db_value' => $fileName,
            'url' => asset('assets/images/category/icons/' . $fileName)
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Unable to download icon',
        ]);
    }
}
}
