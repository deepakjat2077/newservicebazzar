<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /** Cache entry AppServiceProvider reads on every request. */
    public const CACHE_KEY = 'site.setting';

    /**
     * Drop the cached row whenever this one is written.
     *
     * Every writer in the admin goes through Eloquent (there is no
     * ->where()->update() on this table), so the model event is enough —
     * no controller needs to remember to clear anything.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    // 'mail' (SMTP credentials), 'gupshup' (WhatsApp API credentials) and
    // 'seo' are deliberately excluded — they are only ever set via direct
    // property assignment in EmailController / SettingController::seoUpdate()
    // / SettingController::gupshupUpdate(), never via mass update() from
    // request input.
    protected $fillable = [
        'site_name', 'site_currency', 'currency_symbol', 'first_color',
        'second_color', 'tawk_chat', 're_captcha', 'registration',
        'email_verification', 'email_queue', 'multi_lang', 'recaptcha',
    ];

    protected $casts = [
    	'seo'=>'object',
    	'mail'=>'object',
    	'recaptcha'=>'object',
    	'gupshup'=>'object',
    ];
}
