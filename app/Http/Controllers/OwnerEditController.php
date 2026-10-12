<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets a business owner use the SB Sales edit pages (name, address, timings,
 * photos …) for their own listing, so both sides see the same screens.
 *
 * Each owner route names the SalesGenioController method it stands for; this
 * checks the listing belongs to the signed-in user, marks the request as
 * "owner mode" (sg_route() then builds owner URLs in those views) and hands
 * over to the sales method unchanged.
 */
class OwnerEditController extends Controller
{
    public function proxy(Request $request, $id)
    {
        Listing::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $method = $request->route('sg');
        abort_unless(is_string($method) && method_exists(SalesGenioController::class, $method), 404);

        $request->attributes->set('sg_owner', true);

        $params = $request->route()->parameters();
        unset($params['sg']);

        $response = app()->call([app(SalesGenioController::class), $method], $params);

        // Sales pages finish on the sales profile; an owner goes back to theirs.
        if ($response instanceof RedirectResponse
            && preg_match('#/sb-sales/business/' . (int) $id . '/?$#', $response->getTargetUrl())) {
            $response->setTargetUrl(route('user.listing.profile', $id));
        }

        return $response;
    }
}
