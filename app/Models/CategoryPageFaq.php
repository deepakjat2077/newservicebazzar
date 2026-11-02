<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * "People also search for" Q&As for one city x category page. Written by
 * App\Services\CategoryFaqWriter (seo:pasf-faq), shown on the page only
 * after an admin approves it on Admin > SEO > People Also Search.
 */
class CategoryPageFaq extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'keywords'     => 'array',
        'faqs'         => 'array',
        'links'        => 'array',
        'generated_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /** Categories the feature is switched on for (a trial on Pet Shop first). */
    public static function enabledFor($categoryId): bool
    {
        return in_array((int) $categoryId, array_map('intval', (array) config('seo.pasf_faq_categories', [])), true);
    }

    /** The page path this row is shown on, e.g. "jaipur/pet-shop". */
    public function pagePath(): ?string
    {
        if (!$this->location || !$this->category) {
            return null;
        }

        return $this->location->slug . '/' . $this->category->slug;
    }

    /** Secret for the admin's "Preview on page" link, which shows a draft on the live page. */
    public function previewToken(): string
    {
        return substr(hash_hmac('sha256', 'pasf-preview|' . $this->id, (string) config('app.key')), 0, 20);
    }

    /** The live page with this row's Q&As shown even as a draft (a query string also skips the page cache). */
    public function previewUrl(): ?string
    {
        $path = $this->pagePath();

        return $path ? url('/' . $path) . '?pasf_preview=' . $this->previewToken() . '#pasf-1' : null;
    }

    /**
     * Have seo:google-index send the page to Google again on its next hourly
     * run (city category pages go first): it resends any URL whose last send
     * wasn't a 200.
     */
    public function requestReindex(): void
    {
        $path = $this->pagePath();
        if (!$path) {
            return;
        }

        \Illuminate\Support\Facades\DB::table('google_index_submissions')
            ->where('url', 'https://www.servicebazzar.com/' . $path)
            ->update(['http_status' => null, 'error' => 'Q&A changed: send again', 'updated_at' => now()]);
    }

    /** Drop the page's full-page cache entry so a change shows straight away. */
    public function forgetPageCache(): void
    {
        $path = $this->pagePath();
        if (!$path) {
            return;
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'www.servicebazzar.com';
        Cache::store(config('cache.public_pages.store'))->forget('page:' . sha1($host . '|' . $path));
    }
}
