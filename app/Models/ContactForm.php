<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactForm extends Model
{
    use HasFactory;

    protected $table = 'contact_forms';

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'status', 'ip_address',
    ];
}