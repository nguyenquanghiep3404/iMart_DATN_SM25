<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\ProductVariant;
use Cart;

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
    //     dd(
    //     'Item ID đang kiểm tra:', $item->id,
    //     'Kết quả của ->exists():', $item->review()->exists(),
    //     'Câu lệnh SQL đang chạy:', $item->review()->toSql()
    // );

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
    public function buyAgain(Order $order)
{
    // 1. Kiểm tra quyền sở hữu đơn hàng
    if (auth()->id() !== $order->user_id) {
        abort(403);
    }

    // Tải trước các mối quan hệ để tối ưu
    $order->load('items.productVariant');

    $unavailableProducts = [];

    // 2. Lặp qua từng sản phẩm trong đơn hàng cũ (ĐÃ MỞ COMMENT)
    foreach ($order->items as $item) {
        $variant = $item->productVariant;

        // 3. KIỂM TRA QUAN TRỌNG: Sản phẩm có còn tồn tại và còn hàng không?
        if ($variant && $variant->is_active && $variant->quantity > 0) {

            // 4. Thêm sản phẩm vào giỏ hàng
            // LƯU Ý: Thay thế 'Cart::add(...)' bằng logic giỏ hàng thực tế của bạn
            Cart::add([
                'id' => $variant->id,
                'name' => $item->product_name,
                'price' => $variant->price, // Lấy giá mới nhất
                'quantity' => $item->quantity,
                'attributes' => $item->variant_attributes ?? [],
                'associatedModel' => $variant
            ]);

        } else {
            // Ghi nhận lại các sản phẩm không có sẵn
            $unavailableProducts[] = $item->product_name;
        }
    }

    // 5. Chuyển hướng người dùng đến trang giỏ hàng
    $redirect = redirect()->route('cart.index'); // Thay 'cart.index' bằng route giỏ hàng của bạn

    if (empty($unavailableProducts)) {
        return $redirect->with('success', 'Đã thêm tất cả sản phẩm vào giỏ hàng!');
    } else {
        return $redirect->with('warning', 'Một vài sản phẩm không còn bán hoặc đã hết hàng: ' . implode(', ', $unavailableProducts));
    }
}
}
