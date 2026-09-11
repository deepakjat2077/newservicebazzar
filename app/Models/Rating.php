<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

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