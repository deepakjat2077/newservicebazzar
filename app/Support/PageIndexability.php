<?php

namespace App\Support;

/**
 * Whether a generated lander page has enough content to be worth indexing.
 *
 * The "is this page too empty to index?" test used to be written inline at
 * each lander, three times, in two different shapes (`->total() === 0` on a
 * paginator, `->isEmpty()` on a collection) — and was missing entirely from
 * the city-only pages, which is how empty city pages stayed indexable. One
 * place to ask means the answer cannot drift between page types again, and
 * the threshold behind it becomes a config change rather than a code change.
 *
 * @see config/seo.php
 */
class PageIndexability
{
    /**
     * The robots directive for a lander holding $listingCount live listings.
     *
     * `follow` is kept in both branches on purpose: a page that should not be
     * indexed still carries links to related categories and cities that
     * should be crawled.
     */
    public static function robots(int $listingCount): string
    {
        return self::shouldIndex($listingCount)
            ? 'index, follow'
            : 'noindex, follow';
    }

    public static function shouldIndex(int $listingCount): bool
    {
        return $listingCount >= self::minimumListings();
    }

    /**
     * Never below 1 — at zero the setting would mark genuinely empty pages as
     * indexable, which is the exact problem this class exists to prevent.
     */
    public static function minimumListings(): int
    {
        return max(1, (int) config('seo.min_listings_to_index', 1));
    }
}
