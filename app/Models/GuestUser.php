<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestUser extends Model
{
    use HasFactory;

    // 'token' and 'email_verified_at' are deliberately excluded — they are
    // only ever set via direct property assignment during OTP verification,
    // never mass-assigned from request input.
    protected $fillable = [
        'name', 'email', 'phone', 'last_ip',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function otps()
    {
        return $this->hasMany(GuestOtp::class);
    }

    public function conversations()
    {
        return $this->hasMany(GuestConversation::class);
    }
}
