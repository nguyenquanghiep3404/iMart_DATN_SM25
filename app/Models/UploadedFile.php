<?php

// app/Models/UploadedFile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'path',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'type',
        'order',
        'alt_text',
        'user_id',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
