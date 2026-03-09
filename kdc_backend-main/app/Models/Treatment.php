<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'doctor_fees',
        'patient_fees'
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}