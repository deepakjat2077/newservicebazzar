<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Coordinates -> "Maharana Pratap Chauraha, Jhotwara, Jaipur, Rajasthan".
 *
 * Uses Photon (komoot's free OpenStreetMap geocoder). nominatim.openstreetmap.org
 * answered this server with 429, and the Google key is restricted to Places.
 * Results are cached per ~100 m cell for 30 days, so a rep working one area
 * costs one lookup.
 */
class ReverseGeocoder
{
    public static function place(float $lat, float $lng): ?string
    {
        $key = 'revgeo:' . round($lat, 3) . ',' . round($lng, 3);

        return Cache::remember($key, now()->addDays(30), function () use ($lat, $lng) {
            try {
                $res = Http::timeout(6)
                    ->withHeaders(['User-Agent' => 'ServiceBazzar/1.0 (+https://www.servicebazzar.com)'])
                    ->get('https://photon.komoot.io/reverse', ['lat' => $lat, 'lon' => $lng, 'lang' => 'en']);
            } catch (\Throwable $e) {
                return null;
            }

            $p = $res->successful() ? ($res->json('features.0.properties') ?? null) : null;
            if (!$p) {
                return null;
            }

            $parts = [
                $p['name'] ?? null,
                $p['street'] ?? null,
                $p['district'] ?? null,
                $p['locality'] ?? null,
                isset($p['city']) ? trim(preg_replace('/\s+(Municipal Corporation|Nagar Nigam)$/i', '', $p['city'])) : null,
                $p['state'] ?? null,
            ];

            $seen = [];
            $out = [];
            foreach ($parts as $part) {
                $part = trim((string) $part);
                $k = mb_strtolower($part);
                if ($part === '' || isset($seen[$k])) {
                    continue;
                }
                $seen[$k] = true;
                $out[] = $part;
            }

            return $out ? mb_substr(implode(', ', $out), 0, 250) : null;
        });
    }
}
