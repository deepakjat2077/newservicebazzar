<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Support\GoogleReviews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchGooglePlaceIds extends Command
{
    protected $signature = 'google:fetch-place-ids
                            {--limit=0 : Stop after this many listings (0 = no limit)}
                            {--chunk=50 : Rows per database chunk}
                            {--monthly-cap= : Stop once this many calls have been made this calendar month (default: config)}';

    protected $description = 'Find and store the Google place id for listings that do not have one yet';

    public function handle(): int
    {
        if (!GoogleReviews::apiKey()) {
            $this->error('GOOGLE_PLACES_API_KEY is not set.');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        /*
         * Each lookup is a billed Places "Text Search Pro" call, and Google
         * gives 5,000 of them free per calendar month. The counter below is
         * keyed by month, so it resets on the 1st on its own and the job can
         * be left running at any hourly rate without ever spilling past that
         * month's budget. listing.place_id_monthly_cap sets the budget per
         * calendar month, so a month that may spend credits and a month that
         * must stay free need no code change between them.
         */
        $month = now()->format('Y-m');
        $caps = config('listing.place_id_monthly_cap', 5000);
        $monthlyCap = (int) ($this->option('monthly-cap') ?? 0)
            ?: (int) (is_array($caps) ? ($caps[$month] ?? $caps['default'] ?? 5000) : $caps);
        $monthKey = 'google:place-ids:' . $month;

        /*
         * Deliberately the file store, not the default Redis one. The whole
         * default cache gets flushed by ContentHtml::flushPublicPageCache(),
         * which already reset this counter from 716 back to 25 once — and a
         * counter that resets is no budget guard at all. Only app-1 runs the
         * scheduler, so a file on that box is consistent.
         */
        $counter = Cache::store('file');
        $usedThisMonth = (int) $counter->get($monthKey, 0);

        if ($monthlyCap > 0 && $usedThisMonth >= $monthlyCap) {
            $this->info("Monthly cap of {$monthlyCap} already used ({$usedThisMonth}). Nothing to do.");
            return self::SUCCESS;
        }

        $monthlyLeft = $monthlyCap > 0 ? $monthlyCap - $usedThisMonth : PHP_INT_MAX;
        $limit = $limit > 0 ? min($limit, $monthlyLeft) : $monthlyLeft;

        $processed = 0;
        $found = 0;

        // Listings already attempted (synced_at set, even with no match) are skipped,
        // so a re-run does not spend API calls on the same misses.
        Listing::query()
            ->whereNull('gmb_place_id')
            ->whereNull('gmb_reviews_synced_at')
            ->chunkById((int) $this->option('chunk'), function ($listings) use (&$processed, &$found, $limit, $monthKey, $counter) {
                foreach ($listings as $listing) {
                    if ($limit && $processed >= $limit) {
                        return false;
                    }

                    $processed++;
                    // Counted before the call, so a crash cannot undercount and overspend.
                    $counter->put($monthKey, (int) $counter->get($monthKey, 0) + 1, now()->endOfMonth()->addDay());

                    // Place id only. Review details are fetched by google:sync-reviews under its daily cap.
                    $placeId = GoogleReviews::findPlaceId($listing);

                    if ($placeId) {
                        $listing->forceFill(['gmb_place_id' => $placeId])->saveQuietly();
                        $found++;
                    } else {
                        $listing->forceFill(['gmb_reviews_synced_at' => now()])->saveQuietly();
                    }

                    if (GoogleReviews::$stopRequested) {
                        $this->error('Google returned a key or quota error. Stopping. Check the log.');
                        return false;
                    }

                    usleep(200000); // 0.2s between calls
                }
            });

        $used = (int) $counter->get($monthKey, 0);
        $this->info("Processed {$processed} listings, place ids found for {$found}. This month: {$used}" . ($monthlyCap > 0 ? " of {$monthlyCap}" : ''));

        return self::SUCCESS;
    }
}
