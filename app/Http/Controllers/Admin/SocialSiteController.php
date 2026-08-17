<?php

namespace App\Http\Controllers\Admin;

use App\Support\Images\WebpImage;

use App\Http\Controllers\Controller;
use App\Models\SocialSite;
use Illuminate\Http\Request;

class SocialSiteController extends Controller
{
       public function index()
    {
        $socialSites = SocialSite::latest()->get();

        return view('admin.social-sites.index', compact('socialSites'));
    }

    public function store(Request $request)
{
    $request->validate([
        'link'  => ['required', 'url', 'regex:/^https?:\/\//i'],
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
    ]);

    $imagePath = null;

    if ($request->hasFile('image')) {
        $imageName = WebpImage::store(
            $request->file('image'),
            'uploads/social-sites',
            (string) time()
        );

        $imagePath = 'uploads/social-sites/' . $imageName;
    }

    SocialSite::create([
        'link'  => $request->link,
        'image' => $imagePath,
    ]);

    return back()->with('success', 'Social site added successfully.');
}

public function update(Request $request, $id)
{
    $socialSite = SocialSite::findOrFail($id);

    $request->validate([
        'link'  => ['required', 'url', 'regex:/^https?:\/\//i'],
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
    ]);

    $imagePath = $socialSite->image;

    if ($request->hasFile('image')) {

        if ($socialSite->image && file_exists(public_path($socialSite->image))) {
            unlink(public_path($socialSite->image));
        }

        $imageName = WebpImage::store(
            $request->file('image'),
            'uploads/social-sites',
            (string) time()
        );

        $imagePath = 'uploads/social-sites/' . $imageName;
    }

    $socialSite->update([
        'link'  => $request->link,
        'image' => $imagePath,
    ]);

    return back()->with('success', 'Social site updated successfully.');
}

public function delete($id)
{
    $socialSite = SocialSite::findOrFail($id);

    if ($socialSite->image && file_exists(public_path($socialSite->image))) {
        unlink(public_path($socialSite->image));
    }

    $socialSite->delete();

    return back()->with('success', 'Social site deleted successfully.');
}

}
