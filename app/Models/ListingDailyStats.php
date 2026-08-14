<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingDailyStats extends Model
{
    protected $table = 'listing_daily_stats';

    protected $fillable = [
        'listing_id', 'date', 'views', 'unique_views',
        'whatsapp_clicks', 'phone_clicks', 'website_clicks', 'direction_clicks',
        'gallery_clicks', 'review_clicks', 'share_clicks', 'favourite_clicks',
        'avg_time_seconds',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function getRepeatViewsAttribute()
    {
        return max(0, (int) $this->views - (int) $this->unique_views);
    }
}
