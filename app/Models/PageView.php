<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = [
        'client_page_view_id', 'visitor_id', 'session_uuid', 'user_id', 'listing_id',
        'url', 'page_name', 'opened_at', 'closed_at', 'time_spent_seconds', 'is_exit',
        'is_bot', 'user_agent',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_exit' => 'boolean',
        'is_bot' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'session_uuid', 'session_uuid');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
