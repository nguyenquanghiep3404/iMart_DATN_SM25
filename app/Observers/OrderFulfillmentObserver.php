<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderFulfillment;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentObserver
{
    /**
     * Handle the OrderFulfillment "updated" event.
     */
    public function updated(OrderFulfillment $fulfillment): void
    {
        // Chỉ xử lý khi trạng thái thay đổi
        if ($fulfillment->wasChanged('status')) {
            $this->updateOrderStatusBasedOnFulfillments($fulfillment);
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng dựa trên trạng thái của tất cả fulfillments
     */
    private function updateOrderStatusBasedOnFulfillments(OrderFulfillment $fulfillment): void
    {
        $order = $fulfillment->order;
        
        if (!$order) {
            Log::warning('Không tìm thấy đơn hàng cho fulfillment', [
                'fulfillment_id' => $fulfillment->id
            ]);
            return;
        }
        
        // Sử dụng method mới trong Order model
        $order->updateStatusBasedOnFulfillments();
        
        Log::info('Đã gọi cập nhật trạng thái đơn hàng từ OrderFulfillmentObserver', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'fulfillment_id' => $fulfillment->id,
            'fulfillment_status' => $fulfillment->status
        ]);
    }
}