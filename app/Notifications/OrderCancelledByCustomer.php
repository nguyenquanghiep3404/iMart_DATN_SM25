<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCancelledByCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Thông báo: Khách hàng đã hủy đơn hàng #' . $this->order->order_code,
            'message' => 'Đơn hàng đã bị khách hủy, vui lòng kiểm tra.',
            'icon' => 'warning',
            'color' => 'red',
            'order_id' => $this->order->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Thông báo: Khách hàng đã hủy đơn hàng #' . $this->order->order_code,
            'message' => 'Đơn hàng đã bị khách hủy, vui lòng kiểm tra.',
            'order_id' => $this->order->id,
        ]);
    }
}
