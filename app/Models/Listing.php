<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $casts = [
        'features'               => 'array',
        'contact'                => 'array',
        'faq'                    => 'array',

        'mobile_country_code'    => 'array',
        'mobile_numbers'         => 'array',
        'whatsapp_country_code'  => 'array',
        'whatsapp_numbers'       => 'array',
        'landline_country_code'  => 'array',
        'landline_numbers'       => 'array',
        'extra_emails'           => 'array',
        'extra_websites'         => 'array',
        'social_links'           => 'array',

        'business_days'          => 'array',
        'open_time'              => 'array',
        'close_time'             => 'array',

        'business_categories'    => 'array',
        'subcategory_ids'        => 'array',
        'photos'                 => 'array',
    ];

    /**
     * Whether this listing counts as "Open 24 Hours".
     *
     * The public detail page decides this NOT from the open_24_hours flag but
     * by deriving it: a slot that runs 12:00 AM → 11:59 PM is shown as "Open
     * 24 Hours" (see listings/details.blade.php). Listings created through the
     * older main edit form store their 24-hour days exactly that way and leave
     * the flag off, so anything that only reads the flag (the timings editor,
     * the manage page) disagreed with what the public page shows. This mirrors
     * the public rule so every screen agrees.
     */
    public function isOpen24HoursDerived(): bool
    {
        if ($this->open_24_hours) {
            return true;
        }

        $opens = $this->open_time ?? [];
        $closes = $this->close_time ?? [];

        if (empty($opens)) {
            return false;
        }

        foreach ($opens as $i => $open) {
            $close = $closes[$i] ?? '';
            if (strtoupper(trim((string) $open)) !== '12:00 AM'
                || strtoupper(trim((string) $close)) !== '11:59 PM') {
                return false;
            }
        }

        return true;
    }

  public function subcategory()
{
    return $this->belongsTo(Subcategory::class, 'subcategory_id');
}

/**
 * Subcategories are stored as an id array on the listing, so this accessor
 * resolves them.
 *
 * It used to run `Subcategory::whereIn(...)->get()` on every read. Accessors
 * are not cached and the listing card reads this ~7 times per listing, so a
 * page of 2,000 listings fired thousands of queries — and because an accessor
 * shadows the relation of the same name, `->with('subcategory')` could never
 * help.
 *
 * Only the ids on this listing are turned into models; the lookup behind them
 * is the cheap id => {id,name,slug} map below.
 */
public function getSubcategoryAttribute()
{
    $ids = $this->subcategory_ids ?? [];

    if (empty($ids) || !is_array($ids)) {
        return new \Illuminate\Database\Eloquent\Collection();
    }

    $lookup = static::subcategoryLookup();
    $rows = [];

    foreach ($ids as $id) {
        if (isset($lookup[$id])) {
            $rows[] = $lookup[$id];
        }
    }

    // Same Eloquent Collection of Subcategory models the accessor always
    // returned, so `->count()`, `->take(3)` and `->name` keep working.
    return Subcategory::hydrate($rows);
}

/**
 * id => {id, name, slug} for every subcategory, cached.
 *
 * This used to be `Subcategory::all()->keyBy('id')`, written when the comment
 * above it could truthfully say "the whole table is a few hundred rows". It
 * is 5,534 now, and on 2026-08-07 `select * from subcategories` was the single
 * most frequent slow query in the log — 26ms on a quiet minute, 1,421ms on a
 * busy one, on most public pages.
 *
 * Measured on that table: full models 302ms / 9.94 MB, three columns as models
 * 183ms / 7.1 MB, this 26ms / 0.63 MB. Most of the weight was never the data —
 * it was 5,534 Eloquent envelopes, the same thing that made the cached popular
 * -categories payload 13.2 MB before it was reduced to plain arrays.
 *
 * Only id/name/slug are kept because only `$sub->name` is ever read from the
 * accessor (three call sites, all in partials/ajax-listings.blade.php).
 * Subcategory::booted() drops the entry on any write.
 *
 * The static holds it for the request on top of the cache, so a page with
 * 2,000 listings still unserializes once.
 */
protected static $subcategoryLookup;

protected static function subcategoryLookup(): array
{
    if (static::$subcategoryLookup === null) {
        static::$subcategoryLookup = Cache::remember(
            Subcategory::LOOKUP_CACHE_KEY,
            now()->addHours(6),
            function () {
                return Subcategory::select('id', 'name', 'slug')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
            }
        );
    }

    return static::$subcategoryLookup;
}

