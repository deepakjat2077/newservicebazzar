<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'visitor_id', 'user_id', 'ip_address', 'country', 'state', 'city',
        'device_type', 'browser', 'os', 'first_visit_at', 'last_visit_at', 'total_visits',
    ];

    protected $casts = [
        'first_visit_at' => 'datetime',
        'last_visit_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(VisitorSession::class, 'visitor_id', 'visitor_id');
    }
}
