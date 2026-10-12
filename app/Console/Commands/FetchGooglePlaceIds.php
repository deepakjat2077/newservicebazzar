<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Support\GoogleReviews;
use Illuminate\Console\Command;

class FetchGooglePlaceIds extends Command
{
    protected $signature = 'google:fetch-place-ids
                            {--limit=0 : Stop after this many listings (0 = no limit)}
                            {--chunk=50 : Rows per database chunk}';

    protected $description = 'Find and store the Google place id for listings that do not have one yet';

    public function handle(): int
    {
        if (!GoogleReviews::apiKey()) {
            $this->error('GOOGLE_PLACES_API_KEY is not set.');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $processed = 0;
        $found = 0;

        // Listings already attempted (synced_at set, even with no match) are skipped,
        // so a re-run does not spend API calls on the same misses.
        Listing::query()
            ->whereNull('gmb_place_id')
            ->whereNull('gmb_reviews_synced_at')
            ->chunkById((int) $this->option('chunk'), function ($listings) use (&$processed, &$found, $limit) {
                foreach ($listings as $listing) {
                    if ($limit && $processed >= $limit) {
                        return false;
                    }

                    $processed++;

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

        $this->info("Processed {$processed} listings, place ids found for {$found}.");

        return self::SUCCESS;
    }
}
