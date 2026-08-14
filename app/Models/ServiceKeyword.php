<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceKeyword extends Model
{
    use HasFactory;

    protected $table = 'service_keywords';

    protected $fillable = [
        'category_id',
        'keyword',
        'slug',
        'image',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'description',
        'status',
    ];

    /**
     * Category Relation
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}