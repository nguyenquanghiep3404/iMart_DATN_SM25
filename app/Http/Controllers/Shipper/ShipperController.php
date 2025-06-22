<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order; // Import model Order

class ShipperController extends Controller
{

    /**
     * Hiển thị màn hình Dashboard chính cho Shipper.
     */
    public function dashboard()
    {

        $shipper = Auth::user();

        // Lấy danh sách đơn hàng được gán cho shipper này
        // Dựa vào các trạng thái trong file orders.sql của bạn

        // Tab "Cần lấy hàng"
        $ordersToPickup = Order::where('shipped_by', $shipper->id)
                               ->where('status', 'awaiting_shipment')
                               ->orderBy('created_at', 'desc')
                               ->get();

        // Tab "Đang giao"
        $ordersInTransit = Order::where('shipped_by', $shipper->id)
                                ->whereIn('status', ['shipped', 'out_for_delivery'])
                                ->orderBy('updated_at', 'desc')
                                ->get();

        // Tab "Lịch sử giao"
        $ordersHistory = Order::where('shipped_by', $shipper->id)
                              ->whereIn('status', ['delivered', 'cancelled', 'returned', 'failed_delivery'])
                              ->orderBy('updated_at', 'desc')
                              ->limit(50) // Giới hạn lịch sử để tối ưu
                              ->get();

        // Trả về view và truyền dữ liệu
        return view('shipper.dashboard', compact('ordersToPickup', 'ordersInTransit', 'ordersHistory'));
    }

    /**
     * Lấy thông tin chi tiết của một đơn hàng (cho AJAX).
     */
    public function show(Order $order)
    {
        // Kiểm tra quyền, đảm bảo shipper chỉ xem được đơn của mình
        if ($order->shipped_by !== Auth::id()) {
            abort(403, 'Không có quyền truy cập đơn hàng này.');
        }
        // Đưa các sản phẩm của người mua
        $order->load('items');
        // dd($order->items);

        // Trả về view 'shipper.show' và truyền đối tượng $order vào
        return view('shipper.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng (cho AJAX).
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Đảm bảo shipper chỉ cập nhật được đơn hàng của mình
        if ($order->shipped_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền!'], 403);
        }

        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'status' => 'required|string|in:shipped,delivered,failed_delivery',
            'reason' => 'nullable|string|max:255' // Cho trường hợp giao thất bại
        ]);

        $order->status = $validated['status'];

        // Logic cập nhật thời gian tương ứng
        if ($validated['status'] === 'delivered') {
            $order->delivered_at = now();
        }

        if ($validated['status'] === 'failed_delivery') {
            // Dùng toán tử Null Coalescing (??) để kiểm tra
            // Nếu $validated['reason'] tồn tại, dùng nó. Nếu không, dùng một chuỗi mặc định.
            $order->failed_delivery_reason = $validated['reason'] ?? 'Không có lý do cụ thể.';
            $order->delivered_at = null; // Đảm bảo ngày giao hàng thành công là null
        }

        $order->save();

         return redirect()->route('shipper.orders.show', $order)
           ->with('success', 'Cập nhật trạng thái thành công!');
    }
}
