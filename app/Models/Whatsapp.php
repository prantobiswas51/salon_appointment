<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Whatsapp extends Model
{
    protected $fillable = [
        'token',
        'number',
        'number_id',
    ];
}
