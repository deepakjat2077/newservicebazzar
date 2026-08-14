<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin-top: 22px; margin-bottom: 6px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        p.subtitle { color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .summary-table td:first-child { font-weight: bold; width: 60%; }
    </style>
</head>
<body>
    <h1>Analytics Report</h1>
    <p class="subtitle">{{ $fromDate }} to {{ $toDate }}</p>

    <h2>Summary</h2>
    <table class="summary-table">
        <tr><td>Total Visitors</td><td>{{ number_format($totalVisitors) }}</td></tr>
        <tr><td>Returning Visitors</td><td>{{ number_format($returningVisitors) }}</td></tr>
        <tr><td>Active Visitors (last 5 min)</td><td>{{ number_format($activeVisitors) }}</td></tr>
        <tr><td>Total Sessions</td><td>{{ number_format($totalSessions) }}</td></tr>
        <tr><td>Bounce Rate</td><td>{{ $bounceRate }}%</td></tr>
        <tr><td>Avg Session Duration</td><td>{{ gmdate('i:s', $avgSessionDuration) }}</td></tr>
    </table>

    <h2>Top Listings</h2>
    <table>
        <thead><tr><th>Listing</th><th>Views</th><th>Unique Views</th></tr></thead>
        <tbody>
            @forelse($topListings as $row)
                <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_views) }}</td><td>{{ number_format($row->total_unique_views) }}</td></tr>
            @empty
                <tr><td colspan="3">No data</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top Categories</h2>
    <table>
        <thead><tr><th>Category</th><th>Views</th><th>Unique Views</th></tr></thead>
        <tbody>
            @forelse($topCategories as $row)
                <tr><td>{{ $row->name }}</td><td>{{ number_format($row->views) }}</td><td>{{ number_format($row->unique_views) }}</td></tr>
            @empty
                <tr><td colspan="3">No data</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Most Searched Keywords</h2>
    <table>
        <thead><tr><th>Keyword</th><th>Searches</th></tr></thead>
        <tbody>
            @forelse($topKeywords as $row)
                <tr><td>{{ $row->keyword }}</td><td>{{ number_format($row->search_count) }}</td></tr>
            @empty
                <tr><td colspan="2">No data</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>No-Result Searches</h2>
    <table>
        <thead><tr><th>Keyword</th><th>Occurrences</th></tr></thead>
        <tbody>
            @forelse($noResultSearches as $row)
                <tr><td>{{ $row->keyword }}</td><td>{{ number_format($row->occurrences) }}</td></tr>
            @empty
                <tr><td colspan="2">No data</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Conversions</h2>
    <table>
        <thead><tr><th>Type</th><th>Total</th></tr></thead>
        <tbody>
            @forelse($conversionTotals as $row)
                <tr><td>{{ ucwords(str_replace('_', ' ', $row->event_type)) }}</td><td>{{ number_format($row->total) }}</td></tr>
            @empty
                <tr><td colspan="2">No data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
