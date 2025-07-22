<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Province;
use App\Models\Ward;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\WardOld;
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
        
        // Chọn hệ thống địa chỉ (80% dùng hệ thống MỚI, 20% dùng hệ thống CŨ)
        $addressSystem = $this->faker->randomElement(['new', 'old']);
        
        // Lấy random province và ward cho shipping
        $shippingProvince = null;
        $shippingWard = null;
        $shippingDistrict = null;
        
        if ($addressSystem === 'new') {
            $shippingProvince = Province::inRandomOrder()->first();
            if ($shippingProvince) {
                $shippingWard = Ward::where('province_code', $shippingProvince->code)->inRandomOrder()->first();
            }
        } else {
            $shippingProvince = ProvinceOld::inRandomOrder()->first();
            if ($shippingProvince) {
                $shippingDistrict = DistrictOld::where('parent_code', $shippingProvince->code)->inRandomOrder()->first();
                if ($shippingDistrict) {
                    $shippingWard = WardOld::where('parent_code', $shippingDistrict->code)->inRandomOrder()->first();
                }
            }
        }
        
        // Fallback nếu không có dữ liệu location
        if (!$shippingProvince) {
            if ($addressSystem === 'new') {
                $shippingProvince = Province::firstOrCreate([
                    'code' => '11'
                ], [
                    'name' => 'Hà Nội',
                    'slug' => 'ha-noi', 
                    'type' => 'thanh-pho',
                    'name_with_type' => 'Thành phố Hà Nội'
                ]);
                $shippingWard = Ward::firstOrCreate([
                    'code' => '267'
                ], [
                    'name' => 'Minh Châu',
                    'slug' => 'minh-chau',
                    'type' => 'xa',
                    'name_with_type' => 'Xã Minh Châu',
                    'path' => 'Minh Châu, Hà Nội',
                    'path_with_type' => 'Xã Minh Châu, Thành phố Hà Nội',
                    'district_code' => null,
                    'province_code' => $shippingProvince->code,
                ]);
            } else {
                $shippingProvince = ProvinceOld::firstOrCreate([
                    'code' => '89'
                ], [
                    'name' => 'An Giang',
                    'slug' => 'an-giang',
                    'type' => 'tinh',
                    'name_with_type' => 'Tỉnh An Giang'
                ]);
                $shippingDistrict = DistrictOld::firstOrCreate([
                    'code' => '883'
                ], [
                    'name' => 'Long Xuyên',
                    'type' => 'thanh-pho',
                    'name_with_type' => 'Thành phố Long Xuyên',
                    'path_with_type' => 'Thành phố Long Xuyên, Tỉnh An Giang',
                    'parent_code' => $shippingProvince->code,
                ]);
                $shippingWard = WardOld::firstOrCreate([
                    'code' => '30301'
                ], [
                    'name' => 'Mỹ Bình',
                    'type' => 'phuong',
                    'name_with_type' => 'Phường Mỹ Bình',
                    'path_with_type' => 'Phường Mỹ Bình, Thành phố Long Xuyên, Tỉnh An Giang',
                    'parent_code' => $shippingDistrict->code,
                ]);
            }
        }
        
        // Billing address (optional - 30% chance có khác shipping)
        $billingAddressSystem = null;
        $billingProvince = null;
        $billingWard = null;
        $billingDistrict = null;
        
        if ($this->faker->boolean(30)) {
            $billingAddressSystem = $this->faker->randomElement(['new', 'old']);
            
            if ($billingAddressSystem === 'new') {
                $billingProvince = Province::inRandomOrder()->first();
                if ($billingProvince) {
                    $billingWard = Ward::where('province_code', $billingProvince->code)->inRandomOrder()->first();
                }
            } else {
                $billingProvince = ProvinceOld::inRandomOrder()->first();
                if ($billingProvince) {
                    $billingDistrict = DistrictOld::where('parent_code', $billingProvince->code)->inRandomOrder()->first();
                    if ($billingDistrict) {
                        $billingWard = WardOld::where('parent_code', $billingDistrict->code)->inRandomOrder()->first();
                    }
                }
            }
        }
        
        // Chọn trạng thái ngẫu nhiên
        $status = $this->faker->randomElement([
            'pending_confirmation', 'processing', 'awaiting_shipment', 'shipped',
            'out_for_delivery', 'delivered', 'cancelled', 'returned'
        ]);
        
        // Chỉ gán shipper cho những trạng thái cần thiết
        $shippedBy = null;
        if (in_array($status, ['shipped', 'out_for_delivery', 'delivered'])) {
            $shippedBy = User::query()->whereHas('roles', fn($q) => $q->where('name', 'shipper'))->inRandomOrder()->first()?->id;
        }

        return [
            'user_id' => $customer->id,
            'guest_id' => null,
            'order_code' => 'DH-' . strtoupper(Str::random(10)),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone_number ?? $faker_vi->phoneNumber,
            
            // Shipping address
            'shipping_address_line1' => $faker_vi->streetAddress,
            'shipping_address_line2' => $this->faker->optional(0.2)->secondaryAddress,
            'shipping_zip_code' => $this->faker->optional(0.5)->postcode,
            'shipping_country' => 'Vietnam',
            'shipping_address_system' => $addressSystem,
            'shipping_new_province_code' => $addressSystem === 'new' ? $shippingProvince->code : null,
            'shipping_new_ward_code' => $addressSystem === 'new' ? $shippingWard->code : null,
            'shipping_old_province_code' => $addressSystem === 'old' ? $shippingProvince->code : null,
            'shipping_old_district_code' => $addressSystem === 'old' ? $shippingDistrict->code : null,
            'shipping_old_ward_code' => $addressSystem === 'old' ? $shippingWard->code : null,
            
            // Billing address (optional)
            'billing_address_line1' => $billingProvince ? $faker_vi->streetAddress : null,
            'billing_address_line2' => $billingProvince ? $this->faker->optional(0.2)->secondaryAddress : null,
            'billing_zip_code' => $billingProvince ? $this->faker->optional(0.5)->postcode : null,
            'billing_country' => $billingProvince ? 'Vietnam' : null,
            'billing_address_system' => $billingAddressSystem,
            'billing_new_province_code' => $billingAddressSystem === 'new' ? $billingProvince->code : null,
            'billing_new_ward_code' => $billingAddressSystem === 'new' ? $billingWard->code : null,
            'billing_old_province_code' => $billingAddressSystem === 'old' ? $billingProvince->code : null,
            'billing_old_district_code' => $billingAddressSystem === 'old' ? $billingDistrict->code : null,
            'billing_old_ward_code' => $billingAddressSystem === 'old' ? $billingWard->code : null,
            
            'sub_total' => $subTotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount ?? 0,
            'grand_total' => $grandTotal > 0 ? $grandTotal : 0,
            'payment_method' => $this->faker->randomElement(['COD', 'Bank Transfer', 'VNPay', 'Momo']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'shipping_method' => $this->faker->randomElement(['Giao Hàng Nhanh', 'Giao Hàng Tiết Kiệm', 'Viettel Post', 'GrabExpress']),
            'status' => $status,
            'notes_from_customer' => $this->faker->optional(0.3)->sentence,
            'desired_delivery_date' => $this->faker->optional(0.4)->dateTimeBetween('now', '+7 days')?->format('Y-m-d'),
            'desired_delivery_time_slot' => $this->faker->optional(0.4)->randomElement(['Sáng (8h-12h)', 'Chiều (13h-17h)', 'Tối (18h-21h)']),
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'processed_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'order_manager'))->inRandomOrder()->first()?->id,
            'shipped_by' => $shippedBy,
            'delivered_at' => $status === 'delivered' ? $this->faker->optional(0.8)->dateTimeThisMonth() : null,
        ];
    }
}
