<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Images inserted through the admin rich-text editor (Summernote). Without this
 * the editor embedded each picture as base64 text inside the post. Files go to
 * the blog image folder, which is on the NFS share both app servers mount.
 */
class EditorImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ], [
            'image.max'   => 'Image must be 5 MB or smaller.',
            'image.mimes' => 'Use a JPG, PNG, GIF or WebP image.',
        ]);

        $file = $request->file('image');
        $dir  = 'assets/servicebazzar/images/blog/content';
        $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $ext  = $ext === 'jpeg' ? 'jpg' : $ext;
        $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;

        $file->move(public_path($dir), $name);

        return response()->json(['url' => '/' . $dir . '/' . $name]);
    }
}
