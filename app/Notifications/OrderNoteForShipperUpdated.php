<?php

// app/Notifications/OrderNoteForShipperUpdated.php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderNoteForShipperUpdated extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Ghi chú mới từ admin',
            'message' => "Có ghi chú mới cho đơn hàng #{$this->order->order_code}: \"{$this->order->admin_note}\"",
            'icon' => 'warning',
            'color' => 'yellow',
            'order_id' => $this->order->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
