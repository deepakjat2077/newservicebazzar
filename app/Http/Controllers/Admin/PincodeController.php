<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Support\HttpRetry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Areas for a pincode, for the listing form's address block.
 *
 * This used to be a closure in routes/web.php that proxied
 * api.postalpincode.in straight through to the browser. That API is missing
 * plenty of real pincodes — 160043 (Chandigarh, Sector 44) among them — and a
 * miss came back as a dead "Area not found" dropdown with no way past it, so a
 * perfectly valid address simply could not be entered.
 *
 * Two things fix that. The areas already recorded against that pincode in our
 * own listings are offered alongside whatever the API knows, so the system
 * gets better at the pincodes we actually work in; and a miss is reported as a
 * miss rather than an error, letting the form fall back to a free-text box.
 */
class PincodeController extends Controller
{
    protected const ENDPOINT = 'https://api.postalpincode.in/pincode/';

    public function show(string $pin): JsonResponse
    {
        $known = $this->knownFromListings($pin);
        $remote = $this->fromPostalApi($pin);

        // API order first — those are the official post office names — then
        // anything we know that it did not return.
        $areas = collect($remote['areas'])
            ->merge($known['areas'])
            ->map(fn ($area) => trim($area))
            ->filter()
            ->unique(fn ($area) => mb_strtolower($area))
            ->values()
            ->all();

        return response()->json([
            'status'  => !empty($areas),
            'pincode' => $pin,
            'areas'   => $areas,
            'city'    => $remote['city'] ?: $known['city'],
            'state'   => $remote['state'] ?: $known['state'],
            // Lets the form say something useful rather than just going blank.
            'source'  => $this->describeSource($remote, $known),
        ]);
    }

    /**
     * Areas admins have already used for this pincode.
     *
     * Cheap, always available, and correct by definition — somebody on this
     * team typed it against a real address.
     */
    protected function knownFromListings(string $pin): array
    {
        return Cache::remember('pincode-known:' . $pin, now()->addHours(6), function () use ($pin) {
            $rows = Listing::query()
                ->where('pincode', $pin)
                ->whereNotNull('area')
                ->where('area', '<>', '')
                ->select('area', 'city', 'state')
                ->get();

            return [
                'areas' => $rows->pluck('area')
                    ->map(fn ($area) => trim($area))
                    ->filter()
                    ->unique(fn ($area) => mb_strtolower($area))
                    ->values()
                    ->all(),
                'city'  => optional($rows->firstWhere('city', '<>', ''))->city ?? '',
                // Was hardcoded to '' — the column is right there on the
                // same rows city comes from, it just was never selected or
                // read. That's why City filled in from an earlier listing
                // at this pincode but State never did, even when we had a
                // perfectly good state on file for it.
                'state' => optional($rows->firstWhere('state', '<>', ''))->state ?? '',
            ];
        });
    }

    /**
     * The official list, when the API has one.
     *
     * Cached because the postal directory does not change during a session of
     * data entry, and the same pincode gets typed repeatedly.
     */
    protected function fromPostalApi(string $pin): array
    {
        $empty = ['areas' => [], 'city' => '', 'state' => '', 'reached' => false];

        try {
            return Cache::remember('pincode-api:' . $pin, now()->addDays(30), function () use ($pin, $empty) {
                $attempt = HttpRetry::send(
                    fn () => Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->acceptJson()
                        ->connectTimeout(8)
                        ->timeout(15)
                        ->get(self::ENDPOINT . $pin),
                    2,
                    600,
                    'postalpincode',
                    20
                );

                $response = $attempt['response'];

                if ($response === null || !$response->successful()) {
                    // Not cached as a permanent answer: a network blip must not
                    // make a good pincode look empty for the next 30 days.
                    throw new \RuntimeException('postal api unreachable');
                }

                $payload = $response->json();
                $offices = data_get($payload, '0.PostOffice');

                if (data_get($payload, '0.Status') !== 'Success' || !is_array($offices)) {
                    // A genuine "no records found" — worth remembering, and
                    // worth telling apart from a network failure so the form
                    // shows the right message. (array_merge, not `+`: union
                    // keeps the left-hand `reached => false` and the flag
                    // would never flip.)
                    return array_merge($empty, ['reached' => true]);
                }

                return [
                    'areas'   => collect($offices)->pluck('Name')->filter()->values()->all(),
                    'city'    => data_get($offices, '0.District', ''),
                    'state'   => data_get($offices, '0.State', ''),
                    'reached' => true,
                ];
            });
        } catch (\Throwable $exception) {
            Log::warning('Pincode lookup failed', ['pincode' => $pin, 'error' => $exception->getMessage()]);

            return $empty;
        }
    }

    /** What the form should tell the admin about where this list came from. */
    protected function describeSource(array $remote, array $known): string
    {
        if (!empty($remote['areas'])) {
            return 'postal';
        }

        if (!empty($known['areas'])) {
            return 'listings';
        }

        return $remote['reached'] ? 'none' : 'unreachable';
    }
}
