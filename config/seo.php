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

    /*
    |--------------------------------------------------------------------------
    | Default city for internal category links
    |--------------------------------------------------------------------------
    |
    | Category links on shared, full-page-cached pages (the homepage etc.)
    | can't carry a per-visitor city server-side, so they're rendered with
    | this stable default city and rewritten client-side to the visitor's own
    | location. Using a real city here means those links point straight at a
    | live 200 page (e.g. /jaipur/packers-and-movers) instead of the
    | /listings/{slug} shortcut, which only 301-redirects to the same place —
    | so crawlers don't spend a redirect hop on every internal category link.
    |
    */

    'default_city' => env('SEO_DEFAULT_CITY', 'jaipur'),

    /*
    |--------------------------------------------------------------------------
    | IndexNow key
    |--------------------------------------------------------------------------
    |
    | Tells Bing (and through it Copilot, DuckDuckGo and ChatGPT search) about
    | new listings straight away instead of waiting for a crawl. The same key
    | must be served at /{key}.txt — see public/. Google does not use IndexNow.
    |
    */

    'indexnow_key' => env('INDEXNOW_KEY', 'e236d936ea6569bdc12b209d3088cb3d'),

];
