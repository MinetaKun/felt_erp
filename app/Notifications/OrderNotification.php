<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $type;
    protected $message;

    public function __construct($order, $type, $message)
    {
        $this->order = $order;
        $this->type = $type;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_id,
            'type' => $this->type,
            'message' => $this->message,
            'product_name' => $this->order->product_name,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_id,
            'type' => $this->type,
            'message' => $this->message,
            'product_name' => $this->order->product_name,
            'created_at' => now()->toDateTimeString(),
        ]);
    }
}
