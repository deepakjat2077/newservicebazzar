<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExotelService;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Exotel Bridge Call Controller
 * Handles bridge call API requests from mobile app
 */
class ExotelController extends Controller
{
    private $exotel;

    public function __construct(ExotelService $exotelService)
    {
        $this->exotel = $exotelService;
    }

    /**
     * Initiate a bridge call
     * POST /api/bridge-call
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiateCall(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'listing_id' => 'required|exists:listings,id',
                'vendor_phone' => 'required|string|min:10',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to make a call',
                ], 401);
            }

            // Get listing and verify it exists
            $listing = Listing::find($request->listing_id);
            if (!$listing || $listing->status !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or inactive',
                ], 404);
            }

            // Trial phase: bridge call is only offered on the pilot
            // category/city (currently Jaipur + IT Consultants). Every other
            // listing falls back to the plain "Call" button.
            if (!$this->isEligible($listing)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bridge call is not available for this listing yet.',
                ], 422);
            }

            // Format phone numbers
            $customerPhone = ExotelService::formatPhoneNumber($user->mobile);
            $vendorPhone = ExotelService::formatPhoneNumber($request->vendor_phone);

            Log::info('Bridge Call Request', [
                'user_id' => $user->id,
                'listing_id' => $listing->id,
                'customer_phone' => $customerPhone,
            ]);

            // Initiate call
            $result = $this->exotel->initiateCall(
                $customerPhone,
                $vendorPhone,
                [
                    'listing_id' => $listing->id,
                    'callback_url' => route('api.exotel.callback'),
                ]
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to initiate call',
                ], 503);
            }

            return response()->json([
                'success' => true,
                'message' => 'Call initiated successfully',
                'call_sid' => $result['call_sid'],
                'data' => $result['data'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Bridge Call Error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while initiating the call',
            ], 500);
        }
    }

    /**
     * Hangup an active call
     * POST /api/bridge-call/{callSid}/hangup
     *
     * @param Request $request
     * @param string $callSid
     * @return \Illuminate\Http\JsonResponse
     */
    public function hangupCall(Request $request, string $callSid)
    {
        try {
            $result = $this->exotel->hangupCall($callSid);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to hangup call',
                ], 503);
            }

            return response()->json([
                'success' => true,
                'message' => 'Call ended',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook callback from Exotel
     * POST /api/exotel/callback
     * Exotel will send call status updates here
     */
    public function callback(Request $request)
    {
        try {
            $data = $request->all();

            Log::info('Exotel Callback Received', [
                'call_sid' => $data['CallSid'] ?? null,
                'status' => $data['CallStatus'] ?? null,
                'duration' => $data['Duration'] ?? null,
                'recording_url' => $data['RecordingUrl'] ?? null,
            ]);

            // Handle callback logic here
            // - Update call status in database
            // - Store recording URL
            // - Send notifications

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Exotel Callback Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Get call details/recording
     * GET /api/bridge-call/{callSid}
     */
    public function getCallDetails(Request $request, string $callSid)
    {
        try {
            $result = $this->exotel->getCallDetails($callSid);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch call details',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Whether a listing qualifies for bridge call — lets the app decide
     * whether to show the "Call" (bridge) button before the user taps it,
     * instead of only failing on initiateCall().
     *
     * GET /api/bridge-call/eligibility/{listing_id}
     */
    public function checkEligibility(Request $request, int $listing_id)
    {
        $listing = Listing::find($listing_id);

        if (!$listing || $listing->status !== 1) {
            return response()->json([
                'success' => true,
                'eligible' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'eligible' => $this->isEligible($listing),
        ]);
    }

    /**
     * Trial-phase gate: only listings in the configured category AND
     * location get the bridge-call button. Both lists come from
     * config('exotel.restricted_*') — empty means "no restriction",
     * for when the pilot expands to more cities/categories.
     */
    private function isEligible(Listing $listing): bool
    {
        $categoryIds = config('exotel.restricted_category_ids', []);
        $locationIds = config('exotel.restricted_location_ids', []);

        if (!empty($categoryIds) && !in_array((string) $listing->category_id, $categoryIds, true)) {
            return false;
        }

        if (!empty($locationIds) && !in_array((string) $listing->location_id, $locationIds, true)) {
            return false;
        }

        return true;
    }
}
