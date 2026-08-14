<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Template;
use App\Models\Setting;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    use Template;

	protected $template;

	public function __construct(){
        $setting = Setting::first();
        $theme = $setting->theme;
		$this->template = $this->$theme();
	}

    public function item($key){
    	$tempArr = $this->template;
    	if(!array_key_exists($key, $tempArr)){
    		abort(404);
    	}
    	$item = $tempArr[$key];
    	$hasImage = searchForKey(true,'is_image', $item['single_item']);
    	$title = $item['name'];
        $element = $this->getThemeItem($key,false);
        $themeDatas = null;
        if (array_key_exists('multiple_item', $item)) {
            $themeDatas = $this->getThemeItem($key,true);
        }
        $elementName = $key;
    	return view('admin.theme.item',compact('item','title','hasImage','key','element','elementName','themeDatas'));
    }

    public function create($key){
        $tempArr = $this->template;
        if(!array_key_exists($key, $tempArr)){
            abort(404);
        }
        $item = $tempArr[$key];
        $hasImage = searchForKey(true,'is_image', $item['multiple_item']);
        $title = $item['name'];
        $elementName = $key;
        return view('admin.theme.create',compact('item','title','hasImage','elementName'));
    }

    public function edit($key,$id){
        $tempArr = $this->template;
        if(!array_key_exists($key, $tempArr)){
            abort(404);
        }
        $element = Theme::findOrFail($id);
        $item = $tempArr[$key];
        $hasImage = searchForKey(true,'is_image', $item['multiple_item']);
        $title = $item['name'];
        $elementName = $key;
        return view('admin.theme.edit',compact('item','title','hasImage','elementName','element'));
    }
}
