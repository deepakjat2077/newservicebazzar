<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShowcaseListing extends Model
{
    protected $fillable = [
        'listing_id',
        'sort_order',
        'status',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
