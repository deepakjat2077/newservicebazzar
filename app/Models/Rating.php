<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    /**
     * A new, edited or removed rating changes the stars shown on the homepage
     * card and the listing's pages: refresh their cached HTML straight away.
     */
    protected static function booted()
    {
        $refresh = fn (Rating $r) => \App\Support\ListingPageCache::forget(Listing::find($r->listing_id));

        static::saved($refresh);
        static::deleted($refresh);
    }

    protected $fillable = [
        'listing_id',
        'user_id',
        'name',
        'email',
        'ratings',
        'review',
        'is_verified',
        'likes_count',
        'comments_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function likes()
    {
        return $this->hasMany(RatingLike::class, 'rating_id');
    }

    public function comments()
    {
        return $this->hasMany(RatingComment::class, 'rating_id');
    }
}