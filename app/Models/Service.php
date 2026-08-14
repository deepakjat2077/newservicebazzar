<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'title',
        'slug',
        'menu_title',
        'short_description',
        'description',

        'hero_title',
        'hero_image',

        'intro_image',
        'intro_content',

        'trusted_title',
        'trusted_description',
        'trusted_image',

        'services_section_title',

        'seo_title',
        'seo_description',
        'seo_image',

        'why_choose_title',
        'why_choose_image',

        'process_title',

        'partner_title',
        'partner_points',
        'partner_image',

        'faq_title',

        'cta_title',
        'cta_description',
        'cta_button_text',
        'cta_button_link',
        'cta_image',

        'meta_title',
        'meta_keywords',
        'meta_description',

        'show_in_menu',
        'status',

        // JSON fields
        'menu_items',
        'cards',
        'features',
        'process_steps',
        'faqs',
    ];

    protected $casts = [
        'menu_items' => 'array',
        'cards' => 'array',
        'features' => 'array',
        'process_steps' => 'array',
        'faqs' => 'array',
        'show_in_menu' => 'boolean',
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($service) {
            if (empty($service->slug) && !empty($service->title)) {
                $service->slug = Str::slug($service->title);
            }
        });

        // activeServices() is cached for the menu/footer on every page render.
        $forgetCache = function () {
            \Illuminate\Support\Facades\Cache::forget('active_services');
        };

        static::saved($forgetCache);
        static::deleted($forgetCache);
    }
}