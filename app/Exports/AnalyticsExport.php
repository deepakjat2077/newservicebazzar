<?php

namespace App\Exports;

use App\Exports\Sheets\ReportSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnalyticsExport implements WithMultipleSheets
{
    protected array $stats;

    public function __construct(array $stats)
    {
        $this->stats = $stats;
    }

    public function sheets(): array
    {
        $summary = collect([
            ['Metric' => 'Total Visitors', 'Value' => $this->stats['totalVisitors']],
            ['Metric' => 'Returning Visitors', 'Value' => $this->stats['returningVisitors']],
            ['Metric' => 'Active Visitors (last 5 min)', 'Value' => $this->stats['activeVisitors']],
            ['Metric' => 'Total Sessions', 'Value' => $this->stats['totalSessions']],
            ['Metric' => 'Bounce Rate (%)', 'Value' => $this->stats['bounceRate']],
            ['Metric' => 'Avg Session Duration (seconds)', 'Value' => $this->stats['avgSessionDuration']],
        ]);

        $listings = collect($this->stats['topListings'])->map(fn ($r) => [
            'Listing' => $r->name,
            'Views' => $r->total_views,
            'Unique Views' => $r->total_unique_views,
        ]);

        $categories = collect($this->stats['topCategories'])->map(fn ($r) => [
            'Category' => $r->name,
            'Views' => $r->views,
            'Unique Views' => $r->unique_views,
        ]);

        $locations = collect($this->stats['topLocations'])->map(fn ($r) => [
            'Location' => $r->name,
            'Views' => $r->views,
        ]);

        $keywords = collect($this->stats['topKeywords'])->map(fn ($r) => [
            'Keyword' => $r->keyword,
            'Search Count' => $r->search_count,
        ]);

        $noResults = collect($this->stats['noResultSearches'])->map(fn ($r) => [
            'Keyword' => $r->keyword,
            'Occurrences' => $r->occurrences,
        ]);

        $pages = collect($this->stats['topPages'])->map(fn ($r) => [
            'Page' => $r->page_name,
            'Views' => $r->views,
            'Avg Time (sec)' => $r->avg_time ? round($r->avg_time, 1) : 0,
        ]);

        $conversions = collect($this->stats['conversionTotals'])->map(fn ($r) => [
            'Conversion Type' => $r->event_type,
            'Total' => $r->total,
        ]);

        return [
            new ReportSheet($summary, ['Metric', 'Value'], 'Summary'),
            new ReportSheet($listings, ['Listing', 'Views', 'Unique Views'], 'Top Listings'),
            new ReportSheet($categories, ['Category', 'Views', 'Unique Views'], 'Top Categories'),
            new ReportSheet($locations, ['Location', 'Views'], 'Top Locations'),
            new ReportSheet($keywords, ['Keyword', 'Search Count'], 'Top Keywords'),
            new ReportSheet($noResults, ['Keyword', 'Occurrences'], 'No Result Searches'),
            new ReportSheet($pages, ['Page', 'Views', 'Avg Time (sec)'], 'Top Pages'),
            new ReportSheet($conversions, ['Conversion Type', 'Total'], 'Conversions'),
        ];
    }
}
