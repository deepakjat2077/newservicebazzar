<?php

namespace App\Support\Tracking;

use App\Models\AnalyticsEvent;
use App\Models\VisitorSession;
use Illuminate\Support\Facades\DB;

/**
 * Source -> conversion report. Sessions and click/enquiry events are
 * attributed to the channel of the session they happened in; payments have
 * no session of their own, so they take the paying user's most recent
 * non-direct session within the 30 days before payment (else the latest
 * session of any kind).
 */
class SourceReport
{
    public const ATTRIBUTION_DAYS = 30;

    public const MAX_ROWS = 60;

    protected const SEARCH = ['google', 'bing', 'duckduckgo', 'yahoo', 'yandex', 'ecosia', 'baidu'];

    protected const SOCIAL = ['facebook', 'instagram', 'twitter', 'linkedin', 'pinterest', 'youtube', 'reddit', 'whatsapp', 'telegram'];

    protected const ALIASES = [
        'ig' => 'instagram', 'instagram.com' => 'instagram',
        'fb' => 'facebook', 'facebook.com' => 'facebook',
        't.co' => 'twitter', 'x.com' => 'twitter', 'twitter.com' => 'twitter',
        'youtube.com' => 'youtube', 'youtu.be' => 'youtube',
        'linkedin.com' => 'linkedin', 'pinterest.com' => 'pinterest',
        'wa' => 'whatsapp', 'whatsapp.com' => 'whatsapp', 'wa.me' => 'whatsapp',
        'telegram.org' => 'telegram', 't.me' => 'telegram',
        'reddit.com' => 'reddit',
    ];

    public const LEAD_EVENTS = ['call_click', 'whatsapp_click', 'enquiry', 'contact_form_submitted'];

    public const EVENT_COLUMNS = [
        'call_click' => 'calls',
        'whatsapp_click' => 'whatsapp',
        'direction_click' => 'directions',
        'enquiry' => 'enquiries',
        'contact_form_submitted' => 'enquiries',
    ];

    /** @return array{source:string,medium:string,campaign:string} */
    public static function classify(?string $utmSource, ?string $utmMedium, ?string $utmCampaign, ?string $refHost, bool $gclid = false, bool $fbclid = false): array
    {
        $source = static::clean($utmSource);
        $medium = static::clean($utmMedium);
        $campaign = static::clean($utmCampaign);

        if ($source !== null) {
            return [
                'source' => static::normalizeSource($source),
                'medium' => $medium ?? '(none)',
                'campaign' => $campaign ?? '(none)',
            ];
        }

        if ($gclid) {
            return ['source' => 'google', 'medium' => $medium ?? 'cpc', 'campaign' => $campaign ?? '(none)'];
        }

        if ($fbclid) {
            return ['source' => 'facebook', 'medium' => $medium ?? 'paid', 'campaign' => $campaign ?? '(none)'];
        }

        $host = static::cleanHost($refHost);

        if ($host === null) {
            return ['source' => 'direct', 'medium' => '(none)', 'campaign' => '(none)'];
        }

        $name = static::normalizeSource(static::hostToName($host));

        if (in_array($name, static::SEARCH, true)) {
            $medium = 'organic';
        } elseif (in_array($name, static::SOCIAL, true)) {
            $medium = 'social';
        } else {
            $medium = 'referral';
        }

        return ['source' => $name, 'medium' => $medium, 'campaign' => '(none)'];
    }

