<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id', 'owner_id', 'guest_user_id', 'status', 'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function guestUser()
    {
        return $this->belongsTo(GuestUser::class);
    }

    public function messages()
    {
        return $this->hasMany(GuestMessage::class, 'conversation_id');
    }
}
