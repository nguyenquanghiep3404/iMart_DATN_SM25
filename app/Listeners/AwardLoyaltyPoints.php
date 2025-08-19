<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Models\LoyaltyPointLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AwardLoyaltyPoints
{
    /**
     * Xử lý sự kiện.
     *
     * @param \App\Events\OrderDelivered $event
     * @return void
     */
    public function handle(OrderDelivered $event): void
    {
        // Truy cập đơn hàng từ event, sẽ không còn lỗi
        $order = $event->order;
        $user = $order->user;

        // 1. Kiểm tra điều kiện cần thiết
        if (!$user) {
            Log::info("Cộng điểm thất bại: Đơn hàng #{$order->order_code} không có thông tin người dùng.");
            return;
        }

        // 2. Kiểm tra xem đơn hàng này đã được cộng điểm chưa để tránh cộng lặp
        if (LoyaltyPointLog::where('order_id', $order->id)->where('type', 'earn')->exists()) {
            Log::info("Cộng điểm thất bại: Đơn hàng #{$order->order_code} đã được cộng điểm trước đó.");
            return;
        }

        // 3. Tính tổng điểm
        $totalPoints = 0;
        foreach ($order->items as $item) {
            if ($item->variant && $item->variant->points_awarded_on_purchase > 0) {
                $totalPoints += $item->variant->points_awarded_on_purchase * $item->quantity;
            }
        }

        // 4. Thực hiện cộng điểm và ghi log
        if ($totalPoints > 0) {
            DB::transaction(function () use ($user, $order, $totalPoints) {
                $user->increment('loyalty_points_balance', $totalPoints);

                LoyaltyPointLog::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'points' => $totalPoints,
                    'type' => 'earn',
                    'description' => "Cộng điểm cho đơn hàng #{$order->order_code}",
                ]);

                Log::info("Cộng thành công {$totalPoints} điểm cho người dùng #{$user->id} từ đơn hàng #{$order->order_code}.");
            });
        }
    }
}