public static function forgetSubcategoryLookup(): void
{
    static::$subcategoryLookup = null;

    Cache::forget(Subcategory::LOOKUP_CACHE_KEY);
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * The admin who added this listing.
     *
     * Recorded on create only, so it stays the person who put the listing in
     * rather than whoever edited it last. Null for listings that predate the
     * column and for anything a user submitted themselves.
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function extraImages()
    {
        return $this->hasMany(ExtraImage::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    

    public function scopeNotExpired($query)
    {
        return $query;
    }

    /** Google truncates titles past ~60 chars; under 30 reads as thin. */
    public const META_TITLE_MIN = 30;
    public const META_TITLE_MAX = 60;
    public const META_BRAND     = 'Service Bazzar';

    /**
     * Build an SEO meta title from a listing's own fields.
     *
     * Shape: "{Name} in {Area}, {City}". That already carries the business
     * name and the city (what local-search SEO needs) and, because the area
     * is in it, is long enough and distinct enough to clear the "too short"
     * and "duplicated on another page" audit rules that the older bare
     * "{Name} in {City}" titles kept tripping.
     *
     * When name+area+city is still under 30 chars the category is folded in
     * ("{Name} - {Category} in {Place}"); $forceCategory does that up front,
     * which is how the save hook and the backfill break a title that would
     * otherwise duplicate another listing's. Anything over 60 falls back to
     * "{Name} in {City}", then a hard trim as a last resort.
     */
    public static function buildMetaTitle($name, $area, $city, $categoryName = null, bool $forceCategory = false): string
    {
        $name = trim((string) $name);
        $area = trim((string) $area);
        $city = trim((string) $city);
        $cat  = trim((string) $categoryName);

        $place = '';
        if ($area !== '' && strcasecmp($area, $city) !== 0) {
            $place = $area . ($city !== '' ? ', ' . $city : '');
        } elseif ($city !== '') {
            $place = $city;
        }

        $base = $place !== '' ? $name . ' in ' . $place : $name;

        if ($forceCategory && $cat !== '') {
            $withCat = $name . ' - ' . $cat . ($place !== '' ? ' in ' . $place : '');
            $title = mb_strlen($withCat) <= self::META_TITLE_MAX ? $withCat : $base;
        } else {
            $title = $base;
            if (mb_strlen($title) < self::META_TITLE_MIN && $cat !== '') {
                $withCat = $name . ' - ' . $cat . ($place !== '' ? ' in ' . $place : '');
                if (mb_strlen($withCat) <= self::META_TITLE_MAX) {
                    $title = $withCat;
                }
            }
        }

        if (mb_strlen($title) > self::META_TITLE_MAX) {
            $alt = $city !== '' ? $name . ' in ' . $city : $name;
            $title = mb_strlen($alt) <= self::META_TITLE_MAX ? $alt : rtrim(mb_substr($title, 0, self::META_TITLE_MAX));
        }

        return $title;
    }

    /** Append " | Service Bazzar" when a title is still under the minimum. */
    public static function padMetaBrand($title): string
    {
        $title = trim((string) $title);
        $withBrand = $title . ' | ' . self::META_BRAND;

        return mb_strlen($withBrand) <= self::META_TITLE_MAX ? $withBrand : $title;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($listing) {
            $listing->slug = static::generateSlug($listing->name);
        });

        // Guarantee every listing carries a healthy meta title, so the SEO
        // audit's "Title short" / "Title duplicate" rows can't keep appearing
        // for new or re-saved listings. A title that is already 30-60 chars is
        // left untouched — that's an admin- or AI-authored one we shouldn't
        // second-guess; only a missing, too-short or too-long one is rebuilt.
        static::saving(function ($listing) {
            $current = trim((string) ($listing->meta_title ?? ''));
            $len = mb_strlen($current);

            if ($current !== '' && $len >= self::META_TITLE_MIN && $len <= self::META_TITLE_MAX) {
                return;
            }

            $categoryName = $listing->category_id ? optional($listing->category)->name : null;

            $title = static::buildMetaTitle($listing->name, $listing->area, $listing->city, $categoryName);

            $clashes = function ($candidate) use ($listing) {
                return static::where('meta_title', $candidate)
                    ->when($listing->id, fn ($q) => $q->where('id', '!=', $listing->id))
                    ->exists();
            };

            if ($clashes($title)) {
                $alt = static::buildMetaTitle($listing->name, $listing->area, $listing->city, $categoryName, true);
                if ($alt !== $title && !$clashes($alt)) {
                    $title = $alt;
                }
            }

            if (mb_strlen($title) < self::META_TITLE_MIN) {
                $title = static::padMetaBrand($title);
            }

            $listing->meta_title = $title;
        });

        static::updating(function ($listing) {
            if ($listing->isDirty('name')) {
                $oldSlug = $listing->getOriginal('slug');
                $listing->slug = static::generateSlug($listing->name, $listing->id);

                $categorySlug = optional($listing->category)->slug;

                if ($categorySlug && $oldSlug && $oldSlug !== $listing->slug) {
                    \App\Models\UrlRedirect::remember(
                        '/listings/' . $categorySlug . '/' . $oldSlug,
                        '/listings/' . $categorySlug . '/' . $listing->slug
                    );
                }
            }
        });


    }

    public function getSubcategoryIdsArrayAttribute()
{
    if (is_array($this->subcategory_ids)) {
        return $this->subcategory_ids;
    }

    return json_decode($this->subcategory_ids, true) ?? [];
}

    /**
     * The address as one readable line.
     *
     * `address` is a single free-text column that most listings leave empty —
     * the real address is spread across the parts below, which is why anything
     * reading `address` on its own shows nothing. Falls back to assembling
     * them, skipping whichever are blank so no stray commas appear.
     */
    public function getFullAddressAttribute(): string
    {
        if (!empty($this->address)) {
            return $this->address;
        }

        return collect([
            $this->plot_no,
            $this->building_name,
            $this->street_name,
            $this->landmark,
            $this->area,
            $this->city,
            $this->state,
            $this->pincode,
        ])->filter()->implode(', ');
    }

    protected static function generateSlug($name, $id = null)
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($id, function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                })
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}