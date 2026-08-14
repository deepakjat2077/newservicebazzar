<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    // Insert-only log — no reason to pay for an updated_at write on every event.
    const UPDATED_AT = null;

    protected $fillable = [
        'visitor_id', 'session_uuid', 'user_id',
        'event_category', 'event_type',
        'listing_id', 'category_id', 'subcategory_id', 'location_id',
        'keyword', 'results_count', 'url', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
