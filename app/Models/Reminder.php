<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'client_id',
        'appointment_id',
        'message_sent_at',
    ];
}
