<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WishlistProductLowStock extends Notification
{
    use Queueable;

    public function __construct(public ProductVariant $variant) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Sắp hết hàng!',
            'message' => "Sản phẩm \"{$this->variant->product->name}\" với mã (SKU: " . ($this->variant->sku ?? 'N/A') . ") bạn yêu thích sắp hết hàng. Nhanh tay mua ngay!",
            'icon' => 'warning',
            'color' => 'yellow',
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
            ->subject("Sản phẩm bạn yêu thích sắp hết hàng!")
            ->line("Sản phẩm \"{$this->variant->product->name}\" chỉ còn số lượng rất ít.")
            ->action('Xem ngay', url("/san-pham/{$this->variant->product->slug}"))
            ->line('Cảm ơn bạn đã sử dụng iMart!');
    }
}
