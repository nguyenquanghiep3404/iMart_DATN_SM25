<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;

class UserOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng của người dùng đã đăng nhập.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $status = null)
    {
        $user = Auth::user();

        $ordersQuery = Order::where('user_id', $user->id)
            ->with(['items' => function($query) {
                $query->with('productVariant');
            }]);

        if ($status) {
            $ordersQuery->where('status', $this->mapStatus($status));
        }

        // Thêm tìm kiếm nếu có
        if ($request->has('search')) {
            $search = $request->input('search');
            $ordersQuery->where(function($query) use ($search) {
                $query->where('order_code', 'like', "%$search%")
                    ->orWhereHas('items', function($q) use ($search) {
                        $q->where('product_name', 'like', "%$search%");
                    });
            });
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate(10);

        return view('users.orders.index', compact('orders', 'status'));
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with(['items' => function($query) {
                $query->with('productVariant');
            }])
            ->findOrFail($id);

        return view('users.orders.show', compact('order'));
    }

    /**
     * Hiển thị hóa đơn
     */
    public function invoice($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with(['items' => function($query) {
                $query->with('productVariant');
            }])
            ->findOrFail($id);

        return view('users.orders.invoice', compact('order'));
    }

    /**
     * Ánh xạ trạng thái từ URL sang giá trị trong database
     */
    private function mapStatus($status)
    {
        $statusMap = [
            'pending_confirmation' => 'pending_confirmation',
            'processing' => 'processing',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'returned' => 'returned'
        ];

        return $statusMap[$status] ?? $status;
    }
    /**
     * Hủy đơn hàng
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending_confirmation', 'processing', 'awaiting_shipment'])
            ->findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason
        ]);

        // Gửi email thông báo hủy đơn (có thể triển khai sau)

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Đơn hàng đã được hủy thành công.');
    }
}
