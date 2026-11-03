<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryPageFaq;
use App\Models\Listing;
use App\Models\Location;
use App\Support\RelatedSearches;
use Illuminate\Support\Str;

/**
 * The "People also search for" Q&As on a city x category page: what people
 * search around "<category> in <city>" (App\Support\RelatedSearches), each
 * answered in 2-3 sentences from that city's own listings — how many there
 * are, in which areas, their hours and what they offer — so the answers say
 * something a generic article can't. Saved as a draft; an admin approves it
 * before it shows on the page.
 */
class CategoryFaqWriter
{
    /** What a listing's own description says it deals in; the only "offers" the answers may claim. */
    private const OFFER_TERMS = [
        'dogs'            => '/\b(dogs?|pupp(y|ies)|canine)\b/i',
        'cats'            => '/\b(cats?|kittens?|feline)\b/i',
        'birds'           => '/\b(birds?|parrots?|budgies?|lovebirds?)\b/i',
        'fish/aquarium'   => '/\b(fish|aquariums?|aquatic)\b/i',
        'rabbits'         => '/\brabbits?\b/i',
        'pet food'        => '/\b(pet food|dog food|cat food|food|nutrition|treats)\b/i',
        'accessories'     => '/\b(accessor(y|ies)|toys?|leash|collars?|beds?)\b/i',
        'grooming'        => '/\bgroom(ing|er)?\b/i',
        'vet/clinic'      => '/\b(vet|veterinar(y|ian)|clinic|doctor|vaccinat\w*)\b/i',
        'training'        => '/\btrain(ing|er)\b/i',
        'boarding'        => '/\b(boarding|hostel|daycare|day care)\b/i',
        'pet cafe'        => '/\bcaf[eé]\b/i',
        'home delivery'   => '/\bhome delivery|deliver(y|s)\b/i',
    ];

    /**
     * General search groups: a search goes to the first that matches, with a
     * category's own groups (config seo.pasf_faq_groups) between BEFORE and
     * AFTER. Each group becomes one Q&A, so "pet shop jaipur", "pet store in
     * jaipur" and "best pet shop in jaipur" share an answer instead of three
     * near-identical ones, while "dog price" never lands under "pet food".
     */
    private const GROUPS_BEFORE = [
        'price'    => '/\b(price|prices|cost|costs|rate|rates|cheap|sasta|budget|kitne|kitna)\b/u',
        'contact'  => '/\b(contact|number|phone|mobile|whatsapp)\b/u',
        'online'   => '/\b(online|delivery|order)\b/u',
        'adoption' => '/\b(adopt\w*|adoption|free)\b/u',
    ];
    private const GROUPS_AFTER = [
        'buy'  => '/\b(buy|purchase|sale|bazaar|market|wholesale)\b/u',
        'near' => '/(\bnear\b|\bnearby\b|\bkahan\b|\bkaha\b|\bwhere\b|कहां|कहाँ)/u',
    ];
    private const STATES = [
        'india', 'rajasthan', 'gujarat', 'maharashtra', 'telangana', 'karnataka', 'punjab', 'haryana', 'ncr', 'up',
        'uttar', 'pradesh', 'madhya', 'west', 'bengal', 'tamil', 'nadu', 'kerala', 'bihar', 'jammu', 'kashmir', 'and',
    ];

    /** Words that don't make a search about anything more than "the list of shops". */
    private const LIST_WORDS = ['best', 'top', 'good', 'famous', 'popular', 'shop', 'shops', 'store', 'stores', 'in', 'the', 'of', 'a', 'list', 'me', 'mein', 'wala', 'wale', 'city', 'india', 'available', 'which', 'what', 'are', 'is', 'some', 'there', 'any'];

    public function __construct(protected AiContentGenerator $ai)
    {
    }

    public function isConfigured(): bool
    {
        return $this->ai->isConfigured();
    }

