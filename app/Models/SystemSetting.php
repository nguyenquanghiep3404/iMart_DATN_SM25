<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    // Có thể thêm accessor/mutator để tự động cast 'value' dựa trên 'type'
    // public function getValueAttribute($value)
    // {
    //     if ($this->type === 'boolean') {
    //         return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    //     }
    //     if ($this->type === 'number') {
    //         return is_numeric($value) ? (float)$value : $value;
    //     }
    //     if ($this->type === 'json' || $this->type === 'array') {
    //         return json_decode($value, true) ?? $value;
    //     }
    //     return $value;
    // }

    // public function setValueAttribute($value)
    // {
    //     if ($this->type === 'json' || $this->type === 'array') {
    //         $this->attributes['value'] = json_encode($value);
    //     } else {
    //         $this->attributes['value'] = $value;
    //     }
    // }
}