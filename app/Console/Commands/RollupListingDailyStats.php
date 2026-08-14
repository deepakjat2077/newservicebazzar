<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\ListingDailyStats;
use App\Models\PageView;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rolls up the previous day's raw analytics_events/page_views rows into
 * one summary row per listing per day, so the admin dashboard's
 * "top listings" / per-listing analytics queries hit a small, indexed
 * table instead of re-scanning the full event log every time it loads.
 */
class RollupListingDailyStats extends Command
{
    protected $signature = 'analytics:rollup-listings {date? : Y-m-d date to roll up, defaults to yesterday}';

    protected $description = 'Aggregate analytics_events/page_views into listing_daily_stats for one day';

    public function handle(): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))->startOfDay()
            : now()->subDay()->startOfDay();

        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $this->info("Rolling up listing stats for {$date->toDateString()}...");

        $clickTypeColumn = [
            'whatsapp_click' => 'whatsapp_clicks',
            'call_click' => 'phone_clicks',
            'website_click' => 'website_clicks',
            'direction_click' => 'direction_clicks',
            'gallery_click' => 'gallery_clicks',
            'review_click' => 'review_clicks',
            'share_click' => 'share_clicks',
            'favourite_click' => 'favourite_clicks',
        ];

        // views + unique_views, from the listing_view events fired on every
        // listing-detail page load (see tracking.js).
        $viewRows = AnalyticsEvent::query()
            ->where('event_type', 'listing_view')
            ->whereNotNull('listing_id')
            ->whereBetween('created_at', [$start, $end])
            ->select('listing_id')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT visitor_id) as unique_views')
            ->groupBy('listing_id')
            ->get()
            ->keyBy('listing_id');

        // per-click-type counts, from the same generic event log.
        $clickRows = AnalyticsEvent::query()
            ->whereIn('event_type', array_keys($clickTypeColumn))
            ->whereNotNull('listing_id')
            ->whereBetween('created_at', [$start, $end])
            ->select('listing_id', 'event_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('listing_id', 'event_type')
            ->get()
            ->groupBy('listing_id');

        // avg time spent on the listing's own detail page.
        $avgTimeRows = PageView::query()
            ->whereNotNull('listing_id')
            ->whereNotNull('time_spent_seconds')
            ->whereBetween('opened_at', [$start, $end])
            ->select('listing_id')
            ->selectRaw('AVG(time_spent_seconds) as avg_time')
            ->groupBy('listing_id')
            ->get()
            ->keyBy('listing_id');

        $listingIds = collect()
            ->merge($viewRows->keys())
            ->merge($clickRows->keys())
            ->merge($avgTimeRows->keys())
            ->unique();

        $count = 0;

        foreach ($listingIds as $listingId) {
            $row = [
                'views' => (int) ($viewRows[$listingId]->views ?? 0),
                'unique_views' => (int) ($viewRows[$listingId]->unique_views ?? 0),
                'whatsapp_clicks' => 0,
                'phone_clicks' => 0,
                'website_clicks' => 0,
                'direction_clicks' => 0,
                'gallery_clicks' => 0,
                'review_clicks' => 0,
                'share_clicks' => 0,
                'favourite_clicks' => 0,
                'avg_time_seconds' => isset($avgTimeRows[$listingId]) ? round((float) $avgTimeRows[$listingId]->avg_time, 1) : null,
            ];

            foreach ($clickRows[$listingId] ?? [] as $clickRow) {
                $column = $clickTypeColumn[$clickRow->event_type] ?? null;
                if ($column) {
                    $row[$column] = (int) $clickRow->total;
                }
            }

            ListingDailyStats::updateOrCreate(
                ['listing_id' => $listingId, 'date' => $date->toDateString()],
                $row
            );

            $count++;
        }

        $this->info("Done. {$count} listing(s) rolled up for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
