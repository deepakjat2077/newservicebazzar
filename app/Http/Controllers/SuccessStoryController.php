<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Admin CRUD for the client "success stories" (testimonials) shown on the Get
 * Listed screen. Mirrors BannerController: image upload + sort_order + status,
 * add on the index page, edit on its own page, soft actions for toggle/delete.
 * The app reads them through Api\ContentController@getListed.
 */
class SuccessStoryController extends Controller
{
    public function index()
    {
        $stories = SuccessStory::orderBy('sort_order')->orderByDesc('id')->get();

        return view('admin.success_stories.index', compact('stories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name'   => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'rating'        => 'nullable|numeric|min:0|max:5',
            'story'         => 'required|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sort_order'    => 'nullable|integer',
        ]);

        SuccessStory::create([
            'client_name'   => $request->client_name,
            'business_name' => $request->business_name,
            'rating'        => $request->rating,
            'story'         => $request->story,
            'image'         => $request->hasFile('image') ? $this->uploadImage($request->file('image')) : null,
            'sort_order'    => $request->sort_order ?? 0,
            'status'        => 1,
        ]);

        return back()->with('success', 'Success story added successfully');
    }

    public function edit($id)
    {
        $story = SuccessStory::findOrFail($id);

        return view('admin.success_stories.edit', compact('story'));
    }

    public function update(Request $request, $id)
    {
        $story = SuccessStory::findOrFail($id);

        $request->validate([
            'client_name'   => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'rating'        => 'nullable|numeric|min:0|max:5',
            'story'         => 'required|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sort_order'    => 'nullable|integer',
        ]);

        $image = $story->image;
        if ($request->hasFile('image')) {
            $this->deleteImage($image);
            $image = $this->uploadImage($request->file('image'));
        }

        $story->update([
            'client_name'   => $request->client_name,
            'business_name' => $request->business_name,
            'rating'        => $request->rating,
            'story'         => $request->story,
            'image'         => $image,
            'sort_order'    => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.success-stories.index')->with('success', 'Success story updated successfully');
    }

    public function toggle($id)
    {
        $story = SuccessStory::findOrFail($id);
        $story->status = ! $story->status;
        $story->save();

        return back()->with('success', 'Success story status updated');
    }

    public function delete($id)
    {
        $story = SuccessStory::findOrFail($id);
        $this->deleteImage($story->image);
        $story->delete();

        return redirect()->route('admin.success-stories.index')->with('success', 'Success story deleted successfully');
    }

    private function uploadImage($file)
    {
        $path = public_path('assets/images/success-stories');
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $filename = Str::random(20) . time() . '.' . $file->getClientOriginalExtension();
        $manager = new ImageManager(new Driver());
        $manager->read($file->getRealPath())->cover(200, 200)->save($path . '/' . $filename, 90);

        return $filename;
    }

    private function deleteImage($filename)
    {
        if ($filename && file_exists(public_path('assets/images/success-stories/' . $filename))) {
            @unlink(public_path('assets/images/success-stories/' . $filename));
        }
    }
}