    /** Find the searches, write the answers, save as a draft (replaces an older draft or rejection). */
    public function generate(Category $category, Location $location): CategoryPageFaq
    {
        $row = CategoryPageFaq::firstOrNew(['category_id' => $category->id, 'location_id' => $location->id]);

        $listings = $this->listings($category, $location);
        if ($listings->isEmpty()) {
            return $this->fail($row, 'No live listings in this city.');
        }

        $found = RelatedSearches::for(
            $category->name,
            $location->name,
            (array) (config('seo.pasf_faq_terms')[$category->id] ?? [])
        );
        if (!$found['keywords']) {
            return $this->fail($row, 'No related searches found on Google.');
        }

        // Four groups per request, sent together: asked for all 15 at once the
        // AI stops after about 8 and silently drops the rest.
        $keywords = $this->keep($found['keywords'], $category, $location, $listings);
        if (!$keywords) {
            return $this->fail($row, 'No related searches left after removing other places and off-topic ones.');
        }
        $groups = array_slice($this->groups($keywords, $category, $location), 0, 15);
        $prompts = [];
        foreach (array_chunk($groups, 4, true) as $n => $chunk) {
            $prompts[$n] = $this->prompt($category, $location, $chunk, $listings);
        }
        $merged = ['faqs' => [], 'skipped' => []];
        $failed = null;
        foreach ($this->ai->jsonBatch($prompts, 60) as $result) {
            if (!$result['status']) {
                $failed = $result['message'] ?? 'AI request failed.';
                continue;
            }
            $merged['faqs'] = array_merge($merged['faqs'], (array) ($result['data']['faqs'] ?? []));
            $merged['skipped'] = array_merge($merged['skipped'], (array) ($result['data']['skipped'] ?? []));
        }
        if (!$merged['faqs'] && $failed) {
            return $this->fail($row, $failed);
        }

        $faqs = $this->parse($merged, $groups);
        if (!$faqs) {
            return $this->fail($row, 'AI returned no usable answers.');
        }

        $links = $this->links($category, $listings);

        $row->fill([
            'keywords'       => $found['keywords'],
            'keyword_source' => $found['source'],
            'faqs'           => $faqs,
            'links'          => $links,
            'status'         => 'draft',
            'error'          => null,
            'generated_at'   => now(),
            'approved_at'    => null,
            'approved_by'    => null,
        ])->save();

        return $row;
    }

    /**
     * Listing name => page URL, for linking the names in the answers. The AI
     * often shortens "Dogs Heaven Pet Shop & Parlour" to "Dogs Heaven Pet
     * Shop", so the part before "&" / "and" / "-" is added too.
     */
    public function links(Category $category, $listings = null): array
    {
        $links = [];
        foreach ($listings ?? collect() as $l) {
            $url = route('listings.details', ['category' => $category->slug, 'slug' => $l->slug]);
            $links[$l->name] ??= $url;

            $short = trim(preg_split('/\s+(?:&|and|-|\|)\s+/i', $l->name)[0]);
            if ($short !== $l->name && str_word_count($short) >= 2) {
                $links[$short] ??= $url;
            }
        }

        return $links;
    }

    public function linksFor(Category $category, Location $location): array
    {
        return $this->links($category, $this->listings($category, $location));
    }

    private function fail(CategoryPageFaq $row, string $error): CategoryPageFaq
    {
        // A failed retry never takes down Q&As that are already live.
        if ($row->exists && $row->status === 'approved') {
            return $row;
        }
        $row->fill(['status' => 'draft', 'error' => mb_substr($error, 0, 500), 'generated_at' => now()])->save();

        return $row;
    }

    private function listings(Category $category, Location $location)
    {
        return Listing::where('status', 1)
            ->where('category_id', $category->id)
            ->where('location_id', $location->id)
            ->orderByDesc('verified')->orderBy('id')
            ->limit(40)
            ->get(['id', 'name', 'slug', 'area', 'address', 'details', 'business_days', 'open_time', 'close_time', 'verified', 'gmb_rating', 'gmb_review_count']);
    }

