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
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];


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