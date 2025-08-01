<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Register extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['store_location_id', 'name', 'device_uid', 'status'];
    protected $dates = ['deleted_at']; 
    public function storeLocation() {
        return $this->belongsTo(StoreLocation::class);
    }
}