<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A cart the sales team turned into a payment link. The customer opens the
 * link, pays, and every package on it switches on.
 */
class SalesOrder extends Model
{
    public const PENDING = 0;
    public const PAID = 1;
    public const CANCELLED = 2;

    protected $fillable = [
        'token', 'listing_id', 'sales_user_id', 'subtotal', 'gst', 'total',
        'status', 'gateway', 'gateway_order_id', 'gateway_payment_id', 'paid_at',
    ];

    protected $casts = ['paid_at' => 'datetime', 'invoice_sent_at' => 'datetime'];

    public static function newToken(): string
    {
        return Str::lower(Str::random(32));
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function salesUser()
    {
        return $this->belongsTo(SalesUser::class);
    }

    public function isPaid(): bool
    {
        return (int) $this->status === self::PAID;
    }

    public function payUrl(): string
    {
        return route('sales.order.pay', $this->token);
    }
}
