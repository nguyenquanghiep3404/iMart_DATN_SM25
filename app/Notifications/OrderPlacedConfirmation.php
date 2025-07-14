<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderPlacedConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác nhận đơn hàng #' . $this->order->order_code)
            ->greeting('Xin chào ' . $this->order->customer_name . ',')
            ->line('Đơn hàng #' . $this->order->order_code . ' của bạn đã được đặt thành công.')
            ->line('Chúng tôi sẽ sớm xử lý và thông báo cho bạn.')
            ->action('Xem chi tiết đơn hàng', route('users.orders.show', $this->order->id))
            ->line('Cảm ơn bạn đã mua sắm tại iMart!');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Xác nhận đơn hàng #' . $this->order->order_code,
            'message' => 'Đơn hàng của bạn đã được đặt thành công. Chúng tôi sẽ sớm xử lý.',
            'order_id' => $this->order->id,
            'color' => 'green',
            'icon' => 'check'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Xác nhận đơn hàng #' . $this->order->order_code,
            'message' => 'Đơn hàng của bạn đã được đặt thành công.',
            'order_id' => $this->order->id,
        ]);
    }
}
