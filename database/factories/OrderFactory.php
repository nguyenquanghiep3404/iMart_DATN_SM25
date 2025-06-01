<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $customer = User::query()->whereHas('roles', fn($q) => $q->where('name', 'customer'))->inRandomOrder()->first() ?? User::factory()->create();
        $subTotal = $this->faker->numberBetween(500000, 100000000);
        $shippingFee = $this->faker->randomElement([0, 15000, 25000, 35000, 50000]);
        $discountAmount = $this->faker->optional(0.25)->numberBetween(10000, $subTotal / 10);
        $grandTotal = $subTotal + $shippingFee - ($discountAmount ?? 0);

        $faker_vi = \Faker\Factory::create('vi_VN');

        return [
            'user_id' => $customer->id,
            'guest_id' => null,
            'order_code' => 'DH-' . strtoupper(Str::random(10)),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone_number ?? $faker_vi->phoneNumber,
            'shipping_address_line1' => $faker_vi->streetAddress,
            'shipping_address_line2' => $this->faker->optional(0.2)->secondaryAddress,
            'shipping_city' => $faker_vi->city,
            'shipping_district' => $faker_vi->districtName, // Sử dụng districtName
            'shipping_ward' => $faker_vi->wardName,       // Sử dụng wardName
            'shipping_country' => 'Vietnam',
            'sub_total' => $subTotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount ?? 0,
            'grand_total' => $grandTotal > 0 ? $grandTotal : 0,
            'payment_method' => $this->faker->randomElement(['COD', 'Bank Transfer', 'VNPay', 'Momo']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'shipping_method' => $this->faker->randomElement(['Giao Hàng Nhanh', 'Giao Hàng Tiết Kiệm', 'Viettel Post', 'GrabExpress']),
            'status' => $this->faker->randomElement([
                'pending_confirmation', 'processing', 'awaiting_shipment', 'shipped',
                'out_for_delivery', 'delivered', 'cancelled', 'returned'
            ]),
            'notes_from_customer' => $this->faker->optional(0.3)->sentence,
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'processed_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'order_manager'))->inRandomOrder()->first()?->id,
            'shipped_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'shipper'))->inRandomOrder()->first()?->id,
            'delivered_at' => $this->faker->optional(0.5)->dateTimeThisMonth(),
        ];
    }
}
