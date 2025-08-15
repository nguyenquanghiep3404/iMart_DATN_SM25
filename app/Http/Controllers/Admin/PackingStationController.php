<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventorySerial;
use App\Models\OrderItemSerial;
use App\Models\InventoryMovement;
use App\Models\Product; 

/**
 * Class PackingStationController
 * @package App\Http\Controllers\Admin
 *
 * Controller này quản lý logic cho giao diện Trạm Đóng Gói.
 */
class PackingStationController extends Controller
{
    /**
     * Hiển thị giao diện chính của Trạm Đóng Gói.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // View này chính là file HTML bạn đã cung cấp
        return view('admin.packing_station.index');
    }

    /**
     * Lấy danh sách các đơn hàng cần xử lý (đang chờ đóng gói).
     * Trạng thái hợp lệ là 'awaiting_shipment'.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersForPacking()
{
    // Lấy các đơn hàng ở trạng thái 'processing' thay vì 'awaiting_shipment'
    $orders = Order::where('status', 'processing') 
        ->select('id', 'order_code', 'customer_name', 'created_at')
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json($orders);
}


    /**
     * Lấy thông tin chi tiết của một đơn hàng.
     * Eager load các sản phẩm và kiểm tra xem sản phẩm nào cần quét serial.
     *
     * @param int $id ID của đơn hàng
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetails($id)
    {
        $order = Order::with(['items.variant.product'])->findOrFail($id);

        // Xây dựng lại response để thêm cờ 'requires_imei'
        $response = $order->toArray();
        foreach ($response['items'] as $key => $item) {
            // Dựa vào CSDL, ta có thể giả định một sản phẩm cần serial nếu
            // nó có theo dõi trong bảng inventory_serials.
            // Một cách tiếp cận đơn giản là kiểm tra xem có bất kỳ serial nào
            // tồn tại cho product_variant_id này không.
            // Trong thực tế, có thể có một cờ `manage_serial` trên bảng products.
            
            // Ở đây, ta sẽ kiểm tra xem sản phẩm có được quản lý bằng serial không.
            // Logic này cần được điều chỉnh cho phù hợp với quy trình nghiệp vụ của bạn.
            // Ví dụ: kiểm tra category của sản phẩm.
            $product = Product::find($item['variant']['product_id']);
            $response['items'][$key]['requires_imei'] = $this->productRequiresSerial($product);
            
            // Khởi tạo các trường cho front-end
            $response['items'][$key]['imei_input'] = '';
            $response['items'][$key]['imei_scanned'] = false;
        }

        return response()->json($response);
    }

    /**
     * Xác thực một mã IMEI/Serial được quét.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateImei(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string',
            'product_variant_id' => 'required|integer|exists:product_variants,id',
        ]);

        $serial = InventorySerial::where('serial_number', $validated['serial_number'])
            ->where('product_variant_id', $validated['product_variant_id'])
            ->first();

        if (!$serial) {
            return response()->json([
                'success' => false,
                'message' => 'Mã Serial/IMEI không tồn tại cho sản phẩm này.'
            ], 404);
        }

        if ($serial->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => "Mã Serial/IMEI đã được sử dụng hoặc đang ở trạng thái không hợp lệ ({$serial->status})."
            ], 400);
        }

        return response()->json(['success' => true, 'message' => 'Mã hợp lệ.']);
    }

    /**
     * Xác nhận đóng gói, cập nhật CSDL và chuẩn bị in phiếu.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $orderId ID của đơn hàng
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPacking(Request $request, $orderId)
{
    $validated = $request->validate([
        'items' => 'sometimes|array', // 'sometimes' allows it to be empty if no items require IMEI
        'items.*.order_item_id' => 'required|integer|exists:order_items,id',
        'items.*.product_variant_id' => 'required|integer|exists:product_variants,id',
        'items.*.serial_number' => 'required|string', // Only items with serials are sent
    ]);

    try {
        DB::transaction(function () use ($validated, $orderId) {
            $order = Order::with('items.variant.product')->findOrFail($orderId);
            $packer = auth()->user();

            // 1. Kiểm tra trạng thái đơn hàng (Nên là 'processing')
            if ($order->status !== 'processing') { // Sửa từ 'awaiting_shipment' thành 'processing'
                throw new \Exception('Đơn hàng không ở trạng thái "Đang xử lý".');
            }
            
            // 2. Xác định kho hàng từ người đóng gói (packer)
            $storeLocation = $packer->storeLocations()->first();
            if (!$storeLocation) {
                throw new \Exception('Tài khoản của bạn chưa được gán vào kho nào. Vui lòng liên hệ quản trị viên.');
            }
            $storeLocationId = $storeLocation->id;

            // Tạo một map các serial đã quét để dễ truy xuất
            $scannedSerialsMap = collect($validated['items'] ?? [])->keyBy('order_item_id');

            // 3. Lặp qua TẤT CẢ các sản phẩm trong đơn hàng
            foreach ($order->items as $orderItem) {
                // Lấy số lượng tồn kho hiện tại để tính toán `quantity_after_change`
                $inventory = \App\Models\ProductInventory::where('product_variant_id', $orderItem->product_variant_id)
                    ->where('store_location_id', $storeLocationId)
                    ->where('inventory_type', 'new') // Giả sử bán hàng mới
                    ->first();

                if (!$inventory || $inventory->quantity < $orderItem->quantity) {
                    throw new \Exception("Sản phẩm '{$orderItem->product_name}' không đủ tồn kho tại kho hiện tại.");
                }
                $quantityAfterChange = $inventory->quantity - $orderItem->quantity;

                // 4. Xử lý sản phẩm có yêu cầu serial
                if ($this->productRequiresSerial($orderItem->variant->product)) {
                    if (!$scannedSerialsMap->has($orderItem->id)) {
                        throw new \Exception("Sản phẩm '{$orderItem->product_name}' yêu cầu quét serial nhưng không có dữ liệu.");
                    }
                    $serialNumber = $scannedSerialsMap[$orderItem->id]['serial_number'];

                    // Cập nhật bảng `inventory_serials`
                    $inventorySerial = InventorySerial::where('serial_number', $serialNumber)
                        ->where('product_variant_id', $orderItem->product_variant_id)
                        ->where('status', 'available')
                        ->firstOrFail(); // Đảm bảo serial hợp lệ và khả dụng

                    $inventorySerial->update(['status' => 'sold']);

                    // Ghi nhận vào bảng `order_item_serials`
                    OrderItemSerial::create([
                        'order_item_id' => $orderItem->id,
                        'product_variant_id' => $orderItem->product_variant_id,
                        'serial_number' => $serialNumber,
                        'status' => 'sold',
                    ]);
                }
                
                // 5. Trừ tồn kho chung và tạo Inventory Movement cho TẤT CẢ sản phẩm
                $inventory->decrement('quantity', $orderItem->quantity);

                InventoryMovement::create([
                    'product_variant_id' => $orderItem->product_variant_id,
                    'store_location_id' => $storeLocationId, // << FIX: Sử dụng ID kho của người đóng gói
                    'lot_id' => null, // Cần logic để lấy lot_id nếu bạn quản lý theo lô
                    'inventory_type' => 'available',
                    'quantity_change' => -$orderItem->quantity,
                    'quantity_after_change' => $quantityAfterChange,
                    'reason' => 'Packed for Order',
                    'reference_type' => Order::class,
                    'reference_id' => $orderId,
                    'user_id' => $packer->id,
                ]);
            }

            // 6. Cập nhật trạng thái đơn hàng chính
            $order->update([
                'status' => 'awaiting_shipment', // << FIX: Trạng thái tiếp theo là "Chờ vận chuyển"
                'processed_by' => $packer->id,
                'store_location_id' => $storeLocationId // Cập nhật luôn kho xử lý cho đơn hàng
            ]);
            
            // 7. Kiểm tra và tạo phiếu chuyển kho tự động nếu cần thiết
            $fulfillmentCheckService = new \App\Services\OrderFulfillmentCheckService();
            $autoTransferResult = $fulfillmentCheckService->createAutoTransferIfNeeded($order);
            
            if ($autoTransferResult['created']) {
                \Log::info("Đã tạo phiếu chuyển kho tự động cho đơn hàng {$order->order_code} sau khi đóng gói", [
                    'order_id' => $order->id,
                    'transfers' => $autoTransferResult['transfers']
                ]);
            }
        });

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Xác nhận thất bại: ' . $e->getMessage()], 500);
    }

    return response()->json([
        'success' => true,
        'message' => 'Đơn hàng đã được xác nhận đóng gói thành công!',
    ]);
}


    /**
     * Hàm helper để kiểm tra xem một sản phẩm có cần quét serial hay không.
     * Logic này nên được tùy chỉnh cho phù hợp với hệ thống của bạn.
     *
     * @param \App\Models\Product $product
     * @return bool
     */
    private function productRequiresSerial(Product $product): bool
    {
        // Ví dụ: Kiểm tra xem sản phẩm có thuộc danh mục 'Điện thoại', 'Laptop' không.
        // Bạn cần lấy `category_id` và so sánh.
        // Hoặc bạn có thể thêm một trường `requires_serial` vào bảng `products`.
        // Dưới đây là một ví dụ giả định đơn giản.
        if (in_array($product->type, ['variable', 'simple'])) {
            // Một logic tốt hơn là kiểm tra xem sản phẩm có được cấu hình để theo dõi serial hay không
            // Ví dụ: return $product->serial_tracking_enabled;
            // Ở đây, ta giả định tất cả sản phẩm trong CSDL đều có thể cần serial
            // và để front-end hiển thị đúng dựa trên dữ liệu mẫu
            return true; 
        }
        return false;
    }
}