<?php

namespace App\Support;

use App\Models\AdminPushSubscription;
use App\Services\VapidService;
use Illuminate\Support\Facades\Log;

class AdminPush
{
    public static function send(string $title, string $body, string $url = '/admin'): void
    {
        $subscriptions = AdminPushSubscription::all();
        if ($subscriptions->isEmpty()) return;

        $vapid = app(VapidService::class);
        $payload = ['title' => $title, 'body' => $body, 'url' => $url];

        foreach ($subscriptions as $sub) {
            try {
                $vapid->send(
                    ['endpoint' => $sub->endpoint, 'p256dh' => $sub->p256dh, 'auth' => $sub->auth],
                    $payload
                );
            } catch (\Throwable $e) {
                Log::warning('Admin push failed', ['sub_id' => $sub->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
