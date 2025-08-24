<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderFulfillmentItem;
use App\Models\ProductInventory;
use App\Models\ShippingTransitTime;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use App\Models\InventoryMovement;
use App\Services\AutoStockTransferService;
use App\Services\DeliveryOptimizationService;
use App\Services\TrackingCodeService;
// REMOVED: PackageService - now using order_fulfillments directly
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Lớp FulfillmentService chịu trách nhiệm xử lý tất cả các logic nghiệp vụ
 * liên quan đến việc xử lý và chia đơn hàng (Order Fulfillment).
 */
class FulfillmentService
{

protected function groupOrderItemsByLocation(Order $order): array
{
$itemsByLocation = [];
$unfulfillableItems = [];
$autoTransferService = new AutoStockTransferService();

// Lấy thông tin vùng miền của địa chỉ giao hàng
$destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
$destinationRegion = $destinationProvince->region ?? null;

foreach ($order->items as $item) {
// Xây dựng câu truy vấn tìm warehouse có đủ hàng (ưu tiên warehouse)
$inventoryQuery = ProductInventory::where('product_variant_id', $item->product_variant_id)
 ->where('quantity', '>=', $item->quantity)
 ->where('inventory_type', 'new')
 ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
 ->where('store_locations.type', 'warehouse') // Chỉ tìm trong warehouse
 ->where('store_locations.is_active', true)
 ->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code');

// Ưu tiên warehouse cùng vùng miền với khách hàng
if ($destinationRegion) {
 $inventoryQuery->orderByRaw("CASE WHEN provinces_old.region = ? THEN 0 ELSE 1 END", [$destinationRegion]);
}

$inventory = $inventoryQuery->select('product_inventories.*')->first();

if ($inventory) {
 // Tìm thấy hàng ở warehouse
 $locationId = $inventory->store_location_id;
 if (!isset($itemsByLocation[$locationId])) {
 $itemsByLocation[$locationId] = [];
 }
 $itemsByLocation[$locationId][] = $item;
} else {
 // Không tìm thấy hàng ở warehouse, thử tự động chuyển hàng từ store
 $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);
 
 if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
 // Đã tạo phiếu chuyển kho, thông báo cho người dùng
 throw new Exception(
 'Đã tạo phiếu chuyển kho tự động cho SKU: ' . $item->sku . 
 '. Vui lòng đợi xử lý phiếu chuyển kho trước khi đặt hàng.'
 );
 } else {
 // Không thể tạo phiếu chuyển kho hoặc không có hàng ở store
 $unfulfillableItems[] = $item->sku;
 }
}
}

if (!empty($unfulfillableItems)) {
throw new Exception('Không đủ hàng để xử lý cho các SKU: ' . implode(', ', $unfulfillableItems));
}

return $itemsByLocation;
}

/**
 * Tính toán các tùy chọn giao hàng, bao gồm ngày giao hàng và phí vận chuyển.
 * Tích hợp logic tối ưu hóa phương thức giao hàng
 *
 * @param Order $order Đối tượng đơn hàng (thường là từ giỏ hàng).
 * @return array Mảng chứa thông tin về tùy chọn giao hàng.
 */
public function calculateDeliveryOptions(Order $order): array
{
try {
 // Kiểm tra xem có cần chuyển kho không
 $deliveryOptimizationService = new DeliveryOptimizationService();
 $transferCheck = $deliveryOptimizationService->needsStockTransfer($order);
 
 if ($transferCheck['needs_transfer']) {
 // Nếu cần chuyển kho, thử tạo phiếu chuyển kho tự động
 $autoTransferService = new AutoStockTransferService();
 $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);
 
 if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
 return [
 'success' => false,
 'message' => 'Đã tạo phiếu chuyển kho tự động. Vui lòng đợi xử lý phiếu chuyển kho trước khi đặt hàng.',
 'transfer_info' => $transferResult['transfers_created'],
 'reason' => $transferCheck['reason']
 ];
 }
 }
 
 // Lấy tất cả tùy chọn giao hàng có thể
 $deliveryOptions = $deliveryOptimizationService->getAllDeliveryOptions($order);
 
 if (!$deliveryOptions['success']) {
 // Fallback về logic cũ nếu service mới không hoạt động
 return $this->calculateDeliveryOptionsLegacy($order);
 }
 
 return [
 'success' => true,
 'delivery_options' => $deliveryOptions['options'],
 'recommended_option' => $deliveryOptions['recommended'],
 'transfer_check' => $transferCheck
 ];
 
} catch (Exception $e) {
 // Fallback về logic cũ nếu có lỗi
 return $this->calculateDeliveryOptionsLegacy($order);
}
}

