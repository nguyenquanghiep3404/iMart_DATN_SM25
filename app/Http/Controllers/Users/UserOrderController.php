<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

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

    // Validate lý do cơ bản
    $request->validate([
        'reason' => 'required|string|max:255'
    ]);

    $cancellationReason = $request->reason;
    if ($cancellationReason === 'Lý do khác') {
        $request->validate(['reason_other' => 'required|string|max:1000']);
        $cancellationReason = $request->reason_other;
    }

    $isCOD = strtolower($order->payment_method) === 'cod';

    // Trường hợp 1: Đơn hàng COD, chưa thanh toán
    if ($isCOD && $order->payment_status === 'pending') {
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $cancellationReason
        ]);

        // Có thể thêm Log hoặc gửi email thông báo
        Log::info("Đơn hàng COD #{$order->order_code} đã được hủy bởi người dùng.");

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Đơn hàng của bạn đã được hủy thành công.');
    }

    // Trường hợp 2: Đơn hàng đã thanh toán, cần admin duyệt hoàn tiền
    if (!$isCOD && $order->payment_status === 'paid') {
        $request->validate([
            'refund_method' => 'required|in:bank',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:255',
        ]);

        // Thay vì hủy ngay, chúng ta chuyển sang trạng thái "chờ hủy"
        // và lưu thông tin hoàn tiền để admin xử lý.
        $refundDetails = [
            'method' => $request->refund_method,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ];

        // Giả sử bạn có một cột `cancellation_details` kiểu JSON trong bảng `orders`
        // Nếu không có, bạn cần tạo migration để thêm cột này:
        // php artisan make:migration add_cancellation_details_to_orders_table
        $order->update([
            'status' => 'returned', // Hoặc một trạng thái tùy chỉnh như 'cancellation_requested'
            'cancellation_reason' => $cancellationReason,
            'admin_note' => json_encode(['refund_details' => $refundDetails]) // Lưu thông tin hoàn tiền
        ]);

        Log::info("Người dùng yêu cầu hủy và hoàn tiền cho đơn hàng #{$order->order_code}.");

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Yêu cầu hủy đơn hàng đã được gửi. Chúng tôi sẽ xử lý hoàn tiền cho bạn trong thời gian sớm nhất.');
    }

    // Trường hợp mặc định nếu có lỗi logic
    return redirect()->back()->with('error', 'Không thể hủy đơn hàng này.');
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
    if (auth()->id() !== $order->user_id) {
        abort(403);
    }

    // Load sản phẩm trong đơn hàng
    $order->load('items.productVariant');

    // Lấy giỏ hàng hiện tại (nếu có) để cộng dồn, hoặc khởi tạo mảng rỗng
    $cart = session()->get('cart', []);

    foreach ($order->items as $item) {
        $variant = $item->productVariant;

        if ($variant) {
            $quantityToBuy = $item->quantity;

            // Kiểm tra nếu sản phẩm đã có trong giỏ thì cộng dồn số lượng
            if (isset($cart[$variant->id])) {
                $cart[$variant->id]['quantity'] += $quantityToBuy;
            } else {
                // Nếu chưa có thì thêm mới
                $cart[$variant->id] = [
                    'id' => $variant->id,
                    'name' => $item->product_name,
                    'price' => $variant->price,
                    'quantity' => $quantityToBuy,
                    'attributes' => $item->variant_attributes ?? [],
                    // Có thể thêm các trường khác như ảnh nếu cần
                    // 'image' => $variant->image_url,
                ];
            }
        }
    }

    // Lưu giỏ hàng mới vào session
    session()->put('cart', $cart);
    $debugSession = session('cart');
    \Log::debug('Debug Session Cart in buyAgain:', [$debugSession]);

    // BỎ DÒNG NÀY ĐI
    // dd(session('cart'));

    // Chuyển hướng đến trang giỏ hàng với thông báo thành công
    return redirect()->route('cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
}
}
