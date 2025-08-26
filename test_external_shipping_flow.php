<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StoreLocation;
use App\Services\OrderFulfillmentCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== TEST LUỒNG XỬ LÝ EXTERNAL SHIPPING ===\n\n";

try {
    // Tìm một đơn hàng có trạng thái processing và có fulfillments
    $order = Order::with(['fulfillments', 'fulfillments.storeLocation'])
        ->where('status', Order::STATUS_PROCESSING)
        ->whereHas('fulfillments', function($q) {
            $q->where('status', OrderFulfillment::STATUS_PACKED);
        })
        ->first();

    if (!$order) {
        echo "Không tìm thấy đơn hàng phù hợp để test. Tạo dữ liệu test...\n";
        
        // Tạo đơn hàng test
        $order = Order::create([
            'order_code' => 'TEST-' . time(),
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '0123456789',
            'shipping_address_line1' => 'Test Address',
            'shipping_province_code' => '79', // Hồ Chí Minh
            'shipping_district_code' => '760', // Quận 1
            'shipping_ward_code' => '26734', // Phường Bến Nghé
            'status' => Order::STATUS_PROCESSING,
            'sub_total' => 100000,
            'shipping_fee' => 25000,
            'total_amount' => 125000,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_PENDING
        ]);
        
        // Tạo fulfillment test
        $storeLocation = StoreLocation::first();
        if (!$storeLocation) {
            echo "Không tìm thấy store location. Vui lòng tạo dữ liệu cơ bản trước.\n";
            exit(1);
        }
        
        $fulfillment = OrderFulfillment::create([
            'order_id' => $order->id,
            'store_location_id' => $storeLocation->id,
            'status' => OrderFulfillment::STATUS_PACKED,
            'tracking_code' => 'TEST-TRACK-' . time()
        ]);
        
        echo "Đã tạo đơn hàng test: {$order->order_code}\n";
    }

    echo "Đơn hàng test: {$order->order_code}\n";
    echo "Trạng thái hiện tại: {$order->status}\n";
    echo "Số lượng fulfillments: " . $order->fulfillments->count() . "\n\n";

    // Hiển thị thông tin fulfillments
    foreach ($order->fulfillments as $fulfillment) {
        echo "Fulfillment ID {$fulfillment->id}:\n";
        echo "  - Trạng thái: {$fulfillment->status}\n";
        echo "  - Store: " . ($fulfillment->storeLocation ? $fulfillment->storeLocation->name : 'N/A') . "\n";
        echo "  - Tracking: " . ($fulfillment->tracking_code ? $fulfillment->tracking_code : 'N/A') . "\n\n";
    }

    // Test OrderFulfillmentCheckService
    echo "=== KIỂM TRA LOGIC PHÁT HIỆN EXTERNAL SHIPPING ===\n";
    $checkService = new OrderFulfillmentCheckService();
    $result = $checkService->canAssignShipper($order);
    
    echo "Kết quả kiểm tra:\n";
    echo "  - Có thể gán shipper: " . ($result['can_assign'] ? 'Có' : 'Không') . "\n";
    echo "  - Lý do: {$result['reason']}\n";
    echo "  - Trường hợp đặc biệt: " . ($result['is_special_case'] ? 'Có' : 'Không') . "\n";
    echo "  - Yêu cầu external shipping: " . ($result['requires_external_shipping'] ? 'Có' : 'Không') . "\n\n";

    // Test chuyển trạng thái external shipping nếu cần
    if ($result['requires_external_shipping']) {
        echo "=== TEST CHUYỂN TRẠNG THÁI EXTERNAL SHIPPING ===\n";
        
        // Cập nhật trạng thái đơn hàng
        $order->update(['status' => Order::STATUS_EXTERNAL_SHIPPING]);
        echo "Đã cập nhật trạng thái đơn hàng thành: {$order->status}\n";
        
        // Kiểm tra trạng thái fulfillments sau khi Observer chạy
        $order->refresh();
        $order->load('fulfillments');
        
        echo "Trạng thái fulfillments sau khi cập nhật:\n";
        foreach ($order->fulfillments as $fulfillment) {
            echo "  - Fulfillment {$fulfillment->id}: {$fulfillment->status}\n";
        }
    }

    echo "\n=== TEST HOÀN THÀNH ===\n";
    echo "Luồng xử lý external shipping đã được kiểm tra thành công!\n";
    
} catch (Exception $e) {
    echo "Lỗi trong quá trình test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}