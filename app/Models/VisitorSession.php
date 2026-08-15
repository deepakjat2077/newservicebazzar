<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    protected $fillable = [
        'visitor_id', 'session_uuid', 'laravel_session_id', 'user_id', 'ip_address',
        'referrer_url', 'landing_page',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'device_type', 'browser', 'os', 'screen_resolution',
        'country', 'state', 'city',
        'started_at', 'ended_at', 'page_view_count',
        'is_bot', 'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_bot' => 'boolean',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class, 'visitor_id', 'visitor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class, 'session_uuid', 'session_uuid');
    }

    public function events()
    {
        return $this->hasMany(AnalyticsEvent::class, 'session_uuid', 'session_uuid');
    }

    public function getDurationSecondsAttribute()
    {
        if (!$this->started_at || !$this->ended_at) {
            return null;
        }

        return $this->ended_at->diffInSeconds($this->started_at);
    }
}
