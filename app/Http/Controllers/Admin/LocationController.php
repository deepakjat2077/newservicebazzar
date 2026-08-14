<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    use FileHandler;

    public function index(){
    	$locations = Location::paginate(20);
    	return view('admin.location.index',compact('locations'));
    }

    public function create(Request $request)
{
    $request->validate([
        'name' => 'required',
        'image' => 'required|image|mimes:jpg,jpeg,png',
    ]);

    if ($request->hasFile('image')) {
        try {
            $imageData = [
                'file' => $request->image,
                'path' => 'assets/images/location/',
                'size' => '350x240',
                'prevFile' => null,
            ];
            $image = $this->uploadFile('image', $imageData);
        } catch (Exception $e) {
            $alert = ['danger', 'Something wrong'];
            return back()->withAlert($alert);
        }
    }

    $location = new Location();
    $location->name = $request->name;
    $location->status = $request->status ? 1 : 0;
    $location->is_popular = $request->has('is_popular') ? 1 : 0;
    $location->image = $image;

    $location->latitude = $request->latitude;
    $location->longitude = $request->longitude;

    $location->save();

    $alert = ['success', 'Location created successfully'];
    return back()->withAlert($alert);
}

   public function edit(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'image' => 'nullable|image|mimes:jpg,jpeg,png',
    ]);

    $location = Location::findOrFail($id);

    $image = $location->image;

    if ($request->hasFile('image')) {
        try {
            $imageData = [
                'file' => $request->image,
                'path' => 'assets/images/location/',
                'size' => '350x190',
                'prevFile' => $location->image,
            ];
            $image = $this->uploadFile('image', $imageData);
        } catch (Exception $e) {
            $alert = ['danger', 'Something wrong'];
            return back()->withAlert($alert);
        }
    }

    $location->name = $request->name;
    $location->status = $request->status ? 1 : 0;
    $location->is_popular = $request->has('is_popular') ? 1 : 0;
    $location->image = $image;

    $location->latitude = $request->latitude;
    $location->longitude = $request->longitude;

    $location->save();

    $alert = ['success', 'Location updated successfully'];
    return back()->withAlert($alert);
}

	public function delete($id)
{
    if (auth()->guard('admin')->user()->role !== 'super_admin') {
        abort(403, 'Unauthorized');
    }

    try {
        $location = Location::findOrFail($id);

        // old image delete
        if (!empty($location->image)) {
            $imagePath = public_path('assets/images/location' . $location->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $location->delete();

        $alert = ['success', 'Location deleted successfully'];
        return redirect()->route('admin.location.index')->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }
}
}
