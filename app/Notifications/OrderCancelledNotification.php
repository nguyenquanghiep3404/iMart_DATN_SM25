<?php

// app/Notifications/OrderCancelledNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', 'broadcast']; // push cần thêm driver riêng nếu dùng OneSignal/Firebase
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Đơn hàng bị hủy',
            'message' => "Đơn hàng #{$this->order->order_code} đã bị hủy. Bạn không cần giao đơn này nữa.",
            'icon' => 'warning',
            'color' => 'red',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Đơn hàng #{$this->order->order_code} đã bị hủy")
            ->line("Đơn hàng bạn được gán (#{$this->order->order_code}) đã bị hủy.")
            ->line("Bạn không cần giao đơn hàng này nữa.");
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Đơn hàng bị hủy',
            'message' => "Đơn hàng #{$this->order->order_code} đã bị hủy. Bạn không cần giao đơn này nữa.",
            'icon' => 'warning',
            'color' => 'red',
        ]);
    }
}
