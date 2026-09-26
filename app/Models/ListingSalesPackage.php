<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One sales package (SB Verified, a banner, a Top position…) bought for one
 * listing. Written when its payment is credited.
 */
class ListingSalesPackage extends Model
{
    protected $fillable = [
        'listing_id', 'certificate_package_id', 'payment_id', 'plan', 'price',
        'city', 'area', 'position', 'days', 'asset', 'starts_at', 'expires_at',
    ];

    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];

    public function package()
    {
        return $this->belongsTo(CertificatePackage::class, 'certificate_package_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function assetUrl(): ?string
    {
        return $this->asset ? asset(\App\Support\SalesPerks::DIR . '/' . $this->asset) : null;
    }

    public function isActive(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }
}
