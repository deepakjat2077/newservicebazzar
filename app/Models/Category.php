<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    public function listings(){
    	return $this->hasMany(Listing::class)->where('status',1);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = static::generateSlug($category->name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = static::generateSlug($category->name, $category->id);
            }
        });

        // validRouteFirstSegments() caches these slugs to reject unknown URLs
        // without a query, so a new or renamed category must invalidate it or
        // its pages would 404 until the cache expired.
        $forgetCache = function () {
            \Illuminate\Support\Facades\Cache::forget('route_first_segments');
        };

        static::saved($forgetCache);
        static::deleted($forgetCache);
    }

    protected static function generateSlug($name, $id = null)
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;
        while (static::where('slug', $slug)->when($id, function ($query) use ($id) {
            $query->where('id', '!=', $id);
        })->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
public function subcategories()
{
    return $this->hasMany(Subcategory::class);
}
    public function filters()
{
    return $this->hasMany(CategoryFilter::class, 'category_id');
}

public function serviceKeywords()
{
    return $this->hasMany(ServiceKeyword::class, 'category_id');
}

}
