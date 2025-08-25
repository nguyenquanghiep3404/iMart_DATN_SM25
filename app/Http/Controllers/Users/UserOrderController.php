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

        // Cập nhật trạng thái packages khi user hủy đơn hàng
        $this->updatePackageStatusBasedOnOrderStatus($order);

        // Gửi email thông báo hủy đơn (có thể triển khai sau)

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Đơn hàng đã được hủy thành công.');
    }

    /**
     * Cập nhật trạng thái packages dựa trên trạng thái đơn hàng
     */
    private function updatePackageStatusBasedOnOrderStatus(Order $order)
    {
        try {
            // Lấy tất cả packages của đơn hàng thông qua fulfillments
            $packages = \App\Models\Package::whereHas('fulfillment', function ($query) use ($order) {
                $query->where('order_id', $order->id);
            })->get();

            // Mapping trạng thái order sang package status
            $statusMapping = [
                'pending_confirmation' => \App\Models\Package::STATUS_PENDING_CONFIRMATION,
                'processing' => \App\Models\Package::STATUS_PROCESSING,
                'out_for_delivery' => \App\Models\Package::STATUS_OUT_FOR_DELIVERY,
                'delivered' => \App\Models\Package::STATUS_DELIVERED,
                'cancelled' => \App\Models\Package::STATUS_CANCELLED,
                'failed_delivery' => \App\Models\Package::STATUS_FAILED_DELIVERY,
                'returned' => \App\Models\Package::STATUS_RETURNED,
            ];

            $newPackageStatus = $statusMapping[$order->status] ?? null;

            if ($newPackageStatus) {
                foreach ($packages as $package) {
                    $package->updateStatus(
                        $newPackageStatus,
                        "Cập nhật từ user khi đơn hàng chuyển sang {$order->status}",
                        Auth::id()
                    );
                }

                \Log::info('Package statuses updated successfully from user action', [
                    'order_id' => $order->id,
                    'order_status' => $order->status,
                    'package_status' => $newPackageStatus,
                    'packages_count' => $packages->count()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating package statuses from user action', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function confirmReceipt(Order $order)
    {
        if (Auth::id() !== $order->user_id) {
            abort(403);
        }
        // Chỉ xác nhận khi đơn đã giao và chưa có xác nhận trước đó
        if ($order->status === 'delivered' && is_null($order->confirmed_at)) {
            $order->update(['confirmed_at' => now()]);
            return redirect()->back()->with('success', 'Cảm ơn bạn đã xác nhận lại đơn hàng!');
        }
        return redirect()->back()->with('error', 'Không thể thực hiện hành động này.');
    }
}
