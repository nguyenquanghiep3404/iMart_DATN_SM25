<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\AutoStockTransferController;
use Illuminate\Http\Request;
use App\Models\StockTransfer;

echo "Testing receive API for transfer ID 24" . PHP_EOL;

// Kiểm tra transfer tồn tại
$transfer = StockTransfer::find(24);
if (!$transfer) {
    echo "Transfer 24 not found!" . PHP_EOL;
    exit(1);
}

echo "Transfer found: {$transfer->transfer_code}, Status: {$transfer->status}" . PHP_EOL;

// Test controller method trực tiếp
try {
    // Sử dụng Laravel container để tạo controller với dependencies
    $controller = $app->make(AutoStockTransferController::class);
    $request = new Request();
    
    // Gọi phương thức receive
    $response = $controller->receive('24');
    
    echo "Response status: " . $response->getStatusCode() . PHP_EOL;
    echo "Response content: " . $response->getContent() . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}