<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;

$id = 24;

echo "Testing different queries for ID: $id" . PHP_EOL;

// Query 1: Tìm theo ID trực tiếp
$transfer1 = StockTransfer::find($id);
echo "Query 1 (direct find): " . ($transfer1 ? "Found - Code: {$transfer1->transfer_code}" : "Not found") . PHP_EOL;

// Query 2: Query cũ (chỉ AUTO-%)
$transfer2 = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')->find($id);
echo "Query 2 (AUTO-% only): " . ($transfer2 ? "Found - Code: {$transfer2->transfer_code}" : "Not found") . PHP_EOL;

// Query 3: Query mới (AUTO-% hoặc FULFILL-%)
$transfer3 = StockTransfer::where(function($q) {
    $q->where('transfer_code', 'LIKE', 'AUTO-%')
      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
})->find($id);
echo "Query 3 (AUTO-% or FULFILL-%): " . ($transfer3 ? "Found - Code: {$transfer3->transfer_code}" : "Not found") . PHP_EOL;

// Query 4: findOrFail với query mới
try {
    $transfer4 = StockTransfer::where(function($q) {
        $q->where('transfer_code', 'LIKE', 'AUTO-%')
          ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
    })->findOrFail($id);
    echo "Query 4 (findOrFail with conditions): Found - Code: {$transfer4->transfer_code}" . PHP_EOL;
} catch (Exception $e) {
    echo "Query 4 (findOrFail with conditions): Error - " . $e->getMessage() . PHP_EOL;
}

if ($transfer1) {
    echo "\nTransfer details:" . PHP_EOL;
    echo "ID: {$transfer1->id}" . PHP_EOL;
    echo "Code: {$transfer1->transfer_code}" . PHP_EOL;
    echo "Status: {$transfer1->status}" . PHP_EOL;
}