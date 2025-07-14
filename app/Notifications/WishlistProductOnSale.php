<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WishlistProductOnSale extends Notification
{
    use Queueable;

    public function __construct(public ProductVariant $variant) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail']; // ✅ Gửi đủ 3 kênh nếu muốn
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Sản phẩm đang được giảm giá!',
            'message' => "Sản phẩm \"{$this->variant->product->name}\" với mã (SKU: " . ($this->variant->sku ?? 'N/A') . ") mà bạn yêu thích đang giảm giá từ "
                . number_format($this->variant->price, 0) . "₫ còn "
                . number_format($this->variant->sale_price, 0) . "₫.",
            'icon' => 'tag',
            'color' => 'blue',
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
            ->subject("Sản phẩm bạn yêu thích đang giảm giá!")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Tin vui! Sản phẩm \"{$this->variant->product->name}\" đang được giảm giá.")
            ->line("Giá cũ: " . number_format($this->variant->price, 0) . "₫")
            ->line("Giá mới: " . number_format($this->variant->sale_price, 0) . "₫")
            ->action('Xem sản phẩm', url("/san-pham/{$this->variant->product->slug}"))
            ->line('Nhanh tay kẻo lỡ, số lượng có hạn!')
            ->line('Cảm ơn bạn đã đồng hành cùng iMart!');
    }
}
