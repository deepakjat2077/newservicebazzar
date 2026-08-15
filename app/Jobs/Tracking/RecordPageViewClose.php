<?php

namespace App\Jobs\Tracking;

use App\Models\PageView;
use App\Models\VisitorSession;
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
        $this->data = $data;
        $this->connection = config('queue.tracking_connection', 'sync');
    }

    public function handle()
    {
        $d = $this->data;
        $isBot = TrafficFilter::isBotUserAgent($d['user_agent'] ?? null)
            || TrafficFilter::isExcludedIp($d['ip_address'] ?? null);
        $now = now();

        // updateOrCreate rather than a plain update(): the close beacon can
        // legitimately arrive and be processed before the open beacon's job
        // has run (no queue-worker-ordering guarantee in this app), so this
        // must self-heal into a valid row either way rather than silently
        // updating nothing.
        // firstOrNew, not updateOrCreate: updateOrCreate INSERTs immediately,
        // and `opened_at` is NOT NULL with no DB default, so that insert was
        // rejected before the opened_at fallback below could ever run — every
        // close-before-open beacon threw. Building the model in memory first
        // lets the row carry a valid opened_at into its one and only INSERT.
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
}
