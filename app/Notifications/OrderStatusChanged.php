<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $oldStatus;
    protected $newStatus;
    protected $message;

    public function __construct($order, $oldStatus, $newStatus, $message = null)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->message = $message ?? "Order #{$order->order_number} status changed from {$oldStatus} to {$newStatus}";
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'title' => 'Order Status Changed',
            'message' => $this->message,
            'type' => 'order_status_changed',
            'data' => [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'order' => [
                    'id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'status' => $this->newStatus,
                ]
            ],
            'read_at' => null,
            'created_at' => now()->toIso8601String(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'title' => 'Order Status Changed',
            'message' => $this->message,
            'type' => 'order_status_changed',
            'data' => [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'order' => [
                    'id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'status' => $this->newStatus,
                ]
            ],
            'read_at' => null,
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
