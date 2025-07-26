<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BundleSuggestedProduct extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_bundle_id',
        'product_variant_id',
        'discount_type',
        'discount_value',
        'is_preselected',
        'display_order',
    ];

    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
