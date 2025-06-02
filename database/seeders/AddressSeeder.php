<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\User;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        // Address::truncate(); // Cẩn thận
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('No users to create addresses for. Skipping AddressSeeder.');
            return;
        }

        foreach ($users as $user) {
            Address::factory(rand(0, 2))->create(['user_id' => $user->id]); // Mỗi user có 0-2 địa chỉ
        }
        $this->command->info('Addresses seeded successfully!');
    }
}
