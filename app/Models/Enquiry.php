<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected static function booted(): void
    {
        // A deleted enquiry takes its "New enquiry" notification with it, so
        // the vendor's bell never points at a lead that no longer exists.
        static::deleted(function (Enquiry $enquiry) {
            AppNotification::where('enquiry_id', $enquiry->id)->delete();
        });

        // A new FORM enquiry (type 'enquiry' — never the 'call' or 'whatsapp'
        // lead types) is forwarded on WhatsApp to the business(es) it is for,
        // so vendors get the lead instantly. All guards live in
        // queueEnquiryForward().
        static::created(function (Enquiry $enquiry) {
            // A website/app FORM enquiry is created WITHOUT a type — the DB
            // column defaults to 'enquiry', so the model attribute is null at
            // this point. Only 'call' and 'whatsapp' leads set a type
            // explicitly, and those must NOT forward. So forward unless it is
            // one of those two (null / 'enquiry' / anything else = a lead).
            if (!in_array($enquiry->type, ['call', 'whatsapp'], true)) {
                static::queueEnquiryForward($enquiry);
            }
        });
    }

    /**
     * Forward a new form enquiry to the business(es) it is for, on WhatsApp.
     * afterResponse (the queue can't load new job classes — same reason the
     * listing-approval sender uses it), never in console, only when Gupshup is
     * configured. Targets: an enquiry about a specific listing goes to that
     * business only; a general enquiry goes to EVERY status=1 listing in its
     * category + city (capped at 200, featured first). Without both a category
     * and a city it is too broad to blast, so it is skipped. One copy per
     * business, and only to listings that have a usable WhatsApp number.
     */
    protected static function queueEnquiryForward(self $enquiry): void
    {
        if (app()->runningInConsole() || in_array($enquiry->type, ['call', 'whatsapp'], true)) {
            return;
        }

        $id = $enquiry->id;

        dispatch(function () use ($id) {
            $enquiry = static::find($id);
            if (!$enquiry || in_array($enquiry->type, ['call', 'whatsapp'], true)) {
                return;
            }

            $sender = app(\App\Services\WhatsappSender::class);
            if (!$sender->isConfigured()) {
                return;
            }

            $template = $sender->enquiryTemplateId();
            if (!$template) {
                return;
            }

            if ($enquiry->listing_id) {
                $listings = Listing::where('id', $enquiry->listing_id)->where('status', 1)->get();
            } elseif ($enquiry->category_id && $enquiry->location_id) {
                $listings = Listing::where('status', 1)
                    ->where('category_id', $enquiry->category_id)
                    ->where('location_id', $enquiry->location_id)
                    ->orderByDesc('is_featured')
                    ->limit(200)
                    ->get();
            } else {
                return; // no specific business and no city — too broad to blast
            }

            foreach ($listings as $listing) {
                // One copy of this enquiry per business, ever.
                $already = WhatsappMessage::outbound()
                    ->where('enquiry_id', $enquiry->id)
                    ->where('listing_id', $listing->id)
                    ->where('status', '!=', 'failed')
                    ->exists();
                if ($already) {
                    continue;
                }

                if (!\App\Services\WhatsappSender::listingPhone($listing)) {
                    continue;
                }

                $params = \App\Services\WhatsappSender::enquiryParams($enquiry, $listing);
                $sender->sendToListing($listing, $template, $params, [
                    'enquiry_id' => $enquiry->id,
                    'auto'       => 'enquiry_forward',
                ]);

                usleep(200000); // ~0.2s between sends, to respect Gupshup limits
            }
        })->afterResponse();
    }

    protected $fillable = [
        'type',
        'listing_id',
        'author_id',
        'category_id',
        'name',
        'phone',
        'email',
        'message',
        'location_id',
        // Missing here, so the admin panel's Mark as read / unread called
        // update(['is_read' => …]) and Eloquent dropped the one attribute it
        // was changing — no query, no error, and a "marked as read" message
        // either way. The flag never moved.
        'is_read',
        'responded_at',
        'is_starred',
        'is_hidden',
        'note',
        'reminder_at',
        'dedupe_key',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_hidden' => 'boolean',
        'reminder_at' => 'datetime',
        'responded_at' => 'datetime',
    ];


    /**
     * The value that makes one enquiry "the same enquiry" as another.
     *
     * Two scopes, because a visitor may enquire in two different ways:
     *
     *  - about a listing — one enquiry per person per listing, whatever
     *    category or location the form happened to carry alongside it;
     *  - about a category in a place — Physiotherapists in Jaipur once.
     *    Physiotherapists in Jodhpur is a different place, and Dentists in
     *    Jaipur is a different category, so both of those still go through.
     *
     * A missing category or location is spelled '0' rather than left out, so
     * the case from the report — an enquiry carrying only a location — has a
     * key of its own and repeats of it collide like anything else.
     *
     * Phone is reduced to the last ten digits first: the same person typing
     * +91 74178 16381 one day and 07417816381 the next is one person, and
     * without this they would be two.
     *
     * Returns null when there is no usable phone number — nothing to identify
     * the sender by, so the row is stored unconstrained rather than colliding
     * with unrelated enquiries.
     */
    public static function dedupeKeyFor($phone, $listingId, $categoryId, $locationId): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) < 6) {
            return null;
        }

        $digits = substr($digits, -10);

        $scope = $listingId
            ? 'listing:'.$listingId
            : 'category:'.((int) $categoryId).':location:'.((int) $locationId);

        return md5($digits.'|'.$scope);
    }

    public function listing()
{
    return $this->belongsTo(Listing::class, 'listing_id');
}

public function location()
{
    return $this->belongsTo(Location::class, 'location_id');
}

public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}
}