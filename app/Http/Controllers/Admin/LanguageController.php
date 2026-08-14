<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
	use FileHandler;

	public function index(){
    	$languages = Language::get();
    	return view('admin.language.index',compact('languages'));
	}

	public function create(Request $request){
		$request->validate([
			'name'=>'required',
			// Strictly whitelisted — this value is used to build a filesystem
			// path (resources/lang/{code}.json); anything beyond a short
			// locale-style code (e.g. "en", "en-US") could otherwise be used
			// for path traversal.
			'code'=>'required|string|regex:/^[a-zA-Z]{2,5}(-[a-zA-Z]{2,5})?$/|unique:languages,lang_code',
			'image'=>'required|image|mimes:jpg,jpeg,png',
		]);

		$data = '{}';
        $json_file = basename(strtolower($request->code)) . '.json';
        $path = resource_path('lang/') . $json_file;
        File::put($path, $data);

        if ($request->default) {
            Language::where('is_default', 1)->update([
                'is_default' => 0
            ]);
        }

        if ($request->hasFile('image')) {
    		try {
	            $imageData = [
	                'file'=>$request->image,
	                'path'=>'assets/images/language/',
	                'size'=>'120x120',
	                'prevFile'=>null,
	            ];
	            $image = $this->uploadFile('image',$imageData);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
        }

        Language::create([
            'icon' => $image,
            'name' => $request->name,
            'lang_code' => strtolower($request->code),
            'is_default' => $request->default ? 1 : 0,
        ]);

		$alert = ['success','Language created successfully'];
    	return back()->withAlert($alert);
	}

	public function edit(Request $request,$id){
		$request->validate([
			'name'=>'required',
			'image'=>'nullable|image|mimes:jpg,jpeg,png'
		]);

		$language = Language::find($id);
        if (!$language) {
        	$alert = ['danger','Language not found'];
    		return back()->withAlert($alert);
        }
        if ($request->default) {
            Language::where('is_default', 1)->update([
                'is_default' => 0
            ]);
        }

        $image = $language->icon;
        if ($request->hasFile('image')) {
    		try {
	            $imageData = [
	                'file'=>$request->image,
	                'path'=>'assets/images/language/',
	                'size'=>'120x120',
	                'prevFile'=>$language->icon,
	            ];
	            $image = $this->uploadFile('image',$imageData);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
        }

        $language->name = $request->name;
        $language->is_default = $request->default ? 1 : 0;
        $language->icon = $image;
        $language->save();

        $alert = ['success','Language udpated successfully'];
    	return back()->withAlert($alert);

	}

	public function remove(Request $request,$id){
		$language = Language::findOrFail($id);
		$location = 'assets/images/language/'.$language->icon;
		$this->deleteFile($location);
		$language->delete();
		$alert = ['success','Language removed successfully'];
		return back()->withAlert($alert);
	}

	public function translate($id){
		$language = Language::findOrFail($id);
		$trans = file_get_contents(resource_path('lang/') . basename($language->lang_code) . '.json');
		$trans = json_decode($trans);
    	return view('admin.language.translate',compact('language','trans'));
	}

	public function translateUpdate(Request $request, $id){
		$request->validate([
    		'key.*'=>'required',
    		'value.*'=>'required',
    	],[
    		'key.*.required'=>'All default language field is required',
    		'value.*.required'=>'All translate field is required',
    	]);
    	$language = Language::findOrFail($id);
    	$data = [];
    	foreach ($request->key as $key => $def) {
    		$data[$def] = $request->value[$key];
    	}
    	$data = json_encode($data);
    	$file = resource_path('lang/') . basename($language->lang_code) . '.json';
    	file_put_contents($file, $data);
    	$alert = ['success','Translation updated Successfully'];
    	return back()->withAlert($alert);
	}
}
