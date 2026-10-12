<?php

namespace App\Support;

use App\Models\AdminLoginLog;
use Illuminate\Support\Facades\Log;

/**
 * Writes admin_login_logs rows. The row is saved at once; the location
 * (GPS from the login form if the admin allowed it, else the IP's city) is
 * looked up after the response has gone, so signing in never waits on it.
 */
class AdminLoginRecorder
{
    public static function record(string $event, ?int $adminId, ?string $username): void
    {
        try {
            $req = request();
            $ua  = substr((string) $req->userAgent(), 0, 500);
            $lat = $req->input('geo_lat');
            $lng = $req->input('geo_lng');
            $hasGps = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;

            $log = AdminLoginLog::create(array_merge([
                'admin_id'   => $adminId,
                'username'   => $username ? substr($username, 0, 191) : null,
                'event'      => $event,
                'ip'         => $req->ip(),
                'user_agent' => $ua,
                'latitude'   => $hasGps ? (float) $lat : null,
                'longitude'  => $hasGps ? (float) $lng : null,
                'accuracy_m' => $hasGps && is_numeric($req->input('geo_acc')) ? (int) $req->input('geo_acc') : null,
                'location_source' => $hasGps ? 'gps' : 'ip',
            ], GeoIp::agent($ua)));

            app()->terminating(function () use ($log, $hasGps) {
                try {
                    $ip = GeoIp::lookup($log->ip);
                    $data = [
                        'city' => $ip['city'] ?? null, 'state' => $ip['state'] ?? null, 'country' => $ip['country'] ?? null,
                    ];
                    if ($hasGps) {
                        $addr = GeoIp::address((float) $log->latitude, (float) $log->longitude);
                        if ($addr) {
                            $data = array_merge($data, array_filter([
                                'city' => $addr['city'], 'state' => $addr['state'], 'country' => $addr['country'],
                            ]), ['address' => substr((string) $addr['address'], 0, 500)]);
                        }
                    } elseif ($ip) {
                        $data['latitude'] = $ip['lat'];
                        $data['longitude'] = $ip['lng'];
                    }
                    $log->update($data);
                } catch (\Throwable $e) {
                    Log::info('Admin login location lookup failed', ['id' => $log->id, 'error' => $e->getMessage()]);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Admin login log failed', ['error' => $e->getMessage()]);
        }
    }
}
