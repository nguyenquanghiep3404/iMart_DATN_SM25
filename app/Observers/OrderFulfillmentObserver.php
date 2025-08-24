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
        try {
            $order = $fulfillment->order;
            
            if (!$order) {
                Log::warning('Không tìm thấy đơn hàng cho fulfillment', [
                    'fulfillment_id' => $fulfillment->id
                ]);
                return;
            }

            // Lấy tất cả fulfillments của đơn hàng
            $allFulfillments = $order->fulfillments;
            
            if ($allFulfillments->isEmpty()) {
                Log::info('Không có fulfillments nào cho đơn hàng', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]);
                return;
            }

            // Đếm số lượng fulfillments theo từng trạng thái
            $statusCounts = $allFulfillments->groupBy('status')->map->count();
            $totalFulfillments = $allFulfillments->count();

            Log::info('Kiểm tra trạng thái fulfillments để cập nhật đơn hàng', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'current_order_status' => $order->status,
                'total_fulfillments' => $totalFulfillments,
                'status_counts' => $statusCounts->toArray(),
                'changed_fulfillment_id' => $fulfillment->id,
                'changed_fulfillment_status' => $fulfillment->status
            ]);

            $newOrderStatus = null;

            // Logic cập nhật trạng thái đơn hàng
            if ($statusCounts->get(OrderFulfillment::STATUS_CANCELLED, 0) === $totalFulfillments) {
                // Tất cả fulfillments đã bị hủy
                $newOrderStatus = Order::STATUS_CANCELLED;
            } 
            elseif ($statusCounts->get(OrderFulfillment::STATUS_DELIVERED, 0) === $totalFulfillments) {
                // Tất cả fulfillments đã được giao
                $newOrderStatus = Order::STATUS_DELIVERED;
            }
            elseif ($statusCounts->get(OrderFulfillment::STATUS_PACKED, 0) === $totalFulfillments) {
                // Tất cả fulfillments đã được đóng gói - giữ trạng thái processing
                $newOrderStatus = Order::STATUS_PROCESSING;
            }
            elseif ($statusCounts->get(OrderFulfillment::STATUS_SHIPPED, 0) === $totalFulfillments ||
                    $statusCounts->get(OrderFulfillment::STATUS_EXTERNAL_SHIPPING, 0) === $totalFulfillments) {
                // Tất cả fulfillments đã được giao cho đơn vị vận chuyển
                if ($statusCounts->get(OrderFulfillment::STATUS_EXTERNAL_SHIPPING, 0) === $totalFulfillments) {
                    $newOrderStatus = Order::STATUS_EXTERNAL_SHIPPING;
                } else {
                    $newOrderStatus = Order::STATUS_OUT_FOR_DELIVERY;
                }
            }
            elseif (($statusCounts->get(OrderFulfillment::STATUS_SHIPPED, 0) + 
                     $statusCounts->get(OrderFulfillment::STATUS_EXTERNAL_SHIPPING, 0) + 
                     $statusCounts->get(OrderFulfillment::STATUS_DELIVERED, 0)) === $totalFulfillments) {
                // Tất cả fulfillments đã được giao cho đơn vị vận chuyển hoặc đã giao thành công
                // Ưu tiên trạng thái external shipping nếu có
                if ($statusCounts->get(OrderFulfillment::STATUS_EXTERNAL_SHIPPING, 0) > 0) {
                    $newOrderStatus = Order::STATUS_EXTERNAL_SHIPPING;
                } else {
                    $newOrderStatus = Order::STATUS_OUT_FOR_DELIVERY;
                }
            }
            elseif ($statusCounts->get(OrderFulfillment::STATUS_PROCESSING, 0) > 0) {
                // Có ít nhất một fulfillment đang xử lý
                $newOrderStatus = Order::STATUS_PROCESSING;
            }

            // Cập nhật trạng thái đơn hàng nếu cần
            if ($newOrderStatus && $newOrderStatus !== $order->status) {
                // Tạm thời tắt Observer để tránh vòng lặp vô hạn
                Order::withoutEvents(function () use ($order, $newOrderStatus) {
                    $order->update([
                        'status' => $newOrderStatus,
                        'processed_by' => auth()->id() ?? 1 // Fallback to admin user
                    ]);
                });

                Log::info('Đã cập nhật trạng thái đơn hàng dựa trên fulfillments', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'old_status' => $order->getOriginal('status'),
                    'new_status' => $newOrderStatus,
                    'trigger_fulfillment_id' => $fulfillment->id,
                    'trigger_fulfillment_status' => $fulfillment->status,
                    'updated_by' => auth()->id() ?? 1
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái đơn hàng dựa trên fulfillments', [
                'fulfillment_id' => $fulfillment->id,
                'order_id' => $fulfillment->order_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}