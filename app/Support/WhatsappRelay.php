<?php

namespace App\Support;

use App\Events\AdminNotification;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Services\WhatsappSender;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp enquiries through our own Gupshup number, so we know a message
 * was really sent (a direct wa.me chat with the business is invisible to us).
 *
 *  1. A listing's WhatsApp button opens a chat with OUR number, the text
 *     pre-filled with "(Ref: SB<listing id>)".
 *  2. When that message reaches the Gupshup webhook, handleInbound() turns
 *     it into an Enquiry (type "whatsapp") with the sender's WhatsApp number
 *     and profile name. It is not stored as a chat message, so it shows in
 *     Enquiries only, not in the admin WhatsApp chats.
 *  3. After the webhook has answered, the business gets the approved enquiry
 *     template and the customer an auto-reply with the business's number.
 *
 * Switch off with WHATSAPP_RELAY=false: buttons go back to the business's own
 * WhatsApp number.
 */
class WhatsappRelay
{
    /** A repeat of the same customer + listing within this many minutes is the same enquiry. */
    private const REPEAT_MINUTES = 30;

    /** Gupshup's v2 and v3 copies of one message arrive within about a second. */
    private const DOUBLE_DELIVERY_SECONDS = 20;

    public static function enabled(): bool
    {
        return (bool) config('services.gupshup.whatsapp_relay', true) && self::ourNumber() !== '';
    }

    private static function ourNumber(): string
    {
        return preg_replace('/\D+/', '', (string) config('services.gupshup.source'));
    }

    /**
     * Link for a listing's WhatsApp button: our number with the reference
     * pre-filled, or $directUrl (the business's own chat) when the relay is off.
     */
    public static function url($listing, string $directUrl): string
    {
        if (!$listing || empty($listing->id) || !self::enabled()) {
            return $directUrl;
        }

        $service = trim((string) optional($listing->category)->name) ?: 'your services';
        $text = "Hi, I am interested in {$service} from {$listing->name}. Please share details. (Ref: SB{$listing->id})";

        return 'https://wa.me/' . self::ourNumber() . '?text=' . rawurlencode($text);
    }

    public static function listingIdFrom(string $body): ?int
    {
        return preg_match('/\bRef:?\s*SB-?(\d{1,9})\b/i', $body, $m) ? (int) $m[1] : null;
    }

