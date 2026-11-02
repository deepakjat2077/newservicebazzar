<?php

namespace App\Http\Controllers;

use App\Events\AdminNotification;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Rules\IndianMobile;
use App\Services\ExotelService;
use App\Support\AdminPush;
use App\Support\MobileNumber;
use App\Support\OtpGate;
use App\Support\VendorEnquiryNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * "Contact" on a listing card.
 *
 * The card used to print the business's number, so the most valuable thing on
 * a listing page — somebody who wants to ring this business right now — left
 * no trace: the visitor dialled from their own phone and neither the business
 * nor the admin panel ever heard about it. The number is now behind a button:
 * the visitor proves a mobile number over WhatsApp OTP, the lead is stored and
 * the owner is told, and only then is the call put through.
 *
 * The OTP itself is the one the enquiry forms already use (EnquiryOtpController
 * + OtpGate), so a visitor who has just verified for an enquiry is not asked
 * twice — and nothing here can be reached by skipping that step, because the
 * proof is checked again on this side.
 *
 * @see \App\Support\OtpGate
 * @see resources/views/themes/servicebazzar/partials/contact-call.blade.php
 */
class ContactCallController extends Controller
{
    public function connect(Request $request)
    {
        $request->validate([
            'listing_id' => ['required', 'integer'],
            'phone'      => ['required', 'string', 'max:20', new IndianMobile()],
        ]);

        if (!config('contact_call.enabled')) {
            return response()->json([
                'status'  => false,
                'message' => 'This feature is not available right now.',
            ], 404);
        }

        // The same check the enquiry forms sit behind. The popup walks the
        // visitor through it, but a number that never answered a code must not
        // be able to get a business's phone ringing by posting here directly.
        if (!OtpGate::isVerified($request->phone)) {
            return response()->json([
                'status'  => false,
                'message' => 'Please verify your mobile number first.',
            ], 422);
        }

        $listing = Listing::with(['user', 'category', 'location'])
            ->where('status', 1)
            ->find($request->listing_id);

        if (!$listing) {
            return response()->json([
                'status'  => false,
                'message' => 'That business is no longer listed.',
            ], 404);
        }

        $vendorNumber = $listing->mobile_numbers[0] ?? $listing->landline_numbers[0] ?? null;

        if (empty($vendorNumber)) {
            return response()->json([
                'status'  => false,
                'message' => 'This business has not given a contact number.',
            ], 422);
        }

        $enquiry = $this->storeLead($request, $listing);

        // One OTP, one contact — the same rule the enquiry forms follow. The
        // code was spent the moment the business's phone was about to ring.
        OtpGate::consume($request->phone);

        $connected = $this->placeCall($listing, $request->phone, $vendorNumber);

        \App\Support\Tracking\Tracker::conversion($request, 'contact_call', [
            'listing_id' => $listing->id,
        ]);

        if ($connected) {
            return response()->json([
                'status'    => true,
                'connected' => true,
                'message'   => 'Connecting your call. Your phone will ring in a few seconds — please answer it.',
            ]);
        }

        // No bridged call for this listing, so hand over the number itself.
        // The lead is already stored either way, which is the point of the
        // whole step; the visitor still gets what they came for.
        return response()->json([
            'status'    => true,
            'connected' => false,
            'number'    => MobileNumber::national($vendorNumber),
            'tel'       => 'tel:' . preg_replace('/[^0-9+]/', '', (string) $vendorNumber),
            'message'   => 'Verified. Here is the number — tap to call.',
            'enquiry'   => (bool) $enquiry,
        ]);
    }

