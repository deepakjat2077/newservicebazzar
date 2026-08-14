<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\hbb;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Facades\Image;

class hbbController extends Controller
{
     public function index()
    {
        $banners = hbb::latest()->get();
        return view('admin.home_banners_bottom.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'nullable|string|max:255',
            'desktop_banner'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mobile_banner'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

        hbb::create([
            'title'          => $request->title,
            'desktop_banner' => $desktopBanner,
            'mobile_banner'  => $mobileBanner,
            'link'           => $request->link,
            'sort_order'     => $request->sort_order ?? 0,
            'status'         => 1,
        ]);

        return redirect()->route('admin.hbb.index')->with('success', 'Banner added successfully');
    }

    public function edit($id)
    {
        $banner = hbb::findOrFail($id);
    
        return view('admin.home_banners_bottom.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = hbb::findOrFail($id);

        $request->validate([
            'title'           => 'nullable|string|max:255',
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
            'desktop_banner' => $desktopBanner,
            'mobile_banner'  => $mobileBanner,
            'link'           => $request->link,
            'sort_order'     => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.hbb.index')->with('success', 'Banner updated successfully');
    }

  

    public function toggle($id)
    {
        $banner = hbb::findOrFail($id);
        $banner->status = !$banner->status;
        $banner->save();

        return back()->with('success', 'Banner status updated');
    }

   private function uploadResizedBanner($file, $width, $height)
{
    $path = public_path('assets/images/category/hbb');

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $filename = Str::random(20) . time() . '.' . $file->getClientOriginalExtension();

    $manager = new ImageManager(new Driver());
    $image = $manager->read($file->getRealPath());

    $image->resize($width, $height);
    $image->save($path . '/' . $filename);

    return $filename;
}


     public function delete($id)
{
    try {
        $banner = hbb::findOrFail($id);

        // old image delete
        if (!empty($banner->image)) {
            $imagePath = public_path('assets/images/category/hbb/' . $banner->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $banner->delete();

        $alert = ['success', 'Banner deleted successfully'];
        return redirect()->route('admin.hbb.index')->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }
}

    private function deleteBannerFile($filename)
    {
        if ($filename && file_exists(public_path('assets/images/category/hbb/' . $filename))) {
            unlink(public_path('assets/images/category/hbb/' . $filename));
        }
    }
}
