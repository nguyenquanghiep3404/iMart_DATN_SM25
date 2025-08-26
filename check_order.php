<?php
echo "Checking order with fulfillments...\n";

// Kết nối database trực tiếp
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=imart_datn_sm25', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra đơn hàng có trạng thái partially_delivered
    $stmt = $pdo->prepare("SELECT id, order_code, status, store_location_id, payment_status, created_at FROM orders WHERE order_code = ?");
    $stmt->execute(['DH-P5XAZJP3RB']);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "Order found: {$order['order_code']}\n";
        echo "ID: {$order['id']}\n";
        echo "Status: {$order['status']}\n";
        echo "Store Location ID: {$order['store_location_id']}\n";
        echo "Payment Status: {$order['payment_status']}\n";
        echo "Created: {$order['created_at']}\n";
        
        // Kiểm tra fulfillments
        $stmt2 = $pdo->prepare("SELECT id, status, store_location_id, shipper_id, created_at FROM order_fulfillments WHERE order_id = ?");
        $stmt2->execute([$order['id']]);
        $fulfillments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nFulfillments: " . count($fulfillments) . "\n";
        foreach ($fulfillments as $f) {
            echo "  - ID: {$f['id']}, Status: {$f['status']}, Store: {$f['store_location_id']}, Shipper: {$f['shipper_id']}, Created: {$f['created_at']}\n";
        }
        
        // Kiểm tra order items
        $stmt3 = $pdo->prepare("SELECT id, product_name, quantity, price FROM order_items WHERE order_id = ?");
        $stmt3->execute([$order['id']]);
        $items = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nOrder Items: " . count($items) . "\n";
        foreach ($items as $item) {
            echo "  - {$item['product_name']} (Qty: {$item['quantity']}, Price: {$item['price']})\n";
        }
        
    } else {
        echo "Order not found\n";
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}