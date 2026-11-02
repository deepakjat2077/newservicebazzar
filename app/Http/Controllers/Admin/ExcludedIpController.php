<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExcludedIp;
use App\Support\Tracking\TrafficFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tracking → Excluded IPs.
 *
 * Every phone and laptop on the office wifi was being counted as a visitor.
 * Admins browsing while signed in were already skipped, but a staff phone
 * that is not signed in looks like anybody else. This is where those
 * connections are named.
 *
 * Adding one does two things: nothing further is recorded from it, and what
 * it already left behind is flagged as bot traffic — the same flag every
 * analytics query filters on — so today's figures correct themselves.
 */
class ExcludedIpController extends Controller
{
    public function index(Request $request)
    {
        $rules = ExcludedIp::orderByDesc('id')->get();

        return view('admin.analytics.excluded-ips', [
            'rules'     => $rules,
            'yourIp'    => $request->ip(),
            // A home or office connection gives every device its own IPv6
            // address under one shared /64, so the prefix is what excludes
            // the wifi rather than the one phone it was read from.
            'yourRange' => self::rangeFor($request->ip()),
            'fromEnv'   => (array) config('analytics.excluded_ips', []),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'value' => ['required', 'string', 'max:64', 'unique:analytics_excluded_ips,value'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $value = trim($request->input('value'));

        if (!self::isValidRule($value)) {
            return back()->withInput()->withErrors([
                'value' => 'That is not an IP address or a range. Examples: 49.36.180.22, or 2409:4080:d3e:2fb2::/64',
            ]);
        }

        ExcludedIp::create([
            'value'    => $value,
            'label'    => $request->input('label') ?: null,
            'added_by' => auth()->guard('admin')->id(),
        ]);

        $hidden = $this->hidePast($value);

        return back()->withAlert(['success', $hidden > 0
            ? "{$value} ab count nahi hoga. Iski purani {$hidden} visits bhi reports se hata di gayi hain."
            : "{$value} ab count nahi hoga."]);
    }

    public function destroy($id)
    {
        $rule = ExcludedIp::findOrFail($id);
        $value = $rule->value;
        $rule->delete();

        // Traffic already hidden stays hidden. Un-flagging it would also
        // un-flag whatever the bot sweep caught from the same address, and
        // there is no record of which flag came from where.
        return back()->withAlert(['success', "{$value} hata diya. Ab se iski visits dobara count hongi."]);
    }

    /**
     * Flag what this rule's address already left in the reporting tables.
     *
     * Plain addresses are matched in SQL. A range cannot be, so the distinct
     * addresses are read out and tested here — tens of thousands of short
     * strings, once, on an action an admin took deliberately.
     */
    private function hidePast(string $rule): int
    {
        $ips = str_contains($rule, '/')
            ? array_values(array_filter(
                DB::table('visitor_sessions')->distinct()->pluck('ip_address')->all(),
                fn ($ip) => $ip && TrafficFilter::ipMatches($ip, $rule)
            ))
            : [$rule];

        if (!$ips) {
            return 0;
        }

        $sessions = 0;

        foreach (array_chunk($ips, 500) as $chunk) {
            $uuids = DB::table('visitor_sessions')
                ->whereIn('ip_address', $chunk)
                ->where('is_bot', false)
                ->pluck('session_uuid')
                ->all();

            if (!$uuids) {
                continue;
            }

            foreach (array_chunk($uuids, 2000) as $batch) {
                $sessions += DB::table('visitor_sessions')->whereIn('session_uuid', $batch)->update(['is_bot' => true]);
                DB::table('page_views')->whereIn('session_uuid', $batch)->where('is_bot', false)->update(['is_bot' => true]);
                DB::table('analytics_events')->whereIn('session_uuid', $batch)->where('is_bot', false)->update(['is_bot' => true]);
            }

            DB::table('visitors')->whereIn('ip_address', $chunk)->where('is_bot', false)->update(['is_bot' => true]);
        }

        // The dashboard memoises its figures for five minutes; bump the stamp
        // its cache key is built from so the admin sees the corrected numbers
        // straight away rather than the ones they just complained about.
        if ($sessions > 0) {
            Cache::forever(AnalyticsController::SWEEP_STAMP_KEY, now()->getTimestamp());
        }

        return $sessions;
    }

    /** The /64 an IPv6 address sits in — the wifi, rather than the device. */
    public static function rangeFor(?string $ip): ?string
    {
        $bytes = @inet_pton((string) $ip);

        if ($bytes === false || strlen($bytes) !== 16) {
            return null;
        }

        $prefix = substr($bytes, 0, 8) . str_repeat("\0", 8);

        return inet_ntop($prefix) . '/64';
    }

    private static function isValidRule(string $value): bool
    {
        if (!str_contains($value, '/')) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        }

        [$subnet, $bits] = explode('/', $value, 2);
        $bytes = @inet_pton(trim($subnet));

        if ($bytes === false || !ctype_digit(trim($bits))) {
            return false;
        }

        return (int) $bits >= 1 && (int) $bits <= strlen($bytes) * 8;
    }
}
