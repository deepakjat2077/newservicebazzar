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

class RecordPageViewClose implements ShouldQueue
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

        // tracking.js fires this from both pagehide and visibilitychange,
        // so its arrival means a real browser really left the page — the
        // strongest signal the pipeline has, and the one that releases the
        // page view this closes.
        TrackingWriter::writeHeld(PendingTraffic::release($d['session_uuid'] ?? null));
        TrackingWriter::pageViewClose($d);
    }
}
