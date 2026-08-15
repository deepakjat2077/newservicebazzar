<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnalyticsExport;
use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingDailyStats;
use App\Models\Location;
use App\Models\PageView;
use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Support\Tracking\TrafficFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AnalyticsController extends Controller
{
    /**
     * Resolves the today/yesterday/last7/last30/thisMonth/custom filter
     * into concrete [$from, $to] bounds — mirrors the from_date/to_date
     * query-param convention already used by AdminController::dashboard()
     * and AdminController::listingExport().
     */
    protected function resolveRange(Request $request): array
    {
        $range = $request->get('range', 'last7');

        switch ($range) {
            case 'today':
                return [now()->startOfDay(), now()->endOfDay(), $range];
            case 'yesterday':
                return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), $range];
            case 'last30':
                return [now()->subDays(29)->startOfDay(), now()->endOfDay(), $range];
            case 'thisMonth':
                return [now()->startOfMonth(), now()->endOfMonth(), $range];
            case 'custom':
                $from = $request->filled('from_date') ? Carbon::parse($request->from_date)->startOfDay() : now()->subDays(6)->startOfDay();
                $to = $request->filled('to_date') ? Carbon::parse($request->to_date)->endOfDay() : now()->endOfDay();
                return [$from, $to, $range];
            case 'last7':
            default:
                return [now()->subDays(6)->startOfDay(), now()->endOfDay(), 'last7'];
        }
    }

    /**
     * buildStats() behind the shared cache entry.
     *
     * The three export actions used to call buildStats() directly, so an
     * export re-ran all twenty aggregates even when the dashboard the admin
     * was looking at had just computed them — and "view the numbers, then
     * download them" is the only way anyone reaches an export. The PDF one is
     * the worst of the three, since it pays for DomPDF on top.
     *
     * The key depends only on the resolved range, so the page and its exports
     * share one entry rather than each keeping their own.
     */
    protected function cachedStats($from, $to, string $range): array
    {
        $cacheKey = 'analytics:' . $range . ':' . $from->timestamp . ':' . $to->timestamp;

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($from, $to) {
            return $this->buildStats($from, $to);
        });
    }

    public function index(Request $request)
    {
        [$from, $to, $range] = $this->resolveRange($request);

        $data = $this->cachedStats($from, $to, $range);

        $data['range'] = $range;
        $data['fromDate'] = $from->toDateString();
        $data['toDate'] = $to->toDateString();

        return view('admin.analytics.index', $data);
    }

    protected function buildStats($from, $to): array
    {
        $data = [];

        // ---- Visitor totals ----
        $data['totalVisitors'] = $this->visitorQuery()->whereBetween('last_visit_at', [$from, $to])->count();
        $data['returningVisitors'] = $this->visitorQuery()->whereBetween('last_visit_at', [$from, $to])->where('total_visits', '>', 1)->count();
        $data['activeVisitors'] = $this->visitorQuery()->where('last_visit_at', '>=', now()->subMinutes(5))->count();

        // ---- Daily / weekly / monthly visitor trend (for the range chart) ----
        $data['dailyVisitors'] = $this->sessionQuery()->whereBetween('started_at', [$from, $to])
            ->selectRaw('DATE(started_at) as day')
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // ---- Top categories (by listing_view events) ----
        $data['topCategories'] = $this->eventQuery()
            ->join('categories', 'categories.id', '=', 'analytics_events.category_id')
            ->where('analytics_events.event_type', 'listing_view')
            ->whereBetween('analytics_events.created_at', [$from, $to])
            ->select('categories.id', 'categories.name')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT analytics_events.visitor_id) as unique_views')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // ---- Top locations (by listing_view events) ----
        $data['topLocations'] = $this->eventQuery()
            ->join('locations', 'locations.id', '=', 'analytics_events.location_id')
            ->where('analytics_events.event_type', 'listing_view')
            ->whereBetween('analytics_events.created_at', [$from, $to])
            ->select('locations.id', 'locations.name')
            ->selectRaw('COUNT(*) as views')
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // ---- Top listings (from the daily rollup where available, falling
        // back to raw events for "today", which the nightly rollup hasn't
        // processed yet) ----
        $data['topListings'] = $this->eventQuery()
            ->join('listings', 'listings.id', '=', 'analytics_events.listing_id')
            ->where('analytics_events.event_type', 'listing_view')
            ->whereBetween('analytics_events.created_at', [$from, $to])
            ->select('listings.id', 'listings.name')
            ->selectRaw('COUNT(*) as total_views')
            ->selectRaw('COUNT(DISTINCT analytics_events.visitor_id) as total_unique_views')
            ->groupBy('listings.id', 'listings.name')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        // ---- Search keywords ----
        $data['topKeywords'] = $this->eventQuery()->where('event_type', 'search_performed')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('keyword')
            ->where('keyword', '!=', '')
            ->select('keyword')
            ->selectRaw('COUNT(*) as search_count')
            ->groupBy('keyword')
            ->orderByDesc('search_count')
            ->limit(20)
            ->get();

        $data['noResultSearches'] = $this->eventQuery()->where('event_type', 'search_performed')
            ->where('results_count', 0)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('keyword')
            ->where('keyword', '!=', '')
            ->select('keyword')
            ->selectRaw('COUNT(*) as occurrences')
            ->groupBy('keyword')
            ->orderByDesc('occurrences')
            ->limit(20)
            ->get();

        // ---- Top pages ----
        $data['topPages'] = $this->pageViewQuery()->whereBetween('opened_at', [$from, $to])
            ->whereNotNull('page_name')
            ->select('page_name')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('AVG(time_spent_seconds) as avg_time')
            ->groupBy('page_name')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        // ---- Bounce rate + avg session duration (cheap thanks to the
        // page_view_count counter cache maintained on every page-view-open) ----
        $totalSessions = $this->sessionQuery()->whereBetween('started_at', [$from, $to])->count();
        $bouncedSessions = $this->sessionQuery()->whereBetween('started_at', [$from, $to])->where('page_view_count', 1)->count();
        $data['totalSessions'] = $totalSessions;
        $data['bounceRate'] = $totalSessions ? round($bouncedSessions / $totalSessions * 100, 1) : 0;

        $data['avgSessionDuration'] = (int) round($this->sessionQuery()->whereBetween('started_at', [$from, $to])
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, ended_at)) as avg_seconds')
            ->value('avg_seconds') ?? 0);

        // ---- Distributions ----
        $data['deviceDistribution'] = $this->sessionQuery()->whereBetween('started_at', [$from, $to])
            ->whereNotNull('device_type')
            ->select('device_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        $data['browserDistribution'] = $this->sessionQuery()->whereBetween('started_at', [$from, $to])
            ->whereNotNull('browser')
            ->select('browser')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $data['countryDistribution'] = $this->sessionQuery()->whereBetween('started_at', [$from, $to])
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->select('country')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ---- Conversion funnel: distinct visitors reaching each stage in
        // range (a simplification, not a strict same-session ordered
        // funnel — documented in the plan) ----
        $stageVisitors = fn (string $type) => $this->eventQuery()->where('event_type', $type)
            ->whereBetween('created_at', [$from, $to])
            ->distinct('visitor_id')
            ->count('visitor_id');

        $data['funnel'] = [
            'visited' => $this->sessionQuery()->whereBetween('started_at', [$from, $to])->distinct('visitor_id')->count('visitor_id'),
            'viewed_listing' => $stageVisitors('listing_view'),
            'engaged' => $this->eventQuery()->whereIn('event_type', ['whatsapp_click', 'call_click', 'enquiry'])
                ->whereBetween('created_at', [$from, $to])
                ->distinct('visitor_id')->count('visitor_id'),
            'account' => $this->eventQuery()->whereIn('event_type', ['registration', 'login'])
                ->whereBetween('created_at', [$from, $to])
                ->distinct('visitor_id')->count('visitor_id'),
            'paid' => $stageVisitors('payment_success'),
        ];

        // ---- Conversion totals by type ----
        $data['conversionTotals'] = $this->eventQuery()->where('event_category', 'conversion')
            ->whereBetween('created_at', [$from, $to])
            ->select('event_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('event_type')
            ->orderByDesc('total')
            ->get();

        return $data;
    }

    protected function visitorQuery()
    {
        return TrafficFilter::applyCleanScope(Visitor::query(), 'visitors', true);
    }

    protected function sessionQuery()
    {
        return TrafficFilter::applyCleanScope(VisitorSession::query(), 'visitor_sessions', true);
    }

    protected function eventQuery()
    {
        return TrafficFilter::applyCleanScope(AnalyticsEvent::query(), 'analytics_events');
    }

    protected function pageViewQuery()
    {
        return TrafficFilter::applyCleanScope(PageView::query(), 'page_views');
    }

    public function exportExcel(Request $request)
    {
        [$from, $to, $range] = $this->resolveRange($request);
        $stats = $this->cachedStats($from, $to, $range);

        $filename = 'analytics-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.xlsx';

        return Excel::download(new AnalyticsExport($stats), $filename);
    }

    public function exportCsv(Request $request)
    {
        [$from, $to, $range] = $this->resolveRange($request);
        $stats = $this->cachedStats($from, $to, $range);

        $filename = 'analytics-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv';

        $callback = function () use ($stats) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Metric', 'Value']);
            fputcsv($out, ['Total Visitors', $stats['totalVisitors']]);
            fputcsv($out, ['Returning Visitors', $stats['returningVisitors']]);
            fputcsv($out, ['Active Visitors (last 5 min)', $stats['activeVisitors']]);
            fputcsv($out, ['Total Sessions', $stats['totalSessions']]);
            fputcsv($out, ['Bounce Rate (%)', $stats['bounceRate']]);
            fputcsv($out, ['Avg Session Duration (seconds)', $stats['avgSessionDuration']]);
            fputcsv($out, []);

            fputcsv($out, ['Top Listings']);
            fputcsv($out, ['Listing', 'Views', 'Unique Views']);
            foreach ($stats['topListings'] as $row) {
                fputcsv($out, [$row->name, $row->total_views, $row->total_unique_views]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Top Categories']);
            fputcsv($out, ['Category', 'Views', 'Unique Views']);
            foreach ($stats['topCategories'] as $row) {
                fputcsv($out, [$row->name, $row->views, $row->unique_views]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Top Keywords']);
            fputcsv($out, ['Keyword', 'Search Count']);
            foreach ($stats['topKeywords'] as $row) {
                fputcsv($out, [$row->keyword, $row->search_count]);
            }

            fclose($out);
        };

        $filename = 'analytics-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$from, $to, $range] = $this->resolveRange($request);
        $stats = $this->cachedStats($from, $to, $range);
        $stats['fromDate'] = $from->toDateString();
        $stats['toDate'] = $to->toDateString();

        $filename = 'analytics-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.pdf';

        return Pdf::loadView('admin.analytics.pdf', $stats)->download($filename);
    }
}
