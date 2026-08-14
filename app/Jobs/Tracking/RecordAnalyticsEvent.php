<?php

namespace App\Jobs\Tracking;

use App\Models\AnalyticsEvent;
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
        $this->data = $data;
        $this->connection = config('queue.tracking_connection', 'sync');
    }

    public function handle()
    {
        $d = $this->data;

        AnalyticsEvent::create([
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
        ]);
    }
}
