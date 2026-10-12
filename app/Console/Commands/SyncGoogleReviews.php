<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Support\GoogleReviews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncGoogleReviews extends Command
{
    /** Hard daily ceiling, regardless of the --limit passed in. */
    private const DAILY_CAP = 500;

    protected $signature = 'google:sync-reviews {--limit=500 : Max listings this run (capped at 500)}';

    protected $description = 'Refresh Google rating and top reviews for listings with a place id, at most once per 7 days';

    public function handle(): int
    {
        if (!GoogleReviews::apiKey()) {
            $this->error('GOOGLE_PLACES_API_KEY is not set.');
            return self::FAILURE;
        }

        // Daily counter shared across runs, so manual re-runs cannot exceed the cap.
        $dailyKey = 'google:sync-reviews:' . now()->toDateString();
        $usedToday = (int) Cache::get($dailyKey, 0);
        $remaining = self::DAILY_CAP - $usedToday;

        if ($remaining <= 0) {
            $this->info('Daily cap of ' . self::DAILY_CAP . ' already reached today. Nothing to do.');
            return self::SUCCESS;
        }

        $limit = min($remaining, max(1, (int) $this->option('limit')));

        $listings = Listing::query()
            ->whereNotNull('gmb_place_id')
            ->where(function ($q) {
                $q->whereNull('gmb_reviews_synced_at')
                  ->orWhere('gmb_reviews_synced_at', '<', now()->subDays(7));
            })
            ->orderBy('gmb_reviews_synced_at') // NULLs first, then oldest
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $updated = 0;
        $failed = 0;

        foreach ($listings as $listing) {
            // Count every attempt, including failures, since each one can cost an API call.
            Cache::put($dailyKey, (int) Cache::get($dailyKey, 0) + 1, now()->endOfDay());

            if (GoogleReviews::syncListing($listing)) {
                $updated++;
            } else {
                $failed++;
            }

            if (GoogleReviews::$stopRequested) {
                $this->error('Google returned a key or quota error. Stopping. Check the log.');
                break;
            }

            usleep(200000);
        }

        $this->info("Checked {$listings->count()} listings: updated {$updated}, failed {$failed}.");

        return self::SUCCESS;
    }
}
