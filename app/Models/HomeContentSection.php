<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeContentSection extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
    ];
}