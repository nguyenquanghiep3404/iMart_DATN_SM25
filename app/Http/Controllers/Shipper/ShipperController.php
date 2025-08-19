<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Carbon\Carbon;

class ShipperController extends Controller
{
    /**
     * Trang Dashboard: Hiển thị các đơn cần lấy và đang giao.
     */
    public function dashboard()
    {
        $shipper = Auth::user();

        // Lấy các đơn hàng được gán cho shipper này để xử lý trong ngày
        $ordersToPickup = Order::where('shipped_by', $shipper->id)
            ->where('status', 'awaiting_shipment_assigned')
            ->orderBy('created_at', 'desc')->get();

        $ordersInTransit = Order::where('shipped_by', $shipper->id)
            ->whereIn('status', ['shipped', 'out_for_delivery'])
            ->orderBy('updated_at', 'desc')->get();

        return view('shipper.dashboard', compact('shipper', 'ordersToPickup', 'ordersInTransit'));
    }

    /**
     * Trang Thống kê: Tính toán KPI và chuẩn bị dữ liệu cho biểu đồ.
     */
    public function stats(Request $request)
    {
        $shipper = Auth::user();
        $range = $request->input('range', 'today'); // Lấy filter, mặc định là 'today'

        // Xác định khoảng thời gian dựa trên filter
        $now = Carbon::now();
        switch ($range) {
            case 'week':
                $startDate = $now->startOfWeek()->copy();
                $endDate = $now->endOfWeek()->copy();
                break;
            case 'month':
                $startDate = $now->startOfMonth()->copy();
                $endDate = $now->endOfMonth()->copy();
                break;
            case 'today':
            default:
                $startDate = $now->startOfDay()->copy();
                $endDate = $now->endOfDay()->copy();
                break;
        }

        // Lấy các đơn hàng đã hoàn tất trong khoảng thời gian đã chọn
        $finishedOrders = Order::where('shipped_by', $shipper->id)
            ->whereIn('status', ['delivered', 'failed_delivery'])
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->get();

        $deliveredOrders = $finishedOrders->where('status', 'delivered');
        $failedOrders = $finishedOrders->where('status', 'failed_delivery');

        // Tính toán các chỉ số KPI
        $stats = [
            'total_income' => $deliveredOrders->sum('grand_total'),
            'total_delivered' => $deliveredOrders->count(),
            'total_failed' => $failedOrders->count(),
            'success_rate' => $finishedOrders->count() > 0 ? round(($deliveredOrders->count() / $finishedOrders->count()) * 100) : 0,
        ];

        // Chuẩn bị dữ liệu cho biểu đồ
        $chartData = $deliveredOrders->groupBy(function ($date) {
            return Carbon::parse($date->delivered_at)->format('d/m'); // Nhóm theo ngày
        })->map(function ($group) {
            return $group->count(); // Đếm số đơn mỗi ngày
        });

        $chartLabels = $chartData->keys();
        $chartValues = $chartData->values();

        return view('shipper.stats', compact('shipper', 'stats', 'range', 'chartLabels', 'chartValues'));
    }

    /**
     * Trang Lịch sử: Lấy tất cả các đơn hàng đã xử lý và phân trang.
     */
    public function history()
    {
        $shipper = Auth::user();
        $ordersHistory = Order::where('shipped_by', $shipper->id)
            ->whereIn('status', ['delivered', 'cancelled', 'returned', 'failed_delivery'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15); // Phân trang, mỗi trang 15 đơn

        return view('shipper.history', compact('shipper', 'ordersHistory'));
    }

    /**
     * Trang Tài khoản.
     */
    public function profile()
    {
        return view('shipper.profile', ['shipper' => Auth::user()]);
    }

    /**
     * Trang chi tiết một đơn hàng.
     */
    public function show(Order $order)
    {
        if ($order->shipped_by !== Auth::id()) {
            abort(403, 'Không có quyền truy cập đơn hàng này.');
        }
        $order->load('items');
        return view('shipper.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái một đơn hàng.
     */
    public function updateStatus(Request $request, Order $order)
    {
        if ($order->shipped_by !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.'], 403);
            }
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        // Xử lý quét barcode để chuyển từ awaiting_shipment_assigned sang shipped
        if ($request->has('barcode') && $request->input('status') === 'shipped') {
            // Kiểm tra trạng thái đơn hàng phải là awaiting_shipment_assigned
            if ($order->status !== 'awaiting_shipment_assigned') {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Đơn hàng không ở trạng thái chờ lấy hàng.'], 400);
                }
                return back()->with('error', 'Đơn hàng không ở trạng thái chờ lấy hàng.');
            }

            // Xác thực mã barcode (có thể kiểm tra với order_code hoặc logic khác)
            $barcode = $request->input('barcode');
            
            // Kiểm tra mã barcode có khớp với order_code không
            if ($barcode !== $order->order_code) {
                \Log::info('Barcode validation failed', [
                    'barcode' => $barcode,
                    'order_code' => $order->order_code
                ]);
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Mã barcode không khớp với đơn hàng.'], 400);
                }
                return back()->with('error', 'Mã barcode không khớp với đơn hàng.');
            }
            
            \Log::info('Barcode validation passed, updating order status');

            // Cập nhật trạng thái sang out_for_delivery (đang giao hàng)
            $order->status = 'out_for_delivery';
            $order->save();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Đã xác nhận lấy hàng và bắt đầu giao hàng!']);
            }
            return redirect()->route('shipper.dashboard')->with('success', 'Đã xác nhận lấy hàng và bắt đầu giao hàng!');
        }

        // Logic cũ cho các trạng thái khác
        $validated = $request->validate([
            'status' => 'required|string|in:out_for_delivery,delivered,failed_delivery',
            'reason' => 'nullable|string|max:255',
            'notes'  => 'nullable|string|max:500' // Ghi chú thêm
        ]);

        $order->status = $validated['status'];

        if ($validated['status'] === 'delivered') {
            $order->delivered_at = now();
            // Kiểm tra nếu phương thức thanh toán là COD thì cập nhật trạng thái thanh toán là 'paid'.
            if (strtolower($order->payment_method) === 'cod') {
                $order->payment_status = 'paid';
            }
        }

        if ($validated['status'] === 'failed_delivery') {
            $reason = $validated['reason'];
            if ($reason === 'other' && !empty($validated['notes'])) {
                $reason = $validated['notes'];
            }
            $order->failed_delivery_reason = $reason;
            $order->delivered_at = null;
        }

        $order->save();

        return redirect()->route('shipper.dashboard')->with('success', 'Cập nhật trạng thái đơn hàng ' . $order->order_code . ' thành công!');
    }
}