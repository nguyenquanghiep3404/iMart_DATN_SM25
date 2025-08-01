<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyPointController extends Controller
{
    /**
     * Hiển thị trang quản lý lịch sử điểm thưởng.
     */
    public function index(Request $request)
    {
        $query = LoyaltyPointLog::with('user')->orderBy('created_at', 'desc');

        // Tìm kiếm theo email hoặc tên người dùng
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo loại giao dịch
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Lấy danh sách user để admin chọn khi cần điều chỉnh điểm
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.loyalty.index', compact('logs', 'users'));
    }

    /**
     * Điều chỉnh điểm thưởng thủ công cho người dùng.
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        $points = (int) $request->points;

        DB::beginTransaction();
        try {
            // Cập nhật số dư của người dùng
            $user->loyalty_points_balance += $points;
            $user->save();

            // Ghi log giao dịch
            LoyaltyPointLog::create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => 'manual_adjustment',
                'description' => $request->reason,
            ]);

            DB::commit();

            return back()->with('success', 'Đã điều chỉnh thành công ' . $points . ' điểm cho người dùng ' . $user->name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
