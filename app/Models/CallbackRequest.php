<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A "Get a Callback" request from the mobile app's Help screen. Reviewed in the
 * admin panel (status 0 pending, 1 handled).
 */
class CallbackRequest extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone', 'topic', 'message', 'status', 'admin_note', 'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
