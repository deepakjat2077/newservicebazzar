<?php

namespace App\Support;

/**
 * The rating a visitor actually sees on a listing: Service Bazzar's own
 * ratings and the Google ones averaged together, weighted by how many of each
 * there are — the same sum the listing card and the listing page do in PHP.
 *
 * Filtering and sorting used the `review` column alone, which is only the
 * site's own average. Most listings have no rating of their own and carry
 * only a Google one, so "Ratings 3.5+" returned an empty page on categories
 * that plainly showed 4.7-star businesses.
 *
 * The own-rating count has no column of its own, so it comes from a
 * correlated subquery. That is only worth it when the visitor has actually
 * asked to filter or sort by rating, which is why this is not in the default
 * listing query.
 */
class ListingRating
{
    /** Weighted average of the site's own rating and Google's, as a SQL expression. */
    public static function sql(string $table = 'listings'): string
    {
        $own = "(SELECT COUNT(*) FROM ratings WHERE ratings.listing_id = {$table}.id)";

        return "(
            (COALESCE({$table}.review, 0) * {$own})
            + (COALESCE({$table}.gmb_rating, 0) * COALESCE({$table}.gmb_review_count, 0))
        ) / NULLIF({$own} + COALESCE({$table}.gmb_review_count, 0), 0)";
    }

    /** How many ratings that average is based on, both sources together. */
    public static function countSql(string $table = 'listings'): string
    {
        return "((SELECT COUNT(*) FROM ratings WHERE ratings.listing_id = {$table}.id)
            + COALESCE({$table}.gmb_review_count, 0))";
    }
}
