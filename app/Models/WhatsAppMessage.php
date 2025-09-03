<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    protected $fillable = [
        'wa_message_id',
        'from_wa_id',
        'to_wa_id',
        'type',
        'body',
        'direction',
        'status',
        'sent_at',
        'raw_payload',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
