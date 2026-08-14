<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestOtp extends Model
{
    use HasFactory;

    // 'attempts' is deliberately excluded from mass assignment — it is only
    // ever incremented server-side, never set from request input.
    protected $fillable = [
        'guest_user_id', 'code', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function guestUser()
    {
        return $this->belongsTo(GuestUser::class);
    }
}
