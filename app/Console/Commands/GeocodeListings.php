<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Back-fills latitude/longitude for live listings that have none, so they can
 * appear on the map, in "nearby" results and in distance-sorted search.
 *
 * Uses OpenStreetMap's Nominatim (free). Their usage policy allows at most one
 * request per second and requires an identifying User-Agent, so this sleeps
 * between calls — deliberately slow. It is resumable: it only ever looks at
 * rows still missing coordinates, so it can be stopped and re-run safely.
 *
 *   php artisan listings:geocode --limit=50      # try a small batch first
 *   php artisan listings:geocode                 # process all remaining
 */
class GeocodeListings extends Command
{
    protected $signature = 'listings:geocode
        {--limit=0 : Max listings to process this run (0 = all remaining)}
        {--sleep=1100 : Milliseconds to wait between lookups (>=1000 per Nominatim policy)}';

    protected $description = 'Back-fill latitude/longitude for live listings missing coordinates (via Nominatim)';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $sleepMs = max(1000, (int) $this->option('sleep'));

        $query = DB::table('listings')
            ->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude')
                  ->orWhere('latitude', '')->orWhere('longitude', '');
            });

        $pending = (clone $query)->count();
        if ($pending === 0) {
            $this->info('Nothing to do — every live listing already has coordinates.');
            return self::SUCCESS;
        }

        $rows = $query->select('id', 'area', 'street_name', 'landmark', 'city', 'state', 'pincode')
            ->orderBy('id')
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->get();

        $this->info("Listings missing coordinates: {$pending}. Processing {$rows->count()} this run.");
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $done = $skipped = $failed = 0;

        foreach ($rows as $row) {
            $address = $this->buildAddress($row);

            if ($address === null) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $coords = $this->geocode($address);

            if ($coords) {
                DB::table('listings')->where('id', $row->id)->update([
                    'latitude'  => $coords['lat'],
                    'longitude' => $coords['lng'],
                ]);
                $done++;
            } else {
                $failed++;
            }

            $bar->advance();
            usleep($sleepMs * 1000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Updated: {$done}   Not found: {$failed}   Skipped (no address): {$skipped}");
        $remaining = (clone $query)->count();
        $this->info("Still missing coordinates: {$remaining}");

        return self::SUCCESS;
    }

    /** Assemble a geocodable address from whatever fields the row has. */
    private function buildAddress($row): ?string
    {
        $parts = array_filter([
            $row->area, $row->street_name, $row->landmark,
            $row->city, $row->state, $row->pincode,
        ], fn ($v) => filled($v));

        if (empty($parts)) {
            return null;
        }

        return implode(', ', $parts) . ', India';
    }

    /** One Nominatim lookup, India-scoped, matching the site's own geocoder. */
    private function geocode(string $address): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'ServiceBazzar/1.0 (' . config('app.url') . ')',
                'Accept'     => 'application/json',
            ])->timeout(15)->get('https://nominatim.openstreetmap.org/search', [
                'format'       => 'json',
                'limit'        => 1,
                'countrycodes' => 'in',
                'q'            => $address,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $result = $response->json();

            if (empty($result[0]['lat']) || empty($result[0]['lon'])) {
                return null;
            }

            return ['lat' => (string) $result[0]['lat'], 'lng' => (string) $result[0]['lon']];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
