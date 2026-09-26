<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'certificate_package_id', 'name', 'plan',
        'price', 'days', 'is_free', 'meta',
    ];

    protected $casts = ['meta' => 'array', 'is_free' => 'boolean'];

    public function package()
    {
        return $this->belongsTo(CertificatePackage::class, 'certificate_package_id');
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}
