<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\LoyaltyPointLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyPointController extends Controller
{
    /**
     * Hiển thị lịch sử điểm thưởng của người dùng.
     */
    public function history()
    {
        $user = Auth::user();
        $logs = LoyaltyPointLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('users.loyalty.history', [
            'pointsBalance' => $user->loyalty_points_balance,
            'logs' => $logs
        ]);
    }

    /**
     * Cộng điểm thưởng cho đơn hàng đã hoàn thành.
     */
    public function awardPointsForOrder(Order $order)
    {
        // Chỉ cộng điểm cho đơn hàng 'delivered' và cho user đã đăng nhập.
        if ($order->status !== 'delivered' || !$order->user_id) {
            return;
        }

        $user = $order->user;
        $totalPoints = 0;

        // Tính tổng điểm từ các sản phẩm trong đơn hàng
        foreach ($order->items as $item) {
            $totalPoints += $item->variant->points_awarded_on_purchase * $item->quantity;
        }

        if ($totalPoints > 0) {
            DB::transaction(function () use ($user, $order, $totalPoints) {
                // Cập nhật điểm cho user
                $user->loyalty_points_balance += $totalPoints;
                $user->save();

                // Ghi log
                LoyaltyPointLog::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'points' => $totalPoints,
                    'type' => 'earn',
                    'description' => "Cộng điểm cho đơn hàng #{$order->order_code}",
                ]);
            });
        }
    }
}
