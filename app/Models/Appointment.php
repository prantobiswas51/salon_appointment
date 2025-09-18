<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'client_id',
        'service',
        'start_time',
        'duration',
        'attendance_status',
        'status',
        'reminder_sent',
        'notes',
        'event_id'
    ];

    protected $casts = [
        'start_time' => 'datetime', // ensures start_time is a Carbon instance
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
