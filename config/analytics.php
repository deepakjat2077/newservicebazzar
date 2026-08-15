<?php

return [
    'excluded_ips' => array_filter(array_map('trim', explode(',', env('ANALYTICS_EXCLUDED_IPS', '')))),

    'bot_user_agent_patterns' => [
        'bot', 'crawl', 'spider', 'slurp', 'bingpreview', 'facebookexternalhit',
        'WhatsApp', 'TelegramBot', 'Google-InspectionTool', 'Lighthouse',
        'HeadlessChrome', 'Pingdom', 'GTmetrix', 'AhrefsBot', 'SemrushBot',
        'MJ12bot', 'DotBot', 'Bytespider', 'PetalBot', 'YandexBot',
    ],
];
