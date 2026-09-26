<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingKycDocument extends Model
{
    protected $fillable = ['listing_id', 'doc_type', 'doc_number', 'path', 'status', 'ai_check', 'ai_checked_at'];

    protected $casts = [
        'ai_check' => 'array',
        'ai_checked_at' => 'datetime',
    ];
}
