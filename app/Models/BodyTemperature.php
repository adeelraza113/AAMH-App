<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyTemperature extends Model
{
    use HasFactory;
    protected $table = 'body_temperature';

    protected $fillable = [
        'Date',
        'Time',
        'Body_Temperature',
        'Comments'
    ];
}
