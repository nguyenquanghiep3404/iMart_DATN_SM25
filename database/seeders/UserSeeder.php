<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role; // Đã import ở RolePermissionSeeder
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Đã import ở RolePermissionSeeder

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $customerRole = Role::where('name', 'customer')->firstOrFail();
        $shipperRole = Role::where('name', 'shipper')->firstOrFail();
        $contentManagerRole = Role::where('name', 'content_manager')->firstOrFail();
        $orderManagerRole = Role::where('name', 'order_manager')->firstOrFail();

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'], // Điều kiện để tìm
            [ // Thuộc tính để tạo nếu không tìm thấy (hoặc để update nếu dùng updateOrCreate)
                'name' => 'Super Admin',
                'phone_number' => '0987654321', // Ví dụ
                'password' => Hash::make('password'), // Cung cấp password trực tiếp
                'status' => 'active',
                'email_verified_at' => now(),
                // Các trường khác sẽ lấy từ factory nếu không được định nghĩa ở đây
                // Tuy nhiên, firstOrCreate sẽ dùng các giá trị này để tạo mới nếu cần.
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        if (!$admin->images()->where('type', 'avatar')->exists()) {
            UploadedFile::factory()->attachedTo($admin, 'avatar')->create([
                'path' => 'users/avatars/admin_avatar.jpg',
                'filename' => 'admin_avatar.jpg',
                'original_name' => 'admin_avatar.jpg',
            ]);
        }


        // Create Content Manager
        $contentUser = User::firstOrCreate(
            ['email' => 'content@example.com'],
            [
                'name' => 'Content Manager',
                'phone_number' => '0987654322', // Ví dụ
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $contentUser->roles()->syncWithoutDetaching([$contentManagerRole->id]);
        if (!$contentUser->images()->where('type', 'avatar')->exists()) {
            UploadedFile::factory()->attachedTo($contentUser, 'avatar')->create();
        }

        // Create Order Manager
        $orderUser = User::firstOrCreate(
            ['email' => 'order@example.com'],
            [
                'name' => 'Order Manager',
                'phone_number' => '0987654323', // Ví dụ
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $orderUser->roles()->syncWithoutDetaching([$orderManagerRole->id]);
         if (!$orderUser->images()->where('type', 'avatar')->exists()) {
            UploadedFile::factory()->attachedTo($orderUser, 'avatar')->create();
        }


        // Create Shipper Users - Sử dụng factory()->create() sẽ tự động gọi definition()
        User::factory(3)->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ])->each(function ($shipper) use ($shipperRole) {
            $shipper->roles()->attach($shipperRole);
            UploadedFile::factory()->attachedTo($shipper, 'avatar')->create();
        });

        // Create Customer Users - Sử dụng factory()->create()
        User::factory(20)->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ])->each(function ($customer) use ($customerRole) {
            $customer->roles()->attach($customerRole);
            if (rand(0,1)) { // 50% khách hàng có avatar
                UploadedFile::factory()->attachedTo($customer, 'avatar')->create();
            }
        });

        $this->command->info('Users seeded successfully!');
    }
}