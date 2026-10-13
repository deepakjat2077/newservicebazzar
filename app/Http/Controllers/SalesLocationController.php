<?php

namespace App\Http\Controllers;

use App\Models\SalesUserLocation;
use App\Support\ReverseGeocoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Receives the SB Sales panel's location beacon (see SalesGeoBeacon). The first
 * point of a login session is recorded as the "login" location.
 */
class SalesLocationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'status'   => 'required|in:ok,denied,unavailable,timeout',
            'lat'      => 'nullable|required_if:status,ok|numeric|between:-90,90',
            'lng'      => 'nullable|required_if:status,ok|numeric|between:-180,180',
            'accuracy' => 'nullable|integer|min:0|max:1000000',
        ]);

        $user = Auth::guard('sales')->user();
        $isLogin = !$request->session()->get('sales_geo_logged', false);
        $request->session()->put('sales_geo_logged', true);

        $ok = $data['status'] === 'ok';
        $row = SalesUserLocation::create([
            'sales_user_id' => $user->id,
            'event'         => $isLogin ? 'login' : 'ping',
            'status'        => $data['status'],
            'lat'           => $ok ? $data['lat'] : null,
            'lng'           => $ok ? $data['lng'] : null,
            'accuracy'      => $ok ? ($data['accuracy'] ?? null) : null,
            'ip'            => $request->ip(),
            'user_agent'    => mb_substr((string) $request->userAgent(), 0, 250),
        ]);

        // Place name after the response, so the rep's page never waits on it.
        if ($ok) {
            $id = $row->id;
            dispatch(function () use ($id) {
                $loc = SalesUserLocation::find($id);
                if ($loc && !$loc->place) {
                    $place = ReverseGeocoder::place($loc->lat, $loc->lng);
                    if ($place) {
                        $loc->update(['place' => $place]);
                    }
                }
            })->afterResponse();
        }

        return response()->json(['ok' => true]);
    }
}
