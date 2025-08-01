<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name,slug',
            'items.productVariant.product.coverImage',
            'items.productVariant.primaryImage',
            'processor:id,name',
            'shipper:id,name'
        ]);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        if ($request->filled('date_range')) {
            $query->whereDate('created_at', $request->get('date_range'));
        }
        $query->orderBy('created_at', 'desc');
        $orders = $query->paginate(10);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ]
            ]);
        }
        return view('admin.orders.index', compact('orders'));
    }
    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name,slug',
            'items.productVariant.product.coverImage',
            'items.productVariant.primaryImage',
            'processor:id,name',
            'shipper:id,name,email,phone_number',
            'shippingProvince:code,name,name_with_type',
            'shippingWard:code,name,name_with_type,path_with_type',
            'storeLocation:id,name,address,phone,province_code,district_code,ward_code',
            'storeLocation.province:code,name,name_with_type',
            'storeLocation.district:code,name,name_with_type',
            'storeLocation.ward:code,name,name_with_type',
        ]);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
    public function view(Order $order)
    {
        $order->load([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name,slug',
            'items.productVariant.product.coverImage',
            'items.productVariant.primaryImage',
            'processor:id,name',
            'shipper:id,name'
        ]);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(OrderRequest $request, Order $order)
    {
        // BƯỚC 1: Kiểm tra quyền chỉnh sửa
        if (!$order->isEditable() && $order->status !== $request->status) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã hoàn thành hoặc bị hủy, không thể thay đổi trạng thái.',
                'errors' => ['status' => ['Đơn hàng không thể chỉnh sửa.']]
            ], 422);
        }

        try {
            // BƯỚC 2: Lưu trạng thái cũ để ghi log
            $oldStatus = $order->status;

            // BƯỚC 3: Chuẩn bị dữ liệu cập nhật cơ bản
            $updateData = [
                'status' => $request->status,
                'processed_by' => auth()->id(), // Ai cập nhật
            ];

            // Thêm ghi chú admin nếu có
            if ($request->filled('admin_note')) {
                $updateData['admin_note'] = $request->admin_note;
            }

            // BƯỚC 4: Xử lý logic đặc biệt theo từng trạng thái
            switch ($request->status) {
                case Order::STATUS_SHIPPED: // Xuất kho
                case Order::STATUS_OUT_FOR_DELIVERY: // Đang giao
                    if ($request->filled('shipped_by')) {
                        $updateData['shipped_by'] = $request->shipped_by;
                    }
                    break;

                case Order::STATUS_DELIVERED: // Giao thành công
                    $updateData['delivered_at'] = now();
                    // TỰ ĐỘNG: COD + chưa thanh toán → đánh dấu đã thanh toán
                    if ($order->payment_method === 'cod' && $order->payment_status === Order::PAYMENT_PENDING) {
                        $updateData['payment_status'] = Order::PAYMENT_PAID;
                    }
                    break;

                case Order::STATUS_CANCELLED: // Hủy đơn
                    $updateData['cancelled_at'] = now();
                    $updateData['cancellation_reason'] = $request->cancellation_reason;
                    // TỰ ĐỘNG: Đã thanh toán → hoàn tiền
                    if ($order->payment_status === Order::PAYMENT_PAID) {
                        $updateData['payment_status'] = Order::PAYMENT_REFUNDED;
                    }
                    break;

                case Order::STATUS_FAILED_DELIVERY: // Giao thất bại
                    $updateData['failed_delivery_reason'] = $request->failed_delivery_reason;
                    break;

                case Order::STATUS_RETURNED: // Trả hàng
                    $updateData['cancelled_at'] = now();
                    $updateData['cancellation_reason'] = 'Returned by customer';
                    // TỰ ĐỘNG: Hoàn tiền
                    if ($order->payment_status === Order::PAYMENT_PAID) {
                        $updateData['payment_status'] = Order::PAYMENT_REFUNDED;
                    }
                    break;
            }

            // BƯỚC 5: Cập nhật database một lần duy nhất
            $order->update($updateData);

            // Kích hoạt cộng điểm thưởng
            if ($order->status === Order::STATUS_DELIVERED) {
            \Log::info("Kích hoạt sự kiện OrderDelivered cho đơn hàng #{$order->order_code}");
            event(new \App\Events\OrderDelivered($order));
        }

            // BƯỚC 6: Ghi log để theo dõi
            \Log::info('Order status updated', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
                'updated_by' => auth()->id(),
                'admin_note' => $request->admin_note
            ]);

            // BƯỚC 7: Trả về kết quả
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'data' => [
                    'status' => $order->status,
                    'status_text' => $order->status_text, // Sử dụng accessor trong Model
                    'admin_note' => $order->admin_note,
                    'cancellation_reason' => $order->cancellation_reason,
                    'payment_status' => $order->payment_status,
                    'delivered_at' => $order->delivered_at?->format('d/m/Y H:i'),
                    'cancelled_at' => $order->cancelled_at?->format('d/m/Y H:i')
                ]
            ]);
        } catch (\Exception $e) {
            // Ghi log lỗi
            \Log::error('Error updating order status:', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái đơn hàng. Vui lòng thử lại sau.',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getShippers()
    {
        try {
            $shippers = User::whereHas('roles', function ($query) {
                $query->where('name', 'shipper');
            })->select('id', 'name', 'email', 'phone_number')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $shippers
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching shippers:', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách shipper.',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server'
            ], 500);
        }
    }

    public function assignShipper(Request $request, Order $order)
    {
        $request->validate([
            'shipper_id' => 'required|exists:users,id'
        ]);

        try {
            // Kiểm tra xem user có phải là shipper không
            $shipper = User::whereHas('roles', function ($query) {
                $query->where('name', 'shipper');
            })->find($request->shipper_id);

            if (!$shipper) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng được chọn không phải là shipper.',
                ], 422);
            }

            // Kiểm tra trạng thái đơn hàng có thể gán shipper không
            if ($order->status !== Order::STATUS_AWAITING_SHIPMENT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể gán shipper cho đơn hàng đang ở trạng thái "Chờ giao hàng".',
                ], 422);
            }

            // Cập nhật shipper cho đơn hàng
            $order->update([
                'shipped_by' => $request->shipper_id,
                'processed_by' => auth()->id()
            ]);

            // Load lại dữ liệu để trả về
            $order->load('shipper:id,name,email,phone_number');

            // Ghi log
            \Log::info('Shipper assigned to order', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'shipper_id' => $request->shipper_id,
                'shipper_name' => $shipper->name,
                'assigned_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gán shipper thành công!',
                'data' => [
                    'order' => $order,
                    'shipper' => $order->shipper
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi chỉ định người gửi hàng:', [
                'order_id' => $order->id,
                'shipper_id' => $request->shipper_id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể gán shipper. Vui lòng thử lại sau.',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
