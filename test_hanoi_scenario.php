<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ProductInventory;
use App\Models\StoreLocation;
use App\Services\FulfillmentService;
use App\Services\FulfillmentStockTransferService;
use Illuminate\Support\Facades\DB;

echo "Laravel đã được khởi tạo thành công!\n";

// Tìm sản phẩm có SKU 'fgthy'
$productVariant = ProductVariant::where('sku', 'fgthy')->first();

if (!$productVariant) {
    echo "Không tìm thấy sản phẩm có SKU 'fgthy'\n";
    exit;
}

echo "Tìm thấy sản phẩm: {$productVariant->sku}\n";

// Kiểm tra tồn kho của sản phẩm này
$inventories = ProductInventory::where('product_variant_id', $productVariant->id)
    ->with(['storeLocation'])
    ->get();

echo "\nTồn kho của sản phẩm {$productVariant->sku}:\n";
foreach ($inventories as $inventory) {
    echo "- Kho: {$inventory->storeLocation->name} (Type: {$inventory->storeLocation->type}), Số lượng: {$inventory->quantity}\n";
}

// Tìm kho ở Hà Nội
$hanoiWarehouse = StoreLocation::where('type', 'warehouse')
    ->where('province_code', '01') // Mã tỉnh Hà Nội
    ->where('is_active', true)
    ->first();

if ($hanoiWarehouse) {
    echo "\nTìm thấy kho ở Hà Nội: {$hanoiWarehouse->name} (ID: {$hanoiWarehouse->id})\n";
} else {
    echo "\nKhông tìm thấy kho nào ở Hà Nội\n";
}

// Tạo đơn hàng test với địa chỉ giao hàng ở Hà Nội
DB::transaction(function () use ($productVariant) {
    $order = Order::create([
        'order_code' => 'HN-TEST-' . strtoupper(uniqid()),
        'user_id' => 1,
        'customer_name' => 'Khách hàng Hà Nội',
        'customer_email' => 'hanoi@example.com',
        'customer_phone' => '0987654321',
        'shipping_address_line1' => 'Số 1 Đại Cồ Việt',
        'shipping_address_line2' => 'Hai Bà Trưng',
        'shipping_province_code' => '01', // Hà Nội
        'shipping_ward_code' => '00001',
        'shipping_old_province_code' => '01', // Compatibility
        'status' => 'pending_confirmation',
        'payment_status' => 'paid',
        'payment_method' => 'cash_on_delivery',
        'sub_total' => 200000,
        'shipping_fee' => 30000,
        'grand_total' => 230000,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "\nĐã tạo đơn hàng test: {$order->order_code} (ID: {$order->id})\n";
    echo "Địa chỉ giao hàng: Hà Nội\n";
    
    // Tạo order item
    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_variant_id' => $productVariant->id,
        'product_name' => $productVariant->product->name ?? 'Test Product',
        'sku' => $productVariant->sku,
        'quantity' => 1,
        'price' => 200000,
        'total_price' => 200000
    ]);
    
    echo "Đã tạo order item: {$orderItem->sku} x {$orderItem->quantity}\n";
    
    // Tạo fulfillments cho đơn hàng
    echo "\nTạo fulfillments cho đơn hàng...\n";
    try {
        $fulfillmentService = new FulfillmentService();
        $fulfillmentService->createFulfillmentsForOrder($order);
        
        echo "Đã tạo fulfillments thành công!\n";
        
        // Kiểm tra fulfillments
        $order->refresh();
        $fulfillments = $order->fulfillments;
        echo "Số lượng fulfillments: {$fulfillments->count()}\n";
        
        foreach ($fulfillments as $fulfillment) {
            $storeName = $fulfillment->storeLocation->name ?? 'Unknown';
            echo "- Fulfillment ID: {$fulfillment->id}, Status: {$fulfillment->status}, Store: {$storeName} (ID: {$fulfillment->store_location_id})\n";
        }
        
        // Test workflow: Chuyển đơn hàng sang trạng thái 'processing'
        echo "\n=== TEST WORKFLOW HÀ NỘI ===\n";
        echo "Chuyển đơn hàng từ '{$order->status}' sang 'processing'...\n";
        
        $order->status = 'processing';
        $order->save();
        
        echo "Đã cập nhật trạng thái đơn hàng!\n";
        
        // Đợi Observer xử lý
        sleep(2);
        
        // Kiểm tra phiếu chuyển kho được tạo
        $transfers = \App\Models\StockTransfer::where('created_at', '>=', now()->subMinutes(1))->get();
        echo "\nSố lượng phiếu chuyển kho được tạo: {$transfers->count()}\n";
        
        foreach ($transfers as $transfer) {
            $fromStore = \App\Models\StoreLocation::find($transfer->from_location_id);
            $toStore = \App\Models\StoreLocation::find($transfer->to_location_id);
            echo "- Transfer: {$transfer->transfer_code}\n";
            echo "  From: {$fromStore->name} (ID: {$transfer->from_location_id})\n";
            echo "  To: {$toStore->name} (ID: {$transfer->to_location_id})\n";
            echo "  Status: {$transfer->status}\n";
        }
        
        if ($transfers->count() > 0) {
            echo "\n✅ CÓ PHIẾU CHUYỂN KHO ĐƯỢC TẠO!\n";
            echo "Hệ thống đã tạo phiếu chuyển kho cho trường hợp khách hàng ở Hà Nội\n";
        } else {
            echo "\n✅ KHÔNG CẦN CHUYỂN KHO!\n";
            echo "Hệ thống thông minh: Không tạo phiếu chuyển kho vì hàng đã có sẵn ở kho Hà Nội\n";
        }
        
    } catch (Exception $e) {
        echo "Lỗi: {$e->getMessage()}\n";
        echo "Stack trace: {$e->getTraceAsString()}\n";
    }
});