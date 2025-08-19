<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking inventory_movements table structure ===\n";

// Kiểm tra cấu trúc bảng
$table = DB::select('DESCRIBE inventory_movements');

echo "\nTable structure:\n";
foreach($table as $column) {
    echo "- {$column->Field}: {$column->Type}";
    if ($column->Null === 'NO') echo " NOT NULL";
    if ($column->Default) echo " DEFAULT '{$column->Default}'";
    echo "\n";
    
    if ($column->Field === 'inventory_type') {
        echo "  >>> inventory_type column details:\n";
        echo "      Type: {$column->Type}\n";
        echo "      Null: {$column->Null}\n";
        echo "      Default: {$column->Default}\n";
    }
}

// Kiểm tra các giá trị inventory_type hiện có
echo "\nCurrent inventory_type values in database:\n";
$typeValues = DB::table('inventory_movements')
    ->select('inventory_type')
    ->distinct()
    ->get();
    
foreach($typeValues as $type) {
    $count = DB::table('inventory_movements')->where('inventory_type', $type->inventory_type)->count();
    echo "- '{$type->inventory_type}': {$count} records\n";
}

// Kiểm tra xem có ENUM constraint không
echo "\nChecking for ENUM constraints:\n";
$createTable = DB::select("SHOW CREATE TABLE inventory_movements");
if (!empty($createTable)) {
    $createStatement = $createTable[0]->{'Create Table'};
    if (strpos($createStatement, 'enum') !== false || strpos($createStatement, 'ENUM') !== false) {
        echo "Found ENUM constraint in table definition:\n";
        // Extract ENUM values for inventory_type
        preg_match('/`inventory_type`\s+enum\(([^)]+)\)/', $createStatement, $matches);
        if (!empty($matches[1])) {
            echo "Allowed values for inventory_type: {$matches[1]}\n";
        }
    } else {
        echo "No ENUM constraint found for inventory_type column\n";
    }
}

echo "\n=== Check completed ===\n";