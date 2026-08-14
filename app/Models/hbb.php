<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hbb extends Model
{
    //
 protected $table = 'hbb';
      protected $fillable = [
        'title',
        'desktop_banner',
        'mobile_banner',
        'link',
        'sort_order',
        'status',
    ];
}


