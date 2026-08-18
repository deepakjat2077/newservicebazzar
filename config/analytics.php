<?php

return [
    'excluded_ips' => array_filter(array_map('trim', explode(',', env('ANALYTICS_EXCLUDED_IPS', '')))),

    'bot_user_agent_patterns' => [
        'bot', 'crawl', 'spider', 'slurp', 'bingpreview', 'facebookexternalhit',
        'WhatsApp', 'TelegramBot', 'Google-InspectionTool', 'Lighthouse',
        'HeadlessChrome', 'Pingdom', 'GTmetrix', 'AhrefsBot', 'SemrushBot',
        'MJ12bot', 'DotBot', 'Bytespider', 'PetalBot', 'YandexBot',
        'python-requests', 'axios', 'Go-http-client', 'okhttp', 'curl', 'wget',
        'Scrapy', 'PhantomJS', 'Puppeteer', 'Playwright', 'Selenium',
    ],

    /*
    |---------------------------------------------------------------------
    | Header profile
    |---------------------------------------------------------------------
    | A UA-substring blocklist only catches bots that identify themselves.
    | The traffic that actually floods this site sends a stock desktop
    | Chrome UA from a fresh residential IP on every single hit, so it
    | sails straight through the list above. Real browsers always send an
    | Accept-Language header on a document/beacon request; scripted
    | clients routinely omit it, which makes its absence a cheap,
    | zero-query signal available on the request itself.
    */
    'require_accept_language' => (bool) env('ANALYTICS_REQUIRE_ACCEPT_LANGUAGE', true),

    /*
    |---------------------------------------------------------------------
    | Hold buffer (App\Support\Tracking\PendingTraffic)
    |---------------------------------------------------------------------
    | An unknown session's beacons are parked in Redis instead of being
    | written, and released to the database only once that session does
    | something an unattended page render never does. `minutes` is how
    | long they are kept waiting; anything that has not earned its release
    | by then expires and is never stored at all.
    |
    | Long enough to cover a visitor reading one long page before their
    | close beacon fires, short enough that the buffer stays small.
    */
    'hold' => [
        'minutes' => (int) env('ANALYTICS_HOLD_MINUTES', 60),
    ],

    /*
    |---------------------------------------------------------------------
    | Behavioural sweep (App\Console\Commands\MarkBotTraffic)
    |---------------------------------------------------------------------
    | The signal that separates this traffic from humans is behavioural,
    | not header-based: a real browser fires the pagehide/visibilitychange
    | close beacon, views more than one page, clicks something, or comes
    | back. The bot pool does none of those — it renders one page once and
    | discards the context. `grace_minutes` is how long a session is left
    | alone before it is judged, so a visitor who is still reading the page
    | they landed on is never swept.
    */
    'sweep' => [
        'grace_minutes' => (int) env('ANALYTICS_SWEEP_GRACE_MINUTES', 45),
        'lookback_hours' => (int) env('ANALYTICS_SWEEP_LOOKBACK_HOURS', 6),
    ],

    /*
    | listing_view fires automatically on every listing-page load, so it
    | proves nothing about whether a human was present. Every other event
    | type requires a deliberate interaction (a click, a search, a
    | conversion) and therefore counts as proof of a real visitor.
    */
    'passive_event_types' => ['listing_view'],
];
