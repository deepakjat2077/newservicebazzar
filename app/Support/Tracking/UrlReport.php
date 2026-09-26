<?php

namespace App\Support\Tracking;

use App\Models\AnalyticsEvent;
use App\Models\PageView;
use Illuminate\Support\Facades\DB;

/** Traffic for one URL of this site (or everything beneath it), from our own analytics. */
class UrlReport
{
    public static function normalize(string $input): ?array
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        $parts = parse_url(str_contains($input, '://') ? $input : 'https://x' . (str_starts_with($input, '/') ? '' : '/') . $input);
        $path = rtrim($parts['path'] ?? '', '/');

        return ['path' => $path, 'label' => ($path === '' ? '/' : $path)];
    }

    public static function build(\DateTimeInterface $from, \DateTimeInterface $to, string $path, bool $prefix): array
    {
        $views = static::scope(TrafficFilter::applyCleanScope(PageView::query()->from('page_views as p'), 'p'), 'p.url', $path, $prefix)
            ->whereBetween('p.opened_at', [$from, $to]);

        $t = (clone $views)->selectRaw('COUNT(*) views, COUNT(DISTINCT p.visitor_id) visitors, AVG(NULLIF(p.time_spent_seconds,0)) avg_time, SUM(p.is_exit) exits')->first();

        $totals = [
            'views' => (int) $t->views,
            'visitors' => (int) $t->visitors,
            'avg_time' => $t->avg_time !== null ? (int) round($t->avg_time) : null,
            'exit_rate' => $t->views > 0 ? round($t->exits / $t->views * 100) : null,
        ];

        $daily = (clone $views)->selectRaw('DATE(p.opened_at) d, COUNT(*) views, COUNT(DISTINCT p.visitor_id) visitors')
            ->groupBy('d')->orderBy('d')->get();

        $channels = [];

        $rows = (clone $views)
            ->leftJoin('visitor_sessions as s', 's.session_uuid', '=', 'p.session_uuid')
            ->select(array_merge(SourceReport::rawColumns('s'), [DB::raw('COUNT(*) views'), DB::raw('COUNT(DISTINCT p.visitor_id) visitors')]))
            ->groupBy(['utm_source', 'utm_medium', 'utm_campaign', 'ref_host', 'gclid', 'fbclid'])
            ->get();

        foreach ($rows as $r) {
            $ch = SourceReport::fromRow($r);
            $key = $ch['source'] . '|' . $ch['medium'];
            $channels[$key] ??= ['source' => $ch['source'], 'medium' => $ch['medium'], 'views' => 0, 'visitors' => 0];
            $channels[$key]['views'] += (int) $r->views;
            $channels[$key]['visitors'] += (int) $r->visitors;
        }

        usort($channels, fn ($a, $b) => $b['views'] <=> $a['views']);

        $events = static::scope(TrafficFilter::applyCleanScope(AnalyticsEvent::query()->from('analytics_events as e'), 'e'), 'e.url', $path, $prefix)
            ->whereBetween('e.created_at', [$from, $to])
            ->whereIn('e.event_type', array_keys(SourceReport::EVENT_COLUMNS))
            ->selectRaw('e.event_type, COUNT(*) n')->groupBy('e.event_type')->pluck('n', 'e.event_type');

        $actions = ['calls' => 0, 'whatsapp' => 0, 'directions' => 0, 'enquiries' => 0];

        foreach ($events as $type => $n) {
            $actions[SourceReport::EVENT_COLUMNS[$type]] += (int) $n;
        }

        return ['totals' => $totals, 'daily' => $daily, 'channels' => $channels, 'actions' => $actions];
    }

    protected static function scope($query, string $column, string $path, bool $prefix)
    {
        $host = preg_replace('/^www\./', '', (string) parse_url((string) config('app.url'), PHP_URL_HOST)) ?: 'servicebazzar.com';
        $bases = ['https://www.' . $host, 'https://' . $host, 'http://www.' . $host, 'http://' . $host];
        $esc = fn (string $s) => addcslashes($s, '%_\\');

        return $query->where(function ($q) use ($column, $bases, $path, $prefix, $esc) {
            foreach ($bases as $b) {
                $u = $b . $path;
                $q->orWhere($column, $u)->orWhere($column, $u . '/')
                    ->orWhere($column, 'like', $esc($u) . '?%')->orWhere($column, 'like', $esc($u) . '/?%')
                    ->orWhere($column, 'like', $esc($u) . '#%');

                if ($prefix) {
                    $q->orWhere($column, 'like', $esc($u) . '/%');
                }
            }
        });
    }
}
