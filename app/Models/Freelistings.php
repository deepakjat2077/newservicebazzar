<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Freelistings extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'contact_person',
        'pincode',
        'mobile',
        'landline',
        'email',
        'website',
        'plot_no',
        'building_name',
        'street_name',
        'landmark',
        'area',
        'city',
        'state',
        'status',
        'source',
        'sales_user_id',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}