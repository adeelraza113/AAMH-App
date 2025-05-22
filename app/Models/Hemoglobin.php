<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hemoglobin extends Model
{
    use HasFactory;
    protected $table = 'hemoglobin';

    protected $fillable = [
        'Date',
        'Time',
        'Measurement_Type',
        'Sugar_Concentration',
        'Comments',
    ];
}
