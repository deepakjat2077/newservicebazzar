<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

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