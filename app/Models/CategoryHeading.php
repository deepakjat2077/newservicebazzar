<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryHeading extends Model
{
    protected $fillable = [
        'heading',
        'category_ids',
        'heading_link',
        'order_by',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}