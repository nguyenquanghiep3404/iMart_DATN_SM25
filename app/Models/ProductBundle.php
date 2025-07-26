<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_title',
        'description',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];


    public function mainProducts()
    {
        return $this->hasMany(BundleMainProduct::class);
    }

    public function suggestedProducts()
    {
        return $this->hasMany(BundleSuggestedProduct::class);
    }
}
