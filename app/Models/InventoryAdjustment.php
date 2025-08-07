<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'product_variant_id',
        'store_location_id',
        'user_id',
        'old_quantity',
        'new_quantity',
        'difference',
        'reason',
        'note',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }
}
