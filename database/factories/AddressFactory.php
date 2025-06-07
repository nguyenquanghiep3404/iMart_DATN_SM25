<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;
use App\Models\User; // Đã import


class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $faker_vi = \Faker\Factory::create('vi_VN');
        return [
            'user_id' => User::factory(),
            'address_label' => $this->faker->randomElement(['Nhà riêng', 'Công ty', 'Địa chỉ phụ', null]),
            'full_name' => $this->faker->name,
            'phone_number' => $faker_vi->phoneNumber,
            'address_line1' => $faker_vi->streetAddress,
            'address_line2' => $this->faker->optional(0.3)->secondaryAddress,
            'city' => $faker_vi->city,
            'district' => $faker_vi->districtName,
            'ward' => $faker_vi->wardName,
            'country' => 'Vietnam',
            'is_default_shipping' => $this->faker->boolean(20),
            'is_default_billing' => $this->faker->boolean(20),
        ];
    }
}