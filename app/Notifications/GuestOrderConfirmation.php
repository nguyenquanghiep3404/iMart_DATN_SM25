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
            ->line('Mã đơn hàng của bạn: #' . $this->order->order_code)
            ->line('Nếu cần hỗ trợ, vui lòng liên hệ chúng tôi qua số điện thoại hoặc email.')
            ->line('Cảm ơn bạn đã mua sắm tại iMart!');
    }
}
