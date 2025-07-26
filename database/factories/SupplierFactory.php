<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
       return [
            'name' => $this->faker->company . ' Supplier',
            'email' => $this->faker->unique()->companyEmail,
            'phone' => '0' . $this->faker->unique()->numerify('#########'), // 10 số, bắt đầu bằng 0
            'address_line' => $this->faker->streetAddress,

            // Giả sử bạn chưa có bảng địa phương, mock tạm code
            'province_code' => '79',  // TP HCM
            'district_code' => '760', // Quận 1
            'ward_code' => '26734',   // Phường Bến Nghé
        ];
    }
}