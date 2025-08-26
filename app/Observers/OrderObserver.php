<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderCancelledByCustomer;
use App\Notifications\OrderPlacedConfirmation;
use App\Notifications\OrderPaymentStatusUpdated;
use App\Notifications\NewOrderAssignedToShipper;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderNoteForShipperUpdated;
use App\Notifications\GuestOrderConfirmation;


class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
   public function created(Order $order)
{
    // Gửi cho admin và order_manager
    $recipients = User::whereHas('roles', function ($q) {
        $q->whereIn('name', ['admin', 'order_manager']);
    })->get();

    if ($recipients->isNotEmpty()) {
        Notification::send($recipients, new NewOrderNotification($order));
    }

    // Gửi xác nhận cho khách đã đăng nhập
    if ($order->user) {
        $order->user->notify(new OrderPlacedConfirmation($order));
    } else if ($order->customer_email) {
        // 👈 Gửi thông báo cho khách vãng lai qua email
            Notification::route('mail', $order->customer_email)
            ->notify(new GuestOrderConfirmation($order));
    }
}




    /**
     * Handle the Order "updated" event.
     */

    public function updated(Order $order): void
    {
        // Gửi thông báo khi trạng thái thanh toán thay đổi
        if ($order->wasChanged('payment_status') && in_array($order->payment_status, [
            Order::PAYMENT_PAID,
            Order::PAYMENT_REFUNDED
        ])) {
            \Log::info("Trạng thái thanh toán đơn hàng {$order->order_code} đã thay đổi thành {$order->payment_status}");

            if ($order->user) {
                $order->user->notify(new OrderPaymentStatusUpdated($order));
            }
        }

        // Cập nhật trạng thái order_fulfillments khi trạng thái đơn hàng thay đổi
        if ($order->wasChanged('status')) {
            $this->updateFulfillmentStatus($order);
        }
        
        // Xử lý đặc biệt cho trạng thái hủy đơn
        if ($order->wasChanged('status') && $order->status === Order::STATUS_CANCELLED) {
            $recipients = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'order_manager']);
            })->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new OrderCancelledByCustomer($order));
            }
        }
        if (
            $order->wasChanged('shipped_by') &&
            $order->shipped_by
        ) {
            $shipper = User::find($order->shipped_by);
            if ($shipper) {
                $shipper->notify(new NewOrderAssignedToShipper($order));
            }
        }
        if (
            $order->isDirty('status')
            && $order->status === Order::STATUS_CANCELLED
            && $order->shipped_by
        ) {

            $shipper = $order->shipper;

            if ($shipper) {
                $shipper->notify(new OrderCancelledNotification($order));
            }
        }
        if ($order->isDirty('admin_note') && $order->shipped_by) {
            $shipper = $order->shipper;
            if ($shipper) {
                $shipper->notify(new OrderNoteForShipperUpdated($order));
            }
        }
    }


    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    /**
     * Cập nhật trạng thái order_fulfillments dựa trên trạng thái đơn hàng
     */
    private function updateFulfillmentStatus(Order $order): void
    {
        try {
            $fulfillments = $order->fulfillments;
            
            if ($fulfillments->isEmpty()) {
                \Log::info('Không có order_fulfillments nào để cập nhật cho đơn hàng', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]);
                return;
            }

            $fulfillmentStatus = null;

            // Mapping trạng thái đơn hàng sang trạng thái order_fulfillment
            switch ($order->status) {
                case Order::STATUS_PENDING_CONFIRMATION:
                    $fulfillmentStatus = OrderFulfillment::STATUS_PENDING;
                    break;
                    
                case Order::STATUS_PROCESSING:
                    $fulfillmentStatus = OrderFulfillment::STATUS_PROCESSING;
                    break;
                    
                case Order::STATUS_OUT_FOR_DELIVERY:
                    $fulfillmentStatus = OrderFulfillment::STATUS_SHIPPED;
                    break;
                    
                case Order::STATUS_EXTERNAL_SHIPPING:
                    $fulfillmentStatus = OrderFulfillment::STATUS_EXTERNAL_SHIPPING;
                    break;
                    
                case Order::STATUS_DELIVERED:
                    $fulfillmentStatus = OrderFulfillment::STATUS_DELIVERED;
                    break;
                    
                case Order::STATUS_CANCELLED:
                    $fulfillmentStatus = OrderFulfillment::STATUS_CANCELLED;
                    break;
                    
                case Order::STATUS_FAILED_DELIVERY:
                    $fulfillmentStatus = OrderFulfillment::STATUS_FAILED;
                    break;
                    
                case Order::STATUS_RETURNED:
                    $fulfillmentStatus = OrderFulfillment::STATUS_RETURNED;
                    break;
            }

            // Cập nhật trạng thái cho tất cả order_fulfillments nếu có mapping
            if ($fulfillmentStatus) {
                $updatedCount = $order->fulfillments()->update([
                    'status' => $fulfillmentStatus
                ]);
                
                \Log::info('Đã cập nhật trạng thái order_fulfillments theo đơn hàng trong Observer', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'order_status' => $order->status,
                    'fulfillment_status' => $fulfillmentStatus,
                    'fulfillments_count' => $fulfillments->count(),
                    'updated_count' => $updatedCount
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Lỗi khi cập nhật trạng thái order_fulfillments trong Observer', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'order_status' => $order->status,
                'error' => $e->getMessage()
            ]);
        }
    }
}
