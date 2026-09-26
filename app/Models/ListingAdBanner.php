<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One rotational banner bought for a listing. Created (status 0) when its
 * payment is credited; goes live once the buyer fills in the details and the
 * creative is generated.
 */
class ListingAdBanner extends Model
{
    public const PENDING = 0;
    public const LIVE = 1;
    public const PAUSED = 2;

    protected $fillable = [
        'listing_id', 'certificate_package_id', 'listing_sales_package_id',
        'city', 'area', 'category_id', 'title', 'subtitle', 'phone', 'photo', 'image', 'image_mobile',
        'status', 'starts_at', 'expires_at',
    ];

    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function package()
    {
        return $this->belongsTo(CertificatePackage::class, 'certificate_package_id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('assets/images/ad_banners/' . $this->image) : null;
    }

    public function mobileImageUrl(): ?string
    {
        return $this->image_mobile ? asset('assets/images/ad_banners/' . $this->image_mobile) : null;
    }

    /** Public page of the business this banner advertises. */
    public function listingUrl(): ?string
    {
        $listing = $this->listing;

        if (!$listing || !$listing->slug) {
            return null;
        }

        $category = $listing->category->slug ?? optional($listing->categories->first())->slug;

        return $category
            ? route('listings.details', ['category' => $category, 'slug' => $listing->slug])
            : null;
    }

    public function isLive(): bool
    {
        return $this->status === self::LIVE
            && $this->expires_at && $this->expires_at->isFuture();
    }

    public function daysLeft(): int
    {
        return $this->expires_at ? (int) max(0, ceil(now()->diffInDays($this->expires_at, false))) : 0;
    }

    /** "2 days 4 hours left", "6 hours 20 minutes left", or "expired". */
    public function timeLeft(): string
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 'expired';
        }

        $minutes = (int) floor(now()->diffInMinutes($this->expires_at));
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days) {
            $parts[] = $days . ' day' . ($days > 1 ? 's' : '');
        }
        if ($hours) {
            $parts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        if (!$days && $mins) {
            $parts[] = $mins . ' minute' . ($mins > 1 ? 's' : '');
        }

        return ($parts ? implode(' ', $parts) : 'less than a minute') . ' left';
    }
}
