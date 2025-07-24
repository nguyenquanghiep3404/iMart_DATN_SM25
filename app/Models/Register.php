<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;
    protected $fillable = ['store_location_id', 'name', 'device_uid', 'status'];

    public function storeLocation() {
        return $this->belongsTo(StoreLocation::class);
    }
}