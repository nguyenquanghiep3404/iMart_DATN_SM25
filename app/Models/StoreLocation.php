<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'address', 'phone_number', 'is_active'];

    public function registers() {
        return $this->hasMany(Register::class);
    }

    public function productInventories() {
        return $this->hasMany(ProductInventory::class);
    }
}