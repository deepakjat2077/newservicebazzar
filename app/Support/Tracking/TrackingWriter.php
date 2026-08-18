<?php

namespace App\Support\Tracking;

use App\Jobs\Tracking\ResolveVisitorGeoAndDevice;
use App\Models\AnalyticsEvent;
use App\Models\PageView;
use App\Models\Visitor;
use App\Models\VisitorSession;

/**
 * The database half of the tracking pipeline, split out of the jobs so
 * that a beacon released from PendingTraffic can be written by exactly the
 * code that would have written it at the time — the jobs decide whether a
 * beacon is written now, held, or released; this decides what a write is.
 */
class TrackingWriter
{
    /** Replay beacons handed back by PendingTraffic::release(). */
    public static function writeHeld(array $held): void
    {
        foreach ($held as $item) {
            switch ($item['type'] ?? null) {
                case PendingTraffic::TYPE_PAGE_VIEW_OPEN:
                    static::pageViewOpen($item['data']);
                    break;
                case PendingTraffic::TYPE_PAGE_VIEW_CLOSE:
                    static::pageViewClose($item['data']);
                    break;
                case PendingTraffic::TYPE_EVENT:
                    static::event($item['data']);
                    break;
            }
        }
    }

    public static function pageViewOpen(array $d): void
    {
        $ua = UserAgentParser::parse($d['user_agent'] ?? null);
        $isBot = TrafficFilter::isBotPayload($d);
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
            'is_bot' => $isBot,
            'user_agent' => $d['user_agent'] ?? null,
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
            $session->is_bot = $isBot;
            $session->user_agent = $d['user_agent'] ?? null;
            $session->screen_resolution = $d['screen_resolution'] ?? null;
            // The beacon's own timestamp where it was held: a released
            // page view must date from when it happened, not from the
            // close beacon that let it through, or a session's whole
            // history collapses onto the moment it was proven human.
            $session->started_at = static::occurredAt($d, $now);
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
                'opened_at' => static::occurredAt($d, $now),
                'is_bot' => $isBot,
                'user_agent' => $d['user_agent'] ?? null,
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

    public static function pageViewClose(array $d): void
    {
        $isBot = TrafficFilter::isBotPayload($d);
        $now = static::occurredAt($d, now());

        // firstOrNew, not updateOrCreate: the close beacon can legitimately
        // arrive and be processed before the open beacon's job has run (no
        // queue-worker-ordering guarantee in this app), so this must
        // self-heal into a valid row either way rather than silently
        // updating nothing. updateOrCreate INSERTs immediately, and
        // `opened_at` is NOT NULL with no DB default, so that insert was
        // rejected before the opened_at fallback below could ever run —
        // every close-before-open beacon threw. Building the model in
        // memory first lets the row carry a valid opened_at into its one
        // and only INSERT.
        $pageView = PageView::firstOrNew(
            ['client_page_view_id' => $d['client_page_view_id']]
        );

        foreach (['visitor_id', 'session_uuid', 'user_id', 'url', 'page_name'] as $field) {
            if (($d[$field] ?? null) !== null) {
                $pageView->{$field} = $d[$field];
            }
        }

        $pageView->closed_at = $now;
        $pageView->time_spent_seconds = max(0, (int) ($d['time_spent_seconds'] ?? 0));
        $pageView->is_exit = (bool) ($d['is_exit'] ?? false);
        $pageView->is_bot = $isBot;

        if (!empty($d['user_agent'])) {
            $pageView->user_agent = $d['user_agent'];
        }

        if (empty($pageView->opened_at)) {
            $pageView->opened_at = $now;
        }

        $pageView->save();

        if (!empty($d['session_uuid'])) {
            VisitorSession::where('session_uuid', $d['session_uuid'])->update(['ended_at' => $now]);
        }
    }

    public static function event(array $d): void
    {
        $event = new AnalyticsEvent([
            'visitor_id' => $d['visitor_id'],
            'session_uuid' => $d['session_uuid'] ?? null,
            'user_id' => $d['user_id'] ?? null,
            'event_category' => $d['event_category'],
            'event_type' => $d['event_type'],
            'listing_id' => $d['listing_id'] ?? null,
            'category_id' => $d['category_id'] ?? null,
            'subcategory_id' => $d['subcategory_id'] ?? null,
            'location_id' => $d['location_id'] ?? null,
            'keyword' => isset($d['keyword']) ? mb_strtolower(trim($d['keyword'])) : null,
            'results_count' => $d['results_count'] ?? null,
            'url' => $d['url'] ?? null,
            'meta' => $d['meta'] ?? null,
            'is_bot' => TrafficFilter::isBotPayload($d),
            'user_agent' => $d['user_agent'] ?? null,
        ]);

        if (!empty($d['occurred_at'])) {
            $event->created_at = $d['occurred_at'];
        }

        $event->save();
    }

    /**
     * When the beacon actually happened. Stamped by the job the moment it
     * was received, so a held beacon carries its own time through to the
     * write however long it waited in PendingTraffic.
     */
    protected static function occurredAt(array $d, $fallback)
    {
        return !empty($d['occurred_at']) ? \Carbon\Carbon::parse($d['occurred_at']) : $fallback;
    }
}
