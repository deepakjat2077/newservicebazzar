<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Claim a business by its phone number
    |--------------------------------------------------------------------------
    |
    | When someone verifies an OTP on a number that is printed on a listing,
    | that listing is moved to their account (App\Support\ListingClaim), so a
    | business created by the sales team, or by an earlier signup with a typo
    | in the number, shows up on the account its owner actually signs in with.
    |
    | Service Bazzar's own numbers are excluded automatically (every admin's,
    | plus the WhatsApp sender) because the sales team filled their own number
    | in on many listings. Add any others here, comma-separated.
    |
    */

    'claim_by_phone' => env('LISTING_CLAIM_BY_PHONE', true),

    'claim_blocked_numbers' => env('LISTING_CLAIM_BLOCKED_NUMBERS', ''),

    /*
    | A number on more than this many listings waits for an admin instead of
    | moving on its own. There is no limit on how many listings one owner may
    | have; this only catches a number typed in across unrelated businesses.
    | 0 turns the check off and moves every match.
    */

    'claim_review_above' => (int) env('LISTING_CLAIM_REVIEW_ABOVE', 5),

    /*
    |--------------------------------------------------------------------------
    | Google place id lookups per calendar month
    |--------------------------------------------------------------------------
    |
    | google:fetch-place-ids stops once it has made this many Places "Text
    | Search" calls in a calendar month. Google gives 5,000 free each month,
    | so the default costs nothing and the counter resets on the 1st by
    | itself: the job can be scheduled at any hourly rate and will simply idle
    | once the month's free allowance is spent. Raise it to pay for speed —
    | each call beyond 5,000 is about Rs 2.80.
    |
    */

    'place_id_monthly_cap' => [
        // Google gives 5,000 free Text Search calls a calendar month, so this
        // is what a month costs nothing. The trial credits (₹28,797, expiring
        // 9 Jan 2027) are spent in November to clear the backlog, stopping
        // just short of them — the last ~440 listings roll into December and
        // ride that month's free allowance instead.
        '2026-10' => 5000,
        '2026-11' => 9200,
        'default' => (int) env('GOOGLE_PLACE_ID_MONTHLY_CAP', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Everyday words for a category the assistant does have
    |--------------------------------------------------------------------------
    |
    | Visitors ask for a cafe, a taxi, a gym, a PG. None of those words appear
    | in any category name, so the assistant found nothing and answered "we
    | have no listings for that" about businesses it is in fact listing. Each
    | line maps what people type to the category it means.
    |
    | Only consulted when nothing matched a category name outright, so adding
    | a word here can never override a real name. Phrases are matched as whole
    | words, longest first.
    |
    */

    'assistant_synonyms' => [
        'Restaurant' => [
            'coffee shop', 'coffee house', 'cafeteria', 'cafe', 'cafes', 'caffe', 'kefe',
            'dhaba', 'restro', 'resto', 'eatery', 'bhojnalaya', 'khana',
            'resturant', 'restorent', 'restraunt',
        ],
        'Hotel' => ['hotal', 'lodge', 'guest house', 'guesthouse'],
        'Paying Guest Accommodation' => ['pg', 'paying guest', 'hostel'],
        'Gym Center' => ['gym', 'gyms', 'fitness centre', 'fitness center', 'fitness'],
        'Car Hire' => ['taxi', 'taxis', 'cab', 'cabs', 'car rental', 'car on rent', 'rent a car'],
        'Car Repair' => ['mechanic', 'garage', 'car service', 'denting painting'],
        'Packers And Movers' => ['shifting', 'house shifting', 'home shifting', 'relocation'],
        'Caterers' => ['catering', 'tiffin', 'tiffin service'],
        'Cake Shop' => ['bakery', 'bakers', 'pastry shop'],
        'Institute' => ['coaching', 'coaching centre', 'coaching center', 'tuition', 'classes'],
        'College' => ['collage'],
        'Lawyers' => ['advocate', 'advocates', 'vakil', 'legal advisor'],
        'Beauty Parlours' => ['parlor', 'parlour', 'beauty parlor'],
        'Salon' => ['barber', 'hair cutting', 'hair salon', 'hair dresser'],
        'Bridal Makeup Services' => ['makeup artist', 'make up artist'],
        'Mehndi Artist' => ['mehandi', 'mehendi', 'henna'],
        'Physiotherapists' => ['physiotherapy', 'physio'],
        'Travel Agent' => ['tour operator', 'travel agency', 'tour package'],
        'Real Estate' => ['property dealer', 'property dealers', 'broker', 'flat', 'plot'],
    ],

];
