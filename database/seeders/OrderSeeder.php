<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy người dùng có vai trò là customer
        $customers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->get();
        if ($customers->isEmpty()) {
            $this->command->error('Không tìm thấy người dùng nào có vai trò "customer". Vui lòng seed user trước.');
            return;
        }

        // THAY ĐỔI 1: Sửa câu truy vấn để lấy các biến thể sản phẩm còn hàng
        // Thay vì dùng `where('stock_quantity', '>', 0)`, ta dùng `whereHas` để kiểm tra
        // số lượng trong bảng `product_inventories` có liên quan.
        // `with('inventories')` được dùng để tải trước dữ liệu tồn kho, giúp tối ưu hiệu năng.
        $availableVariants = ProductVariant::with('inventories')
            ->where('status', 'active')
            ->whereHas('inventories', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->get();

        if ($availableVariants->isEmpty()) {
            $this->command->warn('Không có biến thể sản phẩm nào đang hoạt động và còn hàng. Seeder sẽ không tạo đơn hàng nào.');
            return;
        }

        $this->command->info('Bắt đầu tạo 50 đơn hàng mẫu...');

        // Tạo 50 đơn hàng
        Order::factory(50)->create()->each(function ($order) use ($availableVariants) {
            $itemCount = rand(1, 4);
            $actualSubTotal = 0;

            // Lấy ngẫu nhiên các sản phẩm để thêm vào đơn hàng
            $variantsToOrder = $availableVariants->random($itemCount < $availableVariants->count() ? $itemCount : $availableVariants->count())->unique('id');

            foreach ($variantsToOrder as $variantToOrder) {
                
                // THAY ĐỔI 2: Lấy tổng số lượng tồn kho từ tất cả các bản ghi inventory liên quan
                $totalStock = $variantToOrder->inventories->sum('quantity');

                if ($totalStock <= 0) {
                    continue; // Bỏ qua nếu sản phẩm hết hàng
                }

                // Số lượng đặt hàng sẽ từ 1 đến 3, nhưng không vượt quá số lượng tồn kho
                $quantityOrdered = rand(1, min(3, $totalStock));

                // Tạo order item
                $orderItem = OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variantToOrder->id,
                    'sku' => $variantToOrder->sku,
                    'quantity' => $quantityOrdered,
                    // Các thông tin khác như 'product_name', 'price', 'total_price' sẽ được factory xử lý
                ]);

                $actualSubTotal += $orderItem->total_price;

                // THAY ĐỔI 3: Cập nhật logic trừ kho (nếu bạn muốn kích hoạt)
                // Logic này sẽ trừ vào bản ghi inventory đầu tiên tìm thấy.
                // Trong thực tế, bạn có thể có logic phức tạp hơn (vd: trừ kho ưu tiên).
                if ($variantToOrder->manage_stock) {
                    $inventoryToUpdate = $variantToOrder->inventories->first();
                    if ($inventoryToUpdate && $inventoryToUpdate->quantity >= $quantityOrdered) {
                        $inventoryToUpdate->decrement('quantity', $quantityOrdered);
                    }
                }
            }

            // Cập nhật lại tổng tiền của đơn hàng
            if ($actualSubTotal > 0) {
                $order->sub_total = $actualSubTotal;
                $grandTotal = $actualSubTotal + $order->shipping_fee - $order->discount_amount + $order->tax_amount;
                $order->grand_total = $grandTotal > 0 ? $grandTotal : 0;
                $order->save();
            } else {
                // Nếu không có item nào được thêm, xóa đơn hàng rỗng
                $order->delete();
            }
        });

        $this->command->info('Tạo đơn hàng và chi tiết đơn hàng mẫu thành công!');
    }
}