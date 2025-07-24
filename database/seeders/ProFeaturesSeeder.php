<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\ReturnRequest;
use App\Models\Stocktake;
use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Models\Supplier;
use App\Models\TradeInItem;
use App\Models\WarrantyClaim;
use Illuminate\Database\Seeder;

class ProFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding professional features...');

        // 1. Tạo các địa điểm cửa hàng
        $this->command->line('Creating store locations...');
        StoreLocation::factory()->create(['name' => 'Kho Online']); // Kho chính cho web
        StoreLocation::factory(3)->create(); // 3 cửa hàng vật lý

        // 2. Tạo nhà cung cấp
        $this->command->line('Creating suppliers...');
        Supplier::factory(5)->create();

        // 3. Tạo đơn nhập hàng và các mục chi tiết
        $this->command->line('Creating purchase orders...');
        PurchaseOrder::factory(10)->create()->each(function ($po) {
            \App\Models\PurchaseOrderItem::factory(rand(2, 5))->create(['purchase_order_id' => $po->id]);
        });
        
        // 4. Tạo các phiếu chuyển kho và chi tiết
        $this->command->line('Creating stock transfers...');
        StockTransfer::factory(5)->create()->each(function ($st) {
            \App\Models\StockTransferItem::factory(rand(1, 3))->create(['stock_transfer_id' => $st->id]);
        });

        // 5. Tạo các yêu cầu trả hàng
        $this->command->line('Creating return requests...');
        if (\App\Models\Order::where('status', 'delivered')->count() > 0) {
            ReturnRequest::factory(5)->create()->each(function ($rr) {
                 $orderItem = $rr->order->orderItems()->inRandomOrder()->first();
                 if($orderItem) {
                    \App\Models\ReturnItem::factory()->create([
                        'return_request_id' => $rr->id,
                        'order_item_id' => $orderItem->id,
                        'quantity' => 1,
                    ]);
                 }
            });
        } else {
            $this->command->warn('No delivered orders found to create return requests.');
        }

        // 6. Tạo các yêu cầu bảo hành
        $this->command->line('Creating warranty claims...');
        if (\App\Models\OrderItem::count() > 0) {
            WarrantyClaim::factory(5)->create();
        } else {
            $this->command->warn('No order items found to create warranty claims.');
        }

        // 7. Tạo các sản phẩm cũ / mở hộp
        $this->command->line('Creating trade-in items...');
        TradeInItem::factory(15)->create();

        // 8. Tạo một đợt kiểm kê kho
        $this->command->line('Creating a stocktake process...');
        Stocktake::factory()->create()->each(function ($st) {
            // Lấy 10 sản phẩm ngẫu nhiên tại cửa hàng đó để kiểm kê
            $inventories = \App\Models\ProductInventory::where('store_location_id', $st->store_location_id)
                ->inRandomOrder()->take(10)->get();
            foreach ($inventories as $inv) {
                \App\Models\StocktakeItem::factory()->create([
                    'stocktake_id' => $st->id,
                    'product_variant_id' => $inv->product_variant_id,
                    'inventory_type' => $inv->inventory_type,
                    'system_quantity' => $inv->quantity,
                ]);
            }
        });

        $this->command->info('Professional features seeded successfully!');
    }
}
