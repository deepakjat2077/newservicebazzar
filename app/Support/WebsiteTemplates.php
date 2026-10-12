<?php

namespace App\Support;

/**
 * The website designs a rep can offer with a website package. The home page of each is drawn
 * live from the business's own details by resources/views/sales_genio/website_preview.blade.php.
 */
class WebsiteTemplates
{
    public static function all(): array
    {
        return [
            'classic' => ['name' => 'Classic',  'tagline' => 'Traditional, trusted and well-established', 'accent' => '#b8860b'],
            'fresh'   => ['name' => 'Fresh',    'tagline' => 'Clean, friendly and easy to read',          'accent' => '#16a34a'],
            'bold'    => ['name' => 'Bold',     'tagline' => 'Dark, modern and impossible to miss',       'accent' => '#f59e0b'],
            'minimal' => ['name' => 'Minimal',  'tagline' => 'Simple, calm and elegant',                  'accent' => '#111827'],
            'vibrant' => ['name' => 'Vibrant',  'tagline' => 'Colourful, lively and full of energy',      'accent' => '#e11d74'],
        ];
    }

    public static function exists(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::all());
    }

    public static function name(?string $key): ?string
    {
        return self::all()[$key]['name'] ?? null;
    }
}
