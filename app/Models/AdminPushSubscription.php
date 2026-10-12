<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPushSubscription extends Model
{
    protected $fillable = ['admin_id', 'endpoint', 'p256dh', 'auth'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
