<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignLink extends Model
{
    protected $fillable = ['name', 'campaign', 'source', 'medium', 'landing_url', 'created_by'];

    public const CHANNELS = [
        'facebook_ad' => ['Facebook Ad', 'facebook', 'cpc'],
        'instagram_ad' => ['Instagram Ad', 'instagram', 'cpc'],
        'google_ad' => ['Google Ads', 'google', 'cpc'],
        'facebook_post' => ['Facebook post / page', 'facebook', 'social'],
        'instagram_post' => ['Instagram post / bio', 'instagram', 'social'],
        'whatsapp_broadcast' => ['WhatsApp broadcast / group', 'whatsapp', 'broadcast'],
        'whatsapp_status' => ['WhatsApp status', 'whatsapp', 'status'],
        'youtube' => ['YouTube video / description', 'youtube', 'video'],
        'sms' => ['SMS', 'sms', 'sms'],
        'email' => ['Email', 'email', 'email'],
        'qr' => ['QR code / poster / pamphlet', 'qr', 'offline'],
    ];

    public function url(): string
    {
        $parts = parse_url($this->landing_url);
        parse_str($parts['query'] ?? '', $query);

        $query = array_merge($query, [
            'utm_source' => $this->source,
            'utm_medium' => $this->medium,
            'utm_campaign' => $this->campaign,
        ]);

        return ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . ($parts['path'] ?? '/')
            . '?' . http_build_query($query);
    }

    public function channelLabel(): string
    {
        foreach (self::CHANNELS as [$label, $source, $medium]) {
            if ($source === $this->source && $medium === $this->medium) {
                return $label;
            }
        }

        return $this->source . ' / ' . $this->medium;
    }
}
