<?php

use Illuminate\Support\Facades\Route;
use App\Models\Order;

// Add this route to web.php temporarily
Route::get('/test-order-71', function () {
    $order = Order::with([
        'fulfillments:id,order_id,store_location_id,shipper_id,tracking_code,shipping_carrier,status,shipped_at,delivered_at,estimated_delivery_date,shipping_fee',
        'fulfillments.storeLocation:id,name'
    ])->find(71);
    
    if (!$order) {
        return 'Order not found';
    }
    
    $html = '<h1>Order #' . $order->order_code . '</h1>';
    $html .= '<h2>Customer: ' . $order->customer_name . '</h2>';
    
    foreach ($order->fulfillments as $fulfillment) {
        $html .= '<div style="border: 1px solid #ccc; margin: 10px; padding: 10px;">';
        $html .= '<h3>Fulfillment #' . $fulfillment->id . '</h3>';
        $html .= '<p>Store: ' . $fulfillment->storeLocation->name . '</p>';
        $html .= '<p>Status: ' . $fulfillment->status . '</p>';
        
        if ($fulfillment->estimated_delivery_date) {
            $formatted_date = \Carbon\Carbon::parse($fulfillment->estimated_delivery_date)->format('d/m/Y');
            $html .= '<p style="color: blue; font-weight: bold;">Dự kiến giao: ' . $formatted_date . '</p>';
        } else {
            $html .= '<p style="color: red;">Không có ngày dự kiến giao</p>';
        }
        
        if ($fulfillment->shipping_fee) {
            $formatted_fee = number_format($fulfillment->shipping_fee, 0, ',', '.');
            $html .= '<p style="color: green;">Phí vận chuyển: ' . $formatted_fee . ' ₫</p>';
        }
        
        $html .= '</div>';
    }
    
    return $html;
});

echo "Route test created. Add this to routes/web.php:\n\n";
echo file_get_contents(__FILE__);