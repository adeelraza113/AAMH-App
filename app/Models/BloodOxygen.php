<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodOxygen extends Model
{
    use HasFactory;
    protected $table = 'blood_oxygen';

    protected $fillable = [
        'Date',
        'Time',
        'Blood_Oxygen_Saturation',
        'Comments'
    ];
}