    /**
     * Called by the Gupshup webhook for every incoming message. Returns true
     * when it was a relay enquiry (handled here, not to be stored as a chat).
     * Gupshup delivers the same message through both its v2 and v3 webhooks
     * and retries on timeouts, so repeats are recognised and ignored.
     */
    public static function handleInbound(string $phone, ?string $profileName, string $body): bool
    {
        $listingId = self::listingIdFrom($body);
        if (!$listingId || !self::enabled()) {
            return false;
        }

        $listing = Listing::with(['category', 'location'])->find($listingId);
        if (!$listing) {
            return false;
        }

        // The same message delivered twice (v2 + v3 webhook, about a second apart).
        if (!Cache::add('wa-relay-msg:' . md5($phone . '|' . $listingId . '|' . $body), 1, self::DOUBLE_DELIVERY_SECONDS)) {
            return true;
        }

        $lock = Cache::lock('wa-relay:' . $phone . ':' . $listingId, 20);
        if (!$lock->block(10)) {
            return true; // the other delivery of this message is handling it
        }

        try {
            $localPhone = (strlen($phone) === 12 && str_starts_with($phone, '91')) ? substr($phone, 2) : $phone;

            // The customer sent it again: answer them, but don't send the
            // business the same lead twice. 0 minutes = every message is a new lead.
            $repeatMinutes = (int) config('services.gupshup.relay_repeat_minutes', self::REPEAT_MINUTES);
            $repeat = $repeatMinutes <= 0 ? null : Enquiry::where('type', 'whatsapp')
                ->where('listing_id', $listingId)
                ->where('phone', $localPhone)
                ->where('created_at', '>=', now()->subMinutes($repeatMinutes))
                ->latest('id')->first();
            if ($repeat) {
                dispatch(function () use ($repeat, $listing, $phone) {
                    self::replyToCustomer($repeat, $listing, $phone, true);
                })->afterResponse();
                return true;
            }

            $name = trim(preg_replace('/\s+/u', ' ', (string) $profileName));
            $enquiry = Enquiry::create([
                'type'        => 'whatsapp',
                'listing_id'  => $listing->id,
                'category_id' => $listing->category_id,
                'location_id' => $listing->location_id,
                'name'        => mb_substr($name !== '' ? $name : 'WhatsApp user', 0, 120),
                'phone'       => $localPhone,
                'message'     => mb_substr($body, 0, 1000),
                'is_read'     => 0,
                'dedupe_key'  => md5('wa-relay|' . $phone . '|' . $listing->id . '|' . now()->format('YmdHis')),
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp relay enquiry could not be stored', ['listing_id' => $listingId, 'error' => $e->getMessage()]);
            return true;
        } finally {
            $lock->release();
        }

        Log::info('WhatsApp relay enquiry stored', ['enquiry_id' => $enquiry->id, 'listing_id' => $listing->id]);

        // Gupshup wants its 200 quickly; the sends can take seconds each.
        dispatch(function () use ($enquiry, $listing, $phone) {
            self::notify($enquiry, $listing, $phone);
        })->afterResponse();

        return true;
    }

    private static function notify(Enquiry $enquiry, Listing $listing, string $customerPhone): void
    {
        $sender = app(WhatsappSender::class);

        try {
            event(new AdminNotification('enquiry', Enquiry::where('is_read', 0)->count()));
            AdminPush::send('New WhatsApp Enquiry', $enquiry->name . ' messaged about ' . $listing->name . '.', '/admin/enquiries');
        } catch (\Throwable $e) {
            Log::warning('WhatsApp relay admin notification failed', ['error' => $e->getMessage()]);
        }

        // The business: the new_customer_lead template, or the older enquiry
        // template (the one admins send by hand) if that one is not set or fails.
        $leadTemplateId = trim((string) config('services.gupshup.lead_template_id'));
        $sent = null;
        if ($leadTemplateId !== '') {
            $sent = $sender->sendToListing($listing, $leadTemplateId, self::leadParams($enquiry, $listing), [
                'enquiry_id' => $enquiry->id,
            ]);
        }
        if ((!$sent || $sent->status === 'failed') && ($templateId = $sender->enquiryTemplateId())) {
            $sender->sendToListing($listing, $templateId, WhatsappSender::enquiryParams($enquiry, $listing), [
                'enquiry_id' => $enquiry->id,
            ]);
        }

        try {
            VendorEnquiryNotifier::notify($listing, [
                'name'       => $enquiry->name,
                'phone'      => $enquiry->phone,
                'category'   => optional($listing->category)->name,
                'location'   => $listing->location_id,
                'message'    => $enquiry->message,
                'enquiry_id' => $enquiry->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp relay vendor push failed', ['error' => $e->getMessage()]);
        }

        self::replyToCustomer($enquiry, $listing, $customerPhone, false);
    }

    /** Who we passed the customer to, and the number to call meanwhile. */
    private static function replyToCustomer(Enquiry $enquiry, Listing $listing, string $customerPhone, bool $alreadySent): void
    {
        $businessPhone = self::businessPhone($listing);
        $firstName = trim(explode(' ', $enquiry->name)[0] ?? '');
        $greeting = ($firstName !== '' && $enquiry->name !== 'WhatsApp user') ? 'Thank you ' . $firstName . '!' : 'Thank you!';
        $text = $greeting . ($alreadySent
                ? ' Your enquiry was already sent to ' . $listing->name . ' a few minutes ago. They will contact you soon.'
                : ' Your enquiry has been sent to ' . $listing->name . '. They will contact you soon.')
            . ($businessPhone ? "\n\nYou can also call them directly: " . $businessPhone : '')
            . "\n\n- Service Bazzar";

        app(WhatsappSender::class)->sendText($customerPhone, $text, [
            'listing_id'           => $listing->id,
            'enquiry_id'           => $enquiry->id,
            'skip_session_check'   => true, // they messaged us seconds ago
            'no_log'               => true, // keep it out of the admin chats
        ]);
    }

    /** new_customer_lead variables; WhatsApp rejects blank or multi-line values. */
    private static function leadParams(Enquiry $enquiry, Listing $listing): array
    {
        $clean = fn ($v) => ($v = trim(preg_replace('/\s+/u', ' ', (string) $v))) === '' ? '-' : $v;

        // {{1}} is the only place the business can be named, and one owner
        // often has several listings: "Contact (Business)".
        $contact = trim((string) $listing->contact_person);
        $business = trim((string) $listing->name);
        $hello = ($contact !== '' && mb_stripos($business, $contact) === false) ? $contact . ' (' . $business . ')' : $business;

        return array_map($clean, [
            $hello,
            $enquiry->name,
            strlen((string) $enquiry->phone) === 10 ? '+91 ' . $enquiry->phone : '+' . $enquiry->phone,
            optional($listing->category)->name,
            optional($listing->location)->name,
        ]);
    }

    private static function businessPhone(Listing $listing): ?string
    {
        foreach ([$listing->mobile_numbers, $listing->whatsapp_numbers, $listing->mobile] as $value) {
            if (is_string($value) && str_starts_with(trim($value), '[')) {
                $value = json_decode($value, true) ?: [];
            }
            foreach ((array) $value as $n) {
                $digits = preg_replace('/\D+/', '', (string) $n);
                if (strlen($digits) >= 10) {
                    return '+91 ' . substr($digits, -10);
                }
            }
        }
        return null;
    }
}
