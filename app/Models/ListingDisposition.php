<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingDisposition extends Model
{
    protected $fillable = ['listing_id', 'sales_user_id', 'disposition', 'call_back', 'due_at', 'note'];

    protected $casts = ['call_back' => 'boolean', 'due_at' => 'datetime'];

    /**
     * Pin each new disposition to where the rep was: their latest GPS point
     * from the SB Sales panel within the last 30 minutes (SalesGeoBeacon).
     */
    protected static function booted()
    {
        static::creating(function (ListingDisposition $d) {
            if ($d->sales_user_id && empty($d->location_id)) {
                $d->location_id = SalesUserLocation::where('sales_user_id', $d->sales_user_id)
                    ->where('status', 'ok')
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->latest('id')
                    ->value('id');
            }
        });
    }

    public function location()
    {
        return $this->belongsTo(SalesUserLocation::class, 'location_id');
    }

    /** Dispositions that carry a date: the rep has to act on them. */
    public const SCHEDULED = ['Follow Up', 'Call Back', 'Appointment Refixed', 'Self Appointment'];

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast();
    }

    public function isDueToday(): bool
    {
        return $this->due_at && $this->due_at->isToday();
    }

    /** "Overdue by 2 hours", "Today 04:30 PM", "26 Sep, 11:00 AM". */
    public function dueLabel(): string
    {
        if (!$this->due_at) {
            return 'No date set';
        }

        if ($this->due_at->isPast()) {
            return 'Overdue · ' . $this->due_at->diffForHumans(null, true) . ' ago';
        }

        return $this->due_at->isToday()
            ? 'Today ' . $this->due_at->format('h:i A')
            : $this->due_at->format('d M, h:i A');
    }

    public function salesUser()
    {
        return $this->belongsTo(SalesUser::class, 'sales_user_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }
}
