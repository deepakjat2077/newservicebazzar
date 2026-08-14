<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    public function listings(){
    	return $this->hasMany(Listing::class)->where('status',1);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($location) {
            $location->slug = static::generateSlug($location->name);
        });

        static::updating(function ($location) {
            if ($location->isDirty('name')) {
                $oldSlug = $location->getOriginal('slug');
                $location->slug = static::generateSlug($location->name, $location->id);

                if ($oldSlug && $oldSlug !== $location->slug) {
                    \App\Models\UrlRedirect::remember('/' . $oldSlug, '/' . $location->slug);
                }
            }
        });

        // activeLocations() caches this table for the footer on every page,
        // and validRouteFirstSegments() caches its slugs to reject unknown
        // city URLs without a query — a new city must open immediately, not
        // 404 until the cache expires.
        $forgetCache = function () {
            \Illuminate\Support\Facades\Cache::forget('active_locations');
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
}
