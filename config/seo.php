<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minimum listings before a lander page may be indexed
    |--------------------------------------------------------------------------
    |
    | City x category, city x subcategory and city-only pages are generated
    | from the catalogue, so the site can produce far more URLs than it has
    | content for. A page below this many live listings is served with
    | "noindex, follow": Google is told upfront not to index it, instead of
    | spending crawl budget to reach that conclusion itself and reporting it
    | back as Soft 404 / "Crawled - currently not indexed".
    |
    | `follow` is always kept, so the related category/city links on a
    | non-indexable page still pass crawl paths through to real pages.
    |
    | 1 (the default) means only genuinely empty pages are held back — a page
    | with a single listing stays indexable. Raising this to 2 or 3 also holds
    | back thin pages, which is the usual answer to a thin-content or keyword
    | cannibalization finding, at the cost of de-indexing pages where a real
    | business is the only one listed. Change the number here (or set
    | SEO_MIN_LISTINGS_TO_INDEX) — nothing else needs editing.
    |
    */

    'min_listings_to_index' => (int) env('SEO_MIN_LISTINGS_TO_INDEX', 1),

];
