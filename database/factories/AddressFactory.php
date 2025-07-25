<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;
use App\Models\User;
use App\Models\Province;
use App\Models\Ward;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\WardOld;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $faker_vi = \Faker\Factory::create('vi_VN');
        
        // Chọn hệ thống địa chỉ
        $addressSystem = $this->faker->randomElement(['new', 'old']);
        
        // Lấy random province và ward
        $province = null;
        $ward = null;
        $district = null;
        
        if ($addressSystem === 'new') {
            $province = Province::inRandomOrder()->first();
            if ($province) {
                $ward = Ward::where('province_code', $province->code)->inRandomOrder()->first();
            }
        } else {
            $province = ProvinceOld::inRandomOrder()->first();
            if ($province) {
                $district = DistrictOld::where('parent_code', $province->code)->inRandomOrder()->first();
                if ($district) {
                    $ward = WardOld::where('parent_code', $district->code)->inRandomOrder()->first();
                }
            }
        }
        
        // Fallback nếu không có dữ liệu location
        if (!$province) {
            if ($addressSystem === 'new') {
                $province = Province::create([
                    'code' => '11',
                    'name' => 'Hà Nội',
                    'slug' => 'ha-noi', 
                    'type' => 'thanh-pho',
                    'name_with_type' => 'Thành phố Hà Nội'
                ]);
                $ward = Ward::create([
                    'code' => '267',
                    'name' => 'Minh Châu',
                    'slug' => 'minh-chau',
                    'type' => 'xa',
                    'name_with_type' => 'Xã Minh Châu',
                    'path' => 'Minh Châu, Hà Nội',
                    'path_with_type' => 'Xã Minh Châu, Thành phố Hà Nội',
                    'district_code' => null,
                    'province_code' => $province->code,
                ]);
            } else {
                $province = ProvinceOld::create([
                    'code' => '89',
                    'name' => 'An Giang',
                    'slug' => 'an-giang',
                    'type' => 'tinh',
                    'name_with_type' => 'Tỉnh An Giang'
                ]);
                $district = DistrictOld::create([
                    'code' => '883',
                    'name' => 'Long Xuyên',
                    'type' => 'thanh-pho',
                    'name_with_type' => 'Thành phố Long Xuyên',
                    'path_with_type' => 'Thành phố Long Xuyên, Tỉnh An Giang',
                    'parent_code' => $province->code,
                ]);
                $ward = WardOld::create([
                    'code' => '30301',
                    'name' => 'Mỹ Bình',
                    'type' => 'phuong',
                    'name_with_type' => 'Phường Mỹ Bình',
                    'path_with_type' => 'Phường Mỹ Bình, Thành phố Long Xuyên, Tỉnh An Giang',
                    'parent_code' => $district->code,
                ]);
            }
        }

        return [
            'user_id' => User::factory(),
            'address_label' => $this->faker->randomElement(['Nhà riêng', 'Công ty', 'Địa chỉ phụ', null]),
            'full_name' => $this->faker->name,
            'phone_number' => $faker_vi->phoneNumber,
            'address_line1' => $faker_vi->streetAddress,
            'address_line2' => $this->faker->optional(0.3)->secondaryAddress,
            'address_system' => $addressSystem,
            'new_province_code' => $addressSystem === 'new' ? $province->code : null,
            'new_ward_code' => $addressSystem === 'new' ? $ward->code : null,
            'old_province_code' => $addressSystem === 'old' ? $province->code : null,
            'old_district_code' => $addressSystem === 'old' ? $district->code : null,
            'old_ward_code' => $addressSystem === 'old' ? $ward->code : null,
            'is_default_shipping' => $this->faker->boolean(20),
            'is_default_billing' => $this->faker->boolean(20),
        ];
    }
}