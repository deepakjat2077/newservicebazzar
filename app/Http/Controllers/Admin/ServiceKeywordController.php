<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ServiceKeyword;
use App\Support\ContentHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceKeywordController extends Controller
{
    public function index()
    {
        $keywords = ServiceKeyword::with('category')->latest()->paginate(20);
        return view('admin.service_keywords.index', compact('keywords'));
    }

public function create()
{
    $categories = Category::where('status', 1)
        ->orderBy('name', 'asc')
        ->get(['id', 'name']);

    return view('admin.service_keywords.create', compact('categories'));
}
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'keyword' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $image = null;

        if ($request->hasFile('image')) {
            $image = time().'_'.uniqid().'.'.$request->image->extension();
            $request->image->move(public_path('assets/images/service-keywords'), $image);
        }

        ServiceKeyword::create([
            'category_id' => $request->category_id,
            'keyword' => $request->keyword,
            'slug' => Str::slug($request->keyword),
            'image' => $image,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'description' => ContentHtml::clean($request->description),
            'status' => $request->status ?? 1,
        ]);
        ContentHtml::flushPublicPageCache();

        return redirect()->route('admin.service-keywords.index')->with('success', 'Service keyword added successfully');
    }

    public function edit($id)
    {
        $keyword = ServiceKeyword::findOrFail($id);
        $categories = Category::where('status', 1)->get();

        return view('admin.service_keywords.edit', compact('keyword', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $keyword = ServiceKeyword::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'keyword' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $image = $keyword->image;

        if ($request->hasFile('image')) {
            if ($keyword->image && file_exists(public_path('assets/images/service-keywords/'.$keyword->image))) {
                unlink(public_path('assets/images/service-keywords/'.$keyword->image));
            }

            $image = time().'_'.uniqid().'.'.$request->image->extension();
            $request->image->move(public_path('assets/images/service-keywords'), $image);
        }

        $keyword->update([
            'category_id' => $request->category_id,
            'keyword' => $request->keyword,
            'slug' => Str::slug($request->keyword),
            'image' => $image,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'description' => ContentHtml::clean($request->description),
            'status' => $request->status ?? 1,
        ]);
        ContentHtml::flushPublicPageCache();

        return redirect()->route('admin.service-keywords.index')->with('success', 'Service keyword updated successfully');
    }

    public function delete($id)
    {
        $keyword = ServiceKeyword::findOrFail($id);

        if ($keyword->image && file_exists(public_path('assets/images/service-keywords/'.$keyword->image))) {
            unlink(public_path('assets/images/service-keywords/'.$keyword->image));
        }

        $keyword->delete();

        return back()->with('success', 'Service keyword deleted successfully');
    }

    public function status($id)
    {
        $keyword = ServiceKeyword::findOrFail($id);
        $keyword->status = $keyword->status == 1 ? 0 : 1;
        $keyword->save();

        return back()->with('success', 'Status updated successfully');
    }
}
