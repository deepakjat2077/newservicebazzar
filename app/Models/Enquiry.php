<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = [
        'listing_id',
        'author_id',
        'category_id',
        'name',
        'phone',
        'email',
        'location_id',
        // Missing here, so the admin panel's Mark as read / unread called
        // update(['is_read' => …]) and Eloquent dropped the one attribute it
        // was changing — no query, no error, and a "marked as read" message
        // either way. The flag never moved.
        'is_read',
        'dedupe_key',
    ];

    protected $casts = [
        'is_read' => 'boolean',
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