<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingLike extends Model
{
    protected $table = 'rating_likes';

    protected $fillable = [
        'rating_id',
        'user_id',
    ];

    public function rating()
    {
        return $this->belongsTo(Rating::class, 'rating_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}