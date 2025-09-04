<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    //
    protected $fillable = [
        'client_id',
        'service',
        'appointment_time',
        'duration',
        'attendance_status',
        'status',
        'reminder_sent',
        'notes',
    ];

    protected $casts = [
        'appointment_time' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
