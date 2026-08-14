<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Freelistings extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'pincode',
          'mobile', 
    'email',  
        'plot_no',
        'building_name',
        'street_name',
        'landmark',
        'area',
        'city',
        'state',
        'status',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}