<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StockTransfer;

echo "Kiểm tra phiếu chuyển kho được tạo trong 2 giờ qua:\n";
echo "=================================================\n";

$transfers = StockTransfer::where('created_at', '>=', now()->subHours(2))
    ->orderBy('created_at', 'desc')
    ->get(['id', 'transfer_code', 'status', 'created_at']);

if ($transfers->count() > 0) {
    foreach ($transfers as $transfer) {
        echo "ID: {$transfer->id}\n";
        echo "Code: {$transfer->transfer_code}\n";
        echo "Status: {$transfer->status}\n";
        echo "Created: {$transfer->created_at}\n";
        echo "---\n";
    }
} else {
    echo "Không có phiếu chuyển kho nào được tạo trong 2 giờ qua.\n";
}

echo "\nKiểm tra phiếu chuyển kho có mã bắt đầu bằng 'AUTO-':\n";
echo "=====================================================\n";

$autoTransfers = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'transfer_code', 'status', 'created_at']);

if ($autoTransfers->count() > 0) {
    foreach ($autoTransfers as $transfer) {
        echo "ID: {$transfer->id}\n";
        echo "Code: {$transfer->transfer_code}\n";
        echo "Status: {$transfer->status}\n";
        echo "Created: {$transfer->created_at}\n";
        echo "---\n";
    }
} else {
    echo "Không có phiếu chuyển kho nào có mã bắt đầu bằng 'AUTO-'.\n";
}

echo "\nKiểm tra phiếu chuyển kho có mã bắt đầu bằng 'FULFILL-':\n";
echo "======================================================\n";

$fulfillTransfers = StockTransfer::where('transfer_code', 'LIKE', 'FULFILL-%')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'transfer_code', 'status', 'created_at']);

if ($fulfillTransfers->count() > 0) {
    foreach ($fulfillTransfers as $transfer) {
        echo "ID: {$transfer->id}\n";
        echo "Code: {$transfer->transfer_code}\n";
        echo "Status: {$transfer->status}\n";
        echo "Created: {$transfer->created_at}\n";
        echo "---\n";
    }
} else {
    echo "Không có phiếu chuyển kho nào có mã bắt đầu bằng 'FULFILL-'.\n";
}