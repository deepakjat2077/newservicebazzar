<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Theme;
use App\Support\GoogleIndexing;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Hourly: send new or changed live listings and blog posts to the Google
 * Indexing API, newest first, within Google's 200-a-day quota. A URL is sent
 * again only when its page changed after the last send.
 */
class SubmitGoogleIndexing extends Command
{
    protected $signature = 'seo:google-index
                            {--dry-run : List what would be sent}
                            {--limit=0 : Max URLs this run (0 = what is left of today\'s quota)}
                            {--days=14 : Only look at pages created or changed in this many days}';

    protected $description = 'Notify the Google Indexing API about new or updated city category/subcategory pages, blog posts and listings';

    private const DAILY_QUOTA = 200;

    public function handle(): int
    {
        if (!GoogleIndexing::configured()) {
            $this->error('Key file ' . GoogleIndexing::KEY_FILE . ' is missing or unreadable.');
            $this->recordRun(null, 0, 0, 'Key file ' . GoogleIndexing::KEY_FILE . ' is missing or not readable by ' . (posix_getpwuid(posix_geteuid())['name'] ?? 'this user') . '.');
            return self::FAILURE;
        }

        $since = now()->subDays(max(1, (int) $this->option('days')));
        $sentToday = DB::table('google_index_submissions')
            ->where('notified_at', '>=', now()->startOfDay())
            ->where('http_status', 200)->count();
        $left = self::DAILY_QUOTA - $sentToday;
        $limit = (int) $this->option('limit') > 0 ? min((int) $this->option('limit'), $left) : $left;

        $candidates = $this->candidates($since);

        $known = DB::table('google_index_submissions')
            ->get(['url', 'content_updated_at', 'http_status'])->keyBy('url');

        $pending = array_values(array_filter($candidates, function ($c) use ($known) {
            $row = $known->get($c['url']);
            return !$row || (int) $row->http_status !== 200
                || ($c['changed_at'] && (!$row->content_updated_at || $c['changed_at']->gt(Carbon::parse($row->content_updated_at))));
        }));
        // The owner's priority: city category pages, then blog posts, then city
        // subcategory pages, listings last. Within a group, newest first
        // (listings by creation, since their updated_at moves for routine reasons).
        $rank = ['category' => 0, 'blog' => 1, 'subcategory' => 2, 'listing' => 3];
        usort($pending, fn ($a, $b) => [$rank[$a['source']], $b['created_at'], $b['changed_at']] <=> [$rank[$b['source']], $a['created_at'], $a['changed_at']]);

        $this->info(count($pending) . " URL(s) waiting, {$sentToday} sent today, sending up to " . max(0, $limit) . '.');

        if ($this->option('dry-run') || $limit <= 0) {
            foreach (array_slice($pending, 0, 10) as $c) {
                $this->line('  ' . $c['url']);
            }
            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;
        $lastError = null;
        foreach (array_slice($pending, 0, $limit) as $c) {
            try {
                [$status, $error] = GoogleIndexing::publish($c['url']);
            } catch (\Throwable $e) {
                [$status, $error] = [0, mb_substr($e->getMessage(), 0, 450)];
            }

            DB::table('google_index_submissions')->updateOrInsert(
                ['url' => $c['url']],
                [
                    'source' => $c['source'], 'source_id' => $c['id'],
                    'content_updated_at' => $c['changed_at'], 'notified_at' => now(),
                    'http_status' => $status, 'error' => $error,
                    'updated_at' => now(), 'created_at' => now(),
                ]
            );

            if ($status === 200) {
                $ok++;
            } else {
                $fail++;
                $lastError = "HTTP {$status}: {$error}";
                $this->warn("  HTTP {$status} {$c['url']} {$error}");
                if ($status === 429 || $status === 403) {
                    $this->error('Quota or permission problem from Google; stopping this run.');
                    break;
                }
            }
            usleep(250000);
        }

        $this->info("Sent {$ok}, failed {$fail}.");
        $this->recordRun(count($pending) - $ok, $ok, $fail, $lastError);
        return self::SUCCESS;
    }

    /** Last real run, for the admin Google Indexing page. */
    private function recordRun(?int $waiting, int $sent, int $failed, ?string $error): void
    {
        \Illuminate\Support\Facades\Cache::put(GoogleIndexing::LAST_RUN_KEY, [
            'at' => now()->toDateTimeString(), 'waiting' => $waiting,
            'sent' => $sent, 'failed' => $failed, 'error' => $error,
        ], now()->addDays(7));
    }

    /** City category/subcategory pages (from the sitemaps), plus live listings and blog posts changed since $since. */
    private function candidates(Carbon $since): array
    {
        $out = $this->sitemapCandidates();

        Listing::where('status', 1)
            ->where(fn ($q) => $q->where('created_at', '>=', $since)->orWhere('updated_at', '>=', $since))
            ->with('category:id,slug')
            ->select(['id', 'slug', 'category_id', 'created_at', 'updated_at'])
            ->chunkById(1000, function ($listings) use (&$out) {
                foreach ($listings as $l) {
                    if (empty($l->slug) || empty($l->category?->slug)) {
                        continue;
                    }
                    $out[] = [
                        'url' => route('listings.details', ['category' => $l->category->slug, 'slug' => $l->slug]),
                        'source' => 'listing', 'id' => $l->id,
                        'changed_at' => $l->updated_at ?? $l->created_at,
                        'created_at' => $l->created_at,
                    ];
                }
            });

        foreach (Theme::where('name', 'blog')->where('is_multiple', 1)->get(['id', 'content', 'created_at']) as $b) {
            $slug = $b->content->slug ?? null;
            if (!$slug) {
                continue;
            }
            $changed = !empty($b->content->content_updated_at) ? Carbon::parse($b->content->content_updated_at) : $b->created_at;
            if ($changed && $changed->lt($since)) {
                continue;
            }
            $out[] = ['url' => route('blog.details', $slug), 'source' => 'blog', 'id' => $b->id, 'changed_at' => $changed, 'created_at' => $b->created_at];
        }

        return $out;
    }

    /**
     * Every /{city}/{category-or-subcategory} URL the city sitemaps list (only
     * pages that have listings), with its <lastmod>, so a page goes again once
     * it changes. Read through the local web server and cached for 6 hours.
     */
    private function sitemapCandidates(): array
    {
        $rows = \Illuminate\Support\Facades\Cache::remember('google-index:sitemap-urls', now()->addHours(6), function () {
            $get = fn (string $path) => (string) \Illuminate\Support\Facades\Http::timeout(60)
                ->withHeaders(['Host' => 'www.servicebazzar.com', 'X-Forwarded-Proto' => 'https'])
                ->get('http://127.0.0.1' . $path)->body();

            preg_match_all('#<loc>https://www\.servicebazzar\.com(/sitemap-city-[^<]+)</loc>#', $get('/sitemap.xml'), $maps);
            $categorySlugs = \App\Models\Category::where('status', 1)->pluck('slug')->flip();

            $rows = [];
            foreach ($maps[1] as $path) {
                preg_match_all('#<loc>(https://www\.servicebazzar\.com/[^/<]+/([^/<]+))</loc>\s*<lastmod>([^<]+)</lastmod>#', $get($path), $m, PREG_SET_ORDER);
                foreach ($m as $r) {
                    $rows[] = [$r[1], isset($categorySlugs[$r[2]]) ? 'category' : 'subcategory', $r[3]];
                }
            }

            return $rows;
        });

        return array_map(function ($r) {
            $changed = Carbon::parse($r[2]);
            return ['url' => $r[0], 'source' => $r[1], 'id' => null, 'changed_at' => $changed, 'created_at' => $changed];
        }, $rows);
    }
}
