<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'message_sent_at',
        'appointment_id',
        'client_id',
    ];
}
