<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\GoogleIndexing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin "Search Console" report. Reads the daily Google Search Console data
 * that `seo:gsc-pull` stores in gsc_metrics (service account — no OAuth, no
 * local token, works on both servers). Its job is to surface where clicks
 * are being left on the table:
 *   - Page-2 opportunities: queries ranking 8-20 with many impressions —
 *     push these up to win clicks.
 *   - CTR opportunities: pages already on page 1 that get impressions but
 *     few clicks — fix the title/meta to win clicks straight away.
 */
class GscController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->input('days', 28);
        $days = in_array($days, [7, 28, 90], true) ? $days : 28;
        $end  = now()->subDays(2)->toDateString();          // GSC lags ~2 days
        $from = now()->subDays(2 + $days)->toDateString();

        $hasData = DB::table('gsc_metrics')->exists();

        return view('admin.gsc.index', [
            'hasData'     => $hasData,
            'configured'  => GoogleIndexing::configured(),
            'lastPull'    => cache()->get('gsc:last-pull'),
            'days'        => $days,
            'from'        => $from,
            'end'         => $end,
            'totals'      => $this->totals($from, $end),
            'trend'       => $this->trend($from, $end),
            'topQueries'  => $this->aggregate('query', $from, $end)->sortByDesc('clicks')->take(25)->values(),
            'topPages'    => $this->aggregate('page', $from, $end)->sortByDesc('clicks')->take(25)->values(),
            'page2'       => $this->page2Opportunities($from, $end),
            'ctrOpps'     => $this->ctrOpportunities($from, $end),
        ]);
    }

    /** Site totals across the window, with a true CTR from summed clicks/impressions. */
    private function totals(string $from, string $end): object
    {
        $r = DB::table('gsc_metrics')
            ->where('dimension', 'total')->whereBetween('date', [$from, $end])
            ->selectRaw('SUM(clicks) c, SUM(impressions) i,
                         SUM(position * impressions) / NULLIF(SUM(impressions),0) pos')
            ->first();

        $clicks = (int) ($r->c ?? 0);
        $impr   = (int) ($r->i ?? 0);

        return (object) [
            'clicks'      => $clicks,
            'impressions' => $impr,
            'ctr'         => $impr > 0 ? round($clicks / $impr * 100, 2) : 0,
            'position'    => round((float) ($r->pos ?? 0), 1),
        ];
    }

    /** Per-day clicks/impressions for the little trend chart. */
    private function trend(string $from, string $end)
    {
        return DB::table('gsc_metrics')
            ->where('dimension', 'total')->whereBetween('date', [$from, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->selectRaw('date, SUM(clicks) clicks, SUM(impressions) impressions')
            ->get();
    }

    /**
     * Collapse the per-day rows of one dimension into one row per value:
     * clicks & impressions summed, position weighted by impressions, ctr derived.
     */
    private function aggregate(string $dimension, string $from, string $end)
    {
        return DB::table('gsc_metrics')
            ->where('dimension', $dimension)->whereBetween('date', [$from, $end])
            ->groupBy('value')
            ->selectRaw('value,
                         SUM(clicks) clicks,
                         SUM(impressions) impressions,
                         SUM(position * impressions) / NULLIF(SUM(impressions),0) position')
            ->get()
            ->map(function ($r) {
                $r->clicks      = (int) $r->clicks;
                $r->impressions = (int) $r->impressions;
                $r->position    = round((float) $r->position, 1);
                $r->ctr         = $r->impressions > 0 ? round($r->clicks / $r->impressions * 100, 2) : 0;
                return $r;
            });
    }

    /**
     * Queries stuck on page 2 (position 8-20) with real demand (impressions):
     * closest to page 1, so the cheapest clicks to win by lifting the ranking.
     */
    private function page2Opportunities(string $from, string $end)
    {
        return $this->aggregate('query', $from, $end)
            ->filter(fn ($r) => $r->position >= 8 && $r->position <= 20 && $r->impressions >= 30)
            ->sortByDesc('impressions')
            ->take(30)
            ->values();
    }

    /**
     * Pages already on page 1 (avg position <= 10) that pull a lot of
     * impressions but few clicks — the title/meta isn't earning the click.
     * The fastest win because the fix is entirely ours (no ranking wait).
     */
    private function ctrOpportunities(string $from, string $end)
    {
        return $this->aggregate('page', $from, $end)
            ->filter(fn ($r) => $r->position <= 10 && $r->impressions >= 100 && $r->ctr < 2)
            ->sortByDesc('impressions')
            ->take(30)
            ->values();
    }
}
