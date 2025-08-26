<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Services\InventoryCommitmentService;
use App\Services\AutoStockTransferService;
use App\Services\StockTransferWorkflowService;
use App\Services\TrackingCodeService;
use App\Models\CancellationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProductInventory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Tự động áp dụng OrderPolicy cho tất cả các phương thức
        $this->authorizeResource(Order::class, 'order');
    }

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
        // Force refresh từ database để đảm bảo dữ liệu mới nhất
        $order->refresh();

        $order->load([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name,slug',
            'items.productVariant.product.coverImage',
            'items.productVariant.primaryImage',
            'processor:id,name',
            'cancellationRequest',
            'shipper:id,name,email,phone_number',
            'shippingProvince:code,name,name_with_type',
            'shippingWard:code,name,name_with_type,path_with_type',
            'storeLocation:id,name,address,phone,province_code,district_code,ward_code',
            'storeLocation.province:code,name,name_with_type',
            'storeLocation.district:code,name,name_with_type',
            'storeLocation.ward:code,name,name_with_type',
            'couponUsages.coupon:id,code,type,value,description',
            // Load thông tin fulfillments cho mô hình đa kho
            'fulfillments:id,order_id,store_location_id,shipper_id,tracking_code,shipping_carrier,status,shipped_at,delivered_at,estimated_delivery_date,shipping_fee',
            'fulfillments.storeLocation:id,name,address,phone,province_code,district_code,ward_code,type',
            'fulfillments.storeLocation.province:code,name,name_with_type',
            'fulfillments.storeLocation.district:code,name,name_with_type',
            'fulfillments.storeLocation.ward:code,name,name_with_type',
            'fulfillments.items',
            'fulfillments.items.orderItem:id,product_variant_id,product_name,variant_attributes,sku,quantity,price,total_price',
            'fulfillments.items.orderItem.productVariant:id,sku,product_id,primary_image_id',
            'fulfillments.items.orderItem.productVariant.product:id,name',
            'fulfillments.items.orderItem.productVariant.primaryImage',
            'fulfillments.items.orderItem.productVariant.product.coverImage',
            // REMOVED: Package functionality - now using order_fulfillments directly
        ]);

        // REMOVED: Package refresh logic - now using order_fulfillments directly

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
    public function view(Order $order)
    {
        // Force refresh từ database để đảm bảo dữ liệu mới nhất
        $order->refresh();

        $order->load([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name,slug',
            'items.productVariant.product.coverImage',
            'items.productVariant.primaryImage',
            'processor:id,name',
            'cancellationRequest',
            'shipper:id,name',
            'couponUsages.coupon:id,code,type,value,description',
            // Load thông tin fulfillments cho mô hình đa kho
            'fulfillments:id,order_id,store_location_id,shipper_id,tracking_code,shipping_carrier,status,shipped_at,delivered_at,estimated_delivery_date,shipping_fee',
            'fulfillments.storeLocation:id,name,address,phone,province_code,district_code,ward_code,type',
            'fulfillments.storeLocation.province:code,name,name_with_type',
            'fulfillments.storeLocation.district:code,name,name_with_type',
            'fulfillments.storeLocation.ward:code,name,name_with_type',
            'fulfillments.items',
            'fulfillments.items.orderItem:id,product_variant_id,product_name,variant_attributes,sku,quantity,price,total_price',
            'fulfillments.items.orderItem.productVariant:id,sku,product_id,primary_image_id',
            'fulfillments.items.orderItem.productVariant.product:id,name',
            'fulfillments.items.orderItem.productVariant.primaryImage',
            'fulfillments.items.orderItem.productVariant.product.coverImage',
            // Load thông tin packages cho mô hình quản lý gói hàng - Force fresh từ DB
            'fulfillments.packages:id,order_fulfillment_id,package_code,description,shipping_carrier,tracking_code,status,shipped_at,delivered_at',
            'fulfillments.packages.fulfillmentItems:id,package_id,order_fulfillment_id,order_item_id,quantity',
            'fulfillments.packages.fulfillmentItems.orderItem:id,product_variant_id,product_name,variant_attributes,sku,quantity,price,total_price',
            'fulfillments.packages.statusHistory:id,package_id,status,timestamp,notes,created_by',
            'fulfillments.packages.statusHistory.createdBy:id,name',
        ]);

        // Force refresh packages để đảm bảo trạng thái mới nhất
        foreach ($order->fulfillments as $fulfillment) {
            foreach ($fulfillment->packages as $package) {
                $package->refresh();
            }
        }
        $order->load('cancellationRequest');
        // dd($order->toArray());

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
                case Order::STATUS_PROCESSING: // Đang xử lý
                    if ($oldStatus === Order::STATUS_PENDING_CONFIRMATION) {
                        // Tự động tạo tracking code khi xác nhận đơn hàng
                        $trackingCodeService = new TrackingCodeService();
                        $trackingCode = $trackingCodeService->assignTrackingCodeToOrder($order);

                        \Log::info('Order confirmed and tracking code generated', [
                            'order_id' => $order->id,
                            'order_code' => $order->order_code,
                            'tracking_code' => $trackingCode,
                            'confirmed_by' => auth()->id()
                        ]);

                        // Logic tạo phiếu chuyển kho đã được chuyển sang OrderObserver
                        // để sử dụng FulfillmentStockTransferService thay vì AutoStockTransferService
                    }
                    break;

                case Order::STATUS_OUT_FOR_DELIVERY: // Đang giao hàng
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

                    // TỰ ĐỘNG: Hủy các phiếu chuyển kho tự động liên quan
                    $this->cancelRelatedAutoStockTransfers($order);
                    break;

                case Order::STATUS_FAILED_DELIVERY: // Giao thất bại
                    $updateData['failed_delivery_reason'] = $request->failed_delivery_reason;
                    break;


            }

            // BƯỚC 5: Cập nhật database một lần duy nhất
            $order->update($updateData);

            // BƯỚC 5.1: Cập nhật trạng thái packages theo trạng thái đơn hàng
            $this->updatePackageStatusBasedOnOrderStatus($order, $request->status, $oldStatus);

            // BƯỚC 5.5: Xử lý tồn kho theo trạng thái
            $inventoryService = new InventoryCommitmentService();

            switch ($request->status) {
                case Order::STATUS_OUT_FOR_DELIVERY: // Xuất kho thực tế
                    if ($oldStatus !== Order::STATUS_OUT_FOR_DELIVERY) {
                        $inventoryService->fulfillInventoryForOrder($order);
                        \Log::info("Đã xuất kho cho đơn hàng #{$order->order_code}");
                    }
                    break;

                case Order::STATUS_CANCELLED: // Thả tồn kho đã tạm giữ
                case Order::STATUS_RETURNED:
                    if ($oldStatus !== Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_RETURNED) {
                        $inventoryService->releaseInventoryForOrder($order);
                        \Log::info("Đã thả tồn kho cho đơn hàng #{$order->order_code}");
                    }
                    break;
            }

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

    public function getShippers(Request $request)
    {
        try {
            $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'shipper');
            })->select('id', 'name', 'email', 'phone_number')
                ->where('status', 'active');

            // Nếu có order_id, lọc shipper theo warehouse của đơn hàng
            if ($request->filled('order_id')) {
                $order = Order::find($request->order_id);
                if ($order && $order->store_location_id) {
                    $query->whereHas('warehouseAssignments', function ($q) use ($order) {
                        $q->where('store_location_id', $order->store_location_id);
                    });
                }
            }

            $shippers = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $shippers
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching shippers:', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'order_id' => $request->order_id ?? null
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
            if ($order->status !== Order::STATUS_PROCESSING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể gán shipper cho đơn hàng đang ở trạng thái "Đang xử lý".',
                ], 422);
            }

            // Kiểm tra đơn hàng phải có mã vận đơn từ fulfillment
            $fulfillment = $order->fulfillments()->whereNotNull('tracking_code')->first();
            if (!$fulfillment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng chưa có mã vận đơn. Vui lòng xác nhận đơn hàng trước khi gán shipper.',
                ], 422);
            }

            // Kiểm tra điều kiện fulfillment trước khi gán shipper
            $fulfillmentCheckService = new \App\Services\OrderFulfillmentCheckService();
            $fulfillmentCheck = $fulfillmentCheckService->canAssignShipper($order);

            if (!$fulfillmentCheck['can_assign']) {
                return response()->json([
                    'success' => false,
                    'message' => $fulfillmentCheck['reason'],
                    'requires_transfer' => $fulfillmentCheck['requires_transfer'],
                    'transfer_info' => $fulfillmentCheck['transfer_info'] ?? null,
                    'estimated_arrival' => $fulfillmentCheck['estimated_arrival'] ?? null
                ], 422);
            }

            // Kiểm tra xem đơn hàng có phải trường hợp đặc biệt không
            $isSpecialCase = $fulfillmentCheck['is_special_case'] ?? false;
            $requiresTransfer = $fulfillmentCheck['requires_transfer'] ?? false;

            if ($isSpecialCase) {
                // Trường hợp đặc biệt (phí 25k/40k): chuyển sang external_shipping
                $order->update([
                    'processed_by' => auth()->id(),
                    'status' => Order::STATUS_EXTERNAL_SHIPPING
                ]);

                // Cập nhật trạng thái fulfillments thành external_shipping
                $order->fulfillments()->update([
                    'status' => \App\Models\OrderFulfillment::STATUS_EXTERNAL_SHIPPING
                ]);
            } else {
                // Gán shipper cho tất cả fulfillments của đơn hàng
                $order->fulfillments()->update([
                    'shipper_id' => $request->shipper_id,
                    'status' => \App\Models\OrderFulfillment::STATUS_SHIPPED,
                    'shipped_at' => now()
                ]);

                // Cập nhật trạng thái đơn hàng
                $order->update([
                    'shipped_by' => $request->shipper_id,
                    'processed_by' => auth()->id(),
                    'status' => Order::STATUS_OUT_FOR_DELIVERY,
                    'shipped_at' => now()
                ]);
            }

            // Load lại dữ liệu để trả về
            $order->load(['shipper:id,name,email,phone_number', 'fulfillments.shipper:id,name,email,phone_number']);

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

    /**
     * Hủy các phiếu chuyển kho tự động liên quan đến đơn hàng
     */
    private function cancelRelatedAutoStockTransfers(Order $order)
    {
        try {
            // Tìm các phiếu chuyển kho tự động liên quan thông qua notes
            $relatedTransfers = \App\Models\StockTransfer::where('notes', 'like', '%Order:' . $order->order_code . '%')
                ->where('status', '!=', 'cancelled')
                ->get();

            if ($relatedTransfers->isEmpty()) {
                \Log::info('Không tìm thấy phiếu chuyển kho tự động nào liên quan đến đơn hàng', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]);
                return;
            }

            $workflowService = new \App\Services\StockTransferWorkflowService();
            $cancelledCount = 0;

            foreach ($relatedTransfers as $transfer) {
                try {
                    $result = $workflowService->cancelTransfer($transfer);
                    if ($result['success']) {
                        $cancelledCount++;
                        \Log::info('Đã hủy phiếu chuyển kho tự động', [
                            'transfer_id' => $transfer->id,
                            'transfer_code' => $transfer->transfer_code,
                            'order_id' => $order->id,
                            'order_code' => $order->order_code,
                            'cancelled_by' => auth()->id()
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Lỗi khi hủy phiếu chuyển kho tự động', [
                        'transfer_id' => $transfer->id,
                        'transfer_code' => $transfer->transfer_code,
                        'order_id' => $order->id,
                        'order_code' => $order->order_code,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            \Log::info('Hoàn thành hủy phiếu chuyển kho tự động cho đơn hàng', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'total_transfers' => $relatedTransfers->count(),
                'cancelled_count' => $cancelledCount,
                'cancelled_by' => auth()->id()
            ]);

        } catch (\Exception $e) {
            \Log::error('Lỗi khi hủy phiếu chuyển kho tự động liên quan đến đơn hàng', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Cập nhật trạng thái order_fulfillments dựa trên trạng thái đơn hàng
     */
    private function updatePackageStatusBasedOnOrderStatus(Order $order, string $newStatus, string $oldStatus)
    {
        try {
            // Lấy tất cả order_fulfillments của đơn hàng
            $fulfillments = $order->fulfillments;

            if ($fulfillments->isEmpty()) {
                \Log::info('Không có order_fulfillments nào để cập nhật cho đơn hàng', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]);
                return;
            }

            $fulfillmentStatus = null;

            // Mapping trạng thái đơn hàng sang trạng thái order_fulfillment
            switch ($newStatus) {
                case Order::STATUS_PENDING_CONFIRMATION:
                    $fulfillmentStatus = OrderFulfillment::STATUS_PENDING;
                    break;

                case Order::STATUS_PROCESSING:
                    $fulfillmentStatus = OrderFulfillment::STATUS_PROCESSING;
                    break;

                case Order::STATUS_OUT_FOR_DELIVERY:
                    $fulfillmentStatus = OrderFulfillment::STATUS_SHIPPED;
                    break;

                case Order::STATUS_EXTERNAL_SHIPPING:
                    $fulfillmentStatus = OrderFulfillment::STATUS_EXTERNAL_SHIPPING;
                    break;

                case Order::STATUS_DELIVERED:
                    $fulfillmentStatus = OrderFulfillment::STATUS_DELIVERED;
                    break;

                case Order::STATUS_CANCELLED:
                    $fulfillmentStatus = OrderFulfillment::STATUS_CANCELLED;
                    break;

                case Order::STATUS_FAILED_DELIVERY:
                    $fulfillmentStatus = OrderFulfillment::STATUS_FAILED;
                    break;

                case Order::STATUS_RETURNED:
                    $fulfillmentStatus = OrderFulfillment::STATUS_RETURNED;
                    break;
            }

            // Cập nhật trạng thái cho tất cả order_fulfillments nếu có mapping
            if ($fulfillmentStatus) {
                $updatedCount = $order->fulfillments()->update([
                    'status' => $fulfillmentStatus
                ]);

                \Log::info('Đã cập nhật trạng thái order_fulfillments theo đơn hàng', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'old_order_status' => $oldStatus,
                    'new_order_status' => $newStatus,
                    'fulfillment_status' => $fulfillmentStatus,
                    'fulfillments_count' => $fulfillments->count(),
                    'updated_count' => $updatedCount,
                    'updated_by' => auth()->id()
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Lỗi khi cập nhật trạng thái order_fulfillments', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'new_status' => $newStatus,
                'old_status' => $oldStatus,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }
    public function showCancellationRequest(CancellationRequest $cancellationRequest)
    {
        $cancellationRequest->load(['order.items', 'user']);
        return view('admin.orders.cancellation-show', compact('cancellationRequest'));
    }

    /**
     * Phê duyệt yêu cầu hủy đơn và hoàn tiền.
     */
    public function approveCancellationRequest(CancellationRequest $cancellationRequest)
    {
        if ($cancellationRequest->status !== 'pending_review') {
            return redirect()->back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái của yêu cầu hủy
            $cancellationRequest->status = 'approved';
            $cancellationRequest->approved_by = Auth::id();
            $cancellationRequest->save();

            // Lấy và cập nhật đơn hàng liên quan
            $order = $cancellationRequest->order;
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'cancelled_at' => now()
            ]);

            // ===== LOGIC HOÀN KHO ĐA ĐỊA ĐIỂM =====
            $order->load('items.productVariant');

            // Xác định kho đã xử lý đơn hàng này
            // Giả định rằng đơn hàng có trường `store_location_id`
            $storeLocationId = $order->store_location_id;

            if ($storeLocationId) {
                foreach ($order->items as $item) {
                    if ($item->productVariant) {
                        // Tìm bản ghi tồn kho tương ứng tại kho đã xử lý và cập nhật
                        ProductInventory::where('product_variant_id', $item->product_variant_id)
                                        ->where('store_location_id', $storeLocationId)
                                        ->where('inventory_type', 'new') // Giả định hoàn trả hàng mới
                                        ->increment('quantity', $item->quantity);
                    }
                }
            } else {
                // Ghi log cảnh báo nếu không tìm thấy kho để hoàn trả
                Log::warning("Không thể hoàn trả tồn kho cho đơn hàng #{$order->order_code} vì không xác định được store_location_id.");
            }
            // ===== KẾT THÚC LOGIC HOÀN KHO =====

            Log::info("Admin (ID: " . Auth::id() . ") đã duyệt yêu cầu hủy cho đơn hàng #{$order->order_code}.");

            DB::commit();

            // Chuyển hướng về trang chi tiết đơn hàng để thấy sự thay đổi
            return redirect()->route('admin.orders.index')
                ->with('success', "Đã duyệt yêu cầu hủy cho đơn hàng #{$order->order_code}. Tồn kho đã được cập nhật.");

        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác tất cả thay đổi nếu có lỗi
            Log::error("Lỗi khi duyệt yêu cầu hủy ID #{$cancellationRequest->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Đã có lỗi xảy ra trong quá trình xử lý.');
        }
    }


}
