<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * When a rep successfully saves a change to a business's profile, note it so
 * the business also appears under their New Business list. Campaign, cart,
 * order and follow-up actions are deliberately not edits.
 */
class RecordSalesEdit
{
    private const EDITS = [
        'sales_genio.business.name.update',
        'sales_genio.business.address.update',
        'sales_genio.business.contact.update',
        'sales_genio.business.categories.update',
        'sales_genio.business.map.update',
        'sales_genio.business.awards.update',
        'sales_genio.business.certificates.update',
        'sales_genio.business.timings.update',
        'sales_genio.business.website.update',
        'sales_genio.business.social.update',
        'sales_genio.business.establishment.update',
        'sales_genio.business.language.update',
        'sales_genio.business.photos.upload',
        'sales_genio.business.photos.delete',
        'sales_genio.business.photos.cover',
        'sales_genio.business.video.poster',
        'sales_genio.business.kyc.upload',
        'sales_genio.business.kyc.number',
        'sales_genio.business.kyc.delete',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $name = optional($request->route())->getName();

            if (in_array($name, self::EDITS, true) && $this->saved($request, $response)) {
                $repId = Auth::guard('sales')->id();
                $listingId = (int) $request->route('id');

                if ($repId && $listingId) {
                    $now = now();

                    DB::table('sales_listing_touches')->upsert(
                        [['sales_user_id' => $repId, 'listing_id' => $listingId, 'created_at' => $now, 'updated_at' => $now]],
                        ['sales_user_id', 'listing_id'],
                        ['updated_at']
                    );
                }
            }
        } catch (\Throwable $e) {
            // Never let bookkeeping break a save that has already succeeded.
            report($e);
        }

        return $response;
    }

    /** A failed validation redirects back with errors; nothing was saved then. */
    private function saved(Request $request, $response): bool
    {
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            return !is_array($data) || ($data['status'] ?? true) !== false;
        }

        $session = $request->hasSession() ? $request->session() : null;

        return !$session || (!$session->has('errors') && !$session->has('error'));
    }
}
