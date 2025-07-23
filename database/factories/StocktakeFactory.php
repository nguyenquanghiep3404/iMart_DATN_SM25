<?php
namespace Database\Factories;
use App\Models\StoreLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StocktakeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_location_id' => StoreLocation::query()->inRandomOrder()->first()->id,
            'stocktake_code' => 'ST-' . Str::upper(Str::random(8)),
            'status' => 'counting',
            'started_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'admin'))->inRandomOrder()->first()->id,
        ];
    }
}
