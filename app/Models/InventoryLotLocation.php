<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InventoryLotLocation
 *
 * This model represents the location and quantity of a specific inventory lot.
 * It links an inventory lot to a physical store location and tracks how many
 * units of that lot are present at that location.
 *
 * @package App\Models
 * @property int $id
 * @property int $lot_id The ID of the inventory lot.
 * @property int $store_location_id The ID of the store location.
 * @property int $quantity The number of items from this lot at this location.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InventoryLot $lot
 * @property-read \App\Models\StoreLocation $storeLocation
 */
class InventoryLotLocation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_lot_locations';

    /**
     * The attributes that are mass assignable.
     *
     * Using $fillable is crucial for security to prevent mass-assignment vulnerabilities.
     * Only the fields listed here can be filled using methods like `create()` or `update()`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_id',
        'store_location_id',
        'quantity',
    ];

    /**
     * Get the inventory lot that this location record belongs to.
     *
     * This defines the inverse of a one-to-many relationship.
     * An InventoryLot can have many InventoryLotLocation records, but each
     * record belongs to exactly one InventoryLot.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'lot_id');
    }

    /**
     * Get the store location associated with this inventory lot record.
     *
     * This relationship allows you to easily retrieve details about the warehouse
     * or store where this part of the lot is being stored.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function storeLocation(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'store_location_id');
    }
}
