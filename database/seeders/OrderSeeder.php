<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB; // Đã khai báo

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Order::truncate(); // Cẩn thận
        // OrderItem::truncate(); // Cẩn thận

        $customers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->get();
        if ($customers->isEmpty()) {
            $this->command->error('No customer users found. Please seed users with customer role first.');
            return;
        }

        $availableVariants = ProductVariant::where('status', 'active')->where('stock_quantity', '>', 0)->get();
        if ($availableVariants->isEmpty()) {
            $this->command->warn('No active product variants with stock found. Orders might be created with 0-stock items for testing.');
            // Nếu muốn đảm bảo chỉ tạo đơn với hàng có sẵn, hãy return ở đây hoặc tạo thêm variant
            // return;
        }

        Order::factory(50)->create()->each(function ($order) use ($availableVariants) {
            $itemCount = rand(1, 4);
            $actualSubTotal = 0;

            for ($i = 0; $i < $itemCount; $i++) {
                $variantToOrder = $availableVariants->isNotEmpty() ? $availableVariants->random() : ProductVariant::factory()->create(['stock_quantity' => rand(1, 5)]); // Tạo nếu không có

                if (!$variantToOrder) continue;

                $quantityOrdered = rand(1, min(3, $variantToOrder->stock_quantity > 0 ? $variantToOrder->stock_quantity : 1));

                $orderItem = OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'sku' => $variantToOrder->sku,
                    'product_variant_id' => $variantToOrder->id,
                    'quantity' => $quantityOrdered,
                    // Giá và tổng tiền sẽ được tính trong OrderItemFactory
                ]);
                $actualSubTotal += $orderItem->total_price;

                // Logic trừ kho (đơn giản hóa, trong thực tế cần transaction và kiểm tra kỹ hơn)
                // if ($variantToOrder->manage_stock && $variantToOrder->stock_quantity >= $quantityOrdered) {
                //     $variantToOrder->decrement('stock_quantity', $quantityOrdered);
                // }
            }

            // Cập nhật lại tổng tiền của đơn hàng dựa trên các item thực tế
            if ($actualSubTotal > 0) {
                $order->sub_total = $actualSubTotal;
                $grandTotal = $actualSubTotal + $order->shipping_fee - $order->discount_amount;
                $order->grand_total = $grandTotal > 0 ? $grandTotal : 0;
                $order->save();
            } else {
                // Nếu không có item nào được thêm (ví dụ do hết hàng), có thể xóa đơn hàng này
                $order->delete();
            }
        });
        $this->command->info('Orders and Order Items seeded successfully!');
    }
}
