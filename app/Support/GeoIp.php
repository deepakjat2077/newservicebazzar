<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * City / state / country for an IP address, and a street address for a GPS
 * point. geoplugin.net (used before) stopped answering free requests, which
 * is why every saved country had been coming back empty.
 *
 * Free, keyless services with a fallback, cached so one IP is looked up once.
 */
class GeoIp
{
    /** ['city','state','country','country_code','lat','lng'] or null. */
    public static function lookup(?string $ip): ?array
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        return Cache::remember('geoip2:' . $ip, now()->addDays(30), function () use ($ip) {
            try {
                $r = Http::timeout(4)->get('https://ipwho.is/' . $ip)->json();
                if (!empty($r['success'])) {
                    return [
                        'city' => $r['city'] ?? null, 'state' => $r['region'] ?? null,
                        'country' => $r['country'] ?? null, 'country_code' => $r['country_code'] ?? null,
                        'lat' => $r['latitude'] ?? null, 'lng' => $r['longitude'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
            }

            try {
                $r = Http::timeout(4)->get('http://ip-api.com/json/' . $ip, ['fields' => 'status,country,countryCode,regionName,city,lat,lon'])->json();
                if (($r['status'] ?? '') === 'success') {
                    return [
                        'city' => $r['city'] ?? null, 'state' => $r['regionName'] ?? null,
                        'country' => $r['country'] ?? null, 'country_code' => $r['countryCode'] ?? null,
                        'lat' => $r['lat'] ?? null, 'lng' => $r['lon'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::info('IP location lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            }

            return null;
        });
    }

    /** A readable address for a GPS point (OpenStreetMap data via Photon), or null. */
    public static function address(float $lat, float $lng): ?array
    {
        $key = 'revgeo2:' . round($lat, 4) . ',' . round($lng, 4);
        if (($hit = Cache::get($key)) !== null) {
            return $hit;
        }

        try {
            $f = Http::timeout(5)->get('https://photon.komoot.io/reverse', ['lat' => $lat, 'lon' => $lng])->json('features.0.properties');
        } catch (\Throwable $e) {
            $f = null;
        }
        if (!$f) {
            return null; // not cached, so a later login tries again
        }

        $city = preg_replace('/\s+Municipal Corporation$/i', '', (string) ($f['city'] ?? $f['county'] ?? ''));
        $line = collect([
            $f['name'] ?? null, $f['street'] ?? null, $f['district'] ?? null, $city ?: null,
            $f['state'] ?? null, $f['postcode'] ?? null, $f['country'] ?? null,
        ])->filter()->unique()->implode(', ');

        $out = ['address' => $line ?: null, 'city' => $city ?: null, 'state' => $f['state'] ?? null, 'country' => $f['country'] ?? null];
        Cache::put($key, $out, now()->addDays(30));

        return $out;
    }

    /** ['browser','os','device'] from a user agent string. */
    public static function agent(?string $ua): array
    {
        $ua = (string) $ua;
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Other',
        };
        $os = match (true) {
            (bool) preg_match('/Windows NT 10/', $ua) => 'Windows 10/11',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            (bool) preg_match('/iPhone|iPad|iPod/', $ua) => 'iOS',
            str_contains($ua, 'Mac OS X') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Other',
        };
        $device = preg_match('/iPad|Tablet/i', $ua) ? 'Tablet' : (preg_match('/Mobile|Android|iPhone/i', $ua) ? 'Mobile' : 'Desktop');

        return compact('browser', 'os', 'device');
    }
}
