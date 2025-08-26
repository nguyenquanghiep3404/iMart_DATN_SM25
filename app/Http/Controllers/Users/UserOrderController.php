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
use App\Models\CancellationRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ProductInventory;

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
            'partially_shipped' => 'partially_shipped',
            'out_for_delivery' => 'out_for_delivery',
            'external_shipping' => 'external_shipping',
            'partially_delivered' => 'partially_delivered',
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

            ->whereIn('status', ['pending_confirmation', 'processing', 'cancellation_requested'])
            ->findOrFail($id);

        $request->validate(['reason' => 'required|string|max:255']);
        $cancellationReason = $request->reason === 'Lý do khác'
            ? $request->input('reason_other', $request->reason)
            : $request->reason;


        $isCOD = strtolower($order->payment_method) === 'cod';

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason
        ]);

        // Cập nhật trạng thái packages khi user hủy đơn hàng
        // $this->updatePackageStatusBasedOnOrderStatus($order);



        // =================================================================
        // **TRƯỜNG HỢP 1: ĐƠN HÀNG COD**
        // Hủy trực tiếp và hoàn trả sản phẩm về kho ngay lập tức.
        // =================================================================
        if ($isCOD && $order->payment_status === 'pending') {
            DB::beginTransaction();
            try {
                // Cập nhật trạng thái đơn hàng
                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $cancellationReason,
                ]);

                // Hoàn kho
                $order->load('items.productVariant');
        $storeLocationId = $order->store_location_id; // Lấy kho xử lý của đơn hàng

        if ($storeLocationId) {
            foreach ($order->items as $item) {
                if ($item->productVariant) {
                    // Cập nhật bảng product_inventories
                    ProductInventory::where('product_variant_id', $item->product_variant_id)
                                    ->where('store_location_id', $storeLocationId)
                                    ->where('inventory_type', 'new') // Giả định hoàn trả hàng mới
                                    ->increment('quantity', $item->quantity);
                }
            }
        } else {
            Log::warning("Không thể hoàn kho cho đơn COD #{$order->order_code} vì không có store_location_id.");
        }
                DB::commit();
                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Đơn hàng đã được hủy và sản phẩm đã được hoàn về kho thành công.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Lỗi khi hủy đơn hàng COD #{$order->order_code}: " . $e->getMessage());
                return redirect()->back()->with('error', 'Lỗi khi hủy đơn hàng. Vui lòng thử lại.');
            }
        }

        // =================================================================
        // **TRƯỜNG HỢP 2: ĐƠN ĐÃ THANH TOÁN ONLINE**
        // Tạo một "Yêu cầu hủy" để Admin xem xét và duyệt.
        // =================================================================
        if (!$isCOD && $order->payment_status === 'paid') {
            $request->validate([
                'bank_name' => 'required|string|max:255',
                'bank_account_number' => 'required|string|max:50',
                'bank_account_name' => 'required|string|max:255',
            ]);

            DB::beginTransaction();
            try {
                // Tạo một bản ghi mới trong bảng cancellation_requests
                CancellationRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'cancellation_code' => 'CR-' . strtoupper(Str::random(10)),
                    'reason' => $cancellationReason,
                    'status' => 'pending_review',
                    'refund_method' => 'bank',
                    'refund_amount' => $order->grand_total,
                    'bank_name' => $request->bank_name,
                    'bank_account_name' => $request->bank_account_name,
                    'bank_account_number' => $request->bank_account_number,
                ]);

                // Cập nhật trạng thái đơn hàng thành "đang chờ xử lý hủy"
                $order->update(['status' => 'cancellation_requested']);

                DB::commit();

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Yêu cầu hủy đơn hàng của bạn đã được gửi. Chúng tôi sẽ xem xét và xử lý hoàn tiền trong thời gian sớm nhất.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Lỗi khi tạo yêu cầu hủy cho đơn #{$order->order_code}: " . $e->getMessage());
                return redirect()->back()->with('error', 'Lỗi khi gửi yêu cầu. Vui lòng thử lại.');
            }
        }

        return redirect()->back()->with('error', 'Không thể thực hiện hành động này.');
    }


    /**
     * Cập nhật trạng thái packages dựa trên trạng thái đơn hàng
     */
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
