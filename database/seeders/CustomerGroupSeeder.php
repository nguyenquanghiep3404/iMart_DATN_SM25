<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu muốn
        // CustomerGroup::truncate();

        CustomerGroup::factory()->count(5)->create();

        $this->command->info('Customer groups seeded successfully!');
    }
}
