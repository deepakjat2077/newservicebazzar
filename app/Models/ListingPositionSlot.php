<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One top-position slot held by a business: a position, in an area, for a
 * category, until it expires. Only one business can hold a given slot.
 */
class ListingPositionSlot extends Model
{
    protected $fillable = [
        'certificate_package_id', 'listing_id', 'listing_sales_package_id', 'category_id',
        'city', 'pincode', 'area', 'tier', 'starts_at', 'expires_at',
    ];

    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function scopeLive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
