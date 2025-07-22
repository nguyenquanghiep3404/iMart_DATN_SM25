<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocktakeItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stocktake_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stocktake_id',
        'product_variant_id',
        'inventory_type',
        'system_quantity',
        'counted_quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'system_quantity' => 'integer',
        'counted_quantity' => 'integer',
    ];

    /**
     * Get the stocktake that this item belongs to.
     */
    public function stocktake()
    {
        return $this->belongsTo(Stocktake::class);
    }

    /**
     * Get the product variant associated with the stocktake item.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
