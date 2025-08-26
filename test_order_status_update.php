<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Http\Controllers\Admin\OrderController;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;

try {
    // Tìm một đơn hàng có trạng thái pending_confirmation để test
    $order = Order::where('status', 'pending_confirmation')->first();
    
    if (!$order) {
        echo "Không tìm thấy đơn hàng có trạng thái 'pending_confirmation' để test.\n";
        echo "Tạo đơn hàng test...\n";
        
        // Tạo đơn hàng test
        $order = Order::create([
            'order_code' => 'TEST-' . strtoupper(uniqid()),
            'user_id' => 1, // Giả sử user ID 1 tồn tại
            'status' => 'pending_confirmation',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'total_amount' => 100000,
            'shipping_fee' => 30000,
            'final_amount' => 130000,
            'customer_name' => 'Test Customer',
            'customer_phone' => '0123456789',
            'customer_email' => 'test@example.com',
            'shipping_address' => 'Test Address',
            'store_location_id' => 1, // Giả sử location ID 1 tồn tại
        ]);
        
        echo "Đã tạo đơn hàng test: {$order->order_code} (ID: {$order->id})\n";
    }
    
    echo "Đơn hàng test: {$order->order_code} (ID: {$order->id})\n";
    echo "Trạng thái hiện tại: {$order->status}\n";
    
    // Tạo request giả lập để test cập nhật trạng thái
    $request = new OrderRequest();
    $request->merge([
        'status' => 'processing',
        'admin_note' => 'Test cập nhật trạng thái từ script'
    ]);
    
    // Giả lập user đăng nhập
    auth()->loginUsingId(1);
    
    echo "\nBắt đầu test cập nhật trạng thái từ 'pending_confirmation' sang 'processing'...\n";
    
    // Gọi controller để test
    $controller = app(OrderController::class);
    $response = $controller->updateStatus($request, $order);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
    // Kiểm tra xem response có phải là JSON hợp lệ không
    $responseData = json_decode($response->getContent(), true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\n✅ JSON parsing thành công!\n";
        echo "Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
        echo "Message: " . $responseData['message'] . "\n";
        
        // Kiểm tra trạng thái đơn hàng sau khi cập nhật
        $order->refresh();
        echo "Trạng thái sau cập nhật: {$order->status}\n";
        
    } else {
        echo "\n❌ Lỗi JSON parsing: " . json_last_error_msg() . "\n";
        echo "Response content có thể chứa debug output hoặc không phải JSON hợp lệ.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}