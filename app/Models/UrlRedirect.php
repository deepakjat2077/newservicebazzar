<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlRedirect extends Model
{
    protected $fillable = ['old_path', 'new_path'];

    /**
     * Record (or update) a legacy-URL forward. Called from model `updating`
     * hooks when a slug changes, so an old, possibly-indexed/linked URL 301s
     * to the new one instead of 404ing.
     */
    public static function remember(string $oldPath, string $newPath): void
    {
        // Normalize to match Illuminate\Http\Request::path(), which never
        // has a leading slash — this is what the 404 lookup compares against.
        $oldPath = ltrim($oldPath, '/');
        $newPath = ltrim($newPath, '/');

        if ($oldPath === $newPath || $oldPath === '') {
            return;
        }

        static::updateOrCreate(
            ['old_path' => $oldPath],
            ['new_path' => $newPath]
        );

        // If some earlier rename pointed its old_path at what is now itself
        // being renamed away from, chase that chain forward too so a URL
        // never redirects to another dead redirect.
        static::where('new_path', $oldPath)->update(['new_path' => $newPath]);
    }
}
