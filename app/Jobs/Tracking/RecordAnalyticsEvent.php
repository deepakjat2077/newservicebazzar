<?php

namespace App\Jobs\Tracking;

use App\Support\Tracking\PendingTraffic;
use App\Support\Tracking\TrackingWriter;
use App\Support\Tracking\TrafficFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Single generic job covering every click / search / conversion / view
 * event — one insert-only row per event, distinguished by
 * event_category + event_type. No need for a job-per-event-type: the
 * payload shape barely varies and the table itself is the extensibility
 * mechanism (see AnalyticsEvent).
 */
class RecordAnalyticsEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data = [];

    public function __construct($data)
    {
        $data['occurred_at'] = $data['occurred_at'] ?? now()->toDateTimeString();

        $this->data = $data;
        $this->connection = config('queue.tracking_connection', 'sync');
    }

    public function handle()
    {
        $d = $this->data;

        if (TrafficFilter::isBotPayload($d)) {
            return;
        }

        $sessionUuid = $d['session_uuid'] ?? null;
        $passive = in_array($d['event_type'], config('analytics.passive_event_types', ['listing_view']), true);

        // Passive types fire on page load and prove nothing, so they wait
        // with the page view they belong to. Everything else took a
        // deliberate click and releases the session — as does a conversion
        // with no session at all (a server-side one, e.g. a payment IPN),
        // which has nothing to wait for.
        if (!$passive || !$sessionUuid || !empty($d['user_id'])) {
            TrackingWriter::writeHeld(PendingTraffic::release($sessionUuid));
            TrackingWriter::event($d);

            return;
        }

        if (PendingTraffic::isLive($sessionUuid)) {
            TrackingWriter::event($d);

            return;
        }

        PendingTraffic::hold($sessionUuid, PendingTraffic::TYPE_EVENT, $d);
    }
}
