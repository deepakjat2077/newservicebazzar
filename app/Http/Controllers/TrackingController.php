<?php

namespace App\Http\Controllers;

use App\Jobs\Tracking\RecordAnalyticsEvent;
use App\Jobs\Tracking\RecordPageViewClose;
use App\Jobs\Tracking\RecordPageViewOpen;
use App\Http\Middleware\EnsureVisitorId;
use App\Support\Tracking\TrafficFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Beacon endpoints for the tracking system. Deliberately do zero DB
 * queries on the request path — validate shape only (no `exists:` rules,
 * which would add a synchronous query per beacon) and dispatch a job.
 * Every payload carries a client-generated UUID as its correlation key
 * rather than relying on a server-assigned id, so the pipeline is safe
 * whether the tracking jobs run synchronously (config/queue.php default,
 * since this app has no queue worker guaranteed running) or async via a
 * real queue connection.
 *
 * Each dispatch is wrapped in try/catch: with the "sync" connection a job
 * exception would otherwise propagate straight into this HTTP response —
 * tracking must never break the page it's attached to.
 */
class TrackingController extends Controller
{
    public function pageViewOpen(Request $request)
    {
        if (TrafficFilter::shouldIgnoreRequest($request)) {
            return response()->json(null, 204);
        }

        $data = $request->validate([
            'client_page_view_id' => 'required|uuid',
            'session_uuid' => 'required|uuid',
            'url' => 'required|string|max:2048',
            'page_name' => 'nullable|string|max:100',
            'listing_id' => 'nullable|integer',
            'referrer_url' => 'nullable|string|max:2048',
            'utm_source' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'screen_resolution' => 'nullable|string|max:20',
        ]);

        $visitorId = $request->cookie(EnsureVisitorId::COOKIE_NAME);

        if (!$visitorId) {
            return response()->json(null, 204);
        }

        try {
            RecordPageViewOpen::dispatch(array_merge($data, [
                'visitor_id' => $visitorId,
                'user_id' => Auth::guard('web')->id(),
                'laravel_session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]));
        } catch (\Throwable $e) {
            Log::warning('Tracking: page-view open failed', ['error' => $e->getMessage()]);
        }

        return response()->json(null, 204);
    }

    public function pageViewClose(Request $request)
    {
        if (TrafficFilter::shouldIgnoreRequest($request)) {
            return response()->json(null, 204);
        }

        $data = $request->validate([
            'client_page_view_id' => 'required|uuid',
            'session_uuid' => 'nullable|uuid',
            'time_spent_seconds' => 'nullable|integer|min:0|max:86400',
            'is_exit' => 'nullable|boolean',
            'url' => 'nullable|string|max:2048',
            'page_name' => 'nullable|string|max:100',
        ]);

        $visitorId = $request->cookie(EnsureVisitorId::COOKIE_NAME);

        try {
            RecordPageViewClose::dispatch(array_merge($data, [
                'visitor_id' => $visitorId,
                'user_id' => Auth::guard('web')->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]));
        } catch (\Throwable $e) {
            Log::warning('Tracking: page-view close failed', ['error' => $e->getMessage()]);
        }

        return response()->json(null, 204);
    }

    public function event(Request $request)
    {
        if (TrafficFilter::shouldIgnoreRequest($request)) {
            return response()->json(null, 204);
        }

        $data = $request->validate([
            'event_category' => 'required|string|in:click,search,conversion,view',
            'event_type' => 'required|string|max:100',
            'session_uuid' => 'nullable|uuid',
            'listing_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'subcategory_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'keyword' => 'nullable|string|max:255',
            'results_count' => 'nullable|integer|min:0',
            'url' => 'nullable|string|max:2048',
            'meta' => 'nullable|array',
        ]);

        $visitorId = $request->cookie(EnsureVisitorId::COOKIE_NAME);

        if (!$visitorId) {
            return response()->json(null, 204);
        }

        try {
            RecordAnalyticsEvent::dispatch(array_merge($data, [
                'visitor_id' => $visitorId,
                'user_id' => Auth::guard('web')->id(),
                'user_agent' => $request->userAgent(),
            ]));
        } catch (\Throwable $e) {
            Log::warning('Tracking: event failed', ['error' => $e->getMessage()]);
        }

        return response()->json(null, 204);
    }
}
