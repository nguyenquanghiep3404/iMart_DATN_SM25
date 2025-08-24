<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;

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

        if ($status === 'returned') {
            $refunds = ReturnRequest::with(['order:id,order_code', 'orderItem.variant.product.coverImage'])
                ->whereHas('order', fn($q) => $q->where('user_id', $user->id))
                ->latest()
                ->paginate(10);

            return view('users.orders.index', [
                'orders' => collect([]),
                'status' => $status,
                'refunds' => $refunds
            ]);
        }

        $ordersQuery = Order::where('user_id', $user->id)
            ->with(['items' => fn($q) => $q->with('productVariant')]);

        if ($status) {
            $ordersQuery->where('status', $this->mapStatus($status));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('order_code', 'like', "%$search%")
                    ->orWhereHas('items', fn($q) => $q->where('product_name', 'like', "%$search%"));
            });
        }

        $orders = $ordersQuery->latest()->paginate(10);

        return view('users.orders.index', compact('orders', 'status'));
    }
    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)
            ->with([
                'items.productVariant',
                'shippingProvince',
                'shippingWard',
                'billingProvince',
                'billingWard'
            ])
            ->findOrFail($id);

        // Gán thuộc tính has_reviewed cho từng item
        foreach ($order->items as $item) {
            $item->has_reviewed = $item->review()->exists(); // true/false
        }

        return view('users.orders.show', compact('order'));
    }


    /**
     * Hiển thị hóa đơn
     */
    public function invoice($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with(['items' => function ($query) {
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
            'out_for_delivery' => 'out_for_delivery',
            'external_shipping' => 'external_shipping',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'failed_delivery' => 'failed_delivery'
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
        
        // REMOVED: Package status update - now using order_fulfillments directly

        // Gửi email thông báo hủy đơn (có thể triển khai sau)

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Đơn hàng đã được hủy thành công.');
    }
    
    // REMOVED: updatePackageStatusBasedOnOrderStatus method - now using order_fulfillments directly
}
