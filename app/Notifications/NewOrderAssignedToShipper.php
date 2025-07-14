<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrderAssignedToShipper extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Bạn có một đơn hàng mới cần giao!',
            'message' => "Đơn hàng #{$this->order->order_code}. Địa chỉ: {$this->order->shipping_full_address}. Ghi chú: {$this->order->notes_for_shipper}",
            'icon' => 'truck',
            'color' => 'yellow',
            'order_id' => $this->order->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Bạn có đơn hàng mới được giao!')
            ->line("Đơn hàng #{$this->order->order_code}")
            ->line("Địa chỉ giao hàng: {$this->order->shipping_full_address}")
            ->line("Ghi chú: {$this->order->notes_for_shipper}")
            ->action('Xem đơn hàng', url("/shipper/orders/{$this->order->id}"))
            ->line('Cảm ơn bạn đã sử dụng iMart!');
    }
}
