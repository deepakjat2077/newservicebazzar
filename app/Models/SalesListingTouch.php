<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesListingTouch extends Model
{
    protected $fillable = ['sales_user_id', 'listing_id'];
}
