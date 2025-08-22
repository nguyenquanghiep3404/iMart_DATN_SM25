<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderFulfillment;
use App\Services\PackageService;

try {
    $fulfillment = OrderFulfillment::first();
    
    if ($fulfillment) {
        $service = new PackageService();
        $package = $service->createDefaultPackageForFulfillment($fulfillment);
        echo "Method works! Package ID: " . $package->id . "\n";
        echo "Package Code: " . $package->package_code . "\n";
    } else {
        echo "No fulfillment found to test\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}