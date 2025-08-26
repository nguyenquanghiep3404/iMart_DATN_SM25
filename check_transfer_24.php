<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;

$transfer = StockTransfer::with(['fromLocation', 'toLocation'])->find(24);

if ($transfer) {
    echo "Transfer ID: " . $transfer->id . PHP_EOL;
    echo "Code: " . $transfer->transfer_code . PHP_EOL;
    echo "Status: " . $transfer->status . PHP_EOL;
    echo "From: " . ($transfer->fromLocation ? $transfer->fromLocation->name : 'N/A') . PHP_EOL;
    echo "To: " . ($transfer->toLocation ? $transfer->toLocation->name : 'N/A') . PHP_EOL;
    echo "Created: " . $transfer->created_at . PHP_EOL;
    echo "Updated: " . $transfer->updated_at . PHP_EOL;
    
    // Kiểm tra các trạng thái được phép
    $allowedStatuses = ['in_transit', 'dispatched', 'shipped'];
    echo "Allowed statuses for receive: " . implode(', ', $allowedStatuses) . PHP_EOL;
    echo "Can receive: " . (in_array($transfer->status, $allowedStatuses) ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo "Transfer not found" . PHP_EOL;
}