<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại để truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permission_role')->truncate();
        DB::table('role_user')->truncate();
        Permission::truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Bật lại kiểm tra khóa ngoại

        // --- ROLES ---
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Quản trị viên - Toàn quyền hệ thống']);
        $customerRole = Role::create(['name' => 'customer', 'description' => 'Khách hàng - Người mua hàng']);
        $shipperRole = Role::create(['name' => 'shipper', 'description' => 'Nhân viên giao hàng']);
        $contentManagerRole = Role::create(['name' => 'content_manager', 'description' => 'Quản lý nội dung (bài viết, banner)']);
        $orderManagerRole = Role::create(['name' => 'order_manager', 'description' => 'Quản lý đơn hàng']);
        // Thêm các vai trò khác nếu cần: editor, support_staff, etc.

        // --- PERMISSIONS ---
        // Danh sách quyền hạn cơ bản, bạn có thể mở rộng tùy theo nhu cầu
        $permissionsList = [
            // Dashboard
            'access_admin_dashboard',
            // Users
            'browse_users', 'read_users', 'edit_users', 'add_users', 'delete_users',
            // Roles & Permissions
            'browse_roles', 'read_roles', 'edit_roles', 'add_roles', 'delete_roles',
            'browse_permissions', 'assign_permissions_to_role',
            // Products
            'browse_products', 'read_products', 'edit_products', 'add_products', 'delete_products',
            // Categories
            'browse_categories', 'read_categories', 'edit_categories', 'add_categories', 'delete_categories',
            // Attributes & Values
            'browse_attributes', 'read_attributes', 'edit_attributes', 'add_attributes', 'delete_attributes',
            'browse_attribute_values', 'read_attribute_values', 'edit_attribute_values', 'add_attribute_values', 'delete_attribute_values',
            // Orders
            'browse_orders', 'read_orders', 'edit_orders_status', 'delete_orders', // Thêm/sửa đơn hàng thường từ frontend hoặc logic phức tạp
            // Banners
            'browse_banners', 'read_banners', 'edit_banners', 'add_banners', 'delete_banners',
            // Posts
            'browse_posts', 'read_posts', 'edit_posts', 'add_posts', 'delete_posts',
            'browse_post_categories', 'edit_post_categories', 'add_post_categories', 'delete_post_categories',
            'browse_post_tags', 'edit_post_tags', 'add_post_tags', 'delete_post_tags',
            // Coupons
            'browse_coupons', 'read_coupons', 'edit_coupons', 'add_coupons', 'delete_coupons',
            // Reviews
            'browse_reviews', 'edit_reviews_status', 'delete_reviews',
            // System Settings
            'browse_system_settings', 'edit_system_settings',
            // Shipper specific
            'view_assigned_orders_shipper', 'update_delivery_status_shipper',
            // Uploaded Files (nếu có trang quản lý file riêng)
            // 'browse_uploaded_files', 'delete_uploaded_files',
        ];

        foreach ($permissionsList as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName],
                ['description' => 'Cho phép ' . str_replace('_', ' ', $permissionName)]
            );
        }

        // --- ASSIGN PERMISSIONS TO ROLES ---
        $allPermissions = Permission::all();

        // Admin: Toàn quyền
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Content Manager
        $contentManagerPermissions = Permission::whereIn('name', [
            'access_admin_dashboard',
            'browse_posts', 'read_posts', 'edit_posts', 'add_posts', 'delete_posts',
            'browse_post_categories', 'edit_post_categories', 'add_post_categories', 'delete_post_categories',
            'browse_post_tags', 'edit_post_tags', 'add_post_tags', 'delete_post_tags',
            'browse_banners', 'read_banners', 'edit_banners', 'add_banners', 'delete_banners',
            'browse_categories', 'read_categories', // Để xem và chọn khi tạo bài viết/banner
            'browse_products', 'read_products',   // Để xem và chọn khi tạo bài viết/banner
        ])->pluck('id');
        $contentManagerRole->permissions()->sync($contentManagerPermissions);

        // Order Manager
        $orderManagerPermissions = Permission::whereIn('name', [
            'access_admin_dashboard',
            'browse_orders', 'read_orders', 'edit_orders_status',
            'browse_users', 'read_users', // Để xem thông tin khách hàng của đơn hàng
            'browse_products', 'read_products', // Để xem thông tin sản phẩm trong đơn hàng
        ])->pluck('id');
        $orderManagerRole->permissions()->sync($orderManagerPermissions);

        // Shipper
        $shipperPermissions = Permission::whereIn('name', [
            'view_assigned_orders_shipper', 'update_delivery_status_shipper',
        ])->pluck('id');
        $shipperRole->permissions()->sync($shipperPermissions);

        // Customer role không cần gán quyền admin ở đây.

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}
