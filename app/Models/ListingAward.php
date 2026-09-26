<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingAward extends Model
{
    protected $fillable = ['listing_id', 'type', 'name', 'awarded_by', 'month', 'year', 'photo'];
}
