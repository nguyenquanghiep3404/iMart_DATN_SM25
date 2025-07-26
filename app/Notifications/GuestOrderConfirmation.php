<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GuestOrderConfirmation extends Notification
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác nhận đơn hàng #' . $this->order->order_code)
            ->greeting('Chào ' . $this->order->customer_name . ',')
            ->line('Đơn hàng #' . $this->order->order_code . ' của bạn đã được đặt thành công.')
            ->line('Chúng tôi sẽ sớm xử lý đơn hàng và liên hệ lại với bạn.')
            ->action('Xem chi tiết đơn hàng', route('orders.show', $this->order->id))
            ->line('Cảm ơn bạn đã mua sắm tại iMart!');
    }
}
