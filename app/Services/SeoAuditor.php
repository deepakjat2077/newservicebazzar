<?php

namespace App\Services;

/**
 * On-page SEO rule engine.
 *
 * Every check here works off data we already store, so an audit is a pure
 * in-memory pass over the rows — no page fetching, no external calls.
 *
 * Each check returns issues shaped as:
 *   ['code' => 'meta_title_short', 'severity' => 'warning', 'detail' => '18 chars']
 *
 * The human label lives in issueLabels(), not on each issue — the audit
 * result gets cached, and repeating the same sentence a few thousand times
 * bloats that payload for no gain.
 */
class SeoAuditor
{
    /** Google truncates titles past ~60 chars and descriptions past ~160. */
    public const TITLE_MIN = 30;
    public const TITLE_MAX = 60;
    public const DESC_MIN  = 80;
    public const DESC_MAX  = 160;

    public const KEYWORDS_MIN = 3;
    public const KEYWORDS_MAX = 15;

    /** Below this many characters of body text a page counts as thin. */
    public const CONTENT_MIN = 150;

    private const SEVERITY_WEIGHT = [
        'error'   => 12,
        'warning' => 5,
    ];

    /**
     * Human labels for every issue code the audit can produce. Drives the
     * "issue breakdown" filter list in the UI.
     */
    public static function issueLabels(): array
    {
        return [
            'meta_title_missing'    => 'Meta title missing',
            'meta_title_short'      => 'Meta title too short (under ' . self::TITLE_MIN . ' chars)',
            'meta_title_long'       => 'Meta title too long (over ' . self::TITLE_MAX . ' chars)',
            'meta_title_duplicate'  => 'Meta title duplicated on another page',
            'meta_desc_missing'     => 'Meta description missing',
            'meta_desc_short'       => 'Meta description too short (under ' . self::DESC_MIN . ' chars)',
            'meta_desc_long'        => 'Meta description too long (over ' . self::DESC_MAX . ' chars)',
            'meta_desc_duplicate'   => 'Meta description duplicated on another page',
            'keywords_missing'      => 'Meta keywords missing',
            'keywords_few'          => 'Too few meta keywords (under ' . self::KEYWORDS_MIN . ')',
            'keywords_many'         => 'Too many meta keywords (over ' . self::KEYWORDS_MAX . ')',
            'slug_missing'          => 'URL slug missing',
            'slug_duplicate'        => 'URL slug duplicated — two pages fight for one URL',
            'slug_invalid'          => 'URL slug has spaces, capitals or special characters',
            'slug_long'             => 'URL slug too long (over 75 chars)',
            'slug_collision'        => 'Slug clashes with a category slug — ambiguous URL',
            'content_missing'       => 'Page content missing',
            'content_thin'          => 'Thin content (under ' . self::CONTENT_MIN . ' chars)',
            'name_not_in_title'     => 'Business/page name not in meta title',
            'city_not_in_title'     => 'City not in meta title or description — weak local SEO',
            'image_missing'         => 'Main image missing',
            'no_faq'                => 'No FAQ added — misses FAQ rich results',
            'category_missing'      => 'No category assigned',
        ];
    }

    /**
     * Compact labels for the table badges. The full sentence from
     * issueLabels() blows the issues column past the width of its card, so
     * rows carry these and keep the long form as a tooltip.
     */
    public static function issueShortLabels(): array
    {
        return [
            'meta_title_missing'   => 'Title missing',
            'meta_title_short'     => 'Title short',
            'meta_title_long'      => 'Title long',
            'meta_title_duplicate' => 'Title duplicate',
            'meta_desc_missing'    => 'Description missing',
            'meta_desc_short'      => 'Description short',
            'meta_desc_long'       => 'Description long',
            'meta_desc_duplicate'  => 'Description duplicate',
            'keywords_missing'     => 'Keywords missing',
            'keywords_few'         => 'Few keywords',
            'keywords_many'        => 'Too many keywords',
            'slug_missing'         => 'Slug missing',
            'slug_duplicate'       => 'Slug duplicate',
            'slug_invalid'         => 'Slug invalid',
            'slug_long'            => 'Slug long',
            'slug_collision'       => 'Slug clash',
            'content_missing'      => 'No content',
            'content_thin'         => 'Thin content',
            'name_not_in_title'    => 'Name not in title',
            'city_not_in_title'    => 'City not in title',
            'image_missing'        => 'No image',
            'no_faq'               => 'No FAQ',
            'category_missing'     => 'No category',
        ];
    }

