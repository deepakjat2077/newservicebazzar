<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** A listing the admin put on the home page's "Featured Listings" by hand. */
class HomeFeaturedListing extends Model
{
    protected $fillable = ['listing_id', 'sort_order', 'is_active', 'starts_at', 'ends_at'];

    protected $casts = ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public const CACHE_KEY = 'home:featured_listings:v1';

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /** On, and inside its dates (when it has any). */
    public function scopeLive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    /** The home page shows the change straight away, not after its caches run out. */
    public static function refreshHome(): void
    {
        Cache::forget(self::CACHE_KEY);

        // The guest full-page cache keys pages by host + path (see CachePublicPages).
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'www.servicebazzar.com';
        Cache::store(config('cache.public_pages.store', 'file'))->forget('page:' . sha1($host . '|/'));
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::refreshHome());
        static::deleted(fn () => self::refreshHome());
    }
}
