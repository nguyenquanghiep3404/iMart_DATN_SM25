<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProductOutOfStock extends Notification implements ShouldQueue
{
    use Queueable;

    protected $variant;

    public function __construct($variant)
    {
        $this->variant = $variant;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Sản phẩm {$this->variant->name} - SKU: {$this->variant->sku} đã hết hàng.",
            'variant_id' => $this->variant->id,
        ];
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => "Thông báo: Sản phẩm {$this->variant->name} - SKU: {$this->variant->sku} đã hết hàng.",
        ]);
    }
}
