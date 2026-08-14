<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'browser', 'os', 'ip', 'country', 'country_code',
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}
