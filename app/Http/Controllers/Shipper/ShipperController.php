<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderFulfillment;
use Carbon\Carbon;


class ShipperController extends Controller
{
    /**
     * Dashboard: Hiển thị các GÓI HÀNG (Fulfillments) được gán cho shipper.
     */
    public function dashboard()
    {
        // THÊM LẠI DÒNG NÀY: Lấy thông tin shipper đang đăng nhập
        $shipper = Auth::user();
        $shipperId = $shipper->id;

        // Lấy các gói hàng đang chờ shipper lấy
        $fulfillmentsToPickup = OrderFulfillment::with('order')
            ->where('shipper_id', $shipperId)
            ->where('status', 'awaiting_shipment')
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy các gói hàng đang trên đường vận chuyển
        $fulfillmentsInTransit = OrderFulfillment::with('order')
            ->where('shipper_id', $shipperId)
            ->whereIn('status', ['shipped', 'out_for_delivery'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Dữ liệu cho thông báo
        $unreadNotificationsCount = $shipper->unreadNotifications()->count();
        $recentNotifications = $shipper->notifications()->take(5)->get()->map(function ($notification) {
             return [
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'time' => $notification->created_at->diffForHumans(),
                'color' => $notification->data['color'] ?? 'gray',
                'icon' => $notification->data['icon'] ?? 'info',
             ];
        });
        return view('shipper.dashboard', compact('shipper', 'fulfillmentsToPickup', 'fulfillmentsInTransit', 'unreadNotificationsCount', 'recentNotifications'));
    }

    /**
     * Xem chi tiết một ĐƠN HÀNG, nhưng ủy quyền dựa trên GÓI HÀNG.
     */
    public function show(Order $order)
    {
        // Ủy quyền: Kiểm tra xem shipper có được gán cho BẤT KỲ gói hàng nào của đơn này không.
        $isAuthorized = $order->fulfillments()->where('shipper_id', Auth::id())->exists();

        if (!$isAuthorized) {
            abort(403, 'Không có quyền truy cập đơn hàng này.');
        }

        $order->load('items');
        return view('shipper.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái của một GÓI HÀNG và có thể cả ĐƠN HÀNG.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Ủy quyền tương tự như hàm show
        $isAuthorized = $order->fulfillments()->where('shipper_id', Auth::id())->exists();
        if (!$isAuthorized) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        // Lấy fulfillment cụ thể nếu có fulfillment_id, nếu không thì lấy fulfillment đầu tiên
        $fulfillmentId = $request->input('fulfillment_id');
        if ($fulfillmentId) {
            $fulfillment = $order->fulfillments()->where('shipper_id', Auth::id())->where('id', $fulfillmentId)->first();
            if (!$fulfillment) {
                return back()->with('error', 'Không tìm thấy gói hàng được chỉ định hoặc bạn không có quyền cập nhật.');
            }
        } else {
            // Fallback: Lấy gói hàng đầu tiên mà shipper này được gán (để tương thích với code cũ)
            $fulfillment = $order->fulfillments()->where('shipper_id', Auth::id())->first();
            if (!$fulfillment) {
                return back()->with('error', 'Không tìm thấy gói hàng được gán cho bạn.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|string|in:shipped,delivered,failed_delivery',
            'barcode' => 'nullable|string',
            'reason' => 'nullable|string|max:255',
            'notes'  => 'nullable|string|max:500',
            'fulfillment_id' => 'nullable|integer|exists:order_fulfillments,id'
        ]);
        
        // Logic xác nhận lấy hàng (QUÉT BARCODE)
        if ($validated['status'] === 'shipped') {
            if ($fulfillment->status !== 'awaiting_shipment') {
                return back()->with('error', 'Gói hàng không ở trạng thái chờ lấy hàng.');
            }
            if ($request->barcode !== $order->order_code) {
                 return back()->with('error', 'Mã barcode không khớp với đơn hàng.');
            }
            
            // Cập nhật GÓI HÀNG
            $fulfillment->status = 'shipped'; // Đã lấy hàng, bắt đầu vận chuyển
            $fulfillment->shipped_at = now();
            $fulfillment->save();

            // Cập nhật trạng thái ĐƠN HÀNG dựa trên tất cả fulfillments
            $order->updateStatusBasedOnFulfillments();
            
            return redirect()->route('shipper.dashboard')->with('success', 'Đã xác nhận lấy hàng thành công!');
        }

        // Logic giao hàng (THÀNH CÔNG / THẤT BẠI)
        if ($validated['status'] === 'delivered') {
            $fulfillment->status = 'delivered';
            $fulfillment->delivered_at = now();
            
            // Cập nhật payment status cho COD nếu tất cả gói hàng đã delivered
            if (strtolower($order->payment_method) === 'cod') {
                // Kiểm tra xem tất cả fulfillments đã delivered chưa
                $allDelivered = $order->fulfillments()->where('status', '!=', 'delivered')->count() === 1; // chỉ còn gói hiện tại chưa delivered
                if ($allDelivered) {
                    $order->payment_status = 'paid';
                }
            }
        }

        if ($validated['status'] === 'failed_delivery') {
            $fulfillment->status = 'failed'; // Trạng thái của gói hàng
            $order->failed_delivery_reason = $validated['reason'] ?? $validated['notes'];
        }
        
        $fulfillment->save();
        
        // Cập nhật trạng thái ĐƠN HÀNG dựa trên tất cả fulfillments
        $order->updateStatusBasedOnFulfillments();
        
        // Lưu order sau khi cập nhật trạng thái
        $order->save();

        return redirect()->route('shipper.dashboard')->with('success', 'Cập nhật trạng thái thành công!');
    }


    /**
     * Lịch sử: Hiển thị các GÓI HÀNG đã được xử lý.
     */
    public function history()
    {
        $fulfillmentsHistory = OrderFulfillment::with('order')
            ->where('shipper_id', Auth::id())
            ->whereIn('status', ['delivered', 'failed', 'returned', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('shipper.history', compact('fulfillmentsHistory'));
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
    public function profile()
    {
        return view('shipper.profile', ['shipper' => Auth::user()]);
    }
    public function updateFulfillmentStatus(Request $request, OrderFulfillment $fulfillment)
{
    // 1. Ủy quyền: Kiểm tra xem shipper có đúng là người được gán cho gói hàng này không.
    if ($fulfillment->shipper_id !== Auth::id()) {
        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền cập nhật gói hàng này.'
        ], 403); // 403 Forbidden
    }

    // 2. Kiểm tra trạng thái hiện tại: Chỉ cho phép xác nhận khi gói hàng đang "chờ lấy hàng".
    if ($fulfillment->status !== 'awaiting_shipment') {
        return response()->json([
            'success' => false,
            'message' => 'Gói hàng này đã được lấy hoặc không ở trạng thái chờ lấy hàng.'
        ], 422); // 422 Unprocessable Entity
    }

    // Validate dữ liệu barcode gửi lên
    $validated = $request->validate([
        'barcode' => 'required|string',
    ]);

    if ($validated['barcode'] !== $fulfillment->tracking_code) {
        return response()->json([
            'success' => false,
            'message' => 'Mã vận đơn không khớp. Vui lòng thử lại.'
        ], 422);
    }


    // 4. Cập nhật trạng thái GÓI HÀNG (Fulfillment) thành "shipped".
    $fulfillment->status = 'shipped'; // Đã lấy hàng, bắt đầu vận chuyển
    $fulfillment->shipped_at = now();
    $fulfillment->save();

    // 5. Cập nhật trạng thái ĐƠN HÀNG dựa trên tất cả fulfillments
    $order = $fulfillment->order;
    $order->updateStatusBasedOnFulfillments();

    return response()->json([
        'success' => true,
        'message' => 'Đã xác nhận lấy hàng thành công!'
    ]);
}
public function showFulfillment(OrderFulfillment $fulfillment)
{
    // 1. Ủy quyền: Đảm bảo shipper này được gán cho gói hàng
    if ($fulfillment->shipper_id !== Auth::id()) {
        abort(403, 'Bạn không có quyền truy cập gói hàng này.');
    }

    // 2. Tải các quan hệ cần thiết để hiển thị
    // - order: thông tin chung của đơn hàng (người nhận, địa chỉ...)
    // - items.orderItem: các sản phẩm cụ thể trong gói hàng này
    $fulfillment->load(['order', 'items.orderItem']);
    
    // 3. Trả về view mới (hoặc view cũ đã được sửa)
    return view('shipper.show_fulfillment', compact('fulfillment'));
}




}