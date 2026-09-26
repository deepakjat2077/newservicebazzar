<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Deliberately excludes 'status' (payment success/failure state) and
    // gateway-internal fields ('wallet', 'extra_data', 'reason', 'details')
    // from mass assignment — those are only ever set via direct property
    // assignment in verified server-side code (deposit approval, IPN
    // handlers), never from request input.
    protected $fillable = [
        'user_id', 'package_id', 'sales_package_id', 'sales_plan', 'sales_days', 'sales_city', 'sales_area',
        'listing_id', 'method_currency_id', 'trx_number',
        'amount', 'charge', 'after_charge', 'rate', 'after_convert',
    ];
    protected $casts = ['details'=>'object'];

    /** What was bought — legacy package, or a sales package with its plan. */
    public function purchaseName(): string
    {
        if ($this->sales_package_id) {
            return trim(($this->salesPackage->name ?? 'Package') . ' — ' . ($this->sales_plan ?? ''), ' —');
        }

        return $this->package->name ?? '—';
    }

    /** When the thing bought runs out, or null when it has no expiry. */
    public function purchaseExpiry()
    {
        if ($this->sales_package_id) {
            return $this->sales_days ? $this->created_at->addDays((int) $this->sales_days) : null;
        }

        return $this->package && $this->package->validity
            ? $this->created_at->addDays($this->package->validity) : null;
    }

    public function salesPackage()
    {
        return $this->belongsTo(CertificatePackage::class, 'sales_package_id');
    }

    public function method_currency(){
    	return $this->belongsTo(MethodCurrency::class);
    }

    public function methodName(){
    	return $this->method_currency->paymentMethod->name;
    }

    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function package(){
        return $this->belongsTo(Package::class);
    }

    public function listing(){
        return $this->belongsTo(Listing::class);
    }
}