    public static function build(\DateTimeInterface $from, \DateTimeInterface $to, string $group): array
    {
        $rows = [];
        $blank = fn () => [
            'sessions' => 0, 'visitors' => 0, 'calls' => 0, 'whatsapp' => 0, 'directions' => 0,
            'enquiries' => 0, 'payments' => 0, 'revenue' => 0.0,
        ];
        $add = function (array $ch, string $field, $n) use (&$rows, $blank, $group) {
            $key = $group === 'campaign'
                ? $ch['campaign'] . '|' . $ch['source'] . '|' . $ch['medium']
                : $ch['source'] . '|' . $ch['medium'];

            if (!isset($rows[$key])) {
                $rows[$key] = $blank() + [
                    'source' => $ch['source'], 'medium' => $ch['medium'],
                    'campaign' => $group === 'campaign' ? $ch['campaign'] : null,
                ];
            }

            $rows[$key][$field] += $n;
        };

        foreach (static::sessionGroups($from, $to) as $r) {
            $ch = static::fromRow($r);
            $add($ch, 'sessions', (int) $r->sessions);
            $add($ch, 'visitors', (int) $r->visitors);
        }

        foreach (static::eventGroups($from, $to) as $r) {
            $add(static::fromRow($r), static::EVENT_COLUMNS[$r->event_type], (int) $r->n);
        }

        foreach (static::sessionlessEvents($from, $to) as $e) {
            $ch = static::attribute($e->visitor_id, $e->user_id, \Carbon\Carbon::parse($e->created_at));
            $add($ch, static::EVENT_COLUMNS[$e->event_type], 1);
        }

        foreach (static::paymentAttributions($from, $to) as $p) {
            $add($p['channel'], 'payments', 1);
            $add($p['channel'], 'revenue', $p['amount']);
        }

        foreach ($rows as &$row) {
            $row['leads'] = $row['calls'] + $row['whatsapp'] + $row['enquiries'];
            $row['lead_rate'] = $row['sessions'] > 0 ? round($row['leads'] / $row['sessions'] * 100, 1) : null;
        }
        unset($row);

        uasort($rows, fn ($a, $b) => [$b['sessions'], $b['leads']] <=> [$a['sessions'], $a['leads']]);

        $totals = array_fill_keys(['sessions', 'visitors', 'calls', 'whatsapp', 'directions', 'enquiries', 'payments', 'revenue', 'leads'], 0);

        foreach ($rows as $row) {
            foreach ($totals as $k => $_) {
                $totals[$k] += $row[$k];
            }
        }

        $totals['lead_rate'] = $totals['sessions'] > 0 ? round($totals['leads'] / $totals['sessions'] * 100, 1) : null;

        $rows = array_values($rows);
        $hidden = 0;

        if (count($rows) > static::MAX_ROWS) {
            $hidden = count($rows) - static::MAX_ROWS;
            $rows = array_slice($rows, 0, static::MAX_ROWS);
        }

        return ['rows' => $rows, 'totals' => $totals, 'hidden' => $hidden];
    }

    public static function rawColumns(string $alias): array
    {
        return [
            DB::raw("$alias.utm_source as utm_source"),
            DB::raw("$alias.utm_medium as utm_medium"),
            DB::raw("$alias.utm_campaign as utm_campaign"),
            DB::raw("LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX($alias.referrer_url, '://', -1), '/', 1), ':', 1)) as ref_host"),
            DB::raw("($alias.landing_page LIKE '%gclid=%') as gclid"),
            DB::raw("($alias.landing_page LIKE '%fbclid=%') as fbclid"),
        ];
    }

    protected static function groupBy(string $alias): array
    {
        return ['utm_source', 'utm_medium', 'utm_campaign', 'ref_host', 'gclid', 'fbclid'];
    }

    protected static function sessionGroups($from, $to)
    {
        $q = TrafficFilter::applyCleanScope(VisitorSession::query()->from('visitor_sessions as s'), 's', true)
            ->whereBetween('s.started_at', [$from, $to])
            ->select(array_merge(static::rawColumns('s'), [
                DB::raw('COUNT(*) as sessions'),
                DB::raw('COUNT(DISTINCT s.visitor_id) as visitors'),
            ]));

        return $q->groupBy(static::groupBy('s'))->get();
    }

    protected static function eventGroups($from, $to)
    {
        $q = TrafficFilter::applyCleanScope(AnalyticsEvent::query()->from('analytics_events as e'), 'e')
            ->leftJoin('visitor_sessions as s', 's.session_uuid', '=', 'e.session_uuid')
            ->whereBetween('e.created_at', [$from, $to])
            ->whereNotNull('s.id')
            ->whereIn('e.event_type', array_keys(static::EVENT_COLUMNS))
            ->select(array_merge(static::rawColumns('s'), [
                DB::raw('e.event_type as event_type'),
                DB::raw('COUNT(*) as n'),
            ]));

        return $q->groupBy(array_merge(static::groupBy('s'), ['event_type']))->get();
    }

