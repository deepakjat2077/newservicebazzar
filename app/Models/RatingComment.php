<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingComment extends Model
{
    protected $table = 'rating_comments';

    protected $fillable = [
        'rating_id',
        'user_id',
        'comment',
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