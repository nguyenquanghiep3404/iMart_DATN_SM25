<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'inventory_type',
        'quantity',
        'store_location_id',
    ];
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'store_location_id');
    }
}
