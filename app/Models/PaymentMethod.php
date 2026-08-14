<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'act', 'image', 'setting', 'is_manual',
    ];

    protected $casts = [
    	'setting'=>'object'
    ];

    public function currencies(){
    	return $this->hasMany(MethodCurrency::class);
    }
}
