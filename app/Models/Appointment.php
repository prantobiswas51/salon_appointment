<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'client_phone',
        'service',
        'start_time',
        'duration',
        'status',
        'reminder_sent',
        'notes',
        'event_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    
}
