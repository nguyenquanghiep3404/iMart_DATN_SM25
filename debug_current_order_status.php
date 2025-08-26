<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

echo "=== KIỂM TRA TRẠNG THÁI ĐƠN HÀNG DH-JIHM4GDU0X ===\n\n";

// Tìm đơn hàng
$order = Order::where('order_code', 'DH-JIHM4GDU0X')->first();

if (!$order) {
    echo "Không tìm thấy đơn hàng DH-JIHM4GDU0X\n";
    exit;
}

echo "Đơn hàng: {$order->order_code}\n";
echo "Trạng thái: {$order->status}\n";
echo "Ngày tạo: {$order->created_at}\n\n";

// Kiểm tra fulfillments
echo "=== FULFILLMENTS ===\n";
$fulfillments = OrderFulfillment::where('order_id', $order->id)->get();

foreach ($fulfillments as $fulfillment) {
    echo "Fulfillment ID: {$fulfillment->id}\n";
    echo "Trạng thái: {$fulfillment->status}\n";
    echo "Kho: {$fulfillment->store_location_id}\n";
    echo "Ngày tạo: {$fulfillment->created_at}\n";
    echo "Ngày cập nhật: {$fulfillment->updated_at}\n";
    echo "---\n";
}

// Kiểm tra phiếu chuyển kho liên quan
echo "\n=== PHIẾU CHUYỂN KHO LIÊN QUAN ===\n";
// Tìm phiếu chuyển kho có chứa order code trong notes
$stockTransfers = StockTransfer::where('notes', 'like', '%' . $order->order_code . '%')
    ->orderBy('created_at', 'desc')
    ->get();

if ($stockTransfers->isEmpty()) {
    // Thử tìm theo fulfillment ID
    $fulfillmentIds = $fulfillments->pluck('id')->toArray();
    $stockTransfers = StockTransfer::where(function($query) use ($fulfillmentIds) {
        foreach ($fulfillmentIds as $fulfillmentId) {
            $query->orWhere('notes', 'like', '%fulfillment #' . $fulfillmentId . '%');
        }
    })->orderBy('created_at', 'desc')->get();
}

foreach ($stockTransfers as $transfer) {
    echo "Mã phiếu: {$transfer->transfer_code}\n";
    echo "Trạng thái: {$transfer->status}\n";
    echo "Từ kho: {$transfer->from_location_id}\n";
    echo "Đến kho: {$transfer->to_location_id}\n";
    echo "Ngày tạo: {$transfer->created_at}\n";
    echo "Ngày cập nhật: {$transfer->updated_at}\n";
    echo "Ghi chú: {$transfer->notes}\n";
    echo "---\n";
}

// Kiểm tra log cập nhật gần đây
echo "\n=== KIỂM TRA LOG CẬP NHẬT GẦN ĐÂY ===\n";

// Kiểm tra fulfillment được cập nhật gần đây
$recentFulfillmentUpdates = DB::table('order_fulfillments')
    ->where('order_id', $order->id)
    ->where('updated_at', '>', now()->subHours(24))
    ->select('id', 'status', 'store_location_id', 'updated_at')
    ->get();

echo "Fulfillments cập nhật trong 24h qua:\n";
foreach ($recentFulfillmentUpdates as $update) {
    echo "ID: {$update->id}, Status: {$update->status}, Kho: {$update->store_location_id}, Cập nhật: {$update->updated_at}\n";
}

// Kiểm tra stock transfer được cập nhật gần đây
$recentTransferUpdates = DB::table('stock_transfers')
    ->where('notes', 'like', '%' . $order->order_code . '%')
    ->where('updated_at', '>', now()->subHours(24))
    ->select('transfer_code', 'status', 'updated_at')
    ->get();

echo "\nStock transfers cập nhật trong 24h qua:\n";
foreach ($recentTransferUpdates as $update) {
    echo "Mã: {$update->transfer_code}, Status: {$update->status}, Cập nhật: {$update->updated_at}\n";
}

echo "\n=== HOÀN THÀNH ===\n";