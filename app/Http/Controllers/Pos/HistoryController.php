<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Hiển thị trang lịch sử giao dịch cho phiên POS hiện tại.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
  public function index(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $posSession = PosSession::where('user_id', $user->id)
                            ->where('status', 'open')
                            ->first();

    if (!$posSession) {
        return redirect()->route('pos.dashboard.index')
                         ->with('error', 'Không tìm thấy phiên làm việc đang hoạt động.');
    }

    // === SỬA 'processedBy' THÀNH 'processor' Ở ĐÂY ===
    $ordersQuery = Order::where('pos_session_id', $posSession->id)
                        ->with(['user', 'processor', 'items']) // Đã sửa
                        ->latest();

    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        $ordersQuery->where('order_code', 'LIKE', "%{$searchTerm}%");
    }

    $orders = $ordersQuery->paginate(15)->withQueryString();

    return view('pos.history.index', compact('orders', 'posSession'));
}

}