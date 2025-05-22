<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodSugar extends Model
{
    use HasFactory;
    protected $table = 'blood_sugar';

    protected $fillable = [
        'Date',
        'Time',
        'Sugar',
        'Interval',
        'Comments',
    ];
}
