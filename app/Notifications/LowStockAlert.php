<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $variant;

    public function __construct($variant)
    {
        $this->variant = $variant;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ Cảnh báo tồn kho thấp')
            ->line("Tồn kho của sản phẩm \"{$this->variant->product->name}\" - SKU: {$this->variant->sku} chỉ còn {$this->variant->stock_quantity}.")
            ->action('Quản lý sản phẩm', url("/admin/products/{$this->variant->product_id}/edit"));
    }

   public function toArray($notifiable)
{
    return [
        'title' => '⚠️ Cảnh báo tồn kho thấp',
        'message' => "Tồn kho thấp: {$this->variant->product->name} - SKU: {$this->variant->sku} chỉ còn {$this->variant->stock_quantity}",
        'icon' => '⚠️',
        'color' => 'yellow',
        'product_id' => $this->variant->product_id,
    ];
}


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}