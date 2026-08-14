<?php

namespace App\Support\Tracking;

use App\Http\Middleware\EnsureVisitorId;
use App\Jobs\Tracking\RecordAnalyticsEvent;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * One-line conversion-tracking hook for controllers. Deliberately swallows
 * any failure — tracking must never break the real feature it's attached
 * to (registration, payment, etc still succeed even if this fails).
 */
class Tracker
{
    public static function conversion(Request $request, string $eventType, array $extra = []): void
    {
        try {
            $visitorId = $request->cookie(EnsureVisitorId::COOKIE_NAME);

            if (!$visitorId) {
                return;
            }

            RecordAnalyticsEvent::dispatch(array_merge([
                'visitor_id' => $visitorId,
                'user_id' => Auth::guard('web')->id(),
                'event_category' => 'conversion',
                'event_type' => $eventType,
                'url' => $request->fullUrl(),
            ], $extra));
        } catch (\Throwable $e) {
            Log::warning('Conversion tracking failed', ['event' => $eventType, 'error' => $e->getMessage()]);
        }
    }

    /**
     * For conversions with no HTTP request from the actual visitor's
     * browser to read a cookie from — payment_success specifically fires
     * from a server-to-server IPN webhook (the gateway calling us, not the
     * customer). Resolves a best-effort visitor_id via the paying user's
     * most recently seen Visitor row; proceeds with a null visitor_id
     * (allowed by the schema) if none is found rather than dropping the
     * conversion entirely.
     */
    public static function serverConversion(string $eventType, ?int $userId, array $extra = []): void
    {
        try {
            $visitorId = $userId
                ? Visitor::where('user_id', $userId)->orderByDesc('last_visit_at')->value('visitor_id')
                : null;

            // afterCommit(): this is typically called from inside a
            // DB::transaction() (e.g. GatewayIpn::paymentSuccess()) — must
            // not become visible to a queue worker unless that transaction
            // actually commits.
            RecordAnalyticsEvent::dispatch(array_merge([
                'visitor_id' => $visitorId,
                'user_id' => $userId,
                'event_category' => 'conversion',
                'event_type' => $eventType,
            ], $extra))->afterCommit();
        } catch (\Throwable $e) {
            Log::warning('Server-side conversion tracking failed', ['event' => $eventType, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Backfill a visitor's past anonymous activity onto their new user_id
     * the moment they register/log in — cheap, indexed UPDATEs keyed on
     * visitor_id, safe to call on every login (no-ops once already linked).
     */
    public static function linkUser(Request $request, int $userId): void
    {
        try {
            $visitorId = $request->cookie(EnsureVisitorId::COOKIE_NAME);

            if (!$visitorId) {
                return;
            }

            Visitor::where('visitor_id', $visitorId)->whereNull('user_id')->update(['user_id' => $userId]);
            VisitorSession::where('visitor_id', $visitorId)->whereNull('user_id')->update(['user_id' => $userId]);
        } catch (\Throwable $e) {
            Log::warning('Visitor-user link failed', ['error' => $e->getMessage()]);
        }
    }
}
