<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Listing;
use App\Support\GoogleIndexing;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Push a priority set of listings (by default the Jaipur "Mehndi Artist"
 * category) to the Google Indexing API AHEAD of the general hourly run, so they
 * claim the day's quota first the moment it resets. Idempotent: a listing is
 * skipped once it already has a successful (200) submission that is newer than
 * its last content change, so once the set is indexed this command does nothing.
 * Scheduled at minute 50 — just before the top-of-hour seo:google-index run.
 */
class SubmitGoogleIndexingPriority extends Command
{
    protected $signature = 'seo:google-index-priority
                            {--category=mehndi-artist : Category slug to prioritise}
                            {--city=Jaipur : City name to prioritise}
                            {--limit=0 : Max URLs this run (0 = what is left of today\'s quota)}';

    protected $description = 'Send a priority category/city set of listings to the Google Indexing API first';

    private const DAILY_QUOTA = 200;

    public function handle(): int
    {
        if (!GoogleIndexing::configured()) {
            $this->error('Key file ' . GoogleIndexing::KEY_FILE . ' is missing or unreadable.');
            return self::FAILURE;
        }

        $categorySlug = (string) $this->option('category');
        $city         = (string) $this->option('city');

        $cat = Category::where('slug', $categorySlug)->first();
        if (!$cat) {
            $this->error("Category '{$categorySlug}' not found.");
            return self::FAILURE;
        }

        $sentToday = DB::table('google_index_submissions')
            ->where('notified_at', '>=', now()->startOfDay())
            ->where('http_status', 200)->count();
        $left  = self::DAILY_QUOTA - $sentToday;
        $limit = (int) $this->option('limit') > 0 ? min((int) $this->option('limit'), $left) : $left;

        if ($limit <= 0) {
            $this->info("No quota left today ({$sentToday}/" . self::DAILY_QUOTA . ').');
            return self::SUCCESS;
        }

        $known = DB::table('google_index_submissions')
            ->get(['url', 'content_updated_at', 'http_status'])->keyBy('url');

        $listings = Listing::where('status', 1)->where('category_id', $cat->id)
            ->where(function ($q) use ($city) {
                $q->where('city', $city)->orWhereHas('location', fn ($l) => $l->where('name', $city));
            })
            ->with('category:id,slug')
            ->whereNotNull('slug')->where('slug', '!=', '')
            ->get(['id', 'slug', 'category_id', 'created_at', 'updated_at']);

        $ok = 0; $fail = 0; $sent = 0; $skipped = 0;
        foreach ($listings as $l) {
            if ($sent >= $limit) { $this->info('Quota reached for this run; stopping.'); break; }
            if (empty($l->category?->slug)) { continue; }

            $url     = route('listings.details', ['category' => $l->category->slug, 'slug' => $l->slug]);
            $changed = $l->updated_at ?? $l->created_at;

            // Already indexed for the current version — don't waste quota.
            $row = $known->get($url);
            if ($row && (int) $row->http_status === 200 && $row->content_updated_at
                && $changed && !$changed->gt(Carbon::parse($row->content_updated_at))) {
                $skipped++;
                continue;
            }

            try {
                [$status, $error] = GoogleIndexing::publish($url);
            } catch (\Throwable $e) {
                [$status, $error] = [0, mb_substr($e->getMessage(), 0, 450)];
            }

            DB::table('google_index_submissions')->updateOrInsert(
                ['url' => $url],
                ['source' => 'listing', 'source_id' => $l->id,
                 'content_updated_at' => $changed, 'notified_at' => now(),
                 'http_status' => $status, 'error' => $error,
                 'updated_at' => now(), 'created_at' => now()]
            );

            $sent++;
            if ($status === 200) {
                $ok++;
            } else {
                $fail++;
                $this->warn("HTTP {$status} {$url} {$error}");
                if ($status === 429 || $status === 403) {
                    $this->error('Quota or permission problem from Google; stopping this run.');
                    break;
                }
            }
            usleep(250000);
        }

        $this->info("Priority {$categorySlug}/{$city}: sent OK={$ok}, failed={$fail}, skipped up-to-date={$skipped}.");
        return self::SUCCESS;
    }
}
