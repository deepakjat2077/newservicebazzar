<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeasonalSection;
use App\Models\Category;

class SeasonalSectionController extends Controller
{
    public function index()
    {
        $sections = SeasonalSection::latest()->get();
        return view('admin.seasonal.index', compact('sections'));
    }

    public function create()
    {
        $categories = Category::where('status', 1)->get();
        return view('admin.seasonal.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        SeasonalSection::create([
            'title'        => $request->title,
            'subtitle'     => $request->subtitle,
            'category_ids' => json_encode($request->category_ids),
            'status'       => 1,
        ]);

        return redirect()->route('admin.seasonal.sections.index')
            ->with('success', 'Seasonal Section Added Successfully');
    }

   public function edit($id)
{
    $item = SeasonalSection::findOrFail($id);

    $categories = Category::where('status', 1)->get();

    return view('admin.seasonal.edit', compact('item', 'categories'));
}

   public function update(Request $request, $id)
{
    $section = SeasonalSection::findOrFail($id);

    $section->update([
        'title'        => $request->title,
        'badge_text'   => $request->badge_text,
        'subtitle'     => $request->subtitle,
        'category_ids' => json_encode($request->category_ids),
        'status'       => $request->has('status') ? 1 : 0,
    ]);

    return redirect()->route('admin.seasonal.sections.index')
        ->with('success', 'Updated Successfully');
}

    public function delete($id)
    {
        $section = SeasonalSection::findOrFail($id);
        $section->delete();

        return back()->with('success', 'Deleted Successfully');
    }
}