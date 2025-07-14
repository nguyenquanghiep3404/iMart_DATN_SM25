<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderPaymentStatusUpdated extends Notification implements ShouldQueue
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
        $subject = '';
        $line = '';

        if ($this->order->payment_status === Order::PAYMENT_PAID) {
            $subject = 'Xác nhận thanh toán đơn hàng #' . $this->order->order_code;
            $line = 'Đơn hàng của bạn đã được thanh toán thành công.';
        } elseif ($this->order->payment_status === Order::PAYMENT_REFUNDED) {
            $subject = 'Hoàn tiền đơn hàng #' . $this->order->order_code;
            $line = 'Yêu cầu hoàn tiền cho đơn hàng đã được xử lý thành công.';
        }

        return (new MailMessage)
            ->subject($subject)
            ->line($line)
            ->action('Xem chi tiết đơn hàng', route('users.orders.show', $this->order->id));
    }

    public function toArray($notifiable)
    {
        if ($this->order->payment_status === Order::PAYMENT_PAID) {
            return [
                'title' => 'Đã thanh toán đơn hàng #' . $this->order->order_code,
                'message' => 'Chúng tôi đã xác nhận thanh toán thành công.',
                'color' => 'green',
                'icon' => 'check',
                'order_id' => $this->order->id,
            ];
        } elseif ($this->order->payment_status === Order::PAYMENT_REFUNDED) {
            return [
                'title' => 'Đã hoàn tiền đơn hàng #' . $this->order->order_code,
                'message' => 'Chúng tôi đã xử lý hoàn tiền thành công.',
                'color' => 'yellow',
                'icon' => 'warning',
                'order_id' => $this->order->id,
            ];
        }

        return [];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
