<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeContentSection;
use Illuminate\Http\Request;

class HomeContentSectionController extends Controller
{
    public function index()
    {
        $section = HomeContentSection::first();

        return view('admin.home_content_sections.index', compact('section'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required',
            'status'  => 'nullable|in:0,1',
        ]);

        // agar pehle se record hai to dubara add mat karo
        if (HomeContentSection::count() > 0) {
            return back()->with('error', 'Section already exists. You can only update it.');
        }

        HomeContentSection::create([
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => $request->status ?? 1,
        ]);

        return back()->with('success', 'Section added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required',
            'status'  => 'nullable|in:0,1',
        ]);

        $section = HomeContentSection::findOrFail($id);

        $section->update([
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => $request->status ?? 1,
        ]);

        return back()->with('success', 'Section updated successfully.');
    }
}