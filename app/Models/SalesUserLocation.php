<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesUserLocation extends Model
{
    protected $fillable = ['sales_user_id', 'event', 'status', 'lat', 'lng', 'accuracy', 'place', 'ip', 'user_agent'];

    protected $casts = ['lat' => 'float', 'lng' => 'float', 'accuracy' => 'integer'];

    public function salesUser()
    {
        return $this->belongsTo(SalesUser::class);
    }

    public function hasPoint(): bool
    {
        return $this->status === 'ok' && $this->lat !== null && $this->lng !== null;
    }

    /** Opens the exact point in Google Maps (no API key needed). */
    public function mapUrl(): ?string
    {
        return $this->hasPoint()
            ? 'https://www.google.com/maps?q=' . $this->lat . ',' . $this->lng
            : null;
    }

    /** Place name, or the coordinates while the name is still being looked up. */
    public function label(): string
    {
        if (!$this->hasPoint()) {
            return match ($this->status) {
                'denied'      => 'Location blocked by rep',
                'unavailable' => 'Location unavailable',
                'timeout'     => 'Location timed out',
                default       => 'No location',
            };
        }

        return $this->place ?: sprintf('%.5f, %.5f', $this->lat, $this->lng);
    }
}
