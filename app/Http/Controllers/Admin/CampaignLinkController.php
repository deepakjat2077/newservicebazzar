<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\CampaignLink;
use App\Models\VisitorSession;
use App\Support\Tracking\SourceReport;
use App\Support\Tracking\TrafficFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampaignLinkController extends Controller
{
    public function index()
    {
        $links = CampaignLink::latest()->get();

        return view('admin.analytics.campaigns', [
            'links' => $links,
            'stats' => $this->stats($links),
            'channels' => CampaignLink::CHANNELS,
        ]);
    }

    public function store(Request $request)
    {
        $host = preg_replace('/^www\./', '', (string) parse_url((string) config('app.url'), PHP_URL_HOST));

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'channel' => ['required', Rule::in(array_keys(CampaignLink::CHANNELS))],
            'landing_url' => ['required', 'url', 'max:1000', function ($attr, $value, $fail) use ($host) {
                $h = preg_replace('/^www\./', '', (string) parse_url($value, PHP_URL_HOST));
                if ($h !== $host) {
                    $fail('The page must be on ' . $host . '.');
                }
            }],
        ]);

        [, $source, $medium] = CampaignLink::CHANNELS[$data['channel']];
        $campaign = Str::limit(Str::slug($data['name'], '_'), 100, '');

        if ($campaign === '') {
            return back()->withInput()->withErrors(['name' => 'Use letters or numbers in the campaign name.']);
        }

        if (CampaignLink::where(compact('campaign', 'source', 'medium'))->exists()) {
            return back()->withInput()->withErrors(['name' => 'This campaign already has a link for this channel — copy it from the list below.']);
        }

        $link = CampaignLink::create([
            'name' => $data['name'],
            'campaign' => $campaign,
            'source' => $source,
            'medium' => $medium,
            'landing_url' => $data['landing_url'],
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.analytics.campaigns')->with('campaign_created', $link->id);
    }

    public function destroy($id)
    {
        CampaignLink::findOrFail($id)->delete();

        return redirect()->route('admin.analytics.campaigns');
    }

    /** Visits and leads per link, all time, matched on the UTM values the link carries. */
    protected function stats($links): array
    {
        if ($links->isEmpty()) {
            return [];
        }

        $campaigns = $links->pluck('campaign')->unique()->values()->all();
        $key = fn ($c, $s, $m) => strtolower((string) $c) . '|' . strtolower((string) $s) . '|' . strtolower((string) $m);
        $out = [];

        $sessions = TrafficFilter::applyCleanScope(VisitorSession::query()->from('visitor_sessions as s'), 's', true)
            ->whereIn(DB::raw('LOWER(s.utm_campaign)'), $campaigns)
            ->selectRaw('LOWER(s.utm_campaign) c, LOWER(s.utm_source) so, LOWER(s.utm_medium) me, COUNT(*) sessions, COUNT(DISTINCT s.visitor_id) visitors')
            ->groupBy('c', 'so', 'me')->get();

        foreach ($sessions as $r) {
            $out[$key($r->c, $r->so, $r->me)] = ['sessions' => (int) $r->sessions, 'visitors' => (int) $r->visitors];
        }

        $events = TrafficFilter::applyCleanScope(AnalyticsEvent::query()->from('analytics_events as e'), 'e')
            ->join('visitor_sessions as s', 's.session_uuid', '=', 'e.session_uuid')
            ->whereIn(DB::raw('LOWER(s.utm_campaign)'), $campaigns)
            ->whereIn('e.event_type', array_keys(SourceReport::EVENT_COLUMNS))
            ->selectRaw('LOWER(s.utm_campaign) c, LOWER(s.utm_source) so, LOWER(s.utm_medium) me, e.event_type t, COUNT(*) n')
            ->groupBy('c', 'so', 'me', 't')->get();

        foreach ($events as $r) {
            $k = $key($r->c, $r->so, $r->me);
            $col = SourceReport::EVENT_COLUMNS[$r->t];
            $out[$k][$col] = ($out[$k][$col] ?? 0) + (int) $r->n;
        }

        $result = [];

        foreach ($links as $l) {
            $s = $out[$key($l->campaign, $l->source, $l->medium)] ?? [];
            $result[$l->id] = [
                'sessions' => $s['sessions'] ?? 0,
                'visitors' => $s['visitors'] ?? 0,
                'calls' => $s['calls'] ?? 0,
                'whatsapp' => $s['whatsapp'] ?? 0,
                'enquiries' => $s['enquiries'] ?? 0,
            ];
        }

        return $result;
    }
}
