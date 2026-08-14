<?php

namespace App\Jobs\Tracking;

use App\Models\PageView;
use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Support\Tracking\UserAgentParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordPageViewOpen implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
        // See config/queue.php: "sync" by default (no worker process is
        // guaranteed to be running), overridable via .env once one is.
        $this->connection = config('queue.tracking_connection', 'sync');
    }

    public function handle()
    {
        $d = $this->data;
        $ua = UserAgentParser::parse($d['user_agent'] ?? null);
        $now = now();

        $visitor = Visitor::firstOrNew(['visitor_id' => $d['visitor_id']]);
        $visitorIsNew = !$visitor->exists;
        $ipChanged = $visitor->exists && $visitor->ip_address !== ($d['ip_address'] ?? null);

        $visitor->fill([
            'user_id' => $d['user_id'] ?? $visitor->user_id,
            'ip_address' => $d['ip_address'] ?? $visitor->ip_address,
            'device_type' => $ua['device_type'],
            'browser' => $ua['browser'],
            'os' => $ua['os'],
            'last_visit_at' => $now,
        ]);

        if ($visitorIsNew) {
            $visitor->first_visit_at = $now;
        }

        $visitor->save();

        $session = VisitorSession::where('session_uuid', $d['session_uuid'])->first();
        $sessionIsNew = !$session;

        if ($sessionIsNew) {
            $session = new VisitorSession(['session_uuid' => $d['session_uuid']]);
            $session->visitor_id = $d['visitor_id'];
            $session->laravel_session_id = $d['laravel_session_id'] ?? null;
            $session->ip_address = $d['ip_address'] ?? null;
            $session->referrer_url = $d['referrer_url'] ?? null;
            $session->landing_page = $d['url'] ?? null;
            $session->utm_source = $d['utm_source'] ?? null;
            $session->utm_medium = $d['utm_medium'] ?? null;
            $session->utm_campaign = $d['utm_campaign'] ?? null;
            $session->utm_term = $d['utm_term'] ?? null;
            $session->utm_content = $d['utm_content'] ?? null;
            $session->device_type = $ua['device_type'];
            $session->browser = $ua['browser'];
            $session->os = $ua['os'];
            $session->screen_resolution = $d['screen_resolution'] ?? null;
            $session->started_at = $now;
            $session->page_view_count = 0;

            $visitor->increment('total_visits');
        }

        // A logged-in user_id backfills onto sessions/visitors that started
        // as anonymous (e.g. the visitor logged in partway through).
        if (!empty($d['user_id'])) {
            $session->user_id = $d['user_id'];
        }

        $session->save();
        $session->increment('page_view_count');

        PageView::firstOrCreate(
            ['client_page_view_id' => $d['client_page_view_id']],
            [
                'visitor_id' => $d['visitor_id'],
                'session_uuid' => $d['session_uuid'],
                'user_id' => $d['user_id'] ?? null,
                'listing_id' => $d['listing_id'] ?? null,
                'url' => $d['url'] ?? '',
                'page_name' => $d['page_name'] ?? null,
                'opened_at' => $now,
            ]
        );

        if ($visitorIsNew || $ipChanged) {
            ResolveVisitorGeoAndDevice::dispatch([
                'visitor_id' => $d['visitor_id'],
                'session_uuid' => $d['session_uuid'],
                'ip_address' => $d['ip_address'] ?? null,
                'user_agent' => $d['user_agent'] ?? null,
            ]);
        }
    }
}
