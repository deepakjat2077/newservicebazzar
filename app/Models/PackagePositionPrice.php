<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePositionPrice extends Model
{
    protected $fillable = ['certificate_package_id', 'city', 'area', 'tier', 'position', 'price', 'days'];

    public function package()
    {
        return $this->belongsTo(CertificatePackage::class, 'certificate_package_id');
    }
}