    /**
     * Store the lead and tell the admin panel and the listing's owner.
     *
     * Deliberately never fatal: a lead that could not be written must not stop
     * the call the visitor asked for. It is logged instead.
     */
    private function storeLead(Request $request, Listing $listing): ?Enquiry
    {
        $phone = MobileNumber::national($request->phone);

        try {
            // Two taps on one button, a double-submit, a retry after a dropped
            // response — all the same lead. Held in the shared cache so the
            // second one lands on whichever app server serves it.
            $guard = 'contact-call:' . MobileNumber::digits($phone) . ':' . $listing->id;

            if (!Cache::add($guard, 1, 60)) {
                return null;
            }

            $repeatMinutes = (int) config('contact_call.repeat_minutes', 1440);

            if ($repeatMinutes > 0) {
                $repeat = Enquiry::where('type', 'call')
                    ->where('listing_id', $listing->id)
                    ->where('phone', $phone)
                    ->where('created_at', '>=', now()->subMinutes($repeatMinutes))
                    ->exists();

                if ($repeat) {
                    return null;
                }
            }

            $enquiry = Enquiry::create([
                'type'        => 'call',
                'listing_id'  => $listing->id,
                'category_id' => $listing->category_id,
                'location_id' => $listing->location_id,
                'name'        => $this->callerName($phone),
                'phone'       => $phone,
                'message'     => 'Asked to be connected by call from the ' . $listing->name . ' listing.',
                'is_read'     => 0,
                // Not Enquiry::dedupeKeyFor(): that key is one row per person
                // per listing for all time, and somebody ringing a plumber
                // again next month is a second lead, not a repeat of the
                // first. The repeat window above is what dedupes a call.
                'dedupe_key'  => md5('contact-call|' . $phone . '|' . $listing->id . '|' . now()->format('YmdHis')),
            ]);
        } catch (\Throwable $e) {
            Log::error('Contact-call lead could not be stored', [
                'listing_id' => $listing->id,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }

        try {
            event(new AdminNotification('enquiry', Enquiry::where('is_read', 0)->count()));

            AdminPush::send(
                'New Call Enquiry',
                $enquiry->name . ' asked to be connected to ' . $listing->name . '.',
                '/admin/enquiries'
            );

            VendorEnquiryNotifier::notify($listing, [
                'name'       => $enquiry->name,
                'phone'      => $phone,
                'category'   => optional($listing->category)->name,
                'location'   => optional($listing->location)->name,
                'message'    => $enquiry->message,
                'enquiry_id' => $enquiry->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Contact-call notification failed', [
                'enquiry_id' => $enquiry->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return $enquiry;
    }

    /**
     * What to call this lead in the admin list.
     *
     * The popup asks for a number and nothing else — one field is the whole
     * reason a visitor goes through with it — so the name is whatever we
     * already know about that number, and a plain label when we know nothing.
     */
    private function callerName(string $phone): string
    {
        if ($user = auth()->user()) {
            $name = trim((string) $user->fullname());

            if ($name !== '') {
                return mb_substr($name, 0, 120);
            }
        }

        $known = MobileNumber::findUser($phone);

        if ($known && trim((string) $known->fullname()) !== '') {
            return mb_substr(trim((string) $known->fullname()), 0, 120);
        }

        $past = Enquiry::whereIn('phone', MobileNumber::variants($phone))
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->latest('id')
            ->value('name');

        return $past ? mb_substr($past, 0, 120) : 'Caller ' . MobileNumber::national($phone);
    }

    /**
     * Ring the visitor, then the business, with neither seeing the other's
     * number. Returns false when this listing is outside the Exotel pilot or
     * Exotel would not take the call — the popup then shows the number.
     */
    private function placeCall(Listing $listing, string $visitorPhone, string $vendorNumber): bool
    {
        if (!config('contact_call.bridge') || !self::bridgeEligible($listing)) {
            return false;
        }

        try {
            $result = app(ExotelService::class)->initiateCall(
                ExotelService::formatPhoneNumber($visitorPhone),
                ExotelService::formatPhoneNumber($vendorNumber)
            );
        } catch (\Throwable $e) {
            Log::warning('Contact-call bridge failed', [
                'listing_id' => $listing->id,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }

        if (empty($result['success'])) {
            Log::warning('Contact-call bridge refused', [
                'listing_id' => $listing->id,
                'error'      => $result['error'] ?? null,
            ]);

            return false;
        }

        Log::info('Contact-call bridged', [
            'listing_id' => $listing->id,
            'call_sid'   => $result['call_sid'] ?? null,
        ]);

        return true;
    }

    /**
     * The Exotel pilot's own limits, read straight from config/exotel.php so
     * this and the listing page's "Call Now" button can never disagree about
     * which listings a bridged call is offered on. Empty lists = every
     * listing, which is what widening the pilot looks like.
     */
    public static function bridgeEligible(?Listing $listing): bool
    {
        if (!$listing || !config('exotel.enabled') || empty($listing->mobile_numbers[0])) {
            return false;
        }

        $categoryIds = config('exotel.restricted_category_ids', []);
        $locationIds = config('exotel.restricted_location_ids', []);

        return (empty($categoryIds) || in_array((string) $listing->category_id, $categoryIds, true))
            && (empty($locationIds) || in_array((string) $listing->location_id, $locationIds, true));
    }
}
