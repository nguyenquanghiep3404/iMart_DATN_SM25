<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class AutoCompleteDeliveredOrders extends Command
{
    protected $signature = 'orders:autocomplete';
    protected $description = 'Tự động chuyển các đơn hàng đã giao quá 5 ngày sang trạng thái hoàn thành';

    public function handle()
    {
        $this->info('Bắt đầu quét...');

        // Tìm đơn hàng đã giao quá 5 ngày VÀ chưa được xác nhận
        $ordersToConfirm = Order::where('status', 'delivered')
            ->whereNull('confirmed_at')
            ->where('delivered_at', '<=', now()->subDays(7))
            ->get();

        if ($ordersToConfirm->isEmpty()) {
            $this->info('Không tìm thấy đơn hàng nào cần cập nhật.');
            return 0;
        }

        foreach ($ordersToConfirm as $order) {
            $order->update(['confirmed_at' => now()]); 
            $this->line("Đã tự động xác nhận cho đơn hàng #{$order->order_code}.");
        }

        $this->info("Hoàn tất! Đã cập nhật {$ordersToConfirm->count()} đơn hàng.");
        return 0;
    }
}
