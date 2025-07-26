<?php
namespace Database\Factories;
use App\Models\StoreLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StockTransferFactory extends Factory
{
    public function definition(): array
    {
        $locations = StoreLocation::query()->inRandomOrder()->take(2)->get();
        return [
            'transfer_code' => 'TR-' . Str::upper(Str::random(8)),
            'from_location_id' => $locations->first()->id,
            'to_location_id' => $locations->last()->id,
            'status' => $this->faker->randomElement(['pending', 'shipped', 'received']),
            'created_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'admin'))->inRandomOrder()->first()->id,
            'notes' => $this->faker->sentence,
        ];
    }
}