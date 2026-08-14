<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $casts = ['content'=>'object'];

    protected $fillable = [
        'name',
        'theme_name',
        'is_multiple',
        'content'
    ];

    /*
     * themeRows()/SiteController::themeCollection() cache these rows for every
     * page render, so an admin edit has to drop the cache or it would sit stale
     * for the rest of the TTL.
     */
    protected static function booted()
    {
        $forget = function (self $theme) {
            \Illuminate\Support\Facades\Cache::forget("theme_data:{$theme->theme_name}");
        };

        static::saved($forget);
        static::deleted($forget);
    }
}