    public static function severityOf(string $code): string
    {
        $errors = [
            'meta_title_missing',
            'meta_desc_missing',
            'slug_missing',
            'slug_duplicate',
            'slug_collision',
            'meta_title_duplicate',
            'content_missing',
            'category_missing',
        ];

        return in_array($code, $errors, true) ? 'error' : 'warning';
    }

    /**
     * 100 minus a weighted penalty per issue. Floors at 0 — a page with ten
     * problems is not usefully "more broken" than one with eight.
     */
    public static function scoreFor(array $issues): int
    {
        $penalty = 0;

        foreach ($issues as $issue) {
            $penalty += self::SEVERITY_WEIGHT[$issue['severity']] ?? 5;
        }

        return max(0, 100 - $penalty);
    }

    /**
     * @param object $listing        Row with the listing's SEO columns.
     * @param array  $duplicateMaps  ['meta_title' => [...], 'meta_description' => [...], 'slug' => [...]]
     *                               of normalised value => count, built once for the whole table.
     * @param array  $categorySlugs  All category slugs, for collision checks.
     */
    public function auditListing($listing, array $duplicateMaps, array $categorySlugs = []): array
    {
        $issues = [];

        $this->checkTitle($listing->meta_title ?? '', $issues, $duplicateMaps['meta_title'] ?? []);
        $this->checkDescription($listing->meta_description ?? '', $issues, $duplicateMaps['meta_description'] ?? []);
        $this->checkKeywords($listing->meta_keywords ?? '', $issues);
        $this->checkSlug($listing->slug ?? '', $issues, $duplicateMaps['slug'] ?? [], $categorySlugs);
        $this->checkContent($listing->details ?? '', $issues);

        $name  = trim((string) ($listing->name ?? ''));
        $title = trim((string) ($listing->meta_title ?? ''));
        $desc  = trim((string) ($listing->meta_description ?? ''));
        $city  = trim((string) ($listing->city ?? ''));

        if ($name !== '' && $title !== '' && !$this->containsPhrase($title, $name)) {
            $issues[] = $this->issue('name_not_in_title');
        }

        if ($city !== '' && !$this->containsPhrase($title . ' ' . $desc, $city)) {
            $issues[] = $this->issue('city_not_in_title');
        }

        if (empty($listing->thumb)) {
            $issues[] = $this->issue('image_missing');
        }

        if ($this->isEmptyJsonish($listing->faq ?? null)) {
            $issues[] = $this->issue('no_faq');
        }

        if (empty($listing->category_id)) {
            $issues[] = $this->issue('category_missing');
        }

        return $issues;
    }

    /**
     * Categories and subcategories share a column layout (seo_title,
     * seo_description, seo_keywords, description, slug), so one method
     * covers both.
     */
    public function auditTaxonomy($row, array $duplicateMaps, bool $checkImage = true): array
    {
        $issues = [];

        $this->checkTitle($row->seo_title ?? '', $issues, $duplicateMaps['seo_title'] ?? []);
        $this->checkDescription($row->seo_description ?? '', $issues, $duplicateMaps['seo_description'] ?? []);
        $this->checkKeywords($row->seo_keywords ?? '', $issues);
        $this->checkSlug($row->slug ?? '', $issues, $duplicateMaps['slug'] ?? []);
        $this->checkContent($row->description ?? '', $issues);

        $name  = trim((string) ($row->name ?? ''));
        $title = trim((string) ($row->seo_title ?? ''));

        if ($name !== '' && $title !== '' && !$this->containsPhrase($title, $name)) {
            $issues[] = $this->issue('name_not_in_title');
        }

        // Subcategories don't carry their own photo in the UI (they show
        // the parent category's), so flagging 8,500+ of them for "no image"
        // was noise — only categories are checked here.
        if ($checkImage && empty($row->image)) {
            $issues[] = $this->issue('image_missing');
        }

        return $issues;
    }

