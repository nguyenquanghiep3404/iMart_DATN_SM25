<?php

namespace App\Observers;

use App\Models\Order;
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

        // Các đoạn xử lý khác (như hủy đơn) giữ nguyên
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
}
