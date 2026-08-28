<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An in-app notification for one mobile-app user. Broadcasts are done by
 * creating one row per recipient, so `is_read` is simply per-row.
 */
class AppNotification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'body', 'image', 'link', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
