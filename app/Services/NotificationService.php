<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

/**
 * One call to notify a mobile-app user: it records an in-app notification
 * (shown in the app's Notifications list) and, when Firebase is configured,
 * also delivers a push to the user's devices.
 *
 * Use this anywhere the backend wants to reach a user — a new lead on their
 * business, a payment result, a KYC decision, and so on:
 *
 *   app(NotificationService::class)->send($user, 'New lead', 'Ravi enquired about your business', ['link' => '...']);
 */
class NotificationService
{
    public function __construct(protected FcmService $fcm) {}

    public function send(User $user, string $title, ?string $body = null, array $meta = []): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'image'   => $meta['image'] ?? null,
            'link'    => $meta['link'] ?? null,
            'is_read' => false,
        ]);

        $this->fcm->sendToUser($user, $title, (string) $body, array_filter([
            'notification_id' => (string) $notification->id,
            'link'            => $meta['link'] ?? null,
            'type'            => $meta['type'] ?? null,
        ]));

        return $notification;
    }
}
