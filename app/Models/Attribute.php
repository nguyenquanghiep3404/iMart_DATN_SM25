<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_type',
    ];

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
