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

    /*
    |--------------------------------------------------------------------------
    | "People also search for" Q&As (seo:pasf-faq)
    |--------------------------------------------------------------------------
    |
    | Category ids whose city pages get the related-searches block with short
    | answers written from that city's listings. A trial on Pet Shop (53)
    | first; the command refuses any other category, and nothing shows until
    | an admin approves it. Comma-separated in SEO_PASF_FAQ_CATEGORIES.
    |
    */

    'pasf_faq_categories' => array_filter(array_map('intval', explode(',', (string) env('SEO_PASF_FAQ_CATEGORIES', '53')))),

    // A related search is kept only if it has one of its category's words
    // (Google's related searches for "pet shop in kolkata" include "kolkata
    // map" and "capital of kolkata"), and never if it has an excluded phrase
    // ("pet scan" is a hospital test). Without an entry here, the words of
    // the category name are used.
    'pasf_faq_terms' => [
        53 => [
            'pet', 'pets', 'dog', 'dogs', 'puppy', 'puppies', 'pup', 'cat', 'cats', 'kitten', 'kittens',
            'bird', 'birds', 'parrot', 'parrots', 'fish', 'aquarium', 'rabbit', 'rabbits', 'hamster', 'hamsters',
            'labrador', 'pug', 'pomeranian', 'persian', 'beagle', 'husky', 'shihtzu', 'boxer',
            'squirrel', 'turtle', 'tortoise', 'iguana', 'guinea', 'exotic', 'animal', 'animals',
            'kutte', 'kutta', 'billi', 'कुत्ते', 'कुत्ता', 'डॉग', 'पेट', 'बिल्ली',
        ],
    ],
    // Searches that want the same answer share one Q&A; a search goes to the
    // first group whose pattern it matches (a category's own groups first,
    // then the general ones in CategoryFaqWriter::GROUPS), and one matching
    // none gets a Q&A of its own.
    'pasf_faq_groups' => [
        53 => [
            'cats'    => '/\b(cats?|kittens?|persian|billi|बिल्ली)\b/u',
            'birds'   => '/\b(birds?|parrots?|budgies?|lovebirds?)\b/u',
            'fish'    => '/\b(fish|aquariums?)\b/u',
            'small'   => '/\b(rabbits?|hamsters?|guinea)\b/u',
            'food'    => '/\b(food|nutrition|treats)\b/u',
            'accessories' => '/\b(accessor\w*|toys?|cages?|collars?|leash\w*|beds?)\b/u',
            'grooming'    => '/\b(groom\w*|spa|bath\w*|haircut)\b/u',
            'dogs'    => '/\b(dogs?|pupp(y|ies)|pup|labrador|pug|pomeranian|beagle|husky|shih ?tzu|boxer|kutte|kutta|कुत्ते|कुत्ता|डॉग)\b/u',
        ],
    ],
    // Places abroad that share a name with one of our cities (Kota Kinabalu,
    // Hyderabad Sindh, Surat Thani): a search with any of these is dropped.
    'pasf_faq_foreign_words' => [
        'thani', 'sindh', 'pakistan', 'karachi', 'lahore', 'qasimabad', 'latifabad', 'kotri',
        'kinabalu', 'bharu', 'damansara', 'kemuning', 'warisan', 'tinggi', 'jaya', 'masai', 'puteri', 'sentosa',
        'wisata', 'cibubur', 'yogyakarta', 'bekasi', 'jakarta', 'bandung', 'indonesia', 'malaysia', 'thailand',
        'bangladesh', 'nepal', 'dhaka', 'fotos', 'tu', 'kuala', 'selangor', 'johor', 'sabah', 'kasablanka', 'joe', 'bogor', 'padang', 'cirebon', 'garut', 'sidoarjo', 'taman', 'jawa',
        'graha', 'pendamar', 'laksamana', 'marudu', 'permai', 'samarahan', 'belud', 'danau', 'seriemas',
        'parahyangan', 'padawan', 'syahbandar', 'sarang', 'semut', 'baru', 'bharu',
    ],
    'pasf_faq_exclude' => [
        'pet scan', 'pet test', 'pet ct', 'pet-ct', 'pet bottle', 'pet exam', 'pet full form', 'plastic',
        'license', 'licence', 'open a pet shop', 'start a pet shop', 'fire', 'catcher', 'allowed', 'reviews', 'review', 'photos',
        'jobs', 'job', 'vacancy', 'salary', 'franchise', 'wholesale rate', 'business', 'km',
    ],

];
