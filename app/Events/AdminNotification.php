<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $count;

    public function __construct($type, $count)
    {
        $this->type = $type;
        $this->count = $count;
    }

    /**
     * Broadcast channel
     */
    public function broadcastOn()
    {
        return new Channel('admin-notifications');
    }

    /**
     * Event name
     */
    public function broadcastAs()
    {
        return 'admin.notification';
    }

    /**
     * Data sent to frontend
     */
    public function broadcastWith()
    {
        return [
            'type'  => $this->type,
            'count' => $this->count,
        ];
    }
}