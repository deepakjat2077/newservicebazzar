<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SocialSite extends Model
{
    /** Cache entry AppServiceProvider reads on every request. */
    public const CACHE_KEY = 'site.social_sites';

    protected $fillable = [
        'link',
        'image',
    ];

    /** Same reasoning as Setting::booted() — every writer uses Eloquent. */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}