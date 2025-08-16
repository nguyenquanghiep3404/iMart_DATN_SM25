<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Check stock_transfers table structure ===\n";

// Kiểm tra cấu trúc bảng
$table = DB::select('DESCRIBE stock_transfers');

echo "\nTable structure:\n";
foreach($table as $column) {
    echo "- {$column->Field}: {$column->Type}";
    if ($column->Null === 'NO') echo " NOT NULL";
    if ($column->Default !== null) echo " DEFAULT '{$column->Default}'";
    echo "\n";
}

echo "\n=== Check completed ===\n";