<?php

require_once 'vendor/autoload.php';

// Test script để kiểm tra logic tính thời gian vận chuyển mới

use App\Models\ShippingTransitTime;
use App\Services\DeliveryOptimizationService;
use App\Models\Order;
use App\Models\StoreLocation;

echo "=== TEST SHIPPING TRANSIT TIME LOGIC ===\n\n";

// Test 1: Kiểm tra ShippingTransitTime::getTransitTime
echo "Test 1: ShippingTransitTime::getTransitTime\n";
echo "- Giao hàng nội bộ (store_shipper) từ HN đến HCM:\n";
$transitTime = ShippingTransitTime::getTransitTime('store_shipper', 'HN', 'HCM');
if ($transitTime) {
    echo "  + Thời gian: {$transitTime->transit_days_min}-{$transitTime->transit_days_max} ngày\n";
} else {
    echo "  + Không có dữ liệu, sử dụng mặc định: 7 ngày\n";
}

echo "- Giao hàng GHN từ HN đến HCM:\n";
$ghnTransitTime = ShippingTransitTime::getTransitTime('GHN', 'HN', 'HCM');
if ($ghnTransitTime) {
    echo "  + Thời gian: {$ghnTransitTime->transit_days_min}-{$ghnTransitTime->transit_days_max} ngày\n";
} else {
    echo "  + Không có dữ liệu, sử dụng mặc định: 2-3 ngày\n";
}

echo "- Giao hàng GHTK từ HN đến HCM:\n";
$ghtkTransitTime = ShippingTransitTime::getTransitTime('GHTK', 'HN', 'HCM');
if ($ghtkTransitTime) {
    echo "  + Thời gian: {$ghtkTransitTime->transit_days_min}-{$ghtkTransitTime->transit_days_max} ngày\n";
} else {
    echo "  + Không có dữ liệu, sử dụng mặc định: 3-5 ngày\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
echo "\nCác phương thức giao hàng đã được cập nhật để sử dụng bảng shipping_transit_times:\n";
echo "1. Giao hàng nội bộ: Sử dụng carrier_name = 'store_shipper'\n";
echo "2. Giao hàng GHN: Sử dụng carrier_name = 'GHN'\n";
echo "3. Giao hàng GHTK: Sử dụng carrier_name = 'GHTK'\n";
echo "4. Nếu không có dữ liệu trong bảng, sử dụng giá trị mặc định:\n";
echo "   - Giao hàng nội bộ: 7 ngày\n";
echo "   - GHN: 2-3 ngày\n";
echo "   - GHTK: 3-5 ngày\n";