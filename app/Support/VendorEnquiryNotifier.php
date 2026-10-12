<?php

namespace App\Support;

use App\Models\Listing;
use App\Models\Location;
use App\Models\WebPushSubscription;
use App\Services\NotificationService;
use App\Services\VapidService;
use Illuminate\Support\Facades\Log;

/**
 * Tells a listing's owner about a new enquiry with the whole enquiry in it —
 * who, their number, what they want and where — on every channel they have:
 *
 *   - the bell in the site header (app_notifications)
 *   - the Service Bazzar mobile app (FCM, sent by NotificationService)
 *   - browser notifications on each desktop/phone browser they turned them on in
 *
 * Email stays with the callers that already send it.
 */
class VendorEnquiryNotifier
{
    /**
     * $info: name, phone, email, category (name), location (name or id), message,
     *        enquiry_id (the saved enquiry — no notification without one)
     */
    public static function notify(Listing $listing, array $info): void
    {
        $listing->loadMissing(['user', 'category']);
        $owner = $listing->user;

        if (!$owner) {
            return;
        }

        // Only a lead that is really on the vendor's Leads page is announced.
        $enquiryId = $info['enquiry_id'] ?? null;
        if (!$enquiryId || !\App\Models\Enquiry::whereKey($enquiryId)->exists()) {
            Log::warning('Enquiry notification skipped: enquiry not saved', ['listing_id' => $listing->id, 'enquiry_id' => $enquiryId]);
            return;
        }

        $link  = route('user.listing.leads', $listing->id);
        $title = 'New enquiry — ' . $listing->name;
        $body  = self::body($listing, $info);

        try {
            app(NotificationService::class)->send($owner, $title, $body, [
                'type'       => 'enquiry',
                'listing_id' => (string) $listing->id,
                'link'       => $link,
                'enquiry_id' => $enquiryId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Enquiry in-app/FCM notification failed', ['listing_id' => $listing->id, 'error' => $e->getMessage()]);
        }

        $subscriptions = WebPushSubscription::where('user_id', $owner->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $vapid   = app(VapidService::class);
        $payload = ['title' => $title, 'body' => $body, 'url' => $link];

        foreach ($subscriptions as $sub) {
            try {
                $vapid->send(['endpoint' => $sub->endpoint, 'p256dh' => $sub->p256dh, 'auth' => $sub->auth], $payload);
            } catch (\Throwable $e) {
                Log::warning('Enquiry web push failed', ['sub_id' => $sub->id, 'error' => $e->getMessage()]);
            }
        }
    }

    /** The enquiry's details as a small table for the owner's email: who, number, email, what, where, when, message. */
    public static function emailTable(Listing $listing, array $info): string
    {
        $listing->loadMissing('category');

        $location = $info['location'] ?? null;
        if (is_numeric($location)) {
            $location = Location::whereKey($location)->value('name');
        }

        $rows = [
            'Name'        => trim((string) ($info['name'] ?? '')),
            'Mobile'      => trim((string) ($info['phone'] ?? '')),
            'Email'       => trim((string) ($info['email'] ?? '')),
            'Looking for' => trim((string) (($info['category'] ?? null) ?: optional($listing->category)->name)),
            'Location'    => trim((string) ($location ?: $listing->city)),
            'Received'    => now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d M Y, h:i A'),
            'Message'     => trim(preg_replace('/\s+/', ' ', (string) ($info['message'] ?? ''))),
        ];

        $html = '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:6px 0 16px;font-size:14px;min-width:280px;">';
        foreach ($rows as $label => $value) {
            if ($value === '') {
                continue;
            }
            if ($label === 'Mobile') {
                $digits = preg_replace('/\D+/', '', $value);
                $value = '<a href="tel:' . e($digits) . '" style="color:#1a73e8;text-decoration:none;">' . e($value) . '</a>';
            } elseif ($label === 'Email') {
                $value = '<a href="mailto:' . e($value) . '" style="color:#1a73e8;text-decoration:none;">' . e($value) . '</a>';
            } else {
                $value = e($value);
            }
            $html .= '<tr><td style="padding:6px 14px 6px 0;color:#6b7285;white-space:nowrap;vertical-align:top;">' . e($label) . '</td>'
                . '<td style="padding:6px 0;color:#12203f;font-weight:600;">' . $value . '</td></tr>';
        }

        return $html . '</table>';
    }

    /** "Ravi Kumar · 98xxxxxx10 / Looking for: Hotel in Jaipur / "message"" */
    public static function body(Listing $listing, array $info): string
    {
        $name  = trim((string) ($info['name'] ?? '')) ?: 'A customer';
        $phone = trim((string) ($info['phone'] ?? ''));
        $email = trim((string) ($info['email'] ?? ''));

        $location = $info['location'] ?? null;
        if (is_numeric($location)) {
            $location = Location::whereKey($location)->value('name');
        }
        $location = trim((string) ($location ?: $listing->city));

        $category = trim((string) (($info['category'] ?? null) ?: optional($listing->category)->name));

        $lines = [$name . ($phone !== '' ? ' · ' . $phone : '') . ($email !== '' ? ' · ' . $email : '')];

        if ($category !== '' || $location !== '') {
            $lines[] = 'Looking for: ' . trim($category . ($location !== '' ? ' in ' . $location : ''));
        }

        $message = trim(preg_replace('/\s+/', ' ', (string) ($info['message'] ?? '')));
        if ($message !== '') {
            $lines[] = '"' . \Illuminate\Support\Str::limit($message, 140) . '"';
        }

        return implode("\n", $lines);
    }
}
