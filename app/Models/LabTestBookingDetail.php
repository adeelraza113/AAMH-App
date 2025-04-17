<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTestBookingDetail extends Model
{
    protected $table = 'tblLabTestBookingDetails';

    protected $fillable = [
        'BookingID', 'ServiceProfileID',
    ];

    public $timestamps = false; 
}
