<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct($order)
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
            ->subject('Có đơn hàng mới #' . $this->order->order_code)
            ->greeting('Xin chào ' . $notifiable->name . ',')
            ->line('Một đơn hàng mới vừa được đặt trên hệ thống.')
            ->line('Mã đơn hàng: #' . $this->order->order_code)
            ->line('Tổng giá trị: ' . number_format($this->order->grand_total, 0, ',', '.') . ' đ')
            ->action('Xem chi tiết đơn hàng', route('admin.orders.view', $this->order->id))
            ->line('Vui lòng xử lý đơn hàng càng sớm càng tốt.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Có đơn hàng mới #' . $this->order->order_code,
            'message' => 'Tổng giá trị: ' . number_format($this->order->grand_total, 0, ',', '.') . ' đ cần được xử lý',
            'order_id' => $this->order->id,
            'color' => 'yellow',
            'icon' => 'shopping-cart',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Có đơn hàng mới #' . $this->order->order_code,
            'message' => 'Tổng giá trị: ' . number_format($this->order->grand_total, 0, ',', '.') . ' đ',
            'order_id' => $this->order->id,
            'color' => 'yellow',
            'icon' => 'shopping-cart',
        ]);
    }
}
