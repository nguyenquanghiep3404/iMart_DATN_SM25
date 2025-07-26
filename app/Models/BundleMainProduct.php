<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BundleMainProduct extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_bundle_id',
        'product_variant_id',
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
