<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
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
