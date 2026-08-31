<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(){
    	$packages = Package::get();
    	return view('admin.package.index',compact('packages'));
    }

    public function store(Request $request){
    	$this->validation($request);
    	$package = new Package();
    	$this->packageData($package,$request);
    	$alert = ['success','Package created successfully'];
    	return back()->withAlert($alert);
    }

    public function update(Request $request,$id){
    	$this->validation($request);
    	$package = Package::findOrfail($id);
    	$this->packageData($package,$request);
    	$alert = ['success','Package updated successfully'];
    	return back()->withAlert($alert);
    }

    public function status($id){
    	$package = Package::findOrfail($id);
    	if ($package->status == 1) {
    		$package->status = 0;
    	}else{
    		$package->status = 1;
    	}
    	$package->save();
    	$alert = ['success','Package status updated successfully'];
    	return back()->withAlert($alert);
    }

    protected function packageData($package,$request){
    	$package->name = $request->name;
    	$package->price = $request->price;
    	$package->listing_allow = $request->listing_allow;
    	$package->features_allow = $request->features_allow;
    	$package->featured_allow = $request->featured_allow;
    	$package->image_allow = $request->image_allow;
    	$package->validity = $request->validity;
        $package->contact_allow = $request->contact_allow;
        // Marketing fields for the public "Advertise" plan card. `features` is a
        // free-text list (one feature per line) that renders as the green-tick
        // "Features Included" checklist.
        $package->features = $request->features;
        $package->old_price = $request->old_price ?: null;
        $package->tag = $request->tag;
        $package->is_popular = $request->boolean('is_popular');
    	$package->save();
    }

    protected function validation($request){
    $request->validate([
        'name'=>'required',
        'price'=>'required|numeric|gte:0',
        'listing_allow'=>'required|integer|gte:0',
        'features_allow'=>'required|integer|gte:0',
        'featured_allow'=>'required|integer|gte:0',
        'image_allow'=>'required|integer|gte:0',
        'validity'=>'required|integer|gte:0',
        'contact_allow'=>'required|integer|gte:0',
        'features'=>'nullable|string',
        'old_price'=>'nullable|numeric|gte:0',
        'tag'=>'nullable|string|max:40',
    ]);
}


public function activateFree($id)
{
    $package = Package::findOrFail($id);

    // Security check
    if($package->price > 0){
        return back()->with('error','Invalid Package');
    }

    $user = auth()->user();

    // Assign package
    $user->package_id = $package->id;

    // Expiry set (agar validity column hai)
    if($package->validity){
        $user->package_exp = now()->addDays($package->validity);
    }

    $user->save();

    return redirect()->route('user.dashboard')
        ->with('success','Free Package Activated Successfully');
}


  public function delete($id)
{
    try {
        $package = Package::findOrFail($id);

        // image delete
        if (!empty($package->image)) {
            $path = public_path('assets/images/package/' . $package->image);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $package->delete();

        $alert = ['success', 'Package deleted successfully'];
        return back()->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', 'Delete failed'];
        return back()->withAlert($alert);
    }
}
}
