<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Thêm để sử dụng DB facade nếu cần truncate
use Illuminate\Support\Facades\Log;
class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Bắt đầu chạy CartSeeder phiên bản hoàn chỉnh...');
    
        // Sử dụng chunk để xử lý user theo từng nhóm, tối ưu bộ nhớ
        \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))
            ->chunk(100, function ($customers) { // Xử lý 100 user mỗi lần
    
                foreach ($customers as $customer) {
                    if (!$this->command->confirm("Bạn có muốn tạo giỏ hàng cho user: {$customer->name} (ID: {$customer->id})?", true)) {
                        $this->command->line("-> Đã bỏ qua user ID: {$customer->id}.");
                        continue;
                    }
    
                    // Bắt đầu transaction cho mỗi user để đảm bảo an toàn
                    try {
                        \Illuminate\Support\Facades\DB::beginTransaction();
    
                        \Illuminate\Support\Facades\Log::info("Bắt đầu xử lý cho User ID: {$customer->id}");
                        $cart = \App\Models\Cart::firstOrCreate(['user_id' => $customer->id]);
    
                        $numberOfItemsInCart = rand(1, 5);
                        $variantsForThisCart = \App\Models\ProductVariant::where('status', 'active')
                            ->inRandomOrder()
                            ->limit($numberOfItemsInCart)
                            ->get();
    
                        if ($variantsForThisCart->isNotEmpty()) {
                            foreach ($variantsForThisCart as $variant) {
                                \App\Models\CartItem::firstOrCreate(
                                    ['cart_id' => $cart->id, 'cartable_id' => $variant->id, 'cartable_type' => \App\Models\ProductVariant::class],
                                    ['quantity' => rand(1, 3), 'price' => $variant->sale_price ?? $variant->price]
                                );
                            }
                            $this->command->info("-> ✅ Đã tạo giỏ hàng với " . $variantsForThisCart->count() . " sản phẩm cho user ID: {$customer->id}");
                        } else {
                            $this->command->warn("-> ⚠️ Không tìm thấy sản phẩm nào đang hoạt động để thêm cho user ID: {$customer->id}.");
                        }
    
                        \Illuminate\Support\Facades\DB::commit();
                    
                    } catch (\Throwable $e) { // Dùng \Throwable để bắt mọi loại lỗi
                        \Illuminate\Support\Facades\DB::rollBack();
                        \Illuminate\Support\Facades\Log::error("Lỗi khi xử lý user ID {$customer->id}: " . $e->getMessage(), [
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        $this->command->error("❌ Đã xảy ra lỗi với user ID {$customer->id}. Kiểm tra log để biết chi tiết.");
                    }
                }
            });
    
        $this->command->info('✅ CartSeeder đã hoàn thành thành công!');
    }
    

}
