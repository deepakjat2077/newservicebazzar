<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Subcategory extends Model
{
    /** The id => {id,name,slug} map Listing::subcategoryLookup() reads. */
    public const LOOKUP_CACHE_KEY = 'subcategories.lookup';

    /** Any write invalidates the map the listing cards render from. */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::LOOKUP_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::LOOKUP_CACHE_KEY));
    }

    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'description',
        'status'
    ];

    // Category relation
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}