<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WishlistProductBackInStock extends Notification
{
    use Queueable;

    public function __construct(public ProductVariant $variant) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail']; // ✅ Thêm mail nếu muốn gửi email
    }

    public function toArray($notifiable)
    {
        return [
            'title'   => 'Tin vui! Sản phẩm đã có hàng trở lại',
           'message' => "Sản phẩm \"{$this->variant->product->name}\" với mã (SKU: " . ($this->variant->sku ?? 'N/A') . ") bạn yêu thích đã có hàng trở lại!",
            'icon'    => 'check-circle',
            'color'   => 'green',
            'variant_id' => $this->variant->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Sản phẩm bạn yêu thích đã có hàng trở lại!")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Sản phẩm \"{$this->variant->product->name}\" trong danh sách yêu thích của bạn đã có hàng trở lại.")
            ->action('Mua ngay', url("/san-pham/{$this->variant->product->slug}"))
            ->line('Cảm ơn bạn đã đồng hành cùng iMart!');
    }
}
