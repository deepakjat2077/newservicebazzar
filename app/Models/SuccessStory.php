<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    protected $fillable = [
        'client_name',
        'business_name',
        'image',
        'rating',
        'story',
        'sort_order',
        'status',
    ];
}
