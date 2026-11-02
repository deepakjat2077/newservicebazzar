<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\GoogleIndexing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Admin report for the Google Indexing API (seo:google-index): which URLs were
 * sent and what Google answered, plus whether Search Console now shows them as
 * indexed (seo:google-index-check, or the per-row "Check now" button).
 */
class GoogleIndexingController extends Controller
{
    private const SOURCES = ['category' => 'Category', 'subcategory' => 'Subcategory', 'blog' => 'Blog', 'listing' => 'Listing'];

    public function index(Request $request)
    {
        $q = DB::table('google_index_submissions');

        if (isset(self::SOURCES[$request->source])) {
            $q->where('source', $request->source);
        }
        match ($request->status) {
            'sent'   => $q->where('http_status', 200),
            'failed' => $q->where(fn ($w) => $w->whereNull('http_status')->orWhere('http_status', '<>', 200)),
            default  => null,
        };
        match ($request->google) {
            'indexed'     => $q->where('index_verdict', 'PASS'),
            'not_indexed' => $q->whereNotNull('index_verdict')->where('index_verdict', '<>', 'PASS'),
            'unchecked'   => $q->whereNull('inspected_at'),
            default       => null,
        };
        if ($request->filled('search')) {
            $q->where('url', 'like', '%' . trim($request->search) . '%');
        }

        $rows = $q->orderByDesc('notified_at')->paginate(50)->withQueryString();

        $bySource = DB::table('google_index_submissions')
            ->selectRaw("source,
                SUM(http_status = 200) AS sent,
                SUM(http_status IS NULL OR http_status <> 200) AS failed,
                SUM(index_verdict = 'PASS') AS indexed,
                SUM(index_verdict IS NOT NULL AND index_verdict <> 'PASS') AS not_indexed,
                SUM(http_status = 200 AND inspected_at IS NULL) AS unchecked")
            ->groupBy('source')->get()->keyBy('source');

        $totals = (object) [
            'sent'        => (int) $bySource->sum('sent'),
            'failed'      => (int) $bySource->sum('failed'),
            'indexed'     => (int) $bySource->sum('indexed'),
            'not_indexed' => (int) $bySource->sum('not_indexed'),
            'unchecked'   => (int) $bySource->sum('unchecked'),
        ];

        $sentToday = DB::table('google_index_submissions')
            ->where('notified_at', '>=', now()->startOfDay())->where('http_status', 200)->count();

        return view('admin.gsc.indexing', [
            'rows'         => $rows,
            'sources'      => self::SOURCES,
            'bySource'     => $bySource,
            'totals'       => $totals,
            'sentToday'    => $sentToday,
            'configured'   => GoogleIndexing::configured(),
            'lastRun'      => Cache::get(GoogleIndexing::LAST_RUN_KEY),
            'inspectError' => Cache::get(GoogleIndexing::INSPECT_ERROR_KEY),
        ]);
    }

    /** "Check now": ask Google about one URL straight away. */
    public function inspect(int $id)
    {
        $row = DB::table('google_index_submissions')->where('id', $id)->first(['id', 'url']);
        abort_unless($row, 404);

        [$status, $error] = GoogleIndexing::inspectAndStore($row->id, $row->url);

        if ($status !== 200) {
            return back()->withNotify([['error', 'Google check failed (HTTP ' . $status . '): ' . mb_substr((string) $error, 0, 200)]]);
        }

        return back()->withNotify([['success', 'Checked on Google: ' . $row->url]]);
    }
}
