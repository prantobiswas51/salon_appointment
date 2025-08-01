<?php

namespace App\Models;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    //

    protected $fillable = [
        'name',
        'phone',
        'email',
        'dob',
        'notes',
        'status',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
