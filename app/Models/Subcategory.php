<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Subcategory extends Model
{
    /** The id => {id,name,slug} map Listing::subcategoryLookup() reads. */
    public const LOOKUP_CACHE_KEY = 'subcategories.lookup';

    /** Google truncates titles past ~60 chars; under 30 reads as thin. */
    public const SEO_TITLE_MIN = 30;
    public const SEO_TITLE_MAX = 60;
    public const SEO_BRAND     = 'ServiceBazzar';

    /**
     * Candidate SEO titles for a subcategory, best first.
     *
     * The name is the search term itself, so every candidate keeps it intact.
     * The older writer padded short names to "Best {name} Services |
     * ServiceBazzar" after folding a " Services" suffix into the name — which
     * made "GST Compliance" and "GST Compliance Services" collapse to the same
     * title and fed the SEO audit's "Title duplicate" bucket. Building from the
     * raw name keeps near-identical names distinct, and the ladder gives the
     * save hook clean alternatives ("Top …", "Trusted …", "{name} in
     * {category}") to break any remaining clash. Only 30-60 char forms are
     * returned; a guaranteed in-range fallback is appended last.
     */
    public static function seoTitleCandidates(string $name, ?string $categoryName = null): array
    {
        $name = trim($name);
        $cat  = trim((string) $categoryName);
        $display = \App\Support\LanderNames::plural($name);

        // Same shape as the live page titles (App\Support\LanderSchema): a lead
        // word + name + " – suffix", 50-60 chars, no brand. Several seeds give
        // distinct alternatives for the uniqueness check in the saving hook.
        $raw = [];
        foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $seed) {
            $raw[] = \App\Support\LanderSchema::landerTitle($display, $name . ':' . $seed);
        }
        $raw[] = $cat !== '' ? \App\Support\LanderSchema::landerTitle($display . ' – ' . $cat, $name) : null;
        $raw[] = $name;

        $ok = [];
        foreach ($raw as $c) {
            if ($c === null) {
                continue;
            }
            $len = mb_strlen($c);
            if ($len >= self::SEO_TITLE_MIN && $len <= self::SEO_TITLE_MAX) {
                $ok[] = $c;
            }
        }

        if ($ok === []) {
            $base = mb_strlen($name) > self::SEO_TITLE_MAX
                ? rtrim(mb_substr($name, 0, self::SEO_TITLE_MAX))
                : $name;
            if (mb_strlen($base) < self::SEO_TITLE_MIN) {
                $base = mb_substr("Best {$name} – Verified Professionals", 0, self::SEO_TITLE_MAX);
            }
            $ok[] = $base;
        }

        return array_values(array_unique($ok));
    }

    /** The deterministic best title (no uniqueness lookup). */
    public static function buildSeoTitle(string $name, ?string $categoryName = null): string
    {
        return self::seoTitleCandidates($name, $categoryName)[0];
    }

    /** Any write invalidates the map the listing cards render from. */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::LOOKUP_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::LOOKUP_CACHE_KEY));

        // Keep every subcategory's seo_title healthy (30-60 chars) AND unique,
        // so the SEO audit's "Title short" / "Title duplicate" rows can't come
        // back for new or re-saved subcategories. A title that is already in
        // range and unique is left as the admin/AI wrote it.
        static::saving(function ($sub) {
            $current = trim((string) ($sub->seo_title ?? ''));
            $len = mb_strlen($current);
            $healthy = $current !== '' && $len >= self::SEO_TITLE_MIN && $len <= self::SEO_TITLE_MAX;

            // The audit also wants the whole name in the title, so a title
            // missing it is not healthy however well-sized it is. A name that
            // can't fit under the 60-char cap is exempt.
            $name = trim((string) ($sub->name ?? ''));
            if ($healthy && $name !== '' && mb_strlen($name) <= self::SEO_TITLE_MAX) {
                // "24 Hours" in the name and "24/7" in the title count as the same.
                $norm = fn ($v) => preg_replace('/\b24\s*(?:hours?|hrs?|x\s*7|\*\s*7|\/\s*7)(?=\s|$)/', '24/7', mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v))));
                // The plural form counts too ("Recruitment Agencies" for "Recruitment Agency").
                $healthy = str_contains($norm($current), $norm($name))
                    || str_contains($norm($current), $norm(\App\Support\LanderNames::plural($name)));
            }

            $collides = function ($title) use ($sub) {
                return static::where('seo_title', $title)
                    ->when($sub->id, fn ($q) => $q->where('id', '!=', $sub->id))
                    ->exists();
            };

            if ($healthy && !$collides($current)) {
                return;
            }

            $categoryName = $sub->category_id ? optional($sub->category)->name : null;
            $candidates = self::seoTitleCandidates((string) $sub->name, $categoryName);

            $chosen = $candidates[0];
            foreach ($candidates as $c) {
                if (!$collides($c)) {
                    $chosen = $c;
                    break;
                }
            }

            $sub->seo_title = $chosen;
        });

        // Never store a description past the 160-char audit ceiling.
        static::saving(function ($sub) {
            $desc = trim((string) ($sub->seo_description ?? ''));
            if ($desc !== '' && mb_strlen($desc) > 160) {
                $sub->seo_description = sb_clamp_meta_description($desc, 160);
            }
        });
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