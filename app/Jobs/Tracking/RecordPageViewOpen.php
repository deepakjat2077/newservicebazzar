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

class RecordPageViewOpen implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data = [];

    public function __construct($data)
    {
        // Stamped at dispatch, not at write: a page view held in
        // PendingTraffic until its session proves human must still be
        // recorded at the time it happened.
        $data['occurred_at'] = $data['occurred_at'] ?? now()->toDateTimeString();

        $this->data = $data;
        // See config/queue.php: "sync" by default (no worker process is
        // guaranteed to be running), overridable via .env once one is.
        $this->connection = config('queue.tracking_connection', 'sync');
    }

    public function handle()
    {
        $d = $this->data;

        // Nothing recognisable as a bot is stored at all, not even flagged.
        if (TrafficFilter::isBotPayload($d)) {
            return;
        }

        $sessionUuid = $d['session_uuid'];

        // A second page view is itself the proof: whatever is driving this
        // session did not render one page and vanish.
        $isSecondView = PendingTraffic::heldCount($sessionUuid, PendingTraffic::TYPE_PAGE_VIEW_OPEN) > 0;

        if (!empty($d['user_id']) || $isSecondView || PendingTraffic::isLive($sessionUuid)) {
            TrackingWriter::writeHeld(PendingTraffic::release($sessionUuid));
            TrackingWriter::pageViewOpen($d);

            return;
        }

        PendingTraffic::hold($sessionUuid, PendingTraffic::TYPE_PAGE_VIEW_OPEN, $d);
    }
}
