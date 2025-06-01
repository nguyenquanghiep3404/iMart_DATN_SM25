<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Thêm để sử dụng DB facade nếu cần truncate

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tùy chọn: Xóa dữ liệu cũ trước khi seed. Cẩn thận khi dùng ở production.
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // CartItem::truncate();
        // Cart::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy danh sách người dùng có vai trò 'customer'
        $customers = User::whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        })->get();

        // Lấy danh sách các biến thể sản phẩm đang hoạt động
        $activeVariants = ProductVariant::where('status', 'active')
                                        // ->where('stock_quantity', '>', 0) // Bạn có thể thêm điều kiện này nếu chỉ muốn thêm sản phẩm còn hàng
                                        ->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customer users found. Please run UserSeeder first. Skipping CartSeeder.');
            return;
        }

        if ($activeVariants->isEmpty()) {
            $this->command->warn('No active product variants found. Carts might be empty or have limited items. Please run ProductSeeder.');
            // Nếu không có sản phẩm nào, việc tạo giỏ hàng có thể không có nhiều ý nghĩa
            // return; // Bỏ comment dòng này nếu bạn muốn dừng lại khi không có sản phẩm
        }

        foreach ($customers as $customer) {
            // Quyết định xem có tạo giỏ hàng cho user này không (ví dụ: 70% user có giỏ hàng)
            if ($this->command->confirm("Tạo giỏ hàng cho người dùng: {$customer->name} (ID: {$customer->id})?", true)) {
            // if (rand(1, 10) <= 7) { // Hoặc dùng random
                // Sử dụng firstOrCreate để tránh tạo nhiều giỏ hàng cho cùng một user nếu seeder chạy lại
                $cart = Cart::firstOrCreate(
                    ['user_id' => $customer->id]
                    // Không cần truyền thêm gì vào mảng thứ hai nếu factory đã xử lý
                    // Hoặc bạn có thể dùng Cart::factory()->create(['user_id' => $customer->id]); nếu user_id trong factory là nullable
                );

                if ($activeVariants->isNotEmpty()) {
                    // Số lượng item ngẫu nhiên trong giỏ hàng, không vượt quá số biến thể có sẵn
                    $numberOfItemsInCart = rand(1, min(5, $activeVariants->count()));
                    
                    // Lấy ngẫu nhiên các biến thể sản phẩm và đảm bảo chúng là duy nhất cho giỏ hàng này
                    $variantsForThisCart = $activeVariants->random($numberOfItemsInCart)->unique('id');

                    foreach ($variantsForThisCart as $variant) {
                        // Sử dụng firstOrCreate để đảm bảo mỗi biến thể chỉ có một dòng trong cart_items cho giỏ hàng này
                        CartItem::firstOrCreate(
                            [
                                'cart_id' => $cart->id,
                                'product_variant_id' => $variant->id,
                            ],
                            [ // Dữ liệu sẽ được dùng để tạo nếu bản ghi chưa tồn tại
                                'quantity' => rand(1, 3),
                                'price' => $variant->sale_price ?? $variant->price, // Lấy giá tại thời điểm này
                            ]
                        );
                    }
                    $this->command->info("Đã tạo giỏ hàng với " . $variantsForThisCart->count() . " loại sản phẩm cho user ID: {$customer->id}");
                } else {
                    $this->command->line("Không có sản phẩm nào để thêm vào giỏ hàng cho user ID: {$customer->id}");
                }
            }
        }

        $this->command->info('Carts and CartItems seeded successfully (or skipped based on user input/conditions).');
    }
}
