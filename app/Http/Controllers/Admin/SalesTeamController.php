<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesGenioController;
use App\Models\ListingDisposition;
use App\Models\SalesUser;
use App\Models\SalesUserLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Admin > Sales Team Report: each SB Sales rep's day (login place, last known
 * place, dispositions) and month (same figures as the rep's own dashboard),
 * plus a per-rep timeline on a map. Locations come from SalesGeoBeacon.
 */
class SalesTeamController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->date($request);

        $reps = SalesUser::orderBy('name')->get(['id', 'name', 'empcode', 'city', 'status']);

        $rows = $reps->map(function (SalesUser $rep) use ($date) {
            $day = SalesUserLocation::where('sales_user_id', $rep->id)->whereDate('created_at', $date);

            return [
                'rep'          => $rep,
                'login'        => (clone $day)->where('event', 'login')->oldest('id')->first(),
                'last'         => (clone $day)->latest('id')->first(),
                'points'       => (clone $day)->where('status', 'ok')->count(),
                'dispositions' => ListingDisposition::where('sales_user_id', $rep->id)->whereDate('created_at', $date)->count(),
                'month'        => SalesGenioController::dashboardStats($rep->id),
            ];
        });

        // Latest known point per rep for the day, for the map at the top.
        $mapPoints = $rows->filter(fn ($r) => $r['last'] && $r['last']->hasPoint())
            ->map(fn ($r) => [
                'name'  => $r['rep']->name,
                'lat'   => $r['last']->lat,
                'lng'   => $r['last']->lng,
                'place' => $r['last']->label(),
                'time'  => $r['last']->created_at->format('h:i A'),
            ])->values();

        return view('admin.sales_team.index', compact('rows', 'date', 'mapPoints'));
    }

    public function show(Request $request, $id)
    {
        $rep = SalesUser::findOrFail($id);
        $date = $this->date($request);

        $locations = SalesUserLocation::where('sales_user_id', $rep->id)
            ->whereDate('created_at', $date)
            ->orderBy('id')
            ->get();

        $dispositions = ListingDisposition::with(['listing:id,name,mobile,city', 'location'])
            ->where('sales_user_id', $rep->id)
            ->whereDate('created_at', $date)
            ->orderBy('id')
            ->get();

        $track = $locations->filter->hasPoint()->map(fn ($l) => [
            'lat'   => $l->lat,
            'lng'   => $l->lng,
            'event' => $l->event,
            'place' => $l->label(),
            'time'  => $l->created_at->format('h:i A'),
        ])->values();

        $dispoPoints = $dispositions->filter(fn ($d) => $d->location && $d->location->hasPoint())
            ->map(fn ($d) => [
                'lat'   => $d->location->lat,
                'lng'   => $d->location->lng,
                'label' => $d->disposition . ' — ' . (optional($d->listing)->name ?? 'Business'),
                'time'  => $d->created_at->format('h:i A'),
            ])->values();

        $month = SalesGenioController::dashboardStats($rep->id);

        return view('admin.sales_team.show', compact('rep', 'date', 'locations', 'dispositions', 'track', 'dispoPoints', 'month'));
    }

    private function date(Request $request): Carbon
    {
        try {
            return $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : now()->startOfDay();
        } catch (\Throwable $e) {
            return now()->startOfDay();
        }
    }
}