    /** Server-recorded conversions carry no session_uuid; they are placed by visitor instead. */
    protected static function sessionlessEvents($from, $to)
    {
        return TrafficFilter::applyCleanScope(AnalyticsEvent::query()->from('analytics_events as e'), 'e')
            ->leftJoin('visitor_sessions as s', 's.session_uuid', '=', 'e.session_uuid')
            ->whereNull('s.id')
            ->whereBetween('e.created_at', [$from, $to])
            ->whereIn('e.event_type', array_keys(static::EVENT_COLUMNS))
            ->limit(5000)
            ->get(['e.event_type', 'e.visitor_id', 'e.user_id', 'e.created_at']);
    }

    protected static function paymentAttributions($from, $to): array
    {
        $payments = DB::table('payments')
            ->where('status', 1)
            ->whereBetween('updated_at', [$from, $to])
            ->get(['id', 'user_id', 'amount', 'updated_at']);

        $out = [];

        foreach ($payments as $p) {
            $out[] = [
                'channel' => static::attribute(null, (int) $p->user_id, \Carbon\Carbon::parse($p->updated_at)),
                'amount' => (float) $p->amount,
            ];
        }

        return $out;
    }

    /**
     * Channel of the visitor's most recent non-direct session in the
     * ATTRIBUTION_DAYS before $at, else their latest session of any kind.
     */
    protected static function attribute(?string $visitorId, ?int $userId, \Carbon\Carbon $at): array
    {
        $none = ['source' => '(unattributed)', 'medium' => '(none)', 'campaign' => '(none)'];

        if (!$visitorId && !$userId) {
            return $none;
        }

        $sessions = TrafficFilter::applyCleanScope(VisitorSession::query()->from('visitor_sessions as s'), 's')
            ->where(function ($q) use ($visitorId, $userId) {
                if ($visitorId) {
                    $q->orWhere('s.visitor_id', $visitorId);
                }

                if ($userId) {
                    $q->orWhere('s.user_id', $userId)
                        ->orWhereIn('s.visitor_id', DB::table('visitors')->where('user_id', $userId)->select('visitor_id'));
                }
            })
            ->where('s.started_at', '<=', $at)
            ->where('s.started_at', '>=', $at->copy()->subDays(static::ATTRIBUTION_DAYS))
            ->orderByDesc('s.started_at')
            ->select(static::rawColumns('s'))
            ->limit(50)
            ->get();

        foreach ($sessions as $s) {
            $ch = static::fromRow($s);

            if ($ch['source'] !== 'direct') {
                return $ch;
            }
        }

        return $sessions->isNotEmpty() ? static::fromRow($sessions->first()) : $none;
    }

    public static function fromRow($r): array
    {
        return static::classify($r->utm_source, $r->utm_medium, $r->utm_campaign, $r->ref_host, (bool) $r->gclid, (bool) $r->fbclid);
    }

    protected static function clean(?string $v): ?string
    {
        $v = strtolower(trim((string) $v));

        return $v === '' ? null : $v;
    }

    protected static function cleanHost(?string $host): ?string
    {
        $host = static::clean($host);

        if ($host === null) {
            return null;
        }

        $host = preg_replace('/^(www|m|l|lm|mobile)\./', '', $host);

        if ($host === 'accounts.google.com') {
            return null;
        }
        $own = parse_url((string) config('app.url'), PHP_URL_HOST);
        $own = $own ? preg_replace('/^www\./', '', strtolower($own)) : 'servicebazzar.com';

        if ($host === $own || str_ends_with($host, '.' . $own)) {
            return null;
        }

        return $host;
    }

    protected static function hostToName(string $host): string
    {
        if (isset(static::ALIASES[$host])) {
            return $host;
        }

        foreach (explode('.', $host) as $label) {
            if ($label === 'googlequicksearchbox') {
                return 'google';
            }

            if (in_array($label, array_merge(static::SEARCH, static::SOCIAL), true)) {
                return $label;
            }
        }

        return $host;
    }

    protected static function normalizeSource(string $source): string
    {
        return static::ALIASES[$source] ?? $source;
    }
}
