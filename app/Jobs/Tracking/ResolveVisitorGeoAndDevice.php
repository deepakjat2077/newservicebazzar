<?php

namespace App\Jobs\Tracking;

use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The only job in the tracking system that makes an external HTTP call.
 * Always runs off the request path (queued), and the result is cached per
 * IP for 30 days so repeat visitors never trigger a second lookup.
 *
 * Reuses the same free, unauthenticated geoplugin.net service already used
 * elsewhere in this codebase (App\Http\Libs\Booster::userInfo()) rather
 * than adding a new package/dependency for IP geolocation.
 */
class ResolveVisitorGeoAndDevice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // a failed/empty geo lookup won't be fixed by retrying

    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
        // Also sync by default (see config/queue.php) for consistency with
        // the rest of the tracking pipeline. This is the one job making an
        // external call, but it's still off the user-visible page load —
        // it only runs inside the fire-and-forget beacon request, not the
        // page render itself — so a slightly slower beacon response has no
        // perceived cost. Switch TRACKING_QUEUE_CONNECTION to "database"
        // once a real worker is running to move it fully off-request again.
        $this->connection = config('queue.tracking_connection', 'sync');
    }

    public function handle()
    {
        $ip = $this->data['ip_address'] ?? null;

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }

        $geo = $this->resolveGeo($ip);

        if (!$geo) {
            return;
        }

        Visitor::where('visitor_id', $this->data['visitor_id'])->update([
            'country' => $geo['country'],
            'state' => $geo['state'],
            'city' => $geo['city'],
        ]);

        if (!empty($this->data['session_uuid'])) {
            VisitorSession::where('session_uuid', $this->data['session_uuid'])->update([
                'country' => $geo['country'],
                'state' => $geo['state'],
                'city' => $geo['city'],
            ]);
        }
    }

    protected function resolveGeo(string $ip): ?array
    {
        return Cache::remember('geoip:' . $ip, now()->addDays(30), function () use ($ip) {
            try {
                $response = Http::timeout(5)->get('http://www.geoplugin.net/json.gp', ['ip' => $ip]);

                if (!$response->successful()) {
                    return null;
                }

                $body = $response->json();

                return [
                    'country' => $body['geoplugin_countryName'] ?? null,
                    'state' => $body['geoplugin_region'] ?? null,
                    'city' => $body['geoplugin_city'] ?? null,
                ];
            } catch (\Throwable $e) {
                // Never let a flaky free geo provider fail a queue worker
                // or pollute failed_jobs with something a retry can't fix.
                Log::warning('Tracking geo lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }
}
