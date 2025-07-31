<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
class CustomerGroup extends Model
{
    use HasFactory,SoftDeletes; 
    protected $fillable = [
        'name',
        'slug',
        'description',
        'min_order_count',
        'min_total_spent',
        'priority',
    ];
    protected $dates = ['deleted_at'];
    // app/Models/CustomerGroup.php
    public function users()
    {
        return $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id');
    }


    public $timestamps = true;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            $group->slug = Str::slug($group->name);
        });

        static::updating(function ($group) {
            $group->slug = Str::slug($group->name);
        });
    }
}