    /**
     * Locations only have a name and a slug, and that slug sits in the
     * `{location}/{slug}` route — so a clash with a category slug makes the
     * URL ambiguous. That is the check that matters here.
     */
    public function auditLocation($row, array $duplicateMaps, array $categorySlugs = []): array
    {
        $issues = [];

        $this->checkSlug($row->slug ?? '', $issues, $duplicateMaps['slug'] ?? [], $categorySlugs);

        if (empty($row->image)) {
            $issues[] = $this->issue('image_missing');
        }

        return $issues;
    }

    private function checkTitle(?string $title, array &$issues, array $duplicateMap = []): void
    {
        $title = trim((string) $title);

        if ($title === '') {
            $issues[] = $this->issue('meta_title_missing');
            return;
        }

        $length = mb_strlen($title);

        if ($length < self::TITLE_MIN) {
            $issues[] = $this->issue('meta_title_short', $length . ' chars');
        } elseif ($length > self::TITLE_MAX) {
            $issues[] = $this->issue('meta_title_long', $length . ' chars');
        }

        if (($duplicateMap[$this->normalise($title)] ?? 0) > 1) {
            $issues[] = $this->issue('meta_title_duplicate');
        }
    }

    private function checkDescription(?string $description, array &$issues, array $duplicateMap = []): void
    {
        $description = trim((string) $description);

        if ($description === '') {
            $issues[] = $this->issue('meta_desc_missing');
            return;
        }

        $length = mb_strlen($description);

        if ($length < self::DESC_MIN) {
            $issues[] = $this->issue('meta_desc_short', $length . ' chars');
        } elseif ($length > self::DESC_MAX) {
            $issues[] = $this->issue('meta_desc_long', $length . ' chars');
        }

        if (($duplicateMap[$this->normalise($description)] ?? 0) > 1) {
            $issues[] = $this->issue('meta_desc_duplicate');
        }
    }

    private function checkKeywords(?string $keywords, array &$issues): void
    {
        $keywords = trim((string) $keywords);

        if ($keywords === '') {
            $issues[] = $this->issue('keywords_missing');
            return;
        }

        $count = count(array_filter(array_map('trim', explode(',', $keywords))));

        if ($count < self::KEYWORDS_MIN) {
            $issues[] = $this->issue('keywords_few', $count . ' keywords');
        } elseif ($count > self::KEYWORDS_MAX) {
            $issues[] = $this->issue('keywords_many', $count . ' keywords');
        }
    }

    private function checkSlug(?string $slug, array &$issues, array $duplicateMap = [], array $categorySlugs = []): void
    {
        $slug = trim((string) $slug);

        if ($slug === '') {
            $issues[] = $this->issue('slug_missing');
            return;
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $issues[] = $this->issue('slug_invalid', $slug);
        }

        if (mb_strlen($slug) > 75) {
            $issues[] = $this->issue('slug_long', mb_strlen($slug) . ' chars');
        }

        if (($duplicateMap[$this->normalise($slug)] ?? 0) > 1) {
            $issues[] = $this->issue('slug_duplicate', $slug);
        }

        if ($categorySlugs !== [] && in_array($slug, $categorySlugs, true)) {
            $issues[] = $this->issue('slug_collision', $slug);
        }
    }

    private function checkContent(?string $content, array &$issues): void
    {
        $plain = trim(html_entity_decode(strip_tags((string) $content)));

        if ($plain === '') {
            $issues[] = $this->issue('content_missing');
            return;
        }

        if (mb_strlen($plain) < self::CONTENT_MIN) {
            $issues[] = $this->issue('content_thin', mb_strlen($plain) . ' chars');
        }
    }

    private function issue(string $code, ?string $detail = null): array
    {
        return [
            'code'     => $code,
            'severity' => self::severityOf($code),
            'detail'   => $detail,
        ];
    }

    private function normalise(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function containsPhrase(string $haystack, string $needle): bool
    {
        return str_contains($this->normalise($haystack), $this->normalise($needle));
    }

    /**
     * `faq` is stored as JSON; an empty array, "[]", "null" and "" all mean
     * "no FAQ".
     */
    private function isEmptyJsonish($value): bool
    {
        if (empty($value)) {
            return true;
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        $decoded = json_decode((string) $value, true);

        return empty($decoded);
    }
}
