<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A "Report a Bug" / "Suggest a Feature" submission from the mobile app.
 */
class AppFeedback extends Model
{
    protected $fillable = [
        'user_id', 'type', 'subject', 'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
