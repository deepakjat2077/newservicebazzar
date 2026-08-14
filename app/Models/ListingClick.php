<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingClick extends Model
{
    use HasFactory;

    protected $table = 'listing_clicks';

    protected $fillable = [
        'listing_id',
        'listing_name',
        'owner_name',
        'owner_number',
        'category_id',
'category_name',
'location_id',
'location_name',
        'clicked_number',
        'type',
        'ip_address',
        'user_agent',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }
}