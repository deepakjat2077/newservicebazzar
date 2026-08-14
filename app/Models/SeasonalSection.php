<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalSection extends Model
{
    protected $fillable = [
        'title',
        'badge_text',
        'subtitle',
        'category_ids',
        'status',
    ];
}