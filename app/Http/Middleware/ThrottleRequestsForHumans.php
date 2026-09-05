<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Rate limit normal traffic but exempt verified crawlers.
 *
 * Extends Laravel's built-in ThrottleRequests to skip rate limiting
 * for requests marked as verified crawlers by AllowSearchEngineCrawlers.
 */
class ThrottleRequestsForHumans extends ThrottleRequests
{
    /**
     * Resolve the number of requests this requester is allowed to make.
     */
    protected function resolveRequestSignature($request)
    {
        // Skip rate limiting entirely for verified crawlers
        if ($request->attributes->get('is_verified_crawler')) {
            return null; // null signature means no throttling
        }

        return parent::resolveRequestSignature($request);
    }
}
