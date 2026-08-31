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
        'user_id', 'package_id', 'listing_id', 'method_currency_id', 'trx_number',
        'amount', 'charge', 'after_charge', 'rate', 'after_convert',
    ];
    protected $casts = ['details'=>'object'];

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
}
