<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * One address, or one range, whose traffic is kept out of the analytics.
 *
 * Read on every beacon, so the list is cached; both app servers share the
 * cache, and saving or deleting clears it, so a change takes effect on both
 * at once.
 */
class ExcludedIp extends Model
{
    public const CACHE_KEY = 'analytics:excluded-ips';

    protected $table = 'analytics_excluded_ips';

    protected $fillable = ['value', 'label', 'added_by'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return string[] */
    public static function values(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            return static::orderBy('id')->pluck('value')->all();
        });
    }
}
