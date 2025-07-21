<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_location_id',
        'stocktake_code',
        'status',
        'started_by',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Get the store location where the stocktake was performed.
     */
    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    /**
     * Get the user who started the stocktake.
     */
    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    /**
     * Get the items for the stocktake.
     */
    public function stocktakeItems()
    {
        return $this->hasMany(StocktakeItem::class);
    }
}
