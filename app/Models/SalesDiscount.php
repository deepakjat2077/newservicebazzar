<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SalesDiscount extends Model
{
    public const PERCENT = 'percent';
    public const FLAT    = 'flat';

    protected $fillable = ['name', 'type', 'value', 'package_ids', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'package_ids' => 'array',
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'is_active'   => 'boolean',
        'value'       => 'float',
    ];

    /** Switched on, and inside its dates right now. */
    public function scopeLive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function isLive(): bool
    {
        return $this->is_active
            && (!$this->starts_at || $this->starts_at->lte(now()))
            && (!$this->ends_at || $this->ends_at->gte(now()));
    }

    public function coversAllPackages(): bool
    {
        return empty($this->package_ids);
    }

    /** What the cart items this discount covers add up to (free lines count for nothing). */
    public function eligibleSubtotal(Collection $items): float
    {
        return (float) $items
            ->filter(fn ($item) => ($item['price'] ?? 0) > 0)
            ->filter(fn ($item) => $this->coversAllPackages() || in_array((int) $item['package_id'], array_map('intval', $this->package_ids), true))
            ->sum('price');
    }

    /** Rupees off for this cart: never more than the items it covers. */
    public function amountFor(Collection $items): float
    {
        $eligible = $this->eligibleSubtotal($items);

        if ($eligible <= 0) {
            return 0.0;
        }

        $off = $this->type === self::PERCENT ? round($eligible * $this->value / 100) : $this->value;

        return (float) min($off, $eligible);
    }

    /** "10% off" or "₹500 off". */
    public function label(): string
    {
        return $this->type === self::PERCENT
            ? rtrim(rtrim(number_format($this->value, 2, '.', ''), '0'), '.') . '% off'
            : '₹' . number_format($this->value, 0) . ' off';
    }

    /**
     * GST and the total payable, from the full item price and the discount. Where GST is charged is
     * one setting (config/invoice.php, 'discount_gst_on'): the full price, or the price after discount.
     */
    public static function totals(float $subtotal, float $discount): array
    {
        $rate  = (float) config('invoice.gst_rate', 18) / 100;
        $base  = config('invoice.discount_gst_on', 'full') === 'net' ? max(0, $subtotal - $discount) : $subtotal;
        $gst   = (float) round($base * $rate);

        return ['gst' => $gst, 'total' => $subtotal + $gst - $discount];
    }
}
