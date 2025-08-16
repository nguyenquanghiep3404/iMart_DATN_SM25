<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking stock_transfer_item_serials table structure ===\n";

// Kiểm tra cấu trúc bảng
$table = DB::select('DESCRIBE stock_transfer_item_serials');

echo "\nTable structure:\n";
foreach($table as $column) {
    echo "- {$column->Field}: {$column->Type}";
    if ($column->Null === 'NO') echo " NOT NULL";
    if ($column->Default) echo " DEFAULT '{$column->Default}'";
    echo "\n";
    
    if ($column->Field === 'status') {
        echo "  >>> Status column details:\n";
        echo "      Type: {$column->Type}\n";
        echo "      Null: {$column->Null}\n";
        echo "      Default: {$column->Default}\n";
    }
}

// Kiểm tra các giá trị status hiện có
echo "\nCurrent status values in database:\n";
$statusValues = DB::table('stock_transfer_item_serials')
    ->select('status')
    ->distinct()
    ->get();
    
foreach($statusValues as $status) {
    $count = DB::table('stock_transfer_item_serials')->where('status', $status->status)->count();
    echo "- '{$status->status}': {$count} records\n";
}

// Kiểm tra xem có ENUM constraint không
echo "\nChecking for ENUM constraints:\n";
$createTable = DB::select("SHOW CREATE TABLE stock_transfer_item_serials");
if (!empty($createTable)) {
    $createStatement = $createTable[0]->{'Create Table'};
    if (strpos($createStatement, 'enum') !== false || strpos($createStatement, 'ENUM') !== false) {
        echo "Found ENUM constraint in table definition:\n";
        // Extract ENUM values
        preg_match('/`status`\s+enum\(([^)]+)\)/', $createStatement, $matches);
        if (!empty($matches[1])) {
            echo "Allowed values: {$matches[1]}\n";
        }
    } else {
        echo "No ENUM constraint found for status column\n";
    }
}

echo "\n=== Check completed ===\n";