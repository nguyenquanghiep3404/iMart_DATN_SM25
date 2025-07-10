<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;
use App\Models\User;
use App\Models\Province;
use App\Models\Ward;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $faker_vi = \Faker\Factory::create('vi_VN');
        
        // Lấy random province và ward
        $province = Province::inRandomOrder()->first();
        $ward = null;
        
        if ($province) {
            $ward = Ward::where('province_code', $province->code)->inRandomOrder()->first();
        }
        
        // Fallback nếu không có dữ liệu location
        if (!$province) {
            $province = Province::create([
                'code' => '11',
                'name' => 'Hà Nội',
                'slug' => 'ha-noi', 
                'type' => 'thanh-pho',
                'name_with_type' => 'Thành phố Hà Nội'
            ]);
        }
        
        if (!$ward) {
            $ward = Ward::create([
                'code' => '267',
                'name' => 'Minh Châu',
                'slug' => 'minh-chau',
                'type' => 'xa',
                'name_with_type' => 'Xã Minh Châu',
                'path' => 'Minh Châu, Hà Nội',
                'path_with_type' => 'Xã Minh Châu, Thành phố Hà Nội',
                'district_code' => '',
                'province_code' => $province->code,
            ]);
        }

        return [
            'user_id' => User::factory(),
            'address_label' => $this->faker->randomElement(['Nhà riêng', 'Công ty', 'Địa chỉ phụ', null]),
            'full_name' => $this->faker->name,
            'phone_number' => $faker_vi->phoneNumber,
            'address_line1' => $faker_vi->streetAddress,
            'address_line2' => $this->faker->optional(0.3)->secondaryAddress,
            'province_code' => $province->code,
            'ward_code' => $ward->code,
            'is_default_shipping' => $this->faker->boolean(20),
            'is_default_billing' => $this->faker->boolean(20),
        ];
    }
}