    /** One line per listing: name | area | hours | rating | what its own page says it offers. */
    private function facts($listings, Location $location): string
    {
        $lines = [];
        foreach ($listings as $l) {
            $area = trim(preg_replace('/\s*\(' . preg_quote($location->name, '/') . '\)\s*/i', '', (string) $l->area));

            $hours = '';
            $open = array_values((array) $l->open_time);
            $close = array_values((array) $l->close_time);
            $days = array_values((array) $l->business_days);
            if ($open && $close) {
                $hours = 'open ' . implode(', ', array_unique(array_map(fn ($o, $c) => "{$o}-{$c}", $open, $close)));
                $hours .= in_array('Sun', $days, true) ? ', open Sundays' : ($days ? ', closed Sundays' : '');
            }

            $rating = ($l->gmb_rating && $l->gmb_review_count) ? "Google rating {$l->gmb_rating}/5 ({$l->gmb_review_count} reviews)" : '';
            $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $l->details))));

            // Read off the whole description here rather than trusting the AI
            // with a cut-down copy: it both missed and invented "sells cat food".
            $offers = [];
            foreach (self::OFFER_TERMS as $label => $pattern) {
                if (preg_match($pattern, $text . ' ' . $l->name)) {
                    $offers[] = $label;
                }
            }

            $lines[] = '- ' . implode(' | ', array_filter([
                $l->name,
                $area !== '' ? 'area: ' . $area : null,
                $hours ?: null,
                $l->verified ? 'verified by Service Bazzar' : null,
                $rating ?: null,
                'mentions: ' . ($offers ? implode(', ', $offers) : 'nothing specific'),
            ]));
        }

        return implode("\n", $lines);
    }

    /**
     * Drop the searches that don't belong on this page, judged on their own
     * (asked inside the answer-writing prompt, the AI answered "Surat Thani"
     * and "Kota Kinabalu" anyway): another country or town, another
     * business's name, or not about going to or buying from such a shop.
     * If the check itself fails, everything is kept: it is only a draft.
     */
    private function keep(array $keywords, Category $category, Location $location, $listings): array
    {
        $city = $location->name;

        // Off-topic searches and other businesses' names are dropped by fixed
        // rules in RelatedSearches. Only "is this place in {$city}?" is left
        // to the AI, which is unreliable with long mixed lists (it kept "kota
        // jaya" and dropped "uppal hyderabad"): 10 at a time, and it must
        // label every one.
        // A search with nothing but the city, a state and ordinary words names
        // no other place: only the rest go to the AI (it was dropping "dog
        // shop in kota rajasthan" and "hamster pet shop in hyderabad").
        $plain = array_merge(
            self::LIST_WORDS, self::STATES, ['near', 'at', 'sector', 'sec', 'open', 'now', 'price', 'prices', 'with', 'online', 'for', 'sale', 'buy', 'cost'],
            preg_split('/[^\p{L}\p{M}\d]+/u', mb_strtolower($category->name . ' ' . $city), -1, PREG_SPLIT_NO_EMPTY),
            (array) (config('seo.pasf_faq_terms')[$category->id] ?? [])
        );
        $ask = array_filter($keywords, fn ($k) => (bool) array_diff(
            preg_split('/[^\p{L}\p{M}\d]+/u', $k, -1, PREG_SPLIT_NO_EMPTY), $plain
        ));

        $prompts = [];
        foreach (array_chunk($ask, 10, true) as $n => $chunk) {
            $list = implode("\n", array_map(fn ($k, $i) => ($i + 1) . '. ' . $k, $chunk, array_keys($chunk)));
            $prompts[$n] = <<<PROMPT
These Google searches mention places. For each one, say where the place is.

{$list}

"{$city}" means {$city}, India. Label each search:
- "india": the search is about {$city}, India, or an area, road, market, mall, sector or neighbourhood inside {$city}, India, or names no place except {$city}.
- "elsewhere": the search is about a place outside {$city}, India: another country (for example Kota Kinabalu, Kota Bharu, Kota Damansara, Kota Jaya, Kota Masai in Malaysia; Kota Wisata in Indonesia; Hyderabad Sindh, Qasimabad, Latifabad in Pakistan; Surat Thani in Thailand) or another Indian town (Suratgarh, Surathkal).

Return JSON with every number: {"labels": {"1": "india", "2": "elsewhere"}}
PROMPT;
        }

        $elsewhere = [];
        foreach ($this->ai->jsonBatch($prompts, 40) as $result) {
            if (!$result['status']) {
                continue; // that chunk is kept as is
            }
            foreach ((array) ($result['data']['labels'] ?? []) as $n => $label) {
                if (mb_strtolower(trim((string) $label)) === 'elsewhere') {
                    $elsewhere[(int) $n - 1] = true;
                }
            }
        }

        return array_values(array_filter($keywords, fn ($k, $i) => !isset($elsewhere[$i]), ARRAY_FILTER_USE_BOTH));
    }

    /** @return array<int, string[]> searches per group, in order of each group's first search */
    private function groups(array $keywords, Category $category, Location $location): array
    {
        $patterns = self::GROUPS_BEFORE + (array) (config('seo.pasf_faq_groups')[$category->id] ?? []) + self::GROUPS_AFTER;
        $plain = array_merge(
            self::LIST_WORDS, ['near', 'at', 'sector', 'sec', 'open', 'now'],
            preg_split('/[^\p{L}\p{M}\d]+/u', mb_strtolower($category->name . ' ' . $location->name), -1, PREG_SPLIT_NO_EMPTY),
            (array) (config('seo.pasf_faq_terms')[$category->id] ?? [])
        );

        $groups = [];
        foreach ($keywords as $k) {
            $name = null;
            foreach ($patterns as $g => $re) {
                if (preg_match($re, $k)) {
                    $name = $g;
                    break;
                }
            }
            if ($name === null) {
                // Only list words: the list of shops. Anything more (an area,
                // "sector 22", a Hindi phrasing) gets its own answer.
                // "pet shop in pune camp" and "pet shop pune camp", or "noida sec
                // 18" and "noida sector 18", are the same search.
                $rest = array_diff(preg_split('/[^\p{L}\p{M}\d]+/u', $k, -1, PREG_SPLIT_NO_EMPTY), $plain);
                $rest = array_unique(array_map(fn ($w) => $w === 'sec' ? 'sector' : $w, $rest));
                sort($rest);
                $name = $rest ? 'own:' . implode(' ', $rest) : 'list';
            }
            $groups[$name][] = $k;
        }

        return array_values($groups);
    }

    private function prompt(Category $category, Location $location, array $groups, $listings): string
    {
        $service = $category->name;
        $city = $location->name;
        $count = $listings->count();
        $searches = implode("\n", array_map(
            fn ($g, $i) => 'Group ' . ($i + 1) . ': ' . implode(' | ', $g),
            $groups,
            array_keys($groups)
        ));
        $facts = $this->facts($listings, $location);

        return <<<PROMPT
You write a short Q&A section for the "{$service} in {$city}" page of Service Bazzar, an Indian local business directory.

People who search for "{$service} in {$city}" on Google also search for these, grouped by what they want to know:
{$searches}

These are ALL {$count} {$service} businesses listed in {$city} on Service Bazzar (one per line):
{$facts}

For each group, write one question and answer that answers what the searches in it want to know. Write a separate question and answer for every group, in the group order. No two questions may ask the same thing.

Leave out a search (list it under "skipped") if:
- it is about a place outside {$city}, India: another country (for example "Surat Thani" is in Thailand) or another town or city (for example "Suratgarh" or "Surathkal" are not Surat). Areas, roads and sectors inside {$city}, or no place at all ("pet buy", "dog price"), are fine; or
- it is not about finding, visiting or buying from a {$service} or what it sells (for example a city map, a movie, an app, a medical test, general trivia).
If every search in a group is left out, write nothing for that group.

Rules:
- Write every question and answer in English, in the Latin alphabet, however the search itself was written. Some searches are typed in Hindi ("जोधपुर में डॉग कहां मिलते हैं") — answer those in English too, because the rest of the page is in English.
- The question is how a real person would ask it, in plain English, and mentions {$city}.
- The answer is 2-3 short sentences, under 60 words, in plain English.
- Answer from the business list above: name the businesses and areas that fit, their hours, what they offer. Use only names, areas, hours, ratings and services that appear in the list. Do not invent anything.
- Say a business offers something (cats, birds, fish, grooming, vet care...) only if it is in that business's "mentions". If the search asks for something, name the businesses whose "mentions" include it. If none do, say the listed shops don't mention it and suggest calling them to ask.
- If the search names an area, road or landmark where no business is listed, say plainly that none is listed there and name the areas where they are. Never guess distances. Never use the words "close", "nearby" or "near" about a different area.
- Never state a price, phone number, address or rating that is not in the list. Prices are not published on Service Bazzar: for price searches, say to call two or three of the shops (name them) and compare. For phone number searches, say the numbers are on each shop's page on Service Bazzar (name them).
- Every one of these businesses is listed on Service Bazzar, so name Service Bazzar in a natural way in most answers — for example that a shop is "listed on Service Bazzar", that its timings, ratings, reviews or contact details are "on its Service Bazzar page", or that a visitor can "compare the listed shops on Service Bazzar". Vary the wording from answer to answer; do not end every answer with the same Service Bazzar sentence, and do not force it where it would read awkwardly.
- When a sentence says what businesses offer, every business named in it must have every one of those things in its "mentions". If they differ, say it per business ("X offers grooming; Y sells pet food").
- Don't recommend or rank businesses ("recommended", "popular"): describe what is listed.
- Write in the third person. Never "I" or "we".
- For a "near me" search, name the areas where businesses are listed.
- No marketing words like "best", "top-notch", "premier", unless it is part of the search. No exclamation marks.
- Do not mention Justdial, Google or any other website.

Return JSON:
{"faqs": [{"group": 1, "question": "...", "answer": "..."}], "skipped": ["<a search, exactly as written above>"]}
PROMPT;
    }

    /**
     * One entry per group the AI answered, with the group's searches as
     * "keywords" (each shows as a chip linked to the answer), less any search
     * it listed as skipped (other place or off topic).
     */
    private function parse($data, array $groups): array
    {
        $skipped = array_flip(array_map(fn ($k) => mb_strtolower(trim((string) $k)), (array) ($data['skipped'] ?? [])));

        $out = [];
        $done = [];
        foreach ((array) ($data['faqs'] ?? []) as $f) {
            $i = (int) ($f['group'] ?? 0) - 1;
            $q = trim(preg_replace('/\s+/u', ' ', (string) ($f['question'] ?? '')));
            $a = trim(preg_replace('/\s+/u', ' ', (string) ($f['answer'] ?? '')));
            if (!isset($groups[$i]) || isset($done[$i]) || $q === '' || $a === '') {
                continue;
            }
            $kw = array_values(array_filter($groups[$i], fn ($k) => !isset($skipped[$k])));
            if (!$kw) {
                continue;
            }
            $done[$i] = true;
            $out[$i] = [
                'keyword'  => $kw[0],
                'keywords' => $kw,
                'question' => mb_substr($q, 0, 200),
                'answer'   => mb_substr($a, 0, 600),
            ];
        }

        ksort($out); // group order: most searched first

        return array_slice(array_values($out), 0, 15);
    }
}
