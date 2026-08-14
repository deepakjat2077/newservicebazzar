<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MethodCurrency extends Model
{
    use HasFactory;

    // 'enable_status', 'minimum', 'maximum', and the charge/rate fields are
    // deliberately excluded — they're only ever set via direct property
    // assignment in currencyUpdate(), never mass-assigned from request input.
    protected $fillable = [
        'payment_method_id', 'currency', 'currency_name', 'is_manual',
    ];

    public function paymentMethod(){
    	return $this->belongsTo(PaymentMethod::class);
    }
}