/**
 * Logic tính toán giao hàng cũ (fallback)
 */
private function calculateDeliveryOptionsLegacy(Order $order): array
{
    try {
        $itemsByLocation = $this->groupOrderItemsByLocation($order);

        if (empty($itemsByLocation)) {
            return ['success' => false, 'message' => 'Không thể xác định phương án vận chuyển do thiếu hàng.'];
        }

        $destinationProvinceCode = $order->shipping_old_province_code;
        $originLocationIds = array_keys($itemsByLocation);
        $originProvinces = StoreLocation::whereIn('id', $originLocationIds)->pluck('province_code', 'id');
        $deliveryDates = [];

        foreach ($originLocationIds as $locationId) {
            $fromProvinceCode = $originProvinces[$locationId];
            // Sử dụng carrier_name 'store_shipper' cho giao hàng nội bộ
            $transitTime = ShippingTransitTime::getTransitTime(
                'store_shipper',
                $fromProvinceCode,
                $destinationProvinceCode
            );

            $transitDays = $transitTime ? $transitTime->transit_days_max : 7; // Mặc định 7 ngày nếu không có dữ liệu
            $deliveryDates[] = Carbon::now()->addDays($transitDays);
        }

        // Ngày giao hàng của toàn bộ đơn hàng là ngày muộn nhất trong các gói hàng
        $earliestDeliveryDate = max($deliveryDates);

        // Logic tính phí vận chuyển (ví dụ: phí cố định cho mỗi gói hàng)
        $numberOfFulfillments = count($itemsByLocation);
        $shippingFeePerFulfillment = 30000; // Phí cố định 30,000 VND
        $totalShippingFee = $numberOfFulfillments * $shippingFeePerFulfillment;
        
        return [
            'success' => true,
            'earliest_delivery_date' => $earliestDeliveryDate->format('Y-m-d'),
            'shipping_fee' => $totalShippingFee,
            'number_of_packages' => $numberOfFulfillments,
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
 }

/**
 * Creates fulfillment records for a confirmed order.
 * This method will split the order, create packages and deduct inventory WITH LEDGER ENTRIES.
 * 
 * @param Order $order The paid order object
 * @return void
 * @throws Exception if any error occurs
 */
public function createFulfillmentsForOrder(Order $order): void
{
DB::transaction(function () use ($order) {
$itemsByLocation = $this->groupOrderItemsByLocation($order);

if (empty($itemsByLocation)) {
 throw new Exception("Không thể tạo fulfillment cho đơn hàng {$order->order_code} do không tìm thấy nguồn hàng.");
}

foreach ($itemsByLocation as $locationId => $items) {
 // Tạo một bản ghi fulfillment cho mỗi kho hàng
 $fulfillment = $order->fulfillments()->create([
 'store_location_id' => $locationId,
 'status' => 'pending',
 ]);
 
 // Chuẩn bị dữ liệu items để chèn hàng loạt
 $fulfillmentItemsData = collect($items)->map(function ($item) use ($fulfillment) {
 return [
'order_fulfillment_id' => $fulfillment->id,
'order_item_id' => $item->id,
'quantity' => $item->quantity,
'created_at' => now(),
'updated_at' => now(),
 ];
 })->all();
 
 // Chèn hàng loạt để tối ưu hiệu suất
 DB::table('order_fulfillment_items')->insert($fulfillmentItemsData);

 // REMOVED: Logic trừ tồn kho đã được chuyển sang InventoryCommitmentService
 // để tránh trừ kho 2 lần. Fulfillment chỉ tạo cấu trúc fulfillment,
 // việc trừ kho sẽ được xử lý bởi InventoryCommitmentService.commitInventoryForOrder()

 // REMOVED: Package creation - now using order_fulfillments directly
}

// Tạo tracking codes cho tất cả fulfillments
$trackingCodeService = new TrackingCodeService();
$trackingCodes = $trackingCodeService->generateTrackingCodesForAllFulfillments($order);

\Log::info('Generated tracking codes for all fulfillments', [
    'order_id' => $order->id,
    'order_code' => $order->order_code,
    'tracking_codes' => $trackingCodes,
    'fulfillments_count' => count($trackingCodes)
]);

// Chỉ cập nhật trạng thái thành 'processing' nếu đơn hàng đã được thanh toán
// COD orders sẽ giữ trạng thái 'pending_confirmation' cho đến khi được xác nhận
if ($order->payment_status === 'paid') {
    $order->status = 'processing';
    $order->save();
}
});
}

/**
 * Tạo Order Fulfillments và Order Fulfillment Items
 */
public function createOrderFulfillments($order, $cartItems, $shipments, $orderItemsMap)
{
    $createdItems = [];
    
    foreach ($shipments as $shipmentData) {
        $fulfillment = OrderFulfillment::create([
            'order_id' => $order->id,
            'store_location_id' => $shipmentData['store_location_id'],
            'status' => 'pending',
            'shipping_carrier' => $shipmentData['shipping_method'],
            'shipping_fee' => $shipmentData['shipping_fee'],
            'desired_delivery_date' => $shipmentData['delivery_date'] ?? null,
            'desired_delivery_time_slot' => $shipmentData['delivery_time_slot'] ?? null,
        ]);

        // Tìm các sản phẩm thuộc kho này và tạo fulfillment items
        $itemsForThisFulfillment = $cartItems->where('store_location_id', $shipmentData['store_location_id']);
        foreach ($itemsForThisFulfillment as $item) {
            $fulfillmentItem = OrderFulfillmentItem::create([
                'order_fulfillment_id' => $fulfillment->id,
                'order_item_id' => $orderItemsMap[$item->product_variant_id],
                'quantity' => $item->quantity,
            ]);
            $createdItems[] = $item->product_variant_id;
        }

        // REMOVED: Package creation - now using order_fulfillments directly
    }
    
    // Kiểm tra xem có order items nào chưa được tạo fulfillment items không
    $allOrderItems = $order->orderItems;
    $missingItems = $allOrderItems->whereNotIn('product_variant_id', $createdItems);
    
    if ($missingItems->count() > 0) {
        // Tạo fulfillment mặc định cho các items còn thiếu
        $defaultFulfillment = OrderFulfillment::create([
            'order_id' => $order->id,
            'store_location_id' => $shipments[0]['store_location_id'] ?? 1, // Sử dụng store đầu tiên hoặc mặc định
            'status' => 'pending',
            'shipping_carrier' => 'Giao hàng mặc định',
            'shipping_fee' => 0,
        ]);
        
        foreach ($missingItems as $orderItem) {
            OrderFulfillmentItem::create([
                'order_fulfillment_id' => $defaultFulfillment->id,
                'order_item_id' => $orderItem->id,
                'quantity' => $orderItem->quantity,
            ]);
        }

        // REMOVED: Package creation - now using order_fulfillments directly
    }
    
    // Tạo tracking codes cho tất cả fulfillments
    $trackingCodeService = new TrackingCodeService();
    $trackingCodes = $trackingCodeService->generateTrackingCodesForAllFulfillments($order);
    
    \Log::info('Generated tracking codes for all fulfillments in createOrderFulfillments', [
        'order_id' => $order->id,
        'order_code' => $order->order_code,
        'tracking_codes' => $trackingCodes,
        'fulfillments_count' => count($trackingCodes)
    ]);
}
}