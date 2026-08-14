<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::with('category')->latest()->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();

        return view('admin.banners.index', compact('banners', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'nullable|string|max:255',
            'category_id'     => 'nullable|exists:categories,id',
            'city'            => 'nullable|string|max:255',
            'desktop_banner'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mobile_banner'   => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link'            => 'nullable|url',
            'sort_order'      => 'nullable|integer',
        ]);

        $desktopBanner = null;
        $mobileBanner = null;

        if ($request->hasFile('desktop_banner')) {
            $desktopBanner = $this->uploadResizedBanner(
                $request->file('desktop_banner'),
                1366,
                113
            );
        }

        if ($request->hasFile('mobile_banner')) {
            $mobileBanner = $this->uploadResizedBanner(
                $request->file('mobile_banner'),
                670,
                176
            );
        }

        Banner::create([
            'title'          => $request->title,
            'category_id'    => $request->category_id,
            'city'           => $request->city,
            'desktop_banner' => $desktopBanner,
            'mobile_banner'  => $mobileBanner,
            'link'           => $request->link,
            'sort_order'     => $request->sort_order ?? 0,
            'status'         => 1,
        ]);

        return back()->with('success', 'Banner added successfully');
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        $categories = Category::where('status', 1)->orderBy('name')->get();

        return view('admin.banners.edit', compact('banner', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title'           => 'nullable|string|max:255',
            'category_id'     => 'nullable|exists:categories,id',
            'city'            => 'nullable|string|max:255',
            'desktop_banner'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mobile_banner'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link'            => 'nullable|url',
            'sort_order'      => 'nullable|integer',
        ]);

        $desktopBanner = $banner->desktop_banner;
        $mobileBanner = $banner->mobile_banner;

        if ($request->hasFile('desktop_banner')) {
            $this->deleteBannerFile($desktopBanner);

            $desktopBanner = $this->uploadResizedBanner(
                $request->file('desktop_banner'),
                1366,
                113
            );
        }

        if ($request->hasFile('mobile_banner')) {
            $this->deleteBannerFile($mobileBanner);

            $mobileBanner = $this->uploadResizedBanner(
                $request->file('mobile_banner'),
                670,
                176
            );
        }

        $banner->update([
            'title'          => $request->title,
            'category_id'    => $request->category_id,
            'city'           => $request->city,
            'desktop_banner' => $desktopBanner,
            'mobile_banner'  => $mobileBanner,
            'link'           => $request->link,
            'sort_order'     => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully');
    }

  

     public function delete($id)
{
    try {
        $banner = Banner::findOrFail($id);

        // old image delete
        if (!empty($banner->image)) {
            $imagePath = public_path('assets/images/category/banner' . $banner->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $banner->delete();

        $alert = ['success', 'Banner deleted successfully'];
        return redirect()->route('admin.banners.index')->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }
}
    public function toggle($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = !$banner->status;
        $banner->save();

        return back()->with('success', 'Banner status updated');
    }

    private function uploadResizedBanner($file, $width, $height)
    {
        $path = public_path('assets/images/category/banner');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $filename = Str::random(20) . time() . '.' . $file->getClientOriginalExtension();
$manager = new ImageManager(new Driver());
      $image = $manager->read($file->getRealPath());

        // Exact size force resize
      $image->resize($width, $height)->save($path . '/' . $filename, 90);

        return $filename;
    }

    private function deleteBannerFile($filename)
    {
        if ($filename && file_exists(public_path('assets/images/category/banner/' . $filename))) {
            unlink(public_path('assets/images/category/banner/' . $filename));
        }
    }
}